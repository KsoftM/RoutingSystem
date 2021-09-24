<?php

namespace KsoftM\system\middleware;

use Closure;

class MiddlewareStake
{
    protected Closure $start;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->start = function ($request) {
            return $request;
        };
    }

    function add(MiddlewareFactory $middlewareFactory): void
    {
        $next = $this->start;

        $this->start = function ($request) use ($middlewareFactory, $next) {
            return $middlewareFactory->handle($request, $next);
        };
    }

    function handle($request): mixed
    {
        return call_user_func($this->start, $request);
    }
}
