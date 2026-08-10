<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $parts = explode('\\', $relativeClass);

    // Lowercase all directories (everything before the final class name)
    $lastIndex = count($parts) - 1;
    for ($i = 0; $i < $lastIndex; $i++) {
        $parts[$i] = strtolower($parts[$i]);
    }

    $file = $baseDir . implode('/', $parts) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
