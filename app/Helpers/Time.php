<?php
namespace App\Helpers;

class Time {
    public static function applyOffset(string $dt, int $minutes): string {
        if ($minutes === 0) return $dt;
        $ts = strtotime($dt);
        if ($ts === false) return $dt;
        return date('Y-m-d H:i:s', $ts + ($minutes * 60));
    }
}

