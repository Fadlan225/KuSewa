<?php

namespace App\Http\Controllers;

use App\Models\payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPaymentSystemController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->integer('year', now()->year);

        $monthlyRevenue = payment::query()
            ->where('payment_status', 'paid')
            ->whereYear('payment_date', $year)
            ->with('booking:id,booking_code,service_fee,total')
            ->get()
            ->groupBy(fn ($item) => Carbon::parse($item->payment_date)->month)
            ->map(fn ($items, $month) => [
                'month' => (int) $month,
                'label' => Carbon::create($year, $month, 1)->translatedFormat('F'),
                'revenue' => $items->sum(fn ($item) => $item->booking?->service_fee ?? 0),
                'transactions' => $items->count(),
            ])->values();

        $transactions = payment::with('booking:id,booking_code,service_fee,total')
            ->latest('payment_date')->latest('id')->paginate(10)->withQueryString();
        $transactions->through(fn ($item) => [
                'id' => $item->id,
                'label' => $item->booking?->booking_code ?? 'Pembayaran',
                'amount' => (float) ($item->booking?->total ?? 0),
                'service_fee' => (float) ($item->booking?->service_fee ?? 0),
                'date' => $item->payment_date,
                'method' => $item->payment_method,
                'status' => $item->payment_status,
                'proof' => $item->proof_of_payment ? asset('storage/' . $item->proof_of_payment) : null,
            ]);

        return Inertia::render('admin/PaymentSystem', [
            'year' => $year,
            'availableYears' => payment::query()->selectRaw('YEAR(payment_date) year')->distinct()->orderByDesc('year')->pluck('year')->values(),
            'monthlyRevenue' => $monthlyRevenue,
            'summary' => [
                'revenue' => payment::where('payment_status', 'paid')->whereYear('payment_date', $year)->with('booking:id,service_fee')->get()->sum(fn ($item) => $item->booking?->service_fee ?? 0),
                'paidTransactions' => payment::where('payment_status', 'paid')->whereYear('payment_date', $year)->count(),
                'pendingTransactions' => payment::where('payment_status', 'pending')->whereYear('payment_date', $year)->count(),
            ],
            'transactions' => $transactions,
        ]);
    }

    public function approve(payment $payment)
    {
        $payment->update(['payment_status' => 'paid', 'payment_date' => now()->toDateString()]);
        return back()->with('success', 'Pembayaran dan bukti transaksi telah disetujui.');
    }

    public function reject(payment $payment)
    {
        $payment->update(['payment_status' => 'failed']);
        return back()->with('success', 'Pembayaran ditolak.');
    }
}
