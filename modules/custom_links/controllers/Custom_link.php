<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom_link extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        // IF MODULE DISABLED THEN SHOW 404
        if(!defined('CUSTOM_LINKS_MODULE_NAME'))
            show_404();
        $this->load->model("Custom_links_model");
        $this->load->helper("security");
    }

    public function iframe($id){
        $this->Custom_links_model->filter_by_type([2]);
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
        $this->data($data);
        $this->view('client_iframe');
        $this->layout();
    }
}
