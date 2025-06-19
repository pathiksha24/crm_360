<?php

if (!$CI->db->table_exists(db_prefix() . 'foreign_records')) {
    $CI->db->query('CREATE TABLE `'. db_prefix() ."foreign_records` (
      `id` INT(10) NOT NULL AUTO_INCREMENT,
      `agent` VARCHAR(255)  NULL,
      `leads` INT  NULL,
      `calls_connected` INT  NULL,
      `calls_not_connected` INT  NULL,
      `international_number` INT  NULL,
      `not_interested` INT  NULL,
      `lost` INT  NULL,
      `manager_id` INT NULL,
      `created_at` DATETIME NULL,
      PRIMARY KEY (`id`));
    ");
}

if (!$CI->db->field_exists('followup' ,db_prefix() . 'foreign_records')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'foreign_records`
    ADD COLUMN `followup` INT NULL;');
}

if (!$CI->db->field_exists('closing_from_walkin' ,db_prefix() . 'foreign_records')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'foreign_records`
    ADD COLUMN `closing_from_walkin` INT NULL;');
}

if (!$CI->db->field_exists('closing_from_leads' ,db_prefix() . 'foreign_records')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'foreign_records`
    ADD COLUMN `closing_from_leads` INT NULL;');
}

if (!$CI->db->field_exists('closing_from_reference' ,db_prefix() . 'foreign_records')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'foreign_records`
    ADD COLUMN `closing_from_reference` INT NULL;');
}

if (!$CI->db->field_exists('related_manager' ,db_prefix() . 'foreign_records')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'foreign_records`
    ADD COLUMN `related_manager` INT NULL;');
}