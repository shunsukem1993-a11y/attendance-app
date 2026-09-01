<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttendanceCorrectionRequestRoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $isAdmin = $request->user()?->admin_status === true;

        $request->attributes->set('is_admin', $isAdmin);

        return $next($request);
    }
}
