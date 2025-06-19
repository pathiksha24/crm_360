<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leadreminder_model extends App_Model
{
    private $statuses;


    public function __construct()
    {
        parent::__construct();
    }

    public function get_leadreminders()
    {
        $staff_id = get_staff_user_id();

        $this->db->where('rel_type', 'lead');
        $this->db->where('staff', $staff_id);
        $this->db->where('popped_up', 0);
        $this->db->limit(1);
        $reminder =  $this->db->get(db_prefix() . 'reminders')->row();

        return $reminder;
    }
    public function update($id)
    {
        $this->db->where('id', $id)->update(db_prefix() . 'reminders', ['popped_up' => 1]);
        return true;
    }
}
