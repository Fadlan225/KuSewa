<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class KonfirmasiPembayaranController extends Controller
{
    /**
     * Menampilkan halaman Form Konfirmasi Pembayaran
     */
    public function index()
    {
        // DIPERBAIKI: Mengarahkan ke Home/Assets/KonfirmasiPembayaran
        return Inertia::render('Home/Assets/KonfirmasiPembayaran');
    }

    /**
     * Menyimpan data konfirmasi pembayaran & upload foto bukti
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'booking_id' => 'required|string|max:50',
            'bank_pengirim' => 'required|string|max:50',
            'nama_pengirim' => 'required|string|max:100',
            'jumlah_transfer' => 'required|numeric|min:1',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Maksimal 5MB
        ], [
            'booking_id.required' => 'ID Pesanan wajib diisi.',
            'bank_pengirim.required' => 'Pilih bank atau metode pengiriman.',
            'nama_pengirim.required' => 'Nama pengirim wajib diisi.',
            'jumlah_transfer.required' => 'Jumlah transfer wajib diisi.',
            'bukti_transfer.required' => 'Bukti transfer wajib diunggah.',
            'bukti_transfer.image' => 'File bukti transfer harus berupa gambar.',
            'bukti_transfer.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        // 2. Upload file bukti transfer ke folder storage/app/public/bukti-transfer
        if ($request->hasFile('bukti_transfer')) {
            $filePath = $request->file('bukti_transfer')->store('bukti-transfer', 'public');
            $validated['bukti_transfer'] = $filePath;
        }

        // 3. Simpan data ke Database (Contoh menggunakan model Pembayaran)
        // \App\Models\Pembayaran::create([
        //     'user_id'         => auth()->id(),
        //     'booking_id'      => $validated['booking_id'],
        //     'bank_pengirim'   => $validated['bank_pengirim'],
        //     'nama_pengirim'   => $validated['nama_pengirim'],
        //     'jumlah_transfer' => $validated['jumlah_transfer'],
        //     'bukti_transfer'  => $validated['bukti_transfer'],
        //     'status'          => 'pending', // Menunggu verifikasi admin
        // ]);

        // 4. Redirect kembali dengan Flash Message Sukses ke Inertia
        return redirect()->back()->with('success', 'Konfirmasi pembayaran berhasil dikirim!');
    }
}