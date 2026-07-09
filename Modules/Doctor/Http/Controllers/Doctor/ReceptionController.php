<?php

namespace Modules\Doctor\Http\Controllers\Doctor;

use App\Support\ClinicDashboardContext;
use App\Traits\ApiResponse;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Doctor\Http\Requests\Web\CreateReceptionBookingRequest;
use Modules\Doctor\Services\ReceptionService;

class ReceptionController extends Controller
{
    use ApiResponse, ResolvesClinicDashboard;

    public function __construct(private ReceptionService $receptionService) {}

    public function stats(): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        return $this->success($this->receptionService->getTodayStats($ctx->doctorId(), $branchId));
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        $bookings = $this->receptionService->listBookings($ctx->doctorId(), $branchId, $request->only([
            'q', 'phone', 'status', 'visit_date',
        ]));

        return $this->success($bookings);
    }

    public function store(CreateReceptionBookingRequest $request): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        try {
            $booking = $this->receptionService->createBooking(
                $ctx->doctorId(),
                $branchId,
                $ctx->user->id,
                $request->validated()
            );

            return $this->created($booking, 'تم إنشاء الحجز — رقم الحجز: '.$booking['booking_number']);
        } catch (\Throwable $e) {
            return $this->serverError('تعذر إنشاء الحجز');
        }
    }

    public function checkIn(int $id): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        try {
            $booking = $this->receptionService->checkIn($ctx->doctorId(), $branchId, $id);

            return $this->success($booking, 'تم تسجيل الحضور وإضافة المريض للدور');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 'INVALID_STATE', 422);
        }
    }

    public function collectPayment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'payment_method' => 'nullable|string|in:cash,visa,mastercard,meeza,instapay,vodafone_cash,bank_transfer',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        $booking = $this->receptionService->collectPayment(
            $ctx->doctorId(),
            $branchId,
            $id,
            $ctx->user->id,
            $request->only(['payment_method', 'amount', 'notes'])
        );

        return $this->success($booking, 'تم تحصيل المبلغ بنجاح');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:with_doctor,completed,cancelled,no_show',
        ]);

        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        try {
            $booking = $this->receptionService->updateStatus(
                $ctx->doctorId(),
                $branchId,
                $id,
                $request->status
            );

            return $this->success($booking, 'تم تحديث الحالة');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 'INVALID_STATE', 422);
        }
    }

    public function queue(): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();
        $branchId = $this->requireBranchId($ctx);

        return $this->success($this->receptionService->getQueue($ctx->doctorId(), $branchId));
    }

    private function requireBranchId(ClinicDashboardContext $ctx): int
    {
        if (! $ctx->branchId()) {
            abort(422, 'لم يتم تحديد الفرع');
        }

        return $ctx->branchId();
    }
}
