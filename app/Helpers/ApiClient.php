<?php
namespace App\Helpers;
class ApiClient {
    private $apiKey;
    private $baseUrl = 'https://panel.momvoip.com/';
    public function __construct() {
        $this->apiKey = getenv('API_KEY');
    }
    private function post($loc, $data = []) {
        $data['apikey'] = $this->apiKey;
        $ch = curl_init($this->baseUrl.'?loc='.$loc);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    public function getBalance() {
        $res = $this->post('voip_api_get_balance');
        return json_decode($res, true);
    }
    // Diğer API fonksiyonları buraya eklenecek
}
