<script setup>
import { Search, FolderOpen, X, Users, ShieldCheck, UserCheck, UserMinus, Plus } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';

const props = defineProps({
    admins: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const searchQuery = ref('');
const selectedRole = ref('Semua Peran');
const selectedStatus = ref('Semua Status');

const totalAdmins = computed(() => props.admins?.length || 0);
const activeAdmins = computed(() => props.admins?.filter(a => a.status === 'Aktif').length || 0);
const superAdmins = computed(() => props.admins?.filter(a => a.role === 'Super Admin').length || 0);
const inactiveAdmins = computed(() => props.admins?.filter(a => a.status !== 'Aktif').length || 0);

const filteredAdmins = computed(() => {
    return props.admins.filter(admin => {
        const matchesSearch = [admin.name, admin.email, admin.role]
            .join(' ')
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());
            
        const matchesRole = selectedRole.value === 'Semua Peran' || admin.role === selectedRole.value;
        const matchesStatus = selectedStatus.value === 'Semua Status' || admin.status === selectedStatus.value;
        
        return matchesSearch && matchesRole && matchesStatus;
    });
});

const isAdding = ref(false);
const form = useForm({
    name: '',
    email: '',
    role: 'Admin',
    status: 'Aktif',
});

const openAddAdmin = () => {
    isAdding.value = true;
    form.reset();
    form.role = 'Admin';
    form.status = 'Aktif';
    form.clearErrors();
};

const closeAddAdmin = () => {
    isAdding.value = false;
};

const addAdmin = () => {
    form.post(route('admin.admin-accounts.store'), {
        onSuccess: () => {
            closeAddAdmin();
        },
        preserveScroll: true,
    });
};

</script>

<template>
    <Head title="Akun Administrator - Admin Panel" />

    <DashboardLayout 
        role="Admin" 
        title="Akun Administrator" 
        description="Pantau dan kelola hak akses akun administrator sistem."
    >
        <template #action>
            <button
                @click="openAddAdmin"
                class="bg-[#FFC000] hover:bg-[#e5ac00] text-[#0A2540] font-bold px-5 py-2.5 rounded shadow-sm hover:shadow transition flex items-center justify-center gap-2 text-sm w-fit"
            >
                <Plus class="" />
                <span>Tambah Akun Admin</span>
            </button>
        </template>

        <div class="space-y-6 mt-6">
            
            <!-- METRIC SUMMARY STATS - Clean Panel Design -->
            <div class="bg-white border border-slate-200/80 rounded-xl shadow-sm mb-6">
                <div class="grid grid-cols-2 xl:grid-cols-4 border-slate-100">
                    <!-- Total Admin -->
                    <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-b xl:border-b-0 border-slate-100">
                        <p class="text-xs text-slate-500 font-medium tracking-wide mb-1 flex items-start gap-2">
                            <Users class="text-slate-400 mt-0.5 w-4 h-4" /> <span>Total Admin</span>
                        </p>
                        <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ totalAdmins }}</p>
                    </div>

                    <!-- Admin Aktif -->
                    <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-b xl:border-b-0 xl:border-r border-slate-100">
                        <p class="text-xs text-slate-500 font-medium tracking-wide mb-1 flex items-start gap-2">
                            <UserCheck class="text-emerald-500 mt-0.5 w-4 h-4" /> <span>Admin Aktif</span>
                        </p>
                        <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ activeAdmins }}</p>
                    </div>

                    <!-- Super Admin -->
                    <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-slate-100">
                        <p class="text-xs text-slate-500 font-medium tracking-wide mb-1 flex items-start gap-2">
                            <ShieldCheck class="text-blue-500 mt-0.5 w-4 h-4" /> <span>Super Admin</span>
                        </p>
                        <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ superAdmins }}</p>
                    </div>

                    <!-- Nonaktif -->
                    <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center">
                        <p class="text-xs text-slate-500 font-medium tracking-wide mb-1 flex items-start gap-2">
                            <UserMinus class="text-rose-500 mt-0.5 w-4 h-4" /> <span>Tidak Aktif</span>
                        </p>
                        <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ inactiveAdmins }}</p>
                    </div>
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
                            type="text"
                            placeholder="Cari nama atau email admin..."
                            class="w-full bg-slate-50 border border-slate-200 text-sm pl-10 pr-4 py-2.5 rounded focus:outline-none focus:bg-white hover:border-[#FFC000] focus:border-[#FFC000] focus:ring-2 focus:ring-[#FFC000]/20 transition-all text-slate-700 placeholder:text-slate-400"
                        />
                    </div>

                    <!-- Dropdowns -->
                    <div class="flex items-center gap-3 w-full lg:w-auto justify-between lg:justify-end flex-wrap sm:flex-nowrap">
                        <CustomSelect
                            v-model="selectedRole"
                            :options="[
                                { label: 'Semua Peran', value: 'Semua Peran' },
                                { label: 'Super Admin', value: 'Super Admin' },
                                { label: 'Admin Konten', value: 'Admin Konten' },
                                { label: 'Admin Support', value: 'Admin Support' },
                                { label: 'Admin', value: 'Admin' }
                            ]"
                        />

                        <CustomSelect
                            v-model="selectedStatus"
                            :options="[
                                { label: 'Semua Status', value: 'Semua Status' },
                                { label: 'Aktif', value: 'Aktif' },
                                { label: 'Tidak Aktif', value: 'Tidak Aktif' }
                            ]"
                        />
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div>
                <div v-if="page.props.flash && page.props.flash.success" class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ page.props.flash.success }}
                </div>
                
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-[940px] w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 uppercase font-bold text-xs tracking-wider">
                                    <th class="py-4 px-6">Nama Admin</th>
                                    <th class="py-4 px-4">Email</th>
                                    <th class="py-4 px-4">Peran</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-4">Bergabung</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="admin in filteredAdmins" :key="admin.id" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-[#0A2540]">{{ admin.name }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">{{ admin.email }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-700">
                                            {{ admin.role }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded px-2.5 py-1 text-xs font-bold',
                                            admin.status === 'Aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ admin.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-slate-500 whitespace-nowrap text-xs">{{ admin.joined }}</td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <button class="rounded bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-slate-200 hover:text-slate-900 border border-slate-200">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredAdmins.length === 0">
                                    <td colspan="6" class="py-16 text-center">
                                        <FolderOpen class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                                        <p class="text-slate-500 font-medium">Belum ada akun administrator yang cocok.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL TAMBAH ADMIN -->
        <div v-if="isAdding" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-lg rounded-xl bg-white border border-slate-100 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-black text-[#0A2540]">Tambah Akun Administrator</h3>
                        <p class="text-xs text-slate-500 mt-1">Isi nama, email, dan peran administrator baru.</p>
                    </div>
                    <button @click="closeAddAdmin" class="w-8 h-8 rounded bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition flex items-center justify-center">
                        <X class="w-4 h-4" />
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 gap-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Nama Lengkap</label>
                        <input v-model="form.name" type="text" class="w-full rounded border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20 focus:border-[#FFC000] transition-colors" placeholder="Masukkan nama admin" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                        <input v-model="form.email" type="email" class="w-full rounded border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20 focus:border-[#FFC000] transition-colors" placeholder="admin@kitasewa.id" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Peran</label>
                            <select v-model="form.role" class="w-full rounded border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20 focus:border-[#FFC000] transition-colors appearance-none custom-select-bg">
                                <option>Super Admin</option>
                                <option>Admin Konten</option>
                                <option>Admin Support</option>
                                <option>Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Status</label>
                            <select v-model="form.status" class="w-full rounded border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20 focus:border-[#FFC000] transition-colors appearance-none custom-select-bg">
                                <option>Aktif</option>
                                <option>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-5 border-t border-slate-100 mt-6">
                        <button @click="closeAddAdmin" class="rounded bg-white border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">Batal</button>
                        <button @click="addAdmin" class="rounded bg-[#0A2540] hover:bg-slate-900 px-5 py-2.5 text-sm font-bold text-white transition shadow-sm">Simpan Akun</button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.custom-select-bg {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
}
</style>
