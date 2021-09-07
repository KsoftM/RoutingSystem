<?php

namespace ksoftm\system\core;

use Closure;
use ksoftm\system\core\Rout;

class Router
{
    /** @var string GET_METHOD get method of the rout. */
    public const GET_METHOD = 'get';

    /** @var string POST_METHOD post method of the rout. */
    public const POST_METHOD = 'get';

    /** @var array $argus routing list. */
    protected static $argus = [];
    /*

[
    get => [
        '/update/011/' => ' updated!'
    ]
]

[
    get => [
        'rout' => [
            'path' =>'/update/011/s',
            'callback'  =>' updated!'
        ]
        0 => [
            'path' =>'/update/011/s',
            'callback'  =>' updated!'
        ]
    ]
]


 */

    /**
     * example Paths
     * 
     * $path = '/user/edit/1';
     * $path = '/user/1';
     * $path = '/user/1/profile';
     * $path = '/user/1/quest/10';
     * $path = '/user/tag-config-test';
     * 
     * And their Routers
     * 
     * $routers = '/user/edit/{0}';
     * $routers = '/user/{0}';
     * $routers = '/user/{0}/profile';
     * $routers = '/user/{0}/quest/{1}';
     * $routers = '/user/{0}';
     * 
     * compare routers to match the path
     * 
     */


    public static function post(string $rout, mixed $callable): Rout
    {
        $tmp = new Rout(
            $rout,
            $callable,
            Router::POST_METHOD
        );
        self::$argus[] = $tmp;

        return $tmp;
    }

    public static function get(string $rout, mixed $callable): Rout
    {
        $tmp = new Rout(
            $rout,
            $callable,
            Router::GET_METHOD
        );
        self::$argus[] = $tmp;

        return $tmp;
    }

    public static function getPathByName(string $name): ?string
    {
        $tmp = self::getRoutByName($name);

        if (!empty($tmp)) {
            return $tmp->getPath();
        }
        return null;
    }

    public static function getRoutByName(string $name): ?Rout
    {
        foreach (self::$argus as $key => $value) {
            if ($value instanceof Rout) {
                if (!empty($value->getName()) && $value->getName() == $name) {
                    return self::$argus[$key];
                }
            }
        }
        return null;
    }

    public static function resolve()
    {
        $url = filter_var(
            parse_url(
                filter_input(INPUT_SERVER, 'REQUEST_URI'),
                PHP_URL_PATH
            ),
            FILTER_SANITIZE_URL
        );



        foreach (self::$argus as $arKey => $arValue) {
            if ($arValue instanceof Rout) {
                $path = explode('/', $arValue->getPath());
                foreach ($path as $key => $value) {
                    if (!empty($value) && !empty($arValue)) {
                        echo '<pre>';
                        var_dump($value, $arValue->getPath());
                        echo '</pre>';
                    }
                }
            }
        }
        echo '<pre>';
        // var_dump($url);
        echo '</pre>';
        exit;
    }
}
