<script setup>
import { Search, FolderOpen, X, ShieldOff, ShieldCheck, Trash2, ChevronLeft, ChevronRight, ShieldAlert } from 'lucide-vue-next';
import { ref } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

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
const toggleStatus = (user) => {
    if (user.role === 'admin') return; // Admin tidak bisa di-suspend dari halaman ini
    router.patch(route('admin.user-management.toggle-status', user.id), {}, {
        preserveScroll: true,
        onSuccess: () => { selectedUser.value = null; },
    });
};

// ── Aksi hapus ────────────────────────────────────────────────────────────────
const openConfirmDelete = (user) => {
    if (user.role === 'admin') return; // Admin tidak dapat dihapus
    confirmDelete.value = user;
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
                                <th class="py-4 px-4">Peran</th>
                                <th class="py-4 px-4">Status</th>
                                <th class="py-4 px-4">Bergabung</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-900">{{ user.name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ user.phone || '-' }}</p>
                                </td>
                                <td class="py-4 px-4 text-slate-600 font-medium">{{ user.email }}</td>
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
                                <td class="py-4 px-4 text-slate-500 whitespace-nowrap">{{ formatDate(user.created_at) }}</td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            @click="selectedUser = user"
                                            class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white transition hover:bg-slate-800 shadow-sm"
                                        >
                                            Detail
                                        </button>
                                        <!-- Tombol hapus disembunyikan untuk akun admin -->
                                        <span
                                            v-if="isAdminRole(user)"
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200/70 px-3 py-1.5 text-[11px] font-semibold text-amber-600 cursor-not-allowed"
                                            title="Akun administrator tidak dapat dihapus"
                                        >
                                            <ShieldAlert class="w-3 h-3" />
                                            Admin
                                        </span>
                                        <button
                                            v-else
                                            @click="openConfirmDelete(user)"
                                            class="rounded-full bg-rose-50 border border-rose-200/70 px-3 py-1.5 text-[11px] font-semibold text-rose-600 transition hover:bg-rose-100"
                                        >
                                            Hapus
                                        </button>
                                    </div>
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
        <div v-if="selectedUser" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-xl rounded-3xl bg-white border border-slate-100 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Detail Akun Pengguna</h3>
                        <p class="text-xs text-slate-500">Informasi lengkap akun penyewa / pemilik</p>
                    </div>
                    <button @click="selectedUser = null" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-500 hover:text-slate-700 transition flex items-center justify-center">
                        <X class="w-4 h-4" />
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 rounded-3xl p-4 border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Nama Lengkap</span>
                            <p class="mt-2 font-semibold text-slate-900">{{ selectedUser.name }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Email</span>
                            <p class="mt-2 font-semibold text-slate-900">{{ selectedUser.email }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Telepon</span>
                            <p class="mt-2 font-semibold text-slate-900">{{ selectedUser.phone || '-' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Status</span>
                            <p class="mt-2 font-semibold" :class="isActive(selectedUser) ? 'text-emerald-600' : 'text-rose-600'">
                                {{ isActive(selectedUser) ? 'Aktif' : 'Nonaktif' }}
                            </p>
                        </div>
                        <div v-if="ownerBadge(selectedUser)">
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Status Owner</span>
                            <p class="mt-2 font-semibold text-amber-600">{{ ownerBadge(selectedUser).label }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Bergabung</span>
                            <p class="mt-2 font-semibold text-slate-900">{{ formatDate(selectedUser.created_at) }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <button
                            @click="toggleStatus(selectedUser)"
                            :class="[
                                'flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-bold transition',
                                isActive(selectedUser)
                                    ? 'bg-rose-50 text-rose-600 hover:bg-rose-100'
                                    : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'
                            ]"
                        >
                            <ShieldOff v-if="isActive(selectedUser)" class="w-4 h-4" />
                            <ShieldCheck v-else class="w-4 h-4" />
                            {{ isActive(selectedUser) ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                        </button>
                        <button @click="selectedUser = null" class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Konfirmasi Hapus -->
        <div v-if="confirmDelete" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-sm rounded-3xl bg-white border border-slate-100 shadow-2xl p-6 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 flex items-center justify-center shrink-0">
                        <Trash2 class="w-5 h-5 text-rose-500" />
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Hapus Akun Pengguna?</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Akun <span class="font-semibold text-slate-800">{{ confirmDelete.name }}</span> akan dihapus permanen.
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="confirmDelete = null" class="rounded-2xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button
                        @click="deleteUser"
                        :disabled="isDeleting"
                        class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700 disabled:opacity-60 transition"
                    >
                        {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
