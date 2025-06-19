<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Zoom_link_send_to_staff extends Lead_manager_mail_template
{
    protected $for = 'customer';

    protected $lead_id;
    protected $meeting_data;

    protected $user_email;
    protected $staff_email;

    public $slug = 'lead-manager-send-to-staff';

    public $rel_type = 'lead_manager';

    public function __construct($lead)
    {
        parent::__construct();
        $this->lead_id     = $lead->leadid;
        $this->user_email = $lead->email;
        $this->staff_email = $lead->staff_email;
        $this->meeting_data = $lead;
    }

    public function build()
    {
        $this->to($this->staff_email)
        ->set_rel_id($this->lead_id)
        ->set_lead_manager_merge_fields('lead_manager_merge_fields', $this->lead_id,$this->meeting_data);
    }
}
