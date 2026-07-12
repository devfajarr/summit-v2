<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcessHttpOnlyCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if ($request->hasCookie('auth_token') && ! $request->headers->has('Authorization')) {
        //     $request->headers->set('Authorization', 'Bearer '.$request->cookie('auth_token'));
        // }

        // return $next($request);

        if (!$request->headers->has('Authorization')) {
            $token = $request->cookie('auth_token');
            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }
        return $next($request);
    }
}
