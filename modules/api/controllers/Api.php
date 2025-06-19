<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Api extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_model');

        // $this->load->library('app_modules');
        // if(!$this->app_modules->is_active('api')){
        //     access_denied("Api");
        // }
        // \modules\api\core\Apiinit::check_url('api');
    }
    public function api_management()
    {
        // \modules\api\core\Apiinit::check_url('api');
        $data['user_api'] = $this->api_model->get_user();
        $data['title'] = _l('api_management');
        $this->load->view('api_management', $data);
    }

    public function api_guide()
    { 
        fopen(APP_MODULES_PATH . 'api/views/apidoc/index.html', 'r');
    }

    /* Add new user or update existing*/
    public function user()
    {
        \modules\api\core\Apiinit::parse_module_url('api');
        if (!is_admin()) {
            access_denied('Ticket Priorities');
        }
        if ($this->input->post()) {
            // \modules\api\core\Apiinit::check_url('api');
            if (!$this->input->post('id')) {
                $id = $this->api_model->add_user($this->input->post());
               
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('user_api')));
                }
                 redirect(admin_url('api/api_management'));
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->api_model->update_user($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('user_api')));
                }
                redirect(admin_url('api/api_management'));
            }
            die;
        }
    }

    /* Delete user */
    public function delete_user($id)
    {
        \modules\api\core\Apiinit::parse_module_url('api');
        if (!is_admin()) {
            access_denied('User');
        }
        if (!$id) {
            redirect(admin_url('api/api_management'));
        }
        $response = $this->api_model->delete_user($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('user_api')));
        }
        redirect(admin_url('api/api_management'));
    }

}