<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClinicPanelMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth('web')->check()) {
            return redirect()->route('doctor.login');
        }

        $user = auth('web')->user();

        if (! $user->isDoctor() && ! $user->isSecretary()) {
            auth('web')->logout();

            return redirect()->route('doctor.login')
                ->withErrors(['phone' => 'غير مصرح لك بالدخول إلى لوحة العيادة.']);
        }

        if (! $user->isActive()) {
            auth('web')->logout();

            return redirect()->route('doctor.login')
                ->withErrors(['phone' => 'الحساب غير نشط.']);
        }

        return $next($request);
    }
}
