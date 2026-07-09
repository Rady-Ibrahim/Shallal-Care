<?php

namespace Modules\Doctor\Http\Controllers\Doctor;

use App\Support\ClinicDashboardContext;
use App\Traits\ApiResponse;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Doctor\Http\Requests\Web\StoreClinicStaffRequest;
use Modules\Doctor\Http\Requests\Web\UpdateClinicStaffRequest;
use Modules\Doctor\Services\ClinicStaffService;

class ClinicStaffController extends Controller
{
    use ApiResponse, ResolvesClinicDashboard;

    public function __construct(private ClinicStaffService $staffService) {}

    public function me(): JsonResponse
    {
        $context = ClinicDashboardContext::resolve();

        return $this->success([
            'user' => [
                'id' => $context->user->id,
                'name' => $context->user->name,
                'role' => $context->user->role,
            ],
            'is_owner' => $context->isOwner(),
            'branch' => $context->branch ? [
                'id' => $context->branch->id,
                'name' => $context->branch->branch_name,
            ] : null,
            'permissions' => $context->permissions(),
        ]);
    }

    public function index(): JsonResponse
    {
        $doctor = $this->resolveDoctor();

        return $this->success($this->staffService->listForDoctor($doctor->id));
    }

    public function permissionsCatalog(): JsonResponse
    {
        return $this->success($this->staffService->permissionCatalog());
    }

    public function store(StoreClinicStaffRequest $request): JsonResponse
    {
        $doctor = $this->resolveDoctor();
        $member = $this->staffService->createStaff($doctor, $request->validated());

        return $this->created([
            'id' => $member->id,
            'user' => ['name' => $member->user->name, 'phone' => $member->user->phone],
        ], 'تم إضافة السكرتير بنجاح');
    }

    public function update(UpdateClinicStaffRequest $request, int $id): JsonResponse
    {
        $doctor = $this->resolveDoctor();
        $this->staffService->updateStaff($doctor, $id, $request->validated());

        return $this->success(null, 'تم تحديث بيانات السكرتير');
    }

    public function updateStatus(int $id): JsonResponse
    {
        request()->validate(['status' => 'required|in:active,inactive']);

        $doctor = $this->resolveDoctor();
        $this->staffService->updateStatus($doctor, $id, request('status'));

        return $this->success(null, 'تم تحديث حالة السكرتير');
    }

    public function destroy(int $id): JsonResponse
    {
        $doctor = $this->resolveDoctor();
        $this->staffService->deleteStaff($doctor, $id);

        return $this->success(null, 'تم حذف السكرتير');
    }
}
