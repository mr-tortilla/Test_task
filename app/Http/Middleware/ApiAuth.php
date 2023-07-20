<?php

namespace App\Http\Middleware;

use App\Facades\Error;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(Request): (Response) $next
     */
    public function handle ( Request $request, Closure $next ): Response
    {
        $user_key = $request->header('Authorization');
        if ( !$user_key ) {
            return Error::api_error('no_token');
        } else {
            $auth = str_replace('Bearer ', '', $user_key);
            if ( $auth !== Config::get('auth.apikey') ) {
                return Error::api_error('invalid_token');
            }
        }

        return $next($request);
    }
}
