<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_model { 
    public function __construct() {
        parent::__construct();        
        $this->userTbl = 'user';
    }

    /**
     * User Registration
     * -------------------
     */
    public function insert_user($data)
    {
        //add created and modified date if not exists
        if(!array_key_exists("created_at", $data)){
            $data['created_at'] = date("Y-m-d");
        }
        if(!array_key_exists("updated_at", $data)){
            $data['updated_at'] = date("Y-m-d");
        }
        
        //insert user data to users table
        $this->db->insert($this->userTbl, $data);
        return $this->db->affected_rows();
    }

    public function fetch_all_users($id = null)
    {
        if($id === null) {
            return $this->db->get($this->userTbl)->result_array(); 
        } else {
            return $this->db->get_where($this->userTbl, ['id' => $id])->result_array();
        }
    }

    public function getMitra() {
        return $this->db->get_where($this->userTbl, ['level' => 2, 'is_active' => 1])->result_array();
    }

    public function getMitraAktivasi() {
        return $this->db->get_where($this->userTbl, ['level' => 2, 'is_active' => null])->result_array();
    }

    /**
     * User Login
     * ------------------
     * @param: username or email address
     * @param: password
     */
    public function user_login($username, $password)
    {
        $this->db->where('email', $username);
        $this->db->or_where('username', $username);
        $q = $this->db->get_where($this->userTbl, ['is_active' => 1]);
        if($q->num_rows()){
            $user_pass = $q->row('password');
            if(md5($password) === $user_pass){
                return $q->row();
            }
            return FALSE;
        } else{
            return FALSE;
        }
    }

    /**
     * User Update
     * -------------------
     */
    public function user_update($data, $id) {
        // add modified date if not exists
        if(!array_key_exists("updated_at", $data)){
            $data['updated_at'] = date("Y-m-d");
        }
        //update user data in users table
        $this->db->update($this->userTbl, $data, ['id' => $id]);
        return $this->db->affected_rows();
    }

    /**
     * Delete user
     */
    public function user_delete($id) {
        $this->db->delete($this->userTbl, ['id' => $id]);
        return $this->db->affected_rows();
    }

    // Ubah Product Status ketika dipinjam
    public function changeStatus($id)
    {
        $this->db->set('is_active', 1);
        $this->db->where('id', $id);
        $this->db->update($this->userTbl);
        return $this->db->affected_rows();
    }
      
}
