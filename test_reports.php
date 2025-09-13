<?php
require_once __DIR__.'/config/bootstrap.php';

// Test ReportsController without web server
try {
    // Simulate a groupmember session
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Test as groupmember
    $_SESSION['user'] = [
        'id' => 1,
        'role' => 'groupmember',
        'exten' => '1001',
        'group_id' => 1
    ];

    $db = \App\Helpers\DB::conn();

    // Test the base where clause logic
    $from = date('Y-m-d 00:00:00', strtotime('-6 days'));
    $to = date('Y-m-d 23:59:59');

    $baseWhere = 'c.start BETWEEN ? AND ?';
    $baseTypes = 'ss';
    $baseParams = [$from, $to];

    // Add groupmember src filter
    $userExten = '1001';
    if (!empty($userExten)) {
        $baseWhere .= ' AND c.src=?';
        $baseTypes .= 's';
        $baseParams[] = $userExten;
    }

    echo "Test Results:\n";
    echo "Base Where: $baseWhere\n";
    echo "Base Types: $baseTypes\n";
    echo "Base Params Count: " . count($baseParams) . "\n";

    // Test agent stats query
    $agentWhere = $baseWhere;
    $agentTypes = $baseTypes;
    $agentParams = $baseParams;

    $sql3 = "SELECT u.login AS user_login, cg.name AS group_name, u.exten AS voip_exten,
                    COUNT(*) AS calls, SUM(CASE WHEN UPPER(c.disposition) IN ('ANSWERED','ANSWER') THEN 1 ELSE 0 END) AS answer
             FROM calls c
             LEFT JOIN users u ON u.exten=c.src
             LEFT JOIN groups cg ON (cg.id=c.group_id OR cg.api_group_id=c.group_id)
             WHERE $agentWhere GROUP BY u.login, cg.name, u.exten ORDER BY calls DESC LIMIT 5";

    echo "\nAgent Stats SQL:\n$sql3\n";
    echo "Agent Types: $agentTypes\n";
    echo "Agent Params Count: " . count($agentParams) . "\n";

    $stmt = $db->prepare($sql3);
    $stmt->bind_param($agentTypes, ...$agentParams);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "\nAgent Stats Results:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- Login: {$row['user_login']}, Exten: {$row['voip_exten']}, Calls: {$row['calls']}\n";
    }

    $stmt->close();
    $db->close();

    echo "\nTest completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>