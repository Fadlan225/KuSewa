<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

// Pencarian
const searchQuery = ref('');

// Data Akun Owner
const owners = ref([
    { id: 1, name: 'Budi Santoso', email: 'budi.santoso@example.com', properties: 4, status: 'Aktif', violation: '-' },
    { id: 2, name: 'Siti Aminah', email: 'siti.aminah@example.com', properties: 2, status: 'Dinonaktifkan', violation: 'Menunggak Pembayaran > 30 Hari' },
    { id: 3, name: 'Agus Pratama', email: 'agus.pratama@example.com', properties: 7, status: 'Aktif', violation: '-' },
    { id: 4, name: 'Diana Wijaya', email: 'diana.wijaya@example.com', properties: 1, status: 'Dinonaktifkan', violation: 'Manipulasi Data / Penipuan' },
    { id: 5, name: 'Reza Rahadian', email: 'reza.rahadian@example.com', properties: 3, status: 'Aktif', violation: '-' },
]);

// Filter Data
const filteredOwners = computed(() => {
    return owners.value.filter(owner => {
        return owner.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
               owner.email.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

// Perhitungan Statistik
const totals = computed(() => ({
    all: owners.value.length,
    active: owners.value.filter(o => o.status === 'Aktif').length,
    suspended: owners.value.filter(o => o.status === 'Dinonaktifkan').length,
}));
</script>

<template>
    <Head title="Manajemen Akun Owner - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden selection:bg-[#0A2540] selection:text-white">
        <!-- Sidebar Container -->
        <div class="h-full flex-shrink-0 border-r border-slate-200/70 bg-white shadow-[1px_0_10px_rgba(0,0,0,0.02)] z-40 relative">
            <AdminSidebar />
        </div>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto relative">
            
            <!-- Premium Glassmorphism Header -->
            <header class="h-[72px] bg-white/70 backdrop-blur-md border-b border-slate-200/70 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0 transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0A2540] to-slate-800 text-[#FFC000] flex items-center justify-center text-lg shadow-md">
                        <i class="fa-solid fa-users text-[16px]"></i>
                    </div>
                    <div>
                        <h1 class="text-[16px] font-extrabold text-slate-900 tracking-tight leading-tight">Daftar Akun Owner</h1>
                        <p class="text-[12px] font-medium text-slate-500">Kelola status dan sanksi pemilik aset.</p>
                    </div>
                </div>

                <!-- Search Bar di Header -->
                <div class="flex items-center gap-3 w-full max-w-sm bg-slate-100/50 hover:bg-slate-100 px-4 py-2.5 rounded-xl border border-transparent focus-within:bg-white focus-within:border-slate-300 focus-within:shadow-sm transition-all duration-300">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-[13px]"></i>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari nama atau email..."
                        class="w-full text-[13px] bg-transparent border-none focus:ring-0 p-0 placeholder-slate-400 text-slate-700 font-medium"
                    />
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto pb-24">
                
                <!-- Modern Stats Cards (3 Kolom Sederhana) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                        <p class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Total Owner</p>
                        <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ totals.all }}</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                        <p class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Akun Aktif</p>
                        <p class="mt-2 text-3xl font-extrabold text-emerald-600">{{ totals.active }}</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                        <p class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Disanksi / Nonaktif</p>
                        <p class="mt-2 text-3xl font-extrabold text-rose-600">{{ totals.suspended }}</p>
                    </div>
                </div>

                <!-- Single Main Table -->
                <section class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white">
                        <div>
                            <h2 class="text-[15px] font-extrabold text-slate-900">Manajemen Akses & Sanksi</h2>
                            <p class="text-[12px] text-slate-500 mt-0.5">Pantau pelanggaran dan kontrol akses masuk ke platform.</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px] whitespace-nowrap">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/50 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                                    <th class="py-4 px-6">Informasi Owner</th>
                                    <th class="py-4 px-5 text-center">Total Properti</th>
                                    <th class="py-4 px-5">Catatan Pelanggaran</th>
                                    <th class="py-4 px-5">Status Akun</th>
                                    <th class="py-4 px-6 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="owner in filteredOwners" :key="owner.id" 
                                    :class="[
                                        'group hover:bg-slate-50/80 transition-all duration-200',
                                        owner.status === 'Aktif' ? 'hover:shadow-[inset_3px_0_0_#0A2540]' : 'hover:shadow-[inset_3px_0_0_#e11d48] bg-rose-50/10'
                                    ]">
                                    
                                    <!-- User Info -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-[12px] font-bold text-slate-600">
                                                {{ owner.name.charAt(0) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ owner.name }}</p>
                                                <p class="text-[11px] font-medium text-slate-500 mt-0.5">{{ owner.email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Properti -->
                                    <td class="py-4 px-5 text-center">
                                        <span class="inline-flex items-center justify-center bg-slate-100 text-slate-700 w-7 h-7 rounded-lg font-bold text-[11px]">
                                            {{ owner.properties }}
                                        </span>
                                    </td>

                                    <!-- Pelanggaran -->
                                    <td class="py-4 px-5 text-slate-600">
                                        <span v-if="owner.violation !== '-'" class="font-semibold text-rose-600 text-[12px]">
                                            <i class="fa-solid fa-circle-exclamation text-[10px] mr-1"></i> {{ owner.violation }}
                                        </span>
                                        <span v-else class="text-slate-400 italic">Bersih</span>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-4 px-5">
                                        <div :class="[
                                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset',
                                            owner.status === 'Aktif' ? 'bg-emerald-50 text-emerald-700 ring-emerald-500/20' : 'bg-rose-50 text-rose-700 ring-rose-500/20'
                                        ]">
                                            <div :class="['w-1.5 h-1.5 rounded-full', owner.status === 'Aktif' ? 'bg-emerald-500' : 'bg-rose-500']"></div>
                                            {{ owner.status }}
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-4 px-6 text-right">
                                        <button v-if="owner.status === 'Aktif'" class="text-[12px] font-bold text-slate-500 bg-white border border-slate-200 shadow-sm px-4 py-1.5 rounded-lg group-hover:bg-rose-600 group-hover:text-white group-hover:border-transparent transition-all active:scale-95">
                                            Nonaktifkan
                                        </button>
                                        <button v-else class="text-[12px] font-bold text-slate-500 bg-white border border-slate-200 shadow-sm px-4 py-1.5 rounded-lg group-hover:bg-emerald-600 group-hover:text-white group-hover:border-transparent transition-all active:scale-95">
                                            Pulihkan Akun
                                        </button>
                                    </td>
                                </tr>

                                <!-- Empty State Pencarian -->
                                <tr v-if="filteredOwners.length === 0">
                                    <td colspan="5" class="py-16 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-50 text-slate-400 mb-3">
                                            <i class="fa-solid fa-magnifying-glass text-xl"></i>
                                        </div>
                                        <p class="text-[13px] font-semibold text-slate-500">Tidak ada akun yang sesuai dengan pencarian.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </main>
    </div>
</template>