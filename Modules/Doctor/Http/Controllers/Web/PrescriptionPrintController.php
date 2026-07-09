<?php

namespace Modules\Doctor\Http\Controllers\Web;

use App\Support\ClinicDashboardContext;
use App\Traits\ResolvesClinicDashboard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Doctor\Services\DoctorDashboardService;

class PrescriptionPrintController extends Controller
{
    use ResolvesClinicDashboard;

    public function __construct(private DoctorDashboardService $dashboardService) {}

    public function show(Request $request, int $id): View
    {
        $context = ClinicDashboardContext::resolve();
        $paper = $request->query('paper', config('clinic.prescription.default_paper', 'a4'));

        if (! in_array($paper, ['a4', 'half_a4'], true)) {
            $paper = 'a4';
        }

        $prescription = $this->dashboardService->getPrescriptionForPrint(
            $context->doctorId(),
            $id,
            $context->branch
        );

        return view('doctor.prescriptions.print', [
            'prescription' => $prescription,
            'paper' => $paper,
            'company' => config('clinic.company'),
        ]);
    }
}
