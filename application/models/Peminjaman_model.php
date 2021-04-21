<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Peminjaman_model extends CI_model { 
    public function __construct() {
        parent::__construct();        
        $this->pinjamTbl = 'peminjaman';
    }

    /**
     * Get Data Peminjaman
     */
    public function getPinjam($id = null) {
        if($id === null) {
            $this->db->select('peminjaman.*, customer.fullname as nama_peminjam, produk.nama_produk, user.fullname');
            $this->db->join('customer', 'peminjaman.id_user = customer.id_cust');
            $this->db->join('produk', 'peminjaman.id_produk = produk.id_produk');
            $this->db->join('user', 'peminjaman.id_mitra = user.id');
            return $this->db->get('peminjaman')->result_array(); 
        } else {
            $this->db->select('peminjaman.*, customer.fullname as nama_peminjam, produk.nama_produk, user.fullname');
            $this->db->join('customer', 'peminjaman.id_user = customer.id_cust');
            $this->db->join('produk', 'peminjaman.id_produk = produk.id_produk');
            $this->db->join('user', 'peminjaman.id_mitra = user.id');
            return $this->db->get_where('peminjaman', ['id_pinjam' => $id])->result_array();
        }
    }

    public function getPinjamMitra($id)
    {
        $this->db->select('peminjaman.*, customer.fullname as nama_peminjam, produk.nama_produk, user.fullname as nama_mitra');
        $this->db->join('customer', 'peminjaman.id_user = customer.id_cust');
        $this->db->join('produk', 'peminjaman.id_produk = produk.id_produk');
        $this->db->join('user', 'peminjaman.id_mitra = user.id');
        return $this->db->get_where('peminjaman', ['id_mitra' => $id])->result_array();
    }

    public function getPinjamCust($id)
    {
        $this->db->select('peminjaman.*, customer.fullname as nama_customer, produk.nama_produk, user.fullname as nama_mitra');
        $this->db->join('customer', 'peminjaman.id_user = customer.id_cust');
        $this->db->join('produk', 'peminjaman.id_produk = produk.id_produk');
        $this->db->join('user', 'peminjaman.id_mitra = user.id');
        return $this->db->get_where('peminjaman', ['id_user' => $id])->result_array();
    }

    /**
     * Tambah data peminjaman
     */
    public function add($data)
    {
        //add created and modified date if not exists
        if(!array_key_exists("tanggal_pinjam", $data)){
            $data['tanggal_pinjam'] = date("Y-m-d H:i:s");
        }
        $data['tanggal_haruskembali'] = date('Y-m-d H:i:s',strtotime("+7 days"));
        // $data['tanggal_haruskembali'] = date('Y-m-d H:i:s');
        
        //insert user data to users table
        $this->db->insert($this->pinjamTbl, $data);
        return $this->db->affected_rows();
    }

    public function getIdProduk($id)
    { 
        return $this->db->get_where($this->pinjamTbl, ['id_pinjam' => $id])->result_array();
        // return $this->db->get_where($this->pinjamTbl, ['id_pinjam' => 1, 'tanggal_haruskembali' =>  date('Y-m-d H:i:s',strtotime("+7 days", strtotime('Y-m-d H:i:s')))])->result_array();
    }

    public function getTgl($id, $id_user, $id_produk)
    { 
        $query = $this->db->get_where($this->pinjamTbl, [
            'id_pinjam' => $id,
            'id_user' => $id_user,
            'id_produk' => $id_produk,
        ])->row();
        $tanggal = $query->tanggal_haruskembali; 
        if(date("Y-m-d H:i:s") > $tanggal){
            return $tanggal;
        } else{
            return false;
        }
    }

    /**
     * Hapus data peminjaman
     */
    public function deletePeminjaman($id)
    {
        $this->db->delete($this->pinjamTbl, ['id_pinjam' => $id]);
        return $this->db->affected_rows();
    }

    public function getDetailpinjam($id, $produk)
    {
        return $this->db->get_where($this->pinjamTbl, ['id_user' => $id,'id_produk' => $produk])->result_array();
    }

    // Ubah Peminjaman Status ketika dikembalikan
    public function changeStatus($id)
    {
        $this->db->set('status', 'Sudah Kembali');
        $this->db->where('id_pinjam', $id);
        $this->db->update('peminjaman');
        return $this->db->affected_rows();
    }

    public function getCountPinjam()
    {
        return $this->db->get('peminjaman')->num_rows(); 
    }


}