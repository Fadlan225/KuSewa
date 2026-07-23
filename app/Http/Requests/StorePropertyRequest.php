<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan middleware auth sudah menangani hak akses
    }

    public function rules(): array
    {
        return [
            'nama_properti' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'in:Kos & Rumah,Apartemen,Kendaraan,Ruko / Lapak'],
            'tipe_sewa' => ['required', 'string', 'in:Harian,Bulanan,Tahunan'],
            'deskripsi' => ['nullable', 'string'],
            'kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'alamat_lengkap' => ['required', 'string'],
            'fasilitas' => ['nullable', 'array'],
            'harga_sewa' => ['required', 'numeric', 'min:0'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'foto_properti.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048']
        ];
    }
}