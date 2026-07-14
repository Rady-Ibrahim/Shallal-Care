<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Auth\Models\User;
use Modules\Doctor\Models\ClinicStaffMember;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\DoctorBranch;
use Modules\Doctor\Support\SecretaryPermissions;

class ClinicDashboardContext
{
    public function __construct(
        public readonly Doctor $doctor,
        public readonly User $user,
        public readonly ?ClinicStaffMember $staffMember = null,
        public readonly ?DoctorBranch $branch = null,
    ) {}

    public static function resolve(): self
    {
        if (app()->bound(self::class)) {
            return app(self::class);
        }

        return self::make();
    }

    public static function make(): self
    {
        $user = auth('web')->user();

        if (! $user) {
            throw new AuthorizationException('يجب تسجيل الدخول أولاً');
        }

        if ($user->isDoctor()) {
            $doctor = Doctor::with(['user', 'primaryBranch'])->where('user_id', $user->id)->firstOrFail();
            $branchId = session('clinic_branch_id', $doctor->primaryBranch?->id);
            $branch = $branchId
                ? DoctorBranch::where('doctor_id', $doctor->id)->find($branchId)
                : $doctor->primaryBranch;

            return new self($doctor, $user, null, $branch);
        }

        if ($user->isSecretary()) {
            $staffMember = ClinicStaffMember::with(['doctor.user', 'branch'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->firstOrFail();

            return new self($staffMember->doctor, $user, $staffMember, $staffMember->branch);
        }

        throw new AuthorizationException('غير مصرح لك بالوصول إلى لوحة العيادة');
    }

    public function isOwner(): bool
    {
        return $this->staffMember === null;
    }

    public function isSecretary(): bool
    {
        return $this->staffMember !== null;
    }

    public function permissions(): array
    {
        if ($this->isOwner()) {
            return array_keys(SecretaryPermissions::ALL);
        }

        return $this->staffMember?->permissions ?? [];
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return $this->staffMember?->hasPermission($permission) ?? false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function doctorId(): int
    {
        return (int) $this->doctor->id;
    }

    public function branchId(): ?int
    {
        return $this->branch?->id ? (int) $this->branch->id : null;
    }
}
