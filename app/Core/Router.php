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
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($uri === '/index.php') { $uri = '/'; }
        $method = $_SERVER['REQUEST_METHOD'];
        Logger::log("Dispatch: $method $uri");
        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                list($controller, $methodName) = explode('@', $route['action']);
                $controller = "App\\Controllers\\$controller";
                call_user_func([new $controller, $methodName]);
                return;
            }
        }
        http_response_code(404);
        Logger::log('404 Not Found for '.$method.' '.$uri);
        $debugEnv = getenv('APP_DEBUG');
        $debug = is_string($debugEnv) ? in_array(strtolower($debugEnv), ['1','true','on','yes'], true) : false;
        if ($debug) {
            echo '<pre style="padding:12px;background:#fff3f3;border:1px solid #ffc9c9">404 Not Found: '.htmlspecialchars($method.' '.$uri)."\n\nRegistered routes:\n";
            foreach ($this->routes as $r) {
                echo htmlspecialchars($r['method'].' '.$r['uri'].' => '.$r['action'])."\n";
            }
            echo '</pre>';
        } else {
            echo '404 Not Found';
        }
    }
}
