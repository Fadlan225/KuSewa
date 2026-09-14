<script setup>
import { Search, AlertCircle, Eye, ShieldAlert, CheckCircle, Clock, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';
import AvatarMale from '@/Components/ui/Icons/AvatarMale.vue';
import AvatarFemale from '@/Components/ui/Icons/AvatarFemale.vue';
import AvatarDefault from '@/Components/ui/Icons/AvatarDefault.vue';

const props = defineProps({
    owners: { type: Object, default: () => ({ data: [] }) },
    totals: { type: Object, default: () => ({ all: 0, active: 0, suspended: 0 }) },
    filters: { type: Object, default: () => ({ search: '', status: 'Semua' }) }
});

const activeFilter = ref(props.filters.status || 'Semua');
const searchQuery = ref(props.filters.search || '');

let searchTimer = null;

watch(activeFilter, (newStatus) => {
    router.get(route('admin.service-fee'), {
        search: searchQuery.value || undefined,
        status: newStatus !== 'Semua' ? newStatus : undefined,
    }, { preserveState: true, replace: true });
});

const applySearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.service-fee'), {
            search: searchQuery.value || undefined,
            status: activeFilter.value !== 'Semua' ? activeFilter.value : undefined,
        }, { preserveState: true, replace: true });
    }, 400);
};

// Formatting Helper
const formatRupiah = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
};
</script>

<template>
    <Head title="Pantauan Pembayaran & Sanksi - Admin Panel" />

    <DashboardLayout 
        role="Admin" 
        title="Pantauan Pembayaran & Sanksi" 
        description="Kelola status pembayaran biaya platform dan sanksi pemilik aset."
    >
        <div class="space-y-6 mt-6">

            <!-- METRIC SUMMARY STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Total Owner -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Total Owner</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ totals.all }}</p>
                </div>

                <!-- Akun Aktif -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Akun Aktif</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ totals.active }}</p>
                </div>

                <!-- Disanksi / Nonaktif -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Disanksi / Nonaktif</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ totals.suspended }}</p>
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
                            placeholder="Cari nama atau email..."
                            class="w-full bg-slate-50 border border-slate-200 text-sm pl-10 pr-4 py-2.5 rounded focus:outline-none focus:bg-white hover:border-[#FFC000] focus:border-[#FFC000] focus:ring-2 focus:ring-[#FFC000]/20 transition-all text-slate-700 placeholder:text-slate-400"
                        />
                    </div>

                    <!-- Dropdowns -->
                    <div class="flex items-center gap-3 w-full lg:w-auto justify-between lg:justify-end flex-wrap sm:flex-nowrap">
                        <CustomSelect
                            v-model="activeFilter"
                            :options="[
                                { label: 'Semua Status', value: 'Semua' },
                                { label: 'Menunggak', value: 'Menunggak' },
                                { label: 'Menunggu Verifikasi', value: 'Menunggu Verifikasi' },
                                { label: 'Lancar', value: 'Lancar' }
                            ]"
                        />
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div>
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-[600px] w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 uppercase font-bold text-xs tracking-wider">
                                    <th class="py-4 px-6">Informasi Owner</th>
                                    <th class="py-4 px-4 text-center">Properti Aktif</th>
                                    <th class="py-4 px-4">Tagihan Belum Lunas</th>
                                    <th class="py-4 px-4">Status Pembayaran</th>
                                    <th class="py-4 px-6 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="owner in owners.data" :key="owner.id" class="hover:bg-slate-50/60 transition-colors group">

                                    <!-- User Info -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div v-if="owner.avatar" class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-slate-200">
                                                <img :src="owner.avatar" :alt="owner.name" class="w-full h-full object-cover" />
                                            </div>
                                            <div v-else class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-slate-200 bg-slate-50 flex items-center justify-center">
                                                <AvatarMale v-if="owner.gender === 'male'" class="w-full h-full" />
                                                <AvatarFemale v-else-if="owner.gender === 'female'" class="w-full h-full" />
                                                <AvatarDefault v-else class="w-full h-full" />
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ owner.name }}</p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">{{ owner.email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Properti -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center justify-center bg-slate-100 text-slate-700 w-7 h-7 rounded-lg font-bold text-xs">
                                            {{ owner.properties }}
                                        </span>
                                    </td>

                                    <!-- Tagihan Belum Lunas -->
                                    <td class="py-4 px-4">
                                        <span class="font-bold text-slate-800">
                                            {{ formatRupiah(owner.total_unpaid) }}
                                        </span>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div :class="[
                                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset',
                                            owner.billing_status === 'Lancar' ? 'bg-emerald-50 text-emerald-700 ring-emerald-500/20' : 
                                            owner.billing_status === 'Menunggu Verifikasi' ? 'bg-amber-50 text-amber-700 ring-amber-500/20' : 
                                            'bg-rose-50 text-rose-700 ring-rose-500/20'
                                        ]">
                                            <CheckCircle v-if="owner.billing_status === 'Lancar'" class="w-3.5 h-3.5" />
                                            <Clock v-else-if="owner.billing_status === 'Menunggu Verifikasi'" class="w-3.5 h-3.5" />
                                            <AlertCircle v-else class="w-3.5 h-3.5" />
                                            {{ owner.billing_status }}
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-4 px-6 text-right whitespace-nowrap space-x-2">
                                        <button class="inline-block rounded bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 transition group-hover:bg-slate-200 group-hover:text-slate-900 border border-slate-200">
                                            Detail
                                        </button>
                                        
                                        <button v-if="owner.account_status === 'active' && owner.billing_status === 'Menunggak'" class="inline-flex items-center rounded bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-600 transition border border-rose-200 hover:bg-rose-600 hover:text-white hover:border-transparent">
                                            <ShieldAlert class="w-3.5 h-3.5 mr-1" /> Nonaktifkan
                                        </button>
                                        <button v-else-if="owner.account_status !== 'active'" class="inline-block rounded bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600 transition border border-emerald-200 hover:bg-emerald-600 hover:text-white hover:border-transparent">
                                            Pulihkan
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="owners.data.length === 0">
                                    <td colspan="5" class="py-16 text-center">
                                        <EmptyStateIcon class="w-32 h-32 mx-auto mb-4 opacity-80" />
                                        <h3 class="text-slate-800 font-bold text-base mb-1">Belum Ada Data Owner</h3>
                                        <p class="text-slate-500 font-medium text-xs max-w-md mx-auto">
                                            Data pantauan tagihan masih kosong atau tidak ada owner yang cocok dengan filter saat ini.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="owners.last_page > 1" class="flex items-center justify-between text-xs text-slate-500 pt-2">
                <span>Menampilkan {{ owners.from }}–{{ owners.to }} dari {{ owners.total }} owner</span>
                <div class="flex items-center gap-1">
                    <Link
                        v-if="owners.prev_page_url"
                        :href="owners.prev_page_url"
                        preserve-state
                        class="rounded border border-slate-200 px-3 py-1.5 hover:bg-slate-50 hover:text-slate-700 transition flex items-center gap-1 font-semibold"
                    >
                        <ChevronLeft class="w-3.5 h-3.5" /> Prev
                    </Link>
                    <span class="px-3 py-1.5 font-bold text-[#0A2540]">{{ owners.current_page }} / {{ owners.last_page }}</span>
                    <Link
                        v-if="owners.next_page_url"
                        :href="owners.next_page_url"
                        preserve-state
                        class="rounded border border-slate-200 px-3 py-1.5 hover:bg-slate-50 hover:text-slate-700 transition flex items-center gap-1 font-semibold"
                    >
                        Next <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
