<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    
public function handle(Request $request, Closure $next)
{
    Log::info('AdminMiddleware: Проверка авторизации.');

    if (!Auth::guard('admin')->check()) {
        Log::info('AdminMiddleware: Пользователь не авторизован. Перенаправление на страницу входа.');
        return redirect()->route('admin.login');
    }

    if (Auth::guard('admin')->user()->role !== 'super_admin') {
        Log::info('AdminMiddleware: Недостаточно прав для доступа.');
        abort(403, 'Недостаточно прав для доступа.');
    }

    Log::info('AdminMiddleware: Доступ разрешён.');
    return $next($request);
    }
}
