<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class WorkspaceController extends Controller
{
    public function bookings(Request $request): Response
    {
        $ownerProfile = $request->user()->ownerProfile;
        
        $query = booking::query()
            ->with(['asset:id,title,owner_profile_id', 'user:id,name'])
            ->when($ownerProfile, fn($query) => $query->whereHas('asset', fn($assetQuery) => $assetQuery->where('owner_profile_id', $ownerProfile->id)), fn($query) => $query->whereRaw('1 = 0'));

        // Hitung jumlah booking per status (seluruh data, bukan hanya halaman ini)
        $statusCounts = [
            'all' => (clone $query)->count(),
            'pending' => (clone $query)->where('booking_status', 'pending')->count(),
            'confirmed' => (clone $query)->where('booking_status', 'confirmed')->count(),
            'active' => (clone $query)->where('booking_status', 'active')->count(),
            'completed' => (clone $query)->where('booking_status', 'completed')->count(),
            'cancelled' => (clone $query)->where('booking_status', 'cancelled')->count(),
        ];

        // Filter by status if provided
        $statusFilter = $request->input('status');
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('booking_status', $statusFilter);
        }

        $bookings = $query->latest()
            ->paginate(10)
            ->through(fn(booking $booking) => [
                'id' => $booking->id,
                'code' => $booking->booking_code,
                'asset' => $booking->asset?->title ?? 'Aset telah dihapus',
                'tenant' => $booking->user?->name ?? 'Penyewa',
                'period' => $booking->start_date?->format('d M Y') . ' - ' . $booking->end_date?->format('d M Y'),
                'total' => (float) $booking->total,
                'status' => $booking->booking_status,
            ]);

        return $this->page('bookings', 'Pemesanan', 'Kelola permintaan sewa dan pantau status pesanan aset Anda.', [
            'bookings' => $bookings,
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * Tinjau detail satu booking (untuk owner konfirmasi/tolak)
     */
    public function review(booking $booking, Request $request): Response
    {
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || !$booking->asset || $booking->asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }

        $booking->load(['asset:id,title,owner_profile_id', 'user:id,name,email,phone']);

        return Inertia::render('owner/BookingReview', [
            'booking' => [
                'id' => $booking->id,
                'code' => $booking->booking_code,
                'asset' => $booking->asset?->title ?? 'Aset telah dihapus',
                'tenant' => $booking->user?->name ?? 'Penyewa',
                'tenant_email' => $booking->user?->email ?? '-',
                'tenant_phone' => $booking->user?->phone ?? '-',
                'start_date' => $booking->start_date->format('d M Y'),
                'end_date' => $booking->end_date->format('d M Y'),
                'subtotal' => (float) $booking->subtotal,
                'service_fee' => (float) $booking->service_fee,
                'total' => (float) $booking->total,
                'status' => $booking->booking_status,
            ],
        ]);
    }

    /**
     * Konfirmasi booking (pending → confirmed)
     */
    public function confirm(booking $booking, Request $request): RedirectResponse
    {
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || !$booking->asset || $booking->asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }

        if ($booking->booking_status !== 'pending') {
            return back()->with('error', 'Hanya booking berstatus menunggu yang dapat dikonfirmasi.');
        }

        $booking->update(['booking_status' => 'confirmed']);

        return redirect()->route('owner.bookings')
            ->with('success', 'Booking ' . $booking->booking_code . ' berhasil dikonfirmasi.');
    }

    /**
     * Tolak booking (pending → cancelled)
     */
    public function rejectBooking(booking $booking, Request $request): RedirectResponse
    {
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || !$booking->asset || $booking->asset->owner_profile_id !== $ownerProfile->id) {
            abort(403);
        }

        if ($booking->booking_status !== 'pending') {
            return back()->with('error', 'Hanya booking berstatus menunggu yang dapat ditolak.');
        }

        $booking->update(['booking_status' => 'cancelled']);

        return redirect()->route('owner.bookings')
            ->with('success', 'Booking ' . $booking->booking_code . ' telah ditolak.');
    }

    public function finance(Request $request): Response
    {
        $ownerProfile = $request->user()->ownerProfile;
        $bookings = booking::query()
            ->with('asset:id,title')
            ->when($ownerProfile, fn($query) => $query->whereHas('asset', fn($assetQuery) => $assetQuery->where('owner_profile_id', $ownerProfile->id)), fn($query) => $query->whereRaw('1 = 0'))
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->latest()
            ->get();

        return $this->page('finance', 'Keuangan', 'Ringkasan pendapatan dari pesanan aset yang telah dikonfirmasi.', [
            'income' => (float) $bookings->sum('total'),
            'fees' => (float) $bookings->sum('service_fee'),
            'transactions' => $bookings->take(8)->map(fn(booking $booking) => [
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
