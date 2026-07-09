<?php

namespace Modules\Doctor\Http\Controllers\Doctor;

use App\Support\ClinicDashboardContext;
use App\Traits\ApiResponse;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Doctor\Services\ClinicReportService;

class ClinicReportController extends Controller
{
    use ApiResponse, ResolvesClinicDashboard;

    public function __construct(private ClinicReportService $reportService) {}

    public function overview(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'nullable|in:today,week,month',
        ]);

        $ctx = ClinicDashboardContext::resolve();

        if (! $ctx->branchId()) {
            abort(422, 'لم يتم تحديد الفرع');
        }

        return $this->success($this->reportService->getOverview(
            $ctx->doctorId(),
            $ctx->branchId(),
            $request->get('period', 'month')
        ));
    }
}
