<?php
// app/Core/Router.php
class Router
{
    private array $routes = [];
    private array $middlewareMap = [
        'auth' => AuthMiddleware::class,
        'admin' => AdminMiddleware::class,
        'guest' => GuestMiddleware::class,
    ];

    public function get(string $uri, $action, array $middleware = []): void
    {
        $this->add('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, $action, array $middleware = []): void
    {
        $this->add('POST', $uri, $action, $middleware);
    }

    private function add(string $method, string $uri, $action, array $middleware): void
    {
        $this->routes[] = compact('method', 'uri', 'action', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/');
        $path = $path === '' ? '/' : $path;

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            $routeUri = rtrim($route['uri'], '/');
            $routeUri = $routeUri === '' ? '/' : $routeUri;

            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '([^/]+)', $routeUri);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            array_shift($matches);

            if (strtoupper($method) === 'POST') {
                verify_csrf();
            }

            foreach ($route['middleware'] as $alias) {
                $class = $this->middlewareMap[$alias] ?? null;
                if ($class) {
                    (new $class())->handle();
                }
            }

            if (is_callable($route['action'])) {
                call_user_func_array($route['action'], $matches);
                return;
            }

            [$controller, $controllerMethod] = $route['action'];
            $instance = new $controller();
            call_user_func_array([$instance, $controllerMethod], $matches);
            return;
        }

        abort(404);
    }
}
