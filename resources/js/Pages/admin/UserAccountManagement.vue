<script setup>
import { Search, FolderOpen, X, ShieldOff, ShieldCheck, Trash2, ChevronLeft, ChevronRight, ShieldAlert } from 'lucide-vue-next';
import { ref } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';

const props = defineProps({
    users:   { type: Object, default: () => ({ data: [] }) },
    stats:   { type: Object, default: () => ({ total: 0, active: 0, inactive: 0 }) },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();

const activeFilter = ref(props.filters.role || 'Semua');
const searchQuery  = ref(props.filters.search || '');
const filters      = ['Semua', 'Pemilik', 'Penyewa'];

const selectedUser   = ref(null);
const confirmDelete  = ref(null);
const confirmToggle  = ref(null);
const isDeleting     = ref(false);

// ── Navigasi filter / search ──────────────────────────────────────────────────
let searchTimer = null;
const applyFilter = (role) => {
    activeFilter.value = role;
    router.get(route('admin.user-management'), {
        search: searchQuery.value,
        role:   role !== 'Semua' ? role : undefined,
    }, { preserveState: true, replace: true });
};

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
            selectedUser.value = null; 
        },
    });
};

// ── Aksi hapus ────────────────────────────────────────────────────────────────
const openConfirmDelete = (user) => {
    if (user.role === 'admin') return; // Admin tidak dapat dihapus
    confirmDelete.value = user;
};

