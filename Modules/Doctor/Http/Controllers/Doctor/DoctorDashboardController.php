<?php

namespace Modules\Doctor\Http\Controllers\Doctor;

use App\Traits\ApiResponse;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Doctor\Http\Requests\Web\CreateGhostPatientRequest;
use Modules\Auth\Models\User;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Services\DoctorDashboardService;
use Modules\Appointment\Services\Api\AppointmentService;

class DoctorDashboardController extends Controller
{
    use ApiResponse, ResolvesClinicDashboard;

    public function __construct(
        private DoctorDashboardService $doctorDashboardService,
        private AppointmentService $appointmentService
    ) {}

    protected function resolveDoctor(): Doctor
    {
        return $this->clinicContext()->doctor;
    }

    public function metrics(): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getMetrics($doctor->id));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب المقاييس');
        }
    }

    public function patients(Request $request): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $filters = $request->only(['search', 'sort_by', 'limit']);
            $paginator = $this->doctorDashboardService->getPatientsList($doctor->id, $filters);

            if ($request->has('limit') && !$request->has('page')) {
                return $this->success($paginator->items());
            }

            return $this->paginated(
                $paginator->items(),
                $paginator->total(),
                $paginator->currentPage(),
                $paginator->perPage()
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب قائمة المرضى');
        }
    }

    public function patientDetails($patientId): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getPatientDetails($doctor->id, (int) $patientId));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب تفاصيل المريض');
        }
    }

    public function todayActivity(): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getTodayActivity($doctor->id));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب نشاط اليوم');
        }
    }

    public function upcomingTasks(): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getUpcomingTasks($doctor->id));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب المهام القادمة');
        }
    }

    public function prescriptions(Request $request): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $prescriptions = $this->doctorDashboardService->getPrescriptions($doctor->id, $request->all());
            return $this->paginated(
                $prescriptions->items(),
                $prescriptions->total(),
                $prescriptions->currentPage(),
                $prescriptions->perPage()
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الوصفات الطبية');
        }
    }

    public function records(Request $request): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $records = $this->doctorDashboardService->getRecords($doctor->id, $request->all());
            return $this->paginated(
                $records->items(),
                $records->total(),
                $records->currentPage(),
                $records->perPage()
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب السجلات الطبية');
        }
    }

    public function patientPrescriptions($patientId): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getPatientPrescriptions($doctor->id, (int) $patientId));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب وصفات المريض');
        }
    }

    public function profile(): JsonResponse
    {
        try {
            return $this->success($this->doctorDashboardService->getProfile(auth('web')->id()));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الملف الشخصي');
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|regex:/^[0-9]{10,15}$/',
            'email' => 'sometimes|email',
            'address' => 'nullable|string',
        ]);

        try {
            $user = $this->doctorDashboardService->updateProfile(auth('web')->id(), $request->all());
            return $this->success($user, 'تم تحديث الملف الشخصي بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث الملف الشخصي');
        }
    }

    public function updateProfessional(Request $request): JsonResponse
    {
        $request->validate([
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0|max:60',
            'consultation_fee' => 'nullable|numeric|min:0',
        ]);

        try {
            $doctor = $this->doctorDashboardService->updateProfessional(auth('web')->id(), $request->all());
            return $this->success($doctor, 'تم تحديث المعلومات المهنية بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث المعلومات المهنية');
        }
    }

    public function schedules(): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getSchedules($doctor->id));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الجداول');
        }
    }

    public function deleteSchedule($scheduleId): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $this->doctorDashboardService->deleteSchedule($doctor->id, (int) $scheduleId);
            return $this->success(null, 'تم حذف الجدول بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حذف الجدول');
        }
    }

    public function calendar(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $doctor = $this->resolveDoctor();
            $data = $this->doctorDashboardService->getCalendar(
                $doctor->id,
                (int) $request->year,
                (int) $request->month
            );
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب التقويم');
        }
    }

    public function appointments(Request $request): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $appointments = $this->doctorDashboardService->getAppointments($doctor->id, $request->all());
            return $this->success($appointments);
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب المواعيد');
        }
    }

    public function appointmentDetails($appointmentId): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getAppointmentDetails($doctor->id, (int) $appointmentId));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب تفاصيل الموعد');
        }
    }

    public function confirmAppointment($appointmentId): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $appointment = $this->appointmentService->confirmByDoctor((int) $appointmentId, $doctor->id);

            if (!$appointment) {
                return $this->error('لا يمكن تأكيد هذا الموعد', 'INVALID_STATUS', 400);
            }

            return $this->success(
                $this->doctorDashboardService->getAppointmentDetails($doctor->id, $appointment->id),
                'تم تأكيد الموعد بنجاح'
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تأكيد الموعد');
        }
    }

    public function rejectAppointment($appointmentId): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $appointment = $this->appointmentService->rejectByDoctor((int) $appointmentId, $doctor->id);

            if (!$appointment) {
                return $this->error('لا يمكن رفض هذا الموعد', 'INVALID_STATUS', 400);
            }

            return $this->success(
                $this->doctorDashboardService->getAppointmentDetails($doctor->id, $appointment->id),
                'تم رفض الموعد بنجاح'
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء رفض الموعد');
        }
    }

    public function completeAppointment($appointmentId): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $appointment = $this->appointmentService->completeByDoctor((int) $appointmentId, $doctor->id);

            if (!$appointment) {
                return $this->error('لا يمكن إكمال هذا الموعد', 'INVALID_STATUS', 400);
            }

            return $this->success(
                $this->doctorDashboardService->getAppointmentDetails($doctor->id, $appointment->id),
                'تم إكمال الموعد بنجاح'
            );
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء إكمال الموعد');
        }
    }

    public function unreadNotifications(): JsonResponse
    {
        try {
            $user = User::findOrFail(auth('web')->id());
            $notifications = $user
                ->unreadNotifications()
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'إشعار',
                    'message' => $notification->data['message'] ?? '',
                    'type' => $notification->data['type'] ?? 'general',
                    'action_url' => $notification->data['action_url'] ?? null,
                    'created_at' => $notification->created_at?->format('Y-m-d H:i'),
                ])
                ->values()
                ->all();

            return $this->success([
                'count' => count($notifications),
                'items' => $notifications,
            ]);
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الإشعارات');
        }
    }

    public function markNotificationRead($notificationId): JsonResponse
    {
        try {
            $user = User::findOrFail(auth('web')->id());
            $notification = $user
                ->notifications()
                ->where('id', $notificationId)
                ->firstOrFail();

            $notification->markAsRead();

            return $this->success(null, 'تم تعليم الإشعار كمقروء');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث الإشعار');
        }
    }

    public function markAllNotificationsRead(): JsonResponse
    {
        try {
            $user = User::findOrFail(auth('web')->id());
            $user->unreadNotifications->markAsRead();
            return $this->success(null, 'تم تعليم كل الإشعارات كمقروءة');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث الإشعارات');
        }
    }

    public function subscription(): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getSubscription($doctor->id));
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء جلب الاشتراك');
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        $updated = $this->doctorDashboardService->changePassword(
            auth('web')->id(),
            $request->current_password,
            $request->new_password
        );

        if (!$updated) {
            return $this->error('كلمة المرور الحالية غير صحيحة', 'INVALID_PASSWORD', 400);
        }

        return $this->success(null, 'تم تغيير كلمة المرور بنجاح');
    }

    public function createGhostPatient(CreateGhostPatientRequest $request): JsonResponse
    {
        try {
            $patient = $this->doctorDashboardService->createGhostPatient(
                auth('web')->id(),
                $request->validated()
            );

            return $this->created([
                'id' => $patient->id,
                'name' => $patient->name,
                'phone' => $patient->phone,
                'is_ghost' => $patient->is_ghost,
            ], 'تم إضافة المريض بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء إضافة المريض');
        }
    }

    public function storePrescription(Request $request): JsonResponse
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:users,id',
            'diagnosis' => 'nullable|string',
            'medicines' => 'required|array|min:1',
            'medicines.*.name' => 'required|string',
            'medicines.*.dosage' => 'required|string',
            'medicines.*.frequency' => 'required',
            'medicines.*.duration' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $doctor = $this->resolveDoctor();
            $record = $this->doctorDashboardService->createPrescription(
                $doctor->id,
                auth('web')->id(),
                $request->all()
            );

            return $this->created($this->doctorDashboardService->getPrescription($doctor->id, $record->id), 'تم إنشاء الوصفة بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء إنشاء الوصفة');
        }
    }

    public function showPrescription($id): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getPrescription($doctor->id, (int) $id));
        } catch (\Exception $e) {
            return $this->notFound('الوصفة غير موجودة');
        }
    }

    public function updatePrescription(Request $request, $id): JsonResponse
    {
        $request->validate([
            'diagnosis' => 'nullable|string',
            'medicines' => 'required|array|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            $doctor = $this->resolveDoctor();
            $this->doctorDashboardService->updatePrescription($doctor->id, (int) $id, $request->all());
            return $this->success($this->doctorDashboardService->getPrescription($doctor->id, (int) $id), 'تم تحديث الوصفة بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث الوصفة');
        }
    }

    public function destroyPrescription($id): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $this->doctorDashboardService->deletePrescription($doctor->id, (int) $id);
            return $this->success(null, 'تم حذف الوصفة بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حذف الوصفة');
        }
    }

    public function storeRecord(Request $request): JsonResponse
    {
        $request->validate([
            'patient_id' => 'required_without:appointment_id|integer|exists:users,id',
            'appointment_id' => 'nullable|integer|exists:appointments,id',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        try {
            $doctor = $this->resolveDoctor();
            $record = $this->doctorDashboardService->createRecord(
                $doctor->id,
                auth('web')->id(),
                $request->only(['patient_id', 'appointment_id', 'type', 'title', 'description', 'notes']),
                $request->file('files', [])
            );

            return $this->created($this->doctorDashboardService->getRecord($doctor->id, $record->id), 'تم إنشاء السجل بنجاح');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 'VALIDATION_ERROR', 422);
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء إنشاء السجل');
        }
    }

    public function showRecord($id): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            return $this->success($this->doctorDashboardService->getRecord($doctor->id, (int) $id));
        } catch (\Exception $e) {
            return $this->notFound('السجل غير موجود');
        }
    }

    public function updateRecord(Request $request, $id): JsonResponse
    {
        $request->validate([
            'type' => 'sometimes|string',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        try {
            $doctor = $this->resolveDoctor();
            $this->doctorDashboardService->updateRecord(
                $doctor->id,
                (int) $id,
                $request->only(['type', 'title', 'description', 'notes']),
                $request->file('files', [])
            );

            return $this->success($this->doctorDashboardService->getRecord($doctor->id, (int) $id), 'تم تحديث السجل بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء تحديث السجل');
        }
    }

    public function destroyRecord($id): JsonResponse
    {
        try {
            $doctor = $this->resolveDoctor();
            $this->doctorDashboardService->deleteRecord($doctor->id, (int) $id);
            return $this->success(null, 'تم حذف السجل بنجاح');
        } catch (\Exception $e) {
            return $this->serverError('حدث خطأ أثناء حذف السجل');
        }
    }
}
