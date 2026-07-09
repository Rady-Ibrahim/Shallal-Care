<?php

namespace Modules\Doctor\Http\Controllers\Doctor;

use App\Support\ClinicDashboardContext;
use App\Traits\ApiResponse;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Doctor\Http\Requests\Web\StoreClinicOrderRequest;
use Modules\Doctor\Services\ClinicOrderService;

class ClinicOrderController extends Controller
{
    use ApiResponse, ResolvesClinicDashboard;

    public function __construct(private ClinicOrderService $orderService) {}

    public function catalog(): JsonResponse
    {
        return $this->success($this->orderService->testCatalog());
    }

    public function stats(): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        return $this->success($this->orderService->getPendingCounts(
            $ctx->doctorId(),
            $this->requireBranchId($ctx)
        ));
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        return $this->success($this->orderService->listOrders(
            $ctx->doctorId(),
            $this->requireBranchId($ctx),
            $request->only(['type', 'status', 'q'])
        ));
    }

    public function show(int $id): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        return $this->success($this->orderService->getOrder($ctx->doctorId(), $id));
    }

    public function store(StoreClinicOrderRequest $request): JsonResponse
    {
        $ctx = ClinicDashboardContext::resolve();

        $order = $this->orderService->createOrder(
            $ctx->doctorId(),
            $this->requireBranchId($ctx),
            $ctx->user->id,
            $request->validated()
        );

        return $this->created($order, 'تم إنشاء الطلب — رقم: '.$order['order_number']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:ordered,sample_collected,in_progress,completed,cancelled',
        ]);

        $ctx = ClinicDashboardContext::resolve();

        try {
            $order = $this->orderService->updateStatus($ctx->doctorId(), $id, $request->status);

            return $this->success($order, 'تم تحديث الحالة');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 'INVALID_STATE', 422);
        }
    }

    public function uploadResults(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'result_summary' => 'nullable|string|max:5000',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        $ctx = ClinicDashboardContext::resolve();

        $order = $this->orderService->uploadResults(
            $ctx->doctorId(),
            $id,
            $ctx->user->id,
            $request->only('result_summary'),
            $request->file('files', [])
        );

        return $this->success($order, 'تم رفع النتائج بنجاح');
    }

    private function requireBranchId(ClinicDashboardContext $ctx): int
    {
        if (! $ctx->branchId()) {
            abort(422, 'لم يتم تحديد الفرع');
        }

        return $ctx->branchId();
    }
}
