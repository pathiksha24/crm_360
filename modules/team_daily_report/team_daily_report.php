<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Team Daily Report
Description: Team Daily Report
Version: 1.0.0
Requires at least: 2.3.*
Author: Bekka Codes
Author URI: https://www.fiverr.com/ossamaben
*/

define('TEAM_DAILY_REPORT_MODULE_NAME', 'team_daily_report');

$ci = & get_instance();
$ci->load->helper(TEAM_DAILY_REPORT_MODULE_NAME.'/team_daily_report');

/**
 * Register activation module hook
 */
register_activation_hook(TEAM_DAILY_REPORT_MODULE_NAME, 'team_daily_report_activation_hook');
function team_daily_report_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

register_language_files(TEAM_DAILY_REPORT_MODULE_NAME, [TEAM_DAILY_REPORT_MODULE_NAME]);

hooks()->add_action('admin_init', 'team_daily_report_init_menu_items');
hooks()->add_action('admin_init', 'team_daily_report_init_permissions');
hooks()->add_action('staff_render_managers', 'team_daily_report_staff_render_managers');
hooks()->add_filter('get_dashboard_widgets', 'team_daily_report_add_dashboard_widget');
function team_daily_report_add_dashboard_widget($widgets)
{
    //if (is_admin() || is_team_leader()){
        $widgets[] = [
            'path'      => 'team_daily_report/widget',
            'container' => 'top-12',
        ];
    //}

    return $widgets;
}



function team_daily_report_init_menu_items() {
    $CI = &get_instance();

    if (has_permission('team_daily_report','', 'team_daily_report')) {
        $CI->app_menu->add_sidebar_menu_item('team_daily_form', [
            'name' => _l('team_daily_form'),
            'icon' => 'fa fa-paste',
            'href' => admin_url('team_daily_report/team_daily_form'),
            'position' => 40,
        ]);
    }

    if (is_admin() || is_team_leader()) {
        $CI->app_menu->add_sidebar_menu_item('team_daily_report', [
            'name' => _l('team_daily_report'),
            'icon' => 'fa fa-chart-simple',
            'position' => 40,
        ]);

        $CI->app_menu->add_sidebar_children_item('team_daily_report', [
            'name' => _l('report'),
            'slug' => 'team_daily_report-report',
            'position' => 5,
            'href' => admin_url('team_daily_report/report'),
        ]);
        if (is_super_admin()) {
            $CI->app_menu->add_sidebar_children_item('team_daily_report', [
                'name' => _l('widget_creator'),
                'slug' => 'team_daily_report-widget_creator',
                'position' => 10,
                'href' => admin_url('team_daily_report/widget_creator'),
            ]);
            $CI->app_menu->add_sidebar_children_item('team_daily_report', [
                'name' => _l('settings'),
                'slug' => 'team_daily_report-team_daily_report_settings',
                'position' => 15,
                'href' => admin_url('settings?group=team_daily_report_settings'),
            ]);
        }
    }
    if (is_super_admin()) {
//        $CI->app->add_settings_section('team_daily_report_settings', [
//            'title'    => _l('team_daily_report_settings'),
//            'position' => 5,
//            'children' => [
//                [
//                    'name'     => _l('team_daily_report_settings'),
//                    'view'     => 'team_daily_report/settings',
//                    'position' => 5,
//                    'icon'     => 'fa fa-cog',
//                ],
//            ]
//        ]);
        $CI->app_tabs->add_settings_tab('team_daily_report_settings', [
            'name'     => '' . _l('team_daily_report_settings') . '',
            'view'     => 'team_daily_report/settings',
            'position' => 36,
        ]);
    }

}

function team_daily_report_init_permissions()
{
    $capabilities['capabilities'] = [
        'team_daily_report' => _l('team_daily_report'),
    ];
    register_staff_capabilities('team_daily_report', $capabilities, _l('team_daily_report'));
}


function team_daily_report_staff_render_managers($member)
{
    if (is_admin() && $member){

        $ci = &get_instance();
        $is_team_leader = $member->is_team_leader;
        $team_leader = $member->team_leader;
        $team_leader_2 = $member->team_leader_2;
        $team_leaders = $ci->staff_model->get('', ['is_team_leader' => 1]);

        $checked = '';
        if ($is_team_leader == 1) $checked = ' checked';

        echo '<div class="checkbox checkbox-primary">                     
            <input type="checkbox" name="is_team_leader" id="is_team_leader"
                '.  $checked .'>
            <label for="is_team_leader">'._l('is_team_leader') .'</label>
        </div>';

        echo render_select('team_leader', $team_leaders, ['staffid', ['firstname', 'lastname']], 'his_team_leader', $team_leader, [], [], '', '', true);
        echo render_select('team_leader_2', $team_leaders, ['staffid', ['firstname', 'lastname']], 'his_team_leader_2', $team_leader_2, [], [], '', '', true);

    }

}

hooks()->add_filter('before_update_staff_member', 'team_daily_report_before_update_staff_member');

function team_daily_report_before_update_staff_member($data)
{
    if (is_admin()) {
        if (isset($data['is_team_leader'])) {
            $data['is_team_leader'] = 1;
        } else {
            $data['is_team_leader'] = 0;
        }
    }
   return $data;
}
