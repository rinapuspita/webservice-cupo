<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pengembalian_model extends CI_model { 
    public function __construct() {
        parent::__construct();        
        $this->kembaliTbl = 'pengembalian';
    }

    /**
     * Get Data Pengembalian
     */
    public function getKembali($id = null) {
        if($id === null) {
            $this->db->select('pengembalian.*, customer.fullname, produk.nama_produk, user.fullname, peminjaman.id_pinjam');
            $this->db->join('customer', 'pengembalian.id_user = customer.id_cust');
            $this->db->join('produk', 'pengembalian.id_produk = produk.id_produk');
            $this->db->join('user', 'pengembalian.id_mitra = user.id');
            $this->db->join('peminjaman', 'pengembalian.id_pinjam = peminjaman.id_pinjam');
            return $this->db->get('pengembalian')->result_array(); 
        } else {
            $this->db->select('pengembalian.*, customer.fullname, produk.nama_produk, user.fullname, peminjaman.id_pinjam');
            $this->db->join('customer', 'pengembalian.id_user = customer.id_cust');
            $this->db->join('produk', 'pengembalian.id_produk = produk.id_produk');
            $this->db->join('user', 'pengembalian.id_mitra = user.id');
            $this->db->join('peminjaman', 'pengembalian.id_pinjam = peminjaman.id_pinjam');
            return $this->db->get_where('pengembalian', ['id_kembali' => $id])->result_array();
        }
    }

    /**
     * Tambah data pengembalian
     */
    public function add($data)
    {
        $data['tanggal_kembali'] = date("Y-m-d H:i:s");
        
        //insert user data to users table
        $this->db->insert($this->kembaliTbl, $data);
        return $this->db->affected_rows();
    }

    /**
     * Hapus data pengembalian
     */
    public function delete($id)
    {
        $this->db->delete($this->kembaliTbl, ['id_kembali' => $id]);
        return $this->db->affected_rows();
    }


}