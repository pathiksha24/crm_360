<?php

if (!$CI->db->field_exists('changeddate' ,db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads`
    ADD COLUMN `changeddate` DATETIME NULL;');
}


if (!$CI->db->field_exists('manager_id' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
    ADD COLUMN `manager_id` INT default 0 NOT NULL;');
}