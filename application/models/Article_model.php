<?php
if(!defined('BASEPATH')) exit('Hacking Attempt : Get Out of the system ..!');

    class Article_model extends CI_model {
        public function getArticles($id = null) {
            if($id === null) {
                return $this->db->get('articles')->result_array(); 
            } else {
                return $this->db->get_where('articles', ['id' => $id])->result_array();
            }
        }

        public function deleteArticles($data) {
        /**
         * Check Article exist with article_id and user_id
         */
            $this->db->get_where('articles', $data);
            if ($this->db->affected_rows() > 0) {
                
                // Delete Article
                $this->db->delete('articles', $data);
                if ($this->db->affected_rows() > 0) {
                    return true;
                }
                return false;
            }   
            return false;
        }

        /**
         * Add new article
         * @param: {array} Article Data
         */
        public function createArticles($data) {
            if(!array_key_exists("created_at", $data)){
                $data['created_at'] = date("Y-m-d H:i:s");
            }
            if(!array_key_exists("updated_at", $data)){
                $data['updated_at'] = date("Y-m-d H:i:s");
            }
            $this->db->insert('articles', $data);
            return $this->db->affected_rows();
        } 

        /**
         * Update new article
         * @param: {array} Article Data
         */
        public function updateArticles($data) {
            /**
             * Check Article exist with article_id and user_id
             */
            $query = $this->db->get_where('articles', [
                'user_id' => $data['user_id'],
                'id' => $data['id'],
            ]);

            if ($this->db->affected_rows() > 0) {
                
                // Update an Article
                $update_data = [
                    'title' =>  $data['title'],
                    'description' =>  $data['description'],
                    'updated_at' => date("Y-m-d H:i:s"),
                ];

                return $this->db->update('articles', $update_data, ['id' => $query->row('id')]);
            }   
            return false;
        }
    }
?>