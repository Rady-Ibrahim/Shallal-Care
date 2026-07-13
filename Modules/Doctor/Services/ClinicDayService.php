<?php

namespace Modules\Doctor\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Doctor\Models\ClinicBooking;
use Modules\Doctor\Models\ClinicPatient;
use Modules\Doctor\Models\Doctor;
use Modules\MedicalRecord\Models\MedicalRecord;
use App\Support\PhoneNormalizer;

class ClinicDayService
{
    public function __construct(
        private ReceptionService $receptionService,
        private BranchFinanceService $financeService,
    ) {}

    public function getClinicMetrics(int $doctorId, ?int $branchId): array
    {
        $doctor = Doctor::findOrFail($doctorId);

        $patientsQuery = ClinicPatient::where('doctor_id', $doctorId);
        $bookingsTodayQuery = ClinicBooking::where('doctor_id', $doctorId)->whereDate('visit_date', today());

        if ($branchId) {
            $bookingsTodayQuery->where('branch_id', $branchId);
        }

        $bookingsToday = (clone $bookingsTodayQuery);
        $finance = $branchId
            ? $this->financeService->getDailySummary($doctorId, $branchId, today()->toDateString())
            : ['income' => 0, 'expense' => 0, 'net' => 0, 'consultations_count' => 0];

        $prescriptions = MedicalRecord::where('doctor_id', $doctorId)
            ->where('record_type', 'prescription');

        if ($branchId) {
            $prescriptions->where('branch_id', $branchId);
        }

        return [
            'patients' => [
                'total' => (clone $patientsQuery)->count(),
                'new_this_month' => (clone $patientsQuery)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'clinic_today' => [
                'total' => (clone $bookingsToday)->count(),
                'waiting' => (clone $bookingsToday)->whereIn('status', [
                    ClinicBooking::STATUS_WAITING,
                    ClinicBooking::STATUS_WITH_DOCTOR,
                ])->count(),
                'completed' => (clone $bookingsToday)->where('status', ClinicBooking::STATUS_COMPLETED)->count(),
                'scheduled' => (clone $bookingsToday)->where('status', ClinicBooking::STATUS_SCHEDULED)->count(),
                'revenue' => $finance['income'] ?? 0,
                'net' => $finance['net'] ?? 0,
                'consultations_paid' => $finance['consultations_count'] ?? 0,
            ],
            'prescriptions' => [
                'total' => (clone $prescriptions)->count(),
                'this_month' => (clone $prescriptions)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'doctor' => [
                'name' => $doctor->user?->name,
                'consultation_fee' => $doctor->consultation_fee,
            ],
        ];
    }

    public function getTodayActivity(int $doctorId, int $branchId): array
    {
        $bookings = $this->receptionService->listBookings($doctorId, $branchId, [
            'visit_date' => today()->toDateString(),
        ]);

        return [
            'bookings' => $bookings->map(fn ($b) => [
                'id' => $b['id'],
                'booking_number' => $b['booking_number'],
                'patient_name' => $b['patient_name'],
                'patient_phone' => $b['patient_phone'],
                'status' => $b['status'],
                'status_label' => $b['status_label'],
                'payment_status' => $b['payment_status'],
                'consultation_fee' => $b['consultation_fee'],
                'queue_position' => $b['queue_position'],
            ])->values()->all(),
        ];
    }

    public function getUpcomingQueue(int $doctorId, int $branchId): array
    {
        $queue = $this->receptionService->getQueue($doctorId, $branchId);

        return [
            'tasks' => collect($queue)->map(fn ($b) => [
                'type' => 'visit',
                'id' => $b['id'],
                'title' => ($b['patient_name'] ?? 'مريض').' — #'.$b['booking_number'],
                'date' => today()->format('Y-m-d'),
                'status' => $b['status'],
                'url' => '/doctor/dashboard/visits/'.$b['id'],
            ])->values()->all(),
        ];
    }

    public function getDayOverview(int $doctorId, int $branchId): array
    {
        return [
            'stats' => $this->receptionService->getTodayStats($doctorId, $branchId),
            'queue' => $this->receptionService->getQueue($doctorId, $branchId),
            'bookings' => $this->receptionService->listBookings($doctorId, $branchId)->take(15)->values()->all(),
            'finance' => $this->financeService->getDailySummary($doctorId, $branchId, today()->toDateString()),
        ];
    }

    public function lookupPatientByPhone(int $doctorId, string $phone): ?array
    {
        $local = PhoneNormalizer::toLocal($phone);
        $e164 = PhoneNormalizer::toE164($phone);

        $user = User::where('role', 'patient')
            ->where(function ($q) use ($phone, $local, $e164) {
                $q->where('phone', $phone)
                    ->orWhere('phone', $local)
                    ->orWhere('phone', $e164);
            })
            ->first();

        if (! $user) {
            return null;
        }

        $clinicPatient = ClinicPatient::where('doctor_id', $doctorId)
            ->where('patient_id', $user->id)
            ->first();

        $lastBooking = ClinicBooking::where('doctor_id', $doctorId)
            ->where('patient_id', $user->id)
            ->orderByDesc('visit_date')
            ->first();

        $lastRecord = MedicalRecord::where('doctor_id', $doctorId)
            ->where('patient_id', $user->id)
            ->whereNotNull('diagnosis')
            ->orderByDesc('created_at')
            ->first();

        $visitsCount = ClinicBooking::where('doctor_id', $doctorId)
            ->where('patient_id', $user->id)
            ->count();

        return [
            'patient_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'file_number' => $clinicPatient?->file_number,
            'allergies' => $clinicPatient?->allergies,
            'chronic_conditions' => $clinicPatient?->chronic_conditions,
            'visits_count' => $visitsCount,
            'last_visit' => $lastBooking?->visit_date?->format('Y-m-d'),
            'last_diagnosis' => $lastRecord?->diagnosis,
            'is_known' => (bool) $clinicPatient,
        ];
    }

    public function getCalendar(int $doctorId, ?int $branchId, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = ClinicBooking::with('patient')
            ->where('doctor_id', $doctorId)
            ->whereBetween('visit_date', [$start->toDateString(), $end->toDateString()]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $bookings = $query->orderBy('visit_date')->get();

        $grouped = [];
        foreach ($bookings as $booking) {
            $date = $booking->visit_date->format('Y-m-d');
            $grouped[$date][] = [
                'id' => $booking->id,
                'booking_number' => $booking->display_booking_number,
                'patient_name' => $booking->patient?->name,
                'status' => $booking->status,
                'source' => 'clinic',
            ];
        }

        return $grouped;
    }

    public function getWaitingScreen(int $doctorId, int $branchId): array
    {
        $queue = $this->receptionService->getQueue($doctorId, $branchId);
        $current = collect($queue)->firstWhere('status', ClinicBooking::STATUS_WITH_DOCTOR);
        $waiting = collect($queue)->where('status', ClinicBooking::STATUS_WAITING)->values();

        return [
            'branch' => $branchId,
            'updated_at' => now()->format('H:i:s'),
            'current' => $current,
            'waiting' => $waiting->all(),
            'waiting_count' => $waiting->count(),
        ];
    }
}
