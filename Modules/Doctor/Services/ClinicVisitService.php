<?php

namespace Modules\Doctor\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Auth\Models\User;
use Modules\Doctor\Models\ClinicBooking;
use Modules\Doctor\Models\ClinicPatient;
use Modules\MedicalRecord\Models\MedicalRecord;

class ClinicVisitService
{
    public function getVisit(int $doctorId, int $branchId, int $bookingId): array
    {
        $booking = $this->findBooking($doctorId, $branchId, $bookingId);
        $booking->load(['patient', 'clinicPatient']);

        $record = MedicalRecord::where('clinic_booking_id', $booking->id)->first();

        return [
            'booking' => $this->formatBooking($booking),
            'visit' => $record ? $this->formatVisitRecord($record) : null,
        ];
    }

    public function startVisit(int $doctorId, int $branchId, int $bookingId, int $userId): array
    {
        return DB::transaction(function () use ($doctorId, $branchId, $bookingId, $userId) {
            $booking = $this->findBooking($doctorId, $branchId, $bookingId);

            if (! in_array($booking->status, [
                ClinicBooking::STATUS_WAITING,
                ClinicBooking::STATUS_WITH_DOCTOR,
            ], true)) {
                throw new \InvalidArgumentException('لا يمكن بدء الكشف لهذا الحجز');
            }

            if ($booking->status === ClinicBooking::STATUS_WAITING) {
                $booking->update(['status' => ClinicBooking::STATUS_WITH_DOCTOR]);
            }

            $record = MedicalRecord::firstOrCreate(
                ['clinic_booking_id' => $booking->id],
                [
                    'doctor_id' => $doctorId,
                    'branch_id' => $branchId,
                    'patient_id' => $booking->patient_id,
                    'record_type' => 'diagnosis',
                    'created_by' => $userId,
                ]
            );

            return [
                'booking' => $this->formatBooking($booking->fresh(['patient', 'clinicPatient'])),
                'visit' => $this->formatVisitRecord($record),
            ];
        });
    }

    public function saveVisit(int $doctorId, int $branchId, int $bookingId, array $data): array
    {
        $booking = $this->findBooking($doctorId, $branchId, $bookingId);

        $record = MedicalRecord::where('clinic_booking_id', $booking->id)->firstOrFail();

        $record->update([
            'diagnosis' => $data['diagnosis'] ?? $record->diagnosis,
            'notes' => $data['notes'] ?? $record->notes,
            'weight' => $data['weight'] ?? $record->weight,
            'height' => $data['height'] ?? $record->height,
            'blood_pressure' => $data['blood_pressure'] ?? $record->blood_pressure,
            'allergies' => $data['allergies'] ?? $record->allergies,
        ]);

        if (! empty($data['chronic_conditions']) || ! empty($data['allergies'])) {
            $clinicPatient = $booking->clinicPatient;
            if ($clinicPatient) {
                $clinicPatient->update(array_filter([
                    'allergies' => $data['allergies'] ?? null,
                    'chronic_conditions' => $data['chronic_conditions'] ?? null,
                ], fn ($v) => $v !== null));
            }
        }

        return [
            'booking' => $this->formatBooking($booking->load(['patient', 'clinicPatient'])),
            'visit' => $this->formatVisitRecord($record->fresh()),
        ];
    }

    public function completeVisit(int $doctorId, int $branchId, int $bookingId): array
    {
        $booking = $this->findBooking($doctorId, $branchId, $bookingId);

        if ($booking->status !== ClinicBooking::STATUS_WITH_DOCTOR) {
            throw new \InvalidArgumentException('المريض ليس في حالة كشف نشط');
        }

        $booking->update([
            'status' => ClinicBooking::STATUS_COMPLETED,
            'completed_at' => now(),
            'queue_position' => null,
        ]);

        return $this->formatBooking($booking->fresh(['patient', 'clinicPatient']));
    }

    public function getPatientTimeline(int $doctorId, int $patientId): Collection
    {
        $this->assertPatientAccess($doctorId, $patientId);

        $bookings = ClinicBooking::with(['clinicPatient'])
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->orderByDesc('visit_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ClinicBooking $b) => [
                'type' => 'visit',
                'id' => $b->id,
                'date' => $b->visit_date?->format('Y-m-d'),
                'title' => 'زيارة — '.$b->display_booking_number,
                'status' => $b->status,
                'status_label' => $this->bookingStatusLabel($b->status),
                'booking_number' => $b->display_booking_number,
                'file_number' => $b->clinicPatient?->file_number,
                'created_at' => $b->created_at?->format('Y-m-d H:i'),
            ]);

