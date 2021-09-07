<?php

namespace ksoftm\system\core;

use Exception;

class Rout
{

    /** @var string $name name of the property. */
    protected ?string $name = null;

    /** @var string $method method of the rout. */
    protected ?string $method = null;

    /** @var string $path path of the rout. */
    protected ?string $path = null;

    /** @var mixed $callback callback of the rout. */
    protected mixed $callback = null;

    /**
     * class construct
     *
     * @param string $routPath
     * @param mixed $callback
     * @param string $name
     */
    public function __construct(string $path, mixed $callback, string $method)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->method = $method;
    }

    /**
     * set the rout name
     *
     * @param string $name
     *
     * @return void
     */
    public function name(string $name): void
    {
        if (preg_match('/^[^0-9][a-z0-9-]+$/', $name)) {
            $this->name = $name;
        } else {
            throw new Exception("$name . ' is not a valid name.");
        }
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
     * check this Rout is post method
     *
     * @return boolean
     */
    public function isPost(): bool
    {
        return $this->method == Router::POST_METHOD ? true : false;
    }

    /**
     * check this Rout is get method
     *
     * @return boolean
     */
    public function isGet(): bool
    {
        return $this->method == Router::GET_METHOD ? true : false;
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
     * @return mixed
     */
    public function getCallback(): mixed
    {
        return $this->callbacks;
    }
}
