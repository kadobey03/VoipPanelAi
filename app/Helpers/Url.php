<?php
namespace App\Helpers;

class Url {
    public static function basePath(): string {
        $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
        if ($base === '/' || $base === '.') return '';
        return $base;
    }
    public static function to(string $path): string {
        if ($path === '' || $path[0] !== '/') { $path = '/'.$path; }
        return self::basePath().$path;
    }
    public static function redirect(string $path): void {
        header('Location: '.self::to($path));
        exit;
    }
}

