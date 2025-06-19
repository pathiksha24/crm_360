<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
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
            'badge' => array(
                'type' => 'VARCHAR',
                'constraint' => 63,
                'default' => NULL,
                'after' => 'main_setup'
            ),
            'badge_color' => array(
                'type' => 'VARCHAR',
                'constraint' => 63,
                'default' => NULL,
                'after' => 'badge'
            ),
            'require_login' => array(
                'type' => 'INT',
                'constraint' => 1,
                'default' => "0",
                'after' => 'badge_color'
            )
        );
        $CI->dbforge->add_column('custom_links', $fields);
    }
}