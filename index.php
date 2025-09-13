<?php
require_once __DIR__.'/config/bootstrap.php';
use App\Core\Router;

try {
    $router = new Router();
    $router->dispatch();
} catch (Throwable $e) {
    \App\Helpers\Logger::log('FrontController exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
    http_response_code(500);
    $debugEnv = getenv('APP_DEBUG');
    $debug = is_string($debugEnv) ? in_array(strtolower($debugEnv), ['1','true','on','yes'], true) : false;
    if ($debug) {
        echo '<pre>'.htmlspecialchars($e).'</pre>';
    } else {
        echo 'Internal Server Error';
    }
}
