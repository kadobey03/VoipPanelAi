<?php
/**
 * Simple Router for Landing Page
 * Handles basic routing and language switching
 */

class SimpleRouter {
    private $lang = 'tr';
    private $routes = [];
    
    public function __construct() {
        $this->detectLanguage();
        $this->defineRoutes();
    }
    
    /**
     * Detect and set language from URL or session
     */
    private function detectLanguage() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check URL parameter first
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['tr', 'en', 'ru'])) {
            $this->lang = $_GET['lang'];
            $_SESSION['lang'] = $this->lang;
        }
        // Check session
        elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['tr', 'en', 'ru'])) {
            $this->lang = $_SESSION['lang'];
        }
        // Check browser language
        else {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
            if (in_array($browserLang, ['tr', 'en', 'ru'])) {
                $this->lang = $browserLang;
                $_SESSION['lang'] = $this->lang;
            }
        }
    }
    
    /**
     * Define available routes
     */
    private function defineRoutes() {
        $this->routes = [
            '/' => 'index.php',
            '/index' => 'index.php',
            '/home' => 'index.php',
            '/tr' => 'index.php?lang=tr',
            '/en' => 'index.php?lang=en',
            '/ru' => 'index.php?lang=ru'
        ];
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage() {
        return $this->lang;
    }
    
    /**
     * Route the request
     */
    public function route() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestUri = rtrim($requestUri, '/');
        
        // Remove base path if running in subdirectory
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        if (empty($requestUri)) {
            $requestUri = '/';
        }
        
        // Check if route exists
        if (isset($this->routes[$requestUri])) {
            $file = $this->routes[$requestUri];
            
            // Extract query parameters if any
            if (strpos($file, '?') !== false) {
                list($file, $queryString) = explode('?', $file, 2);
                parse_str($queryString, $params);
                foreach ($params as $key => $value) {
                    $_GET[$key] = $value;
                }
            }
            
            // Include the target file
            if (file_exists(__DIR__ . '/' . $file)) {
                include __DIR__ . '/' . $file;
                return;
            }
        }
        
        // Default to index.php
        if (file_exists(__DIR__ . '/index.php')) {
            include __DIR__ . '/index.php';
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
    }
    
    /**
     * Generate URL with language parameter
     */
    public function url($path = '', $lang = null) {
        $lang = $lang ?: $this->lang;
        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        
        if ($path === '' || $path === '/') {
            return $baseUrl . '/' . ($lang !== 'tr' ? $lang : '');
        }
        
        return $baseUrl . '/' . ltrim($path, '/') . ($lang !== 'tr' ? '?lang=' . $lang : '');
    }
}

// Initialize router if this file is called directly
if (basename($_SERVER['PHP_SELF']) === 'router.php') {
    $router = new SimpleRouter();
    $router->route();
}
?>