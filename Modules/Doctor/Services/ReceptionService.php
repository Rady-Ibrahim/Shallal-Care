<?php

namespace Modules\Doctor\Services;

use App\Support\PhoneNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Auth\Models\User;
use Modules\Doctor\Models\BranchTransaction;
use Modules\Doctor\Models\ClinicBooking;
use Modules\Doctor\Models\ClinicPatient;
use Modules\Doctor\Models\Doctor;

class ReceptionService
{
    public function createBooking(int $doctorId, int $branchId, int $registeredByUserId, array $data): array
    {
        return DB::transaction(function () use ($doctorId, $branchId, $registeredByUserId, $data) {
            $doctor = Doctor::findOrFail($doctorId);
            $patient = $this->resolvePatientUser($doctor, $data);

            $clinicPatient = ClinicPatient::firstOrCreate(
                ['doctor_id' => $doctorId, 'patient_id' => $patient->id],
                [
                    'file_number' => $this->generateFileNumber($doctorId),
                    'qr_token' => Str::random(32),
                    'registered_branch_id' => $branchId,
                ]
            );

            $fee = $data['consultation_fee'] ?? $doctor->consultation_fee ?? 0;
            $visitDate = $data['visit_date'] ?? today();
            $dailyNumber = $this->nextDailyNumber($branchId, $visitDate);

            $booking = ClinicBooking::create([
                'daily_number' => $dailyNumber,
                'booking_number' => $this->makeInternalBookingNumber($doctorId, $branchId, $visitDate, $dailyNumber),
                'doctor_id' => $doctorId,
                'branch_id' => $branchId,
                'clinic_patient_id' => $clinicPatient->id,
                'patient_id' => $patient->id,
                'visit_date' => $data['visit_date'] ?? today(),
                'status' => ClinicBooking::STATUS_SCHEDULED,
                'consultation_fee' => $fee,
                'payment_status' => 'pending',
                'registered_by' => $registeredByUserId,
                'notes' => $data['notes'] ?? null,
            ]);

            return $this->formatBooking($booking->load(['patient', 'clinicPatient']));
        });
    }

