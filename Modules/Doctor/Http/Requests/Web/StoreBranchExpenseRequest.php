<?php

namespace Modules\Doctor\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categories = array_keys(config('clinic.expense_categories', []));

        return [
            'category' => 'required|string|in:'.implode(',', $categories),
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|in:'.implode(',', array_keys(config('clinic.payment_methods', []))),
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'نوع المصروف مطلوب',
            'amount.required' => 'المبلغ مطلوب',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
        ];
    }
}
