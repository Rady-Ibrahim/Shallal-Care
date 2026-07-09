<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Auth\Models\User;

class ClinicPatient extends Model
{
    protected $table = 'clinic_patients';

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'file_number',
        'qr_token',
        'registered_branch_id',
        'allergies',
        'chronic_conditions',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(DoctorBranch::class, 'registered_branch_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ClinicBooking::class);
    }
}
