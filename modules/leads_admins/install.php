<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->field_exists('leads_staff' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
    ADD COLUMN `leads_staff` TEXT NULL;');
}

if (!$CI->db->field_exists('is_super_admin' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
    ADD COLUMN `is_super_admin` INT NOT NULL DEFAULT 0;');
}