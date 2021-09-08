<?php

namespace ksoftm\system\core;


class Request
{
    public function getMethodData(string $method = Router::GET_METHOD): array|false
    {
        $data = ($method == Router::GET_METHOD) ? $_GET : $_POST;
        $method = ($method == Router::GET_METHOD) ? INPUT_GET : INPUT_POST;

        foreach ($data as $key => $value) {
            $key = filter_var($key, FILTER_SANITIZE_SPECIAL_CHARS);
            $tmp[$key] = filter_input($method, $value, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $tmp ?? false;
    }


    // $request->cookie('key', 'default');
    // $request->except($keys);
    // $request->exists('key');
    // $request->file('key', 'default');

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
        $tmp[] = $this->getMethodData();
        $tmp[] = $this->getMethodData(Router::POST_METHOD);

        foreach ($_COOKIE as $key => $value) {
            $key = filter_var($key, FILTER_SANITIZE_SPECIAL_CHARS);
            $tmp[$key] = filter_input(INPUT_COOKIE, $value, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $tmp ?? false;
    }

    public function userRouterData(): array|false
    {
        $r = Router::resolve();

        if ($r != false) {
            return $r->getUserPathData();
        }

        return false;
    }
}
