<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrimTrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $pathInfo = $request->getPathInfo();

        if ($pathInfo !== '/' && str_ends_with($pathInfo, '/')) {
            $normalizedPath = rtrim($pathInfo, '/');
            $queryString = $request->getQueryString();
            $target = $normalizedPath . ($queryString ? ('?' . $queryString) : '');

            return redirect($target, 301);
        }

        return $next($request);
    }
}
