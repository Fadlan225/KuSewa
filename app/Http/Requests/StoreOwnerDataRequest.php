<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOwnerDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            // Data user
            'name'               => ['required', 'string', 'max:255'],
            'email'              => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'              => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'gender'             => ['required', 'in:male,female'],
            'place_of_birth_code' => ['required', 'string'],
            'date_of_birth'      => ['required', 'date', 'before:today'],

            // Data owner_profile
            'national_id'        => [
                'required', 'string', 'size:16', 'regex:/^\d{16}$/',
                Rule::unique('owner_profiles', 'national_id')->ignore($userId, 'user_id'),
            ],
            'religion'           => ['nullable', 'string', 'max:50'],
            'marital_status'     => ['nullable', 'string', 'max:50'],
            'occupation'         => ['nullable', 'string', 'max:100'],
            'nationality'        => ['nullable', 'string', 'max:10'],

            // Alamat domisili (dari review form)
            'province_code'      => ['required', 'string'],
            'city_code'          => ['required', 'string'],
            'district_code'      => ['required', 'string'],
            'village_code'       => ['required', 'string'],
            'postal_code'        => ['required', 'numeric', 'digits:5'],
            'address'            => ['required', 'string', 'max:500'],

            // Path file KTP (disimpan saat upload)
            'ktp_temp_path'      => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'national_id.required' => 'NIK wajib diisi.',
            'national_id.size'     => 'NIK harus tepat 16 digit.',
            'national_id.regex'    => 'NIK hanya boleh berisi angka.',
            'national_id.unique'   => 'NIK ini sudah terdaftar oleh akun lain.',
            'email.unique'         => 'Email ini sudah terdaftar oleh akun lain.',
            'phone.unique'         => 'Nomor telepon ini sudah terdaftar oleh akun lain.',
            'date_of_birth.before' => 'Tanggal lahir tidak valid.',
        ];
    }
}
