<?php

namespace KsoftM\system\middleware;

use Closure;

interface MiddlewareFactory
{
    function handle($request, Closure $next): mixed;
}
