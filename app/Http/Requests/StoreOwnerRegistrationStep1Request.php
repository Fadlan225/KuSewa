<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOwnerRegistrationStep1Request extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'phone' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId)
            ],
            'gender' => ['required', 'string', 'in:male,female'],
            'place_of_birth_code' => ['required', 'string'],
            'date_of_birth' => ['required', 'date'],
            'national_id' => [
                'required',
                'string',
                'size:16',
                Rule::unique('owner_profiles', 'national_id')->ignore($userId, 'user_id')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'national_id.unique' => 'NIK ini sudah terdaftar oleh akun lain.',
            'email.unique' => 'Email ini sudah terdaftar oleh akun lain.',
            'phone.unique' => 'Nomor telepon ini sudah terdaftar oleh akun lain.',
        ];
    }
}
