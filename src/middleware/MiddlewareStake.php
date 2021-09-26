<?php

namespace ksoftm\system\middleware;

use Closure;
use ksoftm\system\kernel\Request;
use ksoftm\system\utils\SingletonFactory;

class MiddlewareStake extends SingletonFactory
{
    protected Closure $start;

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
    protected function __construct()
    {
        $this->start = function (Request $request) {
            return $request;
        };
    }

    function add(array $middlewareFactories): MiddlewareStake
    {
        foreach ($middlewareFactories as $middlewareFactory) {
            if ($middlewareFactory instanceof MiddlewareFactory) {
                $next = $this->start;

                $this->start = function (Request $request) use ($middlewareFactory, $next) {
                    return $middlewareFactory->handle($request, $next);
                };
            }
        }

        return $this;
    }

    function handle(Request $request): mixed
    {
        return call_user_func($this->start, $request);
    }
}
