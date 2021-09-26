<?php

namespace ksoftm\system\middleware;

use Closure;
use ksoftm\system\kernel\Request;

interface MiddlewareFactory
{
    function handle(Request $request, Closure $next): mixed;
}