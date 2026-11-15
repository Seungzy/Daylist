<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMember
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(! $request->user()->is_admin && $request->user()->member()->where('is_active', true)->exists(), 403, 'An enabled member account is required.');

        return $next($request);
    }
}
