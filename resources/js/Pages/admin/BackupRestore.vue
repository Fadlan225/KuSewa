<script setup>
import { Loader2, X, AlertTriangle, Check, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const backups = ref([
    { id: 1, name: 'Backup 04 Agu 2026', size: '520 MB', created: '04 Agu 2026 08:30', status: 'Berhasil' },
    { id: 2, name: 'Backup 02 Agu 2026', size: '498 MB', created: '02 Agu 2026 23:10', status: 'Berhasil' },
    { id: 3, name: 'Backup 30 Jul 2026', size: '472 MB', created: '30 Jul 2026 22:00', status: 'Gagal' },
]);

const databaseStatus = ref({ size: '42.8 GB', tables: 64, lastOptimized: '03 Agu 2026' });

let nextId = 4;

function formatNow() {
    const now = new Date();
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(now.getDate())} ${months[now.getMonth()]} ${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
}

// ==== Backup Sekarang ====
const isBackingUp = ref(false);

function startBackup() {
    if (isBackingUp.value) return;
    isBackingUp.value = true;

    const entry = {
        id: nextId++,
        name: `Backup ${formatNow().split(' ').slice(0, 3).join(' ')}`,
        size: '-',
        created: formatNow(),
        status: 'Memproses',
    };
    backups.value.unshift(entry);

    setTimeout(() => {
        entry.status = 'Berhasil';
        entry.size = `${(480 + Math.round(Math.random() * 80))} MB`;
        isBackingUp.value = false;
    }, 1800);
}

function retryBackup(item) {
    item.status = 'Memproses';
    setTimeout(() => {
        item.status = 'Berhasil';
        item.size = `${(480 + Math.round(Math.random() * 80))} MB`;
        item.created = formatNow();
    }, 1800);
}

// ==== Unduh ====
function downloadBackup(item) {
    const content = `Nama backup: ${item.name}\nUkuran: ${item.size}\nDibuat: ${item.created}\nStatus: ${item.status}\n\n(Ini berkas placeholder untuk simulasi unduhan backup.)`;
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${item.name.replace(/\s+/g, '-').toLowerCase()}.txt`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// ==== Restore ====
const showRestoreModal = ref(false);
const selectedBackupId = ref(null);
const restoreConfirmText = ref('');
const isRestoring = ref(false);
const restoreDone = ref(false);

const restorableBackups = computed(() => backups.value.filter(item => item.status === 'Berhasil'));
const selectedBackup = computed(() => backups.value.find(item => item.id === selectedBackupId.value) || null);

function openRestore() {
    selectedBackupId.value = restorableBackups.value[0]?.id || null;
    restoreConfirmText.value = '';
    isRestoring.value = false;
    restoreDone.value = false;
    showRestoreModal.value = true;
}

function closeRestore() {
    showRestoreModal.value = false;
}

function confirmRestore() {
    if (!selectedBackup.value || restoreConfirmText.value.trim().toUpperCase() !== 'RESTORE') return;
    isRestoring.value = true;
    setTimeout(() => {
        isRestoring.value = false;
        restoreDone.value = true;
    }, 2000);
}

// ==== Lihat Semua ====
const showAllModal = ref(false);
const allSearch = ref('');
const allStatusFilter = ref('Semua');
const statusOptions = ['Semua', 'Berhasil', 'Gagal', 'Memproses'];

const filteredAll = computed(() => {
    const query = allSearch.value.toLowerCase();
    return backups.value.filter(item => {
        const matchesQuery = [item.name, item.created].join(' ').toLowerCase().includes(query);
        const matchesStatus = allStatusFilter.value === 'Semua' || item.status === allStatusFilter.value;
        return matchesQuery && matchesStatus;
    });
});

function openAll() {
    allSearch.value = '';
    allStatusFilter.value = 'Semua';
    showAllModal.value = true;
}

function closeAll() {
    showAllModal.value = false;
}
</script>

<template>
    <Head title="Backup & Restore Data - Admin Panel" />

    <DashboardLayout role="Admin" title="Backup & Restore Data" description="Kelola cadangan database dan restore sistem saat dibutuhkan.">
        <template #header-actions>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="startBackup"
                        :disabled="isBackingUp"
                        class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <Loader2 v-if="isBackingUp" class="text-[11px] animate-spin" />
                        {{ isBackingUp ? 'Memproses...' : 'Backup Sekarang' }}
                    </button>
                    <button
                        type="button"
                        @click="openRestore"
                        class="rounded-2xl bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition"
                    >
                        Restore
                    </button>
                </div>
        </template>

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
                        <button
                            type="button"
                            @click="openAll"
                            class="text-[11px] font-semibold text-[#0A2540] hover:underline"
                        >
                            Lihat Semua
                        </button>
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
                                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'Berhasil' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : item.status === 'Memproses' ? 'bg-slate-50 text-slate-600 border border-slate-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            <Loader2 v-if="item.status === 'Memproses'" class="text-[9px] animate-spin" />
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button
                                                v-if="item.status === 'Gagal'"
                                                type="button"
                                                @click="retryBackup(item)"
                                                class="rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition"
                                            >
                                                Coba Lagi
                                            </button>
                                            <button
                                                v-if="item.status === 'Berhasil'"
                                                type="button"
                                                @click="downloadBackup(item)"
                                                class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition"
                                            >
                                                Unduh
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        <!-- Modal Restore -->
        <Teleport to="body">
            <div
                v-if="showRestoreModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeRestore"
            >
                <div class="w-full max-w-md rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Restore Database</h3>
                            <p class="text-[11px] text-slate-400">Pulihkan sistem dari salah satu backup yang tersedia.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeRestore"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <X class="text-sm" />
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <template v-if="!restoreDone">
                            <div v-if="restorableBackups.length === 0" class="text-xs text-slate-400 text-center py-6">
                                Belum ada backup berhasil yang bisa dipulihkan.
                            </div>

                            <template v-else>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Pilih backup</label>
                                    <select
                                        v-model.number="selectedBackupId"
                                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                                    >
                                        <option v-for="item in restorableBackups" :key="item.id" :value="item.id">
                                            {{ item.name }} · {{ item.size }}
                                        </option>
                                    </select>
                                </div>

                                <div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 flex gap-2.5">
                                    <AlertTriangle class="text-amber-600 text-xs mt-0.5" />
                                    <p class="text-[11px] text-amber-800">
                                        Semua data saat ini akan digantikan dengan data dari backup terpilih. Tindakan ini tidak bisa dibatalkan.
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">
                                        Ketik <span class="font-bold text-slate-800">RESTORE</span> untuk konfirmasi
                                    </label>
                                    <input
                                        type="text"
                                        v-model="restoreConfirmText"
                                        placeholder="RESTORE"
                                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                                    />
                                </div>
                            </template>
                        </template>

                        <div v-else class="text-center py-6 space-y-2">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mx-auto">
                                <Check class="" />
                            </div>
                            <p class="text-sm font-bold text-slate-900">Restore berhasil</p>
                            <p class="text-[11px] text-slate-500">
                                Sistem telah dipulihkan dari "{{ selectedBackup?.name }}".
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                        <button
                            v-if="!restoreDone"
                            type="button"
                            @click="closeRestore"
                            class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>
                        <button
                            v-if="!restoreDone && restorableBackups.length > 0"
                            type="button"
                            @click="confirmRestore"
                            :disabled="restoreConfirmText.trim().toUpperCase() !== 'RESTORE' || isRestoring"
                            class="rounded-2xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <Loader2 v-if="isRestoring" class="text-[11px] animate-spin" />
                            {{ isRestoring ? 'Memulihkan...' : 'Restore Sekarang' }}
                        </button>
                        <button
                            v-if="restoreDone"
                            type="button"
                            @click="closeRestore"
                            class="rounded-2xl bg-[#0A2540] px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition"
                        >
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Modal Lihat Semua -->
        <Teleport to="body">
            <div
                v-if="showAllModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeAll"
            >
                <div class="w-full max-w-xl rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden flex flex-col max-h-[80vh]">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Semua Riwayat Backup</h3>
                            <p class="text-[11px] text-slate-400">Cari dan saring seluruh riwayat backup.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeAll"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <X class="text-sm" />
                        </button>
                    </div>

                    <div class="px-6 pt-4 shrink-0 flex flex-col sm:flex-row gap-2">
                        <div class="flex items-center gap-3 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60 flex-1">
                            <Search class="text-slate-400 text-xs" />
                            <input
                                type="text"
                                v-model="allSearch"
                                placeholder="Cari nama atau tanggal backup..."
                                class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                            />
                        </div>
                        <select
                            v-model="allStatusFilter"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                        >
                            <option v-for="status in statusOptions" :key="status">{{ status }}</option>
                        </select>
                    </div>

                    <div class="p-6 space-y-2 overflow-y-auto">
                        <div
                            v-for="item in filteredAll"
                            :key="item.id"
                            class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 px-4 py-3"
                        >
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-900 truncate">{{ item.name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ item.size }} · {{ item.created }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span :class="[
                                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold',
                                    item.status === 'Berhasil' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : item.status === 'Memproses' ? 'bg-slate-50 text-slate-600 border border-slate-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                ]">
                                    <Loader2 v-if="item.status === 'Memproses'" class="text-[9px] animate-spin" />
                                    {{ item.status }}
                                </span>
                                <button
                                    v-if="item.status === 'Berhasil'"
                                    type="button"
                                    @click="downloadBackup(item)"
                                    class="rounded-full bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition"
                                >
                                    Unduh
                                </button>
                            </div>
                        </div>

                        <p v-if="filteredAll.length === 0" class="text-center text-xs text-slate-400 py-8">
                            Tidak ada backup sesuai pencarian atau filter.
                        </p>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end shrink-0">
                        <button
                            type="button"
                            @click="closeAll"
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
