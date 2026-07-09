<?php

namespace Modules\Doctor\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Doctor\Models\BranchTransaction;
use Modules\Doctor\Models\ClinicBooking;
use Modules\Doctor\Models\ClinicOrder;
use Modules\Doctor\Models\ClinicPatient;
use Modules\MedicalRecord\Models\MedicalRecord;

class ClinicReportService
{
    public function getOverview(int $doctorId, int $branchId, string $period = 'month'): array
    {
        [$start, $end] = $this->periodRange($period);

        $bookings = ClinicBooking::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereBetween('visit_date', [$start->toDateString(), $end->toDateString()]);

        $visitsTotal = (clone $bookings)->count();
        $visitsCompleted = (clone $bookings)->where('status', ClinicBooking::STATUS_COMPLETED)->count();
        $visitsCancelled = (clone $bookings)->whereIn('status', [
            ClinicBooking::STATUS_CANCELLED,
            ClinicBooking::STATUS_NO_SHOW,
        ])->count();

        $revenue = (float) BranchTransaction::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->where('type', 'income')
            ->whereBetween('created_at', [$start, $end->endOfDay()])
            ->sum('amount');

        $expenses = (float) BranchTransaction::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->where('type', 'expense')
            ->whereBetween('created_at', [$start, $end->endOfDay()])
            ->sum('amount');

        $newPatients = ClinicPatient::where('doctor_id', $doctorId)
            ->where('registered_branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end->endOfDay()])
            ->count();

        $prescriptions = MedicalRecord::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->where('record_type', 'prescription')
            ->whereBetween('created_at', [$start, $end->endOfDay()])
            ->count();

        $pendingOrders = [
            'lab' => ClinicOrder::where('doctor_id', $doctorId)
                ->where('branch_id', $branchId)
                ->where('type', ClinicOrder::TYPE_LAB)
                ->whereNotIn('status', [ClinicOrder::STATUS_COMPLETED, ClinicOrder::STATUS_CANCELLED])
                ->count(),
            'radiology' => ClinicOrder::where('doctor_id', $doctorId)
                ->where('branch_id', $branchId)
                ->where('type', ClinicOrder::TYPE_RADIOLOGY)
                ->whereNotIn('status', [ClinicOrder::STATUS_COMPLETED, ClinicOrder::STATUS_CANCELLED])
                ->count(),
        ];

        return [
            'period' => $period,
            'period_label' => $this->periodLabel($period),
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'visits_total' => $visitsTotal,
            'visits_completed' => $visitsCompleted,
            'visits_cancelled' => $visitsCancelled,
            'completion_rate' => $visitsTotal > 0 ? round(($visitsCompleted / $visitsTotal) * 100, 1) : 0,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net' => $revenue - $expenses,
            'new_patients' => $newPatients,
            'prescriptions' => $prescriptions,
            'pending_lab' => $pendingOrders['lab'],
            'pending_radiology' => $pendingOrders['radiology'],
            'daily_visits' => $this->dailyVisitsChart($doctorId, $branchId, $start, $end),
            'top_diagnoses' => $this->topDiagnoses($doctorId, $branchId, $start, $end),
        ];
    }

    private function dailyVisitsChart(int $doctorId, int $branchId, Carbon $start, Carbon $end): array
    {
        $rows = ClinicBooking::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereBetween('visit_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('visit_date, COUNT(*) as total')
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->pluck('total', 'visit_date');

        $chart = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $chart[] = [
                'date' => $key,
                'label' => $cursor->format('m/d'),
                'total' => (int) ($rows[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        return $chart;
    }

    private function topDiagnoses(int $doctorId, int $branchId, Carbon $start, Carbon $end): array
    {
        return MedicalRecord::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->where('record_type', 'diagnosis')
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->whereBetween('created_at', [$start, $end->endOfDay()])
            ->select('diagnosis', DB::raw('COUNT(*) as count'))
            ->groupBy('diagnosis')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'diagnosis' => $row->diagnosis,
                'count' => (int) $row->count,
            ])
            ->all();
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'today' => [today()->startOfDay(), today()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'اليوم',
            'week' => 'هذا الأسبوع',
            default => 'هذا الشهر',
        };
    }
}
