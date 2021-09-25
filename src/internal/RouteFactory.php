<?php

namespace ksoftm\system\internal;

use Closure;
use Exception;
use ksoftm\system\kernel\Route;

class RouteFactory
{

    /** @var string $name name of the property. */
    protected ?string $name = null;

    /** @var string $method method of the rout. */
    protected ?string $method = null;

    /** @var string $path path of the rout. */
    protected ?string $path = null;

    /** @var Closure $callback callback of the rout. */
    protected ?Closure $callback = null;

    /** @var array $callback user rout data. */
    protected ?array $data = null;

    /**
     * class construct
     *
     * @param string $routPath
     * @param Closure $callback
     * @param string $name
     */
    protected function __construct(string $path, Closure $callback, string $method)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->method = $method;
    }

    public static function new(string $path, Closure $callback, string $method): RouteFactory
    {
        return new RouteFactory($path, $callback, $method);
    }

    /**
     * set the rout name
     *
     * @param string $name
     *
     * @return RouteFactory
     */
    public function name(string $name): RouteFactory
    {
        if (preg_match('/^[^0-9][a-z0-9-.]+$/', $name)) {
            $this->name = $name;
        } else {
            throw new Exception("$name is not a valid name.");
        }

        return $this;
    }

    /**
     * find the rout by name
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * return the route method
     *
     * @return string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function middleware(string $middleware): RouteFactory
    {

        return $this;
    }

    /**
     * check this Rout is post method
     *
     * @return boolean
     */
    public function isPost(): bool
    {
        return (strtolower($this->method) == strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD')) && strtolower($this->method) == Route::POST_METHOD) ? true : false;
    }

    /**
     * check this Rout is get method
     *
     * @return boolean
     */
    public function isGet(): bool
    {
        return (strtolower($this->method) == strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD')) && strtolower($this->method) == Route::GET_METHOD) ? true : false;
    }

    /**
     * get the path of the rout
     *
     * @return void
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * get the callback of the rout
     *
     * @return Closure|false
     */
    public function getCallback(): Closure|false
    {
        return $this->callback ?? false;
    }

    public function setUserPathData(array $data): void
    {
        $this->data = $data;
    }

    public function getUserPathData(): array|false
    {
        return empty($this->data) ? false : $this->data;
    }
}
