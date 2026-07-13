<?php

namespace Modules\Doctor\Http\Controllers\Web;

use App\Support\ClinicDashboardContext;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class DoctorDashboardWebController extends Controller
{
    protected function context(): ClinicDashboardContext
    {
        return ClinicDashboardContext::resolve();
    }

    public function dashboard(): View
    {
        return view('doctor.dashboard', ['doctor' => $this->context()->doctor]);
    }

    public function calendar(): View
    {
        return view('doctor.calendar');
    }

    public function settings(): View
    {
        return view('doctor.settings', ['doctor' => $this->context()->doctor]);
    }

    public function patients(): View
    {
        return view('doctor.patients.index');
    }

    public function patientShow(int $id): View
    {
        return view('doctor.patients.show', ['patientId' => $id]);
    }

    public function patientRecords(int $id): View
    {
        return view('doctor.patients.records', ['patientId' => $id]);
    }

    public function prescriptions(): View
    {
        return view('doctor.prescriptions.index');
    }

    public function prescriptionCreate(): View
    {
        return view('doctor.prescriptions.create');
    }

    public function prescriptionShow(int $id): View
    {
        return view('doctor.prescriptions.show', ['prescriptionId' => $id]);
    }

    public function prescriptionEdit(int $id): View
    {
        return view('doctor.prescriptions.edit', ['prescriptionId' => $id]);
    }

    public function records(): View
    {
        return view('doctor.records.index');
    }

    public function recordCreate(): View
    {
        return view('doctor.records.create');
    }

    public function recordShow(int $id): View
    {
        return view('doctor.records.show', ['recordId' => $id]);
    }

    public function recordEdit(int $id): View
    {
        return view('doctor.records.edit', ['recordId' => $id]);
    }

    public function staff(): View
    {
        return view('doctor.staff.index', [
            'permissionLabels' => \Modules\Doctor\Support\SecretaryPermissions::labels(),
            'defaultPermissions' => \Modules\Doctor\Support\SecretaryPermissions::DEFAULT,
        ]);
    }

    public function subscriptionPlans(): View
    {
        return view('doctor.subscription.plans', ['doctor' => $this->context()->doctor]);
    }

    public function requests(): View
    {
        return view('doctor.requests.index');
    }

    public function reception(): View
    {
        return view('doctor.reception.index');
    }

    public function queue(): View
    {
        return view('doctor.queue.index');
    }

    public function finance(): View
    {
        return view('doctor.finance.index', [
            'paymentMethods' => config('clinic.payment_methods', []),
            'expenseCategories' => config('clinic.expense_categories', []),
            'canManageFinance' => $this->context()->hasPermission('finance.collect'),
        ]);
    }

    public function visit(int $bookingId): View
    {
        return view('doctor.visits.show', [
            'bookingId' => $bookingId,
            'canManageVisit' => $this->context()->hasPermission('records.manage'),
        ]);
    }

    public function patientFile(int $id): View
    {
        return view('doctor.patients.file', [
            'patientId' => $id,
        ]);
    }

    public function orders(): View
    {
        return view('doctor.orders.index', [
            'canManageOrders' => $this->context()->hasPermission('lab.manage'),
            'labTests' => config('clinic.lab_tests', []),
            'radiologyTypes' => config('clinic.radiology_types', []),
        ]);
    }

    public function reports(): View
    {
        return view('doctor.reports.index');
    }

    public function clinicDay(): View
    {
        return view('doctor.today.index', [
            'currencySymbol' => config('clinic.currency_symbol', 'ج.م'),
        ]);
    }

    public function waitingScreen(): View
    {
        return view('doctor.waiting-screen', [
            'branchName' => $this->context()->branch?->branch_name ?? 'العيادة',
        ]);
    }

    public function financePrint(): View
    {
        return view('doctor.finance.print', [
            'currencySymbol' => config('clinic.currency_symbol', 'ج.م'),
            'branchName' => $this->context()->branch?->branch_name ?? '',
            'date' => today()->format('Y-m-d'),
        ]);
    }
}
