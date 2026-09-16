<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'national_id' => ['nullable', 'string', 'size:16'],
            'address' => ['nullable', 'string', 'max:255'],
            'place_of_birth_code' => ['nullable', 'string', 'exists:cities,code'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:10'],
            'bank_code' => ['nullable', 'string', 'exists:banks,code'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder' => ['nullable', 'string', 'max:255'],
        ];
    }
}
