<?php defined('BASEPATH') or exit('No direct script access allowed');
$check =  __dir__ ;
$str= preg_replace('/\W\w+\s*(\W*)$/', '$1', $check);
$str.'/third_party/twilio-web/src/Twilio/autoload.php';
use Twilio\TwiML\VoiceResponse;
use Twilio\Rest\Client;

class Call_control extends CI_Controller{

    var $call_direction;

    public function __construct(){
        parent::__construct();
        hooks()->do_action('after_clients_area_init', $this);
    }

    public function get_staff_own_twilio_number()
    {
     $id=1;
     if($id){
         $CI = &get_instance();
         $CI->db->select('value');
         $CI->db->where(['relid'=>$id,'fieldto'=>'staff']);
         $res= $CI->db->get(db_prefix() . 'customfieldsvalues')->row();
         return ($res)?$res->value:'0';
     }
     return false;
 }

 public function handleCall(){

    $this->load->helper('lead_manager');
    $response = new VoiceResponse();
    $callerIdNumber = isset($_GET['callerIdNumber']) ? $_GET['callerIdNumber'] : get_option('call_twilio_phone_number');
    $leadid=(isset($_GET['leadId'])) ? $_GET['leadId'] : null;
    $phoneNumberToDial = isset($_GET['phoneNumber']) ? $_GET['phoneNumber'] : null;
    if(isset($callerIdNumber)){
     if(get_option('call_twilio_recording_active')){
         $dial = $response->dial('', ['callerId'=>$callerIdNumber, 'record' => 'record-from-ringing-dual',
            'recordingStatusCallback' => base_url().'lead_manager/Call_control/recordCall/'.$leadid,'recordingStatusCallbackMethod' => 'GET', 'recordingTrack' => 'both']);
         if (isset($phoneNumberToDial)) {
            $call_direction = 'outgoing';
            $dial->number($phoneNumberToDial);
        } else {
            $call_direction = 'incoming';
            $dial->client('support_agent');
        }
        header('Content-Type: text/xml');
        echo $response;
    }else{
        $dial = $response->dial('', ['callerId'=>$callerIdNumber]);
        if (isset($phoneNumberToDial)) {
            $call_direction = 'outgoing';
            $dial->number($phoneNumberToDial, ['statusCallback' => base_url().'lead_manager/Call_control/handleNotRecordedOutgoingCall/'.$leadid.'?call_direction='.$call_direction,'statusCallbackMethod'=> 'GET']);
        } else {
            $call_direction = 'incoming';
            $dial->client('support_agent', ['statusCallback' => base_url().'lead_manager/Call_control/handleNotRecordedIncomingCall/'.$leadid.'?call_direction='.$call_direction,'statusCallbackMethod'=> 'GET']);
        }
        header('Content-Type: text/xml');
        echo $response;
    }   
}
}

public function holding($value='')
{
  $response = new VoiceResponse();
  $response->say('Thank you for calling, a representative will be with you shortly');
  $response->redirect(base_url().'lead_manager/Call_control/holding');
  header('Content-Type: text/xml');
  echo $response;
}

public function handleNotRecordedIncomingCall($lead_id=null)
{
    $durationData = '';
    $call = '';
    $call_direction = $this->input->get('call_direction');
    $status = $this->input->get('CallStatus');
    if(!$lead_id){
        $pSid = $_GET['ParentCallSid'];
        $sid  = get_option('call_twilio_account_sid');
        $token  = get_option('call_twilio_auth_token');
        $twilio = new Client($sid, $token);
        $call = $twilio->calls($pSid)->fetch();
        $lead_id = $this->get_lead_id_by_number($call->from);
    }
    if($lead_id){
        if(isset($call) && !empty($call)){
            $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-volume-control-phone" aria-hidden="true"></i> <b>Call Start Time</b> : '.$call->startTime->format('Y-m-d H:i:s').'</h5></div>';
            $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-tty" aria-hidden="true"></i> <b>Call End Time</b> : '.$call->endTime->format('Y-m-d H:i:s').'</h5></div>';
            $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-info-circle" aria-hidden="true"></i> <b>Call Duration</b> : '.gmdate("H:i:s", $call->duration).' SEC</h5></div>';
        }else{
            $durationData = '<div class="task-info task-info-billable"><h5><i class="fa fa-info-circle" aria-hidden="true"></i>'._l('lead_manger_call_duration').': '.gmdate("H:i:s", $this->input->get('CallDuration')).' SEC</h5></div>';
        }
        $this->load->model('lead_manager_model');
        $lead = $this->lead_manager_model->get($lead_id);
        $last_contact = $this->lead_manager_model->update_last_contact($lead_id);
        $data['type'] = 'audio_call';
        $data['lead_id'] = $lead_id;
        $data['date'] = date("Y-m-d H:i:s");
        $data['description'] = $durationData;
        $data['additional_data'] = json_encode($this->input->get());
        $data['staff_id'] = $lead->assigned;
        $data['direction'] = $call_direction;
        $data['call_duration'] = $call->duration;
        $response = $this->lead_manager_model->lead_manger_activity_log($data);
    }
}
public function handleNotRecordedOutgoingCall($lead_id=null)
{
    $durationData = '';
    $call = '';
    $call_direction = $this->input->get('call_direction');
    $status = $this->input->get('CallStatus');
    if($lead_id){
        $pSid = $_GET['CallSid'];
        $sid  = get_option('call_twilio_account_sid');
        $token  = get_option('call_twilio_auth_token');
        $twilio = new Client($sid, $token);
        $call = $twilio->calls($pSid)->fetch();
        if(isset($call) && !empty($call)){
         $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-volume-control-phone" aria-hidden="true"></i> <b>Call Start Time</b> : '.$call->startTime->format('Y-m-d H:i:s').'</h5></div>';
         $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-tty" aria-hidden="true"></i> <b>Call End Time</b> : '.$call->endTime->format('Y-m-d H:i:s').'</h5></div>';
         $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-info-circle" aria-hidden="true"></i> <b>Call Duration</b> : '.gmdate("H:i:s", $call->duration).' SEC</h5></div>';
     }else{
        $durationData = '<div class="task-info task-info-billable"><h5><i class="fa fa-info-circle" aria-hidden="true"></i> <b>Call Duration</b> : '.gmdate("H:i:s", $this->input->get('CallDuration')).' SEC</h5></div>';
    }
    $this->load->model('lead_manager_model');
    $lead = $this->lead_manager_model->get($lead_id);
    $last_contact = $this->lead_manager_model->update_last_contact($lead_id);
    $data['type'] = 'audio_call';
    $data['lead_id'] = $lead_id;
    $data['date'] = date("Y-m-d H:i:s");
    $data['description'] = $durationData;
    $data['additional_data'] = json_encode($this->input->get());
    $data['staff_id'] = $lead->assigned;
    $data['direction'] = $call_direction;
    $data['call_duration'] = $call->duration;
    $response = $this->lead_manager_model->lead_manger_activity_log($data);
}
}

public function get_lead_id_by_number($number)
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

public function recordCall($lead_id=null){
    $call_direction = null;
    $data =array();
    if($lead_id){
        $pSid = $_GET['CallSid'];
        $sid  = get_option('call_twilio_account_sid');
        $token  = get_option('call_twilio_auth_token');
        $twilio = new Client($sid, $token);
        $call = $twilio->calls($pSid)->fetch();
        $call_direction = 'outgoing';
    }else{
       $pSid = $_GET['CallSid'];
       $sid  = get_option('call_twilio_account_sid');
       $token  = get_option('call_twilio_auth_token');
       $twilio = new Client($sid, $token);
       $call = $twilio->calls($pSid)->fetch();
       $lead_id = $this->get_lead_id_by_number($call->from);
       $call_direction = 'incoming';
   }
   if(isset($call) && !empty($call)){
    $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-volume-control-phone" aria-hidden="true"></i> <b>Call Start Time</b> : '.$call->startTime->format('Y-m-d H:i:s').'</h5></div>';
    $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-tty" aria-hidden="true"></i> <b>Call End Time</b> : '.$call->endTime->format('Y-m-d H:i:s').'</h5></div>';
    $durationData .= '<div class="task-info task-info-billable"><h5><i class="fa fa-info-circle" aria-hidden="true"></i> <b>Call Duration</b> : '.gmdate("H:i:s", $call->duration).' SEC</h5></div>';
}else{
    $durationData = '<div class="task-info task-info-billable"><h5><i class="fa fa-info-circle" aria-hidden="true"></i>'._l('lead_manger_call_duration').': '.gmdate("H:i:s", $this->input->get('CallDuration')).' SEC</h5></div>';
}
$this->load->model('lead_manager_model');
$lead = $this->lead_manager_model->get($lead_id);
$last_contact = $this->lead_manager_model->update_last_contact($lead_id);
$data['type'] = 'audio_call';
$data['is_audio_call_recorded'] = TRUE;
$data['lead_id'] = $lead_id;
$data['date'] = date("Y-m-d H:i:s");
$data['description'] = $durationData;
$data['additional_data'] = json_encode($this->input->get());
$data['staff_id'] = $lead->assigned;
$data['direction'] = $call_direction;
$data['call_duration'] = $this->input->get('RecordingDuration');
$response = $this->lead_manager_model->lead_manger_activity_log($data);
echo $response;

}
public function getCallDetials($get){
    $sid = $get['AccountSid'];
    $token = get_option('call_twilio_auth_token');
    $callSid = $get['CallSid'];
    $twilio = new Client($sid, $token);
    $call = $twilio->calls($callSid)->fetch();
    return $call->to;
}
public function getCallDetialsByCallSid($callSid){
    $sid = 'AC1e491928aec1ddac82f49d5bbf13f616';
    $token = get_option('call_twilio_auth_token');
    $twilio = new Client($sid, $token);
    $call = $twilio->calls($callSid)->fetch();
    //echo $this->get_lead_id_by_number($call->from);
    print_r($call);
}
public function holdCall(){
    $client = new Client(get_option('call_twilio_account_sid'), get_option('call_twilio_auth_token'));
    $calls = $client->calls->read(
        array("ParentCallSid" => $_POST['CallSid'])
    );
    $twilioCall = '';
    header('Content-Type: text/xml');
    foreach ($calls as $call) {
        $twilioCall = $client->calls($call->sid)->update(
            array(
                "url" => admin_url().'lead_manager/Call_control/holdQueue',
                "method" => "POST"
            )
        );
        echo $twilioCall->to;
    }
}

public function unholdCall()
{
  $client = new Client(get_option('call_twilio_account_sid'), get_option('call_twilio_auth_token'));
  $calls = $client->calls->read(
    array("ParentCallSid" => $_POST['CallSid'])
);
  $twilioCall = '';
  header('Content-Type: text/xml');
  foreach ($calls as $call) {
    $twilioCall = $client->calls($call->sid)->update(
        array(
            "url" => admin_url().'lead_manager/Call_control/unholdQueue',
            "method" => "POST"
        )
    );
    echo $twilioCall->to;
}
}

public function unholdQueue($value='')
{
  $response = new VoiceResponse();
  $dial = $response->dial('', ['action' => admin_url().'lead_manager/Call_control/holding']);
  $dial->queue('support_agent');
  header('Content-Type: text/xml');
  echo $response;
}

public function waitUrl()
{
    $response = new VoiceResponse();
    $response->play('https://api.twilio.com/cowbell.mp3', ['loop' => 100]);
    header('Content-Type: text/xml');
    return $response;
}

public function holdQueue()
{
    $response = new VoiceResponse();
    $response->enqueue('support_agent', ['waitUrl' => 'https://api.twilio.com/cowbell.mp3']);
    header('Content-Type: text/xml');
    echo $response;

}

public function busyIncommingCalls()
{
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
            $staffId = $this->get_staff_by_twilio_number($record->to);
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
                $pcalls = $twilio->calls->read(["parentCallSid" => $data[$i]['sid']], 1);
                if(isset($pcalls) && !empty($pcalls)){
                    foreach($pcalls as $childcall){
                        $data[$i]['child_status'] = $childcall->status;
                    }
                }
                if($data[$i]['status'] == 'busy' || $data[$i]['child_status'] == 'no-answer' || $data[$i]['status'] == 'failed' || $data[$i]['child_status'] == 'failed' || $data[$i]['child_status'] == 'busy' || $data[$i]['status'] == 'no-answer')
                {
                    $this->addMissedCalls($data, $i);
                }                 
            } 
            $i++;
        }
    }   
}

