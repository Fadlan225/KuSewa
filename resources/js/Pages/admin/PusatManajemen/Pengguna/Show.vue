<script setup>
import { ShieldOff, ShieldCheck, Trash2, ChevronLeft, ShieldAlert, BadgeCheck, FolderOpen } from 'lucide-vue-next';
import { ref } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import AvatarMale from '@/Components/ui/Icons/AvatarMale.vue';
import AvatarFemale from '@/Components/ui/Icons/AvatarFemale.vue';
import AvatarDefault from '@/Components/ui/Icons/AvatarDefault.vue';
import EmptyStateData from '@/Components/ui/Icons/EmptyStateData.vue';
import ToggleStatusIllustration from '@/Components/ui/Icons/ToggleStatusIllustration.vue';

const props = defineProps({
    user: Object
});

const page = usePage();

const confirmDelete  = ref(false);
const confirmToggle  = ref(false);
const isDeleting     = ref(false);

const executeToggleStatus = () => {
    router.post(route('admin.user-management.toggle-status', props.user.id), {
        _method: 'patch'
    }, {
        preserveScroll: true,
        onSuccess: () => { confirmToggle.value = false; },
    });
};

const deleteUser = () => {
    isDeleting.value = true;
    router.post(route('admin.user-management.destroy', props.user.id), {
        _method: 'delete'
    }, {
        onSuccess: () => {
            router.get(route('admin.user-management'));
        },
        onError: () => {
            isDeleting.value = false;
            confirmDelete.value = false;
        },
    });
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
const isActive   = (u) => u.status === 'active';
const isAdminRole = (u) => u.role === 'admin';

const userRoleBadge = (user) => {
    if (user.role === 'admin') return { label: 'Admin', class: 'bg-purple-50 text-purple-700 border-purple-200' };
    if (user.owner_profile?.status === 'verified') return { label: 'Pemilik', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
    return { label: 'Penyewa', class: 'bg-slate-50 text-slate-700 border-slate-200' };
};

const translateGender = (gender) => {
    const map = { male: 'Laki-laki', female: 'Perempuan' };
    return map[gender] || gender;
};
</script>

<template>
    <Head title="Detail Pengguna - Admin Panel" />

    <DashboardLayout 
        role="Admin" 
        title="Manajemen Pengguna" 
        description="Detail profil penyewa atau pemilik aset."
    >
        <template #leftAction>
            <Link :href="route('admin.user-management')" class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-[#0A2540] transition-colors mr-3 group">
                <ChevronLeft class="w-4 h-4 mr-1 transition-transform group-hover:-translate-x-0.5" />
                Kembali ke Manajemen Pengguna
            </Link>
        </template>

        <div class="space-y-6 mt-6 max-w-5xl mx-auto">

            <!-- HEADER PROFILE -->
            <div class="flex flex-col md:flex-row items-start gap-6 bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 relative">
                <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border border-slate-200 bg-slate-50 flex items-center justify-center">
                    <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                    <AvatarMale v-else-if="user.gender === 'male'" class="w-full h-full" />
                    <AvatarFemale v-else-if="user.gender === 'female'" class="w-full h-full" />
                    <AvatarDefault v-else class="w-full h-full" />
                </div>
                
                <div class="flex-1 flex flex-col sm:flex-row justify-between gap-6 w-full">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-black text-[#0A2540]">{{ user.name }}</h2>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold" :class="isActive(user) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="isActive(user) ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                {{ isActive(user) ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        
                        <p class="text-slate-500 text-sm mt-1 font-medium">{{ user.email }}</p>
                        
                        <div class="mt-2.5 flex items-center gap-2">
                            <span :class="['inline-flex items-center rounded border px-3 py-1 text-xs font-bold', userRoleBadge(user).class]">
                                {{ userRoleBadge(user).label }} 
                                <BadgeCheck v-if="user.owner_profile?.status === 'verified'" class="w-3.5 h-3.5 ml-1.5 inline-block text-emerald-600" />
                            </span>
                            <span class="text-xs font-bold text-slate-400 bg-slate-50 border border-slate-100 rounded px-2.5 py-1">ID Pengguna #{{ user.id }}</span>
                        </div>
                        
                        <div class="mt-5 flex flex-wrap items-center gap-x-8 gap-y-2 text-sm font-semibold text-slate-500">
                            <div>Bergabung <span class="text-slate-900">{{ formatDate(user.created_at) }}</span></div>
                            <div>Login terakhir <span class="text-slate-900">{{ user.last_login_at ? formatDate(user.last_login_at) : '-' }}</span></div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:items-end justify-end gap-3 shrink-0">
                        <div class="flex gap-2 w-full sm:w-auto">
                            <button
                                @click="confirmToggle = true"
                                :class="[
                                    'flex-1 sm:flex-none flex items-center justify-center gap-2 rounded px-4 py-2 text-xs font-bold transition shadow-sm border',
                                    isActive(user)
                                        ? 'bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100'
                                        : 'bg-emerald-50 text-emerald-600 border-emerald-200 hover:bg-emerald-100'
                                ]"
                            >
                                <ShieldOff v-if="isActive(user)" class="w-3.5 h-3.5" />
                                <ShieldCheck v-else class="w-3.5 h-3.5" />
                                {{ isActive(user) ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                            </button>
                            <button
                                v-if="!isAdminRole(user)"
                                @click="confirmDelete = true"
                                class="flex items-center justify-center w-8 h-8 rounded bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100 transition shadow-sm shrink-0"
                                title="Hapus Permanen"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2-COLUMNS: INFORMASI AKUN & RINGKASAN AKTIVITAS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Informasi Akun -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50 shrink-0">
                        <h3 class="text-sm font-bold text-slate-800">Informasi Akun</h3>
                    </div>
                    <div class="p-6 flex-1">
                        <div class="space-y-4">
                            <div class="grid grid-cols-[110px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nama</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ user.name }}</div>
                            </div>
                            <div class="grid grid-cols-[110px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Email</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ user.email }}</div>
                            </div>
                            <div class="grid grid-cols-[110px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">No. Telepon</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ user.phone || '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[110px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900 capitalize">{{ translateGender(user.gender) || '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[110px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tempat Lahir</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ user.place_of_birth || '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[110px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Lahir</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ user.date_of_birth ? formatDate(user.date_of_birth) : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ringkasan Aktivitas -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50 shrink-0">
                        <h3 class="text-sm font-bold text-slate-800">Ringkasan Aktivitas</h3>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div v-if="user.owner_profile" class="space-y-4">
                            <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                                <div class="text-sm font-bold text-slate-500">Total aset</div>
                                <div class="text-xl font-black text-[#0A2540]">{{ user.owner_profile.total_assets_count || 0 }}</div>
                            </div>
                            <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                                <div class="text-sm font-bold text-slate-500">Aset aktif</div>
                                <div class="text-xl font-black text-emerald-600">{{ user.owner_profile.active_assets_count || 0 }}</div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-bold text-slate-500">Pengajuan</div>
                                <div class="text-xl font-black text-amber-600">{{ user.owner_profile.pending_assets_count || 0 }}</div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center flex-1 p-6 text-center h-full min-h-[200px]">
                            <EmptyStateData class="w-24 h-24 mb-3 opacity-80" />
                            <h4 class="text-sm font-bold text-slate-800 mb-1">Belum Ada Riwayat Aktivitas Aset</h4>
                            <p class="text-xs font-medium text-slate-500 max-w-[250px] mx-auto leading-relaxed">
                                Pengguna ini belum mendaftarkan atau menyewa aset apapun. Data ringkasan aktivitas akan otomatis tampil ketika pengguna mulai bertransaksi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROFIL PEMILIK -->
            <div v-if="user.owner_profile" class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">Profil Pemilik</h3>
                    <span v-if="user.owner_profile.status === 'verified'" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <BadgeCheck class="w-3.5 h-3.5" />
                        Terverifikasi
                    </span>
                    <span v-else-if="user.owner_profile.status === 'pending'" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Menunggu Verifikasi
                    </span>
                    <span v-else class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        Verifikasi Ditolak
                    </span>
                </div>
                <div class="p-6 relative">
                    <div class="space-y-4 max-w-2xl">
                        <div class="grid grid-cols-[140px_1fr] sm:grid-cols-[180px_1fr]">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIK</div>
                            <div class="text-sm font-semibold text-slate-900">{{ user.owner_profile.national_id || '-' }}</div>
                        </div>
                        <div class="grid grid-cols-[140px_1fr] sm:grid-cols-[180px_1fr]">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kewarganegaraan</div>
                            <div class="text-sm font-semibold text-slate-900">{{ user.owner_profile.nationality || '-' }}</div>
                        </div>
                        <div class="grid grid-cols-[140px_1fr] sm:grid-cols-[180px_1fr]">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pekerjaan</div>
                            <div class="text-sm font-semibold text-slate-900">{{ user.owner_profile.occupation || '-' }}</div>
                        </div>
                        <div class="grid grid-cols-[140px_1fr] sm:grid-cols-[180px_1fr]">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Pernikahan</div>
                            <div class="text-sm font-semibold text-slate-900">{{ user.owner_profile.marital_status || '-' }}</div>
                        </div>
                        <div class="grid grid-cols-[140px_1fr] sm:grid-cols-[180px_1fr] mt-6 pt-5 border-t border-slate-100">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</div>
                            <div class="text-sm font-semibold text-slate-900 leading-relaxed">{{ user.owner_profile.address || '-' }}</div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <Link :href="route('admin.pengajuan-akun', { search: user.email })" class="inline-flex items-center gap-2 rounded-lg bg-slate-50 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors border border-slate-200">
                            Lihat Detail Verifikasi
                        </Link>
                    </div>
                </div>
            </div>
            
            <div class="h-4"></div>
        </div>

        <ConfirmModal
            :show="confirmDelete"
            type="danger"
            title="Hapus Akun Pengguna?"
            :message="`Akun ${user.name} akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.`"
            confirmText="Ya, Hapus"
            cancelText="Batal"
            @confirm="deleteUser"
            @cancel="confirmDelete = false"
        />

        <ConfirmModal
            :show="confirmToggle"
            :type="isActive(user) ? 'danger' : 'primary'"
            :title="isActive(user) ? 'Nonaktifkan Akun?' : 'Aktifkan Akun?'"
            :message="`Apakah Anda yakin ingin ${isActive(user) ? 'menonaktifkan' : 'mengaktifkan'} akun ${user.name}?`"
            :confirmText="isActive(user) ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
            cancelText="Batal"
            @confirm="executeToggleStatus"
            @cancel="confirmToggle = false"
        >
            <template #icon>
                <div class="w-32 mx-auto mb-2">
                    <ToggleStatusIllustration class="w-full h-auto" />
                </div>
            </template>
        </ConfirmModal>
    </DashboardLayout>
</template>
