<?php
namespace App\Core;
class Router {
    protected $routes = [];
    public function __construct() {
        require __DIR__.'/../../routes/web.php';
    }
    public function add($method, $uri, $action) {
        $this->routes[] = compact('method', 'uri', 'action');
    }
    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                list($controller, $methodName) = explode('@', $route['action']);
                $controller = "App\\Controllers\\$controller";
                call_user_func([new $controller, $methodName]);
                return;
            }
        }
        http_response_code(404);
        echo '404 Not Found';
    }
}
