<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Lead Manager
Description: Manage your leads by lead manager
Version: 1.0.3
Author: Zonvoir
Author URI: https://zonvoir.com/
Requires at least: 2.3.*
*/

if (!defined('MODULE_LEAD_MANAGER')) {
	define('MODULE_LEAD_MANAGER', basename(__DIR__));
}

$CI = &get_instance();
define('LEAD_MANAGER_UPLOADS_FOLDER', FCPATH . 'uploads/lead_manager' . '/');
define('LEAD_MANAGER_MAILBOX_FOLDER', FCPATH . 'uploads/lead_manager/mailbox' . '/');
hooks()->add_action('admin_init', 'lead_manager_module_init_menu_items');
hooks()->add_action('after_cron_run', 'busy_incoming_calls');
hooks()->add_action('after_cron_run', 'auto_meeting_status_update');
hooks()->add_filter('calendar_data', 'render_meeting_data',10,2);
hooks()->add_filter('customers_table_row_data', 'render_client_call_td',10,2);
hooks()->add_action('staff_member_edit_view_profile', function($staff_id){
	$CI = &get_instance();
	if($CI->input->post()){
		$post_custom_fields = $CI->input->post('custom_fields');
		$custom_fields = get_custom_fields('staff', ['slug' => 'staff_twilio_phone_number', 'active' => 1]);
		if(isset($custom_fields[0]) && !empty($custom_fields[0])){
			$c_field = $custom_fields[0];
			$phone = $post_custom_fields['staff'][$c_field['id']];
			if(empty($phone)){
				return true;
			}
			if(is_numeric($phone)){
				if(strpos($phone, ' ') !== false) {
	 				 set_alert('danger', _l('lm_twilio_number_space_error'));
	 				 redirect(admin_url('staff/member/'.$staff_id));
				}
			}else{
				set_alert('danger', _l('lm_twilio_number_numeric_error'));
	 			redirect(admin_url('staff/member/'.$staff_id));
			}
			$staffid = get_staff_by_twilio_number($post_custom_fields['staff'][$c_field['id']]);
			if($staffid && $staffid != $staff_id){
				set_alert('danger',_l('lm_twilio_number_already_alloted'));
				redirect(admin_url('staff/member/'.$staff_id));
			}
			return true;
		}

	}
});

$CI->load->helper(MODULE_LEAD_MANAGER . '/lead_manager');

if (has_permission('lead_manager', '', 'can_audio_call') && get_option('call_twilio_active') && get_staff_own_twilio_number()) {
	hooks()->add_action('app_admin_head', 'lead_manager_soft_phone');
}

/**
* Register activation module hook
*/
register_activation_hook(MODULE_LEAD_MANAGER, 'lead_manager_module_activation_hook');

function lead_manager_module_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}
/**

 * Register uninstall module hook

 */

register_uninstall_hook(MODULE_LEAD_MANAGER, 'lead_manager_module_uninstall_hook');

function lead_manager_module_uninstall_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/uninstall.php');
}

register_language_files(MODULE_LEAD_MANAGER, [MODULE_LEAD_MANAGER]);

