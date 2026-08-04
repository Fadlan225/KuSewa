<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const reports = ref([
    { id: 1, user: 'Siti Aminah', type: 'Rating Rendah', asset: 'Kos Mawar', date: '03 Agu 2026', status: 'Diproses' },
    { id: 2, user: 'Andi Wijaya', type: 'Komentar Spam', asset: 'Apartemen Sakura', date: '02 Agu 2026', status: 'Selesai' },
    { id: 3, user: 'Rian Pratama', type: 'Review Negatif', asset: 'Ruko Elok', date: '01 Agu 2026', status: 'Diproses' },
]);

const summary = ref({ total: 128, unresolved: 12, resolved: 116 });
</script>

<template>
    <Head title="Laporan Pengguna & Rating - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-flag"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Laporan Pengguna & Rating</h1>
                        <p class="text-xs text-slate-400">Pantau laporan pengguna dan kualitas rating properti.</p>
                    </div>
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Ekspor Laporan</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Total Laporan</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ summary.total }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Semua laporan pengguna yang masuk.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Belum Selesai</p>
                        <p class="mt-3 text-3xl font-extrabold text-amber-600">{{ summary.unresolved }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Laporan yang masih ditindaklanjuti.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Selesai</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ summary.resolved }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Laporan yang sudah ditutup.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar Laporan</h2>
                            <p class="text-[11px] text-slate-400">Lihat laporan, rating, dan status tindak lanjut.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Filter Laporan</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Pengguna</th>
                                    <th class="py-4 px-4">Tipe Laporan</th>
                                    <th class="py-4 px-4">Properti</th>
                                    <th class="py-4 px-4">Tanggal</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in reports" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.user }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.type }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.asset }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.date }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'Diproses' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                        ]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Tinjau</button>
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