const handleDeleteClick = () => {
    if (selectedUser.value) {
        openConfirmDelete(selectedUser.value);
        selectedUser.value = null;
    }
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
const ownerBadge = (user) => {
    const s = user.owner_profile?.status;
    if (!s) return null;
    const map = { verified: { label: 'Owner Terverifikasi', class: 'bg-emerald-50 text-emerald-700 border-emerald-200/60' },
                  pending:  { label: 'Owner Pending',       class: 'bg-amber-50 text-amber-700 border-amber-200/60' },
                  rejected: { label: 'Owner Ditolak',       class: 'bg-rose-50 text-rose-700 border-rose-200/60' } };
    return map[s] ?? null;
};
</script>

<template>
    <Head title="Akun Penyewa & Pemilik - Admin Panel" />

    <DashboardLayout role="Admin" title="Akun Penyewa & Pemilik" description="Kelola data pengguna, verifikasi akun, dan pantau status aktif.">
        <template #header-actions>
            <div class="flex items-center gap-3 w-64 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60 focus-within:border-[#FFC000] focus-within:ring-1 focus-within:ring-[#FFC000] transition-all">
                <Search class="text-slate-400 w-3.5 h-3.5 shrink-0" />
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="applySearch"
                    placeholder="Cari nama, email..."
                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                />
            </div>
        </template>

        <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">

            <!-- Flash messages -->
            <div v-if="page.props.flash?.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 flex items-center gap-2">
                <ShieldAlert class="w-4 h-4 shrink-0" />
                {{ page.props.flash.error }}
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Total Pengguna</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ stats.total }}</p>
                    <p class="text-[11px] text-slate-500 mt-2">Semua akun pemilik dan penyewa</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Akun Aktif</p>
                    <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ stats.active }}</p>
                    <p class="text-[11px] text-slate-500 mt-2">Akun yang dapat melakukan login</p>
                </div>
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase text-slate-400">Akun Nonaktif</p>
                    <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ stats.inactive }}</p>
                    <p class="text-[11px] text-slate-500 mt-2">Akun yang sedang dibekukan</p>
                </div>
            </div>

            <!-- Filter tabs -->
            <div class="flex items-center justify-end">
                <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200/80 text-xs shadow-xs">
                    <button
                        v-for="filter in filters"
                        :key="filter"
                        @click="applyFilter(filter)"
                        :class="[
                            'px-3.5 py-1.5 rounded-lg font-semibold transition',
                            activeFilter === filter ? 'bg-slate-900 text-[#FFC000] shadow-xs' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'
                        ]"
                    >
                        {{ filter }}
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-[940px] w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                <th class="py-4 px-6">Nama Pengguna</th>
                                <th class="py-4 px-4">Email</th>
                                <th class="py-4 px-4">No Telp</th>
                                <th class="py-4 px-4">Peran</th>
                                <th class="py-4 px-4">Status</th>
                                <th class="py-4 px-6 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in users.data" :key="user.id" @click="selectedUser = user" class="hover:bg-slate-50/60 transition-colors cursor-pointer group">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-900">{{ user.name }}</p>
                                </td>
                                <td class="py-4 px-4 text-slate-600 font-medium">{{ user.email }}</td>
                                <td class="py-4 px-4 text-slate-500 whitespace-nowrap font-medium">{{ user.phone || '-' }}</td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span v-if="ownerBadge(user)"
                                        :class="['inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-bold', ownerBadge(user).class]">
                                        {{ ownerBadge(user).label }}
                                    </span>
                                    <span v-else class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-bold text-slate-700">
                                        Penyewa
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                        isActive(user) ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/70' : 'bg-rose-50 text-rose-600 border border-rose-200/70'
                                    ]">
                                        {{ isActive(user) ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <button class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 group-hover:text-slate-600 group-hover:bg-slate-200/50 transition">
                                        <ChevronRight class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <FolderOpen class="w-6 h-6 mb-2 block mx-auto text-slate-300" />
                                    Tidak ada akun yang cocok dengan filter atau pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ users.from }}–{{ users.to }} dari {{ users.total }} pengguna</span>
                <div class="flex items-center gap-1">
                    <Link
                        v-if="users.prev_page_url"
                        :href="users.prev_page_url"
                        preserve-state
                        class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1"
                    >
                        <ChevronLeft class="w-3.5 h-3.5" /> Prev
                    </Link>
                    <span class="px-3 py-1.5 font-semibold text-slate-700">{{ users.current_page }} / {{ users.last_page }}</span>
                    <Link
                        v-if="users.next_page_url"
                        :href="users.next_page_url"
                        preserve-state
                        class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1"
                    >
                        Next <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- MODAL: Detail & Suspend -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="selectedUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="selectedUser = null"></div>
                <div class="relative w-full max-w-3xl rounded-xl bg-white border border-slate-100 shadow-2xl flex flex-col max-h-[90vh]">
                    <div class="flex items-center justify-between p-5 border-b border-slate-100 shrink-0">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Detail Akun Pengguna</h3>
                            <p class="text-xs text-slate-500">Informasi lengkap akun penyewa / pemilik</p>
                        </div>
                        <button @click="selectedUser = null" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-700 transition flex items-center justify-center">
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                    
                    <div class="p-6 overflow-y-auto space-y-6">
                        <!-- Basic Info -->
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 mb-3 border-b border-slate-100 pb-2">Informasi Akun Utama</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-slate-50 rounded-xl p-4 border border-slate-100">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Nama Lengkap</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.name }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Email</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.email }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Telepon</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.phone || '-' }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Jenis Kelamin</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm capitalize">{{ translateGender(selectedUser.gender) || '-' }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Tanggal Lahir</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.date_of_birth ? formatDate(selectedUser.date_of_birth) : '-' }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Status Akun</span>
                                    <p class="mt-1 font-semibold text-sm" :class="isActive(selectedUser) ? 'text-emerald-600' : 'text-rose-600'">
                                        {{ isActive(selectedUser) ? 'Aktif' : 'Nonaktif' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Peran</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm capitalize">{{ translateRole(selectedUser.role) }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Bergabung Pada</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ formatDate(selectedUser.created_at) }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Login Terakhir</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.last_login_at ? formatDate(selectedUser.last_login_at) : '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Owner Profile -->
                        <div v-if="selectedUser.owner_profile">
                            <h4 class="text-sm font-bold text-slate-800 mb-3 border-b border-slate-100 pb-2">Profil Pemilik Tambahan</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-amber-50/50 rounded-xl p-4 border border-amber-100/50">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">NIK (No. KTP)</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.owner_profile.national_id || '-' }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Status Verifikasi</span>
                                    <p class="mt-1 font-semibold text-amber-600 text-sm capitalize">{{ translateOwnerStatus(selectedUser.owner_profile.status) }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Kewarganegaraan</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.owner_profile.nationality || '-' }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Agama</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.owner_profile.religion || '-' }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Status Pernikahan</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.owner_profile.marital_status || '-' }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Pekerjaan</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.owner_profile.occupation || '-' }}</p>
                                </div>
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Alamat Lengkap</span>
                                    <p class="mt-1 font-semibold text-slate-900 text-sm">{{ selectedUser.owner_profile.address || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center flex-wrap gap-3 mt-4 pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                                <button
                                    @click="openConfirmToggle(selectedUser)"
                                    :class="[
                                        'flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition',
                                        isActive(selectedUser)
                                            ? 'bg-amber-50 text-amber-600 hover:bg-amber-100'
                                            : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'
                                    ]"
                                >
                                    <ShieldOff v-if="isActive(selectedUser)" class="w-4 h-4" />
                                    <ShieldCheck v-else class="w-4 h-4" />
                                    {{ isActive(selectedUser) ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <button
                                    v-if="!isAdminRole(selectedUser)"
                                    @click="handleDeleteClick"
                                    class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold bg-rose-50 text-rose-600 hover:bg-rose-100 transition"
                                >
                                    <Trash2 class="w-4 h-4" />
                                    Hapus
                                </button>
                            </div>
                            <button @click="selectedUser = null" class="rounded-xl bg-slate-900 px-6 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

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
        />
    </DashboardLayout>
</template>
