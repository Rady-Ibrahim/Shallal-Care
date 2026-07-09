<?php

namespace App\Http\Middleware;

use App\Support\ClinicDashboardContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareClinicDashboardContext
{
    public function handle(Request $request, Closure $next)
    {
        if (auth('web')->check()) {
            $context = ClinicDashboardContext::make();
            app()->instance(ClinicDashboardContext::class, $context);

            View::share('clinicContext', $context);
            View::share('clinicBranch', $context->branch);
            View::share('isClinicOwner', $context->isOwner());
            View::share('isSecretary', $context->isSecretary());
        }

        return $next($request);
    }
}
