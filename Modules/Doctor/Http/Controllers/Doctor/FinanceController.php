<?php

namespace Modules\Doctor\Http\Controllers\Doctor;

use App\Support\ClinicDashboardContext;
use App\Traits\ApiResponse;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Doctor\Http\Requests\Web\StoreBranchExpenseRequest;
use Modules\Doctor\Services\BranchFinanceService;

class FinanceController extends Controller
{
    use ApiResponse, ResolvesClinicDashboard;

    public function __construct(private BranchFinanceService $financeService) {}

    public function summary(Request $request): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);
        $date = $request->get('date', today()->toDateString());

        return $this->success($this->financeService->getDailySummary($ctx->doctorId(), $branchId, $date));
    }

    public function transactions(Request $request): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        $transactions = $this->financeService->listTransactions(
            $ctx->doctorId(),
            $branchId,
            $request->only(['date', 'type'])
        );

        return $this->success($transactions);
    }

    public function categories(): JsonResponse
    {
        return $this->success($this->financeService->categoryOptions());
    }

    public function storeExpense(StoreBranchExpenseRequest $request): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        $transaction = $this->financeService->addExpense(
            $ctx->doctorId(),
            $branchId,
            $ctx->user->id,
            $request->validated()
        );

        return $this->created($transaction, 'تم تسجيل المصروف بنجاح');
    }

    private function requireBranchId(ClinicDashboardContext $ctx): int
    {
        if (! $ctx->branchId()) {
            abort(422, 'لم يتم تحديد الفرع');
        }

        return $ctx->branchId();
    }
}
