<?php

namespace ksoftm\system;

use ksoftm\system\kernel\Response;
use ksoftm\system\kernel\Route;
use ksoftm\system\utils\io\FileManager;

if (!function_exists('router')) {
    function router(string $name): string
    {
        return Route::realpath($name);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $name, array $data = null): void
    {
        $path = Route::realpath($name, $data);
        Response::make()->header("Location: $path");
    }
}


if (!function_exists('request_dir')) {
    function request_dir(string $dir): array
    {
        $f = new FileManager($dir);
        $output = [];
        if ($f->isExist()) {
            $f = $f->getDirectoryFiles(true);
            foreach ($f as $data) {
                if ($data instanceof FileManager) {
                    $output[] = $data->requireOnce();
                }
            }
        }
        return $output;
    }
}
