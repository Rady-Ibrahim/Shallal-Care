<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Auth\Models\User;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctors';

    protected $fillable = [
        'user_id',
        'clinic_name',
        'speciality_id',
        'bio_ar',
        'bio_en',
        'experience_years',
        'syndicate_number',
        'consultation_fee',
        'consultation_type',
        'rating',
        'rating_count',
        'latitude',
        'longitude',
        'address',
        'status',
        'subscription_id',
        'license_document',
        'clinic_image',
        'signature_path',
        'stamp_path',
        'reject_reason',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'latitude' => 'decimal:10',
        'longitude' => 'decimal:10',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function branches()
    {
        return $this->hasMany(DoctorBranch::class);
    }

    public function primaryBranch()
    {
        return $this->hasOne(DoctorBranch::class)->where('is_primary', true);
    }

    public function appointments()
    {
        return $this->hasManyThrough(
            \Modules\Appointment\Models\Appointment::class,
            DoctorSchedule::class
        );
    }

    public function reviews()
    {
        return $this->hasMany(\Modules\Review\Models\Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(\Modules\Review\Models\Review::class)
            ->where('status', \Modules\Review\Models\Review::STATUS_APPROVED)
            ->where('is_flagged', false);
    }

    public function subscription()
    {
        return $this->belongsTo(\Modules\Subscription\Models\Subscription::class);
    }

    public function doctorSubscriptions()
    {
        return $this->hasMany(\Modules\Subscription\Models\DoctorSubscription::class);
    }

    public function staffMembers()
    {
        return $this->hasMany(\Modules\Doctor\Models\ClinicStaffMember::class);
    }

    public function activeSubscription()
    {
        return $this->doctorSubscriptions()->active()->with('subscription')->first();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function updateRating(float $newRating): void
    {
        $totalRating = ($this->rating * $this->rating_count) + $newRating;
        $this->rating_count++;
        $this->rating = $totalRating / $this->rating_count;
        $this->save();
    }

    public function recalculateRatingFromReviews(): void
    {
        $stats = $this->approvedReviews()
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();

        $count = (int) ($stats->count ?? 0);
        $this->rating_count = $count;
        $this->rating = $count > 0 ? round((float) $stats->average, 2) : 0;
        $this->save();
    }

    public function hasFeaturedSubscription(): bool
    {
        $active = $this->activeSubscription();

        return $active
            && $active->subscription
            && $active->subscription->is_featured
            && $active->subscription->status === 'active';
    }
}
