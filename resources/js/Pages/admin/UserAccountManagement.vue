<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const activeFilter = ref('Semua');
const searchQuery = ref('');
const filters = ['Semua', 'Pemilik', 'Penyewa'];

const users = ref([
    { id: 1, name: 'Agus Santoso', email: 'agus.santoso@mail.com', role: 'Pemilik', status: 'Aktif', joined: '1 Agustus 2026' },
    { id: 2, name: 'Nadia Lestari', email: 'nadia.lestari@mail.com', role: 'Penyewa', status: 'Aktif', joined: '28 Juli 2026' },
    { id: 3, name: 'Budi Pratama', email: 'budi.pratama@mail.com', role: 'Pemilik', status: 'Tidak Aktif', joined: '15 Juli 2026' },
    { id: 4, name: 'Dewi Arum', email: 'dewi.arum@mail.com', role: 'Penyewa', status: 'Aktif', joined: '9 Juli 2026' },
    { id: 5, name: 'Rio Suherman', email: 'rio.suherman@mail.com', role: 'Pemilik', status: 'Aktif', joined: '3 Juni 2026' },
]);

const selectedUser = ref(null);

const filteredUsers = computed(() => {
    return users.value.filter(user => {
        const matchesFilter = activeFilter.value === 'Semua' || user.role === activeFilter.value;
        const matchesSearch = [user.name, user.email, user.role]
            .join(' ')
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());
        return matchesFilter && matchesSearch;
    });
});

const activeCount = computed(() => users.value.filter(user => user.status === 'Aktif').length);
const inactiveCount = computed(() => users.value.filter(user => user.status !== 'Aktif').length);

const openUserDetail = (user) => {
    selectedUser.value = user;
};

const closeUserDetail = () => {
    selectedUser.value = null;
};
</script>

<template>
    <Head title="Akun Penyewa & Pemilik - Admin Panel" />

    <DashboardLayout role="Admin" title="Akun Penyewa & Pemilik" description="Kelola data pengguna, verifikasi akun, dan pantau status aktif.">
        <template #header-actions>
            <div class="flex items-center gap-3 w-64 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Cari nama, email..."
                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                />
            </div>
        </template>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-4">

                    <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200/80 text-xs shadow-xs">
                        <button
                            v-for="filter in filters"
                            :key="filter"
                            @click="activeFilter = filter"
                            :class="[
                                'px-3.5 py-1.5 rounded-lg font-semibold transition',
                                activeFilter === filter ? 'bg-slate-900 text-[#FFC000] shadow-xs' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'
                            ]"
                        >
                            {{ filter }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Total Pengguna</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ users.length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Semua akun pemilik dan penyewa</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Akun Aktif</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ activeCount }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Akun yang dapat melakukan login</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Akun Nonaktif</p>
                        <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ inactiveCount }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Akun yang sedang dibekukan</p>
                    </div>
                </div>

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
                                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900">{{ user.name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ user.email }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-slate-600 font-medium">{{ user.email }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-bold text-slate-700">
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            user.status === 'Aktif' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/70' : 'bg-rose-50 text-rose-600 border border-rose-200/70'
                                        ]">
                                            {{ user.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-slate-500 whitespace-nowrap">{{ user.joined }}</td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <button
                                            @click="openUserDetail(user)"
                                            class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white transition hover:bg-slate-800 shadow-sm"
                                        >
                                            Detail Akun
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredUsers.length === 0">
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-folder-open text-2xl mb-2 block text-slate-300"></i>
                                        Tidak ada akun yang cocok dengan filter atau pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        <div v-if="selectedUser" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-xl rounded-3xl bg-white border border-slate-100 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Detail Akun Pengguna</h3>
                        <p class="text-xs text-slate-500">Informasi lengkap akun penyewa / pemilik</p>
                    </div>
                    <button @click="closeUserDetail" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-500 hover:text-slate-700 transition flex items-center justify-center">
                        <i class="fa-solid fa-xmark"></i>
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
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Peran</span>
                            <p class="mt-2 font-semibold text-amber-600">{{ selectedUser.role }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Status</span>
                            <p class="mt-2 font-semibold" :class="selectedUser.status === 'Aktif' ? 'text-emerald-600' : 'text-rose-600'">{{ selectedUser.status }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Bergabung</span>
                            <p class="mt-2 font-semibold text-slate-900">{{ selectedUser.joined }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button @click="closeUserDetail" class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
