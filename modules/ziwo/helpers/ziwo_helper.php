<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('before_cron_run', 'get_live_calls');
hooks()->add_filter('cron_functions_execute_seconds', 'ziwo_cron_functions_execute_seconds');
//Call started
//{
//    "result": true,
//    "content": {
//        "callInfo": {},
//        "queueInfo": {
//            "callID": "cd68171d-b33a-483f-b382-483a67223876",
//            "didCalled": "00971568427546",
//            "callerPosition": "agent-0029",
//            "callerID": "agent-0029",
//            "callerName": "Outbound Call",
//            "calleeID": "00971568427546",
//            "calleeName": "00971568427546",
//            "calleePosition": "",
//            "createdEPOCH": "1740415763",
//            "calleeDirection": "outbound",
//            "callState": "active",
//            "calleeCallState": "active",
//            "direction": "inbound",
//            "channelBridgeEPOCH": "1740415771",
//            "originalCallerIdName": "00971568427546",
//            "originalCallerIdNumber": "00971568427546"
//        }
//    },
//    "info": {}
//}


//Call tranfered

//{
//    "result": true,
//    "content": {
//        "callInfo": {},
//        "queueInfo": {
//            "callID": "d4d8c47c-945e-447b-a2cd-8eed7455507f",
//            "didCalled": "0026",
//            "callerPosition": "",
//            "callerID": "00971568427546",
//            "callerName": "Outbound Call",
//            "calleeID": "0026",
//            "calleeName": "0026",
//            "calleePosition": "agent-0026",
//            "createdEPOCH": "1740415769",
//            "calleeDirection": "outbound",
//            "callState": "active",
//            "calleeCallState": "active",
//            "direction": "outbound",
//            "channelBridgeEPOCH": "1740415803"
//        }
//    },
//    "info": {}
//}
function get_live_calls(){
    $ci = &get_instance();
    $ci->load->model('staff_model');
    $ci->load->model('leads_model');

    $agents = $ci->staff_model->get();
    $apiKey = '959ebdcc-2386-45e4-8941-f5e98c7edfd1';
    $apiBaseUrl = 'https://qsm-api.aswat.co/integrations/cti/agents/';

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'api_key: ' . $apiKey,
            'Content-Type: application/json',
        ],
    ]);

    foreach ($agents as $agent) {
        $agentId = $agent['ziwo_agent_id'] ?? null;

        if (!$agentId) {
            continue; // Skip agents without a Ziwo ID
        }
        try {
            // Set the URL dynamically for each agent
            curl_setopt($curl, CURLOPT_URL, $apiBaseUrl . $agentId . '/call');
            $response = curl_exec($curl);

            // Handle cURL errors
            if (curl_errno($curl)) {
                log_activity('[ZIWO INTEGRATION] cURL error: ' . curl_error($curl));
                continue;
            }

            $responseData = json_decode($response, true);
            if (!isset($responseData['result']) || !$responseData['result']) {
                continue;
            }

            $queueInfo = $responseData['content']['queueInfo'] ?? null;
            if (!$queueInfo) {
                continue;
            }
            $transfer = false;

            if ($queueInfo['callerID'] == 'agent-'.$agentId) //initiate the call
            {
                $phoneNumber = $queueInfo['calleeID'];
            }else{
                $phoneNumber = $queueInfo['callerID'];
                $transfer = true;
            }

            if (!$transfer) {
                continue;
            }

            $last9Digits = substr($phoneNumber, -9);

            // Fetch lead using the last 9 digits of the phone number
            $lead = $ci->db->select('id')
                ->from(db_prefix() . 'leads')
                ->where("SUBSTRING(phonenumber, -9) =", $last9Digits)
                ->get()
                ->row_array();


            if ($lead) {
                $data = ['lastcontact' => to_sql_date(date('Y-m-d H:i:s'), true)];

                if ($transfer){
                    $data['source'] = 57;
                }

                if ($lead['assigned'] == $agent['staffid']) {
                    continue;
                }

                $data['assigned'] = $agent['staffid'];

                $ci->db->where('id', $lead['id'])
                    ->update(db_prefix() . 'leads', $data);

                if ($lead['assigned'] != $agent['staffid']) {
                    $ci->leads_model->lead_assigned_member_notification($lead['id'], $agent['staffid'], true);
                    log_activity('[ZIWO INTEGRATION] Ziwo assigned lead '.$lead['id'].' to : ' . $agent['staffid']);
                }
            }

        } catch (Exception $e) {
            log_activity('[ZIWO INTEGRATION] Error: ' . $e->getMessage());
        }
    }
}

function ziwo_cron_functions_execute_seconds($seconds)
{
    return 60;
}