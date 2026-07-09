<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class ClinicOrder extends Model
{
    public const TYPE_LAB = 'lab';

    public const TYPE_RADIOLOGY = 'radiology';

    public const STATUS_ORDERED = 'ordered';

    public const STATUS_SAMPLE_COLLECTED = 'sample_collected';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'clinic_orders';

    protected $fillable = [
        'order_number',
        'doctor_id',
        'branch_id',
        'patient_id',
        'clinic_patient_id',
        'clinic_booking_id',
        'type',
        'status',
        'tests',
        'clinical_notes',
        'result_summary',
        'result_files',
        'ordered_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'tests' => 'array',
        'result_files' => 'array',
        'completed_at' => 'datetime',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(DoctorBranch::class, 'branch_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function clinicPatient(): BelongsTo
    {
        return $this->belongsTo(ClinicPatient::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ClinicBooking::class, 'clinic_booking_id');
    }

    public function orderedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }
}
