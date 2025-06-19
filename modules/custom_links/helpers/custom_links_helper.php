<?php
defined('BASEPATH') or exit('No direct script access allowed');

function add_custom_links_scripts($group){
    if($group == "admin") {
        $CI = &get_instance();
        $CI->app_scripts->add('custom-links-js', module_dir_url(CUSTOM_LINKS_MODULE_NAME, 'assets/custom-links.js?v='.CUSTOM_LINKS_MODULE_VERSION));
        $CI->app_scripts->add('fontawesome-iconpicker-js', module_dir_url(CUSTOM_LINKS_MODULE_NAME, 'assets/font-awesome-icon-picker/js/fontawesome-iconpicker.js'));
    }
}

function add_custom_links_css($group){
    if($group == "admin") {
        $CI = &get_instance();
        $CI->app_css->add('custom-links-css', module_dir_url(CUSTOM_LINKS_MODULE_NAME, 'assets/custom-links.css?v='.CUSTOM_LINKS_MODULE_VERSION));
        $CI->app_css->add('fontawesome-iconpicker-css', module_dir_url(CUSTOM_LINKS_MODULE_NAME, 'assets/font-awesome-icon-picker/css/fontawesome-iconpicker.min.css'));
    }
}

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
function add_setup_menu_custom_links_link(){
    $CI = &get_instance();
    if (has_permission('custom_links', '', 'view') || has_permission('custom_links', '', 'add') || has_permission('custom_links', '', 'edit')) {
        $CI->app_menu->add_setup_menu_item('custom_links', [
            'href'     => admin_url('custom_links'),
            'name'     => _l('mcl_custom_links'),
            'position' => 300,
        ]);
    }

    $CI->load->model("Custom_links/Custom_links_model");
    $CI->Custom_links_model->filter_by_type([0,1]);
    $links = $CI->Custom_links_model->all_rows();
    foreach($links as $link){
        if(!empty($link['users'])){
            $users = explode(",", $link['users']);
            if(!in_array(get_staff_user_id(), $users))
                continue;
        }

        $menu_item = create_menu_item_array($link);

        if($link['main_setup'] == "0"){
            if(empty($link['parent_id']))
                $CI->app_menu->add_sidebar_menu_item($link['unique_id'], $menu_item);
            else
                $CI->app_menu->add_sidebar_children_item($link['parent_id'], $menu_item);
        }
        else{
            if(empty($link['parent_id']))
                $CI->app_menu->add_setup_menu_item($link['unique_id'], $menu_item);
            else
                $CI->app_menu->add_setup_children_item($link['parent_id'], $menu_item);
        }
    }
}


/**
 * Init module menu items in client in clients_init hook
 * @return null
 */
function add_client_menu_custom_links()
{
    $CI = &get_instance();
    $CI->load->model("Custom_links/Custom_links_model");
    $CI->Custom_links_model->filter_by_type([2]);
    $links = $CI->Custom_links_model->all_rows();
    foreach($links as $link){
        if($link['require_login'] == "1" && !is_client_logged_in()) {
            continue;
        }
        $menu_item = create_menu_item_array($link);

        add_theme_menu_item($link['unique_id'], $menu_item);
    }
}

/**
 * Format array to create new menu item
 * @param $link array
 * @return array
 */
function create_menu_item_array($link){
    if($link['external_internal'] == "0"){
        $href = base_url($link['href']);
    }
    else if($link['external_internal'] == "2"){
        $href = "#";
    }
    else{
        if($link['http_protocol'] == "0"){
            $href = 'http://'.$link['href'];
        }
        else{
            $href = 'https://'.$link['href'];
        }
    }
    if($link['show_in_iframe'] == "1"){
        if($link['main_setup'] == 2)
            $href = site_url('custom_links/custom_link/iframe/'.$link['id']);
        else
            $href = admin_url('custom_links/iframe/'.$link['id']);
    }
    $menu_item = [
        'slug'     => $link['unique_id'],
        'href'     => $href,
        'name'     => $link['title'],
        'position' => $link['position'],
    ];

    if($link['open_in_blank'] == "1"){
        $menu_item['href_attributes'] = [
            "target" => "_blank"
        ];
    }
    if(!empty($link['icon']))
        $menu_item['icon'] = 'fa '.$link['icon'];
    if(!empty($link['badge']))
        $menu_item['badge'] = [
            'value' => $link['badge'],
            'color' => $link['badge_color'],
        ];
    return $menu_item;
}

/**
 * Staff permissions for custom links module
 * @param $corePermissions array
 * @param $data array
 * @return array
 */
function custom_links_staff_permissions($corePermissions, $data){
    $corePermissions['custom_links'] = [
        'name'         => _l('mcl_custom_links'),
        'capabilities' => [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'view_own'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit' => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];
    return $corePermissions;
}

/**
 * Convert title to slug for unique id
 * @param $title string
 * @return string
 */
function custom_links_slug($title){
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
}