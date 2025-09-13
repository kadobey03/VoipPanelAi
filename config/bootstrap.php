<?php
// Autoloading
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
// Load .env
if (file_exists(__DIR__.'/../.env')) {
    $lines = file(__DIR__.'/../.env');
    foreach ($lines as $line) {
        if (trim($line) && strpos($line, '=') !== false) {
            putenv(trim($line));
        }
    }
}
// Set PHP default timezone if provided
$tz = getenv('APP_TZ');
if ($tz) {
    @date_default_timezone_set($tz);
}
// Error handling & logging
require_once __DIR__.'/../app/Core/ErrorHandler.php';
$debugEnv = getenv('APP_DEBUG');
$debug = is_string($debugEnv) ? in_array(strtolower($debugEnv), ['1','true','on','yes'], true) : false;
\App\Core\ErrorHandler::register($debug);

// Lightweight migrations (safe ALTERs)
try {
    if (class_exists('App\\Helpers\\DB')) {
        \App\Helpers\DB::migrate();
    }
} catch (Throwable $e) {
    // Log but do not break the app
    if (class_exists('App\\Helpers\\Logger')) { \App\Helpers\Logger::log('Migration error: '.$e->getMessage()); }
}

// Language setup
include __DIR__.'/../app/Helpers/Lang.php';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['tr', 'en'])) {
    \App\Helpers\Lang::set($_GET['lang']);
}
\App\Helpers\Lang::load(\App\Helpers\Lang::current());
