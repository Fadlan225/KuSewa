<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Download, Search, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const reports = ref([
    { id: 1, user: 'Siti Aminah', type: 'Rating Rendah', asset: 'Kos Mawar', date: '03 Agu 2026', status: 'Diproses', note: '', sanction: null },
    { id: 2, user: 'Andi Wijaya', type: 'Komentar Spam', asset: 'Apartemen Sakura', date: '02 Agu 2026', status: 'Selesai', note: 'Komentar sudah dihapus oleh moderator.', sanction: null },
    { id: 3, user: 'Rian Pratama', type: 'Review Negatif', asset: 'Ruko Elok', date: '01 Agu 2026', status: 'Diproses', note: '', sanction: null },
]);

const reportTypes = ['Rating Rendah', 'Komentar Spam', 'Review Negatif', 'Penipuan', 'Konten Tidak Pantas'];
const reportStatuses = ['Diproses', 'Selesai'];

const sanctionTypes = ['Peringatan', 'Suspend Sementara', 'Blokir Permanen'];
const sanctionMeta = {
    'Peringatan': { badge: 'bg-amber-50 text-amber-700 border border-amber-200', icon: 'fa-triangle-exclamation' },
    'Suspend Sementara': { badge: 'bg-orange-50 text-orange-700 border border-orange-200', icon: 'fa-ban' },
    'Blokir Permanen': { badge: 'bg-rose-50 text-rose-700 border border-rose-200', icon: 'fa-user-slash' },
};

let nextSanctionId = 1;
const sanctionLog = ref([]);

// ==== Ringkasan (dihitung otomatis dari data) ====
const totalReports = computed(() => reports.value.length);
const unresolvedCount = computed(() => reports.value.filter(item => item.status === 'Diproses').length);
const resolvedCount = computed(() => reports.value.filter(item => item.status === 'Selesai').length);
const sanctionedCount = computed(() => reports.value.filter(item => item.sanction).length);

// ==== Filter Laporan ====
const showFilterPanel = ref(false);
const searchQuery = ref('');
const typeFilter = ref('Semua');
const statusFilter = ref('Semua');

const filteredReports = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return reports.value.filter(item => {
        const matchesQuery = [item.user, item.type, item.asset].join(' ').toLowerCase().includes(query);
        const matchesType = typeFilter.value === 'Semua' || item.type === typeFilter.value;
        const matchesStatus = statusFilter.value === 'Semua' || item.status === statusFilter.value;
        return matchesQuery && matchesType && matchesStatus;
    });
});

function toggleFilterPanel() {
    showFilterPanel.value = !showFilterPanel.value;
}

function resetFilters() {
    searchQuery.value = '';
    typeFilter.value = 'Semua';
    statusFilter.value = 'Semua';
}

const activeFilterCount = computed(() => {
    let count = 0;
    if (typeFilter.value !== 'Semua') count++;
    if (statusFilter.value !== 'Semua') count++;
    if (searchQuery.value.trim()) count++;
    return count;
});

// ==== Tinjau (detail + tindak lanjut + sanksi) ====
const showReviewModal = ref(false);
const reviewingItem = ref(null);
const reviewStatus = ref('Diproses');
const reviewNote = ref('');

const applySanction = ref(false);
const sanctionType = ref(sanctionTypes[0]);
const suspendDays = ref(3);
const sanctionReason = ref('');
const sanctionError = ref('');

function openReview(item) {
    reviewingItem.value = item;
    reviewStatus.value = item.status;
    reviewNote.value = item.note;
    applySanction.value = false;
    sanctionType.value = sanctionTypes[0];
    suspendDays.value = 3;
    sanctionReason.value = '';
    sanctionError.value = '';
    showReviewModal.value = true;
}

function closeReview() {
    showReviewModal.value = false;
    reviewingItem.value = null;
}

function saveReview() {
    if (!reviewingItem.value) return;

    if (applySanction.value) {
        if (!sanctionReason.value.trim()) {
            sanctionError.value = 'Alasan sanksi wajib diisi.';
            return;
        }

        const entry = {
            id: nextSanctionId++,
            reportId: reviewingItem.value.id,
            user: reviewingItem.value.user,
            type: sanctionType.value,
            reason: sanctionReason.value.trim(),
            duration: sanctionType.value === 'Suspend Sementara' ? `${suspendDays.value} hari` : null,
            appliedAt: new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }),
        };
        sanctionLog.value.unshift(entry);
        reviewingItem.value.sanction = sanctionType.value;
        reviewStatus.value = 'Selesai';
    }

    reviewingItem.value.status = reviewStatus.value;
    reviewingItem.value.note = reviewNote.value.trim();
    closeReview();
}

