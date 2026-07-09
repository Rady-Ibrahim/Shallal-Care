<?php

use Illuminate\Support\Facades\Route;
use Modules\Doctor\Http\Controllers\Doctor\ClinicStaffController;
use Modules\Doctor\Http\Controllers\Doctor\DoctorDashboardController;
use Modules\Doctor\Http\Controllers\Doctor\ClinicOrderController;
use Modules\Doctor\Http\Controllers\Doctor\ClinicReportController;
use Modules\Doctor\Http\Controllers\Doctor\FinanceController;
use Modules\Doctor\Http\Controllers\Doctor\ReceptionController;
use Modules\Doctor\Http\Controllers\Doctor\VisitController;
use Modules\Doctor\Http\Controllers\Web\DoctorAuthController;
use Modules\Doctor\Http\Controllers\Web\DoctorBranchController;
use Modules\Doctor\Http\Controllers\Web\DoctorDashboardWebController;
use Modules\Doctor\Http\Controllers\Web\DoctorSubscriptionController;
use Modules\Doctor\Http\Controllers\Web\DoctorVerificationController;
use Modules\Doctor\Http\Controllers\Web\PrescriptionPrintController;

Route::middleware(['session.scope:doctor', 'web'])->group(function () {
    Route::redirect('/', '/doctor/login');

    Route::prefix('doctor')->name('doctor.')->group(function () {
        Route::middleware('guest:web')->group(function () {
            Route::get('/login', [DoctorAuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [DoctorAuthController::class, 'login']);
            Route::get('/register', [DoctorAuthController::class, 'showRegister'])->name('register');
            Route::post('/register', [DoctorAuthController::class, 'register']);
        });

        Route::middleware(['auth:web', 'doctor'])->group(function () {
            Route::get('/api/csrf-token', fn () => response()->json(['token' => csrf_token()]));

            Route::get('/verify-email', [DoctorAuthController::class, 'showVerifyEmail'])->name('verify-email');
            Route::post('/verify-email', [DoctorAuthController::class, 'verifyEmail'])->name('verify-email.submit');
            Route::post('/verify-email/resend', [DoctorAuthController::class, 'resendVerificationOtp'])->name('verify-email.resend');
            Route::post('/logout', [DoctorAuthController::class, 'logout'])->name('logout');

            Route::middleware('doctor.email.verified')->group(function () {
                Route::get('/pending', [DoctorVerificationController::class, 'pending'])->name('pending');
                Route::get('/rejected', [DoctorVerificationController::class, 'rejected'])->name('rejected');
                Route::get('/suspended', [DoctorVerificationController::class, 'suspended'])->name('suspended');
                Route::post('/resubmit-documents', [DoctorVerificationController::class, 'resubmit'])->name('resubmit');

                Route::middleware(['doctor.approved', 'clinic.context'])->group(function () {
                    Route::get('/dashboard', [DoctorDashboardWebController::class, 'dashboard'])->name('dashboard');

                    Route::middleware('clinic.owner')->group(function () {
                        Route::get('/dashboard/staff', [DoctorDashboardWebController::class, 'staff'])->name('staff.index');
                        Route::get('/dashboard/subscription/plans', [DoctorDashboardWebController::class, 'subscriptionPlans'])->name('subscription.plans');
                    });

                    Route::middleware('clinic.permission:reception.view')->group(function () {
                        Route::get('/dashboard/reception', [DoctorDashboardWebController::class, 'reception'])->name('reception.index');
                    });

                    Route::middleware('clinic.permission:queue.view')->group(function () {
                        Route::get('/dashboard/queue', [DoctorDashboardWebController::class, 'queue'])->name('queue.index');
                    });

                    Route::middleware('clinic.permission:finance.view')->group(function () {
                        Route::get('/dashboard/finance', [DoctorDashboardWebController::class, 'finance'])->name('finance.index');
                    });

                    Route::middleware('clinic.permission:records.view')->group(function () {
                        Route::get('/dashboard/visits/{bookingId}', [DoctorDashboardWebController::class, 'visit'])->name('visits.show');
                        Route::get('/dashboard/patients/{id}/file', [DoctorDashboardWebController::class, 'patientFile'])->name('patients.file');
                    });

                    Route::middleware('clinic.permission:lab.view')->group(function () {
                        Route::get('/dashboard/orders', [DoctorDashboardWebController::class, 'orders'])->name('orders.index');
                    });

                    Route::middleware('clinic.permission:reports.view')->group(function () {
                        Route::get('/dashboard/reports', [DoctorDashboardWebController::class, 'reports'])->name('reports.index');
                    });

                    Route::middleware('clinic.permission:calendar.view')->group(function () {
                        Route::get('/dashboard/calendar', [DoctorDashboardWebController::class, 'calendar'])->name('calendar');
                    });

                    Route::middleware('clinic.permission:settings.view')->group(function () {
                        Route::get('/dashboard/settings', [DoctorDashboardWebController::class, 'settings'])->name('settings');
                    });

                    Route::middleware('clinic.permission:patients.view')->group(function () {
                        Route::get('/dashboard/patients', [DoctorDashboardWebController::class, 'patients'])->name('patients.index');
                        Route::get('/dashboard/patients/{id}', [DoctorDashboardWebController::class, 'patientShow'])->name('patients.show');
                        Route::get('/dashboard/patients/{id}/records', [DoctorDashboardWebController::class, 'patientRecords'])->name('patients.records');
                    });

                    Route::middleware('clinic.permission:prescriptions.view')->group(function () {
                        Route::get('/dashboard/prescriptions', [DoctorDashboardWebController::class, 'prescriptions'])->name('prescriptions.index');
                        Route::get('/dashboard/prescriptions/create', [DoctorDashboardWebController::class, 'prescriptionCreate'])->name('prescriptions.create');
                        Route::get('/dashboard/prescriptions/{id}', [DoctorDashboardWebController::class, 'prescriptionShow'])->name('prescriptions.show');
                        Route::get('/dashboard/prescriptions/{id}/edit', [DoctorDashboardWebController::class, 'prescriptionEdit'])->name('prescriptions.edit');
                        Route::get('/dashboard/prescriptions/{id}/print', [PrescriptionPrintController::class, 'show'])->name('prescriptions.print');
                    });

                    Route::middleware('clinic.permission:records.view')->group(function () {
                        Route::get('/dashboard/records', [DoctorDashboardWebController::class, 'records'])->name('records.index');
                        Route::get('/dashboard/records/create', [DoctorDashboardWebController::class, 'recordCreate'])->name('records.create');
                        Route::get('/dashboard/records/{id}', [DoctorDashboardWebController::class, 'recordShow'])->name('records.show');
                        Route::get('/dashboard/records/{id}/edit', [DoctorDashboardWebController::class, 'recordEdit'])->name('records.edit');
                    });

                    Route::middleware('clinic.permission:appointments.view')->group(function () {
                        Route::get('/dashboard/requests', [DoctorDashboardWebController::class, 'requests'])->name('requests');
                    });

                    Route::prefix('api')->group(function () {
                        Route::get('/me', [ClinicStaffController::class, 'me']);

                        Route::middleware('clinic.permission:reception.view')->group(function () {
                            Route::get('/reception/stats', [ReceptionController::class, 'stats']);
                            Route::get('/reception/bookings', [ReceptionController::class, 'index']);
                        });

                        Route::middleware('clinic.permission:reception.manage')->group(function () {
                            Route::post('/reception/bookings', [ReceptionController::class, 'store']);
                            Route::post('/reception/bookings/{id}/check-in', [ReceptionController::class, 'checkIn']);
                            Route::post('/reception/bookings/{id}/collect', [ReceptionController::class, 'collectPayment']);
                            Route::patch('/reception/bookings/{id}/status', [ReceptionController::class, 'updateStatus']);
                        });

                        Route::middleware('clinic.permission:queue.view')->group(function () {
                            Route::get('/queue', [ReceptionController::class, 'queue']);
                        });

                        Route::middleware('clinic.permission:finance.view')->group(function () {
                            Route::get('/finance/summary', [FinanceController::class, 'summary']);
                            Route::get('/finance/transactions', [FinanceController::class, 'transactions']);
                            Route::get('/finance/categories', [FinanceController::class, 'categories']);
                        });

                        Route::middleware('clinic.permission:finance.collect')->group(function () {
                            Route::post('/finance/expenses', [FinanceController::class, 'storeExpense']);
                        });

                        Route::middleware('clinic.permission:records.view')->group(function () {
                            Route::get('/visits/{bookingId}', [VisitController::class, 'show']);
                            Route::get('/patients/{id}/timeline', [VisitController::class, 'timeline']);
                            Route::get('/patients/{id}/file', [VisitController::class, 'patientFile']);
                            Route::get('/patients/qr/{token}', [VisitController::class, 'patientFileByQr']);
                        });

                        Route::middleware('clinic.permission:records.manage')->group(function () {
                            Route::post('/visits/{bookingId}/start', [VisitController::class, 'start']);
                            Route::put('/visits/{bookingId}', [VisitController::class, 'update']);
                            Route::post('/visits/{bookingId}/complete', [VisitController::class, 'complete']);
                        });

                        Route::middleware('clinic.permission:lab.view')->group(function () {
                            Route::get('/orders/catalog', [ClinicOrderController::class, 'catalog']);
                            Route::get('/orders/stats', [ClinicOrderController::class, 'stats']);
                            Route::get('/orders', [ClinicOrderController::class, 'index']);
                            Route::get('/orders/{id}', [ClinicOrderController::class, 'show']);
                        });

                        Route::middleware('clinic.permission:lab.manage')->group(function () {
                            Route::post('/orders', [ClinicOrderController::class, 'store']);
                            Route::patch('/orders/{id}/status', [ClinicOrderController::class, 'updateStatus']);
                            Route::post('/orders/{id}/results', [ClinicOrderController::class, 'uploadResults']);
                        });

                        Route::middleware('clinic.permission:reports.view')->group(function () {
                            Route::get('/reports/overview', [ClinicReportController::class, 'overview']);
                        });

                        Route::get('/metrics', [DoctorDashboardController::class, 'metrics']);
                        Route::get('/today-activity', [DoctorDashboardController::class, 'todayActivity']);
                        Route::get('/upcoming-tasks', [DoctorDashboardController::class, 'upcomingTasks']);

                        Route::middleware('clinic.permission:patients.view')->group(function () {
                            Route::get('/patients', [DoctorDashboardController::class, 'patients']);
                            Route::get('/patients/{id}', [DoctorDashboardController::class, 'patientDetails']);
                            Route::get('/patients/{id}/prescriptions', [DoctorDashboardController::class, 'patientPrescriptions']);
                        });

                        Route::post('/ghost-patients', [DoctorDashboardController::class, 'createGhostPatient'])
                            ->middleware('clinic.permission:patients.manage');

                        Route::middleware('clinic.permission:prescriptions.view')->group(function () {
                            Route::get('/prescriptions', [DoctorDashboardController::class, 'prescriptions']);
                            Route::get('/prescriptions/{id}', [DoctorDashboardController::class, 'showPrescription']);
                        });

                        Route::middleware('clinic.permission:prescriptions.manage')->group(function () {
                            Route::post('/prescriptions', [DoctorDashboardController::class, 'storePrescription']);
                            Route::put('/prescriptions/{id}', [DoctorDashboardController::class, 'updatePrescription']);
                            Route::delete('/prescriptions/{id}', [DoctorDashboardController::class, 'destroyPrescription']);
                        });

                        Route::middleware('clinic.permission:records.view')->group(function () {
                            Route::get('/records', [DoctorDashboardController::class, 'records']);
                            Route::get('/records/{id}', [DoctorDashboardController::class, 'showRecord']);
                        });

                        Route::middleware('clinic.permission:records.manage')->group(function () {
                            Route::post('/records', [DoctorDashboardController::class, 'storeRecord']);
                            Route::put('/records/{id}', [DoctorDashboardController::class, 'updateRecord']);
                            Route::delete('/records/{id}', [DoctorDashboardController::class, 'destroyRecord']);
                        });

                        Route::get('/profile', [DoctorDashboardController::class, 'profile']);
                        Route::put('/profile', [DoctorDashboardController::class, 'updateProfile'])
                            ->middleware('clinic.permission:settings.view');
                        Route::put('/professional', [DoctorDashboardController::class, 'updateProfessional'])
                            ->middleware('clinic.owner');

                        Route::get('/schedules', [DoctorDashboardController::class, 'schedules']);
                        Route::delete('/schedules/{scheduleId}', [DoctorDashboardController::class, 'deleteSchedule']);

                        Route::get('/calendar', [DoctorDashboardController::class, 'calendar']);
                        Route::get('/appointments', [DoctorDashboardController::class, 'appointments']);
                        Route::get('/appointments/{appointmentId}', [DoctorDashboardController::class, 'appointmentDetails']);
                        Route::post('/appointments/{appointmentId}/confirm', [DoctorDashboardController::class, 'confirmAppointment'])
                            ->middleware('clinic.permission:appointments.manage');
                        Route::post('/appointments/{appointmentId}/reject', [DoctorDashboardController::class, 'rejectAppointment'])
                            ->middleware('clinic.permission:appointments.manage');
                        Route::post('/appointments/{appointmentId}/complete', [DoctorDashboardController::class, 'completeAppointment'])
                            ->middleware('clinic.permission:appointments.manage');

                        Route::get('/notifications/unread', [DoctorDashboardController::class, 'unreadNotifications']);
                        Route::post('/notifications/{notificationId}/read', [DoctorDashboardController::class, 'markNotificationRead']);
                        Route::post('/notifications/read-all', [DoctorDashboardController::class, 'markAllNotificationsRead']);

                        Route::middleware('clinic.owner')->group(function () {
                            Route::get('/subscription', [DoctorDashboardController::class, 'subscription']);
                            Route::get('/subscription/plans', [DoctorSubscriptionController::class, 'plans']);
                            Route::get('/payment-settings', [DoctorSubscriptionController::class, 'paymentSettings']);
                            Route::post('/subscription/subscribe', [DoctorSubscriptionController::class, 'subscribe']);

                            Route::get('/staff', [ClinicStaffController::class, 'index']);
                            Route::get('/staff/permissions', [ClinicStaffController::class, 'permissionsCatalog']);
                            Route::post('/staff', [ClinicStaffController::class, 'store']);
                            Route::put('/staff/{id}', [ClinicStaffController::class, 'update']);
                            Route::patch('/staff/{id}/status', [ClinicStaffController::class, 'updateStatus']);
                            Route::delete('/staff/{id}', [ClinicStaffController::class, 'destroy']);

                            Route::get('/branches', [DoctorBranchController::class, 'index']);
                            Route::post('/branches', [DoctorBranchController::class, 'store']);
                            Route::put('/branches/{branchId}', [DoctorBranchController::class, 'update']);
                            Route::delete('/branches/{branchId}', [DoctorBranchController::class, 'destroy']);
                        });

                        Route::post('/change-password', [DoctorDashboardController::class, 'changePassword'])
                            ->middleware('clinic.permission:settings.view');
                    });
                });
            });
        });
    });
});
