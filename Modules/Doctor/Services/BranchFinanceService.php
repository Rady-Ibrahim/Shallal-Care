<?php

namespace Modules\Doctor\Services;

use Illuminate\Support\Collection;
use Modules\Doctor\Models\BranchTransaction;

class BranchFinanceService
{
    public function getDailySummary(int $doctorId, int $branchId, string $date): array
    {
        $base = BranchTransaction::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', $date);

        $income = (float) (clone $base)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $base)->where('type', 'expense')->sum('amount');

        $byCategory = (clone $base)
            ->selectRaw('type, category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type', 'category')
            ->get()
            ->map(fn ($row) => [
                'type' => $row->type,
                'category' => $row->category,
                'category_label' => $this->categoryLabel($row->type, $row->category),
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ]);

        $byPaymentMethod = (clone $base)
            ->selectRaw("payment_method, SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($row) => [
                'payment_method' => $row->payment_method,
                'payment_method_label' => config('clinic.payment_methods.'.$row->payment_method, $row->payment_method),
                'income' => (float) $row->income,
                'expense' => (float) $row->expense,
            ]);

        $consultations = (clone $base)
            ->where('type', 'income')
            ->where('category', 'consultation')
            ->count();

        return [
            'date' => $date,
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'consultations_count' => $consultations,
            'transactions_count' => (clone $base)->count(),
            'by_category' => $byCategory,
            'by_payment_method' => $byPaymentMethod,
        ];
    }

    public function listTransactions(int $doctorId, int $branchId, array $filters = []): Collection
    {
        $date = $filters['date'] ?? today()->toDateString();

        $query = BranchTransaction::with(['booking', 'recordedByUser'])
            ->where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', $date)
            ->orderByDesc('created_at');

        if (! empty($filters['type']) && in_array($filters['type'], ['income', 'expense'], true)) {
            $query->where('type', $filters['type']);
        }

        return $query->get()->map(fn (BranchTransaction $t) => $this->formatTransaction($t));
    }

    public function addExpense(int $doctorId, int $branchId, int $userId, array $data): array
    {
        $transaction = BranchTransaction::create([
            'branch_id' => $branchId,
            'doctor_id' => $doctorId,
            'type' => 'expense',
            'category' => $data['category'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'recorded_by' => $userId,
            'notes' => $data['notes'] ?? null,
        ]);

        return $this->formatTransaction($transaction->load('recordedByUser'));
    }

    public function categoryOptions(): array
    {
        return [
            'income' => config('clinic.income_categories', []),
            'expense' => config('clinic.expense_categories', []),
        ];
    }

    private function formatTransaction(BranchTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'type' => $transaction->type,
            'type_label' => $transaction->type === 'income' ? 'إيراد' : 'مصروف',
            'category' => $transaction->category,
            'category_label' => $this->categoryLabel($transaction->type, $transaction->category),
            'amount' => (float) $transaction->amount,
            'payment_method' => $transaction->payment_method,
            'payment_method_label' => config('clinic.payment_methods.'.$transaction->payment_method, $transaction->payment_method),
            'booking_number' => $transaction->booking?->display_booking_number,
            'notes' => $transaction->notes,
            'recorded_by' => $transaction->recordedByUser?->name,
            'created_at' => $transaction->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function categoryLabel(string $type, string $category): string
    {
        $key = $type === 'income' ? 'clinic.income_categories' : 'clinic.expense_categories';

        return config("{$key}.{$category}", $category);
    }
}
