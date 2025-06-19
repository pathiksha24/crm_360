<?php
defined('BASEPATH') or exit('No direct script access allowed');

custom_links_db_migration_down();

function custom_links_db_migration_down(){
    $CI       = & get_instance();

    /* ADD TABLE TO SAVE PROJECT - TASK - EMPLOYEE HOURLY RATE */
    if ($CI->db->table_exists(db_prefix().'custom_links')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'custom_links`;');
    }
}