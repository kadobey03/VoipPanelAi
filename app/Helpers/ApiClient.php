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
        $ch = curl_init($this->baseUrl.'?loc='.$loc);
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
    }

    public function getBalance() {
        $res = $this->post('voip_api_get_balance');
        return json_decode($res, true);
    }

    public function addBalance($amount) {
        // Confirm exact endpoint/params with API PDF
        $res = $this->post('voip_api_add_balance', [
            'amount' => $amount,
        ]);
        return json_decode($res, true);
    }

    // Diğer API fonksiyonları buraya eklenecek
}

