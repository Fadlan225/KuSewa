<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function bookings(Request $request): Response
    {
        $ownerProfile = $request->user()->ownerProfile;
        $bookings = booking::query()
            ->with(['asset:id,title', 'user:id,name'])
            ->when($ownerProfile, fn ($query) => $query->whereHas('asset', fn ($assetQuery) => $assetQuery->where('owner_profile_id', $ownerProfile->id)), fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->get()
            ->map(fn (booking $booking) => [
                'code' => $booking->booking_code,
                'asset' => $booking->asset?->title ?? 'Aset telah dihapus',
                'tenant' => $booking->user?->name ?? 'Penyewa',
                'period' => date('d M Y', strtotime($booking->start_date)).' - '.date('d M Y', strtotime($booking->end_date)),
                'total' => (float) $booking->total,
                'status' => $booking->booking_status,
            ]);

        return $this->page('bookings', 'Pemesanan', 'Kelola permintaan sewa dan pantau status pesanan aset Anda.', ['bookings' => $bookings]);
    }

    public function finance(Request $request): Response
    {
        $ownerProfile = $request->user()->ownerProfile;
        $bookings = booking::query()
            ->with('asset:id,title')
            ->when($ownerProfile, fn ($query) => $query->whereHas('asset', fn ($assetQuery) => $assetQuery->where('owner_profile_id', $ownerProfile->id)), fn ($query) => $query->whereRaw('1 = 0'))
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->latest()
            ->get();

        return $this->page('finance', 'Keuangan', 'Ringkasan pendapatan dari pesanan aset yang telah dikonfirmasi.', [
            'income' => (float) $bookings->sum('total'),
            'fees' => (float) $bookings->sum('service_fee'),
            'transactions' => $bookings->take(8)->map(fn (booking $booking) => [
                'code' => $booking->booking_code,
                'asset' => $booking->asset?->title ?? 'Aset',
                'date' => $booking->created_at->format('d M Y'),
                'total' => (float) $booking->total,
            ]),
        ]);
    }

    public function verification(Request $request): Response
    {
        $profile = $request->user()->ownerProfile;

        return $this->page('verification', 'Verifikasi Berkas', 'Pastikan data pemilik dan dokumen pendukung sudah lengkap.', [
            'status' => $profile?->status ?? 'pending',
            'documents' => [
                ['name' => 'Identitas pemilik (KTP)', 'complete' => filled($profile?->ktp_photo)],
                ['name' => 'Data alamat domisili', 'complete' => filled($profile?->address)],
                ['name' => 'Profil pemilik', 'complete' => $profile !== null],
            ],
        ]);
    }

    public function settings(Request $request): Response
    {
        return $this->page('settings', 'Pengaturan Akun', 'Kelola informasi akun dan preferensi notifikasi Anda.', [
            'user' => $request->user()->only('name', 'email', 'phone'),
        ]);
    }

    public function help(): Response
    {
        return $this->page('help', 'Bantuan kusewa', 'Temukan jawaban cepat atau hubungi tim dukungan kami.', [
            'faqs' => [
                ['question' => 'Bagaimana menambahkan aset?', 'answer' => 'Buka menu Properti & Aset, lalu pilih Tambah Properti Baru dan lengkapi data unit.'],
                ['question' => 'Kapan pendapatan masuk?', 'answer' => 'Pendapatan tercatat setelah pesanan dikonfirmasi sesuai proses pembayaran yang berlaku.'],
                ['question' => 'Bagaimana memperbarui status unit?', 'answer' => 'Buka Properti & Aset, pilih ikon edit pada unit, kemudian perbarui status keterisian.'],
            ],
        ]);
    }

    private function page(string $type, string $title, string $description, array $data = []): Response
    {
        return Inertia::render('owner/Workspace', array_merge($data, compact('type', 'title', 'description')));
    }
}
