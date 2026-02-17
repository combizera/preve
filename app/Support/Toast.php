<?php

declare(strict_types=1);

namespace App\Support;

use Inertia\Inertia;

final class Toast
{
    public static function default(string $message): void
    {
        self::flash([
            'type'    => 'default',
            'message' => $message,
        ]);
    }

    public static function message(string $message, string $description): void
    {
        self::flash([
            'type'        => 'message',
            'message'     => $message,
            'description' => $description,
        ]);
    }

    public static function success(string $message): void
    {
        self::flash([
            'type'    => 'success',
            'message' => $message,
        ]);
    }

    public static function info(string $message): void
    {
        self::flash([
            'type'    => 'info',
            'message' => $message,
        ]);
    }

    public static function warning(string $message): void
    {
        self::flash([
            'type'    => 'warning',
            'message' => $message,
        ]);
    }

    public static function error(string $message): void
    {
        self::flash([
            'type'    => 'error',
            'message' => $message,
        ]);
    }

    private static function flash(array $data): void
    {
        Inertia::flash(['toast' => $data]);
    }
}
