<?php

namespace Modules\Admin\Services;

use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\DoctorBranch;
use Modules\Doctor\Models\ClinicBooking;
use Modules\Doctor\Models\ClinicPatient;
use Modules\Doctor\Models\ClinicStaffMember;
use Modules\Doctor\Models\BranchTransaction;
use Modules\Doctor\Models\Speciality;
use Modules\Auth\Models\User;
use Modules\Appointment\Models\Appointment;
use Modules\Subscription\Models\DoctorSubscription;
use Modules\Subscription\Models\Subscription;
use Modules\Review\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminDashboardService
{
    public function getSystemMetrics()
    {
        $totalDoctors = Doctor::count();
        $activeDoctors = Doctor::where('status', 'approved')->count();
        $pendingDoctors = Doctor::where('status', 'pending')->count();
        $suspendedDoctors = Doctor::where('status', 'suspended')->count();

        $totalPatients = User::where('role', 'patient')->where('status', 'active')->count();
        $ghostPatients = User::where('role', 'patient')->where('is_ghost', true)->count();

        $clinicBookingsTotal = ClinicBooking::count();
        $clinicBookingsToday = ClinicBooking::whereDate('visit_date', today())->count();
        $clinicBookingsCompleted = ClinicBooking::where('status', ClinicBooking::STATUS_COMPLETED)->count();
        $clinicBookingsWaiting = ClinicBooking::whereDate('visit_date', today())
            ->whereIn('status', [ClinicBooking::STATUS_WAITING, ClinicBooking::STATUS_WITH_DOCTOR])
            ->count();
        $clinicBookingsCancelled = ClinicBooking::where('status', ClinicBooking::STATUS_CANCELLED)->count();
        $clinicPatientsTotal = ClinicPatient::count();
        $clinicBranchesTotal = DoctorBranch::count();
        $clinicStaffActive = ClinicStaffMember::where('status', 'active')->count();
        $clinicRevenueToday = BranchTransaction::where('type', 'income')
            ->whereDate('created_at', today())
            ->sum('amount');
        $clinicRevenueMonth = BranchTransaction::where('type', 'income')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        $clinicExpenseMonth = BranchTransaction::where('type', 'expense')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $subscriptionRevenueTotal = DoctorSubscription::sum('amount_paid');
        $monthlySubscriptionRevenue = DoctorSubscription::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_paid');
        $lastMonthSubscriptionRevenue = DoctorSubscription::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount_paid');
        $subscriptionGrowth = $lastMonthSubscriptionRevenue > 0
            ? round((($monthlySubscriptionRevenue - $lastMonthSubscriptionRevenue) / $lastMonthSubscriptionRevenue) * 100, 1)
            : 0;

        $avgRating = Review::approved()->avg('rating') ?? 0;
        $totalReviews = Review::approved()->count();
        $pendingReviews = Review::pending()->count();

        $activeSubscriptions = DoctorSubscription::where('status', 'active')->count();
        $expiredSubscriptions = DoctorSubscription::where('status', 'expired')->count();

        return [
            'doctors' => [
                'total' => $totalDoctors,
                'active' => $activeDoctors,
                'pending' => $pendingDoctors,
                'suspended' => $suspendedDoctors,
            ],
            'patients' => [
                'total' => $totalPatients,
                'clinic' => $clinicPatientsTotal,
                'ghost' => $ghostPatients,
            ],
            'subscriptions' => [
                'total_revenue' => (float) $subscriptionRevenueTotal,
                'monthly' => (float) $monthlySubscriptionRevenue,
                'growth' => $subscriptionGrowth,
                'active' => $activeSubscriptions,
                'expired' => $expiredSubscriptions,
            ],
            'reviews' => [
                'average' => round($avgRating, 2),
                'total' => $totalReviews,
                'pending' => $pendingReviews,
            ],
            'clinic' => [
                'bookings_total' => $clinicBookingsTotal,
                'bookings_today' => $clinicBookingsToday,
                'bookings_completed' => $clinicBookingsCompleted,
                'bookings_waiting' => $clinicBookingsWaiting,
                'bookings_cancelled' => $clinicBookingsCancelled,
                'patients' => $clinicPatientsTotal,
                'branches' => $clinicBranchesTotal,
                'staff' => $clinicStaffActive,
                'revenue_today' => (float) $clinicRevenueToday,
                'revenue_month' => (float) $clinicRevenueMonth,
                'expense_month' => (float) $clinicExpenseMonth,
                'net_month' => (float) ($clinicRevenueMonth - $clinicExpenseMonth),
            ],
        ];
    }

    public function getDoctorsStats($filters = [])
    {
        $limit = (int) ($filters['limit'] ?? 20);

        $query = Doctor::with(['user', 'speciality', 'subscription'])
            ->withCount('branches')
            ->withCount('clinicBookings as visits_count')
            ->withCount(['clinicBookings as bookings_today' => fn ($q) => $q->whereDate('visit_date', today())]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['speciality_id'])) {
            $query->where('speciality_id', $filters['speciality_id']);
        }

        if (isset($filters['search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('phone', 'like', '%'.$filters['search'].'%');
            });
        }

        $doctors = $query->orderBy('created_at', 'desc')->paginate($limit);

        return $doctors;
    }

    public function getDoctorDetails(int $doctorId): array
    {
        $doctor = Doctor::with(['user', 'speciality', 'branches', 'schedules'])->findOrFail($doctorId);

        $totalVisits = ClinicBooking::where('doctor_id', $doctorId)->count();
        $completedVisits = ClinicBooking::where('doctor_id', $doctorId)
            ->where('status', ClinicBooking::STATUS_COMPLETED)
            ->count();
        $totalPatients = ClinicPatient::where('doctor_id', $doctorId)->count();
        $totalReviews = Review::where('doctor_id', $doctorId)->approved()->count();
        $staffCount = ClinicStaffMember::where('doctor_id', $doctorId)->where('status', 'active')->count();
        $clinicRevenue = BranchTransaction::where('doctor_id', $doctorId)
            ->where('type', 'income')
            ->sum('amount');

        $recentReviews = Review::where('doctor_id', $doctorId)
            ->approved()
            ->with('patient')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'status' => $review->status,
                'patient_name' => $review->patient?->name,
                'created_at' => $review->created_at,
            ])
            ->values()
            ->all();

        return [
            'id' => $doctor->id,
            'name' => $doctor->user?->name,
            'phone' => $doctor->user?->phone,
            'email' => $doctor->user?->email,
            'speciality' => $doctor->speciality?->name_ar,
            'speciality_id' => $doctor->speciality_id,
            'status' => $doctor->status,
            'experience_years' => $doctor->experience_years,
            'consultation_fee' => $doctor->consultation_fee,
            'rating' => $doctor->rating,
            'bio' => $doctor->bio_ar ?: $doctor->bio_en,
            'address' => $doctor->address,
            'reject_reason' => $doctor->reject_reason,
            'license_document' => storage_public_url($doctor->license_document),
            'clinic_image' => storage_public_url($doctor->clinic_image),
            'created_at' => $doctor->created_at,
            'total_visits' => $totalVisits,
            'completed_visits' => $completedVisits,
            'total_patients' => $totalPatients,
            'staff_count' => $staffCount,
            'clinic_revenue' => (float) $clinicRevenue,
            'total_reviews' => $totalReviews,
            'total_appointments' => $totalVisits,
            'completed_appointments' => $completedVisits,
            'branches' => $doctor->branches->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->branch_name,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'governorate' => $branch->governorate,
                'is_primary' => $branch->is_primary,
            ])->values()->all(),
            'schedules' => $doctor->schedules->map(fn ($schedule) => [
                'id' => $schedule->id,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ])->values()->all(),
            'recent_reviews' => $recentReviews,
        ];
    }

    public function deleteDoctor(int $doctorId): void
    {
        $doctor = Doctor::findOrFail($doctorId);
        $doctor->user?->delete();
        $doctor->delete();
    }

    public function getPatientsStats($filters = [])
    {
        $limit = (int) ($filters['limit'] ?? 20);

        $query = User::where('role', 'patient')
            ->withCount('clinicBookings as total_bookings');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_ghost'])) {
            if ($filters['is_ghost'] === 'ghost') {
                $query->where('is_ghost', true);
            } elseif ($filters['is_ghost'] === 'regular') {
                $query->where('is_ghost', false);
            } else {
                $query->where('is_ghost', filter_var($filters['is_ghost'], FILTER_VALIDATE_BOOLEAN));
            }
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate($limit);
        $patients->getCollection()->transform(fn (User $patient) => $this->formatPatient($patient));

        return $patients;
    }

    public function getPatientDetails(int $patientId): array
    {
        $patient = User::where('role', 'patient')->findOrFail($patientId);

        $totalBookings = ClinicBooking::where('patient_id', $patientId)->count();
        $completedBookings = ClinicBooking::where('patient_id', $patientId)
            ->where('status', ClinicBooking::STATUS_COMPLETED)
            ->count();
        $cancelledBookings = ClinicBooking::where('patient_id', $patientId)
            ->where('status', ClinicBooking::STATUS_CANCELLED)
            ->count();

        $clinicProfiles = ClinicPatient::where('patient_id', $patientId)
            ->with(['doctor.user', 'branch'])
            ->withCount('bookings')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ClinicPatient $profile) => [
                'id' => $profile->id,
                'file_number' => $profile->file_number,
                'doctor_name' => $profile->doctor?->user?->name,
                'doctor_id' => $profile->doctor_id,
                'branch_name' => $profile->branch?->branch_name,
                'bookings_count' => $profile->bookings_count,
                'allergies' => $profile->allergies,
                'chronic_conditions' => $profile->chronic_conditions,
                'created_at' => $profile->created_at?->format('Y-m-d'),
            ])
            ->values()
            ->all();

        $recentBookings = ClinicBooking::where('patient_id', $patientId)
            ->with(['doctor.user', 'doctor.speciality', 'branch'])
            ->orderByDesc('visit_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn (ClinicBooking $booking) => $this->formatClinicBooking($booking))
            ->values()
            ->all();

        return array_merge($this->formatPatient($patient), [
            'total_bookings' => $totalBookings,
            'completed_bookings' => $completedBookings,
            'cancelled_bookings' => $cancelledBookings,
            'total_appointments' => $totalBookings,
            'completed_appointments' => $completedBookings,
            'cancelled_appointments' => $cancelledBookings,
            'clinic_profiles_count' => count($clinicProfiles),
            'clinic_profiles' => $clinicProfiles,
            'recent_bookings' => $recentBookings,
            'recent_appointments' => $recentBookings,
        ]);
    }

    public function getClinicPatientsStats(array $filters = [])
    {
        $limit = (int) ($filters['limit'] ?? 20);

        $query = ClinicPatient::with(['patient', 'doctor.user', 'branch'])
            ->withCount('bookings');

        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', (int) $filters['doctor_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('file_number', 'like', '%'.$search.'%')
                    ->orWhereHas('patient', function ($sub) use ($search) {
                        $sub->where('name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('doctor.user', function ($sub) use ($search) {
                        $sub->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        $patients = $query->orderByDesc('created_at')->paginate($limit);
        $patients->getCollection()->transform(fn (ClinicPatient $profile) => $this->formatClinicPatient($profile));

        return $patients;
    }

    public function getAppointmentsStats($filters = [])
    {
        return $this->getClinicBookingsStats($filters);
    }

    public function getClinicBookingsStats(array $filters = [])
    {
        $limit = (int) ($filters['limit'] ?? 20);

        $query = ClinicBooking::with(['doctor.user', 'doctor.speciality', 'patient', 'branch']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', (int) $filters['doctor_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('visit_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('visit_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($sub) use ($search) {
                    $sub->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                })->orWhereHas('doctor.user', function ($sub) use ($search) {
                    $sub->where('name', 'like', '%'.$search.'%');
                })->orWhere('booking_number', 'like', '%'.$search.'%');
            });
        }

        $bookings = $query->orderByDesc('visit_date')->orderByDesc('created_at')->paginate($limit);
        $bookings->getCollection()->transform(fn (ClinicBooking $booking) => $this->formatClinicBooking($booking));

        return $bookings;
    }

    public function getRevenueDashboardData(array $filters = []): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($filters);

        $clinicIncomeQuery = BranchTransaction::with(['doctor.user', 'branch'])
            ->where('type', 'income')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $clinicExpenseQuery = BranchTransaction::where('type', 'expense')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $subscriptionQuery = DoctorSubscription::with(['doctor.user', 'subscription'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        $clinicIncome = (float) (clone $clinicIncomeQuery)->sum('amount');
        $clinicExpense = (float) (clone $clinicExpenseQuery)->sum('amount');
        $subscriptionRevenue = (float) (clone $subscriptionQuery)->sum('amount_paid');
        $totalCombined = $clinicIncome + $subscriptionRevenue;

        $completedVisits = ClinicBooking::where('status', ClinicBooking::STATUS_COMPLETED)
            ->whereBetween('visit_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->count();

        $clinicTransactions = (clone $clinicIncomeQuery)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'date' => $item->created_at?->format('Y-m-d'),
                'type' => 'clinic_income',
                'description' => ($item->doctor?->user?->name ?? 'عيادة').' — '.($item->branch?->branch_name ?? 'فرع'),
                'amount' => (float) $item->amount,
            ]);

        $subscriptionTransactions = (clone $subscriptionQuery)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'date' => $item->created_at?->format('Y-m-d'),
                'type' => 'subscription',
                'description' => 'اشتراك: '.($item->doctor?->user?->name ?? 'طبيب'),
                'amount' => (float) $item->amount_paid,
            ]);

        $recentTransactions = $clinicTransactions
            ->concat($subscriptionTransactions)
            ->sortByDesc('date')
            ->take(10)
            ->values()
            ->all();

        $topPerformers = Doctor::with('user')
            ->withCount(['clinicBookings as visits_count' => fn ($q) => $q
                ->where('status', ClinicBooking::STATUS_COMPLETED)
                ->whereBetween('visit_date', [$startDate->toDateString(), $endDate->toDateString()])])
            ->orderByDesc('visits_count')
            ->limit(5)
            ->get()
            ->map(fn ($doctor) => [
                'name' => $doctor->user?->name,
                'visits' => $doctor->visits_count,
                'revenue' => (float) BranchTransaction::where('doctor_id', $doctor->id)
                    ->where('type', 'income')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount'),
            ])
            ->values()
            ->all();

        $transactionCount = (clone $subscriptionQuery)->count() + (clone $clinicIncomeQuery)->count();

        $dailyClinicRevenue = BranchTransaction::where('type', 'income')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $dailySubscriptionRevenue = DoctorSubscription::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount_paid) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $dailyRevenue = [];
        $cursor = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $clinic = (float) ($dailyClinicRevenue[$key] ?? 0);
            $subs = (float) ($dailySubscriptionRevenue[$key] ?? 0);
            $dailyRevenue[] = [
                'date' => $key,
                'clinic_income' => $clinic,
                'subscription_revenue' => $subs,
                'total' => $clinic + $subs,
            ];
            $cursor->addDay();
        }

        return [
            'total_revenue' => $totalCombined,
            'clinic_income' => $clinicIncome,
            'clinic_expense' => $clinicExpense,
            'clinic_net' => $clinicIncome - $clinicExpense,
            'completed_visits' => $completedVisits,
            'completed_appointments' => $completedVisits,
            'subscription_revenue' => $subscriptionRevenue,
            'average_revenue' => $transactionCount > 0 ? round($totalCombined / $transactionCount, 2) : 0,
            'revenue_by_category' => [
                ['name' => 'إيرادات العيادات', 'amount' => $clinicIncome, 'percentage' => $totalCombined > 0 ? round(($clinicIncome / $totalCombined) * 100) : 0],
                ['name' => 'اشتراكات الأطباء', 'amount' => $subscriptionRevenue, 'percentage' => $totalCombined > 0 ? round(($subscriptionRevenue / $totalCombined) * 100) : 0],
            ],
            'daily_revenue' => $dailyRevenue,
            'top_performers' => $topPerformers,
            'recent_transactions' => $recentTransactions,
            'period' => [
                'from' => $startDate->toDateString(),
                'to' => $endDate->toDateString(),
            ],
        ];
    }

    public function getSubscriptionsDashboardData(array $filters = []): array
    {
        $limit = (int) ($filters['limit'] ?? 20);
        $status = $filters['status'] ?? null;

        $plans = Subscription::orderBy('sort_order')->get()->map(fn ($plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
            'price' => $plan->price,
            'duration_days' => $plan->duration_days,
            'status' => $plan->status,
            'subscribers_count' => DoctorSubscription::where('subscription_id', $plan->id)->where('status', 'active')->count(),
        ])->values()->all();

        $query = DoctorSubscription::with(['doctor.user', 'subscription'])->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $paginator = $query->paginate($limit);
        $subscriptions = $paginator->getCollection()->map(fn ($sub) => [
            'id' => $sub->id,
            'doctor_name' => $sub->doctor?->user?->name,
            'plan_name' => $sub->subscription?->name,
            'amount_paid' => $sub->amount_paid,
            'submitted_amount' => $sub->submitted_amount,
            'payment_method' => $sub->payment_method,
            'payment_receipt' => storage_public_url($sub->payment_receipt),
            'payment_reject_reason' => $sub->payment_reject_reason,
            'status' => $sub->status,
            'start_date' => $sub->start_date?->format('Y-m-d'),
            'end_date' => $sub->end_date?->format('Y-m-d'),
            'created_at' => $sub->created_at?->format('Y-m-d'),
        ])->values()->all();

        return [
            'plans' => $plans,
            'subscriptions' => $subscriptions,
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'summary' => [
                'active' => DoctorSubscription::where('status', 'active')->count(),
                'expired' => DoctorSubscription::where('status', 'expired')->count(),
                'pending_payment' => DoctorSubscription::where('status', 'pending_payment')->count(),
                'total_revenue' => DoctorSubscription::sum('amount_paid'),
            ],
        ];
    }

    public function getAnalyticsData($period = '30days', $type = null)
    {
        $startDate = $this->resolvePeriodStart($period);

        $dailyUsers = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyBookings = ClinicBooking::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyRevenue = BranchTransaction::where('type', 'income')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $subscriptionDailyRevenue = DoctorSubscription::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount_paid) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalUsers = User::count();
        $activeDoctors = Doctor::where('status', 'approved')->count();
        $dailyBookingsCount = ClinicBooking::whereDate('visit_date', today())->count();
        $totalBookings = ClinicBooking::count();
        $clinicPatientsTotal = ClinicPatient::count();
        $conversionRate = $clinicPatientsTotal > 0
            ? round(($totalBookings / $clinicPatientsTotal) * 100, 1)
            : 0;

        $specialities = Speciality::withCount('doctors')->orderByDesc('doctors_count')->limit(5)->get();
        $totalDoctorsForSpecialities = max($specialities->sum('doctors_count'), 1);

        $topSpecialities = $specialities->map(fn ($speciality) => [
            'name' => $speciality->name_ar,
            'count' => $speciality->doctors_count,
            'percentage' => round(($speciality->doctors_count / $totalDoctorsForSpecialities) * 100),
        ])->values()->all();

        $maleCount = User::where('role', 'patient')->where('gender', 'male')->count();
        $femaleCount = User::where('role', 'patient')->where('gender', 'female')->count();
        $patientTotal = max($maleCount + $femaleCount, 1);

        $peakHoursRaw = ClinicBooking::where('created_at', '>=', $startDate)
            ->whereNotNull('checked_in_at')
            ->selectRaw('HOUR(checked_in_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $peakTotal = max($peakHoursRaw->sum('count'), 1);
        $peakHours = $peakHoursRaw->map(function ($row) use ($peakTotal) {
            $hour = (int) $row->hour;
            $nextHour = ($hour + 1) % 24;

            return [
                'time_range' => sprintf('%02d:00 - %02d:00', $hour, $nextHour),
                'count' => $row->count,
                'percentage' => round(($row->count / $peakTotal) * 100),
            ];
        })->values()->all();

        $geoRaw = DoctorBranch::selectRaw('governorate, COUNT(*) as count')
            ->whereNotNull('governorate')
            ->groupBy('governorate')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        $geoTotal = max($geoRaw->sum('count'), 1);
        $geographicDistribution = $geoRaw->map(fn ($row) => [
            'name' => $row->governorate,
            'count' => $row->count,
            'percentage' => round(($row->count / $geoTotal) * 100),
        ])->values()->all();

        return [
            'total_users' => $totalUsers,
            'active_doctors' => $activeDoctors,
            'daily_bookings' => $dailyBookingsCount,
            'daily_appointments' => $dailyBookingsCount,
            'conversion_rate' => $conversionRate,
            'clinic_patients' => ClinicPatient::count(),
            'clinic_branches' => DoctorBranch::count(),
            'top_specialities' => $topSpecialities,
            'demographics' => [
                'male' => $maleCount,
                'female' => $femaleCount,
                'age_18_25' => 0,
                'age_26_35' => 0,
                'age_36_45' => 0,
                'age_46_plus' => 0,
            ],
            'peak_hours' => $peakHours,
            'geographic_distribution' => $geographicDistribution,
            'users' => $dailyUsers,
            'bookings' => $dailyBookings,
            'appointments' => $dailyBookings,
            'revenue' => $dailyRevenue,
            'subscription_revenue' => $subscriptionDailyRevenue,
            'type' => $type,
        ];
    }

    public function approveDoctor($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $doctor->status = 'approved';
        $doctor->save();
        return $doctor;
    }

    public function rejectDoctor($doctorId, ?string $reason = null)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $doctor->status = 'rejected';
        $doctor->reject_reason = $reason;
        $doctor->save();
        return $doctor;
    }

    public function suspendDoctor($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $doctor->status = 'suspended';
        $doctor->save();
        return $doctor;
    }

    public function activateDoctor($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $doctor->status = 'approved';
        $doctor->save();
        return $doctor;
    }

    protected function formatPatient(User $patient): array
    {
        $totalBookings = $patient->total_bookings ?? $patient->clinic_bookings_count ?? 0;

        return [
            'id' => $patient->id,
            'name' => $patient->name,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'status' => $patient->status,
            'is_ghost' => (bool) $patient->is_ghost,
            'created_at' => $patient->created_at,
            'total_bookings' => $totalBookings,
            'total_appointments' => $totalBookings,
        ];
    }

    protected function formatClinicPatient(ClinicPatient $profile): array
    {
        return [
            'id' => $profile->id,
            'file_number' => $profile->file_number,
            'patient_id' => $profile->patient_id,
            'patient_name' => $profile->patient?->name,
            'patient_phone' => $profile->patient?->phone,
            'doctor_id' => $profile->doctor_id,
            'doctor_name' => $profile->doctor?->user?->name,
            'branch_name' => $profile->branch?->branch_name,
            'bookings_count' => $profile->bookings_count ?? 0,
            'allergies' => $profile->allergies,
            'chronic_conditions' => $profile->chronic_conditions,
            'created_at' => $profile->created_at?->format('Y-m-d'),
        ];
    }

    protected function formatClinicBooking(ClinicBooking $booking): array
    {
        return [
            'id' => $booking->id,
            'booking_number' => $booking->display_booking_number,
            'patient_name' => $booking->patient?->name,
            'patient_phone' => $booking->patient?->phone,
            'doctor_name' => $booking->doctor?->user?->name,
            'doctor_id' => $booking->doctor_id,
            'speciality' => $booking->doctor?->speciality?->name_ar,
            'branch_name' => $booking->branch?->branch_name,
            'visit_date' => $booking->visit_date?->format('Y-m-d'),
            'date' => $booking->visit_date?->format('Y-m-d'),
            'consultation_fee' => (float) $booking->consultation_fee,
            'price' => (float) $booking->consultation_fee,
            'payment_status' => $booking->payment_status,
            'status' => $booking->status,
            'status_label' => $this->clinicBookingStatusLabel($booking->status),
            'created_at' => $booking->created_at?->format('Y-m-d H:i'),
        ];
    }

    protected function clinicBookingStatusLabel(string $status): string
    {
        return match ($status) {
            ClinicBooking::STATUS_SCHEDULED => 'محجوز',
            ClinicBooking::STATUS_CHECKED_IN => 'حضر',
            ClinicBooking::STATUS_WAITING => 'في الدور',
            ClinicBooking::STATUS_WITH_DOCTOR => 'عند الطبيب',
            ClinicBooking::STATUS_COMPLETED => 'مكتمل',
            ClinicBooking::STATUS_CANCELLED => 'ملغي',
            ClinicBooking::STATUS_NO_SHOW => 'لم يحضر',
            default => $status,
        };
    }

    protected function formatAppointment(Appointment $appointment): array
    {
        $time = $appointment->appointment_time;

        if ($time instanceof Carbon) {
            $formattedTime = $time->format('H:i');
        } elseif ($time) {
            $formattedTime = Carbon::parse($time)->format('H:i');
        } else {
            $formattedTime = '-';
        }

        return [
            'id' => $appointment->id,
            'patient_name' => $appointment->patient?->name,
            'patient_phone' => $appointment->patient?->phone,
            'doctor_name' => $appointment->doctor?->user?->name,
            'speciality' => $appointment->doctor?->speciality?->name_ar,
            'appointment_date' => $appointment->appointment_date?->format('Y-m-d'),
            'date' => $appointment->appointment_date?->format('Y-m-d'),
            'appointment_time' => $formattedTime,
            'price' => $appointment->price,
            'status' => $appointment->status,
        ];
    }

    protected function resolvePeriodStart(string $period): Carbon
    {
        return match ($period) {
            'today' => now()->startOfDay(),
            'week', '7days' => now()->subDays(7),
            'month', '30days' => now()->subDays(30),
            'year', '90days' => now()->subDays(90),
            '1year' => now()->subYear(),
            default => now()->subDays(30),
        };
    }

    protected function resolveDateRange(array $filters): array
    {
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            return [
                Carbon::parse($filters['date_from'] ?? now()->subMonth())->startOfDay(),
                Carbon::parse($filters['date_to'] ?? now())->endOfDay(),
            ];
        }

        $start = match ($filters['period'] ?? 'month') {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek()->startOfDay(),
            'month' => now()->startOfMonth()->startOfDay(),
            'year' => now()->startOfYear()->startOfDay(),
            default => now()->subMonth()->startOfDay(),
        };

        return [$start, now()->endOfDay()];
    }

    public function getReviews(array $filters = [])
    {
        $limit = (int) ($filters['limit'] ?? 20);

        $query = Review::with(['patient', 'doctor.user', 'doctor.speciality', 'reviewer']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('doctor.user', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($limit);
    }

    public function formatReview(Review $review): array
    {
        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'status' => $review->status,
            'reject_reason' => $review->reject_reason,
            'patient_name' => $review->patient?->name,
            'patient_phone' => $review->patient?->phone,
            'doctor_id' => $review->doctor_id,
            'doctor_name' => $review->doctor?->user?->name,
            'speciality' => $review->doctor?->speciality?->name_ar,
            'reviewed_by' => $review->reviewer?->name,
            'reviewed_at' => $review->reviewed_at?->format('Y-m-d H:i'),
            'created_at' => $review->created_at?->format('Y-m-d H:i'),
        ];
    }
}
