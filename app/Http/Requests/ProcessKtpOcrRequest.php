<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessKtpOcrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ktp_photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120', // 5MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ktp_photo.required'  => 'File KTP wajib diupload.',
            'ktp_photo.image'     => 'File harus berupa gambar.',
            'ktp_photo.mimes'     => 'Format file harus JPG, JPEG, atau PNG.',
            'ktp_photo.max'       => 'Ukuran file tidak boleh lebih dari 5MB.',
        ];
    }
}
