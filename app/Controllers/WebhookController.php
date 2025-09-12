<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\Logger;

class WebhookController {
    public function momvoip(){
        // Expect JSON
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) { http_response_code(400); echo 'Invalid JSON'; return; }
        // verify apikey
        $apiKey = getenv('API_KEY') ?: '';
        if (!isset($data['apikey']) || $data['apikey'] !== $apiKey) { http_response_code(403); echo 'Forbidden'; return; }
        $event = $data['event'] ?? '';
        Logger::log('Webhook event: '.$event.' payload='.json_encode($data));

        if ($event === 'call_end') {
            $callId = (string)($data['call_id'] ?? '');
            if ($callId !== '') {
                $db = \App\Helpers\DB::conn();
                // Upsert minimal fields
                $src = (string)($data['src'] ?? '');
                $dst = (string)($data['dst'] ?? '');
                $start = (string)($data['start'] ?? date('Y-m-d H:i:s'));
                $duration = (int)($data['duration'] ?? 0);
                $billsec = (int)($data['billsec'] ?? 0);
                $disp = (string)($data['disposition'] ?? '');

                $stmt = $db->prepare('SELECT call_id FROM calls WHERE call_id=?');
                $stmt->bind_param('s', $callId);
                $stmt->execute();
                $stmt->store_result();
                $exists = $stmt->num_rows>0; $stmt->close();

                if ($exists) {
                    $stmt = $db->prepare('UPDATE calls SET src=?, dst=?, start=?, duration=?, billsec=?, disposition=? WHERE call_id=?');
                    $stmt->bind_param('ssssiis', $src, $dst, $start, $duration, $billsec, $disp, $callId);
                    $stmt->execute(); $stmt->close();
                } else {
                    $stmt = $db->prepare('INSERT INTO calls (call_id, src, dst, start, duration, billsec, disposition, group_id, user_id) VALUES (?,?,?,?,?,?,?,?,?)');
                    $zero=0; $stmt->bind_param('ssssiisii', $callId, $src, $dst, $start, $duration, $billsec, $disp, $zero, $zero);
                    $stmt->execute(); $stmt->close();
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['ok'=>true]);
    }
}

