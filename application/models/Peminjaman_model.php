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
            $this->db->select('peminjaman.*, user.fullname, produk.nama_produk');
            $this->db->join('user', 'peminjaman.id_user = user.id');
            $this->db->join('produk', 'peminjaman.id_produk = produk.id_produk');
            return $this->db->get('peminjaman')->result_array(); 
        } else {
            $this->db->select('peminjaman.*, user.fullname, produk.nama_produk');
            $this->db->join('user', 'peminjaman.id_user = user.id');
            $this->db->join('produk', 'peminjaman.id_produk = produk.id_produk');
            return $this->db->get_where('peminjaman', ['id_pinjam' => $id])->result_array();
        }
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
        
        //insert user data to users table
        $this->db->insert($this->pinjamTbl, $data);
        return $this->db->affected_rows();
    }
    /**
     * Tambah data pengembalian
     */
    public function add_pengembalian($data, $id) {
        //update user data in users table
        $this->db->where('id_pinjam', $id);
        $this->db->update('peminjaman', $data);
        return $this->db->affected_rows();
    }

    /**
     * Hapus data peminjaman
     */
    public function deletePeminjaman($id)
    {
        $this->db->delete('peminjaman', ['id_pinjam' => $id]);
        return $this->db->affected_rows();
    }

}