<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = & get_instance();
if (!$CI->db->table_exists(db_prefix() . 'leads_services')) {
    $CI->db->query('CREATE TABLE `'. db_prefix() .'leads_services` (
      `id` INT(10) NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(50) NOT NULL,
      PRIMARY KEY (`id`));
    ');
}
if (!$CI->db->table_exists(db_prefix() . 'leads_languages')) {
    $CI->db->query('CREATE TABLE `'. db_prefix() .'leads_languages` (
      `id` INT(10) NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(50) NOT NULL,
      PRIMARY KEY (`id`));
    ');
}
if (!$CI->db->field_exists('service' ,db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads`
    ADD COLUMN `service` INT NOT NULL DEFAULT 1;');
}
if (!$CI->db->field_exists('language' ,db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads`
    ADD COLUMN `language` INT NOT NULL DEFAULT 1;');
}
if (!$CI->db->field_exists('whatsapp_number' ,db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads`
    ADD COLUMN `whatsapp_number` varchar(50) null;');
}
if (!$CI->db->field_exists('dateclosed' ,db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads`
    ADD COLUMN `dateclosed` datetime null;');
}


add_option('leads_unassigned_admins', json_encode([42]));