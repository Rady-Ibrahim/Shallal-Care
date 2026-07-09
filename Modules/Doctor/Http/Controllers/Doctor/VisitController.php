<?php

namespace Modules\Doctor\Http\Controllers\Doctor;

use App\Support\ClinicDashboardContext;
use App\Traits\ApiResponse;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Doctor\Services\ClinicVisitService;

class VisitController extends Controller
{
    use ApiResponse, ResolvesClinicDashboard;

    public function __construct(private ClinicVisitService $visitService) {}

    public function show(int $bookingId): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        return $this->success($this->visitService->getVisit(
            $ctx->doctorId(),
            $this->requireBranchId($ctx),
            $bookingId
        ));
    }

    public function start(int $bookingId): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        try {
            $data = $this->visitService->startVisit(
                $ctx->doctorId(),
                $this->requireBranchId($ctx),
                $bookingId,
                $ctx->user->id
            );

            return $this->success($data, 'تم بدء الكشف');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 'INVALID_STATE', 422);
        }
    }

    public function update(Request $request, int $bookingId): JsonResponse
    {
        $request->validate([
            'diagnosis' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
            'weight' => 'nullable|string|max:20',
            'height' => 'nullable|string|max:20',
            'blood_pressure' => 'nullable|string|max:20',
            'allergies' => 'nullable|string|max:2000',
            'chronic_conditions' => 'nullable|string|max:2000',
        ]);

        $ctx = ClinicDashboardContext::resolve();

        try {
            $data = $this->visitService->saveVisit(
                $ctx->doctorId(),
                $this->requireBranchId($ctx),
                $bookingId,
                $request->all()
            );

            return $this->success($data, 'تم حفظ بيانات الكشف');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('لم يتم بدء الكشف بعد');
        }
    }

    public function complete(int $bookingId): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        try {
            $booking = $this->visitService->completeVisit(
                $ctx->doctorId(),
                $this->requireBranchId($ctx),
                $bookingId
            );

            return $this->success($booking, 'تم إنهاء الزيارة');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 'INVALID_STATE', 422);
        }
    }

    public function timeline(int $patientId): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        try {
            return $this->success($this->visitService->getPatientTimeline($ctx->doctorId(), $patientId));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('المريض غير موجود');
        }
    }

    public function patientFile(int $patientId): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        try {
            return $this->success($this->visitService->getPatientFile($ctx->doctorId(), $patientId));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('ملف المريض غير موجود');
        }
    }

    public function patientFileByQr(string $token): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        try {
            return $this->success($this->visitService->resolveByQrToken($ctx->doctorId(), $token));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('رمز QR غير صالح');
        }
    }

    private function requireBranchId(ClinicDashboardContext $ctx): int
    {
        if (! $ctx->branchId()) {
            abort(422, 'لم يتم تحديد الفرع');
        }

        return $ctx->branchId();
    }
}
