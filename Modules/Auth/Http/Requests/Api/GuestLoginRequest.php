<?php

namespace Modules\Auth\Http\Requests\Api;

use App\Http\Requests\ApiFormRequest;

class GuestLoginRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'device_id' => 'required|string|min:8|max:128',
        ];
    }

    public function messages(): array
    {
        return [
            'device_id.required' => 'معرّف الجهاز مطلوب',
        ];
    }
}
