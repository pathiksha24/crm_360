<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Staff_logs extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_logs_model');
    }
    /* Get all work_orders in case user go on index page */
    public function index()
    {
        if(!is_admin()){
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('access_denied'));
        }

        $data['title']                = _l('staff_logs');
        $this->load->view('admin/logs/manage', $data);
    }
    public function table()
    {
        $this->app->get_table_data(module_views_path(STAFF_LOGS, 'admin/logs/table'));
    }

    public function update_status($status) {

        $staffid = get_staff_user_id();

        $staff = get_staff(get_staff_user_id());

        if($staff->status != $status){
            // Update the user's status in the database
            $this->staff_logs_model->update_status($staffid, $status);
            $this->staff_logs_model->add_staff_logs($staffid,$status);
        }


        echo json_encode(['status' => 'success']);
    }

    public function clear_logs(){
        
        $this->staff_logs_model->clear_logs();
        redirect(admin_url('staff_logs'));
        
    }
}
