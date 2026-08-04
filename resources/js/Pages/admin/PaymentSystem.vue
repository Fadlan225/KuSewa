<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const paymentMethods = ref([
    { id: 1, name: 'Transfer Bank', active: true, description: 'Transfer manual dari semua bank BUMN/BUMS.' },
    { id: 2, name: 'Virtual Account', active: true, description: 'Bayar dengan kode VA otomatis.' },
    { id: 3, name: 'QRIS', active: false, description: 'Pembayaran via QRIS dari e-wallet.' },
]);

const transactions = ref([
    { id: 1, label: 'Order #A321', amount: 'Rp 1.250.000', date: '2 Ags 2026', method: 'VA', status: 'Berhasil' },
    { id: 2, label: 'Order #B872', amount: 'Rp 675.000', date: '1 Ags 2026', method: 'Transfer Bank', status: 'Menunggu' },
    { id: 3, label: 'Order #C103', amount: 'Rp 2.100.000', date: '31 Jul 2026', method: 'QRIS', status: 'Gagal' },
]);
</script>

<template>
    <Head title="Sistem Pembayaran - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Sistem Pembayaran</h1>
                        <p class="text-xs text-slate-400">Kontrol metode pembayaran dan tinjau transaksi terakhir.</p>
                    </div>
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Tambah Metode</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Metode Aktif</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ paymentMethods.filter(method => method.active).length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah metode pembayaran aktif.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Transaksi Terbaru</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ transactions.length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Tampilan ringkas aktivitas terakhir.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Potensi Pendapatan</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">Rp 4.025.000</p>
                        <p class="text-[11px] text-slate-500 mt-2">Estimasi dari transaksi terbaru.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Metode Pembayaran</h2>
                            <p class="text-[11px] text-slate-400">Kelola status aktif dan konfigurasi setiap metode.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Atur Prioritas</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Metode</th>
                                    <th class="py-4 px-4">Deskripsi</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="method in paymentMethods" :key="method.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ method.name }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ method.description }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            method.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ method.active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Edit</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Transaksi Terbaru</h2>
                            <p class="text-[11px] text-slate-400">Daftar ringkas transaksi pembayaran terakhir.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Lihat Semua</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Transaksi</th>
                                    <th class="py-4 px-4">Jumlah</th>
                                    <th class="py-4 px-4">Metode</th>
                                    <th class="py-4 px-4">Tanggal</th>
                                    <th class="py-4 px-5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in transactions" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.label }}</td>
                                    <td class="py-4 px-4 text-slate-700">{{ item.amount }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.method }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.date }}</td>
                                    <td class="py-4 px-5">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'Berhasil' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : item.status === 'Menunggu' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
