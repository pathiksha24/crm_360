<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $CI = &get_instance();
        $CI->load->dbforge();
        $fields = array(
            'show_in_iframe' => array(
                'type' => 'tinyint',
                'constraint' => "1",
                'default' => "0",
                'after' => 'require_login'
            ),
        );
        $CI->dbforge->add_column('custom_links', $fields);
    }
}