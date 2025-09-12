<?php
namespace App\Helpers;

class ApiClient {
    private $apiKey;
    private $baseUrl;

    public function __construct() {
        $this->apiKey = getenv('API_KEY') ?: '';
        $this->baseUrl = getenv('API_BASE_URL') ?: 'https://panel.momvoip.com/';
        if (!$this->apiKey) {
            // Development fallback; set via .env in production
            $this->apiKey = 'b14rrNepNDrAb2hMgfJWD8ia81LJaEMe';
        }
    }

    private function post($loc, $data = []) {
        $data['apikey'] = $this->apiKey;
        $url = $this->baseUrl.'?loc='.$loc;
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $response = curl_exec($ch);
            if ($response === false) {
                $err = curl_error($ch);
                curl_close($ch);
                throw new \RuntimeException('API request failed: '.$err);
            }
            curl_close($ch);
            return $response;
        } else {
            // Fallback without cURL
            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query($data)
                ]
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                throw new \RuntimeException('API request failed (no cURL)');
            }
            return $response;
        }
    }

    // 7) Get Customer balance
    public function getBalance() {
        $res = $this->post('voip_api_get_balance');
        return json_decode($res, true);
    }

    // 1) Create call from user to client
    public function createCall($userId, $number, $exNumber = null) {
        $payload = ['user_id' => $userId, 'number' => $number];
        if ($exNumber !== null && $exNumber !== '') { $payload['ex_number'] = $exNumber; }
        $res = $this->post('voip_api_call', $payload);
        return json_decode($res, true);
    }

    // 2) Get agents status information
    public function getAgentsStatus() {
        $res = $this->post('voip_api_get_status');
        return json_decode($res, true);
    }

    // 3) Get agent status information
    public function getAgentStatus($userId) {
        $res = $this->post('voip_api_get_status_exten', [ 'user_id' => $userId ]);
        return json_decode($res, true);
    }

    // 4) Get external numbers
    public function getExternalNumbers() {
        $res = $this->post('voip_api_get_numbers');
        return json_decode($res, true);
    }

    // 5) Set status external number “ACTIVE”
    public function setNumberActive($number) {
        $res = $this->post('voip_api_number_set_active', [ 'number' => $number ]);
        return json_decode($res, true);
    }

    // 6) Set status external number “SPAM”
    public function setNumberSpam($number) {
        $res = $this->post('voip_api_number_set_spam', [ 'number' => $number ]);
        return json_decode($res, true);
    }

    // 8) Get call history by agent (10 rows per page)
    public function getCallHistoryByAgent($userId, $page = 1) {
        $res = $this->post('voip_api_get_call_history', [ 'user_id' => $userId, 'page' => $page ]);
        return json_decode($res, true);
    }

    // 10) Download file call audio record (returns raw WAV)
    public function getAudioRecord($callId) {
        return $this->post('voip_api_get_audio_record', [ 'call_id' => $callId ]);
    }

    // 11) Get “Call Plane” data
    public function getCallStat($sdate, $edate) {
        $res = $this->post('voip_api_get_call_stat', [ 'sdate' => $sdate, 'edate' => $edate ]);
        return json_decode($res, true);
    }

    // 12) Get Users
    public function getUsers() {
        $res = $this->post('voip_api_get_users');
        return json_decode($res, true);
    }

    // 13) Get Groups
    public function getGroups() {
        $res = $this->post('voip_api_get_groups');
        return json_decode($res, true);
    }

    // 14) Get call history, filter (100 rows per page)
    public function getCallHistoryFilter($sdate, $edate, $src = null, $dst = null, $page = 1) {
        $payload = ['sdate' => $sdate, 'edate' => $edate, 'page' => $page];
        if ($src) $payload['src'] = $src;
        if ($dst) $payload['dst'] = $dst;
        $res = $this->post('voip_api_get_call_history_filter', $payload);
        return json_decode($res, true);
    }
}
