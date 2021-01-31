<?php

namespace App\Http\Middleware;

use Closure;
use RushApp\Core\Models\Language;

class SetLanguage
{
    public function handle($request, Closure $next)
    {
        $language = $request->header('Language');

        if (!$language) {
            $language = Language::first()->name;
        }

        app()->setLocale($language);
        $request->merge(["language" => $language]);

        return $next($request);
    }
}
