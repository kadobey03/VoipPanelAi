<?php
if (!isset($_GET['token']) || $_GET['token'] !== 'debug2026') {
    die('Yetkisiz');
}

// .env yükle
if (file_exists(__DIR__.'/.env')) {
    foreach (file(__DIR__.'/.env') as $line) {
        $line = trim($line);
        if ($line && strpos($line, '=') !== false && $line[0] !== '#') {
            putenv($line);
        }
    }
    echo "<p style='color:green'>✅ .env dosyası bulundu ve yüklendi</p>";
} else {
    echo "<p style='color:red'>❌ .env dosyası YOK!</p>";
}

$host = getenv('DB_HOST');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_DATABASE');

echo "<p><b>DB_HOST:</b> $host</p>";
echo "<p><b>DB_USERNAME:</b> $user</p>";
echo "<p><b>DB_DATABASE:</b> $db</p>";
echo "<p><b>DB_PASSWORD:</b> " . (empty($pass) ? '(BOŞ)' : '(dolu, ' . strlen($pass) . ' karakter)') . "</p>";

// Bağlantı dene
$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo "<p style='color:red'>❌ DB BAĞLANTI HATASI: " . htmlspecialchars($mysqli->connect_error) . "</p>";
    die();
}
echo "<p style='color:green'>✅ Veritabanı bağlantısı başarılı</p>";
$mysqli->set_charset('utf8mb4');

// users tablosunu kontrol et
$res = $mysqli->query('SELECT id, login, role, password FROM users WHERE login="admin" LIMIT 1');
if (!$res) {
    echo "<p style='color:red'>❌ users tablosu sorgu hatası: " . $mysqli->error . "</p>";
    die();
}
$admin = $res->fetch_assoc();
if (!$admin) {
    echo "<p style='color:orange'>⚠️ 'admin' kullanıcısı bulunamadı!</p>";
    
    // Tüm kullanıcıları listele
    $res2 = $mysqli->query('SELECT id, login, role FROM users LIMIT 10');
    echo "<p>Mevcut kullanıcılar:</p><ul>";
    while ($row = $res2->fetch_assoc()) {
        echo "<li>ID:{$row['id']} - {$row['login']} ({$row['role']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:green'>✅ Admin kullanıcısı bulundu: ID={$admin['id']}, Role={$admin['role']}</p>";
    $hashPreview = substr($admin['password'], 0, 7);
    echo "<p><b>Hash başlangıcı:</b> $hashPreview...</p>";
    
    // Hash geçerli mi?
    if ($hashPreview === '$2y$10$') {
        echo "<p style='color:green'>✅ Hash formatı geçerli (bcrypt)</p>";
    } else {
        echo "<p style='color:red'>❌ Hash formatı GEÇERSİZ! Değer: " . htmlspecialchars(substr($admin['password'], 0, 30)) . "</p>";
    }
}

// Test şifresi dene
if (isset($_POST['test_pass']) && $admin) {
    $result = password_verify($_POST['test_pass'], $admin['password']);
    if ($result) {
        echo "<p style='color:green;font-size:18px;font-weight:bold'>✅ ŞİFRE DOĞRU! Login çalışmalı.</p>";
    } else {
        echo "<p style='color:red;font-size:18px;font-weight:bold'>❌ Şifre yanlış veya hash bozuk.</p>";
    }
}

$mysqli->close();
?>

<hr>
<h3>Şifre Test Et (admin için):</h3>
<form method="post" action="?token=debug2026">
    <input type="text" name="test_pass" placeholder="Test edilecek şifre" style="padding:8px;width:300px">
    <button type="submit" style="padding:8px 16px;background:#2563eb;color:white;border:none;border-radius:4px">Test Et</button>
</form>