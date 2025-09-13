<?php
namespace App\Core;

use App\Helpers\Logger;

class ErrorHandler {
    public static function register(bool $debug = false): void {
        // PHP error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        // Convert PHP errors to exceptions
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) { return false; }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // Uncaught exceptions
        set_exception_handler(function ($e) use ($debug) {
            $msg = sprintf('%s: %s in %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
            $trace = $e->getTraceAsString();
            Logger::log($msg."\n".$trace);
            if (!headers_sent()) {
                http_response_code(500);
            }
            if ($debug) {
                self::renderDebugError($e);
            } else {
                echo 'Internal Server Error';
            }
        });

        // Shutdown handler for fatal errors
        register_shutdown_function(function () use ($debug) {
            $err = error_get_last();
            if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $msg = sprintf('FatalError: %s in %s:%d', $err['message'], $err['file'], $err['line']);
                Logger::log($msg);
                if (!headers_sent()) http_response_code(500);
                if ($debug) {
                    echo '<pre style="padding:12px;background:#fee;color:#900;border:1px solid #fcc;">'.$msg.'</pre>';
                } else {
                    echo 'Internal Server Error';
                }
            }
        });
    }

    private static function renderDebugError(\Throwable $e): void {
        $html = '<!doctype html><meta charset="utf-8"><title>Error</title>';
        $html .= '<div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;padding:16px">';
        $html .= '<h2 style="margin:0 0 8px">'.htmlspecialchars(get_class($e)).'</h2>';
        $html .= '<div style="color:#b91c1c;margin-bottom:8px">'.htmlspecialchars($e->getMessage()).'</div>';
        $html .= '<div style="font-size:12px;color:#555;margin-bottom:8px">'.htmlspecialchars($e->getFile()).':'.$e->getLine().'</div>';
        $html .= '<pre style="white-space:pre-wrap;background:#f8fafc;border:1px solid #e5e7eb;padding:12px;border-radius:6px">'.htmlspecialchars($e->getTraceAsString()).'</pre>';
        // request context
        $html .= '<h3 style="margin-top:16px">Request</h3>';
        $html .= '<pre style="white-space:pre-wrap;background:#f8fafc;border:1px solid #e5e7eb;padding:12px;border-radius:6px">'.htmlspecialchars($_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'])."\n".print_r($_REQUEST, true).'</pre>';
        $html .= '</div>';
        echo $html;
    }
}

