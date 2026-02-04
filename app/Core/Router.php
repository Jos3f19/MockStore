<?php
/**
 * Simple Router Class
 * Handles routing of HTTP requests to appropriate controllers
 */
class Router
{
    private array $routes = [];
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Register a GET route
     */
    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Register a POST route
     */
    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash
        $uri = rtrim($uri, '/') ?: '/';

        // Find matching route
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->convertRouteToRegex($route);
            
            if (preg_match($pattern, $uri, $matches)) {
                // Extract parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Parse handler
                [$controllerName, $methodName] = explode('@', $handler);
                
                // Create controller instance
                $controller = new $controllerName($this->config);
                
                // Call the method with parameters
                call_user_func_array([$controller, $methodName], $params);
                return;
            }
        }

        // No route found
        http_response_code(404);
        echo $this->render404();
    }

    /**
     * Convert route pattern to regex
     */
    private function convertRouteToRegex(string $route): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Render 404 page
     */
    private function render404(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>404 - Page Not Found</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container text-center mt-5">
                <h1 class="display-1">404</h1>
                <p class="lead">Page not found</p>
                <a href="/" class="btn btn-primary">Go Home</a>
            </div>
        </body>
        </html>';
    }
}
