<?php
defined('BASEPATH') or exit('No direct script access allowed');

custom_links_db_up();

function custom_links_db_up(){
    $CI       = & get_instance();

    /* ADD TABLE TO STORE ALL LANGUAGE FILES IN DB */
    if (!$CI->db->table_exists(CUSTOM_LINKS_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' . CUSTOM_LINKS_TABLE_NAME.  "` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(64) NOT NULL,
  `parent_id` varchar(64) NOT NULL,
  `title` varchar(127) NOT NULL,
  `href` longtext NOT NULL,
  `position` int(10) UNSIGNED DEFAULT NULL,
  `icon` varchar(127) DEFAULT NULL,
  `external_internal` int(1) UNSIGNED NOT NULL,
  `http_protocol` int(1) UNSIGNED DEFAULT NULL,
  `open_in_blank` int(1) UNSIGNED DEFAULT 0 NOT NULL,
  `main_setup` int(1) UNSIGNED DEFAULT 0 NOT NULL,
  `badge` varchar(63) DEFAULT NULL,
  `badge_color` varchar(63) DEFAULT NULL,
  `require_login` int(1) UNSIGNED DEFAULT 0 NOT NULL,
  `show_in_iframe` int(1) UNSIGNED DEFAULT 0 NOT NULL,
  `users` TEXT DEFAULT NULL,
  `added_at` datetime DEFAULT NULL,
  `added_by` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }
}