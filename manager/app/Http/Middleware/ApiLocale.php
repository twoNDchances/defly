<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->localeFrom($request));

        return $next($request);
    }

    private function localeFrom(Request $request): string
    {
        $locale = str($request->header('Accept-Language', config('app.locale')))
            ->before(',')
            ->before('-')
            ->lower()
            ->toString();

        return in_array($locale, ['vi', 'en'], true)
            ? $locale
            : config('app.locale');
    }
}
