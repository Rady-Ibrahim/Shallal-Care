<?php

namespace Modules\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Models\User;
use Modules\Auth\Http\Requests\Admin\CreateAdminRequest;
use Modules\Auth\Http\Requests\Admin\CreateDoctorRequest;
use Modules\Auth\Services\Api\AuthService;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;

class AdminUserController extends Controller
{
    use ApiResponse;

    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get all users with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with('doctor');

            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $users = $query->orderBy('created_at', 'desc')->paginate((int) ($request->get('limit', 20)));

            return $this->paginated(
                $users->items(),
                $users->total(),
                $users->currentPage(),
                $users->perPage(),
                'تم جلب المستخدمين بنجاح'
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب المستخدمين');
        }
    }

    /**
     * Get user details
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::with(['doctor', 'appointments'])->findOrFail($id);
            return $this->success($user, 'تم جلب تفاصيل المستخدم بنجاح');
        } catch (\Exception $e) {
            return $this->notFound('المستخدم غير موجود');
        }
    }

    /**
     * Block user
     */
    public function block($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->status = 'blocked';
            $user->save();
            return $this->success($user, 'تم حظر المستخدم بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حظر المستخدم');
        }
    }

    /**
     * Unblock user
     */
    public function unblock($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->status = 'active';
            $user->save();
            return $this->success($user, 'تم فك الحظر عن المستخدم بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء فك الحظر');
        }
    }

    /**
     * Delete user
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth('web')->id()) {
                return $this->error('لا يمكنك حذف حسابك الخاص', 'FORBIDDEN', 403);
            }

            $user->delete();
            return $this->success(null, 'تم حذف المستخدم بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حذف المستخدم');
        }
    }

    /**
     * Create a new admin user (Dashboard only)
     */
    public function createAdmin(CreateAdminRequest $request): JsonResponse
    {
        try {
            $admin = $this->authService->createAdmin($request->validated());

            return $this->created([
                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'phone' => $admin->phone,
                    'email' => $admin->email,
                    'role' => $admin->role,
                    'status' => $admin->status,
                ],
            ], 'تم إنشاء حساب الإدارة بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('فشل إنشاء حساب الإدارة: ' . $e->getMessage());
        }
    }

    /**
     * Create a new doctor user (Dashboard only)
     */
    public function createDoctor(CreateDoctorRequest $request): JsonResponse
    {
        try {
            $doctor = $this->authService->createDoctor($request->validated());

            return $this->created([
                'user' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'phone' => $doctor->phone,
                    'email' => $doctor->email,
                    'role' => $doctor->role,
                    'status' => $doctor->status,
                ],
            ], 'تم إنشاء حساب الطبيب بنجاح - ينتظر الموافقة');
        } catch (\Exception $e) {
            return $this->serverError('فشل إنشاء حساب الطبيب: ' . $e->getMessage());
        }
    }
}
