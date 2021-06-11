<?php
if(!defined('BASEPATH')) exit('Hacking Attempt : Get Out of the system ..!');

    class Produk_model extends CI_model {
        public function getProduk($id = null) {
            if($id === null) {
                return $this->db->get('produk')->result_array(); 
            } else {
                return $this->db->get_where('produk', ['id_produk' => $id])->result_array();
            }
        }

        public function getMitraprod($id) {
            return $this->db->get_where('produk', ['id_mitra' => $id, 'status' => 1])->result_array();
        }

        public function getMitraStok($id) {
            return $this->db->get_where('produk', ['id_mitra' => $id])->num_rows();
        }

        public function getcupKotor() {
            return $this->db->get_where('produk', [
                'status' =>2
                ])->result_array();
        }

        public function deleteProduk($id) {
            $this->db->delete('produk', ['id_produk' => $id]);
            return $this->db->affected_rows();
        }

        public function createProduk($data) {
            $this->db->insert('produk', $data);
            return $this->db->affected_rows();
        } 

        public function updateProduk($data, $id) {
            $this->db->update('produk', $data, ['id_produk' => $id]);
            return $this->db->affected_rows();
        }

        // Ubah Product Status ketika dipinjam
        public function changeStatus($id)
        {
            $this->db->set('status', 0);
            $this->db->where('id_produk', $id);
            $this->db->update('produk');
            return $this->db->affected_rows();
        }

        // Ubah Product Status ketika dikembalikan
        public function changeKembali($id)
        {
            $this->db->set('status', 2);
            $this->db->where('id_produk', $id);
            $this->db->update('produk');
            return $this->db->affected_rows();
        }

        // Ubah Product Status ketika sudah dicuci
        public function changeStatusLagi($id)
        {
            $this->db->set('status', 1);
            $this->db->where('id_produk', $id);
            $this->db->update('produk');
            return $this->db->affected_rows();
        }

        // Ubah Product Lokasi 
        public function changeLokasi($lokasi, $id)
        {
            $this->db->set('id_mitra', $lokasi);
            $this->db->where('id_produk', $id);
            $this->db->update('produk');
            return $this->db->affected_rows();
        }

        // Get data product when scan id
        public function getId($id)
        {
            return $this->db->get_where('produk', ['nama_produk' => $id])->result_array();
        }

        public function getNotif()
        {
            return $this->db->get_where('produk', ['id_mitra' => 2])->result_array();
        }

        public function getCountProduk()
        {
            return $this->db->get('produk')->num_rows(); 
        }

        public function getCount($id)
        {
            return $this->db->get_where('produk', ['id_mitra' => $id])->num_rows(); 
        }
    }
?>