<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

// Filter tab status pengajuan
const activeFilter = ref('Pending');
const filters = ['Semua', 'Pending', 'Disetujui', 'Ditolak'];

const searchQuery = ref('');

// Mock Data Pengajuan Akun Owner & Administrator
const applicants = ref([
    { 
        id: 1, 
        name: 'Ahmad Fauzi, S.T.', 
        email: 'ahmad.fauzi@gmail.com', 
        roleTarget: 'Pemilik (Owner)', 
        nik: '6471012345670001', 
        phone: '081234567890', 
        date: '3 Agustus 2026', 
        status: 'Pending',
        propertyType: 'Kos-kosan & Kontrakan',
        ktpUrl: 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=400' 
    },
    { 
        id: 2, 
        name: 'Siti Aminah, M.Kom.', 
        email: 'siti.aminah99@yahoo.com', 
        roleTarget: 'Pemilik (Owner)', 
        nik: '6472023456780002', 
        phone: '085298765432', 
        date: '3 Agustus 2026', 
        status: 'Pending',
        propertyType: 'Apartemen & Ruko',
        ktpUrl: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400' 
    },
    { 
        id: 3, 
        name: 'Rian Pratama', 
        email: 'rian.pratama@kusewa.id', 
        roleTarget: 'Administrator', 
        nik: '6471034567890003', 
        phone: '081122334455', 
        date: '2 Agustus 2026', 
        status: 'Disetujui',
        propertyType: '-',
        ktpUrl: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400' 
    },
    { 
        id: 4, 
        name: 'Joko Susilo', 
        email: 'jokosusilo@outlook.com', 
        roleTarget: 'Pemilik (Owner)', 
        nik: '6473045678900004', 
        phone: '087788990011', 
        date: '1 Agustus 2026', 
        status: 'Ditolak',
        propertyType: 'Gedung / Baliho',
        ktpUrl: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400' 
    },
]);

// Selected applicant for detailed modal view
const selectedApplicant = ref(null);

const filteredApplicants = computed(() => {
    return applicants.value.filter(item => {
        const matchesFilter = activeFilter.value === 'Semua' || item.status === activeFilter.value;
        const matchesSearch = item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                              item.nik.includes(searchQuery.value) || 
                              item.email.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesFilter && matchesSearch;
    });
});

const handleApprove = (id) => {
    const item = applicants.value.find(a => a.id === id);
    if (item) {
        item.status = 'Disetujui';
        selectedApplicant.value = null;
    }
};

const handleReject = (id) => {
    const item = applicants.value.find(a => a.id === id);
    if (item) {
        item.status = 'Ditolak';
        selectedApplicant.value = null;
    }
};
</script>

