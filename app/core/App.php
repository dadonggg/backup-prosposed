<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    private array $config;

    public function __construct()
    {
        $this->config = require BASE_PATH . '/app/config/config.php';
        Container::set('config', $this->config);
    }

    public function run(): void
    {
        $route = isset($_GET['r']) ? (string)$_GET['r'] : 'home/landing';
        [$controller, $action] = $this->parseRoute($route);

        $controllerClass = 'App\\Controllers\\' . $controller . 'Controller';
        $method = $action . 'Action';

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            echo 'Controller not found';
            return;
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $method)) {
            http_response_code(404);
            echo 'Action not found';
            return;
        }

        $instance->$method();
    }

    private function parseRoute(string $route): array
    {
        $route = trim($route, '/');
        $parts = explode('/', $route);
        $controller = $parts[0] ?? 'auth';
        $action = $parts[1] ?? 'login';

        $controller = preg_replace('/[^a-zA-Z0-9_]/', '', $controller) ?: 'auth';
        $action = preg_replace('/[^a-zA-Z0-9_]/', '', $action) ?: 'login';

        return [ucfirst(strtolower($controller)), strtolower($action)];
    }
}
