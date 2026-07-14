<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Doctor\Models\Doctor;

class ClinicActiveSubscriptionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->isSubscriptionRoute($request)) {
            return $next($request);
        }

        $doctor = $this->resolveDoctor();

        if (! $doctor) {
            if ($request->expectsJson() || $request->is('doctor/api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'DOCTOR_NOT_FOUND', 'message' => 'ملف الطبيب غير موجود'],
                ], 403);
            }

            return redirect()->route('doctor.login');
        }

        if ($doctor->hasActiveSubscription()) {
            return $next($request);
        }

        $message = 'يجب تفعيل اشتراك العيادة للمتابعة';

        if ($request->expectsJson() || $request->is('doctor/api/*')) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SUBSCRIPTION_REQUIRED',
                    'message' => $message,
                    'redirect' => route('doctor.subscription.plans'),
                ],
            ], 403);
        }

        $user = auth('web')->user();
        if ($user?->isSecretary()) {
            if ($this->isSuspendedRoute($request)) {
                return $next($request);
            }

            return redirect()->route('doctor.subscription.suspended');
        }

        return redirect()->route('doctor.subscription.plans')
            ->with('warning', $message);
    }

    protected function resolveDoctor(): ?Doctor
    {
        $user = auth('web')->user();
        if (! $user) {
            return null;
        }

        if ($user->isSecretary()) {
            return $user->clinicStaffMember()
                ->where('status', 'active')
                ->with('doctor')
                ->first()
                ?->doctor;
        }

        return Doctor::where('user_id', $user->id)->first();
    }

    protected function isSubscriptionRoute(Request $request): bool
    {
        $path = trim($request->path(), '/');

        return str_starts_with($path, 'doctor/dashboard/subscription')
            || str_starts_with($path, 'doctor/api/subscription')
            || $path === 'doctor/api/payment-settings'
            || $path === 'doctor/subscription-suspended';
    }

    protected function isSuspendedRoute(Request $request): bool
    {
        return trim($request->path(), '/') === 'doctor/subscription-suspended';
    }
}
