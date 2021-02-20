<?php 
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Article extends REST_Controller {
    public function __construct()
    {
        parent::__construct(); 
        $this->load->model('article_model');
    }

    /**
     * Add new article with API
     * ------------------------
     * @method: POST
     */

     public function article_add_post()
     {
        header("Access-Control-Allow-Origin: *");
        //Load Authorization Token Library
        $this->load->library('Authorization_Token');

        #XSS Filtering
        $_POST = $this->security->xss_clean($_POST);

        /**
         * User token validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE){
            #create user article
            # form validation
            $this->form_validation->set_rules('title', 'Judul', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('description', 'Deskripsi', 'trim|required|max_length[200]');
            if($this->form_validation->run() == FALSE){
                //form validation error
                $message = array(
                    'status' => false,
                    'error' => $this->form_validation->error_array(),
                    'message' => validation_errors()
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            } else{
                //load article model

                $data = [                
                    'user_id' => $is_valid_token['data']->id,
                    'title' => $this->input->post('title', TRUE),
                    'description' => $this->input->post('description', TRUE),
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                if ($this->article_model->createArticles($data) > 0) {
                    $this->response([
                        'status' => true,
                        'message' => 'new Article has been created succesfully'
                    ], REST_Controller::HTTP_CREATED);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'failed create data'
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
                // echo "Success";
            }

        } else{
            $this->response([
                'status' => false,
                'message' => $is_valid_token['message']
            ], REST_Controller::HTTP_NOT_FOUND);
        }
     }

     public function deleteArticle_delete($id)
     {
        header("Access-Control-Allow-Origin: *");
    
        // Load Authorization Token Library
        $this->load->library('Authorization_Token');

        /**
         * User Token Validation
         */
        $is_valid_token = $this->authorization_token->validateToken();
        if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE)
        {
            # Delete a User Article

            # XSS Filtering (https://www.codeigniter.com/user_guide/libraries/security.html)
            $id = $this->security->xss_clean($id);
            
            if (empty($id) AND !is_numeric($id))
            {
                $this->response(['status' => FALSE, 'message' => 'Invalid Article ID' ], REST_Controller::HTTP_NOT_FOUND);
            }
            else
            {
                $delete_article = [
                    'id' => $id,
                    'user_id' => $is_valid_token['data']->id,
                ];

                // Delete an Article
                $output = $this->article_model->deleteArticles($delete_article);

                if ($output > 0 AND !empty($output))
                {
                    // Success
                    $message = [
                        'status' => true,
                        'message' => "Article Deleted"
                    ];
                    $this->response($message, REST_Controller::HTTP_OK);
                } else
                {
                    // Error
                    $message = [
                        'status' => FALSE,
                        'message' => "Article not delete"
                    ];
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            }

        } else {
            $this->response(['status' => FALSE, 'message' => $is_valid_token['message'] ], REST_Controller::HTTP_NOT_FOUND);
        }
     }

     /**
     * Edit article with API
     * ------------------------
     * @method: PUT
     */

    public function updateArticle_put()
    {
       header("Access-Control-Allow-Origin: *");
       //Load Authorization Token Library
       $this->load->library('Authorization_Token');

       #XSS Filtering
       $_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);

       /**
        * User token validation
        */
       $is_valid_token = $this->authorization_token->validateToken();
       
       if (!empty($is_valid_token) AND $is_valid_token['status'] === TRUE){
        #XSS Filtering
       $_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
        
        $id = $this->input->post('id', TRUE);
        $title = $this->input->post('title', TRUE);
        $description = $this->input->post('description', TRUE);
        
           # form validation
           $this->form_validation->set_data([
            'id' => $id,
            'title' => $title,
            'description' => $description,
           ]);

           $this->form_validation->set_rules('id', 'Article ID', 'trim|required|numeric');
           $this->form_validation->set_rules('title', 'Judul', 'trim|required|max_length[50]');
           $this->form_validation->set_rules('description', 'Deskripsi', 'trim|required|max_length[200]');
           if($this->form_validation->run() == FALSE){
               //form validation error
               $message = array(
                   'status' => false,
                   'error' => $this->form_validation->error_array(),
                   'message' => validation_errors()
               );
               $this->response($message, REST_Controller::HTTP_NOT_FOUND);
           } else{
               $data = [                
                   'id' => $id,
                   'user_id' => $is_valid_token['data']->id,
                   'title' => $title,
                   'description' => $description,
               ];

               $output = $this->article_model->updateArticles($data);
               if ($output > 0 AND !empty($output)) {
                   $this->response([
                       'status' => true,
                       'message' => 'update Article has been updated'
                   ], REST_Controller::HTTP_CREATED);
               } else {
                   $this->response([
                       'status' => false,
                      'failed to update data'
                ], REST_Controller::HTTP_BAD_REQUEST);
               }
               // echo "Success";
           }

       } else{
           $this->response([
               'status' => false,
               'message' => $is_valid_token['message']
           ], REST_Controller::HTTP_NOT_FOUND);
       }
    }
}