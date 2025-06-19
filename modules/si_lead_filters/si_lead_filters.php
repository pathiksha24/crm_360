<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: SI Lead Filters
Description: Module will Generate Filters for Lead and save filters as Templates for future use.
Author: Sejal Infotech
Version: 1.1.2
Requires at least: 2.3.*
Author URI: http://www.sejalinfotech.com
*/

define('SI_LEAD_FILTERS_MODULE_NAME', 'si_lead_filters');

$CI = &get_instance();

hooks()->add_action('admin_init', 'si_lead_filters_init_menu_items');
hooks()->add_action('admin_init', 'si_lead_filters_permissions');

/**
* Load the module helper
*/
$CI->load->helper(SI_LEAD_FILTERS_MODULE_NAME . '/si_lead_filters');

/**
* Load the module Model
*/
$CI->load->model(SI_LEAD_FILTERS_MODULE_NAME . '/si_lead_filter_model');

/**
* Register activation module hook
*/
register_activation_hook(SI_LEAD_FILTERS_MODULE_NAME, 'si_lead_filters_activation_hook');

function si_lead_filters_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(SI_LEAD_FILTERS_MODULE_NAME, [SI_LEAD_FILTERS_MODULE_NAME]);

/**
 * Init menu setup module menu items in setup in admin_init hook
 * @return null
 */
function si_lead_filters_init_menu_items()
{
	/**
	* If the logged in user is administrator, add custom Reports in Sidebar, if want to add menu in Setup then Write Setup instead of sidebar in menu ceation
	*/
	if (is_admin() || has_permission('si_lead_filters', '', 'view')) {
		$CI = &get_instance();
		$CI->app_menu->add_sidebar_menu_item('lead-filters', [
			'collapse'	=> true,
			'icon'		=> 'fa fa-filter',
			'name'		=> _l('si_lead_filters_menu'),
			'position'	=> 35,
		]);
		$CI->app_menu->add_sidebar_children_item('lead-filters', [
			'slug'		=> 'si-lead-filter-options',
			'name'		=> _l('si_lf_submenu_lead_filters'),
			'href'		=> admin_url('si_lead_filters/leads_filter'),
			'position'	=> 5,
		]);
		$CI->app_menu->add_sidebar_children_item('lead-filters', [
			'slug'		=> 'si-lead-tmplate-options',
			'name'		=> _l('si_lf_submenu_filter_templates'),
			'href'		=> admin_url('si_lead_filters/list_filters'),
			'position'	=> 10,
		]);
	}
}
function si_lead_filters_permissions()
{
	$capabilities = [];
	$capabilities['capabilities'] = [
		'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
	];
	register_staff_capabilities('si_lead_filters', $capabilities, _l('si_lead_filters'));
}

hooks()->add_action('before_cron_run', 'fix_old_leads_v2');

function fix_old_leads_v2()
{
    $start_time = microtime(true); // Record the start time

    $ci = &get_instance();

    $ci->db->select('leadid, MIN(tbllead_activity_log.date) as earliest_date');
    $ci->db->join('tblleads', 'tblleads.id = tbllead_activity_log.leadid', 'inner');
    $ci->db->where('tbllead_activity_log.description', 'not_lead_activity_assigned_to');
    $ci->db->where('tblleads.dateassigned_fixed', 0);
    $ci->db->group_by('leadid');
    $lead_activity_logs = $ci->db->get('tbllead_activity_log')->result();
//    // Process each distinct lead activity log
//    echo '<pre>';
//    var_dump(count($lead_activity_logs));
//    echo '</pre>';
//    die();
    foreach ($lead_activity_logs as $log) {
//        echo '<pre>';
//        print_r($log);
//        echo '</pre>';
//        die();
        // Update the lead with the earliest activity log date
        $ci->db->where('id', $log->leadid);
        $ci->db->update('tblleads', [
            'dateassigned' => $log->earliest_date,
            'dateassigned_fixed' => 2
        ]);
    }
    $end_time = microtime(true); // Record the end time

    // Calculate the time taken
    $time_taken = $end_time - $start_time;

    // Log or print the time taken
//    echo 'Time taken: ' . $time_taken . ' seconds';
//    die();
}

function fix_old_leads()
{
    $ci = &get_instance();
    $ci->db->where('dateassigned_fixed', 0);
    $ci->db->order_by('dateadded', 'asc');
    $ci->db->limit(1000);
    $leads = $ci->db->select('id')->get('tblleads')->result();
    //88275
    //echo count($leads);die();
    foreach ($leads as $lead) {
        $ci->db->where('leadid', $lead->id);
        $ci->db->where('description', 'not_lead_activity_assigned_to');
        $ci->db->order_by('date', 'asc');
        $lead_activity_log = $ci->db->get(db_prefix() . 'lead_activity_log')->row();

        if ($lead_activity_log) {
            $ci->db->where('id', $lead->id);
            $ci->db->update('tblleads', ['dateassigned' => $lead_activity_log->date, 'dateassigned_fixed' => 1]);
        }

        $ci->db->where('id', $lead->id);
        $ci->db->update('tblleads', ['dateassigned_fixed' => 1]);
    }
}
