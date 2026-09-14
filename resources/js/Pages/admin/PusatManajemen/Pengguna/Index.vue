<script setup>
import { Search, FolderOpen, X, ShieldOff, ShieldCheck, Trash2, ChevronLeft, ChevronRight, ShieldAlert, Users, UserCheck, UserMinus } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import AvatarMale from '@/Components/ui/Icons/AvatarMale.vue';
import AvatarFemale from '@/Components/ui/Icons/AvatarFemale.vue';
import AvatarDefault from '@/Components/ui/Icons/AvatarDefault.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';
import ToggleStatusIllustration from '@/Components/ui/Icons/ToggleStatusIllustration.vue';

const props = defineProps({
    users:   { type: Object, default: () => ({ data: [] }) },
    stats:   { type: Object, default: () => ({ total: 0, owner: 0, customer: 0 }) },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();

const activeFilter = ref(props.filters.role || 'Semua');
const searchQuery  = ref(props.filters.search || '');

const confirmDelete  = ref(null);
const confirmToggle  = ref(null);
const isDeleting     = ref(false);

// ── Navigasi filter / search ──────────────────────────────────────────────────
let searchTimer = null;

watch(activeFilter, (newRole) => {
    router.get(route('admin.user-management'), {
        search: searchQuery.value || undefined,
        role:   newRole !== 'Semua' ? newRole : undefined,
    }, { preserveState: true, replace: true });
});

const applySearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.user-management'), {
            search: searchQuery.value || undefined,
            role:   activeFilter.value !== 'Semua' ? activeFilter.value : undefined,
        }, { preserveState: true, replace: true });
    }, 400);
};

// ── Aksi suspend / aktifkan ───────────────────────────────────────────────────
const openConfirmToggle = (user) => {
    if (user.role === 'admin') return;
    confirmToggle.value = user;
};

const executeToggleStatus = () => {
    if (!confirmToggle.value) return;
    router.patch(route('admin.user-management.toggle-status', confirmToggle.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => { 
            confirmToggle.value = null; 
        },
    });
};

// ── Aksi hapus ────────────────────────────────────────────────────────────────
const openConfirmDelete = (user) => {
    if (user.role === 'admin') return; // Admin tidak dapat dihapus
    confirmDelete.value = user;
};

const handleDeleteClick = (user) => {
    openConfirmDelete(user);
};

const deleteUser = () => {
    if (!confirmDelete.value) return;
    isDeleting.value = true;
    router.delete(route('admin.user-management.destroy', confirmDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => { confirmDelete.value = null; isDeleting.value = false; },
        onError:   () => { isDeleting.value = false; },
    });
};

// ── Helper ────────────────────────────────────────────────────────────────────
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
const isOwner    = (user) => !!user.owner_profile;
const isActive   = (user) => user.status === 'active';
const isAdminRole = (user) => user.role === 'admin';

const translateRole = (role) => {
    const map = { customer: 'Penyewa', admin: 'Admin' };
    return map[role] || role;
};

const translateOwnerStatus = (status) => {
    const map = { verified: 'Terverifikasi', pending: 'Menunggu', rejected: 'Ditolak' };
    return map[status] || status;
};

