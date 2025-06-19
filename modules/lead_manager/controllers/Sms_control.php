<?php defined('BASEPATH') or exit('No direct script access allowed');
$check =  __dir__ ;
$str= preg_replace('/\W\w+\s*(\W*)$/', '$1', $check);
$str.'/third_party/twilio-web/src/Twilio/autoload.php';
use Twilio\TwiML\MessagingResponse;

class Sms_control extends CI_Controller{

    public function __construct(){

        parent::__construct();
    }

    public function Incoming_sms()
    {
        $this->load->helper('lead_manager');
        $insert = [];
        if($this->input->get()){
            $insert['msg_service_id'] = $this->input->get('MessagingServiceSid');
            $insert['msg_sid'] = $this->input->get('MessageSid');
            $insert['from_number'] = $this->input->get('From');
            $insert['to_number'] = $this->input->get('To');
            $insert['from_id'] = get_lead_id_by_number($this->input->get('From'));
            $insert['to_id'] = get_staff_by_twilio_number($this->input->get('To'));
            $insert['sms_direction'] = 'incoming';
            $insert['sms_status'] = $this->input->get('SmsStatus');
            $insert['sms_body'] = $this->input->get('Body');
            $insert['api_response'] = json_encode($this->input->get());
            $CI = &get_instance();
            $res= $CI->db->insert(db_prefix() . 'lead_manager_conversation',$insert);
            die($res);
        }
    }
    public function Incoming_sms_failed()
    {
     $insert = [];
     if($this->input->get()){
        $insert['api_response'] = json_encode($this->input->get());
        $CI = &get_instance();
        $res= $CI->db->insert(db_prefix() . 'lead_manager_conversation',$insert);
        die($res);
    }
}

public function incoming_sms_status_webhook()
{
    if($this->input->post()){
        $post_data = $this->input->post();
        $CI = &get_instance();
        $CI->db->where(['msg_sid' => $post_data['MessageSid']]);
        $CI->db->update(db_prefix() . 'lead_manager_conversation',['sms_status' => $post_data['SmsStatus']]);
    }
}
public function handleReply()
{
    if($this->input->post()){
        $post_data = $this->input->post();
        $CI = &get_instance();
        $CI->db->where(['msg_sid' => $post_data['MessageSid']]);
        $CI->db->update(db_prefix() . 'lead_manager_conversation',['sms_status' => $post_data['SmsStatus']]);
        echo $CI->db->affected_rows();
    }
}

public function send_sms()
{
    $response = new MessagingResponse();
    $response->message('hello how are you',['to' => '+919453974798','from' => '+18044947745','action' => 'https://zonvoirdemo.in/newcrm/admin/lead_manager/sms_control/status_send_sms', 'method' => 'GET']);
    header('Content-Type: text/xml');
    echo $response;
}
public function status_send_sms()
{
    echo $this->input->get('MessageStatus');
}

public function incoming_sms_webhook()
{
    $this->load->helper('lead_manager');
    $insert = [];
    $res = 0;
    if($this->input->get()){
        $clientid = get_client_id_by_number($this->input->get('From'));
        $leadid = get_lead_id_by_number($this->input->get('From'));
        if($leadid){
            $insert['msg_sid'] = $this->input->get('SmsMessageSid');
            $insert['from_number'] = $this->input->get('From');
            $insert['to_number'] = $this->input->get('To');
            $insert['from_id'] = $leadid;
            $insert['to_id'] = get_staff_by_twilio_number($this->input->get('To'));
            $insert['sms_direction'] = 'incoming';
            $insert['sms_status'] = $this->input->get('SmsStatus');
            $insert['sms_body'] = $this->input->get('Body');
            $insert['api_response'] = json_encode($this->input->get());
            $insert['is_client'] = 0;
            $insert['is_read'] = 'no';
            $CI = &get_instance();
            $res= $CI->db->insert(db_prefix() . 'lead_manager_conversation',$insert);
        }
        if($clientid){
            $insert['msg_sid'] = $this->input->get('SmsMessageSid');
            $insert['from_number'] = $this->input->get('From');
            $insert['to_number'] = $this->input->get('To');
            $insert['from_id'] = $clientid;
            $insert['to_id'] = get_staff_by_twilio_number($this->input->get('To'));
            $insert['sms_direction'] = 'incoming';
            $insert['sms_status'] = $this->input->get('SmsStatus');
            $insert['sms_body'] = $this->input->get('Body');
            $insert['api_response'] = json_encode($this->input->get());
            $insert['is_client'] = 1;
            $insert['is_read'] = 'no';
            $CI = &get_instance();
            $res= $CI->db->insert(db_prefix() . 'lead_manager_conversation',$insert);
        }
        die($res);
    }
}

    public function wp_leads_webhook(){
            file_put_contents('test.json', json_encode($this->input->post()));
    }
}