<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckSuperAdmin
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        if (Auth::guard('admin')->user()->role !== 'super_admin') {
            return abort(403, 'Недостаточно прав.');
        }

        return $next($request);
    }
}