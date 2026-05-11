<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $route): void
    {
        $config = Container::get('config');
        $baseUrl = $config['app']['base_url'] ?? '';
        // Do NOT urlencode — extra params like &gym_id=48 must stay as real query params
        header('Location: ' . rtrim($baseUrl, '/') . '/index.php?r=' . $route);
        exit;
    }
}
