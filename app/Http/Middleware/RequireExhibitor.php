<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireExhibitor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isExhibitor()) {
            abort(403);
        }

        if (! config('show.self_entry_open')) {
            return redirect()->route('exhibitor.closed');
        }

        return $next($request);
    }
}
