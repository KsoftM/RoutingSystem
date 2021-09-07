<?php

if (!function_exists('getRoutByName')) {
    function getRoutByName(string $name): string
    {
        return ksoftm\system\core\Router::getPathByName($name);
    }
}
