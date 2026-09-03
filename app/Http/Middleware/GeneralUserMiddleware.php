<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeneralUserMiddleware
{
    /**
     * 一般ユーザーのみアクセスを許可する。
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! $request->user() || $request->user()->admin_status) {
            abort(403);
        }

        return $next($request);
    }
}
