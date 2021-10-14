<?php

namespace ksoftm\system\kernel;

use Closure;
use DOMParentNode;
use Exception;
use ReflectionObject;
use ksoftm\system\internal\RouteFactory;
use ksoftm\system\utils\SingletonFactory;
use ReflectionClass;
use ReflectionMethod;

class Route
{

    /** @var string GET_METHOD get method of the rout. */
    public const GET_METHOD = 'get';

    /** @var string POST_METHOD post method of the rout. */
    public const POST_METHOD = 'post';

    /** @var array $argus routing list. */
    protected static array $argus = [];

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
     */


    public static function post(string $rout, mixed $callable): RouteFactory
    {
        $tmp = RouteFactory::new(
            $rout,
            $callable,
            Route::POST_METHOD
        );

        return self::$argus[] = $tmp;
    }

    public static function get(string $rout, mixed $callable): RouteFactory
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

    public static function currentRoutCheck(string  $routeName): bool
    {
        return Route::getPathByName($routeName) == self::urlPath();
    }

    public static function build(): void
    {
        $r = Route::resolve();

        if ($r != false) {
            $r->applyMiddleware();

            $callable = $r->getCallback();
            $params = [];

            if (is_array($callable) && count($callable) == 2) {
                if (
                    class_exists($callable[0]) &&
                    method_exists($callable[0], $callable[1])
                ) {
                    $call = new ReflectionClass($callable[0]);
                    $parmData = $call->getMethod($callable[1])->getParameters();

                    if ($call->hasMethod('__construct')) {
                        if (
                            $call->getMethod('__construct')->getModifiers() == ReflectionMethod::IS_PUBLIC
                        ) {
                            $callable[0] = new $callable[0];
                        } else {
                            throw new Exception("Invalid method used in the " . Route::class . " method callback functions!");
                        }
                    } else {
                        $callable[0] = new $callable[0];
                    }
                } else {
                    throw new Exception("Callback function of the " . Rout::class . " is invalid.");
                }
            } elseif ($callable instanceof Closure || class_exists($callable)) {
                $call = new ReflectionObject($callable);
                if ($call->hasMethod('__invoke')) {
                    $parmData = $call->getMethod('__invoke')->getParameters();
                }
            }

            foreach ($parmData ?? [] as $value) {

                if ($value->hasType()) {
                    $param = $value->getType()->getName();

                    if (class_exists($param)) {
                        $call = new ReflectionClass($param);

                        if (is_subclass_of($param, SingletonFactory::class)) {
                            $params[] = $param::getInstance();
                        } elseif ($call->hasMethod('__construct')) {
                            $call = $call->getMethod('__construct');
                            if ($call->getModifiers() == ReflectionMethod::IS_PUBLIC) {
                                $params[] = new $param;
                            } else {
                                throw new Exception("Invalid method used in the " . Route::class . " method callback functions!");
                            }
                        } else {
                            $params[] = new $param;
                        }
                    } else {
                        throw new Exception("Invalid parameter type used in the " . Route::class . " method callback functions!");
                    }
                } else {
                    throw new Exception("parameter datatype must be specified in the " . Route::class . " method callback functions!");
                }
            }

            if (is_callable($callable) || is_array($callable)) {
                $data = call_user_func_array($callable, $params);
            } elseif (is_string($callable)) {
                $data = $callable;
            } else {
                $data = false;
            }

            if ($data != false && is_string($data)) {
                echo $data;
            }
        } else {
            Response::centeredMessage('Rout not found..!');
        }
    }

    protected static function urlPath(): string
    {
        return rtrim(filter_var(
            parse_url(
                filter_input(INPUT_SERVER, 'REQUEST_URI'),
                PHP_URL_PATH
            ),
            FILTER_SANITIZE_URL
        ), '/') ?: '/';
    }

    public static function resolve(): RouteFactory|false
    {
        $url = self::urlPath();

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
        return $path ?? false;
    }
}
