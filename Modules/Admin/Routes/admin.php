<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Web\AdminAuthController;
use Modules\Admin\Http\Controllers\Api\AdminDashboardController;
use Modules\Admin\Http\Controllers\Api\AdminDashboardApiController;
use Modules\Admin\Http\Controllers\Api\AdminCatalogApiController;
use Modules\Auth\Http\Controllers\Admin\AdminUserController;

Route::middleware(['session.scope:admin', 'web'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('guest:web')->group(function () {
            Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [AdminAuthController::class, 'login']);
        });

        Route::middleware(['auth:web', 'admin'])->group(function () {
            Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
            Route::get('/api/csrf-token', fn () => response()->json(['token' => csrf_token()]));

            Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
            Route::get('/dashboard/doctors', fn () => view('admin.doctors.index'))->name('doctors.index');
            Route::get('/dashboard/doctors/{id}', fn () => view('admin.doctors.show'))->name('doctors.show');
            Route::get('/dashboard/patients', fn () => view('admin.patients.index'))->name('patients.index');
            Route::get('/dashboard/patients/{id}', fn () => view('admin.patients.show'))->name('patients.show');
            Route::redirect('/dashboard/appointments', '/admin/dashboard/clinic-bookings')->name('appointments.index');
            Route::get('/dashboard/clinic-bookings', fn () => view('admin.clinic-bookings.index'))->name('clinic-bookings.index');
            Route::get('/dashboard/clinic-patients', fn () => view('admin.clinic-patients.index'))->name('clinic-patients.index');
            Route::get('/dashboard/revenue', fn () => view('admin.revenue'))->name('revenue');
            Route::get('/dashboard/subscriptions', fn () => view('admin.subscriptions.index'))->name('subscriptions.index');
            Route::get('/dashboard/reviews', fn () => view('admin.reviews.index'))->name('reviews.index');
            Route::get('/dashboard/analytics', fn () => view('admin.analytics'))->name('analytics');
            Route::get('/dashboard/specialities', fn () => view('admin.specialities.index'))->name('specialities.index');
            Route::get('/dashboard/governorates', fn () => view('admin.governorates.index'))->name('governorates.index');
            Route::get('/users', fn () => view('admin.users.index'))->name('users.index');
            Route::redirect('/subscriptions', '/admin/dashboard/subscriptions')->name('subscriptions');

            Route::post('/doctors/{id}/approve', [AdminDashboardController::class, 'approveDoctor'])->name('doctors.approve');
            Route::post('/doctors/{id}/reject', [AdminDashboardController::class, 'rejectDoctor'])->name('doctors.reject');
            Route::post('/doctors/{id}/suspend', [AdminDashboardController::class, 'suspendDoctor'])->name('doctors.suspend');
            Route::post('/doctors/{id}/activate', [AdminDashboardController::class, 'activateDoctor'])->name('doctors.activate');

            Route::prefix('api')->group(function () {
                Route::get('/metrics', [AdminDashboardApiController::class, 'metrics']);
                Route::get('/notifications/unread', [AdminDashboardApiController::class, 'unreadNotifications']);
                Route::post('/notifications/{notificationId}/read', [AdminDashboardApiController::class, 'markNotificationRead']);
                Route::post('/notifications/read-all', [AdminDashboardApiController::class, 'markAllNotificationsRead']);
                Route::get('/doctors', [AdminDashboardApiController::class, 'doctors']);
                Route::get('/doctors/{id}', [AdminDashboardApiController::class, 'doctorDetails']);
                Route::delete('/doctors/{id}', [AdminDashboardApiController::class, 'destroyDoctor']);
                Route::post('/doctors/{id}/approve', [AdminDashboardApiController::class, 'approveDoctor']);
                Route::post('/doctors/{id}/reject', [AdminDashboardApiController::class, 'rejectDoctor']);
                Route::post('/doctors/{id}/suspend', [AdminDashboardApiController::class, 'suspendDoctor']);
                Route::post('/doctors/{id}/activate', [AdminDashboardApiController::class, 'activateDoctor']);
                Route::get('/patients', [AdminDashboardApiController::class, 'patients']);
                Route::get('/patients/{id}', [AdminDashboardApiController::class, 'patientDetails']);
                Route::get('/clinic-patients', [AdminDashboardApiController::class, 'clinicPatients']);
                Route::get('/appointments', [AdminDashboardApiController::class, 'appointments']);
                Route::get('/clinic-bookings', [AdminDashboardApiController::class, 'clinicBookings']);
            Route::get('/revenue', [AdminDashboardApiController::class, 'revenue']);
            Route::get('/subscriptions', [AdminDashboardApiController::class, 'subscriptions']);
            Route::get('/subscriptions/plans', [AdminDashboardApiController::class, 'subscriptionPlans']);
            Route::post('/subscriptions/plans', [AdminDashboardApiController::class, 'storeSubscriptionPlan']);
            Route::put('/subscriptions/plans/{id}', [AdminDashboardApiController::class, 'updateSubscriptionPlan']);
            Route::delete('/subscriptions/plans/{id}', [AdminDashboardApiController::class, 'deleteSubscriptionPlan']);
            Route::get('/payment-settings', [AdminDashboardApiController::class, 'paymentSettings']);
            Route::put('/payment-settings', [AdminDashboardApiController::class, 'updatePaymentSettings']);
            Route::post('/subscriptions/{id}/confirm', [AdminDashboardApiController::class, 'confirmSubscription']);
            Route::post('/subscriptions/{id}/reject', [AdminDashboardApiController::class, 'rejectSubscription']);
            Route::get('/analytics', [AdminDashboardApiController::class, 'analytics']);
                Route::get('/reviews', [AdminDashboardApiController::class, 'reviews']);
                Route::post('/reviews/{id}/approve', [AdminDashboardApiController::class, 'approveReview']);
                Route::post('/reviews/{id}/reject', [AdminDashboardApiController::class, 'rejectReview']);
                Route::post('/patients/{id}/block', [AdminDashboardApiController::class, 'blockPatient']);
                Route::post('/patients/{id}/unblock', [AdminDashboardApiController::class, 'unblockPatient']);
                Route::delete('/patients/{id}', [AdminDashboardApiController::class, 'deletePatient']);
                Route::post('/patients/{id}/reset-password', [AdminDashboardApiController::class, 'resetPatientPassword']);
                Route::post('/appointments/{id}/confirm', [AdminDashboardApiController::class, 'confirmAppointment']);
                Route::post('/appointments/{id}/cancel', [AdminDashboardApiController::class, 'cancelAppointment']);
                Route::get('/users', [AdminUserController::class, 'index']);
                Route::post('/users/admins', [AdminUserController::class, 'createAdmin']);
                Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
                Route::post('/users/{id}/block', [AdminUserController::class, 'block']);
                Route::post('/users/{id}/unblock', [AdminUserController::class, 'unblock']);

                Route::get('/specialities', [AdminCatalogApiController::class, 'specialities']);
                Route::post('/specialities', [AdminCatalogApiController::class, 'storeSpeciality']);
                Route::put('/specialities/{id}', [AdminCatalogApiController::class, 'updateSpeciality']);
                Route::delete('/specialities/{id}', [AdminCatalogApiController::class, 'destroySpeciality']);
                Route::get('/governorates', [AdminCatalogApiController::class, 'governorates']);
                Route::post('/governorates', [AdminCatalogApiController::class, 'storeGovernorate']);
                Route::put('/governorates/{id}', [AdminCatalogApiController::class, 'updateGovernorate']);
                Route::delete('/governorates/{id}', [AdminCatalogApiController::class, 'destroyGovernorate']);
            });
        });
    });
});
