<?php

namespace ksoftm\system\kernel;

use Closure;
use ksoftm\system\internal\RouteFactory;
use ksoftm\system\utils\html\Mixer;
use ksoftm\system\utils\SingletonFactory;
use ReflectionObject;

class Route
{

    /** @var string GET_METHOD get method of the rout. */
    public const GET_METHOD = 'get';

    /** @var string POST_METHOD post method of the rout. */
    public const POST_METHOD = 'post';

    /** @var array $argus routing list. */
    protected static $argus = [];

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


    public static function post(string $rout, Closure $callable): RouteFactory
    {
        $tmp = RouteFactory::new(
            $rout,
            $callable,
            Route::POST_METHOD
        );

        return self::$argus[] = $tmp;
    }

    public static function get(string $rout, Closure $callable): RouteFactory
    {
        $tmp = RouteFactory::new(
            $rout,
            $callable,
            Route::GET_METHOD
        );

        return self::$argus[] = $tmp;
    }

    public static function getPathByName(string $name): ?string
    {
        $tmp = self::getRoutByName($name);

        if (!empty($tmp)) {
            return $tmp->getPath();
        }
        return null;
    }

    public static function getRoutByName(string $name): ?RouteFactory
    {
        foreach (self::$argus as $key => $value) {
            if ($value instanceof RouteFactory) {
                if (!empty($value->getName()) && $value->getName() == $name) {
                    return self::$argus[$key];
                }
            }
        }
        return null;
    }

    public static function build(): mixed
    {
        $r = Route::resolve();

        if ($r != false) {
            $callable = $r->getCallback();

            $call = new ReflectionObject($callable);

            $params = [];
            foreach ($call->getMethod('__invoke')->getParameters() as $key => $value) {
                if ($value->hasType()) {
                    $param = $value->getType()->getName();

                    if (isset(class_parents($param)[SingletonFactory::class])) {
                        $params[] = $param::getInstance();
                    }
                }
            }

            if (is_callable($callable)) {
                $data = call_user_func_array($callable, $params);
                if (
                    is_object($data) ||
                    is_array($data)
                ) {
                    return $data;
                } else {
                    echo $data;
                    return true;
                }
            }
        } else {
            Response::centeredMessage('Rout not found..!');
            return false;
        }

        return false;
    }

    public static function resolve(): RouteFactory|false
    {
        $url = rtrim(filter_var(
            parse_url(
                filter_input(INPUT_SERVER, 'REQUEST_URI'),
                PHP_URL_PATH
            ),
            FILTER_SANITIZE_URL
        ), '/') ?: '/';


        foreach (self::$argus as $arKey => $arValue) {
            if ($arValue instanceof RouteFactory && ($arValue->isGet() || $arValue->isPost())) {
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
            if (empty($data)) {
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
