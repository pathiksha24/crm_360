<?php

defined('BASEPATH') or exit('No direct script access allowed');

function render_leads_services_select($services, $selected = '', $lang_key = '', $name = 'service', $select_attrs = [], $exclude_default = false)
{
    if (is_admin()/* || get_option('staff_members_create_inline_lead_status') == '1'*/) {
        return render_select_with_input_group($name, $services, ['id', 'name'], $lang_key, $selected, '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="new_lead_service_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a></div>', $select_attrs);
    }

    return render_select($name, $services, ['id', 'name'], $lang_key, $selected, $select_attrs);
}

function render_leads_languages_select($services, $selected = '', $lang_key = '', $name = 'language', $select_attrs = [], $exclude_default = false)
{
    if (is_admin()/* || get_option('staff_members_create_inline_lead_status') == '1'*/) {
        return render_select_with_input_group($name, $services, ['id', 'name'], $lang_key, $selected, '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="new_lead_language_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a></div>', $select_attrs);
    }

    return render_select($name, $services, ['id', 'name'], $lang_key, $selected, $select_attrs);
}

hooks()->add_action('lead_status_changed', 'lead_custom_status_changed');
function lead_custom_status_changed($date){
    if ($date['new_status'] == 'Closings'){
        $ci = &get_instance();
        $lead_id = $date['lead_id'];
        $ci->db->where('id', $lead_id);
        $ci->db->update(db_prefix() . 'leads', [
            'dateclosed' => date('Y-m-d H:i:s'),
        ]);
    }
}

function has_access_to_unassigned_leads($staff_id = '')
{
    if ($staff_id == '') $staff_id = get_staff_user_id();
     // Give access to staff ID 1833 explicitly(hihab access to not assigned staff list->leads)
         if (in_array($staff_id, [183, 59, 163, 216, 34, 214, 72, 20, 141, 194,55])) {
        return true;
         }
    $leads_unassigned_admins = json_decode(get_option('leads_unassigned_admins'));

    return in_array($staff_id, $leads_unassigned_admins);
}

hooks()->add_filter('before_settings_updated', 'custom_leads_before_settings_updated');

function custom_leads_before_settings_updated($data)
{
    if (isset($data['leads_unassigned_admins'])){
        $leads_unassigned_admins = json_encode($data['leads_unassigned_admins']);
        update_option('leads_unassigned_admins', $leads_unassigned_admins);
        unset($data['leads_unassigned_admins']);
    }

    return $data;
}