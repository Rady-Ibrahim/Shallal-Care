<?php

namespace Modules\Admin\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\AppSetting;
use Modules\Admin\Services\AdminDashboardService;
use Modules\Appointment\Services\Api\AppointmentService;
use Modules\Subscription\Services\SubscriptionService;
use Modules\Review\Services\Api\ReviewService;
use Modules\Review\Models\Review;
use Modules\Auth\Models\User;
use App\Traits\ApiResponse;

class AdminDashboardApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AdminDashboardService $adminDashboardService,
        private AppointmentService $appointmentService,
        private SubscriptionService $subscriptionService,
        private ReviewService $reviewService
    ) {}

    public function metrics(): JsonResponse
    {
        try {
            return $this->success($this->adminDashboardService->getSystemMetrics());
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب المقاييس');
        }
    }

    public function doctors(Request $request): JsonResponse
    {
        try {
            $paginator = $this->adminDashboardService->getDoctorsStats($request->all());
            $paginator->getCollection()->transform(fn ($doctor) => $this->formatDoctor($doctor));

            if ($request->has('limit') && !$request->has('page')) {
                return $this->success($paginator->items());
            }

            return $this->paginated(
                $paginator->items(),
                $paginator->total(),
                $paginator->currentPage(),
                $paginator->perPage()
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الأطباء');
        }
    }

    public function doctorDetails($id): JsonResponse
    {
        try {
            return $this->success($this->adminDashboardService->getDoctorDetails((int) $id));
        } catch (\Exception $e) {
            return $this->notFound('الطبيب غير موجود');
        }
    }

    public function destroyDoctor($id): JsonResponse
    {
        try {
            $this->adminDashboardService->deleteDoctor((int) $id);
            return $this->success(null, 'تم حذف الطبيب بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حذف الطبيب');
        }
    }

    public function patients(Request $request): JsonResponse
    {
        try {
            $paginator = $this->adminDashboardService->getPatientsStats($request->all());
            return $this->paginated(
                $paginator->items(),
                $paginator->total(),
                $paginator->currentPage(),
                $paginator->perPage()
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب المرضى');
        }
    }

    public function patientDetails($id): JsonResponse
    {
        try {
            return $this->success($this->adminDashboardService->getPatientDetails((int) $id));
        } catch (\Exception $e) {
            return $this->notFound('المريض غير موجود');
        }
    }

    public function clinicPatients(Request $request): JsonResponse
    {
        try {
            $paginator = $this->adminDashboardService->getClinicPatientsStats($request->all());

            return $this->paginated(
                $paginator->items(),
                $paginator->total(),
                $paginator->currentPage(),
                $paginator->perPage()
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب ملفات مرضى العيادات');
        }
    }

    public function appointments(Request $request): JsonResponse
    {
        return $this->clinicBookings($request);
    }

    public function clinicBookings(Request $request): JsonResponse
    {
        try {
            $paginator = $this->adminDashboardService->getClinicBookingsStats($request->all());

            if ($request->has('limit') && ! $request->has('page')) {
                return $this->success($paginator->items());
            }

            return $this->paginated(
                $paginator->items(),
                $paginator->total(),
                $paginator->currentPage(),
                $paginator->perPage()
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الحجوزات');
        }
    }

    public function revenue(Request $request): JsonResponse
    {
        try {
            $data = $this->adminDashboardService->getRevenueDashboardData($request->all());
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الإيرادات');
        }
    }

    public function subscriptions(Request $request): JsonResponse
    {
        try {
            $data = $this->adminDashboardService->getSubscriptionsDashboardData($request->all());
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الاشتراكات');
        }
    }

    public function paymentSettings(): JsonResponse
    {
        return $this->success(AppSetting::getPaymentSettings());
    }

    public function updatePaymentSettings(Request $request): JsonResponse
    {
        $request->validate([
            'vodafone_cash_number' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
        ]);

        $settings = AppSetting::updatePaymentSettings($request->only([
            'vodafone_cash_number',
            'bank_name',
            'bank_account_name',
            'bank_account_number',
        ]));

        return $this->success($settings, 'تم حفظ إعدادات الدفع بنجاح');
    }

    public function confirmSubscription($id): JsonResponse
    {
        try {
            $subscription = $this->subscriptionService->confirmPayment((int) $id, auth('web')->id());
            return $this->success($subscription, 'تم تأكيد الاشتراك بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تأكيد الاشتراك');
        }
    }

    public function rejectSubscription(Request $request, $id): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            $subscription = $this->subscriptionService->rejectPayment(
                (int) $id,
                auth('web')->id(),
                $request->reason
            );
            return $this->success($subscription, 'تم رفض طلب الاشتراك');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء رفض الاشتراك');
        }
    }

    public function subscriptionPlans(): JsonResponse
    {
        try {
            return $this->success($this->subscriptionService->getAllPlans());
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب خطط الاشتراك');
        }
    }

    public function storeSubscriptionPlan(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_appointments' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'has_analytics' => 'nullable|boolean',
            'has_banner' => 'nullable|boolean',
            'visibility_score' => 'nullable|integer|min:1|max:10',
            'features' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $plan = $this->subscriptionService->createPlan($request->all());
            return $this->created($plan, 'تم إنشاء خطة الاشتراك بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء إنشاء الخطة');
        }
    }

    public function updateSubscriptionPlan(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
            'max_appointments' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'has_analytics' => 'nullable|boolean',
            'has_banner' => 'nullable|boolean',
            'visibility_score' => 'nullable|integer|min:1|max:10',
            'features' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $plan = $this->subscriptionService->updatePlan((int) $id, $request->all());
            return $this->success($plan, 'تم تحديث خطة الاشتراك بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث الخطة');
        }
    }

    public function deleteSubscriptionPlan($id): JsonResponse
    {
        try {
            $this->subscriptionService->deletePlan((int) $id);
            return $this->success(null, 'تم حذف خطة الاشتراك بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حذف الخطة');
        }
    }

    public function analytics(Request $request): JsonResponse
    {
        try {
            $data = $this->adminDashboardService->getAnalyticsData(
                $request->get('period', 'month'),
                $request->get('type')
            );
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب التحليلات');
        }
    }

    public function approveDoctor($id): JsonResponse
    {
        try {
            $doctor = $this->adminDashboardService->approveDoctor($id);
            return $this->success($doctor, 'تم تفعيل حساب الطبيب بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تفعيل حساب الطبيب');
        }
    }

    public function rejectDoctor(Request $request, $id): JsonResponse
    {
        try {
            $doctor = $this->adminDashboardService->rejectDoctor($id, $request->input('reject_reason'));
            return $this->success($doctor, 'تم رفض حساب الطبيب بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء رفض حساب الطبيب');
        }
    }

    public function suspendDoctor($id): JsonResponse
    {
        try {
            $doctor = $this->adminDashboardService->suspendDoctor($id);
            return $this->success($doctor, 'تم تعليق حساب الطبيب بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تعليق حساب الطبيب');
        }
    }

    public function activateDoctor($id): JsonResponse
    {
        try {
            $doctor = $this->adminDashboardService->activateDoctor($id);
            return $this->success($doctor, 'تم تفعيل حساب الطبيب بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تفعيل حساب الطبيب');
        }
    }

    public function blockPatient($id): JsonResponse
    {
        try {
            $patient = \Modules\Auth\Models\User::where('role', 'patient')->findOrFail($id);
            $patient->update(['status' => 'blocked']);
            return $this->success($patient, 'تم حظر المريض بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حظر المريض');
        }
    }

    public function unblockPatient($id): JsonResponse
    {
        try {
            $patient = \Modules\Auth\Models\User::where('role', 'patient')->findOrFail($id);
            $patient->update(['status' => 'active']);
            return $this->success($patient, 'تم فك الحظر عن المريض بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء فك الحظر');
        }
    }

    public function deletePatient($id): JsonResponse
    {
        try {
            $patient = \Modules\Auth\Models\User::where('role', 'patient')->findOrFail($id);
            $patient->delete();
            return $this->success(null, 'تم حذف المريض بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حذف المريض');
        }
    }

    public function resetPatientPassword($id): JsonResponse
    {
        try {
            $patient = \Modules\Auth\Models\User::where('role', 'patient')->findOrFail($id);
            $patient->update(['password' => 'password123']);
            return $this->success(null, 'تم إعادة تعيين كلمة المرور بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء إعادة تعيين كلمة المرور');
        }
    }

    public function confirmAppointment($id): JsonResponse
    {
        try {
            $appointment = $this->appointmentService->confirm((string) $id);

            if (!$appointment) {
                return $this->error('لا يمكن تأكيد هذا الموعد', 'INVALID_STATUS', 400);
            }

            return $this->success($appointment, 'تم تأكيد الموعد بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تأكيد الموعد');
        }
    }

    public function cancelAppointment($id): JsonResponse
    {
        try {
            $appointment = \Modules\Appointment\Models\Appointment::findOrFail($id);

            if ($appointment->status === 'cancelled') {
                return $this->error('الموعد ملغي بالفعل', 'INVALID_STATUS', 400);
            }

            $appointment->update(['status' => 'cancelled']);

            $appointment->load('patient');
            if ($appointment->patient?->isPatient()) {
                $appointment->patient->notify(
                    new \App\Notifications\AppointmentStatusChanged($appointment, 'إلغاء', 'appointment_cancelled')
                );
            }

            return $this->success($appointment, 'تم إلغاء الموعد بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء إلغاء الموعد');
        }
    }

    public function unreadNotifications(): JsonResponse
    {
        try {
            $user = User::findOrFail(auth('web')->id());
            $notifications = $user
                ->unreadNotifications()
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'إشعار',
                    'message' => $notification->data['message'] ?? '',
                    'type' => $notification->data['type'] ?? 'general',
                    'action_url' => $notification->data['action_url'] ?? null,
                    'created_at' => $notification->created_at?->format('Y-m-d H:i'),
                ])
                ->values()
                ->all();

            return $this->success([
                'count' => count($notifications),
                'items' => $notifications,
            ]);
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الإشعارات');
        }
    }

    public function markNotificationRead($notificationId): JsonResponse
    {
        try {
            $user = User::findOrFail(auth('web')->id());
            $notification = $user
                ->notifications()
                ->where('id', $notificationId)
                ->firstOrFail();

            $notification->markAsRead();

            return $this->success(null, 'تم تعليم الإشعار كمقروء');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث الإشعار');
        }
    }

    public function markAllNotificationsRead(): JsonResponse
    {
        try {
            $user = User::findOrFail(auth('web')->id());
            $user->unreadNotifications->markAsRead();

            return $this->success(null, 'تم تعليم جميع الإشعارات كمقروءة');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث الإشعارات');
        }
    }

    public function reviews(Request $request): JsonResponse
    {
        try {
            $paginator = $this->adminDashboardService->getReviews($request->all());
            $items = $paginator->getCollection()
                ->map(fn (Review $review) => $this->adminDashboardService->formatReview($review))
                ->values()
                ->all();

            return $this->paginated(
                $items,
                $paginator->total(),
                $paginator->currentPage(),
                $paginator->perPage()
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب التقييمات');
        }
    }

    public function approveReview($id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            $approved = $this->reviewService->approve($review, (int) auth('web')->id());

            return $this->success(
                $this->adminDashboardService->formatReview($approved),
                'تمت الموافقة على التقييم'
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء الموافقة على التقييم');
        }
    }

    public function rejectReview(Request $request, $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $review = Review::findOrFail($id);
            $rejected = $this->reviewService->reject(
                $review,
                (int) auth('web')->id(),
                $request->input('reason')
            );

            return $this->success(
                $this->adminDashboardService->formatReview($rejected),
                'تم رفض التقييم'
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء رفض التقييم');
        }
    }

    protected function formatDoctor($doctor): array
    {
        return [
            'id' => $doctor->id,
            'name' => $doctor->user?->name,
            'phone' => $doctor->user?->phone,
            'email' => $doctor->user?->email,
            'speciality' => $doctor->speciality?->name_ar,
            'speciality_id' => $doctor->speciality_id,
            'status' => $doctor->status,
            'rating' => $doctor->rating,
            'experience_years' => $doctor->experience_years,
            'branches_count' => $doctor->branches_count ?? 0,
            'visits_count' => $doctor->visits_count ?? 0,
            'bookings_today' => $doctor->bookings_today ?? 0,
            'created_at' => $doctor->created_at,
        ];
    }
}
