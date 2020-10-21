<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */

    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($guard === 'api') {
                return response()->json(['code'=>401,'message'=>'Please Login!','success'=>false]);
            } else {
                return redirect()->guest('login');
            }
        }
        return $next($request);
    }


    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
       // else return response()->json(['code'=>400,'success'=>false,'message'=>'Please Login!']);
    }
}
