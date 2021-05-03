<?php 
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Lokasi extends REST_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Lokasi_model', 'lk');
    }

    // Get Data
    public function index_get() {
        $id = $this->get('id_lokasi');
        // jika id tidak ada (tidak panggil) 
        if($id === null) {
            // maka panggil semua data
            $lokasi = $this->lk->getLokasi($id);
            // tapi jika id di panggil maka hanya id tersebut yang akan muncul pada data tersebut
        } else {
            $lokasi = $this->lk->getLokasi($id);
        }

        if($lokasi) {
            $this->response([
                'status' => true,
                'data' => $lokasi
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else {
            $this->response([
                'status' => false,
                'message' => 'id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function mLok_get() {
        $id = $this->get('id_mitra');
        $mitra = $this->lk->getLokMitra($id);
        if($mitra){
            $this->response([
                'status' => true,
                'data' => $mitra
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else{
            $this->response([
                'status' => false,
                'message' => 'id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    //Add data
    public function index_post()
    {
        header("Access-Control-Allow-Origin: *");
        $_POST = $this->security->xss_clean($_POST);
        $id = $this->input->post('id_mitra', TRUE);
        # form validation
        $this->form_validation->set_rules('id_mitra', 'ID Mitra', 'required');
        $this->form_validation->set_rules('alamat', 'Alamat', 'required');
        $this->form_validation->set_rules('latitude', 'Latitude', 'required');
        $this->form_validation->set_rules('longitude', 'Longitude', 'required');
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
                'id_mitra' => $this->input->post('id_mitra', TRUE),
                'alamat' => $this->input->post('alamat', TRUE),
                'latitude' => $this->input->post('latitude', TRUE),
                'longitude' => $this->input->post('longitude', TRUE),
            ];
            if ($this->lk->add($data) > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'Data lokasi berhasil ditambahkan'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data lokasi gagal ditambahkan'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    //Delete data lokasi
    public function index_delete()
    {
        $id = $this->delete('id_lokasi');
        if (empty($id)) {
            $this->response([
                'status' => false,
                'data' => 'id null'
            ], REST_Controller::HTTP_NOT_FOUND);
        }else{
            if ($this->lk->delete($id) > 0) {
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

    //Update data lokasi
    public function index_put()
    {
        $id = $this->put('id_lokasi');
        $data = [
            'id_mitra' => $this->put('id_mitra'),
            'alamat' => $this->put('alamat'),
            'latitude' => $this->put('latitude'),
            'longitude' => $this->put('longitude')
        ];
        if ($this->lk->update($id, $data) > 0) {
            $this->response([
                'success' => true,
                'message' => 'Data lokasi updated'  
            ], REST_Controller::HTTP_CREATED);
        } else {
            $this->response([
                'success' => false,
                'message' => 'failed to update data'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function edit_put()
    {
        $id = $this->put('id_lokasi');
        // var_dump($id_cust);
        // $id_user = strip_tags($this->put('id_user'));
        // $id_produk = strip_tags($this->put('id_produk'));
        $id_mitra = strip_tags($this->put('id_mitra'));
        $alamat = strip_tags($this->put('alamat'));
        $latitude = strip_tags($this->put('latitude'));
        $longitude = strip_tags($this->put('longitude'));
        // Validate the post data
        if(!empty($id) || !empty($id_mitra) || !empty($alamat) || !empty($longitude) || !empty($longitude)){
        //update user's account data
            $lokasiData = array();
            if(!empty($id_mitra)){
                $lokasiData['id_mitra'] = $id_mitra;
            }
            if(!empty($alamat)){
                $lokasiData['alamat'] = $alamat;
            }
            if(!empty($latitude)){
                $lokasiData['latitude'] = $latitude;
            }
            if(!empty($longitude)){
                $lokasiData['longitude'] =$longitude;
            $update = $this->lk->update($id, $lokasiData);
            if  ($update){
                $this->response([
                    'status' => true,
                    'message' => 'updated succesfully'
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
    }

}