<?php
namespace App\Models;

class CallStat {
    public static function save(array $data) {
        $db = \App\Helpers\DB::conn();
        $stmt = $db->prepare('INSERT INTO call_stats (user_login, group_name, calls, answer, unique_numbers, duration, billsec, talk_percent, jackpot, unique_jackpot, spy_calls, spy_duration, promt_calls, promt_duration, echo_calls, echo_duration, cost, margin_cost, voip_exten, date_from, date_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE calls=VALUES(calls), answer=VALUES(answer), unique_numbers=VALUES(unique_numbers), duration=VALUES(duration), billsec=VALUES(billsec), talk_percent=VALUES(talk_percent), jackpot=VALUES(jackpot), unique_jackpot=VALUES(unique_jackpot), spy_calls=VALUES(spy_calls), spy_duration=VALUES(spy_duration), promt_calls=VALUES(promt_calls), promt_duration=VALUES(promt_duration), echo_calls=VALUES(echo_calls), echo_duration=VALUES(echo_duration), cost=VALUES(cost), margin_cost=VALUES(margin_cost)');
        $stmt->bind_param('ssiiiiidiiiiiiiiiddssss', 
            $data['user_login'], $data['group_name'], $data['calls'], $data['answer'], $data['unique_numbers'], $data['duration'], $data['billsec'], $data['talk_percent'], $data['jackpot'], $data['unique_jackpot'], $data['spy_calls'], $data['spy_duration'], $data['promt_calls'], $data['promt_duration'], $data['echo_calls'], $data['echo_duration'], $data['cost'], $data['margin_cost'], $data['voip_exten'], $data['date_from'], $data['date_to']
        );
        $stmt->execute();
        $stmt->close();
    }

    public static function getLast100() {
        $db = \App\Helpers\DB::conn();
        $res = $db->query('SELECT * FROM call_stats ORDER BY id DESC LIMIT 100');
        $stats = [];
        while ($row = $res->fetch_assoc()) {
            $stats[] = $row;
        }
        return $stats;
    }
}