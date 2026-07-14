<?php

namespace Modules\Subscription\Services;

use Modules\Subscription\Models\Subscription;
use Modules\Subscription\Models\DoctorSubscription;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\ClinicBooking;
use App\Notifications\DoctorSubscriptionRequested;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class SubscriptionService
{
    public function getAllPlans()
    {
        return Subscription::active()
            ->orderByVisibility()
            ->get();
    }

    public function getPlanById($id)
    {
        return Subscription::findOrFail($id);
    }

    public function createPlan(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Subscription::create([
                'name' => $data['name'],
                'description_ar' => $data['description_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'price' => $data['price'],
                'duration_days' => $data['duration_days'],
                'max_appointments' => $data['max_appointments'] ?? null,
                'is_featured' => $data['is_featured'] ?? false,
                'has_analytics' => $data['has_analytics'] ?? false,
                'has_banner' => $data['has_banner'] ?? false,
                'visibility_score' => $data['visibility_score'] ?? 1,
                'features' => $data['features'] ?? null,
                'status' => $data['status'] ?? 'active',
                'sort_order' => $data['sort_order'] ?? 0,
            ]);
        });
    }

    public function updatePlan($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $plan = Subscription::findOrFail($id);
            $plan->update($data);
            return $plan;
        });
    }

    public function deletePlan($id)
    {
        return DB::transaction(function () use ($id) {
            $plan = Subscription::findOrFail($id);
            $plan->delete();
            return true;
        });
    }

    public function subscribeDoctor($doctorId, $subscriptionId, array $paymentData = [])
    {
        return DB::transaction(function () use ($doctorId, $subscriptionId, $paymentData) {
            $doctor = Doctor::findOrFail($doctorId);
            $subscription = Subscription::findOrFail($subscriptionId);

            $activeSubscription = $doctor->doctorSubscriptions()->active()->first();
            if ($activeSubscription) {
                throw new \Exception('Doctor already has an active subscription');
            }

            $pending = $doctor->doctorSubscriptions()->pendingPayment()->first();
            if ($pending) {
                throw new \Exception('Doctor already has a pending payment request');
            }

            $startDate = now();
            $endDate = now()->addDays($subscription->duration_days);

            $doctorSubscription = DoctorSubscription::create([
                'doctor_id' => $doctorId,
                'subscription_id' => $subscriptionId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'amount_paid' => $paymentData['amount'] ?? $subscription->price,
                'payment_method' => $paymentData['payment_method'] ?? null,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'auto_renew' => $paymentData['auto_renew'] ?? false,
            ]);

            $doctor->subscription_id = $subscriptionId;
            $doctor->save();

            return $doctorSubscription;
        });
    }

    public function submitPaymentRequest(
        int $doctorId,
        int $subscriptionId,
        float $submittedAmount,
        UploadedFile $receipt,
        string $paymentMethod = 'bank_transfer'
    ): DoctorSubscription {
        return DB::transaction(function () use ($doctorId, $subscriptionId, $submittedAmount, $receipt, $paymentMethod) {
            $doctor = Doctor::findOrFail($doctorId);
            $subscription = Subscription::findOrFail($subscriptionId);

            if ($doctor->doctorSubscriptions()->active()->exists()) {
                throw new \InvalidArgumentException('لديك اشتراك نشط بالفعل');
            }

            if ($doctor->doctorSubscriptions()->pendingPayment()->exists()) {
                throw new \InvalidArgumentException('لديك طلب دفع قيد المراجعة بالفعل');
            }

            if ((float) $submittedAmount !== (float) $subscription->price) {
                throw new \InvalidArgumentException('المبلغ المُدخل يجب أن يساوي سعر الباقة بالضبط');
            }

            $doctorSubscription = DoctorSubscription::create([
                'doctor_id' => $doctorId,
                'subscription_id' => $subscriptionId,
                'start_date' => null,
                'end_date' => null,
                'status' => 'pending_payment',
                'amount_paid' => 0,
                'submitted_amount' => $submittedAmount,
                'payment_method' => $paymentMethod,
                'payment_receipt' => $receipt->store('subscriptions/receipts', 'public'),
            ]);

            AdminNotificationService::notify(new DoctorSubscriptionRequested($doctorSubscription));

            return $doctorSubscription;
        });
    }

    public function confirmPayment(int $doctorSubscriptionId, int $adminUserId): DoctorSubscription
    {
        return DB::transaction(function () use ($doctorSubscriptionId, $adminUserId) {
            $doctorSubscription = DoctorSubscription::with('subscription')
                ->where('status', 'pending_payment')
                ->findOrFail($doctorSubscriptionId);

            $subscription = $doctorSubscription->subscription;
            $startDate = now();
            $endDate = now()->addDays($subscription->duration_days);

            $doctorSubscription->update([
                'status' => 'active',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'amount_paid' => $doctorSubscription->submitted_amount ?? $subscription->price,
                'reviewed_by' => $adminUserId,
                'reviewed_at' => now(),
                'payment_reject_reason' => null,
                'expiry_reminder_sent_at' => null,
            ]);

            $doctorSubscription->doctor->update(['subscription_id' => $subscription->id]);

            return $doctorSubscription->fresh(['doctor.user', 'subscription']);
        });
    }

    public function rejectPayment(int $doctorSubscriptionId, int $adminUserId, ?string $reason = null): DoctorSubscription
    {
        return DB::transaction(function () use ($doctorSubscriptionId, $adminUserId, $reason) {
            $doctorSubscription = DoctorSubscription::where('status', 'pending_payment')
                ->findOrFail($doctorSubscriptionId);

            $doctorSubscription->update([
                'status' => 'cancelled',
                'payment_reject_reason' => $reason,
                'reviewed_by' => $adminUserId,
                'reviewed_at' => now(),
                'cancelled_at' => now(),
            ]);

            return $doctorSubscription->fresh(['doctor.user', 'subscription']);
        });
    }

    public function getDoctorSubscriptionStatus(int $doctorId): ?array
    {
        $doctor = Doctor::findOrFail($doctorId);

        $pending = $doctor->doctorSubscriptions()->pendingPayment()->with('subscription')->latest()->first();
        if ($pending) {
            return [
                'status' => 'pending_payment',
                'plan_name' => $pending->subscription?->name,
                'price' => $pending->subscription?->price,
                'submitted_amount' => $pending->submitted_amount,
                'payment_method' => $pending->payment_method,
                'payment_reject_reason' => $pending->payment_reject_reason,
                'created_at' => $pending->created_at?->format('Y-m-d'),
            ];
        }

        $active = $doctor->doctorSubscriptions()->active()->with('subscription')->first();
        if ($active) {
            return [
                'status' => 'active',
                'plan_name' => $active->subscription?->name,
                'price' => $active->amount_paid,
                'start_date' => $active->start_date?->format('Y-m-d'),
                'end_date' => $active->end_date?->format('Y-m-d'),
                'days_remaining' => $active->days_remaining,
            ];
        }

        return null;
    }

    public function renewSubscription($doctorId)
    {
        return DB::transaction(function () use ($doctorId) {
            $doctor = Doctor::findOrFail($doctorId);
            $currentSubscription = $doctor->doctorSubscriptions()->active()->first();

            if (!$currentSubscription) {
                throw new \Exception('No active subscription found');
            }

            $subscription = $currentSubscription->subscription;
            $newEndDate = now()->addDays($subscription->duration_days);

            $newSubscription = DoctorSubscription::create([
                'doctor_id' => $doctorId,
                'subscription_id' => $subscription->id,
                'start_date' => now(),
                'end_date' => $newEndDate,
                'status' => 'active',
                'amount_paid' => $subscription->price,
                'payment_method' => $currentSubscription->payment_method,
                'transaction_id' => null,
                'auto_renew' => $currentSubscription->auto_renew,
            ]);

            // Mark old subscription as cancelled
            $currentSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $newSubscription;
        });
    }

    public function cancelSubscription($doctorId)
    {
        return DB::transaction(function () use ($doctorId) {
            $doctor = Doctor::findOrFail($doctorId);
            $activeSubscription = $doctor->doctorSubscriptions()->active()->first();

            if (!$activeSubscription) {
                throw new \Exception('No active subscription found');
            }

            $activeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Remove subscription from doctor
            $doctor->subscription_id = null;
            $doctor->save();

            return true;
        });
    }

    public function getDoctorSubscription($doctorId)
    {
        return DoctorSubscription::with('subscription')
            ->where('doctor_id', $doctorId)
            ->active()
            ->first();
    }

    public function checkDoctorSubscriptionLimit($doctorId): bool
    {
        $status = $this->getClinicBookingLimitStatus((int) $doctorId);

        return $status['can_create_booking'];
    }

    public function getClinicBookingLimitStatus(int $doctorId): array
    {
        $subscription = $this->getDoctorSubscription($doctorId);

        if (! $subscription) {
            return [
                'has_active_subscription' => false,
                'can_create_booking' => false,
                'monthly_bookings_used' => 0,
                'monthly_bookings_limit' => null,
            ];
        }

        $plan = $subscription->subscription;
        $used = ClinicBooking::where('doctor_id', $doctorId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $limit = $plan?->max_appointments;

        return [
            'has_active_subscription' => true,
            'can_create_booking' => $limit === null || $used < $limit,
            'monthly_bookings_used' => $used,
            'monthly_bookings_limit' => $limit,
            'plan_name' => $plan?->name,
        ];
    }

    public function assertCanCreateClinicBooking(int $doctorId): void
    {
        if (! $this->getDoctorSubscription($doctorId)) {
            throw new \InvalidArgumentException('لا يوجد اشتراك نشط للعيادة. يرجى تجديد الاشتراك أولاً.');
        }

        $status = $this->getClinicBookingLimitStatus($doctorId);
        if (! $status['can_create_booking']) {
            $limit = $status['monthly_bookings_limit'];
            throw new \InvalidArgumentException("تم الوصول للحد الشهري للحجوزات ({$limit} حجز). يرجى ترقية الباقة.");
        }
    }

    public function seedDefaultPlans()
    {
        $plans = [
            [
                'name' => 'Basic',
                'description_ar' => 'الباقة الأساسية - ظهور عادي',
                'description_en' => 'Basic Plan - Normal visibility',
                'price' => 0,
                'duration_days' => 30,
                'max_appointments' => 50,
                'is_featured' => false,
                'has_analytics' => false,
                'has_banner' => false,
                'visibility_score' => 1,
                'features' => json_encode([
                    'normal_visibility',
                    '50_appointments_per_month',
                    'basic_support'
                ]),
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'description_ar' => 'الباقة الاحترافية - ظهور أعلى',
                'description_en' => 'Professional Plan - Higher visibility',
                'price' => 50000,
                'duration_days' => 30,
                'max_appointments' => null,
                'is_featured' => false,
                'has_analytics' => true,
                'has_banner' => false,
                'visibility_score' => 2,
                'features' => json_encode([
                    'higher_visibility',
                    'unlimited_appointments',
                    'analytics_dashboard',
                    'priority_support'
                ]),
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium',
                'description_ar' => 'الباقة المميزة - Featured Doctor',
                'description_en' => 'Premium Plan - Featured Doctor',
                'price' => 100000,
                'duration_days' => 30,
                'max_appointments' => null,
                'is_featured' => true,
                'has_analytics' => true,
                'has_banner' => true,
                'visibility_score' => 3,
                'features' => json_encode([
                    'featured_badge',
                    'unlimited_appointments',
                    'advanced_analytics',
                    'banner_display',
                    'dedicated_support',
                    'priority_listing'
                ]),
                'status' => 'active',
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Subscription::create($plan);
        }
    }
}
