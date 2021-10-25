<?php

use ksoftm\system\kernel\Route;
use ksoftm\system\kernel\Response;
use ksoftm\system\utils\io\FileManager;

if (!function_exists('route')) {
    function route(string $name, array $data = []): string
    {
        return Route::realpath($name, $data);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $name, array $data = null): void
    {
        $path = Route::realpath($name, $data);
        Response::make()->header("Location: $path");
    }
}