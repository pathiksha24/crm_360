<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Staff_logs_model extends App_Model
{

    public function update_status($staffid, $status) {

        $data = [
            'current_status' => $status,
            'last_activity' => date('Y-m-d H:i:s')
        ];

        $this->db->where('staffid', $staffid);
        $this->db->update(db_prefix().'staff', $data);

    }

    public function add_staff_logs($staffid,$status){

        $this->db->insert(db_prefix().'staff_logs',[
            'description'   => '<div class="status-indicator">
                <div class="status-dot '.($status == 1 ? 'status-online' : 'status-idle' ).'"></div>
                <span>'.get_staff_full_name($staffid).', get '.($status == 1 ? _l('staff_logs_online') :  _l('staff_logs_offline') ).'</span>
            </div>',
            'date'          => date('Y-m-d H:i:s'),
            'staffid'       => $staffid,
        ]);

    }

    public function clear_logs(){
        $this->db->where('id IS NOT NULL');
        return $this->db->delete(db_prefix().'staff_logs');
    }
}
