<?php

class Foreign Report extends AdminController
{


    private $Foreign Report_permission;

    public function __construct()
    {
        parent::__construct();
        $this->Foreign Report_permission = has_permission('Foreign Report','','Foreign Report');
        $this->load->model('Foreign Report_model');
    }

    public function index($manager_id= 0, $date = 0)
    {
        if (!$this->Foreign Report_permission) access_denied('Foreign Report');
        if ($this->input->is_ajax_request()) {

            $this->app->get_table_data(module_views_path('Foreign Report', 'table'),[
                'date' => $date,
                'manager_id' => $manager_id,
            ]);
        }
        $data['title'] = _l('Foreign Report');
        $users = $this->staff_model->get();
        $managers = [];
        foreach ($users as $manager) {
            if (has_permission('Foreign Report',$manager['staffid'],'Foreign Report')){
                $managers[] = $manager;
            }
        }
        $data['managers'] = $managers;
        $this->load->view('Foreign Report/index', $data);
    }

    public function crud(){
        if (!$this->Foreign Report_permission) access_denied('Foreign Report');
        $alert_type = 'danger';
        $message = '';
        $success = false;
        if ($this->input->post()){
            $data = $this->input->post();
            $id = $data['id'];
            if ($id !== ''){
                unset($data['id']);
                $success = $this->Foreign Report_model->update_Foreign Report($id, $data);
                if ($success){
                    $success = true;
                    $alert_type = 'success';
                    $message = _l('updated_successfully', _l('record'));
                }
            }else{
                $success = $this->Foreign Report_model->add_Foreign Report($data);
                if ($success){
                    $success = true;
                    $alert_type = 'success';
                    $message = _l('added_successfully', _l('record'));
                }
            }
        }
        echo json_encode(
            [
                'success' => $success,
                'alert_type' => $alert_type,
                'message' => $message,
            ]
        );
        die();
    }
    public function delete_record($id){
        if (!$this->Foreign Report_permission) access_denied('Foreign Report');
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'foreign_records');
        echo json_encode(
            [
                'success' => true,
                'alert_type' => 'success',
                'message' => _l('deleted_successfully', _l('record')),
            ]
        );
        die();
    }
}