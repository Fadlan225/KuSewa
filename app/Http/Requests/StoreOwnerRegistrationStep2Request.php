<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerRegistrationStep2Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'province_code' => ['required', 'string'],
            'city_code' => ['required', 'string'],
            'district_code' => ['required', 'string'],
            'village_code' => ['required', 'string'],
            'postal_code' => ['required', 'numeric', 'digits:5'],
            'address' => ['required', 'string'],
        ];
    }
}
