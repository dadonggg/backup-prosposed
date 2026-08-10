<?php

declare(strict_types=1);

namespace App\Core;

final class Container
{
    private static array $items = [];

    public static function set(string $key, $value): void
    {
        self::$items[$key] = $value;
    }

    public static function get(string $key)
    {
        return self::$items[$key] ?? null;
    }
}
