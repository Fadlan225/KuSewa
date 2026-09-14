<script setup>
import { Search, RotateCw, ChevronRight, ChevronLeft, MapPin, X, Check, BadgeCheck, Phone, Eye } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import AssetStatusEmptyIllustration from '@/Components/ui/Icons/AssetStatusEmptyIllustration.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import GroupedSearchableSelect from '@/Components/ui/GroupedSearchableSelect.vue';

const props = defineProps({
    assets:     { type: Object, default: () => ({ data: [] }) },
    stats:      { type: Object, default: () => ({ pending: 0, approved: 0, rejected: 0, total: 0 }) },
    categories: { type: Array,  default: () => [] },
    filters:    { type: Object, default: () => ({}) },
});

const activeFilter     = ref(props.filters.status || 'Semua');
const activeTypeFilter = ref(props.filters.type_id ? Number(props.filters.type_id) : null);
const searchQuery      = ref(props.filters.search || '');
const filters      = ['Semua', 'Menunggu', 'Disetujui', 'Ditolak'];
const statusMap    = { Semua: undefined, Menunggu: 'pending', Disetujui: 'approved', Ditolak: 'rejected' };

const activePopoverId = ref(null);
const popoverPosition = ref({ x: 0, y: 0 });
const selectedOwnerInfo = ref(null);

const showOwnerPopover = (item, event) => {
    selectedOwnerInfo.value = item;
    activePopoverId.value = item.id;
    
    const popoverWidth = 280;
    const popoverHeight = 420; // estimated height
    const margin = 16;
    
    let x = event.clientX + 15;
    let y = event.clientY - 20; // slightly shifted up for better alignment
    
    if (x + popoverWidth + margin > window.innerWidth) {
        x = event.clientX - popoverWidth - 15;
    }
    
    if (y + popoverHeight + margin > window.innerHeight) {
        y = window.innerHeight - popoverHeight - margin;
    }
    
    if (y < margin) {
        y = margin;
    }
    
    popoverPosition.value = { x, y };
};

const closePopover = () => {
    activePopoverId.value = null;
    selectedOwnerInfo.value = null;
};

let searchTimer = null;

watch([activeFilter, activeTypeFilter], ([newStatus, newType]) => {
    router.get(route('admin.validasi-aset'), {
        search: searchQuery.value || undefined,
        status: statusMap[newStatus],
        type_id: newType || undefined,
    }, { preserveState: true, replace: true });
});

const applySearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.validasi-aset'), {
            search: searchQuery.value || undefined,
            status: statusMap[activeFilter.value],
            type_id: activeTypeFilter.value || undefined,
        }, { preserveState: true, replace: true });
    }, 400);
};

