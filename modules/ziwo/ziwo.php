<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Ziwo
Description: Ziwo.io Integration
Version: 2.3.0
Requires at least: 2.3.*
*/

define('ZIWO_MODULE_NAME', 'ziwo');

$CI = &get_instance();
register_language_files(ZIWO_MODULE_NAME, [ZIWO_MODULE_NAME]);
$CI->load->helper(ZIWO_MODULE_NAME . '/ziwo');
hooks()->add_action('app_admin_footer', 'ziwo_load_footer_js');
register_activation_hook(ZIWO_MODULE_NAME, 'ziwo_module_activation_hook');
function ziwo_module_activation_hook()
{
    $CI = &get_instance();
    //install required fields to database
    require_once(__DIR__ . '/install.php');
}

hooks()->add_action('staff_render_managers', 'staff_render_ziwo_agent_id');

function staff_render_ziwo_agent_id($member)
{
    $ziwo_agent_id = '';
    if ($member) $ziwo_agent_id = $member->ziwo_agent_id;
    echo render_input('ziwo_agent_id','ziwo_agent_id', $ziwo_agent_id);
}

hooks()->add_filter('leads_table_row_data', 'ziwo_leads_table_row_data', 10, 2);

function ziwo_leads_table_row_data($row, $aRow)
{$call_icon = '<a href="#" onclick="initiate_ziwo_call('.$aRow['id'].')" title="Ziwo Call">
                    '.$aRow['phonenumber'].'
                  </a>';

    $row[5] = $call_icon;


    return $row;
}

function ziwo_load_footer_js()
{
    echo '<script src="' . module_dir_url(ZIWO_MODULE_NAME, 'assets/js/ziwo.js') .'?v=' . time(). '"></script>';
}