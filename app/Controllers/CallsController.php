<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\ApiClient;

class CallsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ \App\Helpers\Url::redirect('/login'); } }
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

    public function history(){
        $this->requireAuth();
        $api = new ApiClient();
        $db = DB::conn();
        $user = $_SESSION['user'];
        $isSuper = ($user['role'] ?? '') === 'superadmin';
        $from = date('Y-m-d H:i:s', strtotime($_GET['from'] ?? '-1 day'));
        $to   = date('Y-m-d H:i:s', strtotime($_GET['to']   ?? 'now'));
        $src  = $_GET['src']  ?? '';
        $dst  = $_GET['dst']  ?? '';
        $maxPages = (int)($_GET['pages'] ?? 3); if ($maxPages<1) $maxPages=1; if ($maxPages>20) $maxPages=20;
        $statLimit = (int)($_GET['stat_limit'] ?? 25); if ($statLimit<10) $statLimit=10; if ($statLimit>100) $statLimit=100;
        $statPage = (int)($_GET['stat_page'] ?? 1); if ($statPage<1) $statPage=1;
        $selectedGroup = $isSuper ? (isset($_GET['group_id']) && $_GET['group_id']!=='' ? (int)$_GET['group_id'] : null) : (int)($user['group_id'] ?? 0);
        $results = [];
        $callStat = null;
        if (isset($_GET['search'])) {
            try {
                for ($p=1; $p<=$maxPages; $p++) {
                    $rows = $api->getCallHistoryFilter($from, $to, $src ?: null, $dst ?: null, $p);
                    if (!is_array($rows) || count($rows)===0) break;
                    foreach ($rows as $r) {
                        $ext = (string)($r['src'] ?? '');
                        if (!$isSuper) {
                            // filter by group via exten mapping
                            if ($ext!=='') {
                                $stmt=$db->prepare('SELECT group_id FROM users WHERE exten=? LIMIT 1'); $stmt->bind_param('s',$ext); $stmt->execute(); $g=$stmt->get_result()->fetch_assoc(); $stmt->close();
                                if (!$g || (int)$g['group_id'] !== $selectedGroup) { continue; }
                            } else { continue; }
                        } else if ($selectedGroup) {
                            if ($ext!=='') {
                                $stmt=$db->prepare('SELECT group_id FROM users WHERE exten=? LIMIT 1'); $stmt->bind_param('s',$ext); $stmt->execute(); $g=$stmt->get_result()->fetch_assoc(); $stmt->close();
                                if (!$g || (int)$g['group_id'] !== $selectedGroup) { continue; }
                            }
                        }
                        $results[] = $r;
                    }
                    if (count($rows) < 100) break;
                }
                // Get Call Stat from DB if super admin
                if ($isSuper) {
                    $statOffset = ($statPage - 1) * $statLimit;
                    $callStat = \App\Models\CallStat::getByDateRange($from, $to, $statLimit, $statOffset);
                    $statTotal = \App\Models\CallStat::getCount($from, $to);
                    $statTotalPages = ceil($statTotal / $statLimit);
                }
            } catch (\Throwable $e) { $results = ['error'=>$e->getMessage()]; $callStat = ['error'=>$e->getMessage()]; }
        }
        // group options for super admin
        $groups=[]; if ($isSuper) { if($res=$db->query('SELECT id,name FROM groups ORDER BY name')){ while($row=$res->fetch_assoc()){$groups[]=$row;} } }
        require __DIR__.'/../Views/calls/history.php';
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
            // Build exten -> group_id map from API users
            $userMap = [];
            try {
                $apiUsers = $api->getUsers();
                if (is_array($apiUsers)) {
                    foreach ($apiUsers as $au) {
                        $ext = (string)($au['exten'] ?? '');
                        $gid = isset($au['group_id']) ? (int)$au['group_id'] : 0;
                        if ($ext !== '') $userMap[$ext] = $gid;
                    }
                }
            } catch (\Throwable $e) { /* ignore, fallback to local mapping */ }

            // paginate through call history filter (100 rows per page)
            $page = 1;
            while (true) {
                $cdrs = $api->getCallHistoryFilter($from, $to, null, null, $page);
                if (!is_array($cdrs) || count($cdrs)===0) break;
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
                    $costApi = isset($cdr['cost']) ? (float)$cdr['cost'] : 0.0; // may be absent
                    // map to user/group by extension
                    $groupId = 0; $userId = 0;
                    if ($src) {
                        if (isset($userMap[$src])) {
                            $groupId = (int)$userMap[$src];
                        } else {
                            // fallback to local table
                            $stmt=$db->prepare('SELECT id, group_id FROM users WHERE exten=? LIMIT 1');
                            $stmt->bind_param('s',$src);
                            $stmt->execute();
                            $res=$stmt->get_result();
                            if ($u=$res->fetch_assoc()){ $userId=(int)$u['id']; $groupId=(int)$u['group_id']; }
                            $stmt->close();
                        }
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
                    $amountCharged = $costApi > 0 ? round($costApi * (1 + $margin/100), 6) : 0.0;

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
                if (count($cdrs) < 100) break; // page size reached end
                $page++;
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        // simple response
        header('Content-Type: application/json');
        echo json_encode(['imported'=>$imported,'errors'=>$errors], JSON_UNESCAPED_UNICODE);
    }

    public function record(){
        $this->requireAuth();
        $callId = $_GET['call_id'] ?? '';
        if ($callId === '') { http_response_code(400); echo 'call_id required'; return; }
        // Check permission: group admin only their group's calls
        $db = DB::conn();
        $stmt = $db->prepare('SELECT group_id FROM calls WHERE call_id=?');
        $stmt->bind_param('s', $callId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if ($row) {
            $gid = (int)$row['group_id'];
            if (!$this->isSuper() && $gid !== (int)$this->currentGroupId()) { http_response_code(403); echo 'Yetkisiz'; return; }
        } else {
            // No local record; superadmin can try fetch, group admin denied
            if (!$this->isSuper()) { http_response_code(404); echo 'Bulunamadı'; return; }
        }
        $api = new ApiClient();
        try {
            $wav = $api->getAudioRecord($callId);
            header('Content-Type: audio/x-wav');
            header('Content-Disposition: inline; filename="'.$callId.'.wav"');
            echo $wav;
        } catch (\Throwable $e) {
            http_response_code(502);
            echo 'Kayıt alınamadı: '.$e->getMessage();
        }
    }

    public function syncCallStats(){
        // Only for cron or superadmin, but assume cron
        $api = new ApiClient();
        $db = DB::conn();
        $from = date('Y-m-d 00:00:00'); // Today
        $to = date('Y-m-d 23:59:59');
        $last100 = \App\Models\CallStat::getLast100();
        $existingKeys = [];
        foreach ($last100 as $stat) {
            $key = $stat['user_login'] . '|' . $stat['date_from'] . '|' . $stat['date_to'];
            $existingKeys[$key] = $stat;
        }
        try {
            $stats = $api->getCallStat($from, $to);
            if (is_array($stats)) {
                foreach ($stats as $stat) {
                    $key = $stat['user_login'] . '|' . $from . '|' . $to;
                    if (!isset($existingKeys[$key])) {
                        // New stat, save
                        $marginCost = 0.0;
                        $groupId = null;
                        $voipExten = (string)($stat['voip_exten'] ?? '');
                        if ($voipExten !== '') {
                            $stmt = $db->prepare('SELECT group_id FROM users WHERE exten=? LIMIT 1');
                            $stmt->bind_param('s', $voipExten);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            if ($u = $res->fetch_assoc()) {
                                $groupId = (int)$u['group_id'];
                            }
                            $stmt->close();
                        }
                        $cost = (float)($stat['cost'] ?? 0.0);
                        if ($groupId && $cost > 0) {
                            $stmt = $db->prepare('SELECT margin FROM groups WHERE id=?');
                            $stmt->bind_param('i', $groupId);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $margin = 0.0;
                            if ($g = $res->fetch_assoc()) {
                                $margin = (float)$g['margin'];
                            }
                            $stmt->close();
                            $marginCost = round($cost * (1 + $margin / 100), 6);
                            // Deduct from balance
                            $db->begin_transaction();
                            $stmt = $db->prepare('UPDATE groups SET balance = balance - ? WHERE id=?');
                            $stmt->bind_param('di', $marginCost, $groupId);
                            $stmt->execute();
                            $stmt->close();
                            // Transaction
                            $type = 'debit_call_stat';
                            $ref = $stat['user_login'] . '_' . $from;
                            $desc = 'Call stat maliyeti';
                            $neg = -$marginCost;
                            $stmt = $db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?,?,?,?,?)');
                            $stmt->bind_param('isdss', $groupId, $type, $neg, $ref, $desc);
                            $stmt->execute();
                            $stmt->close();
                            $db->commit();
                        }
                        $data = $stat;
                        $data['date_from'] = $from;
                        $data['date_to'] = $to;
                        $data['margin_cost'] = $marginCost;
                        \App\Models\CallStat::save($data);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log error
            \App\Helpers\Logger::log('syncCallStats error: ' . $e->getMessage());
        }
        // Simple response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }

    public function syncHistoricalCallStats(){
        $this->requireAuth();
        if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $from = $_POST['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to = $_POST['to'] ?? date('Y-m-d');
        $api = new ApiClient();
        $db = DB::conn();
        $currentFrom = $from;
        $imported = 0;
        while ($currentFrom <= $to) {
            $currentTo = min($to, date('Y-m-d', strtotime($currentFrom . ' +1 day')));
            try {
                $stats = $api->getCallStat($currentFrom . ' 00:00:00', $currentTo . ' 23:59:59');
                if (is_array($stats)) {
                    foreach ($stats as $stat) {
                        // Check if exists
                        $stmt = $db->prepare('SELECT id FROM call_stats WHERE user_login=? AND date_from=? AND date_to=? LIMIT 1');
                        $stmt->bind_param('sss', $stat['user_login'], $currentFrom . ' 00:00:00', $currentTo . ' 23:59:59');
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows == 0) {
                            $marginCost = 0.0;
                            $groupId = null;
                            $voipExten = (string)($stat['voip_exten'] ?? '');
                            if ($voipExten !== '') {
                                $stmt2 = $db->prepare('SELECT group_id FROM users WHERE exten=? LIMIT 1');
                                $stmt2->bind_param('s', $voipExten);
                                $stmt2->execute();
                                $res = $stmt2->get_result();
                                if ($u = $res->fetch_assoc()) {
                                    $groupId = (int)$u['group_id'];
                                }
                                $stmt2->close();
                            }
                            $cost = (float)($stat['cost'] ?? 0.0);
                            if ($groupId && $cost > 0) {
                                $stmt2 = $db->prepare('SELECT margin FROM groups WHERE id=?');
                                $stmt2->bind_param('i', $groupId);
                                $stmt2->execute();
                                $res = $stmt2->get_result();
                                $margin = 0.0;
                                if ($g = $res->fetch_assoc()) {
                                    $margin = (float)$g['margin'];
                                }
                                $stmt2->close();
                                $marginCost = round($cost * (1 + $margin / 100), 6);
                            }
                            $data = $stat;
                            $data['date_from'] = $currentFrom . ' 00:00:00';
                            $data['date_to'] = $currentTo . ' 23:59:59';
                            $data['margin_cost'] = $marginCost;
                            \App\Models\CallStat::save($data);
                            $imported++;
                        }
                        $stmt->close();
                    }
                }
            } catch (\Throwable $e) {
                // Log error but continue
                \App\Helpers\Logger::log('syncHistoricalCallStats error for ' . $currentFrom . ': ' . $e->getMessage());
            }
            $currentFrom = date('Y-m-d', strtotime($currentFrom . ' +1 day'));
            // Prevent infinite loop
            if ($imported > 10000) break;
        }
        header('Content-Type: application/json');
        echo json_encode(['imported' => $imported], JSON_UNESCAPED_UNICODE);
    }
}