const translateGender = (gender) => {
    const map = { male: 'Laki-laki', female: 'Perempuan' };
    return map[gender] || gender;
};
const userRoleBadge = (user) => {
    if (user.role === 'admin') return { label: 'Admin', class: 'bg-purple-50 text-purple-700 border-purple-200' };
    if (user.owner_profile?.status === 'verified') return { label: 'Pemilik Aset', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
    return { label: 'Penyewa', class: 'bg-slate-50 text-slate-700 border-slate-200' };
};
</script>

<template>
    <Head title="Akun Penyewa & Pemilik - Admin Panel" />

    <DashboardLayout 
        role="Admin" 
        title="Akun Penyewa & Pemilik" 
        description="Kelola data pengguna, verifikasi akun, dan pantau status aktif."
    >
        <div class="space-y-6 mt-6">

            <!-- METRIC SUMMARY STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Total Pengguna -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Total Pengguna</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.total }}</p>
                </div>

                <!-- Total Owner -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Total Pemilik</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.owner }}</p>
                </div>

                <!-- Total Customer -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Total Penyewa</p>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.customer }}</p>
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
                                { label: 'Semua Peran', value: 'Semua' },
                                { label: 'Pemilik', value: 'Pemilik' },
                                { label: 'Penyewa', value: 'Penyewa' },
                                { label: 'Admin', value: 'Admin' }
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
                                    <th class="py-4 px-6">Pengguna</th>
                                    <th class="py-4 px-4">Peran</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50/60 transition-colors group">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div v-if="user.avatar" class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-slate-200">
                                                <img :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                                            </div>
                                            <div v-else class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-slate-200 bg-slate-50 flex items-center justify-center">
                                                <AvatarMale v-if="user.gender === 'male'" class="w-full h-full" />
                                                <AvatarFemale v-else-if="user.gender === 'female'" class="w-full h-full" />
                                                <AvatarDefault v-else class="w-full h-full" />
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ user.name }}</p>
                                                <p class="text-[10px] text-slate-400">{{ user.email }} • {{ user.phone || '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span :class="['inline-flex items-center rounded border px-3 py-1 text-xs font-bold', userRoleBadge(user).class]">
                                            {{ userRoleBadge(user).label }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded px-2.5 py-1 text-xs font-bold',
                                            isActive(user) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ isActive(user) ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <Link :href="route('admin.user-management.show', user.id)" class="inline-block rounded bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 transition group-hover:bg-slate-200 group-hover:text-slate-900 border border-slate-200">
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="4" class="py-16 text-center">
                                        <EmptyStateIcon class="w-32 h-32 mx-auto mb-4 opacity-80" />
                                        <h3 class="text-slate-800 font-bold text-base mb-1">Belum Ada Data Pengguna</h3>
                                        <p class="text-slate-500 font-medium text-xs max-w-md mx-auto">
                                            Data tabel pengguna masih kosong. Pengguna baru akan otomatis muncul di sini setelah mereka mendaftar. Silakan hapus atau ubah filter jika Anda sedang mencari data tertentu.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="flex items-center justify-between text-xs text-slate-500 pt-2">
                <span>Menampilkan {{ users.from }}–{{ users.to }} dari {{ users.total }} pengguna</span>
                <div class="flex items-center gap-1">
                    <Link
                        v-if="users.prev_page_url"
                        :href="users.prev_page_url"
                        preserve-state
                        class="rounded border border-slate-200 px-3 py-1.5 hover:bg-slate-50 hover:text-slate-700 transition flex items-center gap-1 font-semibold"
                    >
                        <ChevronLeft class="w-3.5 h-3.5" /> Prev
                    </Link>
                    <span class="px-3 py-1.5 font-bold text-[#0A2540]">{{ users.current_page }} / {{ users.last_page }}</span>
                    <Link
                        v-if="users.next_page_url"
                        :href="users.next_page_url"
                        preserve-state
                        class="rounded border border-slate-200 px-3 py-1.5 hover:bg-slate-50 hover:text-slate-700 transition flex items-center gap-1 font-semibold"
                    >
                        Next <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
        </div>



        <ConfirmModal
            :show="!!confirmDelete"
            type="danger"
            title="Hapus Akun Pengguna?"
            :message="confirmDelete ? `Akun ${confirmDelete.name} akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.` : ''"
            confirmText="Ya, Hapus"
            cancelText="Batal"
            @confirm="deleteUser"
            @cancel="confirmDelete = null"
        />

        <ConfirmModal
            :show="!!confirmToggle"
            :type="confirmToggle && isActive(confirmToggle) ? 'danger' : 'primary'"
            :title="confirmToggle && isActive(confirmToggle) ? 'Nonaktifkan Akun?' : 'Aktifkan Akun?'"
            :message="confirmToggle ? `Apakah Anda yakin ingin ${isActive(confirmToggle) ? 'menonaktifkan' : 'mengaktifkan'} akun ${confirmToggle.name}?` : ''"
            :confirmText="confirmToggle && isActive(confirmToggle) ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
            cancelText="Batal"
            @confirm="executeToggleStatus"
            @cancel="confirmToggle = null"
        >
            <template #icon>
                <div class="w-32 mx-auto mb-2">
                    <ToggleStatusIllustration class="w-full h-auto" />
                </div>
            </template>
        </ConfirmModal>
    </DashboardLayout>
</template>
