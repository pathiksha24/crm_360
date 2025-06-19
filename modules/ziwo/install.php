<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->field_exists('ziwo_agent_id' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
ADD COLUMN `ziwo_agent_id` varchar(30) null;');
}
