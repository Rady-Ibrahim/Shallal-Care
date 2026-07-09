<?php

namespace App\Http\Middleware;

use App\Support\MobileFeatures;
use Closure;
use Illuminate\Http\Request;

class MobileFeatureMiddleware
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        if (! MobileFeatures::isEnabled($feature)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FEATURE_DISABLED',
                    'message' => 'هذه الميزة غير متاحة حالياً في تطبيق الموبايل',
                    'feature' => $feature,
                ],
            ], 403);
        }

        return $next($request);
    }
}
