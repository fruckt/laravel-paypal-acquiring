<?php

namespace App\Http\Middleware;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Cookie;

class Language
{
    protected $except = [
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $is_except = false;
        $def_language = Config::get('app.locale');
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                $is_except = true;
                break;
            }
        }

        if ($is_except)
            App::setLocale($def_language);
        else {
            $language = Cookie::get('language');
            App::setLocale(empty($language) ? $def_language : $language);
        }

        return $next($request);
    }
}
