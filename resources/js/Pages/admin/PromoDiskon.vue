<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const promos = ref([
    { id: 1, code: 'RENT10', type: 'Diskon', value: '10%', validUntil: '31 Agu 2026', active: true },
    { id: 2, code: 'FREEPIC', type: 'Promo', value: 'Gratis Foto', validUntil: '15 Sep 2026', active: true },
    { id: 3, code: 'SUMMER50', type: 'Diskon', value: '50%', validUntil: '05 Okt 2026', active: false },
]);

// ==== Statistik: state & logic ====
const showStatsModal = ref(false);

const activeCount = computed(() => promos.value.filter(item => item.active).length);
const expiredCount = computed(() => promos.value.filter(item => !item.active).length);

const activeRate = computed(() => {
    if (promos.value.length === 0) return 0;
    return Math.round((activeCount.value / promos.value.length) * 100);
});

const byType = computed(() => {
    const map = {};
    for (const item of promos.value) {
        map[item.type] = (map[item.type] || 0) + 1;
    }
    return Object.entries(map)
        .map(([type, count]) => ({ type, count }))
        .sort((a, b) => b.count - a.count);
});

const byStatus = computed(() => [
    { label: 'Aktif', count: activeCount.value },
    { label: 'Tidak Aktif', count: expiredCount.value },
]);

function openStats() {
    showStatsModal.value = true;
}

function closeStats() {
    showStatsModal.value = false;
}
</script>

<template>
    <Head title="Promo & Diskon - Admin Panel" />

    <DashboardLayout role="Admin" title="Promo & Diskon" description="Pantau kupon, promo musiman, dan ketersediaan diskon.">
        <template #header-actions>
            <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Buat Promo Baru</button>
        </template>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Promo Aktif</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ activeCount }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Promo dan diskon yang sedang berjalan.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Total Kupon</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ promos.length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah kode promo yang terdaftar.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Expired</p>
                        <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ expiredCount }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Promo yang sudah tidak berlaku.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar Promo</h2>
                            <p class="text-[11px] text-slate-400">Kelola kode, validitas, dan status promo.</p>
                        </div>
                        <button
                            type="button"
                            @click="openStats"
                            class="text-[11px] font-semibold text-[#0A2540] hover:underline"
                        >
                            Lihat Statistik
                        </button>
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


        <!-- Modal Statistik -->
        <Teleport to="body">
            <div
                v-if="showStatsModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeStats"
            >
                <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Statistik Promo</h3>
                            <p class="text-[11px] text-slate-400">Ringkasan berdasarkan data promo saat ini.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeStats"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                        <!-- Ringkasan cepat -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">Total Kupon</p>
                                <p class="mt-1 text-xl font-extrabold text-slate-900">{{ promos.length }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">Tingkat Aktif</p>
                                <p class="mt-1 text-xl font-extrabold text-emerald-600">{{ activeRate }}%</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">Tipe Promo</p>
                                <p class="mt-1 text-xl font-extrabold text-slate-900">{{ byType.length }}</p>
                            </div>
                        </div>

                        <!-- Per Tipe -->
                        <div>
                            <p class="text-[11px] font-bold text-slate-500 uppercase mb-3">Berdasarkan Tipe</p>
                            <div class="space-y-2.5">
                                <div v-for="row in byType" :key="row.type" class="flex items-center gap-3">
                                    <span class="w-24 text-xs text-slate-600 shrink-0">{{ row.type }}</span>
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div
                                            class="h-full bg-[#0A2540] rounded-full"
                                            :style="{ width: (row.count / promos.length * 100) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="w-6 text-right text-xs font-semibold text-slate-700">{{ row.count }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Per Status -->
                        <div>
                            <p class="text-[11px] font-bold text-slate-500 uppercase mb-3">Berdasarkan Status</p>
                            <div class="space-y-2.5">
                                <div v-for="row in byStatus" :key="row.label" class="flex items-center gap-3">
                                    <span class="w-24 text-xs text-slate-600 shrink-0">{{ row.label }}</span>
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div
                                            :class="row.label === 'Aktif' ? 'bg-emerald-500' : 'bg-rose-500'"
                                            class="h-full rounded-full"
                                            :style="{ width: (row.count / promos.length * 100) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="w-6 text-right text-xs font-semibold text-slate-700">{{ row.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                        <button
                            type="button"
                            @click="closeStats"
                            class="rounded-2xl bg-[#0A2540] px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </DashboardLayout>
</template>
