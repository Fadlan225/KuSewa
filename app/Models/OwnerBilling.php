<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerBilling extends Model
{
    protected $fillable = [
        'owner_id',
        'invoice_number',
        'period_year',
        'period_month',
        'total_transactions',
        'fee_per_transaction',
        'total_amount',
        'status',
        'due_date',
        'payment_method',
        'payment_proof',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'due_date'  => 'date',
        'paid_at'   => 'datetime',
        'fee_per_transaction' => 'decimal:2',
        'total_amount'        => 'decimal:2',
    ];

    /**
     * Relasi ke User (owner).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Accessor: label periode, e.g. "Agustus 2026"
     */
    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($months[$this->period_month] ?? '-') . ' ' . $this->period_year;
    }

    /**
     * Accessor: due_date dalam format Indonesia, e.g. "10 Agustus 2026"
     */
    public function getDueDateLabelAttribute(): string
    {
        if (!$this->due_date) return '-';
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $this->due_date->day . ' '
            . ($months[$this->due_date->month] ?? '-') . ' '
            . $this->due_date->year;
    }

    /**
     * Accessor: alias invoice_number → invoiceId (untuk frontend)
     */
    public function getInvoiceIdAttribute(): string
    {
        return $this->invoice_number;
    }

    /**
     * Accessor: label status dalam Bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'unpaid'               => 'Belum Dibayar',
            'waiting_verification' => 'Menunggu Verifikasi',
            'paid'                 => 'Lunas',
            'rejected'             => 'Ditolak',
            'overdue'              => 'Terlambat',
            default                => ucfirst($this->status),
        };
    }

    /**
     * Scope: tagihan aktif (belum lunas)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['unpaid', 'waiting_verification', 'overdue']);
    }

    /**
     * Scope: riwayat tagihan (sudah selesai)
     */
    public function scopeHistory($query)
    {
        return $query->whereIn('status', ['paid', 'rejected']);
    }
}
