<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerRegistrationStep3Request extends FormRequest
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
        $user = auth()->user();
        $hasPhoto = $user->ownerProfile && $user->ownerProfile->ktp_photo;

        return [
            'ktp_photo' => [$hasPhoto ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // max 5MB
        ];
    }
}
