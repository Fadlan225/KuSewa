<?php

namespace App\Http\Controllers;

use App\Models\payment;
use App\Models\PaymentMethod;
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

        $availableYears = payment::query()
            ->whereNotNull('payment_date')
            ->selectRaw('YEAR(payment_date) as year')
            ->distinct()
            ->pluck('year')
            ->map(fn ($item) => (int) $item)
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        return Inertia::render('admin/PaymentSystem', [
            'methods' => PaymentMethod::orderBy('sort_order')->orderBy('id')->get(),
            'year' => $year,
            'availableYears' => $availableYears,
            'monthlyRevenue' => $monthlyRevenue,
            'summary' => [
                'revenue' => payment::where('payment_status', 'paid')->whereYear('payment_date', $year)->with('booking:id,service_fee')->get()->sum(fn ($item) => $item->booking?->service_fee ?? 0),
                'paidTransactions' => payment::where('payment_status', 'paid')->whereYear('payment_date', $year)->count(),
                'pendingTransactions' => payment::where('payment_status', 'pending')->whereYear('payment_date', $year)->count(),
            ],
            'transactions' => $transactions,
        ]);
    }

    public function storeMethod(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'code' => 'required|string|max:50|alpha_dash|unique:payment_methods,code', 'description' => 'nullable|string|max:255']);
        $data['sort_order'] = (int) PaymentMethod::max('sort_order') + 1;
        PaymentMethod::create($data);
        return back()->with('success', 'Metode pembayaran ditambahkan.');
    }

    public function updateMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'code' => 'required|string|max:50|alpha_dash|unique:payment_methods,code,' . $paymentMethod->id, 'description' => 'nullable|string|max:255', 'is_active' => 'boolean']);
        $paymentMethod->update($data);
        return back()->with('success', 'Metode pembayaran diperbarui.');
    }

    public function destroyMethod(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return back()->with('success', 'Metode pembayaran dihapus.');
    }

    public function prioritizeMethods(Request $request)
    {
        foreach ($request->input('ids', []) as $index => $id) PaymentMethod::whereKey($id)->update(['sort_order' => $index]);
        return back()->with('success', 'Prioritas metode pembayaran diperbarui.');
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
