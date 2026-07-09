<?php

namespace Modules\MedicalRecord\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Models\Appointment;
use Modules\Doctor\Models\Doctor;
use Modules\Auth\Models\User;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medical_records';

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'branch_id',
        'patient_id',
        'clinic_booking_id',
        'prescription_number',
        'record_type',
        'diagnosis',
        'prescription',
        'notes',
        'attachments',
        'created_by',
        'weight',
        'height',
        'blood_pressure',
        'allergies',
    ];

    protected $casts = [
        'prescription' => 'array',
        'attachments' => 'array',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function clinicBooking()
    {
        return $this->belongsTo(\Modules\Doctor\Models\ClinicBooking::class, 'clinic_booking_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
