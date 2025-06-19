<?php

defined('BASEPATH') or exit('No direct script access allowed');
if (!$CI->db->field_exists('popped_up', db_prefix() . 'reminders')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'reminders`  ADD COLUMN `popped_up` INT(11) NOT NULL DEFAULT 0');
  }