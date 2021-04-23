<?php 
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Pengembalian extends REST_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pengembalian_model', 'pm');
        $this->load->model('Peminjaman_model', 'pem');
        $this->load->model('Produk_model', 'prm');
        $this->load->model('Customer_model', 'cm');
    }

    // Get Data
    public function index_get() {
        $id = $this->get('id_kembali');
        // jika id tidak ada (tidak panggil) 
        if($id === null) {
            // maka panggil semua data
            $kembali = $this->pm->getKembali();
            // tapi jika id di panggil maka hanya id tersebut yang akan muncul pada data tersebut
        } else {
            $kembali = $this->pm->getKembali($id);
        }

        if($kembali) {
            $this->response([
                'status' => true,
                'data' => $kembali
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else {
            $this->response([
                'status' => false,
                'message' => 'id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        
        }
    }

    public function mKembali_get() {
        $id = $this->get('id_mitra');
        $mitra = $this->pm->getKembaliMitra($id);
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

    public function getDetail_post()
    {
        $id = $this->input->post('id_user', TRUE);
        $produk = $this->input->post('id_produk', TRUE);
        $get = $this->pm->getDetailKembali($id, $produk);
        if($get>0){
            $this->response([
                'status' => true,
                'data' => $get
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else{
            $this->response([
                'status' => false,
                'message' => 'Gagal memindai data'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function getRows_get()
        {
            $kembali = $this->pm->getCountKembali();
            if($kembali){
                $this->response([
                    'status' => true,
                    'data' => $kembali
                ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
            } else{
                $this->response([
                    'status' => false,
                    'message' => 'id not found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }

    public function add_post()
    {
        header("Access-Control-Allow-Origin: *");
        $_POST = $this->security->xss_clean($_POST);
        $id = $this->input->post('id_user', TRUE);
        $produk = $this->input->post('id_produk', TRUE);
        $pinjam = $this->input->post('id_pinjam', TRUE);
        # form validation
        $this->form_validation->set_rules('id_produk', 'Kode Produk', 'required');
        if($this->form_validation->run() == FALSE){
            //form validation error
            $message = array(
                'status' => false,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
             );
             $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else{
            $tanggal_kembali = $this->pem->getTgl($pinjam, $id, $produk);
            $date = date("Y-m-d H:i:s", strtotime($tanggal_kembali));
            $tgl = date_create($date);
            $tanggal_now = date_create(date("Y-m-d H:i:s"));
            $terlambat = date_diff($tgl, $tanggal_now);
            $hari = $terlambat->format("%a");
            //menghitung denda
            $denda = $hari*1000;
            if($tanggal_kembali == true){
                $data = [                
                    'id_user' => $id,
                    'id_produk' => $produk,
                    'id_mitra' => $this->input->post('id_mitra', TRUE),
                    'id_pinjam' => $pinjam,
                    'terlambat' => $hari,
                    'denda' => $denda
                ];
                $data = $this->cm->changeKembali($data['id_user']);
                var_dump($data); die;
                if ($this->pm->add($data) > 0) {
                    $this->prm->changeKembali($data['id_produk']);
                    $this->cm->changeKembali($data['id_user']);
                    $this->pem->changeStatus($data['id_pinjam']);
                    $this->response([
                        'status' => true,
                        'message' => 'Data pengembalian berhasil'
                    ], REST_Controller::HTTP_CREATED);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'Data pengembalian gagal ditambahkan'
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            } else{
                $data = [                
                    'id_user' => $id,
                    'id_produk' => $produk,
                    'id_mitra' => $this->input->post('id_mitra', TRUE),
                    'id_pinjam' => $pinjam,
                    'terlambat' => 0,
                    'denda' => 0
                ];
                if ($this->pm->add($data) > 0) {
                    $this->prm->changeKembali($data['id_produk']);
                    $this->cm->changeKembali($data['id_user']);
                    $this->pem->changeStatus($data['id_pinjam']);
                    $this->response([
                        'status' => true,
                        'message' => 'Data pengembalian berhasil'
                    ], REST_Controller::HTTP_CREATED);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'Data pengembalian gagal ditambahkan'
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            }
            
        }
    }

    public function index_put()
    {
        $id = $this->put('id_kembali');
        // var_dump($id_cust);
        // $id_user = strip_tags($this->put('id_user'));
        // $id_produk = strip_tags($this->put('id_produk'));
        // $id_mitra = strip_tags($this->put('id_mitra'));
        $tanggal_kembali = strip_tags($this->put('tanggal_kembali'));
        $status = strip_tags($this->put('status'));
        // Validate the post data
        if(!empty($id) || !empty($tanggal_kembali) || !empty($status)){
        //update user's account data
            $date = date("Y-m-d H:i:s", strtotime($tanggal_kembali));
            $tgl = date_create($date);
            $tanggal_now = date_create(date("Y-m-d H:i:s"));
            $terlambat = date_diff($tgl, $tanggal_now);
            // echo $terlambat->format('%d days');
            $hari = $terlambat->format('%d');
            // $hari = $terlambat->format("%a");
            //menghitung denda
            $denda = $hari*1000;

            $kembaliData = array();
            if(!empty($tanggal_kembali)){
                $kembaliData['tanggal_kembali'] = $tanggal_kembali;
            }
            if(!empty($status)){
                $kembaliData['status'] = $status;
            }
            $kembaliData['terlambat'] = $terlambat;
            $kembaliData['denda'] = $denda;
            
            $update = $this->pm->update($kembaliData, $id);
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

    public function index_delete()
    {
        $id = $this->delete('id_kembali');
        if (empty($id)) {
            $this->response([
                'status' => false,
                'data' => 'id null'
            ], REST_Controller::HTTP_NOT_FOUND);
        }else{
            if ($this->pm->delete($id) > 0) {
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

    public function updateData_put()
    {
        $id_kembali = $this->put('id_kembali');
        $data['kembali'] = $this->pm->getKembali($id_kembali);
        $kembaliData = array();
        // $this->pem->changeStatusHapus($data['kembali']);
        // $this->prm->changeStatus($data['kembali']['id_produk']);
        // $this->cm->changeStatus($data['kembali']['id_user']);
        var_dump($data['kembali']); die;
    }

}