function revokeSanction(item) {
    item.sanction = null;
}

// ==== Riwayat Sanksi ====
const showSanctionLog = ref(false);

function openSanctionLog() {
    showSanctionLog.value = true;
}

function closeSanctionLog() {
    showSanctionLog.value = false;
}

// ==== Ekspor Laporan (CSV) ====
function exportReports() {
    const rows = [
        ['Pengguna', 'Tipe Laporan', 'Properti', 'Tanggal', 'Status', 'Sanksi', 'Catatan'],
        ...filteredReports.value.map(item => [item.user, item.type, item.asset, item.date, item.status, item.sanction || '-', item.note || '']),
    ];

    const csvContent = rows
        .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
        .join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `laporan-pengguna-${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Head title="Laporan Pengguna & Rating - Admin Panel" />

    <DashboardLayout role="Admin" title="Laporan Pengguna & Rating" description="Pantau laporan pengguna dan kualitas rating properti.">
        <template #header-actions>
            <button
                type="button"
                @click="exportReports"
                class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition flex items-center gap-2"
            >
                <Download class="text-[#FFC000]" />
                Export CSV
            </button>
            <button
                type="button"
                @click="openSanctionLog"
                class="rounded-2xl bg-white border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
            >
                Riwayat Sanksi
            </button>
        </template>

        <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto pb-32">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Total Laporan</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ totalReports }}</p>
                    <p class="text-[11px] text-slate-500 mt-2">Semua laporan pengguna yang masuk.</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Belum Selesai</p>
                    <p class="mt-3 text-3xl font-extrabold text-amber-600">{{ unresolvedCount }}</p>
                    <p class="text-[11px] text-slate-500 mt-2">Laporan yang masih ditindaklanjuti.</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Selesai</p>
                    <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ resolvedCount }}</p>
                    <p class="text-[11px] text-slate-500 mt-2">Laporan yang sudah ditutup.</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Sanksi Diberikan</p>
                    <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ sanctionedCount }}</p>
                    <p class="text-[11px] text-slate-500 mt-2">Pengguna yang sudah dikenai tindakan.</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Daftar Laporan</h2>
                        <p class="text-[11px] text-slate-400">Lihat laporan, rating, dan status tindak lanjut.</p>
                    </div>
                    <button
                        type="button"
                        @click="toggleFilterPanel"
                        class="text-[11px] font-semibold text-[#0A2540] hover:underline flex items-center gap-1.5"
                    >
                        Filter Laporan
                        <span v-if="activeFilterCount > 0" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-[#0A2540] text-white text-[9px] font-bold">
                            {{ activeFilterCount }}
                        </span>
                    </button>
                </div>

                <!-- Panel Filter -->
                <div v-if="showFilterPanel" class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="flex items-center gap-3 bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 sm:col-span-1">
                            <Search class="text-slate-400 text-xs" />
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Cari pengguna atau properti..."
                                class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                            />
                        </div>
                        <select
                            v-model="typeFilter"
                            class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                        >
                            <option>Semua</option>
                            <option v-for="type in reportTypes" :key="type">{{ type }}</option>
                        </select>
                        <select
                            v-model="statusFilter"
                            class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                        >
                            <option>Semua</option>
                            <option v-for="status in reportStatuses" :key="status">{{ status }}</option>
                        </select>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <p class="text-[11px] text-slate-400">{{ filteredReports.length }} dari {{ totalReports }} laporan ditampilkan.</p>
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
                                <th class="py-4 px-5">Pengguna</th>
                                <th class="py-4 px-4">Tipe Laporan</th>
                                <th class="py-4 px-4">Properti</th>
                                <th class="py-4 px-4">Tanggal</th>
                                <th class="py-4 px-4">Status</th>
                                <th class="py-4 px-4">Sanksi</th>
                                <th class="py-4 px-5">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in filteredReports" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
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
                                <td class="py-4 px-4">
                                    <span
                                        v-if="item.sanction"
                                        :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold', sanctionMeta[item.sanction].badge]"
                                    >
                                        <AppIcon :iconClass="['fa-solid', sanctionMeta[item.sanction].icon, 'text-[9px]']" />
                                        {{ item.sanction }}
                                    </span>
                                    <span v-else class="text-slate-300 text-[11px]">-</span>
                                </td>
                                <td class="py-4 px-5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            @click="openReview(item)"
                                            class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition"
                                        >
                                            Tinjau
                                        </button>
                                        <button
                                            v-if="item.sanction"
                                            type="button"
                                            @click="revokeSanction(item)"
                                            class="rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-500 hover:bg-slate-50 transition"
                                        >
                                            Cabut Sanksi
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredReports.length === 0">
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Tidak ada laporan sesuai pencarian atau filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tinjau Laporan -->
        <Teleport to="body">
            <div
                v-if="showReviewModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4 py-8"
                @click.self="closeReview"
            >
                <div class="w-full max-w-md rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden flex flex-col max-h-full">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Tinjau Laporan</h3>
                            <p class="text-[11px] text-slate-400">Periksa detail dan tentukan tindak lanjut.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeReview"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <X class="text-sm" />
                        </button>
                    </div>

                    <div v-if="reviewingItem" class="p-6 space-y-4 overflow-y-auto">
                        <div class="rounded-2xl bg-slate-50 p-4 space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Pengguna</span>
                                <span class="font-semibold text-slate-900">{{ reviewingItem.user }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Tipe laporan</span>
                                <span class="font-semibold text-slate-900">{{ reviewingItem.type }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Properti</span>
                                <span class="font-semibold text-slate-900">{{ reviewingItem.asset }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Tanggal</span>
                                <span class="font-semibold text-slate-900">{{ reviewingItem.date }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Status laporan</label>
                            <select
                                v-model="reviewStatus"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                            >
                                <option v-for="status in reportStatuses" :key="status">{{ status }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Catatan tindak lanjut</label>
                            <textarea
                                v-model="reviewNote"
                                rows="2"
                                placeholder="Tulis tindakan yang sudah atau akan dilakukan..."
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20 resize-none"
                            ></textarea>
                        </div>

                        <!-- Sanksi -->
                        <div class="rounded-2xl border border-rose-100 bg-rose-50/40 p-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="applySanction" class="rounded border-slate-300" />
                                <span class="text-xs font-bold text-slate-800">Berikan sanksi ke {{ reviewingItem.user }}</span>
                            </label>

                            <div v-if="applySanction" class="mt-3 space-y-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Jenis sanksi</label>
                                    <select
                                        v-model="sanctionType"
                                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                                    >
                                        <option v-for="type in sanctionTypes" :key="type">{{ type }}</option>
                                    </select>
                                </div>

                                <div v-if="sanctionType === 'Suspend Sementara'">
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Durasi (hari)</label>
                                    <input
                                        type="number"
                                        min="1"
                                        v-model.number="suspendDays"
                                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Alasan sanksi</label>
                                    <textarea
                                        v-model="sanctionReason"
                                        rows="2"
                                        placeholder="Contoh: Pelanggaran berulang terhadap kebijakan komunitas."
                                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20 resize-none"
                                    ></textarea>
                                </div>

                                <p v-if="sanctionError" class="text-[11px] font-semibold text-rose-600">{{ sanctionError }}</p>

                                <p class="text-[10px] text-slate-400">
                                    Status laporan akan otomatis diubah menjadi "Selesai" saat sanksi diterapkan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 shrink-0">
                        <button
                            type="button"
                            @click="closeReview"
                            class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="saveReview"
                            :class="[
                                'rounded-2xl px-4 py-2 text-xs font-bold text-white transition',
                                applySanction ? 'bg-rose-600 hover:bg-rose-700' : 'bg-[#0A2540] hover:bg-slate-900'
                            ]"
                        >
                            {{ applySanction ? 'Terapkan Sanksi' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Modal Riwayat Sanksi -->
        <Teleport to="body">
            <div
                v-if="showSanctionLog"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeSanctionLog"
            >
                <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden flex flex-col max-h-[80vh]">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Riwayat Sanksi</h3>
                            <p class="text-[11px] text-slate-400">Semua tindakan yang pernah diberikan ke pengguna.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeSanctionLog"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <X class="text-sm" />
                        </button>
                    </div>

                    <div class="p-6 space-y-2 overflow-y-auto">
                        <div
                            v-for="entry in sanctionLog"
                            :key="entry.id"
                            class="rounded-2xl border border-slate-100 px-4 py-3"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold text-slate-900">{{ entry.user }}</p>
                                <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold', sanctionMeta[entry.type].badge]">
                                    <AppIcon :iconClass="['fa-solid', sanctionMeta[entry.type].icon, 'text-[9px]']" />
                                    {{ entry.type }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1.5">{{ entry.reason }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ entry.appliedAt }}<span v-if="entry.duration"> · Durasi {{ entry.duration }}</span>
                            </p>
                        </div>

                        <p v-if="sanctionLog.length === 0" class="text-center text-xs text-slate-400 py-8">
                            Belum ada sanksi yang diberikan.
                        </p>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end shrink-0">
                        <button
                            type="button"
                            @click="closeSanctionLog"
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