        $records = MedicalRecord::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (MedicalRecord $r) => [
                'type' => $r->record_type === 'prescription' ? 'prescription' : 'record',
                'id' => $r->id,
                'date' => $r->created_at?->format('Y-m-d'),
                'title' => $r->record_type === 'prescription'
                    ? 'روشتة '.($r->prescription_number ?? '#'.$r->id)
                    : ($r->diagnosis ?: 'سجل طبي'),
                'record_type' => $r->record_type,
                'diagnosis' => $r->diagnosis,
                'booking_id' => $r->clinic_booking_id,
                'created_at' => $r->created_at?->format('Y-m-d H:i'),
            ]);

        $orders = \Modules\Doctor\Models\ClinicOrder::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($o) => [
                'type' => $o->type === 'lab' ? 'lab' : 'radiology',
                'id' => $o->id,
                'date' => $o->created_at?->format('Y-m-d'),
                'title' => ($o->type === 'lab' ? 'تحليل ' : 'أشعة ').$o->order_number,
                'status' => $o->status,
                'created_at' => $o->created_at?->format('Y-m-d H:i'),
            ]);

        return $bookings->concat($records)->concat($orders)
            ->sortByDesc('created_at')
            ->values();
    }

    public function getPatientFile(int $doctorId, int $patientId): array
    {
        $this->assertPatientAccess($doctorId, $patientId);

        $patient = User::findOrFail($patientId);
        $clinicPatient = ClinicPatient::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->first();

        if ($clinicPatient && ! $clinicPatient->qr_token) {
            $clinicPatient->update(['qr_token' => Str::random(32)]);
            $clinicPatient->refresh();
        }

        $fileUrl = $clinicPatient
            ? url('/doctor/dashboard/patients/'.$patientId.'/file')
            : null;

        return [
            'patient_id' => $patient->id,
            'name' => $patient->name,
            'phone' => $patient->phone,
            'gender' => $patient->gender,
            'file_number' => $clinicPatient?->file_number,
            'qr_token' => $clinicPatient?->qr_token,
            'qr_url' => $fileUrl,
            'allergies' => $clinicPatient?->allergies,
            'chronic_conditions' => $clinicPatient?->chronic_conditions,
            'timeline' => $this->getPatientTimeline($doctorId, $patientId),
        ];
    }

    public function resolveByQrToken(int $doctorId, string $token): array
    {
        $clinicPatient = ClinicPatient::with('patient')
            ->where('doctor_id', $doctorId)
            ->where('qr_token', $token)
            ->firstOrFail();

        return $this->getPatientFile($doctorId, $clinicPatient->patient_id);
    }

    private function assertPatientAccess(int $doctorId, int $patientId): void
    {
        $doctor = \Modules\Doctor\Models\Doctor::findOrFail($doctorId);

        $hasAccess = ClinicPatient::where('doctor_id', $doctorId)->where('patient_id', $patientId)->exists()
            || User::where('id', $patientId)->where('created_by_doctor_id', $doctor->user_id)->exists();

        if (! $hasAccess) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }
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
            'patient_id' => $booking->patient_id,
            'patient_name' => $booking->patient?->name,
            'patient_phone' => $booking->patient?->phone,
            'file_number' => $booking->clinicPatient?->file_number,
            'status' => $booking->status,
            'visit_date' => $booking->visit_date?->format('Y-m-d'),
            'consultation_fee' => (float) $booking->consultation_fee,
            'payment_status' => $booking->payment_status,
        ];
    }

    private function formatVisitRecord(MedicalRecord $record): array
    {
        return [
            'id' => $record->id,
            'diagnosis' => $record->diagnosis,
            'notes' => $record->notes,
            'weight' => $record->weight,
            'height' => $record->height,
            'blood_pressure' => $record->blood_pressure,
            'allergies' => $record->allergies,
            'created_at' => $record->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function bookingStatusLabel(string $status): string
    {
        return match ($status) {
            ClinicBooking::STATUS_SCHEDULED => 'محجوز',
            ClinicBooking::STATUS_WAITING => 'في الدور',
            ClinicBooking::STATUS_WITH_DOCTOR => 'عند الطبيب',
            ClinicBooking::STATUS_COMPLETED => 'تم',
            ClinicBooking::STATUS_CANCELLED => 'ملغي',
            ClinicBooking::STATUS_NO_SHOW => 'لم يحضر',
            default => $status,
        };
    }
}
