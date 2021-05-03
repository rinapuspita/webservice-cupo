<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Users extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function user_get()
    {
        $mitra = $this->user_model->getMitra();
        if ($mitra) {
            $this->response([
                'status' => true,
                'data' => $mitra
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else {
            $this->response([
                'status' => false,
                'message' => 'id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function userActive_get()
    {
        $mitra = $this->user_model->getMitraAktivasi();
        if ($mitra) {
            $this->response([
                'status' => true,
                'data' => $mitra
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else {
            $this->response([
                'status' => false,
                'message' => 'id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function changeActive_get()
    {
        $id = $this->get('id');
        $mitra = $this->user_model->changeStatus($id);
        if($mitra){
            $this->response([
                'status' => true,
                'data' => 'User activation successfully'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else{
            $this->response([
                'status' => false,
                'message' => 'failed update user activation'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }



    /**
     * Add new user (mitra)
     * @method : POST
     * @link: users/register
     */
    public function add_users_post()
    {
        header("Access-Control-Allow-Origin: *");
        $_POST = $this->security->xss_clean($_POST);

        # form validation
        $this->form_validation->set_rules('fullname', 'Full Name', 'trim|required|max_length[50]');
        $this->form_validation->set_rules(
            'username',
            'Username',
            'trim|required|is_unique[user.username]|alpha_numeric',
            array('is_unique' => 'This %s already exists please enter another username')
        );
        $this->form_validation->set_rules(
            'email',
            'Email',
            'trim|required|valid_email|max_length[60]|is_unique[user.email]',
            array('is_unique' => 'This %s already exists please enter another email address')
        );
        $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[25]');
        // $this->form_validation->set_rules('passconf', 'Password Confirmation', 'required');
        if ($this->form_validation->run() == FALSE) {
            //form validation error
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {

            $data = [
                'fullname' => $this->input->post('fullname', TRUE),
                'email' => $this->input->post('email', TRUE),
                'username' => $this->input->post('username', TRUE),
                'password' => md5($this->input->post('password', TRUE)),
                // 'avatar' => $this->input->post('avatar', TRUE),
                'level' => 2,
                // 'created_at' => time(),
                // 'updated_at' => time(),
            ];
            if ($this->user_model->insert_user($data) > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'new User has been created succesfully'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'failed create data'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            // echo "Success";
        }
    }
    /**
     * fetch All user data
     * @method : GET
     * @link: users/get
     */

    public function fetch_all_users_get()
    {
        header("Access-Control-Allow-Origin: *");
        $this->user_model->fetch_all_users();
        // $data = array('returned');
        // $this->response($data);
        $id = $this->get('id');
        // jika id tidak ada (tidak panggil) 
        if ($id === null) {
            // maka panggil semua data
            $data = $this->user_model->fetch_all_users();
            // tapi jika id di panggil maka hanya id tersebut yang akan muncul pada data tersebut
        } else {
            $data = $this->user_model->fetch_all_users($id);
        }

        if ($data) {
            $this->response([
                'status' => true,
                'data' => $data
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else {
            $this->response([
                'status' => false,
                'message' => 'id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code

        }
    }

    /**
     * Update user data
     * @method : PUT
     * @link: users/update
     */
    public function update_user_put()
    {
        $id = $this->put('id');
        // header("Access-Control-Allow-Origin: *");
        // $_POST = $this->security->xss_clean($_POST);

        $fullname = strip_tags($this->put('fullname'));
        $username = strip_tags($this->put('username'));
        $email = strip_tags($this->put('email'));
        $password = strip_tags($this->put('password'));
        $this->response([
            'status' => true,
            'message' => $id . $fullname . $username . $email . $password
        ], REST_Controller::HTTP_OK);
        var_dump($id, $fullname, $username, $email, $password);
        die;
        // Validate the post data
        if (!empty($id) && (!empty($fullname) || !empty($username) || !empty($email) || !empty($password))) {
            //update user's account data
            $userData = array();
            if (!empty($fullname)) {
                $userData['fullname'] = $fullname;
            }
            if (!empty($username)) {
                $userData['username'] = $username;
            }
            if (!empty($email)) {
                $userData['email'] = $email;
            }
            if (!empty($password)) {
                $userData['password'] = md5($password);
            }
            $userData['updated_at'] = date("Y-m-d H:i:s");
            $update = $this->user_model->user_update($userData, $id);
            //check if the user data is updated
            if ($update) {
                $this->response([
                    'status' => true,
                    'message' => 'User info has been updated succesfully'
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Some problems occurred, please try again.'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            // Set the response and exit
            $this->response([
                'status' => false,
                'message' => 'Provide at least one user info to update.'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    /**
     * User Login API
     * ---------------------
     * @param: username or email
     * @param: password
     * ---------------------
     * @link: users/login
     */

    public function login_post()
    {
        header("Access-Control-Allow-Origin: *");
        $_POST = $this->security->xss_clean($_POST);

        # form validation
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[25]');
        if ($this->form_validation->run() == FALSE) {
            //form validation error
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // Load login Function
            $username = $this->input->post('username');
            $pass = $this->input->post('password');
            $output = $this->user_model->user_login($username, $pass);
            if (!empty($output) and $output != FALSE) {
                //Load Authorization Token Library
                $this->load->library('Authorization_Token');

                //Generate Token
                $token_data['id'] = $output->id;
                $token_data['fullname'] = $output->fullname;
                $token_data['username'] = $output->username;
                $token_data['email'] = $output->email;
                $token_data['created_at'] = $output->created_at;
                $token_data['updated_at'] = $output->updated_at;
                $token_data['time'] = time();

                $user_token = $this->authorization_token->generateToken($token_data);

                $return_data = [
                    'user_id' => $output->id,
                    'full_name' => $output->fullname,
                    'email' => $output->email,
                    'level' => $output->level,
                    'created_at' => $output->created_at,
                    'token' => $user_token
                ];
                //Login Success
                $this->response([
                    'status' => true,
                    'data' => $return_data,
                    'message' => 'User Login succesfully'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Invalid Username or Password'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * Hapus data user
     */
    public function index_delete()
    {
        $id = $this->delete('id');
        if (empty($id)) {
            $this->response([
                'status' => false,
                'data' => 'id null'
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            if ($this->user_model->user_delete($id) > 0) {
                $this->response([
                    'status' => true,
                    'id' => $id,
                    'message' => 'deleted'
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'data' => 'id not found'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
}
