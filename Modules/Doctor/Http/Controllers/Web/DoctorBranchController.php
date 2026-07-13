<?php

namespace Modules\Doctor\Http\Controllers\Web;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Doctor\Http\Requests\Api\CreateBranchRequest;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Services\Api\DoctorBranchService;
use App\Traits\ApiResponse;

class DoctorBranchController extends Controller
{
    use ApiResponse;

    public function __construct(private DoctorBranchService $branchService) {}

    protected function resolveDoctor(): Doctor
    {
        return Doctor::where('user_id', auth('web')->id())->firstOrFail();
    }

    public function index(): JsonResponse
    {
        $doctor = $this->resolveDoctor();
        $branches = $this->branchService->getBranches((string) $doctor->id);

        return $this->success($branches->map(fn ($branch) => [
            'id' => $branch->id,
            'branch_name' => $branch->branch_name,
            'governorate' => $branch->governorate,
            'district' => $branch->district,
            'address' => $branch->address,
            'latitude' => $branch->latitude,
            'longitude' => $branch->longitude,
            'phone' => $branch->phone,
            'is_primary' => $branch->is_primary,
        ]));
    }

    public function store(CreateBranchRequest $request): JsonResponse
    {
        $doctor = $this->resolveDoctor();
        $branch = $this->branchService->create((string) $doctor->id, $request->validated());

        return $this->created([
            'id' => $branch->id,
            'branch_name' => $branch->branch_name,
            'governorate' => $branch->governorate,
            'district' => $branch->district,
            'address' => $branch->address,
            'latitude' => $branch->latitude,
            'longitude' => $branch->longitude,
            'phone' => $branch->phone,
            'is_primary' => $branch->is_primary,
        ], 'تم إضافة الفرع بنجاح');
    }

    public function update(string $branchId, CreateBranchRequest $request): JsonResponse
    {
        $doctor = $this->resolveDoctor();
        $branch = $this->branchService->getBranch($branchId);

        if (!$branch || $branch->doctor_id !== $doctor->id) {
            return $this->forbidden('غير مصرح');
        }

        $updatedBranch = $this->branchService->update($branchId, $request->validated());

        return $this->success([
            'id' => $updatedBranch->id,
            'branch_name' => $updatedBranch->branch_name,
            'governorate' => $updatedBranch->governorate,
            'district' => $updatedBranch->district,
            'address' => $updatedBranch->address,
            'latitude' => $updatedBranch->latitude,
            'longitude' => $updatedBranch->longitude,
            'phone' => $updatedBranch->phone,
            'is_primary' => $updatedBranch->is_primary,
        ], 'تم تعديل الفرع بنجاح');
    }

    public function destroy(string $branchId): JsonResponse
    {
        $doctor = $this->resolveDoctor();
        $branch = $this->branchService->getBranch($branchId);

        if (!$branch || $branch->doctor_id !== $doctor->id) {
            return $this->forbidden('غير مصرح');
        }

        $deleted = $this->branchService->delete($branchId);

        if (!$deleted) {
            return $this->error('لا يمكن حذف الفرع الرئيسي', 'CANNOT_DELETE_PRIMARY_BRANCH', 400);
        }

        return $this->success(null, 'تم حذف الفرع بنجاح');
    }

    public function switchBranch(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate(['branch_id' => 'required|integer|exists:doctor_branches,id']);

        $doctor = $this->resolveDoctor();
        $branch = $this->branchService->getBranch((string) $request->branch_id);

        if (! $branch || (int) $branch->doctor_id !== (int) $doctor->id) {
            return $this->forbidden('غير مصرح');
        }

        session(['clinic_branch_id' => $branch->id]);

        return $this->success([
            'branch_id' => $branch->id,
            'branch_name' => $branch->branch_name,
        ], 'تم تبديل الفرع');
    }
}
