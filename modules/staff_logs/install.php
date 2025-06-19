<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'staff_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'staff_logs`
      (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `description` VARCHAR(255) NULL,
        `date` DATETIME NULL,
        `staffid` INT NULL,
        PRIMARY KEY (`id`)
    )');
}

if (!$CI->db->field_exists('status', db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`  ADD COLUMN `status` BOOLEAN NULL');
}

if (!$CI->db->field_exists('current_status', db_prefix() . 'staff')) {

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff` CHANGE `status` `current_status` BOOLEAN NULL');
}
