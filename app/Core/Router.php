<?php
namespace App\Core;

use App\Helpers\Logger;

class Router {
    protected $routes = [];
    public function __construct() {
        // Expose $this as $router for the route file
        $router = $this;
        require __DIR__.'/../../routes/web.php';
    }
    public function add($method, $uri, $action) {
        $this->routes[] = compact('method', 'uri', 'action');
    }
    public function dispatch() {
        $raw = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
        $uri = $raw;
        // Strip base path if app runs in subdirectory (e.g., /VoipPanelAi)
        if ($base && $base !== '/' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        if ($uri === '' || $uri === false) { $uri = '/'; }
        if ($uri === '/index.php') { $uri = '/'; }
        // Normalize trailing slash
        $norm = rtrim($uri, '/'); if ($norm === '') { $norm = '/'; }
        $method = $_SERVER['REQUEST_METHOD'];
        Logger::log("Dispatch: $method $raw (base=$base, norm=$norm)");
        foreach ($this->routes as $route) {
            $ruri = rtrim($route['uri'], '/'); if ($ruri === '') { $ruri = '/'; }
            if ($ruri === $norm && $route['method'] === $method) {
                list($controller, $methodName) = explode('@', $route['action']);
                $controller = "App\\Controllers\\$controller";
                call_user_func([new $controller, $methodName]);
                return;
            }
        }
        http_response_code(404);
        Logger::log('404 Not Found for '.$method.' '.$raw.' (base='.$base.', norm='.$norm.')');
        $debugEnv = getenv('APP_DEBUG');
        $debug = is_string($debugEnv) ? in_array(strtolower($debugEnv), ['1','true','on','yes'], true) : false;
        if ($debug) {
            echo '<pre style="padding:12px;background:#fff3f3;border:1px solid #ffc9c9">404 Not Found: '.htmlspecialchars($method.' '.$raw).'\n';
            echo 'Base: '.htmlspecialchars($base).'\nNormalized: '.htmlspecialchars($norm)."\n\nRegistered routes:\n";
            foreach ($this->routes as $r) {
                echo htmlspecialchars($r['method'].' '.$r['uri'].' => '.$r['action'])."\n";
            }
            echo '</pre>';
        } else {
            echo '404 Not Found';
        }
    }
}
