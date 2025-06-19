<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

add_option('call_twilio_account_sid', 0);
add_option('call_twilio_auth_token', 0);
add_option('call_twilio_phone_number', 0);
add_option('call_twiml_app_sid', 0);
add_option('call_twilio_recording_active', 0);
add_option('call_twilio_active', 0);
add_option('call_zoom_active', 0);
if (!$CI->db->table_exists(db_prefix() . 'lead_manager_activity_log')) {

  $CI->db->query("CREATE TABLE " .db_prefix(). "lead_manager_activity_log (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type` enum('audio_call','video_call','sms') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'audio_call',
    `is_audio_call_recorded` tinyint(1) NOT NULL DEFAULT '0',
    `lead_id` int(11) NOT NULL,
    `date` datetime NOT NULL,
    `description` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    `additional_data` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    `staff_id` int(11) NOT NULL,
    `direction` enum('incoming','outgoing') NOT NULL,
    `call_duration` varchar(255) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");    
}

if (!$CI->db->table_exists(db_prefix() . 'lead_manager_meeting_remark')) {

 $CI->db->query('CREATE TABLE `' . db_prefix() . "lead_manager_meeting_remark` (
   `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `rel_id` int(11) NOT NULL,
   `remark` longtext NOT NULL,
   `rel_type` varchar(100) NOT NULL COMMENT '1=audio_call,2=zoom_call',
   `date` datetime NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');    
 }
 $row_exists = $CI->db->query('SELECT * FROM '.db_prefix() . 'emailtemplates where type = "lead_manager" and slug = "lead-manager-send-to-customer" and language = "english";')->row();
 if(!$row_exists){
   $message='<html class="no-js" lang=""> <head> <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700;900&display=swap" rel="stylesheet"> </head> <body style=" font-family: "Noto Sans KR", sans-serif;margin: 0; padding: 0; overflow-x: hidden; background-color: #f4f5f9; padding: 35px;"> <section style="box-shadow: 0 4px 8px 0 rgb(241 241 241 / 20%), 0 6px 20px 0 rgb(212 212 212 / 19%); background-color: white; padding: 20px; max-width: 46%; margin: 0 auto; border: 1px solid #e4e4e4;"> <div class=" margin: 10px 6px; margin-bottom: 0;"> <h4 style="margin: 0; font-size: 14px; text-transform: uppercase; font-weight: 500;">Hi &nbsp;{customer_name},</h4> <p style=" margin: 6px 0; font-size: 14px;">Lead Manager Sent You a Meeting Url</p></div><table class="table" style=" width: 100%; margin-top: 30px;"> <tbody> <tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Topic :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{topic}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010;position: relative; left: 2px; width: 25%;">Meeting ID :</th> <td style=" border-top: 1px solid #dee2e6;font-size: 14px;padding-left: 15px;">{meeting_id}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010;position: relative; left: 2px; width: 25%;">Time :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px;padding-left: 15px;">{meeting_time}</td></tr><tr> <th style="padding:8px 0;vertical-align:top;border-top:1px solid #dee2e6;text-align:left;font-size:14px;color:#101010;width:25%">Duration :</th> <td style="border-top:1px solid #dee2e6;font-size:14px;padding-left:15px">{meeting_duration} Minutes</td></tr></tbody> </table> <div > <a href="{join_url}" target="_blank"><button style="background-color: #2d8cff; border-color: transparent; padding: 11px 22px; color: white; border-radius: 7px; margin: 18px 0; font-weight: 500; font-size: 18px; cursor: pointer;" type="button" class="btn btn-secondary">Start Meeting</button></a> </div><hr style=" border: 1px solid #dedede;"> <div style="text-align: right;padding: 0 0px;"> <p style=" margin: 2px;">Thank You for choosing Lead Manager</p><b>- The Lead Manager Team</b> </div></section> </body></html>';
   $CI->db->query("INSERT INTO `".db_prefix() ."emailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES ('lead_manager', 'lead-manager-send-to-customer', 'english', 'Lead Manager (Sent Zoom Link)', 'Lead Manager Sent Zoom Link','".$message."','', NULL, 0, 1, 0);");
 }
 $row_exists = $CI->db->query('SELECT * FROM '.db_prefix() . 'emailtemplates where type = "lead_manager" and slug = "lead-manager-send-to-staff" and language = "english";')->row();
 if(!$row_exists){
   $message='<html class="no-js" lang=""> <head> <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700;900&display=swap" rel="stylesheet"> </head> <body style=" font-family: "Noto Sans KR", sans-serif;margin: 0; padding: 0; overflow-x: hidden; background-color: #f4f5f9; padding: 35px;"> <section style="box-shadow: 0 4px 8px 0 rgb(241 241 241 / 20%), 0 6px 20px 0 rgb(212 212 212 / 19%); background-color: white; padding: 20px; max-width: 46%; margin: 0 auto; border: 1px solid #e4e4e4;"> <div class=" margin: 10px 6px; margin-bottom: 0;"> <h4 style="margin: 0; font-size: 14px; text-transform: uppercase; font-weight: 500;">Hi &nbsp;{staff_name},</h4> <p style=" margin: 6px 0; font-size: 14px;">Lead Manager Sent You a Meeting Url</p></div><table class="table" style=" width: 100%; margin-top: 30px;"> <tbody> <tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Topic :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{topic}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010;position: relative; left: 2px; width: 25%;">Meeting ID :</th> <td style=" border-top: 1px solid #dee2e6;font-size: 14px;padding-left: 15px;">{meeting_id}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010;position: relative; left: 2px; width: 25%;">Time :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px;padding-left: 15px;">{meeting_time}</td></tr><tr> <th style="padding:8px 0;vertical-align:top;border-top:1px solid #dee2e6;text-align:left;font-size:14px;color:#101010;width:25%">Duration :</th> <td style="border-top:1px solid #dee2e6;font-size:14px;padding-left:15px">{meeting_duration} Minutes</td></tbody> </table> <div > <a href="{join_url}" target="_blank"><button style="background-color: #2d8cff; border-color: transparent; padding: 11px 22px; color: white; border-radius: 7px; margin: 18px 0; font-weight: 500; font-size: 18px; cursor: pointer;" type="button" class="btn btn-secondary">Start Meeting</button></a> </div><hr style=" border: 1px solid #dedede;"> <div style="text-align: right;padding: 0 0px;"> <p style=" margin: 2px;">Thank You for choosing Lead Manager</p><b>- The Lead Manager Team</b> </div></section> </body></html>';
   $CI->db->query("INSERT INTO `".db_prefix() ."emailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES ('lead_manager', 'lead-manager-send-to-staff', 'english', 'Lead Manager (Sent Zoom Link)', 'Lead Manager Sent Zoom Link','".$message."','', NULL, 0, 1, 0);");
 }

 if (!$CI->db->table_exists(db_prefix() . 'lead_manager_missed_calls')) {
   $CI->db->query("CREATE TABLE `tbllead_manager_missed_calls` (
   `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `staff_id` int(11) NOT NULL,
   `call_sid` mediumtext COLLATE utf8_unicode_ci NOT NULL,
   `staff_twilio_number` mediumtext COLLATE utf8_unicode_ci,
   `date` datetime NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");    
 }

 if (!$CI->db->field_exists('converted_by_lead_manager', db_prefix() . 'leads')) {
  $CI->db->query("ALTER TABLE `".db_prefix()."leads` ADD COLUMN converted_by_lead_manager BOOLEAN NOT NULL DEFAULT FALSE AFTER client_id");
}

if (!$CI->db->table_exists(db_prefix() . 'lead_manager_zoom_meeting')) {

 $CI->db->query('CREATE TABLE `' . db_prefix() . "lead_manager_zoom_meeting` (
   `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `leadid` int(11) NOT NULL,
   `meeting_id` varchar(255) NOT NULL,
   `join_url` longtext NOT NULL,
   `start_url` longtext NOT NULL,
   `name` varchar(110) NOT NULL,
   `email` varchar(110) NOT NULL,
   `staff_id` int(11) NOT NULL,
   `staff_name` varchar(110) NOT NULL,
   `staff_email` varchar(100) NOT NULL,
   `country` varchar(100) NOT NULL,
   `timezone` varchar(100) NOT NULL,
   `meeting_date` datetime NOT NULL,
   `meeting_duration` int(11) NOT NULL,
   `meeting_option` longtext NOT NULL,
   `meeting_agenda` varchar(200) NOT NULL,
   `meeting_description` longtext NOT NULL,
   `password` varchar(100) NOT NULL,
   `remark` longtext NOT NULL,
   `status` int(11) NOT NULL COMMENT '1=waiting,',
   `created_at` datetime NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');    
}

$query = $CI->db->get_where(db_prefix().'customfields', array('slug' => 'staff_twilio_phone_number', 'fieldto' => 'staff'));
if($query->num_rows() == 0){
  $data = array(
  'fieldto' => 'staff',
  'name' => 'Twilio Phone Number',
  'slug' => 'staff_twilio_phone_number',
  'required' => 0,
  'type' => 'input',
  'options' => '',
  'display_inline' => 0,
  'field_order' => 8,
  'active' => 1,
  'show_on_pdf' => 0,
  'show_on_ticket_form' => 0,
  'only_admin' => 0,
  'show_on_table' => 1,
  'show_on_client_portal' => 0,
  'disalow_client_to_edit' => 0,
  'bs_column' => 12,
  );
  $CI->db->insert(db_prefix().'customfields', $data);
}

if (!$CI->db->field_exists('lm_follow_up', db_prefix() . 'leads')) {
  $CI->db->query("ALTER TABLE `".db_prefix()."leads` ADD COLUMN `lm_follow_up` TINYINT NOT NULL DEFAULT '0'");
}
if (!$CI->db->field_exists('lm_follow_up_date', db_prefix() . 'lead_manager_meeting_remark')) {
  $CI->db->query("ALTER TABLE `".db_prefix()."lead_manager_meeting_remark` ADD COLUMN `lm_follow_up_date` VARCHAR(255) NULL DEFAULT NULL");
}
if (!$CI->db->table_exists(db_prefix() . 'lead_manager_conversation')) {
  $CI->db->query("CREATE TABLE `". db_prefix() ."lead_manager_conversation` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `msg_service_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `msg_sid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `from_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `to_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `to_id` int(11) DEFAULT NULL,
    `from_id` int(11) DEFAULT NULL,
    `sms_direction` enum('outgoing','incoming') COLLATE utf8_unicode_ci NOT NULL,
    `sms_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `sms_body` longtext COLLATE utf8_unicode_ci,
    `api_response` longtext COLLATE utf8_unicode_ci,
    `is_read` ENUM('no','yes') NULL DEFAULT NULL ,
    `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
}
$template_exists_customer = $CI->db->query('SELECT * FROM '.db_prefix() . 'emailtemplates where type = "lead_manager" and slug = "lead-manager-send-to-customer" and language = "english";')->row();
     $customer_template='<html class="no-js" lang=""><head><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700;900&display=swap" rel="stylesheet"> </head> <body style=" font-family: "Noto Sans KR", sans-serif;margin: 0; padding: 0; overflow-x: hidden; background-color: #f4f5f9; padding: 35px;"> <section style="box-shadow: 0 4px 8px 0 rgb(241 241 241 / 20%), 0 6px 20px 0 rgb(212 212 212 / 19%); background-color: white; padding: 20px; max-width: 46%; margin: 0 auto; border: 1px solid #e4e4e4;"> <div class=" margin: 10px 6px; margin-bottom: 0;"> <h4 style="margin: 0; font-size: 14px; text-transform: uppercase; font-weight: 500;">Hi &nbsp;<b>{lead_name}</b>,</h4> <p style=" margin: 6px 0; font-size: 14px;">{staff_name} Sent You a Meeting details</p></div><table class="table" style=" width: 100%; margin-top: 30px;"> <tbody><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Staff Email :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{staff_email}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Staff Contact No :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{staff_phonenumber}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Meeting agenda :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{topic}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Description :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{meeting_description}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Meeting Created :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{created_at}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Meeting scheduled :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{meeting_time}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010;position: relative; left: 2px; width: 25%;">Meeting ID :</th> <td style=" border-top: 1px solid #dee2e6;font-size: 14px;padding-left: 15px;">{meeting_id}</td></tr><tr> <th style="padding:8px 0;vertical-align:top;border-top:1px solid #dee2e6;text-align:left;font-size:14px;color:#101010;width:25%">Duration :</th> <td style="border-top:1px solid #dee2e6;font-size:14px;padding-left:15px">{meeting_duration} Minutes</td></tr></tbody> </table> <div > <a href="{join_url}" target="_blank"><button style="background-color: #2d8cff; border-color: transparent; padding: 11px 22px; color: white; border-radius: 7px; margin: 18px 0; font-weight: 500; font-size: 18px; cursor: pointer;" type="button" class="btn btn-secondary">Join Meeting</button></a> </div><hr style=" border: 1px solid #dedede;"> <div style="text-align: right;padding: 0 0px;"> <p style=" margin: 2px;">Thank You for choosing Lead Manager</p><a href="https://zonvoir.com/"><b>Powered by zonvoir</b></a> </div></section> </body></html>';
     if(!$template_exists_customer){
      $CI->db->query("INSERT INTO `".db_prefix() ."emailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES ('lead_manager', 'lead-manager-send-to-customer', 'english', 'Lead Manager (Sent Zoom Link)', 'Lead Manager Sent Zoom Link','".$customer_template."','', NULL, 0, 1, 0);");
    }else{
      $CI->db->where('emailtemplateid',$template_exists_customer->emailtemplateid);
      $CI->db->update(db_prefix() ."emailtemplates",['message' => $customer_template]);
    }

    $template_exists_staff = $CI->db->query('SELECT * FROM '.db_prefix() . 'emailtemplates where type = "lead_manager" and slug = "lead-manager-send-to-staff" and language = "english";')->row();
    $staff_template='<html class="no-js" lang=""> <head> <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700;900&display=swap" rel="stylesheet"> </head> <body style=" font-family: "Noto Sans KR", sans-serif;margin: 0; padding: 0; overflow-x: hidden; background-color: #f4f5f9; padding: 35px;"> <section style="box-shadow: 0 4px 8px 0 rgb(241 241 241 / 20%), 0 6px 20px 0 rgb(212 212 212 / 19%); background-color: white; padding: 20px; max-width: 46%; margin: 0 auto; border: 1px solid #e4e4e4;"> <div class=" margin: 10px 6px; margin-bottom: 0;"> <h4 style="margin: 0; font-size: 14px; text-transform: uppercase; font-weight: 500;">Hi &nbsp;<b>{staff_name}</b>,</h4> <p style=" margin: 6px 0; font-size: 14px;">You created a Meeting for <b> {lead_name} </b> at {created_at}</p></div><table class="table" style=" width: 100%; margin-top: 30px;"> <tbody><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Lead Email :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{lead_email}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Lead Contact No. :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{lead_phonenumber}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Meeting agenda :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{topic}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Description :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{meeting_description}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010; position: relative; left: 2px; width: 25%;">Meeting scheduled :</th> <td style=" border-top: 1px solid #dee2e6; font-size: 14px; padding-left: 15px;">{meeting_time}</td></tr><tr> <th style=" padding: 8px 0; vertical-align: top; border-top: 1px solid #dee2e6; text-align: left; font-size: 14px; color: #101010;position: relative; left: 2px; width: 25%;">Meeting ID :</th> <td style=" border-top: 1px solid #dee2e6;font-size: 14px;padding-left: 15px;">{meeting_id}</td></tr><tr> <th style="padding:8px 0;vertical-align:top;border-top:1px solid #dee2e6;text-align:left;font-size:14px;color:#101010;width:25%">Duration :</th> <td style="border-top:1px solid #dee2e6;font-size:14px;padding-left:15px">{meeting_duration} Minutes</td></tr></tbody> </table> <div > <a href="{join_url}" target="_blank"><button style="background-color: #7cb342; border-color: transparent; padding: 11px 22px; color: white; border-radius: 7px; margin: 18px 0; font-weight: 500; font-size: 18px; cursor: pointer;" type="button" class="btn btn-secondary">Start Meeting</button></a> </div><hr style=" border: 1px solid #dedede;"> <div style="text-align: right;padding: 0 0px;"> <p style=" margin: 2px;">Thank You for choosing Lead Manager</p><a href="https://zonvoir.com/"><b>Powered by zonvoir</b></a> </div></section> </body></html>';
    if(!$template_exists_staff){
      $CI->db->query("INSERT INTO `".db_prefix() ."emailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES ('lead_manager', 'lead-manager-send-to-staff', 'english', 'Lead Manager (Sent Zoom Link)', 'Lead Manager Sent Zoom Link','".$message."','', NULL, 0, 1, 0);");
    }else{
      $CI->db->where('emailtemplateid',$template_exists_staff->emailtemplateid);
      $CI->db->update(db_prefix() ."emailtemplates",['message' => $staff_template]);
    }

    if ($CI->db->field_exists('meeting_id', db_prefix().'lead_manager_zoom_meeting')) {

      $CI->db->query("ALTER TABLE ".db_prefix()."lead_manager_zoom_meeting CHANGE `meeting_id` `meeting_id` VARCHAR(255) NOT NULL;");
    }
    if ($CI->db->field_exists('status', db_prefix().'lead_manager_zoom_meeting')) {
      $CI->db->query("ALTER TABLE ".db_prefix()."lead_manager_zoom_meeting CHANGE `status` `status` VARCHAR(255) NOT NULL;");
      $CI->db->query("UPDATE ".db_prefix()."lead_manager_zoom_meeting SET `status` = 'waiting';");
    }
    if ($CI->db->field_exists('lm_follow_up_date', db_prefix().'lead_manager_meeting_remark')) {
      $CI->db->query("ALTER TABLE ".db_prefix()."lead_manager_meeting_remark CHANGE `lm_follow_up_date` `lm_follow_up_date` DATETIME NULL DEFAULT NULL;");
    }
    if (!$CI->db->field_exists('is_client', db_prefix().'lead_manager_zoom_meeting')) {
      $CI->db->query("ALTER TABLE ".db_prefix()."lead_manager_zoom_meeting ADD `is_client` TINYINT NOT NULL DEFAULT '0' AFTER `status`;");
    }
    if (!$CI->db->field_exists('is_client', db_prefix().'lead_manager_conversation')) {
      $CI->db->query("ALTER TABLE ".db_prefix()."lead_manager_conversation ADD `is_client` TINYINT NOT NULL DEFAULT '0' AFTER `api_response`;");
    }
    if (!$CI->db->field_exists('is_client', db_prefix().'lead_manager_activity_log')) {
      $CI->db->query("ALTER TABLE ".db_prefix()."lead_manager_activity_log ADD `is_client` TINYINT NOT NULL DEFAULT '0' AFTER `call_duration`;");
    }
    if (!$CI->db->table_exists(db_prefix() . 'lead_manager_mailbox')) {
      $CI->db->query("CREATE TABLE `". db_prefix() ."lead_manager_mailbox` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staffid` int(11) NOT NULL,
        `toid` int(11) NOT NULL DEFAULT '0',
        `is_client` tinyint(4) NOT NULL,
        `from_email` varchar(255) NOT NULL,
        `fromName` varchar(100) DEFAULT NULL,
        `to_email` varchar(255) NOT NULL,
        `subject` varchar(255) DEFAULT NULL,
        `direction` enum('inbound','outbound') NOT NULL,
        `message` text NOT NULL,
        `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `status` varchar(100) NOT NULL,
        `is_attachment` tinyint(4) NOT NULL,
        `is_read` tinyint(4) NOT NULL,
        `is_bookmark` tinyint(4) NOT NULL DEFAULT '0',
        `is_favourite` tinyint(4) NOT NULL DEFAULT '0',
        `sequence_id` int(11) DEFAULT NULL,
        `email_size` varchar(255) DEFAULT NULL,
        `mail_date` varchar(255) DEFAULT NULL,
        PRIMARY KEY (id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }
    if (!$CI->db->table_exists(db_prefix() . 'lead_manager_mailbox_settings')) {
      $CI->db->query("CREATE TABLE `". db_prefix() ."lead_manager_mailbox_settings` (
        `staff_id` int(11) NOT NULL,
        `is_smtp` tinyint(4) NOT NULL,
        `smtp_server` varchar(100) NOT NULL,
        `smtp_user` varchar(100) NOT NULL,
        `smtp_password` varchar(255) NOT NULL,
        `smtp_encryption` varchar(100) NOT NULL,
        `smtp_port` int(11) DEFAULT NULL,
        `smtp_fromname` varchar(255) DEFAULT NULL,
        `is_imap` tinyint(4) NOT NULL DEFAULT '0',
        `imap_server` varchar(100) DEFAULT NULL,
        `imap_user` varchar(100) DEFAULT NULL,
        `imap_password` varchar(255) DEFAULT NULL,
        `imap_encryption` varchar(100) DEFAULT NULL,
        `imap_folder` varchar(100) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    }
    if (!$CI->db->table_exists(db_prefix() . 'lead_manager_mailbox_attachments')) {
      $CI->db->query("CREATE TABLE `". db_prefix() ."lead_manager_mailbox_attachments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staff_id` int(11) NOT NULL,
        `mailbox_id` int(11) NOT NULL,
        `file_name` varchar(255) NOT NULL,
        `filetype` varchar(100) NOT NULL,
        `added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    }
    if(!is_dir(LEAD_MANAGER_UPLOADS_FOLDER)){
      mkdir(LEAD_MANAGER_UPLOADS_FOLDER, 0777, TRUE);
      fopen(LEAD_MANAGER_UPLOADS_FOLDER . 'index.html', 'w');
      $fp = fopen(LEAD_MANAGER_UPLOADS_FOLDER . 'index.html', 'a+');
      if ($fp) {
        fclose($fp);
      }
    }
    if(!is_dir(LEAD_MANAGER_MAILBOX_FOLDER)){
      mkdir(LEAD_MANAGER_MAILBOX_FOLDER, 0777, TRUE);
      fopen(LEAD_MANAGER_MAILBOX_FOLDER . 'index.html', 'w');
      $fp = fopen(LEAD_MANAGER_MAILBOX_FOLDER . 'index.html', 'a+');
      if ($fp) {
        fclose($fp);
      }
    }
    add_option('lead_manager_imap_check_every', 3);
    add_option('lead_manager_imap_last_checked', 0);

if (!$CI->db->field_exists('in_reply_to', db_prefix() . 'lead_manager_mailbox')) {
  $CI->db->query("ALTER TABLE `".db_prefix()."lead_manager_mailbox` ADD `in_reply_to` VARCHAR(255) NULL DEFAULT NULL AFTER `mail_date`");
}
if (!$CI->db->field_exists('in_references', db_prefix() . 'lead_manager_mailbox')) {
  $CI->db->query("ALTER TABLE `".db_prefix()."lead_manager_mailbox` ADD `in_references`TEXT NULL DEFAULT NULL AFTER `in_reply_to`");
}
if (!$CI->db->field_exists('message_id', db_prefix() . 'lead_manager_mailbox')) {
  $CI->db->query("ALTER TABLE `".db_prefix()."lead_manager_mailbox` ADD `message_id` VARCHAR(255) NULL DEFAULT NULL AFTER `in_references`");
}