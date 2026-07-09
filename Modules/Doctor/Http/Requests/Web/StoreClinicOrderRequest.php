<?php

namespace Modules\Doctor\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreClinicOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:lab,radiology',
            'patient_id' => 'required|integer|exists:users,id',
            'clinic_booking_id' => 'nullable|integer|exists:clinic_bookings,id',
            'tests' => 'required|array|min:1',
            'tests.*' => 'required|string|max:64',
            'clinical_notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'المريض مطلوب',
            'tests.required' => 'اختر تحليلاً أو أشعة واحدة على الأقل',
            'tests.min' => 'اختر تحليلاً أو أشعة واحدة على الأقل',
        ];
    }
}
