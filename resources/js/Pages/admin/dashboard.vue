<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const props = defineProps({
    stats: Object,
    recentBookings: Array,
    user: Object,
});

const user = computed(() => page.props.auth?.user || props.user || {});
</script>

<template>
    <Head title="Admin Dashboard - kusewa.id" />

    <div class="min-h-screen bg-slate-100 text-slate-900 px-4 py-6 lg:px-8">
        <div class="max-w-[1360px] mx-auto space-y-6">
            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Dashboard Admin</h1>
                        <p class="text-sm text-slate-500 mt-1">Ringkasan sistem untuk tim admin.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Pengguna</p>
                            <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ stats.total_users }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Pemilik</p>
                            <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ stats.total_owners }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Aset</p>
                            <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ stats.total_assets }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Pendapatan</p>
                            <p class="mt-3 text-2xl font-extrabold text-slate-900">Rp {{ stats.total_revenue.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Statistik Penting</h2>
                            <p class="text-sm text-slate-500 mt-1">Monitoring cepat untuk status sistem.</p>
                        </div>
                        <Link href="/" class="text-sm font-semibold text-[#FFC000] hover:text-[#e6ad00]">Lihat situs</Link>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl bg-[#0A2540] p-5 text-white">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Owner Verifikasi Pending</p>
                            <p class="mt-4 text-3xl font-bold">{{ stats.pending_owner_verifications }}</p>
                        </div>
                        <div class="rounded-3xl bg-[#FFC000]/10 p-5 text-[#0A2540]">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pemesanan Pending</p>
                            <p class="mt-4 text-3xl font-bold">{{ stats.pending_bookings }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-bold text-slate-900">Aksi Cepat</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <p>• Kelola pengguna, verifikasi owner, dan periksa aset.</p>
                        <p>• Gunakan rute admin di URL <span class="font-semibold">/admin/dashboard</span>.</p>
                        <p>• Pastikan user memiliki <span class="font-semibold">role = admin</span>.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Transaksi Terbaru</h2>
                        <p class="text-sm text-slate-500 mt-1">5 pemesanan terbaru dari sistem.</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-600">Admin: {{ user.name }}</span>
                </div>

                <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-[0.18em] text-xs">
                            <tr>
                                <th class="px-4 py-4">Kode</th>
                                <th class="px-4 py-4">Aset</th>
                                <th class="px-4 py-4">Status</th>
                                <th class="px-4 py-4">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <tr v-for="booking in props.recentBookings" :key="booking.code">
                                <td class="px-4 py-4 font-semibold text-slate-900">{{ booking.code }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ booking.asset }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ booking.status }}</td>
                                <td class="px-4 py-4 font-semibold text-slate-900">Rp {{ booking.total.toLocaleString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>
