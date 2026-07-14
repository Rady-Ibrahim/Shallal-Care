<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('subscriptions:process')->dailyAt('08:00');
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('doctor/*')) {
                return route('doctor.login');
            }

            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            return '/login';
        });

        // تسجيل الـ Middleware الـ Aliases الخاصة بالسيستم
        $middleware->alias([
            'admin'            => \App\Http\Middleware\AdminMiddleware::class,
            'doctor'           => \App\Http\Middleware\DoctorMiddleware::class,
            'clinic.panel'     => \App\Http\Middleware\ClinicPanelMiddleware::class,
            'clinic.context'   => \App\Http\Middleware\ShareClinicDashboardContext::class,
            'clinic.permission'=> \App\Http\Middleware\ClinicPermissionMiddleware::class,
            'clinic.owner'     => \App\Http\Middleware\ClinicOwnerMiddleware::class,
            'clinic.subscription' => \App\Http\Middleware\ClinicActiveSubscriptionMiddleware::class,
            'mobile.feature'   => \App\Http\Middleware\MobileFeatureMiddleware::class,
            'doctor.approved'  => \App\Http\Middleware\DoctorApprovedMiddleware::class,
            'doctor.email.verified' => \App\Http\Middleware\DoctorEmailVerifiedMiddleware::class,
            'session.scope'       => \App\Http\Middleware\SetSessionCookie::class,
            'role'             => \App\Http\Middleware\RoleMiddleware::class, // 🌟 تم إضافة الرول ميدل وير هنا
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // تطبيق حماية الـ Security Headers على كل مسارات الـ API أوتوماتيك
        $middleware->api(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // جلسات admin/doctor منفصلة — SetSessionCookie قبل StartSession
        $middleware->priority([
            \App\Http\Middleware\SetSessionCookie::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // 🎯 الفصل الذكي والمستقر في معالجة الأخطاء
        $exceptions->render(function (Throwable $e, Request $request) {
            
            if ($e instanceof TokenMismatchException) {
                if ($request->expectsJson() || $request->is('*/api/*') || $request->ajax()) {
                    return response()->json(['success' => false], 419);
                }

                if ($request->is('admin/*')) {
                    return redirect()->route('admin.login');
                }

                if ($request->is('doctor/*')) {
                    return redirect()->route('doctor.login');
                }

                return redirect('/');
            }

            if ($e instanceof AuthenticationException) {
                if ($request->expectsJson() || $request->is('*/api/*') || $request->ajax()) {
                    return response()->json(['success' => false], 401);
                }
            }

            // 1️⃣ لو الطلب جاي من الـ API (بوست مان / الموبايل) -> رجع JSON دايماً بنظافة
            if ($request->expectsJson() || $request->is('api/*')) {
                $statusCode = 500;
                
                if (method_exists($e, 'getStatusCode')) {
                    $statusCode = $e->getStatusCode();
                } elseif ($e instanceof AuthenticationException) {
                    $statusCode = 401;
                }

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code'    => class_basename($e),
                        'message' => config('app.debug') ? $e->getMessage() : 'حدث خطأ في الخادم',
                    ],
                ], $statusCode);
            }

            // 2️⃣ لو طلب ويب عادي (المتصفح) -> اترك لارافيل يكمل الـ Redirects وصفحات الـ HTML العادية
            return null;
        });
    })->create();