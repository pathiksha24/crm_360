<?php
defined('BASEPATH') or exit('No direct script access allowed');
if(!$CI->db->table_exists(db_prefix() . 'si_lead_filter')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "si_lead_filter` (
	`id` int(11) NOT NULL,
	`filter_name` varchar(200) NOT NULL,
	`filter_parameters` text NOT NULL,
	`staff_id` int(11) NOT NULL DEFAULT '0'
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'si_lead_filter`
	ADD PRIMARY KEY (`id`),
	ADD KEY `staff_id` (`staff_id`);');
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'si_lead_filter`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}


if (!$CI->db->field_exists('dateassigned_fixed' ,db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads`
        ADD COLUMN `dateassigned_fixed` int default 0 null;');
}