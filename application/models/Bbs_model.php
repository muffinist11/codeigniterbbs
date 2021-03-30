<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bbs_model extends CI_Model
{


    public function fetch_one_row($id)
    {   
        $this->load->database();
        return $this->db->where('id', $id)
            ->select('id, view_name, message')
            ->get('message')
            ->row_array();
    }

    public function fetch_all_rows($limit=null)
    {   
        $this->load->database();
        !empty($limit)? $this->db->limit($limit): false;
        return $this->db->order_by('post_date', 'ASC')
            ->get('message')
            ->result_array();
    } 

    public function insert_row($data)
    {   
        $this->load->database();
        return $this->db->insert('message', $data);
    }

    public function update_row($id, $data)
    {
        $this->load->database();
        return $this->db->where('id', $id)
            ->update('message', $data);
    }

    public function delete_row($id)
    {
        $this->load->database();
        return $this->db->where('id', $id)
            ->delete('message');

            
    }

}