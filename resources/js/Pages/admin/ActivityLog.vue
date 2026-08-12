<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const logs = ref([
    { id: 1, time: '04 Agu 2026 09:45', action: 'Login Admin', user: 'Super Admin', detail: 'Masuk ke dashboard admin.' },
    { id: 2, time: '04 Agu 2026 08:30', action: 'Terbitkan Promo', user: 'Marketing Admin', detail: 'Menambahkan promo diskon 10%.' },
    { id: 3, time: '03 Agu 2026 21:12', action: 'Verifikasi Aset', user: 'Moderator', detail: 'Aset Kost Nyaman 2Kamar disetujui.' },
]);

const monthMap = {
    Jan: 0, Feb: 1, Mar: 2, Apr: 3, Mei: 4, Jun: 5,
    Jul: 6, Agu: 7, Sep: 8, Okt: 9, Nov: 10, Des: 11,
};

function parseLogTime(text) {
    // Format: "04 Agu 2026 09:45"
    const match = text.match(/^(\d{1,2}) (\w{3}) (\d{4}) (\d{1,2}):(\d{2})$/);
    if (!match) return null;
    const [, day, monthStr, year, hour, minute] = match;
    const month = monthMap[monthStr];
    if (month === undefined) return null;
    return new Date(Number(year), month, Number(day), Number(hour), Number(minute));
}

// ==== Filter ====
const showFilterPanel = ref(false);
const searchQuery = ref('');
const actionFilter = ref('Semua');
const adminFilter = ref('Semua');

const actionOptions = computed(() => ['Semua', ...new Set(logs.value.map(item => item.action))]);
const adminOptions = computed(() => ['Semua', ...new Set(logs.value.map(item => item.user))]);

const filteredLogs = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return logs.value.filter(item => {
        const matchesQuery = [item.action, item.user, item.detail].join(' ').toLowerCase().includes(query);
        const matchesAction = actionFilter.value === 'Semua' || item.action === actionFilter.value;
        const matchesAdmin = adminFilter.value === 'Semua' || item.user === adminFilter.value;
        return matchesQuery && matchesAction && matchesAdmin;
    });
});

function toggleFilterPanel() {
    showFilterPanel.value = !showFilterPanel.value;
}

function resetFilters() {
    searchQuery.value = '';
    actionFilter.value = 'Semua';
    adminFilter.value = 'Semua';
}

const activeFilterCount = computed(() => {
    let count = 0;
    if (actionFilter.value !== 'Semua') count++;
    if (adminFilter.value !== 'Semua') count++;
    if (searchQuery.value.trim()) count++;
    return count;
});

// ==== Ekspor Log (CSV) ====
function exportLogs() {
    const rows = [
        ['Waktu', 'Aksi', 'Admin', 'Detail'],
        ...filteredLogs.value.map(item => [item.time, item.action, item.user, item.detail]),
    ];

    const csvContent = rows
        .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
        .join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `log-aktivitas-admin-${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// ==== Bersihkan Log Lama ====
const showCleanupModal = ref(false);
const cleanupDays = ref(30);
const cleanupOptions = [7, 30, 90];

const logsToRemoveCount = computed(() => {
    const cutoff = new Date();
    cutoff.setDate(cutoff.getDate() - cleanupDays.value);
    return logs.value.filter(item => {
        const time = parseLogTime(item.time);
        return time && time < cutoff;
    }).length;
});

function openCleanup() {
    cleanupDays.value = 30;
    showCleanupModal.value = true;
}

function closeCleanup() {
    showCleanupModal.value = false;
}

function confirmCleanup() {
    const cutoff = new Date();
    cutoff.setDate(cutoff.getDate() - cleanupDays.value);
    logs.value = logs.value.filter(item => {
        const time = parseLogTime(item.time);
        return !time || time >= cutoff;
    });
    showCleanupModal.value = false;
}
</script>

<template>
    <Head title="Log Aktivitas Admin - Admin Panel" />

    <DashboardLayout role="Admin" title="Log Aktivitas Admin" description="Catat semua aksi penting yang dilakukan tim admin.">
        <template #header-actions>
            <button
                type="button"
                @click="exportLogs"
                class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition"
            >
                Ekspor Log
            </button>
        </template>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Ringkasan Log</h2>
                            <p class="text-[11px] text-slate-400">Lihat riwayat kegiatan admin terbaru dan filter laporan.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="toggleFilterPanel"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition flex items-center gap-1.5"
                            >
                                Filter
                                <span v-if="activeFilterCount > 0" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-[#0A2540] text-white text-[9px] font-bold">
                                    {{ activeFilterCount }}
                                </span>
                            </button>
                            <button
                                type="button"
                                @click="openCleanup"
                                class="rounded-2xl bg-slate-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-slate-800 transition"
                            >
                                Bersihkan Log Lama
                            </button>
                        </div>
                    </div>

                    <!-- Panel Filter -->
                    <div v-if="showFilterPanel" class="rounded-2xl bg-slate-50/60 border border-slate-100 px-4 py-4 mb-5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="flex items-center gap-3 bg-white px-3.5 py-2 rounded-xl border border-slate-200/80">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                                <input
                                    type="text"
                                    v-model="searchQuery"
                                    placeholder="Cari aksi, admin, atau detail..."
                                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                                />
                            </div>
                            <select
                                v-model="actionFilter"
                                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                            >
                                <option v-for="action in actionOptions" :key="action">{{ action }}</option>
                            </select>
                            <select
                                v-model="adminFilter"
                                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                            >
                                <option v-for="admin in adminOptions" :key="admin">{{ admin }}</option>
                            </select>
                        </div>
                        <div class="flex justify-between items-center mt-3">
                            <p class="text-[11px] text-slate-400">{{ filteredLogs.length }} dari {{ logs.length }} log ditampilkan.</p>
                            <button
                                type="button"
                                @click="resetFilters"
                                class="text-[11px] font-semibold text-slate-500 hover:text-slate-800"
                            >
                                Reset filter
                            </button>
                        </div>
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
                                <tr v-for="item in filteredLogs" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 text-slate-600">{{ item.time }}</td>
                                    <td class="py-4 px-4 font-semibold text-slate-900">{{ item.action }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.user }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.detail }}</td>
                                </tr>
                                <tr v-if="filteredLogs.length === 0">
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        Tidak ada log sesuai pencarian atau filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        <!-- Modal Bersihkan Log Lama -->
        <Teleport to="body">
            <div
                v-if="showCleanupModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeCleanup"
            >
                <div class="w-full max-w-sm rounded-3xl bg-white shadow-xl border border-slate-100 p-6">
                    <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-4">
                        <i class="fa-solid fa-broom"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Bersihkan log lama</h3>
                    <p class="text-xs text-slate-500 mt-1.5">
                        Pilih ambang waktu. Log yang lebih lama dari itu akan dihapus permanen.
                    </p>

                    <div class="mt-4">
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Hapus log lebih lama dari</label>
                        <select
                            v-model.number="cleanupDays"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                        >
                            <option v-for="days in cleanupOptions" :key="days" :value="days">{{ days }} hari</option>
                        </select>
                    </div>

                    <p class="text-[11px] text-slate-500 mt-3">
                        <span class="font-bold text-slate-900">{{ logsToRemoveCount }}</span> log akan dihapus.
                    </p>

                    <div class="flex justify-end gap-2 mt-6">
                        <button
                            type="button"
                            @click="closeCleanup"
                            class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="confirmCleanup"
                            :disabled="logsToRemoveCount === 0"
                            class="rounded-2xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Hapus {{ logsToRemoveCount }} Log
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </DashboardLayout>
</template>
