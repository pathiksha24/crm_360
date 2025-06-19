<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Leads_admins extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->model('leads_customization_model');
    }

    public function index()
    {
        var_dump('here');
    }

}