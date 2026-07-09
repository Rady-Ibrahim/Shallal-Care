<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetSessionCookie
{
    public function handle(Request $request, Closure $next, string $scope = 'default')
    {
        $cookie = match ($scope) {
            'admin' => env('ADMIN_SESSION_COOKIE', 'shallal_care_admin_session'),
            'doctor' => env('DOCTOR_SESSION_COOKIE', 'shallal_care_doctor_session'),
            default => config('session.cookie'),
        };

        config(['session.cookie' => $cookie]);

        return $next($request);
    }
}
