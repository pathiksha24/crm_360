<?php
class Foreign Report_model extends App_Model
{

    public function add_Foreign Report($data){
        $data['created_at'] = to_sql_date(date('Y-m-d H:i:s'), true);
        $data['manager_id'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'foreign_records', $data);
        $entry_id = $this->db->insert_id();
        if ($entry_id) return $entry_id;
        return false;
    }
    public function update_Foreign Report($id, $data){
        $data['manager_id'] = get_staff_user_id();
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'foreign_records', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
}