<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\asset;
use App\Models\asset_category;
use App\Models\booking;
use App\Models\OwnerBilling;
use App\Models\service_fee as ServiceFee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Tampilkan daftar semua booking pada aset milik owner yang sedang login.
     * Support filter: status, kategori, jenis (via query param).
     * Semua data menggunakan eager loading untuk performa optimal.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownerProfile = $user->ownerProfile;

        if (!$ownerProfile) {
            return Inertia::render('owner/Workspace', [
                'type'         => 'bookings',
                'title'        => 'Daftar Pesanan',
                'description'  => 'Pantau dan kelola semua pesanan aset Anda di satu tempat.',
                'bookings'     => ['data' => [], 'meta' => ['total' => 0, 'from' => 0, 'to' => 0, 'links' => []]],
                'statusCounts' => array_fill_keys(['all', 'pending', 'confirmed', 'active', 'completed', 'cancelled'], 0),
                'kategoriGroups' => [],
            ]);
        }

        // Ambil semua ID aset milik owner ini (1 query)
        $assetIds = asset::where('owner_profile_id', $ownerProfile->id)->pluck('id');

        // === Hitung status counts (1 query terpisah sebelum pagination) ===
        $statusCountsRaw = booking::whereIn('asset_id', $assetIds)
            ->select('booking_status', DB::raw('count(*) as total'))
            ->groupBy('booking_status')
            ->pluck('total', 'booking_status')
            ->toArray();

        $totalAll = array_sum($statusCountsRaw);
        $statusCounts = [
            'all'       => $totalAll,
            'pending'   => $statusCountsRaw['pending']   ?? 0,
            'confirmed' => $statusCountsRaw['confirmed'] ?? 0,
            'active'    => $statusCountsRaw['active']    ?? 0,
            'completed' => $statusCountsRaw['completed'] ?? 0,
            'cancelled' => $statusCountsRaw['cancelled'] ?? 0,
        ];

        // === Query utama: booking dengan eager loading penuh ===
        $query = booking::with([
            'asset:id,title,asset_type_id,owner_profile_id',
            'asset.type:id,name,category_id,rental_unit',
            'asset.type.category:id,name',
            'asset.firstImage:id,asset_id,image',
            'user:id,name,email,phone',
            'assetUnit:id,name',
            'payment:id,booking_id,payment_status,payment_method,proof_of_payment,expires_at',
        ])
        ->whereIn('asset_id', $assetIds)
        ->latest();

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('booking_status', $request->status);
        }

        // Filter by kategori (via asset type category)
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->whereHas('asset.type.category', function ($q) use ($request) {
                $q->where('name', $request->kategori);
            });
        }

        // Filter by jenis (asset type name)
        if ($request->filled('jenis') && $request->jenis !== 'all') {
            $query->whereHas('asset.type', function ($q) use ($request) {
                $q->where('name', $request->jenis);
            });
        }

        // Filter by tanggal (created_at)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $bookings = $query->paginate(15)->withQueryString();

        // Transform data untuk frontend — semua field yang dibutuhkan view
        $bookingsTransformed = $bookings->through(function ($booking) {
            return [
                'id'            => $booking->id,
                'code'          => $booking->booking_code,
                'asset'         => $booking->asset_name ?? $booking->asset?->title,
                'unit'          => $booking->asset_unit_name,
                'kategori'      => $booking->asset?->type?->category?->name,
                'jenis'         => $booking->asset?->type?->name,
                'status'        => $booking->booking_status,
                'tenant'        => $booking->booker_name,
                'tenant_email'  => $booking->booker_email,
                'tenant_phone'  => $booking->booker_phone,
                'guest_name'    => $booking->guest_name,
                'start_date'    => Carbon::parse($booking->start_date)->translatedFormat('d M Y'),
                'end_date'      => Carbon::parse($booking->end_date)->translatedFormat('d M Y'),
                'period'        => Carbon::parse($booking->start_date)->translatedFormat('d M Y')
                                   . ' - ' . Carbon::parse($booking->end_date)->translatedFormat('d M Y'),
                'subtotal'      => $booking->subtotal,
                'service_fee'   => $booking->service_fee,
                'total'         => $booking->total,
                'payment'       => $booking->payment ? [
                    'status'            => $booking->payment->payment_status,
                    'method'            => $booking->payment->payment_method,
                    'proof'             => $booking->payment->proof_of_payment,
                    'expires_at'        => $booking->payment->expires_at,
                ] : null,
                'asset_image'   => $booking->asset?->firstImage?->image,
                'created_at'    => $booking->created_at->translatedFormat('d M Y, H:i'),
            ];
        });

        // === Ambil kategori grup dari database untuk filter dropdown ===
        $kategoriGroups = asset_category::with(['types:id,category_id,name'])
            ->get(['id', 'name'])
            ->map(fn($cat) => [
                'label'   => $cat->name,
                'options' => $cat->types->pluck('name')->toArray(),
            ])
            ->toArray();

        return Inertia::render('owner/Booking/Index', [
            'type'           => 'bookings',
            'title'          => 'Daftar Pesanan',
            'description'    => 'Pantau dan kelola semua pesanan aset Anda di satu tempat.',
            'bookings'       => [
                'data' => $bookingsTransformed->items(),
                'meta' => [
                    'total'        => $bookings->total(),
                    'from'         => $bookings->firstItem() ?? 0,
                    'to'           => $bookings->lastItem() ?? 0,
                    'current_page' => $bookings->currentPage(),
                    'last_page'    => $bookings->lastPage(),
                    'links'        => $bookings->linkCollection()->toArray(),
                ],
            ],
            'statusCounts'   => $statusCounts,
            'kategoriGroups' => $kategoriGroups,
        ]);
    }

    /**
     * Tampilkan detail satu booking.
     * Hanya owner yang memiliki aset yang dipesan yang dapat mengaksesnya.
     */
    public function show(Request $request, $id)
    {
        $booking = booking::with([
            'asset:id,title,owner_profile_id,asset_type_id',
            'asset.type:id,name,category_id,rental_unit',
            'asset.type.category:id,name',
            'asset.firstImage:id,asset_id,image',
            'asset.ownerProfile:id,user_id',
            'user:id,name,email,phone',
            'assetUnit:id,name',
            'payment:id,booking_id,payment_status,payment_method,proof_of_payment,expires_at',
        ])->findOrFail($id);

        // Pastikan booking ini milik aset owner yang sedang login
        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $booking->asset?->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        return Inertia::render('owner/Booking/Show', [
            'booking' => [
                'id'           => $booking->id,
                'code'         => $booking->booking_code,
                'asset'        => $booking->asset_name ?? $booking->asset?->title,
                'unit'         => $booking->asset_unit_name,
                'kategori'     => $booking->asset?->type?->category?->name,
                'jenis'        => $booking->asset?->type?->name,
                'status'       => $booking->booking_status,
                'tenant'       => $booking->booker_name,
                'tenant_email' => $booking->booker_email,
                'tenant_phone' => $booking->booker_phone,
                'guest_name'   => $booking->guest_name,
                'start_date'   => Carbon::parse($booking->start_date)->translatedFormat('d F Y'),
                'end_date'     => Carbon::parse($booking->end_date)->translatedFormat('d F Y'),
                'subtotal'     => $booking->subtotal,
                'service_fee'  => $booking->service_fee,
                'total'        => $booking->total,
                'asset_image'  => $booking->asset?->firstImage?->image,
                'created_at'   => $booking->created_at->translatedFormat('d M Y, H:i'),
                'payment'      => $booking->payment ? [
                    'status'     => $booking->payment->payment_status,
                    'method'     => $booking->payment->payment_method,
                    'proof'      => $booking->payment->proof_of_payment,
                    'expires_at' => $booking->payment->expires_at,
                ] : null,
                'user' => [
                    'name'  => $booking->user?->name,
                    'email' => $booking->user?->email,
                    'phone' => $booking->user?->phone,
                ],
            ],
        ]);
    }

    /**
     * Owner mengkonfirmasi booking: pending → confirmed.
     */
    public function confirm(Request $request, $id)
    {
        $booking = $this->authorizeBooking($request, $id);

        if ($booking->booking_status !== 'pending') {
            return back()->withErrors(['booking' => 'Pesanan ini tidak dalam status menunggu konfirmasi.']);
        }

        $booking->update(['booking_status' => 'confirmed']);

        return redirect()->route('owner.bookings')
            ->with('success', 'Pesanan berhasil dikonfirmasi.');
    }

    /**
     * Owner menolak/membatalkan booking: pending → cancelled.
     */
    public function reject(Request $request, $id)
    {
        $booking = $this->authorizeBooking($request, $id);

        if ($booking->booking_status !== 'pending') {
            return back()->withErrors(['booking' => 'Pesanan ini tidak dapat ditolak.']);
        }

        $booking->update(['booking_status' => 'cancelled']);

        return redirect()->route('owner.bookings')
            ->with('success', 'Pesanan berhasil ditolak.');
    }

    /**
     * Owner menandai booking sebagai selesai: active → completed.
     * Opsi B: Langsung update/buat invoice bulan berjalan secara real-time.
     */
    public function complete(Request $request, $id)
    {
        $booking = $this->authorizeBooking($request, $id);

        if ($booking->booking_status !== 'active') {
            return back()->withErrors(['booking' => 'Pesanan ini belum dalam status aktif.']);
        }

        $booking->update(['booking_status' => 'completed']);

        // ─── Opsi B: Real-time invoice update ───────────────────────────
        $this->upsertMonthlyBilling($booking);
        // ────────────────────────────────────────────────────────────────

        return redirect()->route('owner.bookings')
            ->with('success', 'Pesanan telah ditandai selesai.');
    }

    /**
     * Buat atau update invoice bulan berjalan milik owner yang asetnya di-complete.
     * Dipanggil setiap kali booking berhasil di-complete (Opsi B).
     */
    private function upsertMonthlyBilling(booking $booking): void
    {
        // Ambil user_id owner dari aset
        $booking->loadMissing('asset.ownerProfile');
        $ownerUserId = $booking->asset?->ownerProfile?->user_id;
        if (!$ownerUserId) return;

        // Ambil tarif biaya layanan aktif
        $serviceFee = ServiceFee::orderByDesc('id')->first();
        if (!$serviceFee) return;

        $now   = Carbon::now();
        $year  = $now->year;
        $month = $now->month;

        // Jatuh tempo: tanggal 10 bulan berjalan (atau bulan depan jika sudah lewat)
        $dueDate = Carbon::create($year, $month, 10);
        if ($now->day > 10) {
            $dueDate = $dueDate->addMonth();
        }

        DB::transaction(function () use ($ownerUserId, $year, $month, $booking, $serviceFee, $dueDate) {
            // Cari invoice bulan ini, atau buat baru jika belum ada
            $billing = OwnerBilling::firstOrCreate(
                [
                    'owner_id'     => $ownerUserId,
                    'period_year'  => $year,
                    'period_month' => $month,
                ],
                [
                    'invoice_number'      => sprintf('INV/%d%02d/KSW/%04d', $year, $month, OwnerBilling::count() + 1),
                    'total_transactions'  => 0,
                    'fee_per_transaction' => $serviceFee->fee_value,
                    'total_amount'        => 0,
                    'status'              => 'unpaid',
                    'due_date'            => $dueDate,
                ]
            );

            // Jangan update jika sudah lunas atau sedang diverifikasi
            if (in_array($billing->status, ['paid', 'waiting_verification'])) return;

            // Hitung ulang dari database (akurat, anti race condition)
            $stats = booking::where('booking_status', 'completed')
                ->whereHas('asset', fn($q) => $q->whereHas(
                    'ownerProfile', fn($q2) => $q2->where('user_id', $ownerUserId)
                ))
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $month)
                ->selectRaw('COUNT(*) as total_trx, SUM(service_fee) as total_fee')
                ->first();

            $totalTrx = (int) ($stats->total_trx ?? 0);
            $totalAmount = $serviceFee->fee_type === 'fixed'
                ? $serviceFee->fee_value * $totalTrx
                : (float) ($stats->total_fee ?? 0);

            $billing->update([
                'total_transactions'  => $totalTrx,
                'fee_per_transaction' => $serviceFee->fee_value,
                'total_amount'        => $totalAmount,
            ]);
        });
    }

    /**
     * Helper: ambil booking dan pastikan hanya owner aset yang bisa mengaksesnya.
     */
    private function authorizeBooking(Request $request, $id): booking
    {
        $booking = booking::with('asset:id,owner_profile_id')->findOrFail($id);

        $ownerProfile = $request->user()->ownerProfile;
        if (!$ownerProfile || $booking->asset?->owner_profile_id !== $ownerProfile->id) {
            abort(403, 'Anda tidak berhak melakukan tindakan pada pesanan ini.');
        }

        return $booking;
    }

    /**
     * Owner memverifikasi pembayaran: payment verifying -> verified, booking confirmed/pending -> active.
     */
    public function verifyPayment(Request $request, $id)
    {
        $booking = $this->authorizeBooking($request, $id);

        if (!$booking->payment || $booking->payment->payment_status !== 'verifying') {
            return back()->withErrors(['booking' => 'Pesanan ini tidak memiliki pembayaran yang perlu diverifikasi.']);
        }

        $booking->update(['booking_status' => 'active']);
        $booking->payment->update(['payment_status' => 'paid']);

        return redirect()->route('owner.bookings')
            ->with('success', 'Pembayaran berhasil diverifikasi. Pesanan sekarang Aktif!');
    }
}
