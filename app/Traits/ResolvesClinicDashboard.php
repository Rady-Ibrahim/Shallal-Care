<?php

namespace App\Traits;

use App\Support\ClinicDashboardContext;
use Modules\Doctor\Models\Doctor;

trait ResolvesClinicDashboard
{
    protected function clinicContext(): ClinicDashboardContext
    {
        return ClinicDashboardContext::resolve();
    }

    protected function resolveDoctor(): Doctor
    {
        return $this->clinicContext()->doctor;
    }
}
