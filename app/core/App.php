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

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            echo 'Controller not found';
            return;
        }

        $instance = new $controllerClass();
        $targetAction = strtolower($action) . 'action';

        // Resolve exact method name case-insensitively using Reflection
        $resolvedMethod = null;
        $reflector = new \ReflectionClass($instance);
        foreach ($reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $methodObj) {
            if (strtolower($methodObj->getName()) === $targetAction) {
                $resolvedMethod = $methodObj->getName();
                break;
            }
        }

        if ($resolvedMethod === null) {
            http_response_code(404);
            echo 'Action not found';
            return;
        }

        $instance->$resolvedMethod();
    }

    private function parseRoute(string $route): array
    {
        $route = trim($route, '/');
        $parts = explode('/', $route);
        $controller = $parts[0] ?? 'auth';
        $action = $parts[1] ?? 'login';

        $controller = preg_replace('/[^a-zA-Z0-9_]/', '', $controller) ?: 'auth';
        $action = preg_replace('/[^a-zA-Z0-9_]/', '', $action) ?: 'login';

        // Resolve the controller name by scanning the controllers directory
        // case-insensitively so that camelCase controllers like FoodApiController
        // and WorkoutSessionController are found even when the URL uses lowercase.
        $resolvedController = $this->resolveControllerName($controller);

        return [$resolvedController, strtolower($action)];
    }

    /**
     * Find the correct PascalCase controller name by matching the given segment
     * against actual Controller filenames, case-insensitively.
     */
    private function resolveControllerName(string $segment): string
    {
        $controllersDir = BASE_PATH . '/app/controllers';
        $segmentLower = strtolower($segment);

        if (is_dir($controllersDir)) {
            foreach (scandir($controllersDir) as $file) {
                if (substr($file, -strlen('Controller.php')) !== 'Controller.php') {
                    continue;
                }
                // Strip "Controller.php" to get the bare class prefix e.g. "FoodApi"
                $prefix = substr($file, 0, -strlen('Controller.php'));
                if (strtolower($prefix) === $segmentLower) {
                    return $prefix;
                }
            }
        }

        // Fallback to simple ucfirst if no match found
        return ucfirst($segmentLower);
    }
}
