<?php

namespace Modules\Doctor\Http\Controllers\Doctor;

use App\Support\ClinicDashboardContext;
use App\Traits\ApiResponse;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Doctor\Services\ClinicDayService;

class ClinicDayController extends Controller
{
    use ApiResponse, ResolvesClinicDashboard;

    public function __construct(private ClinicDayService $clinicDayService) {}

    public function overview(): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        return $this->success($this->clinicDayService->getDayOverview(
            $ctx->doctorId(),
            $this->requireBranchId($ctx)
        ));
    }

    public function patientLookup(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|min:8']);

        $ctx = ClinicDashboardContext::resolve();
        $patient = $this->clinicDayService->lookupPatientByPhone($ctx->doctorId(), $request->phone);

        if (! $patient) {
            return $this->success(['found' => false], 'مريض جديد');
        }

        return $this->success(['found' => true, 'patient' => $patient]);
    }

    public function waitingScreen(): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        return $this->success($this->clinicDayService->getWaitingScreen(
            $ctx->doctorId(),
            $this->requireBranchId($ctx)
        ));
    }

    public function templates(): JsonResponse
    {
        return $this->success([
            'diagnosis' => config('clinic.diagnosis_templates', []),
            'prescription_notes' => config('clinic.prescription_snippets', []),
        ]);
    }

    private function requireBranchId(ClinicDashboardContext $ctx): int
    {
        if (! $ctx->branchId()) {
            abort(422, 'لم يتم تحديد الفرع');
        }

        return $ctx->branchId();
    }
}
