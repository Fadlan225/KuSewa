<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const promos = ref([
    { id: 1, code: 'RENT10', type: 'Diskon', value: '10%', validUntil: '31 Agu 2026', active: true },
    { id: 2, code: 'FREEPIC', type: 'Promo', value: 'Gratis Foto', validUntil: '15 Sep 2026', active: true },
    { id: 3, code: 'SUMMER50', type: 'Diskon', value: '50%', validUntil: '05 Okt 2026', active: false },
]);
</script>

<template>
    <Head title="Promo & Diskon - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-ticket" />
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Promo & Diskon</h1>
                        <p class="text-xs text-slate-400">Pantau kupon, promo musiman, dan ketersediaan diskon.</p>
                    </div>
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Buat Promo Baru</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Promo Aktif</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ promos.filter(item => item.active).length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Promo dan diskon yang sedang berjalan.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Total Kupon</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ promos.length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah kode promo yang terdaftar.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Expired</p>
                        <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ promos.filter(item => !item.active).length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Promo yang sudah tidak berlaku.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar Promo</h2>
                            <p class="text-[11px] text-slate-400">Kelola kode, validitas, dan status promo.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Lihat Statistik</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Kode</th>
                                    <th class="py-4 px-4">Tipe</th>
                                    <th class="py-4 px-4">Nilai</th>
                                    <th class="py-4 px-4">Berlaku Hingga</th>
                                    <th class="py-4 px-5">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in promos" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.code }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.type }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.value }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.validUntil }}</td>
                                    <td class="py-4 px-5">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ item.active ? 'Aktif' : 'Tidak Aktif' }}
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
