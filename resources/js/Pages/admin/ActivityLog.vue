<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const logs = ref([
    { id: 1, time: '04 Agu 2026 09:45', action: 'Login Admin', user: 'Super Admin', detail: 'Masuk ke dashboard admin.' },
    { id: 2, time: '04 Agu 2026 08:30', action: 'Terbitkan Promo', user: 'Marketing Admin', detail: 'Menambahkan promo diskon 10%.' },
    { id: 3, time: '03 Agu 2026 21:12', action: 'Verifikasi Aset', user: 'Moderator', detail: 'Aset Kost Nyaman 2Kamar disetujui.' },
]);
</script>

<template>
    <Head title="Log Aktivitas Admin - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Log Aktivitas Admin</h1>
                        <p class="text-xs text-slate-400">Catat semua aksi penting yang dilakukan tim admin.</p>
                    </div>
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Ekspor Log</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Ringkasan Log</h2>
                            <p class="text-[11px] text-slate-400">Lihat riwayat kegiatan admin terbaru dan filter laporan.</p>
                        </div>
                        <button class="rounded-2xl bg-slate-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-slate-800 transition">Bersihkan Log Lama</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Waktu</th>
                                    <th class="py-4 px-4">Aksi</th>
                                    <th class="py-4 px-4">Admin</th>
                                    <th class="py-4 px-4">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in logs" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 text-slate-600">{{ item.time }}</td>
                                    <td class="py-4 px-4 font-semibold text-slate-900">{{ item.action }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.user }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.detail }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
