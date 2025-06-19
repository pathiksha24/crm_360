<?php 
defined('BASEPATH') or exit('No direct script access allowed');
$check =  __dir__ ;
$str= preg_replace('/\W\w+\s*(\W*)$/', '$1', $check);
$str.'/third_party/twilio-web/src/Twilio/autoload.php';
use Twilio\Rest\Client;
use app\services\imap\Imap;
use Ddeboer\Imap\SearchExpression;
use app\services\imap\ConnectionErrorException;
function call_api_setting(){
    $data['account_sid'] = get_option('call_twilio_account_sid');
    $data['auth_token'] = get_option('call_twilio_auth_token');
    $data['twilio_number'] = get_option('call_twilio_phone_number');
    $data['twiml_app_sid'] = get_option('call_twiml_app_sid');    
    return $data;
}
function lead_manager_send_mail_template()
{  
    $params = func_get_args();
    return lead_manager_mail_template(...$params)->send();
}

function lead_manager_mail_template($class)
{
    $CI = &get_instance();

    $params = func_get_args();

    unset($params[0]);

    $params = array_values($params);

    $path = lead_manager_get_mail_template_path($class, $params);

    if (!file_exists($path)) {
        if (!defined('CRON')) {
            show_error('Mail Class Does Not Exists [' . $path . ']');
        } else {

            return false;
        }
    }
    if (!class_exists($class, false)) {
        include_once($path);
    }
    $instance = new $class(...$params);
    return $instance;
}
function lead_manager_get_mail_template_path($class, &$params)
{
    $CI  = &get_instance();
    $dir = APP_MODULES_PATH . 'lead_manager/libraries/mails/';
    if (isset($params[0]) && is_string($params[0]) && is_dir(module_dir_path($params[0]))) {
        $module = $CI->app_modules->get($params[0]);

        if ($module['activated'] === 1) {
            $dir = module_libs_path($params[0]) . 'mails/';
        }
        unset($params[0]);
        $params = array_values($params);
    }

    return $dir . ucfirst($class) . '.php';
}

function get_zoom_status_by_id($id)
{
    $CI = &get_instance();
    if (!class_exists('lead_manager_model')) {
        $CI->load->model('lead_manager_model');
    }
    $statuses = $CI->lead_manager_model->get_zoom_statuses();
    $status = [
      'id'    => 0,
      'color' => '#333',
      'name'  => _l('End'),
      'order' => 1,
  ];
  foreach ($statuses as $s) {
    if ($s['id'] == $id) {
        $status = $s;

        break;
    }
}
return $status;
}

function get_latest_zoom_meeting_remark($id)
{ 
    if($id){
       $CI = &get_instance();
       $CI->db->where(['rel_id'=>$id]);
       $CI->db->order_by('date','desc');
       $res= $CI->db->get(db_prefix() . 'lead_manager_meeting_remark')->row();
       return ($res)?$res->remark:'...';
   }
   return false;
}

