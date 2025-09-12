<?php
namespace App\Helpers;
class Cache {
    private static $cacheDir = __DIR__ . '/../../storage/cache/';
    private static function ensurePath(){ if (!is_dir(self::$cacheDir)) @mkdir(self::$cacheDir, 0777, true); }
    public static function set($key, $value, $ttl = 300) {
        self::ensurePath();
        $data = ['value' => $value, 'expires' => time() + $ttl];
        file_put_contents(self::$cacheDir . md5($key), serialize($data));
    }
    public static function get($key) {
        self::ensurePath();
        $file = self::$cacheDir . md5($key);
        if (!file_exists($file)) return null;
        $data = unserialize(@file_get_contents($file));
        if (!$data || !isset($data['expires'])) return null;
        if ($data['expires'] < time()) { @unlink($file); return null; }
        return $data['value'];
    }
}
