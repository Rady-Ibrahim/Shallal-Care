<?php

namespace Modules\Doctor\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Doctor\Models\ClinicOrder;
use Modules\Doctor\Models\ClinicPatient;

class ClinicOrderService
{
    public function createOrder(int $doctorId, int $branchId, int $userId, array $data): array
    {
        $type = $data['type'];
        $patientId = (int) $data['patient_id'];

        $clinicPatient = ClinicPatient::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->first();

        $order = ClinicOrder::create([
            'order_number' => $this->generateOrderNumber($doctorId, $branchId, $type),
            'doctor_id' => $doctorId,
            'branch_id' => $branchId,
            'patient_id' => $patientId,
            'clinic_patient_id' => $clinicPatient?->id,
            'clinic_booking_id' => $data['clinic_booking_id'] ?? null,
            'type' => $type,
            'status' => ClinicOrder::STATUS_ORDERED,
            'tests' => $data['tests'],
            'clinical_notes' => $data['clinical_notes'] ?? null,
            'ordered_by' => $userId,
        ]);

        return $this->formatOrder($order->load(['patient', 'clinicPatient']));
    }

    public function listOrders(int $doctorId, int $branchId, array $filters = []): Collection
    {
        $query = ClinicOrder::with(['patient', 'clinicPatient'])
            ->where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->orderByDesc('created_at');

        if (! empty($filters['type']) && in_array($filters['type'], ['lab', 'radiology'], true)) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%'.$search.'%')
                    ->orWhereHas('patient', fn ($sub) => $sub->where('name', 'like', '%'.$search.'%'));
            });
        }

        return $query->get()->map(fn (ClinicOrder $o) => $this->formatOrder($o));
    }

    public function getOrder(int $doctorId, int $orderId): array
    {
        $order = ClinicOrder::with(['patient', 'clinicPatient', 'booking'])
            ->where('doctor_id', $doctorId)
            ->findOrFail($orderId);

        return $this->formatOrder($order, true);
    }

    public function updateStatus(int $doctorId, int $orderId, string $status): array
    {
        $allowed = [
            ClinicOrder::STATUS_ORDERED,
            ClinicOrder::STATUS_SAMPLE_COLLECTED,
            ClinicOrder::STATUS_IN_PROGRESS,
            ClinicOrder::STATUS_COMPLETED,
            ClinicOrder::STATUS_CANCELLED,
        ];

        if (! in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('حالة غير صالحة');
        }

        $order = ClinicOrder::where('doctor_id', $doctorId)->findOrFail($orderId);
        $updates = ['status' => $status];

        if ($status === ClinicOrder::STATUS_COMPLETED && ! $order->completed_at) {
            $updates['completed_at'] = now();
        }

        $order->update($updates);

        return $this->formatOrder($order->fresh(['patient', 'clinicPatient']));
    }

    public function uploadResults(int $doctorId, int $orderId, int $userId, array $data, array $files = []): array
    {
        $order = ClinicOrder::where('doctor_id', $doctorId)->findOrFail($orderId);

        $uploaded = $order->result_files ?? [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploaded[] = $this->uploadResultFile($file);
            }
        }

        $order->update([
            'result_summary' => $data['result_summary'] ?? $order->result_summary,
            'result_files' => $uploaded,
            'status' => ClinicOrder::STATUS_COMPLETED,
            'completed_by' => $userId,
            'completed_at' => now(),
        ]);

        return $this->formatOrder($order->fresh(['patient', 'clinicPatient']), true);
    }

    public function getPendingCounts(int $doctorId, int $branchId): array
    {
        $base = ClinicOrder::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereNotIn('status', [ClinicOrder::STATUS_COMPLETED, ClinicOrder::STATUS_CANCELLED]);

        return [
            'lab' => (clone $base)->where('type', ClinicOrder::TYPE_LAB)->count(),
            'radiology' => (clone $base)->where('type', ClinicOrder::TYPE_RADIOLOGY)->count(),
            'total' => (clone $base)->count(),
        ];
    }

    public function testCatalog(): array
    {
        return [
            'lab' => config('clinic.lab_tests', []),
            'radiology' => config('clinic.radiology_types', []),
        ];
    }

    private function generateOrderNumber(int $doctorId, int $branchId, string $type): string
    {
        $prefix = $type === ClinicOrder::TYPE_LAB ? 'LAB' : 'RAD';
        $date = now()->format('ymd');
        $count = ClinicOrder::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->where('type', $type)
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('%s-%s-%s-%03d', $prefix, $date, $branchId, $count);
    }

    private function uploadResultFile(UploadedFile $file): array
    {
        $path = $file->store('clinic-orders', 'public');

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => Storage::url($path),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_at' => now()->toDateTimeString(),
        ];
    }

    private function formatOrder(ClinicOrder $order, bool $detailed = false): array
    {
        $tests = collect($order->tests ?? [])->map(function ($key) use ($order) {
            $catalog = $order->type === ClinicOrder::TYPE_LAB
                ? config('clinic.lab_tests', [])
                : config('clinic.radiology_types', []);

            return [
                'key' => $key,
                'label' => $catalog[$key] ?? $key,
            ];
        })->all();

        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'type' => $order->type,
            'type_label' => $order->type === ClinicOrder::TYPE_LAB ? 'تحليل' : 'أشعة',
            'status' => $order->status,
            'status_label' => $this->statusLabel($order->status),
            'patient_id' => $order->patient_id,
            'patient_name' => $order->patient?->name,
            'file_number' => $order->clinicPatient?->file_number,
            'tests' => $tests,
            'tests_count' => count($tests),
            'clinical_notes' => $order->clinical_notes,
            'created_at' => $order->created_at?->format('Y-m-d H:i'),
        ];

        if ($detailed) {
            $data['result_summary'] = $order->result_summary;
            $data['result_files'] = $order->result_files ?? [];
            $data['booking_number'] = $order->booking?->display_booking_number;
            $data['completed_at'] = $order->completed_at?->format('Y-m-d H:i');
        }

        return $data;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            ClinicOrder::STATUS_ORDERED => 'مطلوب',
            ClinicOrder::STATUS_SAMPLE_COLLECTED => 'تم سحب العينة',
            ClinicOrder::STATUS_IN_PROGRESS => 'قيد التنفيذ',
            ClinicOrder::STATUS_COMPLETED => 'مكتمل',
            ClinicOrder::STATUS_CANCELLED => 'ملغي',
            default => $status,
        };
    }
}
