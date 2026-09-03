<script setup>
import { Search, FolderOpen, X, ChevronLeft, ChevronRight, ExternalLink, ImageOff } from 'lucide-vue-next';
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    applicants: { type: Object, default: () => ({ data: [] }) },
    stats:      { type: Object, default: () => ({ pending: 0, verified: 0, rejected: 0 }) },
    filters:    { type: Object, default: () => ({}) },
});

const activeFilter = ref(props.filters.status || 'Semua');
const searchQuery  = ref(props.filters.search || '');
const filterTabs   = ['Semua', 'pending', 'verified', 'rejected'];
const filterLabels = { Semua: 'Semua', pending: 'Pending', verified: 'Disetujui', rejected: 'Ditolak' };

const selectedApplicant = ref(null);
const rejectReason      = ref('');
const showRejectInput   = ref(false);
const isSubmitting      = ref(false);
const ktpImageError     = ref(false);

// Reset error state saat buka modal baru
const openDetail = (item) => {
    selectedApplicant.value = item;
    showRejectInput.value   = false;
    ktpImageError.value     = false;
};

// ── Filter / search ───────────────────────────────────────────────────────────
let searchTimer = null;
const applyFilter = (status) => {
    activeFilter.value = status;
    router.get(route('admin.pengajuan-akun'), {
        search: searchQuery.value || undefined,
        status: status !== 'Semua' ? status : undefined,
    }, { preserveState: true, replace: true });
};

const applySearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.pengajuan-akun'), {
            search: searchQuery.value || undefined,
            status: activeFilter.value !== 'Semua' ? activeFilter.value : undefined,
        }, { preserveState: true, replace: true });
    }, 400);
};

// ── Approve ───────────────────────────────────────────────────────────────────
const handleApprove = (id) => {
    isSubmitting.value = true;
    router.patch(route('admin.pengajuan-akun.approve', id), {}, {
        preserveScroll: true,
        onSuccess: () => { selectedApplicant.value = null; isSubmitting.value = false; },
        onError:   () => { isSubmitting.value = false; },
    });
};

// ── Reject ────────────────────────────────────────────────────────────────────
const openReject = () => { showRejectInput.value = true; rejectReason.value = ''; };
const cancelReject = () => { showRejectInput.value = false; rejectReason.value = ''; };
const handleReject = (id) => {
    if (!rejectReason.value.trim()) return;
    isSubmitting.value = true;
    router.patch(route('admin.pengajuan-akun.reject', id), { reason: rejectReason.value }, {
        preserveScroll: true,
        onSuccess: () => { selectedApplicant.value = null; showRejectInput.value = false; isSubmitting.value = false; },
        onError:   () => { isSubmitting.value = false; },
    });
};

// ── Helper ────────────────────────────────────────────────────────────────────
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
const statusClass = (s) => ({
    pending:  'bg-amber-50 text-amber-600 border border-amber-200/60',
    verified: 'bg-emerald-50 text-emerald-600 border border-emerald-200/60',
    rejected: 'bg-rose-50 text-rose-600 border border-rose-200/60',
}[s] ?? 'bg-slate-50 text-slate-600 border border-slate-200');
const statusLabel = (s) => ({ pending: 'Pending', verified: 'Disetujui', rejected: 'Ditolak' }[s] ?? s);
</script>

