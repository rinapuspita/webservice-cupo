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

    public function add_post()
    {
        header("Access-Control-Allow-Origin: *");
        $_POST = $this->security->xss_clean($_POST);

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

            $data = [                
                'id_user' => $this->input->post('id_user', TRUE),
                'id_produk' => $this->input->post('id_produk', TRUE),
            ];
            if ($this->pem->add($data) > 0) {
                $this->prm->changeStatus($data['id_produk']);
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
            // echo "Success";
        }
    }

    public function addPengembalian_put()
    {
        $id = $this->put('id_pinjam');
        $data = [
            'id_user' => $this->put('id_user'),
            'id_produk' => $this->put('id_produk'),
            'status' => 'Sudah Kembali',
            'tanggal_kembali' => date("Y-m-d H:i:s"),
        ];

        if ($this->pem->add_pengembalian($data, $id) > 0) {
            $this->prm->changeKembali($data['id_produk']);
            $this->response([
                'success' => true,
                'message' => 'Data pengembalian berhasil ditambahkan'
            ], REST_Controller::HTTP_CREATED);
        } else {
            $this->response([
                'success' => false,
                'message' => 'Gagal menambahkan data pengembalian'
            ], REST_Controller::HTTP_BAD_REQUEST);
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
}