    public function listBookings(int $doctorId, int $branchId, array $filters = []): Collection
    {
        $visitDate = $filters['visit_date'] ?? today()->toDateString();

        $query = ClinicBooking::with(['patient', 'clinicPatient'])
            ->where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereDate('visit_date', $visitDate)
            ->orderByDesc('created_at');

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        $search = trim((string) ($filters['q'] ?? ''));
        $phone = trim((string) ($filters['phone'] ?? ''));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', '%'.$search.'%')
                    ->orWhereHas('patient', fn ($sub) => $sub->where('name', 'like', '%'.$search.'%'));

                if (ctype_digit($search)) {
                    $q->orWhere('daily_number', (int) $search);
                }
            });
        }

        if ($phone !== '') {
            $normalized = PhoneNormalizer::toE164($phone);
            $local = PhoneNormalizer::toLocal($phone);
            $query->whereHas('patient', function ($sub) use ($phone, $normalized, $local) {
                $sub->where('phone', 'like', '%'.$phone.'%')
                    ->orWhere('phone', $normalized)
                    ->orWhere('phone', $local);
            });
        }

        return $query->get()->map(fn (ClinicBooking $b) => $this->formatBooking($b));
    }

    public function getTodayStats(int $doctorId, int $branchId): array
    {
        $base = ClinicBooking::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereDate('visit_date', today());

        return [
            'total' => (clone $base)->count(),
            'scheduled' => (clone $base)->where('status', ClinicBooking::STATUS_SCHEDULED)->count(),
            'waiting' => (clone $base)->whereIn('status', [
                ClinicBooking::STATUS_CHECKED_IN,
                ClinicBooking::STATUS_WAITING,
            ])->count(),
            'with_doctor' => (clone $base)->where('status', ClinicBooking::STATUS_WITH_DOCTOR)->count(),
            'completed' => (clone $base)->where('status', ClinicBooking::STATUS_COMPLETED)->count(),
            'pending_payment' => (clone $base)->where('payment_status', 'pending')
                ->whereNotIn('status', [ClinicBooking::STATUS_CANCELLED, ClinicBooking::STATUS_NO_SHOW])
                ->count(),
        ];
    }

    public function checkIn(int $doctorId, int $branchId, int $bookingId): array
    {
        $booking = $this->findBooking($doctorId, $branchId, $bookingId);

        if (in_array($booking->status, [ClinicBooking::STATUS_CANCELLED, ClinicBooking::STATUS_NO_SHOW, ClinicBooking::STATUS_COMPLETED], true)) {
            throw new \InvalidArgumentException('لا يمكن تسجيل حضور لهذا الحجز');
        }

        $booking->update([
            'status' => ClinicBooking::STATUS_WAITING,
            'checked_in_at' => now(),
            'queue_position' => $this->nextQueuePosition($branchId),
        ]);

        return $this->formatBooking($booking->fresh(['patient', 'clinicPatient']));
    }

    public function collectPayment(int $doctorId, int $branchId, int $bookingId, int $userId, array $data): array
    {
        return DB::transaction(function () use ($doctorId, $branchId, $bookingId, $userId, $data) {
            $booking = $this->findBooking($doctorId, $branchId, $bookingId);
            $method = $data['payment_method'] ?? 'cash';
            $amount = $data['amount'] ?? $booking->consultation_fee;

            $booking->update([
                'payment_status' => 'paid',
                'payment_method' => $method,
            ]);

            BranchTransaction::create([
                'branch_id' => $branchId,
                'doctor_id' => $doctorId,
                'clinic_booking_id' => $booking->id,
                'type' => 'income',
                'category' => 'consultation',
                'amount' => $amount,
                'payment_method' => $method,
                'recorded_by' => $userId,
                'notes' => $data['notes'] ?? 'تحصيل كشف — '.$booking->display_booking_number,
            ]);

            return $this->formatBooking($booking->fresh(['patient', 'clinicPatient']));
        });
    }

    public function updateStatus(int $doctorId, int $branchId, int $bookingId, string $status): array
    {
        $allowed = [
            ClinicBooking::STATUS_WITH_DOCTOR,
            ClinicBooking::STATUS_COMPLETED,
            ClinicBooking::STATUS_CANCELLED,
            ClinicBooking::STATUS_NO_SHOW,
        ];

        if (! in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('حالة غير صالحة');
        }

        $booking = $this->findBooking($doctorId, $branchId, $bookingId);

        $updates = ['status' => $status];

        if ($status === ClinicBooking::STATUS_COMPLETED) {
            $updates['completed_at'] = now();
            $updates['queue_position'] = null;
        }

        $booking->update($updates);

        return $this->formatBooking($booking->fresh(['patient', 'clinicPatient']));
    }

    public function getQueue(int $doctorId, int $branchId): Collection
    {
        return ClinicBooking::with(['patient', 'clinicPatient'])
            ->where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereDate('visit_date', today())
            ->whereIn('status', [
                ClinicBooking::STATUS_WAITING,
                ClinicBooking::STATUS_WITH_DOCTOR,
            ])
            ->orderBy('queue_position')
            ->orderBy('checked_in_at')
            ->get()
            ->map(fn (ClinicBooking $b) => $this->formatBooking($b));
    }

    private function resolvePatientUser(Doctor $doctor, array $data): User
    {
        $phone = PhoneNormalizer::toE164($data['phone']);
        $local = PhoneNormalizer::toLocal($data['phone']);

        $existing = User::where('role', 'patient')
            ->where(function ($q) use ($phone, $local) {
                $q->where('phone', $phone)->orWhere('phone', $local);
            })
            ->first();

        if ($existing) {
            $existing->update(array_filter([
                'name' => $data['name'] ?? $existing->name,
                'gender' => $data['gender'] ?? $existing->gender,
            ]));

            return $existing->fresh();
        }

        return User::create([
            'name' => $data['name'],
            'phone' => $phone,
            'password' => bcrypt(Str::random(32)),
            'role' => 'patient',
            'status' => 'active',
            'is_ghost' => true,
            'created_by_doctor_id' => $doctor->user_id,
            'gender' => $data['gender'] ?? null,
        ]);
    }

    private function generateFileNumber(int $doctorId): string
    {
        $count = ClinicPatient::where('doctor_id', $doctorId)->count() + 1;

        return sprintf('SC-%04d', $count);
    }

    private function nextDailyNumber(int $branchId, $visitDate): int
    {
        $visitDate = $visitDate instanceof \Carbon\CarbonInterface
            ? $visitDate->toDateString()
            : (string) $visitDate;

        $last = ClinicBooking::where('branch_id', $branchId)
            ->whereDate('visit_date', $visitDate)
            ->lockForUpdate()
            ->max('daily_number');

        return ((int) $last) + 1;
    }

    private function makeInternalBookingNumber(int $doctorId, int $branchId, $visitDate, int $dailyNumber): string
    {
        $dateKey = str_replace('-', '', $visitDate instanceof \Carbon\CarbonInterface
            ? $visitDate->format('Y-m-d')
            : (string) $visitDate);

        return sprintf('BK-%s-%s-%d', $dateKey, $branchId, $dailyNumber);
    }

    private function nextQueuePosition(int $branchId): int
    {
        $max = ClinicBooking::where('branch_id', $branchId)
            ->whereDate('visit_date', today())
            ->whereIn('status', [ClinicBooking::STATUS_WAITING, ClinicBooking::STATUS_WITH_DOCTOR])
            ->max('queue_position');

        return ((int) $max) + 1;
    }

    private function findBooking(int $doctorId, int $branchId, int $bookingId): ClinicBooking
    {
        return ClinicBooking::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->where('id', $bookingId)
            ->firstOrFail();
    }

    private function formatBooking(ClinicBooking $booking): array
    {
        return [
            'id' => $booking->id,
            'booking_number' => $booking->display_booking_number,
            'daily_number' => $booking->daily_number,
            'file_number' => $booking->clinicPatient?->file_number,
            'patient_id' => $booking->patient_id,
            'patient_name' => $booking->patient?->name,
            'patient_phone' => $booking->patient?->phone,
            'visit_date' => $booking->visit_date?->format('Y-m-d'),
            'status' => $booking->status,
            'status_label' => $this->statusLabel($booking->status),
            'consultation_fee' => (float) $booking->consultation_fee,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'queue_position' => $booking->queue_position,
            'checked_in_at' => $booking->checked_in_at?->format('H:i'),
            'notes' => $booking->notes,
            'created_at' => $booking->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            ClinicBooking::STATUS_SCHEDULED => 'محجوز',
            ClinicBooking::STATUS_CHECKED_IN => 'حضر',
            ClinicBooking::STATUS_WAITING => 'في الدور',
            ClinicBooking::STATUS_WITH_DOCTOR => 'عند الطبيب',
            ClinicBooking::STATUS_COMPLETED => 'تم',
            ClinicBooking::STATUS_CANCELLED => 'ملغي',
            ClinicBooking::STATUS_NO_SHOW => 'لم يحضر',
            default => $status,
        };
    }
}
