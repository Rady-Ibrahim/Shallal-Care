<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Doctor\Models\Doctor;

class DoctorApprovedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('web')->user();

        if ($user?->isSecretary()) {
            $staff = $user->clinicStaffMember()->where('status', 'active')->with('doctor')->first();

            if (! $staff || $staff->doctor->status !== 'approved') {
                if ($request->expectsJson() || $request->is('doctor/api/*')) {
                    return response()->json([
                        'success' => false,
                        'error' => ['code' => 'CLINIC_NOT_ACTIVE', 'message' => 'عيادة الطبيب غير نشطة'],
                    ], 403);
                }

                auth('web')->logout();

                return redirect()->route('doctor.login')
                    ->withErrors(['phone' => 'عيادة الطبيب غير نشطة حالياً.']);
            }

            return $next($request);
        }

        $doctor = Doctor::where('user_id', auth('web')->id())->first();

        if (!$doctor) {
            if ($request->expectsJson() || $request->is('doctor/api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'DOCTOR_NOT_FOUND', 'message' => 'ملف الطبيب غير موجود'],
                ], 403);
            }

            auth('web')->logout();

            return redirect()->route('doctor.login')
                ->withErrors(['phone' => 'ملف الطبيب غير موجود. يرجى التواصل مع الإدارة.']);
        }

        if ($doctor->status === 'approved') {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('doctor/api/*')) {
            $message = match ($doctor->status) {
                'pending' => 'حسابك قيد المراجعة من قبل الإدارة',
                'rejected' => 'تم رفض حسابك. يرجى إعادة رفع المستندات',
                'suspended' => 'تم تعليق حسابك',
                default => 'غير مصرح لك بالوصول',
            };

            return response()->json([
                'success' => false,
                'error' => ['code' => 'DOCTOR_NOT_APPROVED', 'message' => $message],
            ], 403);
        }

        return match ($doctor->status) {
            'pending' => redirect()->route('doctor.pending'),
            'rejected' => redirect()->route('doctor.rejected'),
            'suspended' => redirect()->route('doctor.suspended'),
            default => redirect()->route('doctor.pending'),
        };
    }
}