<template>
    <Head title="Kelola Pengajuan Akun - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">

        <!-- Sidebar Komponen yang dipakai konsisten di semua halaman admin -->
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">

            <!-- Topbar Header -->
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3 w-1/3 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    <input 
                        type="text" 
                        v-model="searchQuery"
                        placeholder="Cari nama, NIK, atau email pengaju..." 
                        class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700" 
                    />
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-regular fa-bell text-sm"></i>
                        <span class="w-2 h-2 bg-rose-500 rounded-full absolute top-2 right-2 ring-2 ring-white"></span>
                    </button>
                    <div class="h-6 w-[1px] bg-slate-200"></div>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-900 text-[#FFC000] flex items-center justify-center font-bold text-xs shadow-sm">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <div class="text-left leading-tight hidden sm:block">
                            <p class="text-xs font-bold text-slate-800">Super Administrator</p>
                            <p class="text-[10px] text-emerald-600 font-semibold">● Online</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Body -->
            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">

                <!-- Title Row & Filter Tabs -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-slate-900 tracking-tight">Kelola Pengajuan Akun</h1>
                            <p class="text-xs text-slate-400">Verifikasi data NIK, dokumen KTP, dan registrasi akun Pemilik atau Administrator baru.</p>
                        </div>
                    </div>

                    <!-- Filter Tabs -->
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

                <!-- Table Container -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-[940px] w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-6">Calon Pengguna</th>
                                    <th class="py-4 px-4">Target Peran</th>
                                    <th class="py-4 px-4">Estimasi Jenis Aset</th>
                                    <th class="py-4 px-4">NIK Terdaftar</th>
                                    <th class="py-4 px-4">Tanggal Pengajuan</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in filteredApplicants" :key="item.id" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-slate-800 uppercase shadow-xs">
                                                {{ item.name.charAt(0) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ item.name }}</p>
                                                <p class="text-[10px] text-slate-400">{{ item.email }} • {{ item.phone }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 font-semibold whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border border-amber-200/60 bg-amber-50 px-3 py-1 text-[10px] font-bold text-amber-700">
                                            {{ item.roleTarget }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-slate-600 font-medium">{{ item.propertyType }}</td>
                                    <td class="py-4 px-4 font-mono text-slate-600 font-medium">{{ item.nik }}</td>
                                    <td class="py-4 px-4 text-slate-500 whitespace-nowrap">{{ item.date }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'Pending' ? 'bg-amber-50 text-amber-600 border border-amber-200/60' :
                                            item.status === 'Disetujui' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/60' :
                                            'bg-rose-50 text-rose-600 border border-rose-200/60'
                                        ]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                            <button 
                                                @click="selectedApplicant = item"
                                                class="rounded-full bg-slate-100 px-4 py-1.5 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-200"
                                            >
                                                Detail KTP
                                            </button>
                                            <button 
                                                v-if="item.status === 'Pending'"
                                                @click="handleApprove(item.id)"
                                                class="rounded-full bg-emerald-600 px-4 py-1.5 text-[11px] font-semibold text-white transition hover:bg-emerald-700"
                                            >
                                                Setujui
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredApplicants.length === 0">
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-folder-open text-2xl mb-2 block text-slate-300"></i>
                                        Tidak ada data pengajuan akun yang sesuai dengan filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

        <!-- MODAL DETAIL KTP & VERIFIKASI -->
        <div v-if="selectedApplicant" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Verifikasi Dokumen NIK & KTP Owner</h3>
                        <p class="text-[10px] text-slate-400">Periksa kesesuaian data diri dengan foto KTP terlampir.</p>
                    </div>
                    <button @click="selectedApplicant = null" class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                        <div>
                            <span class="text-slate-400 text-[10px] block font-medium">Nama Lengkap</span>
                            <p class="font-bold text-slate-900 text-sm mt-0.5">{{ selectedApplicant.name }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block font-medium">Target Peran</span>
                            <p class="font-bold text-amber-600 text-xs mt-0.5">{{ selectedApplicant.roleTarget }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block font-medium">Nomor NIK</span>
                            <p class="font-mono font-bold text-slate-800 mt-0.5">{{ selectedApplicant.nik }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block font-medium">Nomor Telepon / WhatsApp</span>
                            <p class="font-semibold text-slate-800 mt-0.5">{{ selectedApplicant.phone }}</p>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[10px] block mb-1.5 font-bold uppercase tracking-wider">Lampiran Foto KTP Resmi</span>
                        <div class="h-48 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200/80 shadow-inner">
                            <img :src="selectedApplicant.ktpUrl" class="w-full h-full object-cover hover:scale-105 transition duration-300 cursor-zoom-in" />
                        </div>
                    </div>
                </div>

                <div v-if="selectedApplicant.status === 'Pending'" class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button 
                        @click="handleReject(selectedApplicant.id)"
                        class="px-4 py-2.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold transition">
                        Tolak Pengajuan
                    </button>
                </div>
                <div v-else class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <span class="text-sm font-semibold text-slate-500">Pengajuan sudah {{ selectedApplicant.status.toLowerCase() }}.</span>
                </div>
            </div>
        </div>

    </div>
</template>