<?php

if (!$CI->db->table_exists(db_prefix() . 'auto_assign_leads_log')) {
    $CI->db->query('CREATE TABLE `'. db_prefix() ."auto_assign_leads_log` (
      `id` INT(10) NOT NULL AUTO_INCREMENT,
      `admin` int NOT NULL,
      `lead_id` int NOT NULL,
      `staff_id` int NOT NULL,
      `service_id` int NOT NULL,
      `date` DATETIME NULL,
      PRIMARY KEY (`id`));
    ");
}

add_option('max_leads_to_distribute', 1200);
add_option('max_leads_for_each_staff', 100);