<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const notifications = ref([
    { id: 1, title: 'Server Maintenance', type: 'Sistem', created: '04 Agu 2026', status: 'Dikirim' },
    { id: 2, title: 'Update Kebijakan', type: 'CMS', created: '02 Agu 2026', status: 'Dijadwalkan' },
    { id: 3, title: 'Promo Baru', type: 'Marketing', created: '30 Jul 2026', status: 'Dikirim' },
]);
</script>

<template>
    <Head title="Notifikasi Sistem - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-regular fa-bell"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Notifikasi Sistem</h1>
                        <p class="text-xs text-slate-400">Kelola pesan sistem dan jadwal notifikasi ke pengguna.</p>
                    </div>
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Buat Notifikasi</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Terkirim</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ notifications.filter(item => item.status === 'Dikirim').length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Notifikasi yang sudah dikirim.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Dijadwalkan</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ notifications.filter(item => item.status === 'Dijadwalkan').length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Notifikasi yang akan dikirim.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Jenis Notifikasi</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ new Set(notifications.map(item => item.type)).size }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Kategori pesan sistem yang tersedia.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar Notifikasi</h2>
                            <p class="text-[11px] text-slate-400">Tampilkan jadwal, tipe, dan status pengiriman.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Lihat Semua</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Judul</th>
                                    <th class="py-4 px-4">Tipe</th>
                                    <th class="py-4 px-4">Tanggal</th>
                                    <th class="py-4 px-5">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in notifications" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.title }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.type }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.created }}</td>
                                    <td class="py-4 px-5">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'Dikirim' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'
                                        ]">
                                            {{ item.status }}
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
            </div>
        </main>
    </div>
</template>