public function get_staff_by_twilio_number($number)
{
 if($number){
     $CI = &get_instance();
     $CI->db->select('relid');
     $CI->db->where(['value'=>$number,'fieldto'=>'staff']);
     $res= $CI->db->get(db_prefix() . 'customfieldsvalues')->row();
     return ($res) ? $res->relid : '0';
 }
 return false;
}
public function get_lead_name_by_number($number)
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

public function addMissedCalls($data, $j)
{
    $CI = &get_instance();
    $CI->db->where('call_sid', $data[$j]['sid']);
    $q = $CI->db->get(db_prefix() . 'lead_manager_missed_calls');
    $leadName = $this->get_lead_name_by_number($data[$j]['from']);
    if($q->num_rows() == 0){
        $insert_data = array(
            'staff_id' => $data[$j]['staff_id'],
            'call_sid' => $data[$j]['sid'],
            'staff_twilio_number' => $data[$j]['to'],
            'date' => $data[$j]['dateCreated'],
        );
        $this->db->insert(db_prefix() . 'lead_manager_missed_calls',$insert_data);
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
       $this->db->insert(db_prefix() . 'notifications',$notifcationArr);
   }
}

public function getFromNumberByChildCallSid()
{
  $childCallSid = $_POST["CallSid"];
  $data = [];
  if($childCallSid){
    $sid  = get_option('call_twilio_account_sid');
    $token  = get_option('call_twilio_auth_token');
    $twilio = new Client($sid, $token);
    $childCall = $twilio->calls($childCallSid)->fetch();
    $parentCallSid = $childCall->parentCallSid;
    if($parentCallSid){
        $pcalls = $twilio->calls($parentCallSid)->fetch();
        if(isset($pcalls) && !empty($pcalls)){
            $leadName = $this->get_lead_name_by_number($pcalls->from);
            if($leadName){
                $data['from'] = $leadName;
            }else{
                $data['from'] = $pcalls->from;
            }
            $data['to'] = $pcalls->to;
        }
        echo json_encode($data); exit;
    }
}
}

public function active_twilio_account()
{
   $sid  = get_option('call_twilio_account_sid');
   $token  = get_option('call_twilio_auth_token');
   $response = array();
   $twilio = new Client($sid, $token);
   $incomingPhoneNumbers = $twilio->incomingPhoneNumbers
   ->read([]);
   $response['numbers'] = count($incomingPhoneNumbers);
   $account = $twilio->api->v2010->accounts($sid)
   ->fetch();
   $response['balance'] = $this->active_twilio_account_curl($account->subresourceUris['balance']);
   return $response;                 
}

public function active_twilio_account_curl($url)
{
   $sid  = get_option('call_twilio_account_sid');
   $token  = get_option('call_twilio_auth_token');
   $curl = curl_init();
   curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.twilio.com/'.$url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Basic QUMxZTQ5MTkyOGFlYzFkZGFjODJmNDlkNWJiZjEzZjYxNjo3Mzg3ZmJhN2YyNDZhMWJjZjQyZWY1MGE5MTE2OGE0Ng=='
    ),
  ));
   $response = curl_exec($curl);
   curl_close($curl);
   return json_decode($response);

}
}