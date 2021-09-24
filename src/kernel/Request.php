<?php

namespace ksoftm\system\kernel;

use ksoftm\system\utils\SingletonFactory;

/*
    future updates

    $request->cookie('key', 'default');
    $request->file('key', 'default');

*/

/**
 * Request class
 */
class Request extends SingletonFactory
{
    protected static ?self $instance = null;
    public static function getInstance(): self
    {
        if (empty(self::$instance)) {
            self::$instance = parent::init($instance, self::class);
        }
        return self::$instance;
    }

    public function getMethodData(string $method = Route::GET_METHOD): array|false
    {
        $data = ($method == Route::GET_METHOD) ? $_GET : $_POST;
        $method = ($method == Route::GET_METHOD) ? INPUT_GET : INPUT_POST;

        foreach ($data as $key => $value) {
            $key = filter_var($key, FILTER_SANITIZE_SPECIAL_CHARS);
            $tmp[$key] = filter_input($method, $value, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $tmp ?? false;
    }

    public function exists(string $key): bool
    {
        return $this->except([$key]) == false ? false : true;
    }

    public function except(array $keys): array
    {
        $data = $this->getAll();

        foreach ($keys as $eKey) {
            if (array_key_exists($eKey, $data)) {
                $tmp[$eKey] = $data[$eKey];
            }
        }

        return $tmp ?? false;
    }

    public function getAll(): array|false
    {
        $output[] = $this->getMethodData();
        $output[] = $this->getMethodData(Route::POST_METHOD);
        $output[] = $_SESSION;
        $output[] = $_FILES;

        foreach (array_merge($_COOKIE, $_SESSION, $_FILES) as $key => $value) {
            $key = filter_var($key, FILTER_SANITIZE_SPECIAL_CHARS);
            $tmp[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        $output = $tmp;

        return $tmp ?? false;
    }

    public function userRouterData(): array|false
    {
        $r = Route::resolve();

        if ($r != false) {
            return $r->getUserPathData();
        }

        return false;
    }
}