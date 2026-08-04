<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const searchQuery = ref('');
const contentTab = ref('Kategori');
const tabs = ['Kategori', 'Fasilitas'];

const categories = ref([
    { id: 1, name: 'Kost', active: true, description: 'Ruangan untuk kos harian atau bulanan.' },
    { id: 2, name: 'Apartemen', active: true, description: 'Unit apartemen dengan fasilitas lengkap.' },
    { id: 3, name: 'Ruko', active: false, description: 'Ruko untuk usaha dan hunian.' },
    { id: 4, name: 'Villa', active: true, description: 'Hunian villa untuk liburan.' },
]);

const facilities = ref([
    { id: 1, name: 'WiFi', type: 'Gratis', active: true },
    { id: 2, name: 'AC', type: 'Kamar Tidur', active: true },
    { id: 3, name: 'Kolam Renang', type: 'Umum', active: false },
    { id: 4, name: 'Parkir', type: 'Terbuka', active: true },
    { id: 5, name: 'Dapur Bersama', type: 'Komunitas', active: true },
]);

const filteredItems = computed(() => {
    const query = searchQuery.value.toLowerCase();
    if (contentTab.value === 'Kategori') {
        return categories.value.filter(item => {
            return [item.name, item.description].join(' ').toLowerCase().includes(query);
        });
    }

    return facilities.value.filter(item => {
        return [item.name, item.type].join(' ').toLowerCase().includes(query);
    });
});
</script>

<template>
    <Head title="Kategori & Fasilitas - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3 w-1/3 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari kategori atau fasilitas..."
                        class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                    />
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Tambah Item</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-slate-900 tracking-tight">Kategori & Fasilitas</h1>
                            <p class="text-xs text-slate-400">Kelola master data kategori properti dan daftar fasilitas yang tersedia.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 bg-white p-1 rounded-xl border border-slate-200/80 text-xs shadow-xs">
                        <button
                            v-for="tab in tabs"
                            :key="tab"
                            @click="contentTab = tab"
                            :class="[
                                'px-3.5 py-1.5 rounded-lg font-semibold transition',
                                contentTab === tab ? 'bg-slate-900 text-[#FFC000] shadow-xs' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'
                            ]"
                        >
                            {{ tab }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Total Kategori</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ categories.length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Kategori properti yang tersedia.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Total Fasilitas</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ facilities.length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Fasilitas yang dapat dipilih di listing.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Aktif / Nonaktif</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ filteredItems.length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Item sesuai pencarian saat ini.</p>
                    </div>
                </div>

                <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar {{ contentTab }}</h2>
                            <p class="text-[11px] text-slate-400">Atur nama, tipe, dan status aktif untuk {{ contentTab.toLowerCase() }}.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Kelola Semua</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Nama</th>
                                    <th class="py-4 px-4" v-if="contentTab === 'Kategori'">Deskripsi</th>
                                    <th class="py-4 px-4" v-if="contentTab === 'Fasilitas'">Tipe</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.name }}</td>
                                    <td v-if="contentTab === 'Kategori'" class="py-4 px-4 text-slate-600">{{ item.description }}</td>
                                    <td v-if="contentTab === 'Fasilitas'" class="py-4 px-4 text-slate-600">{{ item.type }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ item.active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Edit</button>
                                    </td>
                                </tr>
                                <tr v-if="filteredItems.length === 0">
                                    <td :colspan="contentTab === 'Kategori' ? 4 : 3" class="py-12 text-center text-slate-400">
                                        Tidak ada {{ contentTab.toLowerCase() }} sesuai pencarian.
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
