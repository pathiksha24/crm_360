<?php

class Ziwo extends AdminController
{
    public function __construct()
    {
        parent::__construct();

    }

    public function get_live_calls(){
        get_live_calls();
        die();
        $agents = $this->getZiwoAgents();
        $this->load->model('leads_model');
        foreach ($agents as $agent) {
            if ($agent['ziwo_agent_id']) {
                try {
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://qsm-api.aswat.co/integrations/cti/agents/'.$agent['ziwo_agent_id'].'/call',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            'api_key: 959ebdcc-2386-45e4-8941-f5e98c7edfd1',
                            'Content-Type: application/json'
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);
                    $response = json_decode($response, true);
                    if ($response['result'] == true){
                        if (isset($response['content']['queueInfo']) && !empty($response['content']['queueInfo'])){
                            $liveCallInfo = $response['content']['queueInfo'];
                            $phonenumber = $liveCallInfo['calleeID'];

                            //Get Lead
                            $this->db->group_start();
                            //$this->db->where("SUBSTRING(whatsapp_number, -9) =", substr($phonenumber, -9));
                            $this->db->or_where("SUBSTRING(phonenumber, -9) =", substr($phonenumber, -9));
                            $this->db->group_end();
                            $lead = $this->db->from(db_prefix() . 'leads')->get()->row_array();
                            if ($lead){
                                if ($lead['assigned'] == $agent['staffid']) continue;

                                $this->db->where('id', $lead['id']);
                                $this->db->update(db_prefix() . 'leads', [
                                    'assigned' => $agent['staffid'],
                                ]);

                                $this->leads_model->lead_assigned_member_notification($lead['id'], $agent['staffid'], true);
                            }
                        }
                    }
                }catch (Exception $e){
                    log_activity('[ZIWO INTEGRATION] error : '.$e->getMessage());
                }
            }
        }
    }

    public function make_call($lead_id){

        $this->load->model('leads_model');
        $this->load->model('staff_model');
        $lead = $this->leads_model->get($lead_id);

        if (is_null($lead)) {
            echo json_encode(['success' => false, 'message' => 'Lead not found']);die();
        }

        if ($lead->assigned ==0 || $lead->assigned == '') {
            echo json_encode(['success' => false, 'message' => 'Lead not Assigned']);die();
        }
        $agent = $this->staff_model->get($lead->assigned);

        if (is_null($agent)) {
            echo json_encode(['success' => false, 'message' => 'Agent not found']);die();
        }

        if ($agent->ziwo_agent_id == 0 || $agent->ziwo_agent_id == '' || $agent->ziwo_agent_id == null) {
            echo json_encode(['success' => false, 'message' => 'Ziwo Agent ID is missing']);die();
        }

        if ($lead->phonenumber == 0 || $lead->phonenumber == '' || $lead->phonenumber == null) {
            echo json_encode(['success' => false, 'message' => 'Lead Phonenumber not found']);die();
        }
        $phonenumber = $this->formatPhoneNumber($lead->phonenumber);

        $curl = curl_init();

        $api_key = '959ebdcc-2386-45e4-8941-f5e98c7edfd1';

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://qsm-api.aswat.co/integrations/cti/agents/'.$agent->ziwo_agent_id.'/call',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'number='.$phonenumber,
            CURLOPT_HTTPHEADER => array(
                'api_key: '.$api_key,
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response, true);
        $message = 'Call success';
        $success = true;
        if ($response['result'] == false){
            $message = $response['error']['message'];
            $success = false;
        }
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
    }

    function formatPhoneNumber($number) {
        // Remove any non-numeric characters
        $number = preg_replace('/\D/', '', $number);

        // If the number starts with "00" already, return as is
        if (strpos($number, '00') === 0) {
            return $number;
        }

        // If the number starts with "0" (local format), you may choose how to handle it
        if (strpos($number, '0') === 0 && strlen($number) > 10) {
            return '00' . substr($number, 1);
        }

        // If the number starts with a country code (e.g., "971" for UAE), prepend "00"
        if (strpos($number, '00') !== 0 && strpos($number, '0') !== 0) {
            return '00' . $number;
        }

        return $number;
    }

    private function getZiwoAgents()
    {
        get_live_calls();
        die();
        $this->load->model('staff_model');
        return $this->staff_model->get();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://qsm-api.aswat.co/admin/agents',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'access_token: e7d07b48-d8dd-48b7-800d-ad13f69491b3'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
}