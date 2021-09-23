<?php

namespace ksoftm\system\kernel;

use Closure;
use ksoftm\system\internal\Rout;

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
     * preg_match('/[\/]user\/(.*)\/profile\/edit\/slug[\/](.*)/', $input_line, $output_array);
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

    public static function resolve(): Rout|false
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
                $path = $arValue->getPath();

                if (preg_match_all('/[\/.*]*[{][.*]*([^}]*)[}][\/.*]*/miU', $path, $output_array)) {
                    $format = "$path/";
                    foreach ($output_array[1] as $key => $value) {
                        $format = str_replace(sprintf("{%s}", $value), '(.*)', $format);
                    }
                    $format = str_replace('/', '\/', $format);
                    $format = sprintf("/%s/miU", $format);


                    if (preg_match_all($format, "$url/", $out)) {

                        array_shift($out);

                        $data = [];
                        array_map(
                            function ($key, $value) use (&$data) {
                                $data += [$key => $value[0]];
                            },
                            $output_array[1],
                            $out
                        );
                        $arValue->setUserPathData($data);
                        return $arValue;
                    }
                } else {
                    if ($url == $path) {
                        return $arValue;
                    }
                }
            }
        }

        return false;
    }

    /**
     * get the real path of a specific path
     *
     * @param string $name name of the rout
     * @param array $data data must be in left ot right order regarding the rout.
     *
     * @return string|false
     */
    public static function realPath(string $name, array $data = null): string|false
    {
        $path = self::getPathByName($name);

        if (preg_match_all(
            '/[\/.*]*[{][.*]*([^}]*)[}][\/.*]*/miU',
            $path,
            $matches
        )) {
            if (!empty($data)) {
                return false;
            }
            $path = "$path/";
            foreach ($matches[1] as $value) {
                $path = str_replace(sprintf("{%s}", $value), '%s', $path);
            }

            foreach ($data as $value) {
                $path = implode($value, explode('%s', $path, 2));
            }
        }

        return $path;
    }
}