function get_staff_own_twilio_number()
{ 
    $id=get_staff_user_id();
    if($id){
       $CI = &get_instance();
       $twilio_result = $CI->db->get_where(db_prefix().'customfields',['slug' => 'staff_twilio_phone_number','fieldto'=>'staff'])->row();
       if(isset($twilio_result) && !empty($twilio_result)){
        $CI->db->select('value');
        $CI->db->where(['relid'=>$id,'fieldto'=>'staff','fieldid'=>$twilio_result->id]);
        $res= $CI->db->get(db_prefix() . 'customfieldsvalues')->row();
        return ($res)?$res->value:'0';
    }else{
        return '0';
    }
}
return false;
}
function get_staff_by_twilio_number($number)
{
   if($number){
       $CI = &get_instance();
       $twilio_result = $CI->db->get_where(db_prefix().'customfields',['slug' => 'staff_twilio_phone_number','fieldto'=>'staff'])->row();
       if(isset($twilio_result) && !empty($twilio_result)){
           $CI->db->select('relid');
           $CI->db->where(['value'=>$number,'fieldto'=>'staff']);
           $res= $CI->db->get(db_prefix() . 'customfieldsvalues')->row();
           return ($res) ? $res->relid : '0';
       }else{
        return '0';
    }
}
return false;
}
function busy_incoming_calls()
{
    if(get_option('call_twilio_active')){
        $now = new DateTime();
        $todayDate = $now->format('Y-m-d');
        $dateObj = $todayDate.'T00:00:00Z';
        $sid  = get_option('call_twilio_account_sid');
        $token  = get_option('call_twilio_auth_token');
        $twilio = new Client($sid, $token);
        $calls = $twilio->calls->read(["direction" => "inbound-dial", 'startTimeAfter' => new \DateTime($dateObj)], 10);
        $i = 0;
        $data = [];
        if(isset($calls) && !empty($calls)){
            foreach ($calls as $record) {
                $staffId = get_staff_by_twilio_number($record->to);
                if($staffId){
                    $callDate = $record->dateCreated;
                    $data[$i]['status'] = $record->status;
                    $data[$i]['from'] = $record->from;
                    $data[$i]['direction'] = $record->direction;
                    $data[$i]['to'] = $record->to;
                    $data[$i]['sid'] = $record->sid;
                    $data[$i]['parentCallSid'] = $record->parentCallSid;
                    $data[$i]['dateCreated'] = $callDate->format('Y-m-d H:i:s');
                    $data[$i]['dateCreated1'] = $record->dateCreated;
                    $data[$i]['staff_id'] = $staffId;
                    $data[$i]['child_status']='';
                    $pcalls = $twilio->calls->read(["parentCallSid" => $data[$i]['sid']], 1);
                    if(isset($pcalls) && !empty($pcalls)){
                        foreach($pcalls as $childcall){
                            $data[$i]['child_status'] = $childcall->status;
                        }
                    }
                    if($data[$i]['status'] == 'busy' || $data[$i]['child_status'] == 'no-answer' || $data[$i]['status'] == 'failed' || $data[$i]['child_status'] == 'failed' || $data[$i]['child_status'] == 'busy' || $data[$i]['status'] == 'no-answer')
                    {
                        addMissedCalls($data, $i);
                    }                 
                } 
                $i++;
            }
        }   
    }
}
function get_lead_name_by_number($number)
{
   if($number){
       $CI = &get_instance();
       $CI->db->select('name');
       $CI->db->where(['phonenumber' => $number]);
       $res= $CI->db->get(db_prefix() . 'leads')->row();
       return ($res) ? $res->name : false;
   }
   return false;
}
function addMissedCalls($data, $j)
{
    $CI = &get_instance();
    $CI->db->where('call_sid', $data[$j]['sid']);
    $q = $CI->db->get(db_prefix() . 'lead_manager_missed_calls');
    $leadName = get_lead_name_by_number($data[$j]['from']);
    if($q->num_rows() == 0){
        $insert_data = array(
            'staff_id' => $data[$j]['staff_id'],
            'call_sid' => $data[$j]['sid'],
            'staff_twilio_number' => $data[$j]['to'],
            'date' => $data[$j]['dateCreated'],
        );
        $CI->db->insert(db_prefix() . 'lead_manager_missed_calls',$insert_data);
        $notifcationArr = array(
            'isread' => 0,
            'isread_inline' => 0,
            'date' => $data[$j]['dateCreated'],
            'description' => 'You have missed call from: '.$data[$j]['from'].' at '.$data[$j]['dateCreated'],
            'fromuserid' => 0,
            'fromclientid' => 0,
            'from_fullname' => '//',
            'touserid' => $data[$j]['staff_id'],
            'link' => null,
            'additional_data' => null
        );
        if($leadName){
         $notifcationArr['description']  = 'You have missed call from: lead '.$leadName.' ('.$data[$j]['from'].')';
     }else{
         $notifcationArr['description']  = 'You have missed call from: '.$data[$j]['from'];
     }
     $CI->db->insert(db_prefix() . 'notifications',$notifcationArr);
 }
}
function get_lead_id_by_number($number)
{
   if($number){
       $CI = &get_instance();
       $CI->db->select('id');
       $CI->db->where(['phonenumber' => $number]);
       $res= $CI->db->get(db_prefix() . 'leads')->row();
       return ($res) ? $res->id : false;
   }
   return false;
}
function auto_meeting_status_update(){
    if(get_option('call_zoom_active')){
        $CI = &get_instance();
        if (!class_exists('lead_manager_model')) {
            $CI->load->model('lead_manager/lead_manager_model');
        }
        $params['apiSecret'] = get_option('zoom_secret_key');
        $params['apiKey'] = get_option('zoom_api_key');
        $params['path'] = 'meetings';
        $meetings = $CI->lead_manager_model->zoomMeetingDetails('',['status' => 'waiting']);
        include_once('modules/lead_manager/libraries/zoom/ZoomJwtApiWrapper.php');
        $zoom = new ZoomJwtApiWrapper($params);
        if(isset($meetings) && !empty($meetings)){
            foreach ($meetings as $meeting) {
                $pathParams=array('meetingId'=>$meeting['meeting_id']);
                $response = $zoom->doRequest('GET', '/meetings/{meetingId}',[] ,$pathParams , '');
                if(isset($response) && !empty($response) && isset($response['status'])){
                    $update_data = ['id' => $meeting['id']];
                    $update_data['status'] = $response['status'];
                    $CI->lead_manager_model->update_meeting_status($update_data);
                }
            }
        }
    }
}
function get_last_message_conversation($lead_id, $where)
{
    if(is_numeric($lead_id)){
        $CI = &get_instance();
        $staff_id = get_staff_user_id();
        $query = '';
        if($where['is_client'] == 'no'){
            $query = $CI->db->query("SELECT * FROM ".db_prefix() . "lead_manager_conversation WHERE (to_id=".$lead_id." AND from_id=".$staff_id.") OR (to_id=".$staff_id." AND from_id=".$lead_id.") AND is_client = 0 ORDER BY id DESC LIMIT 1");
        }else{
            $query = $CI->db->query("SELECT * FROM ".db_prefix() . "lead_manager_conversation WHERE (to_id=".$lead_id." AND from_id=".$staff_id.") OR (to_id=".$staff_id." AND from_id=".$lead_id.") AND is_client = 1 ORDER BY id DESC LIMIT 1");
        }
        return $query->row();
    }
}
function get_meetings($status)
{
    $CI = &get_instance();
    $CI->db->where(['status'=>$status]);
    $result = $CI->db->get(db_prefix() . 'lead_manager_zoom_meeting')->result_array();
    return $result;
}
function get_client_id_by_number($number)
{
   if($number){
       $CI = &get_instance();
       $CI->db->select('userid');
       $CI->db->where(['phonenumber' => $number, 'is_primary' => 1]);
       $res= $CI->db->get(db_prefix() . 'contacts')->row();
       return ($res) ? $res->userid : false;
   }
   return false;
}
function render_yes_no_option_lm($option_value, $label, $tooltip = '', $replace_yes_text = '', $replace_no_text = '', $replace_1 = '', $replace_0 = '')
{
    //die($replace_0);
    ob_start(); ?>
    <div class="form-group">
        <label for="<?php echo $option_value; ?>" class="control-label clearfix">
            <?php echo($tooltip != '' ? '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="' . _l($tooltip, '', false) . '"></i> ': '') . _l($label, '', false); ?>
        </label>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_1_<?php echo $label; ?>" name="settings[<?php echo $option_value; ?>]" value="1" <?php echo $option_value == $replace_1 ? 'checked': '';?>>
            <label for="y_opt_1_<?php echo $label; ?>">
                <?php echo $replace_yes_text == '' ? _l('settings_yes') : $replace_yes_text; ?>
            </label>
        </div>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_2_<?php echo $label; ?>" name="settings[<?php echo $option_value; ?>]" value="0" <?php echo $option_value == $replace_0 ? 'checked': '';?>>
            <label for="y_opt_2_<?php echo $label; ?>">
                <?php echo $replace_no_text == '' ? _l('settings_no') : $replace_no_text; ?>
            </label>
        </div>
    </div>
    <?php
    $settings = ob_get_contents();
    ob_end_clean();
    echo $settings;
}
function get_email_mailbox_configuration()
{
    $CI = &get_instance();
    $staff_id = get_staff_user_id();
    if (!class_exists('lead_manager_model')) {
        $CI->load->model('lead_manager/lead_manager_model');
    }
    return $CI->lead_manager_model->get_mail_box_configuration($staff_id);  
}
function handle_lead_manager_mail_box_attachments_array($staffid, $mailboxid, $index_name = 'attachments')
{
    $uploaded_files = [];
    $path           = LEAD_MANAGER_MAILBOX_FOLDER .'/'. $mailboxid . '/';
    $CI             = &get_instance();
    if (isset($_FILES[$index_name]['name'])
        && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }
        _file_attachments_index_fix($index_name);
        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            // Get the temp file path
            $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
                    || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                    continue;
            }
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
            $newFilePath = $path . $filename;
                // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                array_push($uploaded_files, [
                    'file_name' => $filename,
                    'filetype'  => $_FILES[$index_name]['type'][$i],
                ]);

                if (is_image($newFilePath)) {
                    create_img_thumb($path, $filename);
                }
            }
        }
    }
}

