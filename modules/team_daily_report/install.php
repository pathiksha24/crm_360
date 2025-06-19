<?php
add_option('team_daily_form_client_nationalities', '[]');
add_option('team_daily_form_services', '[]');
add_option('team_daily_form_sources', '[]');
add_option('team_daily_form_cities', '[]');

if (!$CI->db->field_exists('is_team_leader' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
    ADD COLUMN `is_team_leader` int default 0 NULL;');
}

if (!$CI->db->field_exists('team_leader' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
    ADD COLUMN `team_leader` int default 0 NULL;');
}
if (!$CI->db->field_exists('team_leader_2' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
    ADD COLUMN `team_leader_2` int default 0 NULL;');
}


if (!$CI->db->table_exists(db_prefix() . 'team_daily_report')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'team_daily_report` (
      `id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL,
      `createddate` datetime NOT NULL,
      `date` datetime NOT NULL,
      `client_firstname` varchar(50) NOT NULL,
      `client_lastname` varchar(50) NOT NULL,
      `client_nationality` varchar(50) NOT NULL,
      `city` varchar(50) NOT NULL,
      `deposit_gross` varchar(150) NOT NULL,
      `net_amount` varchar(150) NOT NULL,
      `phonenumber` varchar(50) NOT NULL,
      `service_name` varchar(50) NOT NULL,
      `closing_source` varchar(50) NOT NULL,
      `team_leader` int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');


    $CI->db->query('ALTER TABLE `' . db_prefix() . 'team_daily_report`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'team_daily_report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
}

if (!$CI->db->table_exists(db_prefix() . 'team_daily_report_widgets')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'team_daily_report_widgets` (
      `id` int(11) NOT NULL,
      `widget_name` varchar(1024) NOT NULL,
      `filters` TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');


    $CI->db->query('ALTER TABLE `' . db_prefix() . 'team_daily_report_widgets`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'team_daily_report_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
}


if (!$CI->db->field_exists('lead_id' ,db_prefix() . 'team_daily_report')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'team_daily_report`
    ADD COLUMN `lead_id` int default 0 NULL;');
}