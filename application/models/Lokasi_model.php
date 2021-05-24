<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Lokasi_model extends CI_model { 
    public function __construct() {
        parent::__construct();        
        $this->lokasiTbl = 'lokasi';
    }

    /**
     * Get Data Lokasi
     */
    public function getLokasi($id = null) {
        if($id === null) {
            $this->db->select('lokasi.*, user.fullname');
            $this->db->join('user', 'lokasi.id_mitra = user.id');
            return $this->db->get('lokasi')->result_array(); 
        } else {
            $this->db->select('lokasi.*, user.fullname');
            $this->db->join('user', 'lokasi.id_mitra = user.id');
            return $this->db->get_where('lokasi', ['id_lokasi' => $id])->result_array();
        }
    }

    public function getLokMitra($id)
    {
        return $this->db->get_where('lokasi', ['id_mitra' => $id])->result_array();
    }

    /**
     * Tambah data lokasi
     */
    public function add($data)
    {
        //insert user data to users table
        $this->db->insert($this->lokasiTbl, $data);
        return $this->db->affected_rows();
    }

    /**
     * Hapus data lokasi
     */
    public function delete($id)
    {
        $this->db->delete($this->lokasiTbl, ['id_lokasi' => $id]);
        return $this->db->affected_rows();
    }

    /**
     * Update data lokasi
     */
    public function update($id, $data)
    {
        $this->db->where('id_lokasi', $id);
        $this->db->update('lokasi', $data);
        return $this->db->affected_rows();
    }

    public function getCount()
    {
        return $this->db->get('lokasi')->num_rows(); 
    }

}