<?php

if (!function_exists('router')) {
    function router(string $name): string
    {
        return ksoftm\system\kernel\Router::realpath($name);
    }
}