if (count($uploaded_files) > 0) {
    return $uploaded_files;
}
return false;
}
function get_lead_id_by_email($email)
{
   if($email){
       $CI = &get_instance();
       $CI->db->select('id');
       $CI->db->where(['email' => $email]);
       $res= $CI->db->get(db_prefix() . 'leads')->row();
       return ($res) ? $res->id : false;
   }
   return false;
}
function lead_manager_prepare_imap_email_body_html($body)
{
    $CI = &get_instance();
    $body = trim($body);
    $body = str_replace('&nbsp;', ' ', $body);
        // Remove html tags - strips inline styles also
    $body = trim(strip_html_tags($body, '<br/>, <br>, <a>'));
        // Once again do security
    $body = $CI->security->xss_clean($body);
        // Remove duplicate new lines
    $body = preg_replace("/[\r\n]+/", "\n", $body);
        // new lines with <br />
    $body = preg_replace('/\n(\s*\n)+/', '<br />', $body);
    $body = preg_replace('/\n/', '<br>', $body);
    return $body;
}
function check_lead_manager_mailbox_email_imap()
{
    $CI = &get_instance();
    if (!class_exists('lead_manager_model')) {
        $CI->load->model('lead_manager/lead_manager_model');
    }
    $check_every = get_option('lead_manager_imap_check_every');
    $last_run = get_option('lead_manager_imap_last_checked');
    $settings = $CI->lead_manager_model->get_mail_box_configuration();
    update_option('lead_manager_imap_last_checked',time());
    foreach ($settings as $setting) {
        $mail = (object) $setting;
        $last_sequence_id = $CI->lead_manager_model->get_mail_box_last_sequence($mail->staff_id);
        if ($mail->is_imap == '1') {
         if (empty($last_run) || (time() > $last_run + ($check_every * 60))) {
            $CI->load->model('spam_filters_model');
            $password = $mail->imap_password;
            if (!$password) {
                log_activity('Failed to decrypt email integration password, navigateo to Lead manager -> Mailbox -> setting and re-add the password.');
                continue;
            }
            $imap = new Imap(
             $mail->imap_user,
             $password,
             $mail->imap_server,
             $mail->imap_encryption
         );
            try {
                $connection = $imap->testConnection();
            } catch (ConnectionErrorException $e) {
                log_activity('Unable to connect IMAP Server! ' . $e->getMessage(), $mail->staff_id);
                continue;
            }
            if (empty($mail->folder)) {
                $mail->folder = stripos($mail->imap_server, 'outlook') !== false
                || stripos($mail->imap_server, 'microsoft')
                || stripos($mail->imap_server, 'office365') !== false ? 'Inbox' : 'INBOX';
            }
            $mailbox = $connection->getMailbox($mail->folder);
            $messages = NULL;
            if($last_sequence_id){
                $messages = $mailbox->getMessageSequence($last_sequence_id.':*');    
            }else{
                $messages = $mailbox->getMessages();
            }            
            include_once(APPPATH . 'third_party/simple_html_dom.php');
            foreach ($messages as $message) {
                if($message->getNumber() > $last_sequence_id){
                    $body = $message->getBodyHtml() ?? $message->getBodyText();
                    $html = str_get_html($body);
                    $formFields              = [];
                    $lead_form_custom_fields = [];
                    if ($html) {
                        foreach ($html->find('[id^="field_"],[id^="custom_field_"]') as $data) {
                            if (isset($data->plaintext)) {
                                $value = strip_tags(trim($data->plaintext));
                                if ($value && isset($data->attr['id']) && !empty($data->attr['id'])) {
                                    $formFields[$data->attr['id']] = $CI->security->xss_clean($value);
                                }
                            }
                        }
                    }

                    foreach ($formFields as $key => $val) {
                        $field = (strpos($key, 'custom_field_') !== false ? strafter($key, 'custom_field_') : strafter($key, 'field_'));

                        if (strpos($key, 'custom_field_') !== false) {
                            $lead_form_custom_fields[$field] = $val;
                        } elseif ($CI->db->field_exists($field, db_prefix() . 'leads')) {
                            $formFields[$field] = $val;
                        }

                        unset($formFields[$key]);
                    }

                    $fromAddress = null;
                    $fromName    = null;

                    if ($message->getFrom()) {
                        $fromAddress = $message->getFrom()->getAddress();
                        $fromName    = $message->getFrom()->getName();
                    }

                    $replyTo = $message->getReplyTo();

                    if (count($replyTo) === 1) {
                        $fromAddress = $replyTo[0]->getAddress();
                        $fromName    = $replyTo[0]->getName() ?? $fromName;
                    }

                    $fromAddress = $formFields['email'] ?? $fromAddress;
                    $fromName    = $formFields['name'] ?? $fromName;

                    /**
                     * Check the the fromAddress is null, perhaps invalid address?
                     * @see https://github.com/ddeboer/imap/issues/370
                     */
                    if (is_null($fromAddress)) {
                        $message->markAsSeen();

                        continue;
                    }

                    $mailstatus = $CI->spam_filters_model->check($fromAddress, $message->getSubject(), $body, 'leads');

                    if ($mailstatus) {
                        $message->markAsSeen();
                        log_activity('Lead Email Integration Blocked Email by Spam Filters [' . $mailstatus . ']');

                        continue;
                    }

                    $body = lead_manager_prepare_imap_email_body_html($body);

                    $mail_data = [
                        'fromName'                           => $fromName,
                        'staffid'                            => $mail->staff_id,
                        'toid'                               => get_lead_id_by_email($fromAddress),
                        'mail_date'                          => $message->getHeaders()->get('udate'),
                        'status'                             => 'get',
                        'from_email'                         => $fromAddress,
                        'to_email'                           => $mail->imap_user,
                        'subject'                            => $message->getSubject(),
                        'direction'                          => 'inbound',
                        'message'                            => $body,
                        'is_attachment'                      => count($message->getAttachments())>0 ? 1 : 0,
                        'is_read'                            => $message->isUnseen(),
                        'email_size'                         => $message->getHeaders()->get('size'),
                        'sequence_id'                        => $message->getNumber(),
                        'in_reply_to'                        => $message->getHeaders()->get('in_reply_to'),
                        'in_references'                      => $message->getHeaders()->get('references'),
                        'message_id'                         => $message->getHeaders()->get('message_id'),


                    ];
                    $CI->db->insert(db_prefix() . 'lead_manager_mailbox', $mail_data);
                    $insert_id = $CI->db->insert_id();
                    if ($insert_id) {
                        $message->markAsSeen();
                        
                        $CI->lead_manager_model->handleLeadManagerMailboxImapAttachments($message, $insert_id, $mail->staff_id);
                    }
                }
            }
        }   
    }   
}

}
function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
}
function get_file_icons($type)
{
    $icon = '';
    if($type == 'application/pdf'){
        $icon = '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>';
    }elseif ($type == 'image/png') {
        $icon = '<i class="fa fa-picture-o" aria-hidden="true"></i>';
    }elseif ($type == 'image/jpeg') {
        $icon = '<i class="fa fa-picture-o" aria-hidden="true"></i>';
    }elseif ($type == 'application/msword') {
        $icon = '<i class="fa fa-file-word-o" aria-hidden="true"></i>';
    }elseif ($type == 'application/vnd.ms-powerpoint') {
        $icon = '<i class="fa fa-file-powerpoint-o" aria-hidden="true"></i>';
    }elseif ($type == 'application/vnd.ms-excel') {
        $icon = '<i class="fa fa-file-excel-o" aria-hidden="true"></i>';
    }else{
        $icon = '<i class="fa fa-file-o" aria-hidden="true"></i>';
    }
    return $icon;
}
?>