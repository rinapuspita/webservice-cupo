<?php 
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Peminjaman extends REST_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Peminjaman_model', 'pem');
        $this->load->model('Produk_model', 'prm');
        $this->load->model('Customer_model', 'cm');
    }

    // Get Data
    public function getAll_get() {
        $id = $this->get('id_pinjam');
        // jika id tidak ada (tidak panggil) 
        if($id === null) {
            // maka panggil semua data
            $pinjam = $this->pem->getPinjam();
            // tapi jika id di panggil maka hanya id tersebut yang akan muncul pada data tersebut
        } else {
            $pinjam = $this->pem->getPinjam($id);
        }

        if($pinjam) {
            $this->response([
                'status' => true,
                'data' => $pinjam
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else {
            $this->response([
                'status' => false,
                'message' => 'id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        
        }
    }

    public function mPinjam_get() {
        $id = $this->get('id_mitra');
        $mitra = $this->pem->getPinjamMitra($id);
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

    public function cPinjam_get() {
        $id = $this->get('id_user');
        $cust = $this->pem->getPinjamCust($id);
        if($cust){
            $this->response([
                'status' => true,
                'data' => $cust
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
            $output = $this->cm->getLimit($id);
            if((!empty($output) AND $output!= FALSE)){
                $this->response([
                    'status' => false,
                    'message' => 'Limit pinjam sudah habis'
                ], REST_Controller::HTTP_NOT_FOUND);
            } else{
                $data = [                
                    'id_user' => $this->input->post('id_user', TRUE),
                    'id_produk' => $this->input->post('id_produk', TRUE),
                    'id_mitra' => $this->input->post('id_mitra', TRUE),
                ];
                if ($this->pem->add($data) > 0) {
                    $this->prm->changeStatus($data['id_produk']);
                    $this->cm->changeStatus($data['id_user']);
                    $this->response([
                        'status' => true,
                        'message' => 'Data peminjaman berhasil ditambahkan'
                    ], REST_Controller::HTTP_CREATED);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'Data peminjaman gagal ditambahkan'
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            }
            // echo "Success";
        }
    }

    public function getId_post()
    {
        $data = $this->input->post('nama_produk', TRUE);
        $get = $this->prm->getId($data);
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

    public function getDetail_post()
    {
        $id = $this->input->post('id_user', TRUE);
        $produk = $this->input->post('id_produk', TRUE);
        $get = $this->pem->getDetailpinjam($id, $produk);
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

    public function getTglpinjam_post()
    {
        $id = $this->input->post('id_user', TRUE);
        $produk = $this->input->post('id_produk', TRUE);
        $pinjam = $this->input->post('id_pinjam', TRUE);
        $get = $this->pem->getTgl($pinjam, $id, $produk);
        $date = date("Y-m-d H:i:s", strtotime($get));
        $tgl = date_create($date);
        // echo $get;
        $tanggal_now = date_create(date("Y-m-d H:i:s"));
        // print $tanggal_now;
        $terlambat = date_diff($tgl, $tanggal_now);
        $hari = $terlambat->format("%a");
        if($get == true){
            $this->response([
                'status' => true,
                'data' => $get,
                'message' => $hari
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else{
            $this->response([
                'status' => false,
                'message' => 'Gagal memindai data'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function change_post()
    {
        $data = [                
            'id_user' => $this->input->post('id_user', TRUE),
        ];
        $this->cm->changeKembali($data['id_user']);
        $this->response([
            'status' => true,
            'message' => 'Limit berhasil diubah'
        ], REST_Controller::HTTP_NOT_FOUND);
    }

    public function limit_get()
    {
        $output = $this->cm->getLimit(2);
        if((!empty($output) AND $output!= FALSE)){
            $this->response([
                'status' => false,
                'message' => 'Return true'
            ], REST_Controller::HTTP_OK);
        } else{
            $this->response([
                'status' => true,
                'message' => 'Limit pinjam sudah habis'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function index_delete()
    {
        $id = $this->delete('id_pinjam');
        if (empty($id)) {
            $this->response([
                'status' => false,
                'data' => 'id null'
            ], REST_Controller::HTTP_NOT_FOUND);
        }else{
            if ($this->pem->deletePeminjaman($id) > 0) {
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
            $pinjam = $this->pem->getCountPinjam();
            if($pinjam){
                $this->response([
                    'status' => true,
                    'data' => $pinjam
                ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
            } else{
                $this->response([
                    'status' => false,
                    'message' => 'id not found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }
    
}