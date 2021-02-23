<?php
    use Restserver\Libraries\REST_Controller;
    defined('BASEPATH') OR exit('No direct script access allowed');

    require APPPATH . 'libraries/REST_Controller.php';
    require APPPATH . 'libraries/Format.php';

    class Produk extends REST_Controller {
        public function __construct()
        {
            parent::__construct();
            $this->load->model('Produk_model', 'pm');
        }

        // Get Data
        public function index_get() {
            $id = $this->get('id_produk');
            // jika id tidak ada (tidak panggil) 
            if($id === null) {
                // maka panggil semua data
                $produk = $this->pm->getProduk();
                // tapi jika id di panggil maka hanya id tersebut yang akan muncul pada data tersebut
            } else {
                $produk = $this->pm->getProduk($id);

            }

            if($produk) {
                $this->response([
                    'status' => true,
                    'data' => $produk
                ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'id not found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            
            }

        }

        // delete data
        public function index_delete() {
            // $id = (int) $this->get('id');
            $id = $this->delete('id_produk');
            if($id === null) {
                $this->response([
                    'status' => false,
                    'message' => 'provide an id'
                ], REST_Controller::HTTP_BAD_REQUEST); 
            } else {
                if($this->pm->deleteProduk($id) > 0) {
                    // Ok
                    $this->response([
                        'status' => true,
                        'id_produk' => $id,
                        'message' => 'deleted success'
                    ], REST_Controller::HTTP_NO_CONTENT);
                } else {
                    // id not found
                    $this->response([
                        'status' => false,
                        'message' => 'id not found'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
                
                }
            }
        }

        // post data
        public function index_post() {
            $nama_produk=  $this->post('nama_produk');
            $this->load->library('ciqrcode'); //pemanggilan library QR CODE

            $config['cacheable']	= true; //boolean, the default is true
            $config['cachedir']		= './assets/'; //string, the default is application/cache/
            $config['errorlog']		= './assets/'; //string, the default is application/logs/
            $config['imagedir']		= './assets/images/'; //direktori penyimpanan qr code
            $config['quality']		= true; //boolean, the default is true
            $config['size']			= '1024'; //interger, the default is 1024
            $config['black']		= array(224,255,255); // array, default is array(255,255,255)
            $config['white']		= array(70,130,180); // array, default is array(0,0,0)
            $this->ciqrcode->initialize($config);

            $image_name=$nama_produk.'.png'; //buat name dari qr code sesuai dengan nim

            $params['data'] = $nama_produk; //data yang akan di jadikan QR CODE
            $params['level'] = 'H'; //H=High
            $params['size'] = 10;
            $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
            $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
            $data = [                
                'nama_produk' => $nama_produk,
                'qr_code' => $image_name,
                'status' => 1
            ];

            if ($this->pm->createProduk($data) > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'new Product has been created'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'failed create data'
                    
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }

        // update data
        public function index_put() {
            $id = $this->put('id_produk');
            $nama_produk=  $this->put('nama_produk');
            $this->load->library('ciqrcode'); //pemanggilan library QR CODE

            $config['cacheable']	= true; //boolean, the default is true
            $config['cachedir']		= './assets/'; //string, the default is application/cache/
            $config['errorlog']		= './assets/'; //string, the default is application/logs/
            $config['imagedir']		= './assets/images/'; //direktori penyimpanan qr code
            $config['quality']		= true; //boolean, the default is true
            $config['size']			= '1024'; //interger, the default is 1024
            $config['black']		= array(224,255,255); // array, default is array(255,255,255)
            $config['white']		= array(70,130,180); // array, default is array(0,0,0)
            $this->ciqrcode->initialize($config);

            $image_name=$nama_produk.'.png'; //buat name dari qr code sesuai dengan nim

            $params['data'] = $nama_produk; //data yang akan di jadikan QR CODE
            $params['level'] = 'H'; //H=High
            $params['size'] = 10;
            $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
            $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
            $data = [
                'nama_produk' => $nama_produk,
                'qr_code' => $image_name,
                'status' => $this->put('status')
            ];

            if ($this->pm->updateProduk($data, $id) > 0) {
                $this->response([
                    'status' => true,
                    'message' => 'update Product has been updated'
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'failed to update data'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

    }

?>