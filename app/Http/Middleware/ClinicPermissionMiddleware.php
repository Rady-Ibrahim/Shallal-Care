<?php

namespace App\Http\Middleware;

use App\Support\ClinicDashboardContext;
use Closure;
use Illuminate\Http\Request;

class ClinicPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $context = ClinicDashboardContext::resolve();

        if (! $context->hasPermission($permission)) {
            if ($request->expectsJson() || $request->is('doctor/api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'FORBIDDEN', 'message' => 'ليس لديك صلاحية لهذه العملية'],
                ], 403);
            }

            abort(403, 'ليس لديك صلاحية لهذه العملية');
        }

        return $next($request);
    }
}
