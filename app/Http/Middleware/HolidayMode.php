<?php

namespace App\Http\Middleware;

use App\Settings\WebsiteSettings;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class HolidayMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(WebsiteSettings::class);
        
        if ($settings->holiday_mode) {
            return Inertia::render('Visitors/Shop/HolidayMode')->toResponse($request);
        }

        return $next($request);
    }
}