function lead_manager_module_init_menu_items() {
	$CI = &get_instance();
	$CI->app->add_quick_actions_link([
		'name'       => _l('lead_manager'),
		'url'        => 'lead_manager',
		'permission' => 'lead_manager',
		'position'   => 52,
	]);
	if (staff_can('view', 'settings')) {
		$CI->app_tabs->add_settings_tab('lead_manager', [
			'name'     => '' . _l('lead_manager') . '',
			'view'     => 'lead_manager/admin/settings',
			'position' => 36,
		]);
	}
	if (has_permission('lead_manager', '', 'view_own') || has_permission('lead_manager', '', 'view')) {
		$CI->app_menu->add_sidebar_menu_item('lead_manager', [
			'slug'     => 'lead_manager',
			'name'     => _l('lead_manager'),
			'position' => 10,
			'icon'     => 'fa fa-sitemap'
		]);
		$CI->app_menu->add_sidebar_children_item('lead_manager', [
			'slug'     => 'lead_manager_dashboard', 
			'name'     => _l('lead_manager_dashboard'),
			'href'     => admin_url('lead_manager/dashboard'), 
			'position' => 1,
		]);
		$CI->app_menu->add_sidebar_children_item('lead_manager', [
			'slug'     => 'lead_manager_appointment',  
			'name'     => _l('lead_manager_zoom_meetings'),
			'href'     => admin_url('lead_manager/shedule_appointment'),
			'position' => 2,
		]); 

		$CI->app_menu->add_sidebar_children_item('lead_manager', [
			'slug'     => 'lead_manager_leads', 
			'name'     => _l('lead_manager_lead'),
			'href'     => admin_url('lead_manager'), 
			'position' => 3,
		]);	
		$CI->app_menu->add_sidebar_children_item('lead_manager', [
			'slug'     => 'lead_manager_chats', 
			'name'     => _l('lead_manager_lead_chats'),
			'href'     => admin_url('lead_manager/chats'), 
			'position' => 4,
		]);	
		if(has_permission('lead_manager', '', 'can_email')){
			$CI->app_menu->add_sidebar_children_item('lead_manager', [
			'slug'     => 'lead_manager_mailbox', 
			'name'     => _l('lead_manager_lead_mailbox'),
			'href'     => admin_url('lead_manager/mailbox'), 
			'position' => 5,
			]);	
		}
	}

	$CI->app_scripts->add(MODULE_LEAD_MANAGER.'-js', base_url('modules/'.MODULE_LEAD_MANAGER.'/assets/js/'.MODULE_LEAD_MANAGER.'.js?v='.time()));

	if (has_permission('lead_manager', '', 'can_audio_call') && get_option('call_twilio_active') && get_staff_own_twilio_number()) {
		$CI->app_css->add(MODULE_LEAD_MANAGER.'-soft-phone-css', base_url('modules/'.MODULE_LEAD_MANAGER.'/assets/css/soft_phone.css?v='.time()));
		$CI->app_scripts->add(MODULE_LEAD_MANAGER.'-twilio-sdk-js', base_url('modules/'.MODULE_LEAD_MANAGER.'/assets/js/twilio.min.js?v='.time()));
		$CI->app_scripts->add(MODULE_LEAD_MANAGER.'-soft-phone-js', base_url('modules/'.MODULE_LEAD_MANAGER.'/assets/js/soft_phone.js?v='.time()));	
	}

	$capabilities = [];
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view_own'),
		'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
		'delete' => _l('permission_delete'),
		'can_audio_call' => _l('lead_manger_permission_audio_call'),
		'can_video_call' => _l('lead_manger_permission_video_call'),
		'can_sms' => _l('lead_manger_permission_sms'),
		'can_email' => _l('lead_manger_permission_email')
	];

	register_staff_capabilities('lead_manager', $capabilities, _l('lead_manager'));

	load_client_lead_manager($CI->uri->uri_to_assoc(1));

	check_lead_manager_chat_menu();

}

