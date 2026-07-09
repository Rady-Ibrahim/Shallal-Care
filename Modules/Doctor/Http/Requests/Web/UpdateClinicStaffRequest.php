<?php

namespace Modules\Doctor\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Doctor\Support\SecretaryPermissions;

class UpdateClinicStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = (int) $this->route('id');
        $userId = \Modules\Doctor\Models\ClinicStaffMember::where('id', $memberId)->value('user_id');

        return [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,'.$userId,
            'email' => 'nullable|email|max:255|unique:users,email,'.$userId,
            'password' => 'nullable|string|min:8|confirmed',
            'branch_id' => 'sometimes|integer|exists:doctor_branches,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:'.implode(',', array_keys(SecretaryPermissions::ALL)),
        ];
    }
}
