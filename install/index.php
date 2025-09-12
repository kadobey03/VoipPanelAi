<?php
session_start();
if (file_exists(__DIR__.'/../.env') && isset($_GET['done'])) {
    echo '<h2>Kurulum tamamlandı. <a href="/">Panele git</a></h2>';
    exit;
}
function envWrite($data) {
    $env = '';
    foreach ($data as $k => $v) {
        $env .= "$k=$v\n";
    }
    file_put_contents(__DIR__.'/../.env', $env);
}
function importSQL($host, $db, $user, $pass, $sqlFile) {
    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_errno) return $mysqli->connect_error;
    $sql = file_get_contents($sqlFile);
    $mysqli->multi_query($sql);
    do { } while ($mysqli->more_results() && $mysqli->next_result());
    return $mysqli->error ? $mysqli->error : true;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $admin_user = $_POST['admin_user'];
    $admin_pass = password_hash($_POST['admin_pass'], PASSWORD_BCRYPT);
    $api_key = $_POST['api_key'];
    // .env yaz
    envWrite([
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'APP_KEY' => bin2hex(random_bytes(16)),
        'DB_HOST' => $db_host,
        'DB_PORT' => '3306',
        'DB_DATABASE' => $db_name,
        'DB_USERNAME' => $db_user,
        'DB_PASSWORD' => $db_pass,
        'API_KEY' => $api_key
    ]);
    // DB oluştur
    $mysqli = new mysqli($db_host, $db_user, $db_pass);
    $mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $mysqli->close();
    // SQL import
    $sqlFile = __DIR__.'/../install.sql';
    $err = importSQL($db_host, $db_name, $db_user, $db_pass, $sqlFile);
    if ($err === true) {
        // Admin şifresini güncelle
        $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
        $stmt = $mysqli->prepare("UPDATE users SET login=?, password=? WHERE id=1");
        $stmt->bind_param('ss', $admin_user, $admin_pass);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
        header('Location: index.php?done=1');
        exit;
    } else {
        $error = $err;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>PapaM VoIP Panel Kurulum</title>
    <link href="/assets/css/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded shadow w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-4">PapaM VoIP Panel Kurulum</h2>
    <?php if (isset($error)): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">Hata: <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-2">
            <label class="block">Veritabanı Sunucusu</label>
            <input name="db_host" class="border p-2 w-full" required value="localhost">
        </div>
        <div class="mb-2">
            <label class="block">Veritabanı Adı</label>
            <input name="db_name" class="border p-2 w-full" required>
        </div>
        <div class="mb-2">
            <label class="block">Veritabanı Kullanıcı Adı</label>
            <input name="db_user" class="border p-2 w-full" required>
        </div>
        <div class="mb-2">
            <label class="block">Veritabanı Şifresi</label>
            <input name="db_pass" type="password" class="border p-2 w-full">
        </div>
        <div class="mb-2">
            <label class="block">Admin Kullanıcı Adı</label>
            <input name="admin_user" class="border p-2 w-full" required value="admin">
        </div>
        <div class="mb-2">
            <label class="block">Admin Şifresi</label>
            <input name="admin_pass" type="password" class="border p-2 w-full" required>
        </div>
        <div class="mb-2">
            <label class="block">API Key</label>
            <input name="api_key" class="border p-2 w-full" required value="b14rrNepNDrAb2hMgfJWD8ia81LJaEMe">
        </div>
        <button class="mt-4 w-full bg-blue-600 text-white p-2 rounded" type="submit">Kurulumu Başlat</button>
    </form>
</div>
</body>
</html>
