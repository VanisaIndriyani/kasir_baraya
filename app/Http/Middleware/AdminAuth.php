<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $admin = $request->session()->get('admin');
        $id = is_array($admin) ? (int) ($admin['id'] ?? 0) : 0;

        if ($id <= 0) {
            return redirect('/admin/login.php');
        }

        return $next($request);
    }
}

