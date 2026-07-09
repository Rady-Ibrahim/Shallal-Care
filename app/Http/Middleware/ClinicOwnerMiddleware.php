<?php

namespace App\Http\Middleware;

use App\Support\ClinicDashboardContext;
use Closure;
use Illuminate\Http\Request;

class ClinicOwnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $context = ClinicDashboardContext::resolve();

        if (! $context->isOwner()) {
            if ($request->expectsJson() || $request->is('doctor/api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'OWNER_ONLY', 'message' => 'هذه العملية للطبيب المالك فقط'],
                ], 403);
            }

            abort(403, 'هذه العملية للطبيب المالك فقط');
        }

        return $next($request);
    }
}
