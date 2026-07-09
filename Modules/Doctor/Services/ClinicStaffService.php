<?php

namespace Modules\Doctor\Services;

use App\Support\PhoneNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Modules\Doctor\Models\ClinicStaffMember;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Support\SecretaryPermissions;

class ClinicStaffService
{
    public function listForDoctor(int $doctorId): Collection
    {
        return ClinicStaffMember::with(['user', 'branch'])
            ->where('doctor_id', $doctorId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ClinicStaffMember $member) => $this->formatMember($member));
    }

    public function createStaff(Doctor $doctor, array $data): ClinicStaffMember
    {
        return DB::transaction(function () use ($doctor, $data) {
            $phone = PhoneNormalizer::toE164($data['phone']);
            $permissions = SecretaryPermissions::sanitize(
                $data['permissions'] ?? SecretaryPermissions::DEFAULT
            );

            if ($permissions === []) {
                $permissions = SecretaryPermissions::DEFAULT;
            }

            $user = User::create([
                'name' => $data['name'],
                'phone' => $phone,
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'secretary',
                'status' => 'active',
                'phone_verified_at' => now(),
            ]);

            return ClinicStaffMember::create([
                'doctor_id' => $doctor->id,
                'branch_id' => $data['branch_id'],
                'user_id' => $user->id,
                'permissions' => $permissions,
                'status' => 'active',
            ])->load(['user', 'branch']);
        });
    }

    public function updateStaff(Doctor $doctor, int $memberId, array $data): ClinicStaffMember
    {
        $member = $this->findMemberForDoctor($doctor->id, $memberId);

        return DB::transaction(function () use ($member, $data) {
            $userUpdates = [];

            if (isset($data['name'])) {
                $userUpdates['name'] = $data['name'];
            }

            if (isset($data['phone'])) {
                $userUpdates['phone'] = PhoneNormalizer::toE164($data['phone']);
            }

            if (array_key_exists('email', $data)) {
                $userUpdates['email'] = $data['email'];
            }

            if (! empty($data['password'])) {
                $userUpdates['password'] = Hash::make($data['password']);
            }

            if ($userUpdates !== []) {
                $member->user->update($userUpdates);
            }

            $memberUpdates = [];

            if (isset($data['branch_id'])) {
                $memberUpdates['branch_id'] = $data['branch_id'];
            }

            if (isset($data['permissions'])) {
                $permissions = SecretaryPermissions::sanitize($data['permissions']);
                $memberUpdates['permissions'] = $permissions === []
                    ? SecretaryPermissions::DEFAULT
                    : $permissions;
            }

            if ($memberUpdates !== []) {
                $member->update($memberUpdates);
            }

            return $member->fresh(['user', 'branch']);
        });
    }

    public function updateStatus(Doctor $doctor, int $memberId, string $status): ClinicStaffMember
    {
        $member = $this->findMemberForDoctor($doctor->id, $memberId);
        $member->update(['status' => $status]);
        $member->user->update(['status' => $status === 'active' ? 'active' : 'inactive']);

        return $member->fresh(['user', 'branch']);
    }

    public function deleteStaff(Doctor $doctor, int $memberId): void
    {
        $member = $this->findMemberForDoctor($doctor->id, $memberId);

        DB::transaction(function () use ($member) {
            $member->user->delete();
            $member->delete();
        });
    }

    public function permissionCatalog(): array
    {
        return SecretaryPermissions::labels();
    }

    private function findMemberForDoctor(int $doctorId, int $memberId): ClinicStaffMember
    {
        return ClinicStaffMember::with(['user', 'branch'])
            ->where('doctor_id', $doctorId)
            ->where('id', $memberId)
            ->firstOrFail();
    }

    private function formatMember(ClinicStaffMember $member): array
    {
        return [
            'id' => $member->id,
            'status' => $member->status,
            'permissions' => $member->permissions ?? [],
            'branch' => [
                'id' => $member->branch?->id,
                'name' => $member->branch?->branch_name,
            ],
            'created_at' => $member->created_at?->format('Y-m-d H:i'),
            'user' => [
                'id' => $member->user->id,
                'name' => $member->user->name,
                'phone' => $member->user->phone,
                'email' => $member->user->email,
                'status' => $member->user->status,
            ],
        ];
    }
}