const refresh = () => {
    router.get(route('admin.validasi-aset'), {
        search: searchQuery.value || undefined,
        status: statusMap[activeFilter.value],
        type_id: activeTypeFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

const formatRupiah = (value) => value ? `Rp ${Number(value).toLocaleString('id-ID')}` : '-';
const formatDate   = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
const assetImages  = (asset) => {
    const imgs = asset?.images?.map(i => i.image ? '/storage/' + i.image : null).filter(Boolean);
    return imgs?.length ? imgs : ['https://placehold.co/800x500?text=Belum+Ada+Foto'];
};
const detailLabel = (key) => key.replaceAll('_', ' ');
const statusClass = (s) => ({
    pending:  'bg-amber-50 text-amber-700 border-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected: 'bg-rose-50 text-rose-700 border-rose-200',
}[s] ?? 'bg-slate-50 text-slate-700 border-slate-200');
const statusLabel = (s) => ({ pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' }[s] ?? s);
</script>

<template>
    <Head title="Validasi Aset - Admin Panel" />

    <DashboardLayout role="Admin" title="Validasi Aset Properti" description="Pantau dan verifikasi aset properti yang didaftarkan oleh para owner.">
        <div class="space-y-6 mt-6">
            
            <!-- METRIC SUMMARY STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Keseluruhan -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Total Keseluruhan</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.total }}</p>
                </div>
                <!-- Menunggu Verifikasi -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Menunggu Verifikasi</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.pending }}</p>
                </div>
                <!-- Aset Disetujui -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Aset Disetujui</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.approved }}</p>
                </div>
                <!-- Aset Ditolak -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Aset Ditolak</p>
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
                            placeholder="Cari nama aset, pemilik..."
                            class="w-full bg-slate-50 border border-slate-200 text-sm pl-10 pr-4 py-2.5 rounded focus:outline-none focus:bg-white hover:border-[#FFC000] focus:border-[#FFC000] focus:ring-2 focus:ring-[#FFC000]/20 transition-all text-slate-700 placeholder:text-slate-400"
                        />
                    </div>

                    <!-- Dropdowns & Actions -->
                    <div class="flex items-center gap-3 w-full lg:w-auto justify-between lg:justify-end flex-wrap sm:flex-nowrap">
                        <div class="w-48">
                            <GroupedSearchableSelect
                                v-model="activeTypeFilter"
                                :categories="[{ id: 'all', name: 'Semua Kategori', types: [{ id: null, name: 'Semua Tipe Aset' }] }, ...categories]"
                                placeholder="Pilih Tipe Aset"
                            />
                        </div>
                        <CustomSelect
                            v-model="activeFilter"
                            :options="[
                                { label: 'Semua Status', value: 'Semua' },
                                { label: 'Menunggu', value: 'Menunggu' },
                                { label: 'Disetujui', value: 'Disetujui' },
                                { label: 'Ditolak', value: 'Ditolak' }
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
                                    <th class="py-4 px-6">Informasi Aset</th>
                                    <th class="py-4 px-4">Pemilik</th>
                                    <th class="py-4 px-4">Lokasi</th>
                                    <th class="py-4 px-4">Tanggal Diajukan</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in assets.data" :key="item.id" class="hover:bg-slate-50/60 transition-colors group">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-lg overflow-hidden shrink-0 border border-slate-200 bg-slate-50">
                                                <img :src="assetImages(item)[0]" :alt="item.title" class="w-full h-full object-cover" />
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ item.title }}</p>
                                                <p class="text-[10px] text-slate-400 font-semibold">{{ item.type?.name ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 cursor-pointer hover:bg-slate-100/50 transition-colors" @click.stop="showOwnerPopover(item, $event)" @contextmenu.prevent="showOwnerPopover(item, $event)">
                                        <div class="flex flex-col">
                                            <p class="font-bold text-slate-900 text-sm">{{ item.owner_profile?.user?.name ?? '-' }}</p>
                                            <p class="text-[10px] text-slate-400">{{ item.owner_profile?.user?.email ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-col">
                                            <p class="font-bold text-slate-700 text-sm">{{ item.city?.name ?? '-' }}</p>
                                            <p class="text-[10px] text-slate-400">{{ item.district?.name ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-slate-500 font-medium text-xs whitespace-nowrap">{{ formatDate(item.created_at) }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span :class="['inline-flex items-center rounded border px-3 py-1 text-xs font-bold', statusClass(item.status)]">
                                            {{ statusLabel(item.status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <div class="inline-flex justify-end">
                                            <Link :href="route('admin.validasi-aset.show', item.id)" class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 font-bold text-xs hover:text-[#0A2540] hover:bg-slate-100 transition border border-slate-200 shadow-sm">
                                                Detail
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="assets.data.length === 0">
                                    <td colspan="6" class="py-16 text-center">
                                        <AssetStatusEmptyIllustration class="w-32 h-32 mx-auto mb-4 opacity-80" />
                                        <h3 class="text-slate-800 font-bold text-base mb-1">Belum Ada Data Aset</h3>
                                        <p class="text-slate-500 font-medium text-xs max-w-md mx-auto">
                                            Data aset properti masih kosong atau tidak ada yang sesuai dengan pencarian Anda. Aset yang didaftarkan oleh pemilik akan muncul di sini untuk Anda validasi. Silakan ubah filter untuk mencari data lain.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="assets.last_page > 1" class="flex items-center justify-between text-xs text-slate-500 pt-2">
                <span>Menampilkan {{ assets.from }}–{{ assets.to }} dari {{ assets.total }} aset</span>
                <div class="flex items-center gap-1">
                    <Link
                        v-if="assets.prev_page_url"
                        :href="assets.prev_page_url"
                        preserve-state
                        class="rounded border border-slate-200 px-3 py-1.5 hover:bg-slate-50 hover:text-slate-700 transition flex items-center gap-1 font-semibold"
                    >
                        <ChevronLeft class="w-3.5 h-3.5" /> Prev
                    </Link>
                    <span class="px-3 py-1.5 font-bold text-[#0A2540]">{{ assets.current_page }} / {{ assets.last_page }}</span>
                    <Link
                        v-if="assets.next_page_url"
                        :href="assets.next_page_url"
                        preserve-state
                        class="rounded border border-slate-200 px-3 py-1.5 hover:bg-slate-50 hover:text-slate-700 transition flex items-center gap-1 font-semibold"
                    >
                        Next <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
            </div>
    </DashboardLayout>

    <!-- Popover Backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="activePopoverId" class="fixed inset-0 z-40" @click="closePopover" @contextmenu.prevent="closePopover"></div>
    </Transition>
    
    <!-- Owner Info Popover -->
    <Transition
        enter-active-class="transition-all duration-200 ease-out origin-top-left"
        enter-from-class="opacity-0 scale-75"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-150 ease-in origin-top-left"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-75"
    >
        <div 
            v-if="activePopoverId" 
            class="fixed z-50 w-56 bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden flex flex-col"
            :style="{ top: `${popoverPosition.y}px`, left: `${popoverPosition.x}px` }"
        >
        <div class="px-4 py-3 flex items-center justify-between border-b border-slate-50">
            <h3 class="font-bold text-slate-800 text-xs">Info Pemilik</h3>
            <button @click="closePopover" class="text-slate-400 hover:text-slate-600 transition">
                <X class="w-3.5 h-3.5" />
            </button>
        </div>

        <div class="p-4 space-y-4">
            <div class="flex items-center gap-3">
                <img 
                    :src="selectedOwnerInfo.owner_profile?.user?.profile_photo ? '/storage/' + selectedOwnerInfo.owner_profile.user.profile_photo : (selectedOwnerInfo.owner_profile?.user?.gender === 'female' ? '/assets/img/AvatarFemale.svg' : '/assets/img/AvatarMale.svg')" 
                    class="w-10 h-10 rounded-full object-cover border border-slate-100 shadow-sm shrink-0" 
                />
                <div class="overflow-hidden">
                    <div class="flex items-center gap-1">
                        <p class="font-bold text-slate-900 text-xs truncate">{{ selectedOwnerInfo.owner_profile?.user?.name }}</p>
                        <BadgeCheck v-if="selectedOwnerInfo.owner_profile?.status === 'verified'" class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                    </div>
                    <p class="text-[10px] text-slate-500 truncate">{{ selectedOwnerInfo.owner_profile?.user?.email }}</p>
                </div>
            </div>

            <div class="space-y-3 pt-3 border-t border-slate-100">
                <div>
                    <span class="block text-[10px] font-semibold text-slate-400">Nomor Telepon</span>
                    <div class="text-xs font-medium text-slate-700 mt-0.5">{{ selectedOwnerInfo.owner_profile?.user?.phone || '-' }}</div>
                </div>
                <div>
                    <span class="block text-[10px] font-semibold text-slate-400">Tanggal Bergabung</span>
                    <div class="text-xs font-medium text-slate-700 mt-0.5">{{ formatDate(selectedOwnerInfo.owner_profile?.user?.created_at) }}</div>
                </div>
                <div>
                    <span class="block text-[10px] font-semibold text-slate-400">Total Aset (Aktif)</span>
                    <div class="text-xs font-medium text-slate-700 mt-0.5">{{ selectedOwnerInfo.owner_profile?.assets_count || 0 }} Aset</div>
                </div>
            </div>
        </div>
        
        <div class="p-2 border-t border-slate-100 bg-slate-50/50">
            <Link 
                v-if="selectedOwnerInfo.owner_profile?.user?.id"
                :href="route('admin.user-management.show', selectedOwnerInfo.owner_profile.user.id)"
                class="w-full flex items-center justify-center gap-1.5 py-2 rounded-lg hover:bg-slate-200/50 transition text-xs font-bold text-[#0A2540]"
            >
                <Eye class="w-3.5 h-3.5" />
                <span>Lihat Profil Lengkap</span>
            </Link>
        </div>
        </div>
    </Transition>
</template>
