<?php 
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Customer extends REST_Controller {
    public function __construct()
    {
        parent::__construct(); 
        $this->load->model('customer_model');
    }

    /**
     * fetch All user data
     * @method : GET
     * @link: users/get
     */

    public function index_get()
    {
        header("Access-Control-Allow-Origin: *");
        $this->customer_model->fetch_all_users();
        $id = $this->get('id_cust');
            // jika id tidak ada (tidak panggil) 
            if($id === null) {
                // maka panggil semua data
                $data = $this->customer_model->fetch_all_users();
                // tapi jika id di panggil maka hanya id tersebut yang akan muncul pada data tersebut
            } else {
                $data = $this->customer_model->fetch_all_users($id);

            }

            if($data) {
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
     * Add new user (cust)
     * @method : POST
     * @link: customer/register
     */
    public function add_cust_post()
    {
        header("Access-Control-Allow-Origin: *");
        $_POST = $this->security->xss_clean($_POST);

        # form validation
        $this->form_validation->set_rules('fullname', 'Full Name', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[user.username]|alpha_numeric',
        array('is_unique' => 'This %s already exists please enter another username'));
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[60]|is_unique[user.email]',
        array('is_unique' => 'This %s already exists please enter another email address'));
        $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[25]');
        // $this->form_validation->set_rules('passconf', 'Password Confirmation', 'required');
        if($this->form_validation->run() == FALSE){
            //form validation error
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
             );
             $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else{
            $nameFirstChar = $this->input->post('username', TRUE)[0];
            $data = [                
                'fullname' => $this->input->post('fullname', TRUE),
                'email' => $this->input->post('email', TRUE),
                'username' => $this->input->post('username', TRUE),
                'password' => md5($this->input->post('password', TRUE)),
                // 'avatar' =>  $this->input->post('avatar', TRUE),
                'limit_pinjam' => 2,
                // 'created_at' => time(),
                // 'updated_at' => time(),
            ];
            if ($this->customer_model->insert_user($data) > 0) {
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

    public function index_put()
        {
            $id = $this->put('id_cust');
            $fullname = strip_tags($this->put('fullname'));
            $username = strip_tags($this->put('username'));
            $email = strip_tags($this->put('email'));
            $password = strip_tags($this->put('password'));
            $hp = strip_tags($this->put('no_hp'));
            $data = [
                'id_cust' => $id,
                'fullname' => $fullname,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'no_hp' => $hp
            ];

            $update = $this->customer_model->user_update($data, $id);
            if ($update) {
                $this->response([
                    'status' => true,
                    'message' => 'data customer updated'  
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'failed to update data'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

    /**
     * Update customer data
     * @method : PUT
     * @link: users/update
     */
    public function update_customer_put()
    {
        $id_cust = $this->put('id_cust');
        // var_dump($id_cust);
        $fullname = strip_tags($this->put('fullname'));
        $username = strip_tags($this->put('username'));
        $email = strip_tags($this->put('email'));
        $password = strip_tags($this->put('password'));
        $hp = strip_tags($this->put('no_hp'));
        // Validate the post data
        if(!empty($id_cust) && (!empty($fullname) || !empty($username) || !empty($email) || !empty($password) || !empty($hp))){
        //update user's account data
            $userData = array();
            if(!empty($fullname)){
                $userData['fullname'] = $fullname;
            }
            if(!empty($username)){
                $userData['username'] = $username;
            }
            if(!empty($email)){
                $userData['email'] = $email;
            }
            if(!empty($password)){
                $userData['password'] = md5($password);
            }
            if(!empty($hp)){
                $userData['no_hp'] = $hp;
            }
            $update = $this->customer_model->user_update($userData, $id_cust);
            //check if the user data is updated
            if  ($update){
                $this->response([
                    'status' => true,
                    'message' => 'User info has been updated succesfully'
                ], REST_Controller::HTTP_OK);
            } else{
                $this->response([
                    'status' => false,
                    'message' => 'Some problems occurred, please try again.'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        } else{
            // Set the response and exit
            $this->response([
                'status' => false,
                'message' => 'Provide at least one user info to update.' 
            ],REST_Controller::HTTP_BAD_REQUEST);
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
       if($this->form_validation->run() == FALSE){
           //form validation error
           $message = array(
               'status' => false,
               'error' => $this->form_validation->error_array(),
               'message' => validation_errors()
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
       } else{
           // Load login Function
           $username = $this->input->post('username');
           $pass = $this->input->post('password');
           $output = $this->customer_model->user_login($username, $pass);
           if (!empty($output) AND $output!= FALSE) {
               //Load Authorization Token Library
               $this->load->library('Authorization_Token');

               //Generate Token
               $token_data['id_cust'] = $output->id_cust;
               $token_data['fullname'] = $output->fullname;
               $token_data['username'] = $output->username;
               $token_data['email'] = $output->email;
               $token_data['no_hp'] = $output->no_hp;
               $token_data['created_at'] = $output->created_at;
               $token_data['updated_at'] = $output->updated_at;
               $token_data['limit_pinjam'] = $output->limit_pinjam;
               $token_data['time'] = time();


               $user_token = $this->authorization_token->generateToken($token_data);
            //    print_r($this->Authorization_Token->generateToken($token_data));
            //    exit;
               
               $return_data = [
                   'id_cust' => $output->id_cust,
                   'full_name' => $output->fullname,
                   'email' => $output->email,
                   'limit_pinjam' => $output->limit_pinjam,
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
     * Hapus data customer
     */
    public function index_delete()
    {
        $id = $this->delete('id_cust');
        if (empty($id)) {
            $this->response([
                'status' => false,
                'data' => 'id null'
            ], REST_Controller::HTTP_NOT_FOUND);
        }else{
            if ($this->customer_model->user_delete($id) > 0) {
                $this->response([
                    'status' => true,
                    'id' => $id,
                    'message' => 'deleted'
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => false,
                    'data' => 'id not found'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function getRows_get()
    {
        $customer = $this->customer_model->getCount();
        if($customer){
            $this->response([
                'status' => true,
                'data' => $customer
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else{
            $this->response([
                'status' => false,
                'message' => 'id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

}
?>