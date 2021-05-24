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

    public function mPinjamAcc_get() {
        $id = $this->get('id_mitra');
        $mitra = $this->pem->getPinjamAktivasi($id);
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

    public function changeActive_get()
    {
        $id = $this->get('id_pinjam');
        $data = $this->pem->getPinjam($id);
        // var_dump($data[0]['id_produk']); die;
        $pinjam = $this->pem->aktivasiAcc($id);
        if($pinjam){
            $this->prm->changeStatus($data[0]['id_produk']);
            $this->cm->changeStatus($data[0]['id_user']);
            $this->response([
                'status' => true,
                'data' => 'Transaction activation successfully'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        } else{
            $this->response([
                'status' => false,
                'message' => 'failed update activation'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function mPinjamToday_get() {
        $id = $this->get('id_mitra');
        $mitra = $this->pem->getPinjamMitraHariini($id);
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
            } 
            else{
                $data = [                
                    'id_user' => $this->input->post('id_user', TRUE),
                    'id_produk' => $this->input->post('id_produk', TRUE),
                    'id_mitra' => $this->input->post('id_mitra', TRUE),
                ];
                // $data = $this->cm->changeStatus($data['id_user']);
                // var_dump($data);die;
                if ($this->pem->add($data) > 0) {
                    // $this->prm->changeStatus($data['id_produk']);
                    // $this->cm->changeStatus($data['id_user']);
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
        $date = date("Y-m-d", strtotime($get));
        $tgl = date_create($date);
        // echo $get;
        $tanggal_now = date_create(date("Y-m-d"));
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

    public function index_put()
    {
        $id = $this->put('id_pinjam');
        // var_dump($id_cust);
        // $id_user = strip_tags($this->put('id_user'));
        // $id_produk = strip_tags($this->put('id_produk'));
        // $id_mitra = strip_tags($this->put('id_mitra'));
        $tanggal_pinjam = strip_tags($this->put('tanggal_pinjam'));
        $tanggal_haruskembali = strip_tags($this->put('tanggal_haruskembali'));
        // Validate the post data
        if(!empty($id) || !empty($tanggal_pinjam) || !empty($tanggal_haruskembali)){
        //update user's account data
            $pinjamData = array();
            if(!empty($tanggal_pinjam)){
                $pinjamData['tanggal_pinjam'] = $tanggal_pinjam;
            }
            if(!empty($tanggal_haruskembali)){
                $pinjamData['tanggal_haruskembali'] = $tanggal_haruskembali;
            }
            $update = $this->pem->update($pinjamData, $id);
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

    public function getRowsMitra_get()
    {
        $id = $this->get('id_mitra');
        $pinjam = $this->pem->getCount($id);
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