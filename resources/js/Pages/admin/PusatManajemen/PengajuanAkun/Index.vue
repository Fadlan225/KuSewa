<script setup>
import { Search, FolderOpen, X, ChevronLeft, ChevronRight, ExternalLink, ImageOff } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import WaitingVerificationIcon from '@/Components/ui/Icons/WaitingVerificationIcon.vue';
import EmptyStateData from '@/Components/ui/Icons/EmptyStateData.vue';
import AvatarMale from '@/Components/ui/Icons/AvatarMale.vue';
import AvatarFemale from '@/Components/ui/Icons/AvatarFemale.vue';
import AvatarDefault from '@/Components/ui/Icons/AvatarDefault.vue';

const props = defineProps({
    applicants: { type: Object, default: () => ({ data: [] }) },
    stats:      { type: Object, default: () => ({ pending: 0, verified: 0, rejected: 0 }) },
    filters:    { type: Object, default: () => ({}) },
});

const activeFilter = ref(props.filters.status || 'Semua');
const searchQuery  = ref(props.filters.search || '');

// ── Filter / search ───────────────────────────────────────────────────────────
let searchTimer = null;

watch(activeFilter, (newStatus) => {
    router.get(route('admin.pengajuan-akun'), {
        search: searchQuery.value || undefined,
        status: newStatus !== 'Semua' ? newStatus : undefined,
    }, { preserveState: true, replace: true });
});

const applySearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.pengajuan-akun'), {
            search: searchQuery.value || undefined,
            status: activeFilter.value !== 'Semua' ? activeFilter.value : undefined,
        }, { preserveState: true, replace: true });
    }, 400);
};

// ── Helper ────────────────────────────────────────────────────────────────────
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
const statusClass = (s) => ({
    pending:  'bg-amber-50 text-amber-700 border-amber-200',
    verified: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected: 'bg-rose-50 text-rose-700 border-rose-200',
}[s] ?? 'bg-slate-50 text-slate-700 border-slate-200');
const statusLabel = (s) => ({ pending: 'Menunggu', verified: 'Disetujui', rejected: 'Ditolak' }[s] ?? s);

const translateGender = (gender) => {
    const map = { male: 'Laki-laki', female: 'Perempuan' };
    return map[gender] || gender;
};
</script>

<template>
    <Head title="Kelola Pengajuan Akun - Admin Panel" />

    <DashboardLayout role="Admin" title="Kelola Pengajuan Akun" description="Verifikasi dan pantau permintaan akun baru dari calon pemilik aset (owner).">
        <div class="space-y-6 mt-6">

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Menunggu Review</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.pending }}</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Disetujui</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.verified }}</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Ditolak</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.rejected }}</p>
                </div>
            </div>

            <!-- FILTER BAR & SEARCH -->
            <div class="bg-white border border-slate-200/60 shadow-sm rounded-xl p-4 md:p-5 space-y-4 relative z-20">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    
                    <!-- Search Box -->
                    <div class="relative w-full lg:max-w-sm flex-1">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm" />
                        <input
                            v-model="searchQuery"
                            @input="applySearch"
                            type="text"
                            placeholder="Cari nama, NIK..."
                            class="w-full bg-slate-50 border border-slate-200 text-sm pl-10 pr-4 py-2.5 rounded focus:outline-none focus:bg-white hover:border-[#FFC000] focus:border-[#FFC000] focus:ring-2 focus:ring-[#FFC000]/20 transition-all text-slate-700 placeholder:text-slate-400"
                        />
                    </div>

                    <!-- Dropdowns -->
                    <div class="flex items-center gap-3 w-full lg:w-auto justify-between lg:justify-end flex-wrap sm:flex-nowrap">
                        <CustomSelect
                            v-model="activeFilter"
                            :options="[
                                { label: 'Semua Status', value: 'Semua' },
                                { label: 'Menunggu', value: 'pending' },
                                { label: 'Disetujui', value: 'verified' },
                                { label: 'Ditolak', value: 'rejected' }
                            ]"
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-[600px] w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 uppercase font-bold text-xs tracking-wider">
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
                                        <div v-if="item.user?.avatar" class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-slate-200">
                                            <img :src="item.user.avatar" :alt="item.user.name" class="w-full h-full object-cover" />
                                        </div>
                                        <div v-else class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-slate-200 bg-slate-50 flex items-center justify-center">
                                            <AvatarMale v-if="item.user?.gender === 'male'" class="w-full h-full" />
                                            <AvatarFemale v-else-if="item.user?.gender === 'female'" class="w-full h-full" />
                                            <AvatarDefault v-else class="w-full h-full" />
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
                                    <span :class="['inline-flex items-center rounded border px-3 py-1 text-xs font-bold', statusClass(item.status)]">
                                        {{ statusLabel(item.status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.pengajuan-akun.show', item.id)"
                                            class="rounded bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-slate-200 hover:text-slate-900 border border-slate-200"
                                        >Detail</Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="applicants.data.length === 0">
                                    <td colspan="5" class="py-16 text-center">
                                        <WaitingVerificationIcon class="w-32 h-32 mx-auto mb-4 opacity-80" />
                                        <h3 class="text-slate-800 font-bold text-base mb-1">Belum Ada Pengajuan Akun</h3>
                                        <p class="text-slate-500 font-medium text-xs max-w-md mx-auto">
                                            Tidak ada data pengajuan yang perlu diverifikasi saat ini. Pengajuan akan muncul di sini otomatis ketika pengguna mendaftar sebagai pemilik aset. Anda bisa menyesuaikan filter untuk melihat riwayat pengajuan sebelumnya.
                                        </p>
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
    </DashboardLayout>
</template>
