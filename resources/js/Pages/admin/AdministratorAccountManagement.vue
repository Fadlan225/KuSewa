<script setup>
import { Search, FolderOpen, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    admins: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const searchQuery = ref('');
const filteredAdmins = computed(() => {
    return props.admins.filter(admin => {
        return [admin.name, admin.email, admin.role]
            .join(' ')
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());
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

    <DashboardLayout role="Admin" title="Akun Administrator" description="Tambah dan lihat daftar akun administrator.">
        <template #header-actions>
            <div class="flex items-center gap-3 w-64 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                <Search class="text-slate-400 text-xs" />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Cari nama, email, atau peran..."
                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                />
            </div>
            <button
                @click="openAddAdmin"
                class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition"
            >
                Tambah Akun Admin
            </button>
        </template>

        <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-4">
                <div class="text-xs text-slate-500">Total admin: <span class="font-semibold text-slate-900">{{ props.admins?.length || 0 }}</span></div>
            </div>

            <div>
                <div v-if="page.props.flash && page.props.flash.success" class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ page.props.flash.success }}
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-[940px] w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
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
                                    <p class="font-bold text-slate-900">{{ admin.name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ admin.email }}</p>
                                </td>
                                <td class="py-4 px-4 text-slate-600 font-medium">{{ admin.email }}</td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-bold text-slate-700">
                                        {{ admin.role }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                        admin.status === 'Aktif' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/70' : 'bg-rose-50 text-rose-600 border border-rose-200/70'
                                    ]">
                                        {{ admin.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-slate-500 whitespace-nowrap">{{ admin.joined }}</td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white transition hover:bg-slate-800">
                                        Detail Akun
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredAdmins.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <FolderOpen class="text-2xl mb-2 block text-slate-300" />
                                    Belum ada akun administrator yang cocok.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isAdding" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-lg rounded-3xl bg-white border border-slate-100 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tambah Akun Administrator</h3>
                        <p class="text-xs text-slate-500">Isi nama, email, dan peran administrator baru.</p>
                    </div>
                    <button @click="closeAddAdmin" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-500 hover:text-slate-700 transition flex items-center justify-center">
                        <X class="" />
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 gap-4">
                        <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Nama Lengkap</label>
                        <input v-model="form.name" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20" placeholder="Masukkan nama admin" />
                        <p v-if="form.errors.name" class="mt-2 text-[10px] text-rose-600">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Email</label>
                        <input v-model="form.email" type="email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20" placeholder="admin@kusewa.id" />
                        <p v-if="form.errors.email" class="mt-2 text-[10px] text-rose-600">{{ form.errors.email }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Peran</label>
                            <select v-model="form.role" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20">
                                <option>Super Admin</option>
                                <option>Admin Konten</option>
                                <option>Admin Support</option>
                                <option>Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Status</label>
                            <select v-model="form.status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20">
                                <option>Aktif</option>
                                <option>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button @click="closeAddAdmin" class="rounded-2xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                        <button @click="addAdmin" class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-900 transition">Simpan Akun</button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
