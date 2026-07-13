<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class ClinicBooking extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_WAITING = 'waiting';

    public const STATUS_WITH_DOCTOR = 'with_doctor';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    protected $table = 'clinic_bookings';

    protected $fillable = [
        'booking_number',
        'daily_number',
        'doctor_id',
        'branch_id',
        'clinic_patient_id',
        'patient_id',
        'visit_date',
        'status',
        'consultation_fee',
        'payment_status',
        'payment_method',
        'registered_by',
        'queue_position',
        'checked_in_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'consultation_fee' => 'decimal:2',
        'checked_in_at' => 'datetime',
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

    public function clinicPatient(): BelongsTo
    {
        return $this->belongsTo(ClinicPatient::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function isInQueue(): bool
    {
        return in_array($this->status, [self::STATUS_WAITING, self::STATUS_WITH_DOCTOR], true);
    }

    public function getDisplayBookingNumberAttribute(): string
    {
        if ($this->daily_number) {
            return (string) $this->daily_number;
        }

        if (preg_match('/-(\d+)$/', (string) $this->booking_number, $matches)) {
            return $matches[1];
        }

        return (string) $this->booking_number;
    }
}
