<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom_links extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // IF MODULE DISABLED THEN SHOW 404
        if(!defined('CUSTOM_LINKS_MODULE_NAME'))
            show_404();
        $this->load->model("Custom_links_model");
        $this->load->helper("security");
        $this->load->library("form_validation");
    }

    public function index(){
        $this->link();
    }

    public function link($id = ''){
        if (! (has_permission('custom_links', '', 'view') || has_permission('custom_links', '', 'view_own') || has_permission('custom_links', '', 'create') || has_permission('custom_links', '', 'edit'))) {
            access_denied('custom_links');
        }
        if(!empty($id)){
            if (! has_permission('custom_links', '', 'edit')) {
                access_denied('custom_links');
            }
            $link = $this->Custom_links_model->get_detail($id);
            if(!$link)
                show_404();

            $data['link'] = $link;
        }
        $staff_id = get_staff_user_id();

        if(!empty($this->input->post())) {
            $this->form_validation->set_rules("main_setup", _l('mcl_select_menu'), 'trim|required');
            $this->form_validation->set_rules("title", _l('mcl_link_title'), 'trim|required');
            $this->form_validation->set_rules("href", _l('mcl_link'), 'trim|required');
            if (!$this->form_validation->run()) {
                set_alert("danger", _l("mcl_validation_error"));
                redirect(admin_url("custom_links"));
            }
            $insert['main_setup'] = intval($this->input->post('main_setup'));
            $insert['parent_id'] = empty($this->input->post('parent_id')) ? "" : $this->input->post('parent_id');
            $insert['title'] = $this->input->post('title');
            $insert['href'] = $this->input->post('href');
            $insert['position'] = $this->input->post('position');
            $insert['icon'] = $this->input->post('icon');
            $insert['badge'] = $this->input->post('badge');
            $insert['badge_color'] = $this->input->post('badge_color');
            $insert['external_internal'] = $this->input->post('external_internal') > 2 ? 0 : $this->input->post('external_internal');
            $insert['http_protocol'] = $this->input->post('http_protocol') == 1 ? 1 : 0;
            $insert['open_in_blank'] = $this->input->post('open_in_blank') == 1 ? 1 : 0;
            $insert['require_login'] = $this->input->post('require_login') == 1 ? 1 : 0;
            $insert['show_in_iframe'] = $this->input->post('show_in_iframe') == 1 ? 1 : 0;
            $users = $this->input->post('users');
            $insert['users'] = NULL;
            if(is_array($users) && count($users) > 0){
                $insert['users'] = implode(",", $users);
            }
            if(isset($link)){
                $inserted = $this->Custom_links_model->update($insert, $id);
                $message = _l("mcl_link_updated_msg");
            }
            else{
                if(!has_permission('custom_links', '', 'create')){
                    access_denied('custom_links');
                }
                $insert['added_at'] = date("Y-m-d H:i:s");
                $insert['added_by'] = $staff_id;
                $inserted = $this->Custom_links_model->insert($insert);
                $update['unique_id'] = custom_links_slug($this->input->post('title')).$inserted;
                $inserted = $this->Custom_links_model->update($update, $inserted);
                $message = _l("mcl_link_added_msg");
            }
            if ($inserted) {
                set_alert("success", $message);
                if(isset($link))
                    redirect(admin_url("custom_links/link/".$id));
                else
                    redirect(admin_url("custom_links"));
            } else {
                set_alert("warning", _l("mcl_link_failed_msg"));
            }
        }

        $data['main_menu_items'] = $this->app_menu->get_sidebar_menu_items();

        $data['setup_menu_items'] = $this->app_menu->get_setup_menu_items();

        $this->load->model("Staff_model");
        if(has_permission('custom_links', '', 'view_own')){
            $this->Custom_links_model->filter_added_by($staff_id);
        }
        $this->Custom_links_model->filter_by_type(["0"]);
        $this->Custom_links_model->sort_by(CUSTOM_LINKS_TABLE_NAME.".parent_id");
        $this->Custom_links_model->sort_by(CUSTOM_LINKS_TABLE_NAME.".position");
        $main_links = $this->Custom_links_model->all_rows();
        foreach ($main_links as $link){
            if(!empty($link['parent_id']) && isset($data['main_links'][$link['parent_id']])){
                $data['main_links'][$link['parent_id']]['children'][] = $link;
            }
            else{
                $data['main_links'][$link['unique_id']] = $link;
            }
        }

        if(has_permission('custom_links', '', 'view_own')){
            $this->Custom_links_model->filter_added_by($staff_id);
        }
        $this->Custom_links_model->filter_by_type(["1"]);
        $this->Custom_links_model->sort_by(CUSTOM_LINKS_TABLE_NAME.".parent_id");
        $this->Custom_links_model->sort_by(CUSTOM_LINKS_TABLE_NAME.".position");
        $setup_links = $this->Custom_links_model->all_rows();
        foreach ($setup_links as $link){
            if(!empty($link['parent_id']) && isset($data['setup_links'][$link['parent_id']])){
                $data['setup_links'][$link['parent_id']]['children'][] = $link;
            }
            else{
                $data['setup_links'][$link['unique_id']] = $link;
            }
        }

        if(has_permission('custom_links', '', 'view_own')){
            $this->Custom_links_model->filter_added_by($staff_id);
        }
        $this->Custom_links_model->filter_by_type(["2"]);
        $this->Custom_links_model->sort_by(CUSTOM_LINKS_TABLE_NAME.".parent_id");
        $this->Custom_links_model->sort_by(CUSTOM_LINKS_TABLE_NAME.".position");
        $data['client_links'] = $this->Custom_links_model->all_rows();

        $data['staff_ajax'] = false;
        if(total_rows(db_prefix().'staff', ['active' => "1"]) > 1){
            $data['staff_ajax'] = true;
            $data['staff'] = [];
            if(isset($link) && !empty($link['users'])){
                $users = explode(",", $link['users']);
                $data['staff'] = $this->db->where_in("staffid", $users)->get(db_prefix()."staff")->result_array();
            }
        }
        else{
            $data['staff'] = $this->Staff_model->get();
        }

        $data['title'] = _l('mcl_custom_links');
        $this->load->view('index', $data);
    }

    public function delete($id){
        if (! has_permission('custom_links', '', 'delete')) {
            access_denied('custom_links');
        }
        $link = $this->Custom_links_model->get_detail($id);
        if(!$link)
            show_404();

        $deleted = $this->Custom_links_model->delete($id);
        if ($deleted) {
            set_alert("success", _l('mcl_link_deleted_msg'));
        } else {
            set_alert("warning", _l("mcl_link_delete_failed_msg"));
        }
        redirect(admin_url("custom_links"));
    }

    public function iframe($id){
        $this->Custom_links_model->filter_by_type([0, 1]);
        $link = $this->Custom_links_model->get_detail($id);
        if(!$link){
            show_404();
        }

        if($link['external_internal'] == "0"){
            $href = base_url($link['href']);
        }
        else{
            if($link['http_protocol'] == "0"){
                $href = 'http://'.$link['href'];
            }
            else{
                $href = 'https://'.$link['href'];
            }
        }

        $data['href'] = $href;
        $data['link'] = $link;
        $data['title'] = _l('mcl_custom_links')." - ".$link['title'];
        $this->load->view('iframe', $data);
    }
}
