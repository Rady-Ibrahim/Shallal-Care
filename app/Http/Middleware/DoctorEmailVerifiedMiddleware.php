<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DoctorEmailVerifiedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('web')->user();

        if ($user?->isSecretary()) {
            return $next($request);
        }

        if ($user && $user->email && ! $user->email_verified_at) {
            return redirect()->route('doctor.verify-email')
                ->with('warning', 'يرجى تفعيل بريدك الإلكتروني للمتابعة.');
        }

        return $next($request);
    }
}
