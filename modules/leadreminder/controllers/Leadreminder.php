<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leadreminder extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leadreminder_model');
    }

    /* Get all work_orders in case user go on index page */
    public function index() {}
    public function update($id)
    {

        $success = $this->leadreminder_model->update($id);
        $message = '';
        if ($success) {
            $message = _l('updated_successfully', _l('leadreminder'));
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function fetch(){
        $reminder = $this->leadreminder_model->get_leadreminders();
        echo json_encode([
            'reminder' => $reminder
        ]);
    }
}
