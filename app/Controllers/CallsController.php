<?php
namespace App\Controllers;

use App\Helpers\DB;
use App\Helpers\ApiClient;

class CallsController {
    private function startSession(){ if(session_status()===PHP_SESSION_NONE) session_start(); }
    private function requireAuth(){ $this->startSession(); if(!isset($_SESSION['user'])){ \App\Helpers\Url::redirect('/login'); } }
    private function isSuper(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='superadmin'; }
    private function isGroupMember(): bool { return isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='groupmember'; }
    private function currentGroupId(): ?int { return $_SESSION['user']['group_id'] ?? null; }

    public function index(){
        $this->requireAuth();
        $db=DB::conn();
        $gid = isset($_GET['group_id']) ? (int)$_GET['group_id'] : ($this->isSuper()? null : (int)$this->currentGroupId());
        // groups map for display (by local id and api_group_id)
        $groupNamesById = [];
        $groupNamesByApi = [];
        if ($res=$db->query('SELECT id, api_group_id, name FROM groups')){ while($r=$res->fetch_assoc()){ $groupNamesById[(int)$r['id']]=$r['name']; if(!empty($r['api_group_id'])){$groupNamesByApi[(int)$r['api_group_id']]=$r['name'];} } }
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
        $db = DB::conn();
        $user = $_SESSION['user'];
        $isSuper = ($user['role'] ?? '') === 'superadmin';
        $isGroupMember = ($user['role'] ?? '') === 'groupmember';

        // Groupmember için session'da agent_id yoksa veritabanından çek
        if ($isGroupMember && !isset($user['agent_id'])) {
            $userId = (int)$user['id'];
            $stmt = $db->prepare('SELECT agent_id FROM users WHERE id=?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            if ($r && $r['agent_id']) {
                $_SESSION['user']['agent_id'] = $r['agent_id'];
                $user['agent_id'] = $r['agent_id'];
            }
            $stmt->close();
        }
        $from = date('Y-m-d H:i:s', strtotime($_GET['from'] ?? '-1 day'));
        $to   = date('Y-m-d H:i:s', strtotime($_GET['to']   ?? 'now'));
        $src  = trim($_GET['src']  ?? '');
        $dst  = trim($_GET['dst']  ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = min(200, max(10, (int)($_GET['per'] ?? 100)));
        $offset = ($page-1)*$per;
        $selectedGroup = $isSuper ? (isset($_GET['group_id']) && $_GET['group_id']!=='' ? (int)$_GET['group_id'] : null) : (int)($user['group_id'] ?? 0);

        $where = 'start BETWEEN ? AND ?';
        $types = 'ss';
        $params = [$from, $to];
        if (!$isSuper) { $where .= ' AND group_id=?'; $types.='i'; $params[] = (int)($user['group_id'] ?? 0); }
        if (($user['role'] ?? '') === 'user') { $where .= ' AND src=?'; $types.='s'; $params[] = $user['exten'] ?? ''; }
        if ($isGroupMember) {
            // Groupmember için agent_id'den exten al
            $agentExten = '';
            $agentId = isset($user['agent_id']) ? (int)$user['agent_id'] : 0;
            error_log("Groupmember agent_id: " . $agentId . ", user_id: " . $user['id']);
            if ($agentId > 0) {
                $stmt = $db->prepare('SELECT exten FROM agents WHERE id=?');
                $stmt->bind_param('i', $agentId);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                if ($r) {
                    $agentExten = $r['exten'];
                    error_log("Found agent exten: " . $agentExten . " for agent_id: " . $agentId);
                } else {
                    error_log("Agent not found for agent_id: " . $agentId);
                }
                $stmt->close();
            } else {
                error_log("No agent_id found for groupmember with user_id: " . $user['id']);
            }
            if (!empty($agentExten)) {
                $where .= ' AND src=?';
                $types.='s';
                $params[] = $agentExten;
                error_log("Applying filter: src=" . $agentExten . ", group_id=" . ($user['group_id'] ?? 0));
            } else {
                error_log("No agent exten found, applying only group filter for groupmember");
            }
        }
        if ($isSuper && $selectedGroup) { $where .= ' AND group_id=?'; $types.='i'; $params[] = $selectedGroup; }
        if ($src !== '') { $where .= ' AND src LIKE ?'; $types.='s'; $params[] = $src.'%'; }
        if ($dst !== '') { $where .= ' AND dst LIKE ?'; $types.='s'; $params[] = $dst.'%'; }

        // Count
        $stmt = $db->prepare("SELECT COUNT(*) c FROM calls WHERE $where");
        $stmt->bind_param($types, ...$params);
        $stmt->execute(); $total = (int)$stmt->get_result()->fetch_assoc()['c']; $stmt->close();
        $totalPages = max(1, (int)ceil($total / $per)); if ($page > $totalPages) { $page = $totalPages; $offset = ($page-1)*$per; }

        // Data
        $stmt = $db->prepare("SELECT call_id, src, dst, start, duration, billsec, disposition, group_id, user_id, cost_api, margin_percent, amount_charged FROM calls WHERE $where ORDER BY start DESC LIMIT $per OFFSET $offset");
        $stmt->bind_param($types, ...$params);
        $stmt->execute(); $res = $stmt->get_result();
        $calls=[]; while($row=$res->fetch_assoc()){$calls[]=$row;} $stmt->close();

        // groups map for display (and options for super admin)
        $groupNamesById=[]; $groupNamesByApi=[]; $groups=[];
        if($r=$db->query('SELECT id, api_group_id, name FROM groups ORDER BY name')){ while($rw=$r->fetch_assoc()){ $groupNamesById[(int)$rw['id']]=$rw['name']; if(!empty($rw['api_group_id'])){$groupNamesByApi[(int)$rw['api_group_id']]=$rw['name'];} $groups[]=$rw; } }

        require __DIR__.'/../Views/calls/history.php';
    }

    // Cron-friendly sync: daily window, secure with token
    public function syncCron(){
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $expected = getenv('CRON_TOKEN') ?: '';
        if (!$expected || $token !== $expected) { http_response_code(403); echo 'Forbidden'; return; }
        // Default: last 24 hours (yesterday this time -> now)
        $from = $_GET['from'] ?? $_POST['from'] ?? date('Y-m-d H:i:s', time()-86400);
        $to   = $_GET['to']   ?? $_POST['to']   ?? date('Y-m-d H:i:s');
        // Reuse sync logic by inline call (duplicate of sync but without auth/session)
        $api = new ApiClient();
        $db = DB::conn();
        $imported=0; $skipped=0; $errors=[]; $trace=[]; $pages=0;
        $debug = isset($_GET['debug']) ? (($_GET['debug']==='1') || (strtolower((string)$_GET['debug'])==='true')) : false;
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
            } catch (\Throwable $e) { /* ignore */ }

            $page = 1;
            while (true) {
                $cdrs = $api->getCallHistoryFilter($from, $to, null, null, $page);
                $pages++;
                if ($debug) { $trace[] = ['request'=>['from'=>$from,'to'=>$to,'page'=>$page], 'response'=>$cdrs]; }
                if (!is_array($cdrs) || count($cdrs)===0) break;
                foreach ($cdrs as $cdr) {
                    $callId = $cdr['call_id'] ?? $cdr['uniqueid'] ?? null;
                    if (!$callId) { continue; }
                    $stmt=$db->prepare('SELECT call_id FROM calls WHERE call_id=?');
                    $stmt->bind_param('s',$callId);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows>0) { $stmt->close(); $skipped++; continue; }
                    $stmt->close();

                    $src = $cdr['src'] ?? '';
                    $dst = $cdr['dst'] ?? '';
                    $start = $cdr['start'] ?? ($cdr['calldate'] ?? date('Y-m-d H:i:s'));
                    $duration = (int)($cdr['duration'] ?? 0);
                    $billsec = (int)($cdr['billsec'] ?? 0);
                    $disp = $cdr['disposition'] ?? '';
                    // Try multiple possible cost keys
                    $costApi = 0.0;
                    if (isset($cdr['cost'])) { $costApi = (float)$cdr['cost']; }
                    elseif (isset($cdr['price'])) { $costApi = (float)$cdr['price']; }
                    elseif (isset($cdr['call_cost'])) { $costApi = (float)$cdr['call_cost']; }
                    elseif (isset($cdr['charge'])) { $costApi = (float)$cdr['charge']; }
                    $groupId = 0; $userId = 0;
                    if ($src) {
                        if (isset($userMap[$src])) { $groupId = (int)$userMap[$src]; }
                        else { $stmt=$db->prepare('SELECT id, group_id FROM users WHERE exten=? LIMIT 1'); $stmt->bind_param('s',$src); $stmt->execute(); $r=$stmt->get_result(); if($u=$r->fetch_assoc()){ $userId=(int)$u['id']; $groupId=(int)$u['group_id']; } $stmt->close(); }
                    }
                    if (!$groupId) { $groupId = 0; }
                    $margin=0.0;
                    if ($groupId) { $stmt=$db->prepare('SELECT margin FROM groups WHERE id=?'); $stmt->bind_param('i',$groupId); $stmt->execute(); $r=$stmt->get_result(); if($g=$r->fetch_assoc()){ $margin=(float)$g['margin']; } $stmt->close(); }
                    $amountCharged = $costApi > 0 ? round($costApi * (1 + $margin/100), 6) : 0.0;
                    $stmt=$db->prepare('INSERT INTO calls (call_id, src, dst, start, duration, billsec, disposition, group_id, user_id, cost_api, margin_percent, amount_charged) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
                    $stmt->bind_param('ssssiisiiddd', $callId, $src, $dst, $start, $duration, $billsec, $disp, $groupId, $userId, $costApi, $margin, $amountCharged);
                    $stmt->execute(); $stmt->close();
                    if ($groupId && $amountCharged>0 && in_array(strtoupper($disp), ['ANSWERED','ANSWER'])) {
                        $db->begin_transaction();
                        try{
                            $stmt=$db->prepare('UPDATE groups SET balance = balance - ? WHERE id=?');
                            $stmt->bind_param('di',$amountCharged,$groupId);
                            $stmt->execute(); $stmt->close();
                            $type='debit_call'; $ref=$callId; $desc='Cagri ucreti'; $neg=-$amountCharged;
                            $stmt=$db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?,?,?,?,?)');
                            $stmt->bind_param('isdss', $groupId, $type, $neg, $ref, $desc);
                            $stmt->execute(); $stmt->close();
                            $db->commit();
                        } catch(\Throwable $e){ $db->rollback(); $errors[]=$e->getMessage(); }
                    }
                    $imported++;
                }
                if (count($cdrs) < 100) break;
                $page++;
            }
        } catch (\Throwable $e) { $errors[]=$e->getMessage(); }
        // Distribute Call Plane total cost per agent to answered calls by billsec
        try {
            $stats = $api->getCallStat($from, $to);
            if (is_array($stats)) {
                foreach ($stats as $st) {
                    $ext = (string)($st['voip_exten'] ?? $st['user_login'] ?? '');
                    $totalCost = (float)($st['cost'] ?? 0);
                    if ($ext === '' || $totalCost <= 0) continue;
                    $stmt=$db->prepare("SELECT call_id, billsec, group_id, amount_charged, margin_percent FROM calls WHERE src=? AND start BETWEEN ? AND ? AND UPPER(disposition) IN ('ANSWERED','ANSWER')");
                    $stmt->bind_param('sss',$ext,$from,$to);
                    $stmt->execute(); $res=$stmt->get_result();
                    $rows=[]; $sum=0; while($r=$res->fetch_assoc()){ $rows[]=$r; $sum += (int)$r['billsec']; }
                    $stmt->close();
                    if ($sum <= 0 || count($rows)===0) continue;
                    foreach ($rows as $r) {
                        $share = $totalCost * ((int)$r['billsec'] / $sum);
                        $gidRaw = (int)$r['group_id']; $margin = (float)$r['margin_percent']; $localGid = $gidRaw;
                        if ($margin == 0.0 || $gidRaw == 0) {
                            $stmt=$db->prepare('SELECT id, margin FROM groups WHERE id=? OR api_group_id=? ORDER BY id LIMIT 1');
                            $stmt->bind_param('ii',$gidRaw,$gidRaw);
                            $stmt->execute(); $g=$stmt->get_result()->fetch_assoc(); $stmt->close();
                            if ($g){ $localGid=(int)$g['id']; $margin=(float)$g['margin']; }
                        } else {
                            $stmt=$db->prepare('SELECT id FROM groups WHERE id=? OR api_group_id=? ORDER BY id LIMIT 1');
                            $stmt->bind_param('ii',$gidRaw,$gidRaw); $stmt->execute(); $g=$stmt->get_result()->fetch_assoc(); $stmt->close(); if($g){$localGid=(int)$g['id'];}
                        }
                        $amount = $share > 0 ? round($share * (1 + $margin/100), 6) : 0.0;
                        $old = (float)$r['amount_charged']; $delta = $amount - $old;
                        $stmt=$db->prepare('UPDATE calls SET cost_api=?, margin_percent=?, amount_charged=?, group_id=? WHERE call_id=?');
                        $stmt->bind_param('dddis', $share, $margin, $amount, $localGid, $r['call_id']);
                        $stmt->execute(); $stmt->close();
                        if ($localGid && abs($delta) > 0.000001) {
                            $db->begin_transaction();
                            try{
                                $stmt=$db->prepare('UPDATE groups SET balance = balance - ? WHERE id=?');
                                $stmt->bind_param('di', $delta, $localGid); $stmt->execute(); $stmt->close();
                                $type = ($old > 0) ? 'debit_call_adj' : 'debit_call';
                                $ref = $r['call_id']; $desc = ($old > 0) ? 'Cagri tutar guncelleme' : 'Cagri ucreti'; $neg = -$delta;
                                $stmt=$db->prepare('INSERT INTO transactions (group_id, type, amount, reference, description) VALUES (?,?,?,?,?)');
                                $stmt->bind_param('isdss', $localGid, $type, $neg, $ref, $desc); $stmt->execute(); $stmt->close();
                                $db->commit();
                            } catch(\Throwable $e){ $db->rollback(); $errors[]='dist:'.$e->getMessage(); }
                        }
                    }
                }
            }
        } catch (\Throwable $e) { $errors[]='callstat:'.$e->getMessage(); }
        header('Content-Type: application/json'); echo json_encode([
            'from'=>$from,
            'to'=>$to,
            'pages'=>$pages,
            'imported'=>$imported,
            'skipped_existing'=>$skipped,
            'errors'=>$errors,
            'trace'=>$debug ? $trace : null
        ], JSON_UNESCAPED_UNICODE);
    }
    public function sync(){
        $this->requireAuth();
        if (!$this->isSuper()) { http_response_code(403); echo 'Yetkisiz'; return; }
        $from = $_POST['from'] ?? date('Y-m-d H:i:s', time()-86400);
        $to   = $_POST['to']   ?? date('Y-m-d H:i:s');
        $api = new ApiClient();
        $db = DB::conn();
        $imported=0; $skipped=0; $errors=[]; $trace=[]; $pages=0;
        $debug = isset($_GET['debug']) ? (($_GET['debug']==='1') || (strtolower((string)$_GET['debug'])==='true')) : false;
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
                $pages++;
                if ($debug) { $trace[] = ['request'=>['from'=>$from,'to'=>$to,'page'=>$page], 'response'=>$cdrs]; }
                if (!is_array($cdrs) || count($cdrs)===0) break;
                foreach ($cdrs as $cdr) {
                    $callId = $cdr['call_id'] ?? $cdr['uniqueid'] ?? null;
                    if (!$callId) { continue; }
                    // skip if exists
                    $stmt=$db->prepare('SELECT call_id FROM calls WHERE call_id=?');
                    $stmt->bind_param('s',$callId);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows>0) { $stmt->close(); $skipped++; continue; }
                    $stmt->close();

                    $src = $cdr['src'] ?? '';
                    $dst = $cdr['dst'] ?? '';
                    $start = $cdr['start'] ?? ($cdr['calldate'] ?? date('Y-m-d H:i:s'));
                    $duration = (int)($cdr['duration'] ?? 0);
                    $billsec = (int)($cdr['billsec'] ?? 0);
                    $disp = $cdr['disposition'] ?? '';
                    $costApi = 0.0; // may be absent
                    if (isset($cdr['cost'])) { $costApi = (float)$cdr['cost']; }
                    elseif (isset($cdr['price'])) { $costApi = (float)$cdr['price']; }
                    elseif (isset($cdr['call_cost'])) { $costApi = (float)$cdr['call_cost']; }
                    elseif (isset($cdr['charge'])) { $costApi = (float)$cdr['charge']; }
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

        // verbose response
        header('Content-Type: application/json');
        echo json_encode([
            'from'=>$from,
            'to'=>$to,
            'pages'=>$pages,
            'imported'=>$imported,
            'skipped_existing'=>$skipped,
            'errors'=>$errors,
            'trace'=>$debug ? $trace : null
        ], JSON_UNESCAPED_UNICODE);
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
        $from = date('Y-m-d 00:00:00', strtotime('-1 day')); // Yesterday
        $to = date('Y-m-d 23:59:59', strtotime('-1 day'));
        $last100 = \App\Models\CallStat::getLast100();
        $existingKeys = [];
        foreach ($last100 as $stat) {
            $key = $stat['user_login'] . '|' . $stat['date_from'] . '|' . $stat['date_to'];
            $existingKeys[$key] = $stat;
        }
        $added = 0;
        $requestData = ['sdate' => $from, 'edate' => $to, 'apikey' => '***'];
        try {
            $stats = $api->getCallStat($from, $to);
            $statCount = is_array($stats) ? count($stats) : 0;
            if (is_array($stats)) {
                foreach ($stats as $stat) {
                    if (!is_array($stat)) continue;
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
                        $added++;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log error
            \App\Helpers\Logger::log('syncCallStats error: ' . $e->getMessage());
            $statCount = 0;
            $error = $e->getMessage();
            $stats = null;
        }
        // Response
        header('Content-Type: application/json');
        echo json_encode([
            'request' => $requestData,
            'response' => $stats,
            'stat_count' => $statCount ?? 0,
            'added' => $added,
            'error' => $error ?? null
        ], JSON_UNESCAPED_UNICODE);
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
                        if (!is_array($stat)) continue;
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
