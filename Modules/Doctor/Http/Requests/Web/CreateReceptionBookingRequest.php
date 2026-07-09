<?php

namespace Modules\Doctor\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class CreateReceptionBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female',
            'consultation_fee' => 'nullable|numeric|min:0',
            'visit_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المريض مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب لتسجيل المريض',
        ];
    }
}
