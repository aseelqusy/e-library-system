<?php

class Router {
    private array $routes = [];

    public function get(string $path, string $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $this->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->convertToRegex($route['path']);

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        $this->handleNotFound();
    }

    private function getUri(): string {
        $uri = $_GET['url'] ?? '';
        return trim($uri, '/');
    }

    private function convertToRegex(string $path): string {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function callHandler(string $handler, array $params): void {
        [$controllerName, $method] = explode('@', $handler);

        $controllerFile = APP_PATH . '/Controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $this->handleNotFound();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            $this->handleNotFound();
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            $this->handleNotFound();
            return;
        }

        call_user_func_array([$controller, $method], array_values($params));
    }

    private function handleNotFound(): void {
        http_response_code(404);
        View::render('errors/404', ['title' => '404 - Page Not Found']);
    }

    public function handleForbidden(): void {
        http_response_code(403);
        View::render('errors/403', ['title' => '403 - Access Denied']);
    }
}

