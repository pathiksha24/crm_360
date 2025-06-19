<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Elite_download_modules extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('language');

        if (!is_admin() || !defined('ELITE_DM_MODULE_NAME')) {
            access_denied('elite_download_modules');
        }
    }

    public function download_module($module) 
    {
        if(get_option(ELITE_DM_PREFIX.'_module') == 'enable') {
            $this->load->library('zip');
            $folderPath = './modules/'.$module;
            $this->zip->read_dir($folderPath, false);
            $this->zip->download($module.'.zip');
        }
    }
}
