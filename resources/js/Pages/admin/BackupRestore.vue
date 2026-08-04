<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const backups = ref([
    { id: 1, name: 'Backup 04 Agu 2026', size: '520 MB', created: '04 Agu 2026 08:30', status: 'Berhasil' },
    { id: 2, name: 'Backup 02 Agu 2026', size: '498 MB', created: '02 Agu 2026 23:10', status: 'Berhasil' },
    { id: 3, name: 'Backup 30 Jul 2026', size: '472 MB', created: '30 Jul 2026 22:00', status: 'Gagal' },
]);

const databaseStatus = ref({ size: '42.8 GB', tables: 64, lastOptimized: '03 Agu 2026' });
</script>

<template>
    <Head title="Backup & Restore Data - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Backup & Restore Data</h1>
                        <p class="text-xs text-slate-400">Kelola cadangan database dan restore sistem saat dibutuhkan.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Backup Sekarang</button>
                    <button class="rounded-2xl bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">Restore</button>
                </div>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Ukuran Database</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ databaseStatus.size }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Total penggunaan database saat ini.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Jumlah Tabel</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ databaseStatus.tables }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah tabel database yang tercatat.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Terakhir Dioptimalkan</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ databaseStatus.lastOptimized }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Waktu optimasi database terakhir.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Riwayat Backup</h2>
                            <p class="text-[11px] text-slate-400">Daftar backup terakhir dan status hasilnya.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Lihat Semua</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Nama Backup</th>
                                    <th class="py-4 px-4">Ukuran</th>
                                    <th class="py-4 px-4">Tanggal</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in backups" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.name }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.size }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.created }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'Berhasil' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Unduh</button>
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
