<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class BranchTransaction extends Model
{
    protected $table = 'branch_transactions';

    protected $fillable = [
        'branch_id',
        'doctor_id',
        'clinic_booking_id',
        'type',
        'category',
        'amount',
        'payment_method',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(DoctorBranch::class, 'branch_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ClinicBooking::class, 'clinic_booking_id');
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
