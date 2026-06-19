<?php
class Router {
    private array $routes = [];

    public function get(string $path, array $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $path   = '/' . ltrim(substr($uri, strlen($base)), '/');
        if ($path === '') {
            $path = '/';
        }

        // Exact match
        if (isset($this->routes[$method][$path])) {
            $this->call($this->routes[$method][$path], []);
            return;
        }

        // Param match
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{[a-z_]+\}/', '([^/]+)', $route);
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                array_shift($matches);
                $this->call($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        require APP_ROOT . '/views/errors/404.php';
    }

    private function call(array $handler, array $params): void {
        $file = APP_ROOT . '/controllers/' . $handler[0] . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
        $ctrl   = new $handler[0]();
        $action = $handler[1];
        $ctrl->$action(...$params);
    }
}