function lead_manager_soft_phone()
{
	$CI = &get_instance();
	$softPhone = get_staff_own_twilio_number();
	$data['staffPhone'] ='<script>let staffPhone="'.$softPhone.'"</script>';
	$data['staffPhoneNumber'] = $softPhone;
	$CI->load->view('lead_manager/soft_phone',$data);
}
function render_meeting_data($data,$data_array)
{
	$add_data=[];
	$meetings = get_meetings(1);;
	if(isset($meetings) && !empty($meetings)){
		foreach ($meetings as $meeting) {
			$add_data['title'] = $meeting['meeting_agenda'];
			$add_data['_tooltip'] = $meeting['meeting_description'];
			$add_data['date'] = $meeting['meeting_date'];
			$add_data['color'] = '#4eaaf4';
			$add_data['url'] = admin_url('lead_manager/shedule_appointment');
			array_push($data, $add_data);		
		}
	}
	return $data;
}
function render_client_call_td($data, $data_array)
{
	$no_permission='';
	$allow_call='';
	$allow_sms='';
	$allow_video_call='';
	if (has_permission('lead_manager', '', 'can_audio_call') && get_option('call_twilio_active') && !empty($data_array['phonenumber'])) {
		$callerIdNumber = get_staff_own_twilio_number();
		if(isset($callerIdNumber) && !empty($callerIdNumber)){
			$allow_call = '<li><a href="javascript:void(0);" onclick="dialPhone('.$data_array["phonenumber"].','.$data_array['contact_id'].','.$callerIdNumber.');"><i class="fa fa-phone" aria-hidden="true" data-toggle="tooltip" data-title="Call"></i></a></li>'; 
		}else{
			$allow_call = '<li class="fa-stack"><a href="javascript:void(0);" title="'._l('lead_manager_twilio_number_not_assigned').'"><i class="fa fa-phone fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></li>'; 
		}
	}if (has_permission('lead_manager', '', 'can_video_call') && get_option('call_zoom_active') && !empty($data_array['email'])) {
		$allow_video_call ='<li><a href="javascript:void(0);" onclick="leadManagerClientZoom('.$data_array['contact_id'].');" data-toggle="tooltip" data-title="Zoom Meeting"><i class="fa fa-video-camera" aria-hidden="true"></i></a></li>';
	}if (has_permission('lead_manager', '', 'can_sms') && !empty($data_array['phonenumber'])) {
		$allow_sms = '<li><a href="javascript:void(0);" onclick="leadManagerClientMessage('.$data_array['contact_id'].');" data-toggle="tooltip" data-title="Message" ><i class="fa fa-comments-o" aria-hidden="true"></i></a></li>';
	}if(empty($allow_call) && empty($allow_video_call) && empty($allow_sms)){
		$no_permission = '<ul class="list-inline"><li>'._l('lead_manger_no_permission').'</li></ul>';
	}
	$data[5] = '<td>'.$data_array['phonenumber'].'</td><ul class="list-inline"><li>'.$allow_call.$allow_video_call.$allow_sms.$no_permission.'<li><a href="javascript:void(0);" onclick="leadManagerClientActivity('.$data_array['contact_id'].');" title="'._l("activity_log").'"><i class="fa fa-eye" aria-hidden="true"></i></a></li></li></ul>';
	return $data;
}
function load_client_lead_manager($uri){
	
	if(isset($uri['admin']) && !empty($uri['admin']) && $uri['admin'] == 'clients'){
		hooks()->add_action('before_js_scripts_render', function(){
			$CI = &get_instance();
			$CI->load->view('lead_manager/admin/client/manage');
		});
	}
}



hooks()->add_action("pre_deactivate_module",function($module){
	if ($module['system_name'] == MODULE_LEAD_MANAGER) {
		$CI = &get_instance();
		$CI->app_object_cache->add(MODULE_LEAD_MANAGER.'_is_verified', 0);
		delete_option(MODULE_LEAD_MANAGER."_is_verified");
	}
});
function check_lead_manager_chat_menu()
{
	$aside_menu_active = get_option('aside_menu_active');
	$menu = json_decode($aside_menu_active);
	$chats_menu_found = false;
	$mailbox_menu_found = false;
	$chat_menu_pos = 0;
	if(isset($menu) && !empty($menu) && isset($menu->lead_manager) && !empty($menu->lead_manager)){
		foreach ($menu->lead_manager->children as $child) {
			if($child->id == 'lead_manager_chats'){
				$chats_menu_found = true;
			}
			if($child->id == 'lead_manager_mailbox'){
				$mailbox_menu_found = true;
			}
			$chat_menu_pos = $child->position+5;
		}
		if(!$chats_menu_found){
			$lead_manager_chats = array('id'=>'lead_manager_chats', 'position' => $chat_menu_pos, 'disabled' => false, 'icon' => '');
			$menu->lead_manager->children->lead_manager_chats = (object)$lead_manager_chats;
			update_option('aside_menu_active', json_encode($menu));

		}
		if(!$mailbox_menu_found){
			$lead_manager_mailbox = array('id'=>'lead_manager_mailbox', 'position' => $chat_menu_pos+5, 'disabled' => false, 'icon' => '');
			$menu->lead_manager->children->lead_manager_mailbox = (object)$lead_manager_mailbox;
			update_option('aside_menu_active', json_encode($menu));
		}
	}
}
hooks()->add_action('after_cron_run', 'check_lead_manager_mailbox_email_imap');
