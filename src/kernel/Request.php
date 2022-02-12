<?php

namespace ksoftm\system\kernel;

use ksoftm\system\utils\datatype\Dictionary;
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
    /** @var Dictionary $args globlal variable data arguments. */
    protected Dictionary $args;

    protected static ?self $instance = null;
    public static function getInstance(): self
    {
        if (empty(self::$instance)) {
            self::$instance = parent::init($instance, self::class);
        }
        return self::$instance;
    }

    /**
     * Class constructor.
     */
    public function __construct()
    {
        if (empty($this->args)) {
            $this->args = new Dictionary();
            $this->loadData();
        }
    }

    public function getMethodData(string $method = Route::GET_METHOD): Dictionary|false
    {
        // todo add more methdo like get post cookie session files route
        $data = false;

        switch (strtolower($method)) {
            case 'get':
                $data = $_GET;
                break;
            case 'post':
                $data = $_POST;
                break;
            case 'cookie':
                $data = $_COOKIE;
                break;
            case 'session':
                $data = $_SESSION;
                break;
            case 'files':
                $data = $_FILES;
                break;
            case 'route':
                $data = $this->userRouterData();
                break;

            default:
                $data = false;
                break;
        }

        if ($data != false) {
            $tmp = new Dictionary();
            foreach ($data as $key => $value) {
                $key = filter_var($key, FILTER_SANITIZE_SPECIAL_CHARS);
                $tmp->add($key, filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS));
            }
        }

        return $tmp ?? false;
    }

    public function exists(string $key): bool
    {
        return  is_array($this->except([$key])) && $this->except([$key]) != false ? true : false;
    }

    public function except(array $keys): array|false
    {
        // $this->loadData();

        if ($this->args != false) {
            $tmp = null;

            foreach ($keys as $eKey) {
                $tmp = $this->args->getEach(function (string $key, Dictionary $d) use ($eKey) {
                    if ($d->haveKey($eKey)) {
                        return $d->getKey($eKey);
                    }
                });
            }
        }

        return $tmp ?? false;
    }

    public function getAll(): Dictionary|false
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

        $tmp = $this->getMethodData('get');
        $this->args->add("get", $tmp == false ? new Dictionary() : $tmp);

        $tmp = $this->getMethodData('post');
        $this->args->add("post", $tmp == false ? new Dictionary() : $tmp);

        $tmp = $this->getMethodData('cookie');
        $this->args->add("cookie", $tmp == false ? new Dictionary() : $tmp);

        $tmp = $this->getMethodData('session');
        $this->args->add("session", $tmp == false ? new Dictionary() : $tmp);

        $tmp = $this->getMethodData('files');
        $this->args->add("files", $tmp == false ? new Dictionary() : $tmp);

        $tmp = $this->getMethodData('route');
        $this->args->add("route", $tmp == false ? new Dictionary() : $tmp);


        return $this->args ?? false;
    }

    public function loadData(): void
    {
        $this->args = $this->getAll();
    }

    public function __get(string $data)
    {
        // $this->loadData();

        if ($this->args->haveKey($data)) {
            return $this->args->$data;
        }

        return null;
    }

    public function userRouterData(): array|false
    {
        $r = Route::resolve();

        if ($r != false) {
            return $r->getUserPathData();
        }

        return false;
    }

    public function isGetMethod(): bool
    {
        return $this->currentMethod()  == 'get' ? true : false;
    }

    public function isPostMethod(): bool
    {
        return $this->currentMethod()  == 'post' ? true : false;
    }

    public function currentMethod(): string
    {
        return strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
    }
}
