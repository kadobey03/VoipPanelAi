<?php
namespace App\Helpers;

class Url {
    public static function basePath(): string {
        // Check if we're in a subdirectory
        $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/');

        // If running from subdomain or different structure, return empty base
        if ($base === '/' || $base === '.' || $base === '') return '';

        // For subdomains like eticaret.akkocbilisim.com, use empty base
        if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.') !== false) {
            $hostParts = explode('.', $_SERVER['HTTP_HOST']);
            if (count($hostParts) > 2) {
                return '';
            }
        }

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

