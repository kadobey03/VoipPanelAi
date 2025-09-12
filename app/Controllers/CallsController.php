<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\ApiClient;

class CallsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ header('Location: /login'); exit; } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }
    private function currentGroupId(): ?int { return $_SESSION['user']['group_id'] ?? null; }

    public function index(){
        $this->requireAuth();
        $db=DB::conn();
        $gid = isset($_GET['group_id']) ? (int)$_GET['group_id'] : ($this->isSuper()? null : (int)$this->currentGroupId());
        if ($gid) {
            $stmt=$db->prepare('SELECT call_id, src, dst, start, duration, billsec, disposition, group_id, user_id, cost_api, margin_percent, amount_charged FROM calls WHERE group_id=? ORDER BY start DESC LIMIT 200');
            $stmt->bind_param('i',$gid);
            $stmt->execute();
            $res=$stmt->get_result();
            $stmt->close();
        } else {
            $res=$db->query('SELECT call_id, src, dst, start, duration, billsec, disposition, group_id, user_id, cost_api, margin_percent, amount_charged FROM calls ORDER BY start DESC LIMIT 200');
        }
        $calls=[]; while($row=$res->fetch_assoc()){$calls[]=$row;}
        require __DIR__.'/../Views/calls/index.php';
    }

    public function sync(){
        $this->requireAuth();
        if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $from = $_POST['from'] ?? date('Y-m-d H:i:s', time()-3600);
        $to   = $_POST['to']   ?? date('Y-m-d H:i:s');
        $api = new ApiClient();
        $db = DB::conn();
        $imported=0; $errors=[];
        try {
            $cdrs = $api->listCalls($from, $to); // expects array of CDRs
            foreach ($cdrs as $cdr) {
                $callId = $cdr['call_id'] ?? $cdr['uniqueid'] ?? null;
                if (!$callId) { continue; }
                // skip if exists
                $stmt=$db->prepare('SELECT call_id FROM calls WHERE call_id=?');
                $stmt->bind_param('s',$callId);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows>0) { $stmt->close(); continue; }
                $stmt->close();

                $src = $cdr['src'] ?? '';
                $dst = $cdr['dst'] ?? '';
                $start = $cdr['start'] ?? ($cdr['calldate'] ?? date('Y-m-d H:i:s'));
                $duration = (int)($cdr['duration'] ?? 0);
                $billsec = (int)($cdr['billsec'] ?? 0);
                $disp = $cdr['disposition'] ?? '';
                $costApi = (float)($cdr['cost'] ?? 0);
                // map to user/group by extension
                $groupId = null; $userId = null;
                if ($src) {
                    $stmt=$db->prepare('SELECT id, group_id FROM users WHERE exten=? LIMIT 1');
                    $stmt->bind_param('s',$src);
                    $stmt->execute();
                    $res=$stmt->get_result();
                    if ($u=$res->fetch_assoc()){ $userId=(int)$u['id']; $groupId=(int)$u['group_id']; }
                    $stmt->close();
                }
                if (!$groupId) { $groupId = 0; }

                // fetch group margin
                $margin=0.0;
                if ($groupId) {
                    $stmt=$db->prepare('SELECT margin FROM groups WHERE id=?');
                    $stmt->bind_param('i',$groupId);
                    $stmt->execute();
                    $res=$stmt->get_result();
                    if ($g=$res->fetch_assoc()) { $margin=(float)$g['margin']; }
                    $stmt->close();
                }
                $amountCharged = round($costApi * (1 + $margin/100), 6);

                // insert call
                $stmt=$db->prepare('INSERT INTO calls (call_id, src, dst, start, duration, billsec, disposition, group_id, user_id, cost_api, margin_percent, amount_charged) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->bind_param('ssssiisiiddd', $callId, $src, $dst, $start, $duration, $billsec, $disp, $groupId, $userId, $costApi, $margin, $amountCharged);
                $stmt->execute();
                $stmt->close();

                // deduct from group balance if group exists and call answered
                if ($groupId && $amountCharged>0 && in_array(strtoupper($disp), ['ANSWERED','ANSWER'])) {
                    $db->begin_transaction();
                    try{
                        $stmt=$db->prepare('UPDATE groups SET balance = balance - ? WHERE id=?');
                        $stmt->bind_param('di',$amountCharged,$groupId);
                        $stmt->execute();
                        $stmt->close();

                        $type='debit_call'; $ref=$callId; $desc='Cagri ucreti';
                        $neg = -$amountCharged;
                        $stmt=$db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?,?,?,?,?)');
                        $stmt->bind_param('isdss', $groupId, $type, $neg, $ref, $desc);
                        $stmt->execute();
                        $stmt->close();
                        $db->commit();
                    } catch(\Throwable $e){ $db->rollback(); $errors[]=$e->getMessage(); }
                }

                $imported++;
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        // simple response
        header('Content-Type: application/json');
        echo json_encode(['imported'=>$imported,'errors'=>$errors], JSON_UNESCAPED_UNICODE);
    }
}

