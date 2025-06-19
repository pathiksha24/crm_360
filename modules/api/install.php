<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = & get_instance();
if (!$CI->db->table_exists(db_prefix() . 'user_api')) {
    $CI->db->query('CREATE TABLE `'. db_prefix() .'user_api` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user` VARCHAR(50) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expiration_date` DATETIME NOT NULL,
  PRIMARY KEY (`id`));
');
}

if($CI->db->field_exists('password',db_prefix() . 'user_api')) {
	$CI->db->query('ALTER TABLE '.db_prefix().'user_api DROP `password`');
}