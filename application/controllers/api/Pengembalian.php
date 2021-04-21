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

}