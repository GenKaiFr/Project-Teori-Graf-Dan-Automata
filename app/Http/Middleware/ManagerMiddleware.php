<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        if (!auth()->user()->canManageMeetings()) {
            abort(403, 'Access denied. Manager or Admin only. Your role: ' . auth()->user()->role);
        }

        return $next($request);
    }
}