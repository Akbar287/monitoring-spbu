<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCacheControlHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Set Cache-Control header for JavaScript files
        if ($response->headers->get('Content-Type') === 'application/javascript') {
            $response->header('Cache-Control', 'public, max-age=604800'); // Cache for 1 week
        }
        return $next($request);
    }
}
