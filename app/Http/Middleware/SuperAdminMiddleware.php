<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->is_active || !auth()->user()->role || auth()->user()->role->name !== 'super_admin') {
            abort(403, 'Bạn không có quyền truy cập khu vực Super Admin.');
        }
        return $next($request);
    }
}
