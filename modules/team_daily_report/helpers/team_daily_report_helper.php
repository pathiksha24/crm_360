<?php
defined('BASEPATH') or exit('No direct script access allowed');


function is_super_admin()
{
    return is_admin(get_staff_user_id());

    //return get_staff_user_id() == 42 || get_staff_user_id() == 1;
}

function get_second_team_leader($staff_id)
{
    $ci = &get_instance();
    $ci->db->where('staffid', $staff_id);
    $staff = $ci->db->get('staff')->row_array();
    return $staff['team_leader_2'];
}
function convertToTitleCase($string) {
    $string = str_replace('_', ' ', $string);
    $string = ucwords($string);
    return $string;
}

function is_team_leader()
{
    $staff = get_staff();
    if ($staff) {
        return $staff->is_team_leader;
    }
    return 0;
}

function get_staff_team_leader()
{
    $staff = get_staff();
    if ($staff) {
        return $staff->team_leader;
    }
    return 0;
}

function get_team_daily_dropdown($type)
{
    $data = json_decode(get_option($type), true);
    if ($data == null || $data == '') $data = [];
    $data = array_map('trim', $data);
    $data = array_unique($data);
    $data = array_values($data);

    $result = [];
    foreach ($data as $key => $value) {
        $result[$key]['id'] = $value;
        $result[$key]['value'] = $value;
    }
    return $result;
}

hooks()->add_filter('before_settings_updated', 'team_daily_report_before_settings_updated');
function team_daily_report_before_settings_updated($data)
{

    if (isset($data['client_nationalities'])){
        $client_nationalities = explode(',', $data['client_nationalities']);
        $client_nationalities = array_map('trim', $client_nationalities);
        update_option('team_daily_form_client_nationalities', json_encode($client_nationalities));
        unset($data['client_nationalities']);
    }

    if (isset($data['services'])){
        $services = explode(',', $data['services']);
        $services = array_map('trim', $services);
        update_option('team_daily_form_services', json_encode($services));
        unset($data['services']);
    }

    if (isset($data['sources'])){
        $sources = explode(',', $data['sources']);
        $sources = array_map('trim', $sources);
        update_option('team_daily_form_sources', json_encode($sources));
        unset($data['sources']);
    }

    if (isset($data['cities'])){
        $cities = explode(',', $data['cities']);
        $cities = array_map('trim', $cities);
        update_option('team_daily_form_cities', json_encode($cities));
        unset($data['cities']);
    }

    return $data;
}

function get_lead_phonenumber($lead_id)
{
    $ci = get_instance();
    $lead = $ci->db->where('id', $lead_id)->get('leads')->row();
    if ($lead) {
        return $lead->phonenumber;
    }
    return '';
}