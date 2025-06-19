<?php
class Team_daily_report_model extends App_Model
{

    public function insert_team_daily_report($data)
    {
        $this->db->insert('tblteam_daily_report', $data);
        return $this->db->insert_id();
    }

    public function update_team_daily_report($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('tblteam_daily_report', $data);
        return $id;
    }

    public function get_entry($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('tblteam_daily_report')->row_array();
    }

    public function create_widget($data)
    {
        $this->db->insert('tblteam_daily_report_widgets', $data);
        return $this->db->insert_id();
    }
}