<template>
    <Head title="Kelola Pengajuan Akun - Admin Panel" />

    <DashboardLayout role="Admin" title="Kelola Pengajuan Akun" description="Verifikasi dan pantau permintaan akun baru dari calon pemilik aset (owner).">
        <template #header-actions>
            <div class="flex items-center gap-3 w-64 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60 focus-within:border-[#FFC000] focus-within:ring-1 focus-within:ring-[#FFC000] transition-all">
                <Search class="text-slate-400 w-3.5 h-3.5 shrink-0" />
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="applySearch"
                    placeholder="Cari nama, NIK..."
                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                />
            </div>
        </template>

        <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Menunggu Review</p>
                    <p class="mt-3 text-3xl font-extrabold text-amber-500">{{ stats.pending }}</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Disetujui</p>
                    <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ stats.verified }}</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Ditolak</p>
                    <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ stats.rejected }}</p>
                </div>
            </div>

            <!-- Filter tabs -->
            <div class="flex justify-end">
                <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200/80 text-xs shadow-xs">
                    <button
                        v-for="tab in filterTabs" :key="tab"
                        @click="applyFilter(tab)"
                        :class="[
                            'px-3.5 py-1.5 rounded-lg font-semibold transition',
                            activeFilter === tab ? 'bg-slate-900 text-[#FFC000] shadow-xs' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'
                        ]"
                    >{{ filterLabels[tab] }}</button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-[940px] w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                <th class="py-4 px-6">Calon Pengguna</th>
                                <th class="py-4 px-4">NIK</th>
                                <th class="py-4 px-4">Tanggal Pengajuan</th>
                                <th class="py-4 px-4">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in applicants.data" :key="item.id" class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-slate-800 uppercase shadow-xs">
                                            {{ item.user?.name?.charAt(0) ?? '?' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ item.user?.name }}</p>
                                            <p class="text-[10px] text-slate-400">{{ item.user?.email }} • {{ item.user?.phone ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-600 font-medium">{{ item.national_id ?? '-' }}</td>
                                <td class="py-4 px-4 text-slate-500 whitespace-nowrap">{{ formatDate(item.created_at) }}</td>
                                <td class="py-4 px-4">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold', statusClass(item.status)]">
                                        {{ statusLabel(item.status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        <button
                                            @click="openDetail(item); showRejectInput = false"
                                            class="rounded-full bg-slate-100 px-4 py-1.5 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-200"
                                        >Detail KTP</button>
                                        <button
                                            v-if="item.status === 'pending'"
                                            @click="handleApprove(item.id)"
                                            class="rounded-full bg-emerald-600 px-4 py-1.5 text-[11px] font-semibold text-white transition hover:bg-emerald-700"
                                        >Setujui</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="applicants.data.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <FolderOpen class="w-6 h-6 mb-2 block mx-auto text-slate-300" />
                                    Tidak ada data pengajuan akun yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="applicants.last_page > 1" class="flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ applicants.from }}–{{ applicants.to }} dari {{ applicants.total }}</span>
                <div class="flex items-center gap-1">
                    <Link v-if="applicants.prev_page_url" :href="applicants.prev_page_url" preserve-state class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1">
                        <ChevronLeft class="w-3.5 h-3.5" /> Prev
                    </Link>
                    <span class="px-3 py-1.5 font-semibold text-slate-700">{{ applicants.current_page }} / {{ applicants.last_page }}</span>
                    <Link v-if="applicants.next_page_url" :href="applicants.next_page_url" preserve-state class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1">
                        Next <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- MODAL: Detail KTP & Verifikasi -->
        <div v-if="selectedApplicant" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Verifikasi Dokumen NIK & KTP Owner</h3>
                        <p class="text-[10px] text-slate-400">Periksa kesesuaian data diri dengan foto KTP terlampir.</p>
                    </div>
                    <button @click="selectedApplicant = null; showRejectInput = false" class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                        <div>
                            <span class="text-slate-400 text-[10px] block font-medium">Nama Lengkap</span>
                            <p class="font-bold text-slate-900 text-sm mt-0.5">{{ selectedApplicant.user?.name }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block font-medium">Status</span>
                            <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold mt-0.5', statusClass(selectedApplicant.status)]">
                                {{ statusLabel(selectedApplicant.status) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block font-medium">Nomor NIK</span>
                            <p class="font-mono font-bold text-slate-800 mt-0.5">{{ selectedApplicant.national_id ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block font-medium">Telepon / WhatsApp</span>
                            <p class="font-semibold text-slate-800 mt-0.5">{{ selectedApplicant.user?.phone ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- KTP Photo -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-slate-400 text-[10px] block font-bold uppercase tracking-wider">Lampiran Foto KTP Resmi</span>
                            <a
                                v-if="selectedApplicant.ktp_url && !ktpImageError"
                                :href="selectedApplicant.ktp_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-[10px] text-slate-500 hover:text-slate-800 transition"
                            >
                                <ExternalLink class="w-3 h-3" />
                                Buka di tab baru
                            </a>
                        </div>
                        <div class="h-56 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200/80 shadow-inner relative">
                            <img
                                v-if="selectedApplicant.ktp_url && !ktpImageError"
                                :src="selectedApplicant.ktp_url"
                                class="w-full h-full object-contain hover:scale-105 transition duration-300 cursor-zoom-in bg-slate-900"
                                @error="ktpImageError = true"
                                alt="Foto KTP"
                            />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-xs gap-2">
                                <ImageOff class="w-8 h-8 text-slate-300" />
                                <span>{{ ktpImageError ? 'Gagal memuat foto KTP' : 'Tidak ada foto KTP' }}</span>
                                <a
                                    v-if="ktpImageError && selectedApplicant.ktp_url"
                                    :href="selectedApplicant.ktp_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-1 text-[11px] text-blue-500 hover:underline inline-flex items-center gap-1"
                                >
                                    <ExternalLink class="w-3 h-3" /> Coba buka langsung
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Alasan penolakan (jika sudah ditolak) -->
                    <div v-if="selectedApplicant.rejection_reason" class="bg-rose-50 rounded-2xl p-3 border border-rose-200/60">
                        <span class="text-[10px] font-bold uppercase text-rose-400 block mb-1">Alasan Penolakan</span>
                        <p class="text-rose-700 font-medium">{{ selectedApplicant.rejection_reason }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div v-if="selectedApplicant.status === 'pending'" class="pt-3 border-t border-slate-100 space-y-3">
                    <!-- Input alasan tolak -->
                    <div v-if="showRejectInput" class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-600">Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="rejectReason"
                            rows="3"
                            placeholder="Tulis alasan penolakan yang jelas untuk dikirim ke pemohon..."
                            class="w-full text-xs border border-slate-300 focus:ring-2 focus:ring-rose-400 focus:border-transparent outline-none rounded-xl px-3 py-2 transition resize-none"
                        ></textarea>
                        <div class="flex items-center gap-2">
                            <button @click="cancelReject" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">Batal</button>
                            <button
                                @click="handleReject(selectedApplicant.id)"
                                :disabled="!rejectReason.trim() || isSubmitting"
                                class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 disabled:opacity-50 transition"
                            >{{ isSubmitting ? 'Memproses...' : 'Konfirmasi Tolak' }}</button>
                        </div>
                    </div>

                    <div v-else class="flex items-center justify-end gap-3">
                        <button
                            @click="openReject"
                            class="px-4 py-2.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold transition text-sm"
                        >Tolak Pengajuan</button>
                        <button
                            @click="handleApprove(selectedApplicant.id)"
                            :disabled="isSubmitting"
                            class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-bold transition text-sm disabled:opacity-60"
                        >{{ isSubmitting ? 'Memproses...' : 'Setujui Pengajuan' }}</button>
                    </div>
                </div>
                <div v-else class="flex items-center justify-end pt-3 border-t border-slate-100">
                    <span class="text-sm font-semibold text-slate-500">Pengajuan sudah {{ statusLabel(selectedApplicant.status).toLowerCase() }}.</span>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
