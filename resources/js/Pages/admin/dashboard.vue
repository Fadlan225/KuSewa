<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const props = defineProps({
    admin: {
        type: Object,
        default: () => ({
            name: 'Super Admin',
            role: 'Administrator Utama',
            email: 'admin@kusewa.id'
        })
    },
    stats: {
        type: Object,
        default: () => ({
            totalUsers: 0,
            totalProperties: 0,
            pendingApprovals: 0,
            monthlyRevenue: 0
        })
    },
    recentActivities: { type: Array, default: () => [] },
    quickActions: { type: Array, default: () => [] },
});

const tabs = [
    { label: 'Overview', route: 'admin.dashboard' },
    { label: 'Verifikasi Aset', route: 'admin.asset-validation' },
    { label: 'Pengguna', route: 'admin.pengajuan-akun' },
    { label: 'Laporan Keuangan', route: 'admin.payment-system' },
];

const formatRupiah = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
</script>

<template>
    <Head title="Admin Dashboard - kusewa.id" />

    <!-- Layout Utama Admin -->
    <div class="h-screen bg-[#F3F5F8] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30 overflow-hidden">

        <!-- ==================== SIDEBAR COMPONENT ==================== -->
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <!-- ==================== MAIN CONTENT ==================== -->
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">

            <!-- TOPBAR HEADER -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3 w-1/3">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    <input type="text" placeholder="Cari data user, properti, atau sistem..." class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400" />
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-bell text-sm"></i>
                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full absolute top-2 right-2"></span>
                    </button>

                    <div class="h-6 w-[1px] bg-slate-200"></div>

                    <!-- Admin Profile Info -->
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#0A2540] text-[#FFC000] flex items-center justify-center font-black text-xs shadow-xs">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="text-left leading-tight hidden sm:block">
                            <p class="text-xs font-bold text-slate-800">{{ admin.name }}</p>
                            <p class="text-[10px] text-amber-600 font-semibold">● Full Access</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- BODY CONTENT AREA -->
            <div class="p-6 space-y-5 max-w-[1400px] w-full mx-auto">

                <!-- TITLE ROW & QUICK ACTION -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#0A2540] text-[#FFC000] flex items-center justify-center font-black text-base shadow-sm">
                            <i class="fa-solid fa-gauge-high"></i>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-slate-900 tracking-tight">Panel Kontrol Administrator</h1>
                            <p class="text-[11px] text-slate-400">Pantau seluruh aktivitas platform, verifikasi aset, dan pengguna kusewa.id</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-xs">
                        <button class="bg-[#0A2540] hover:bg-slate-800 active:scale-95 text-white font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                            <i class="fa-solid fa-download text-xs text-[#FFC000]"></i>
                            <span>Export Laporan</span>
                        </button>
                    </div>
                </div>

                <!-- TABS CATEGORY -->
                <div class="flex items-center gap-6 border-b border-slate-200/80 pb-1 text-xs font-medium">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.route"
                        :href="route(tab.route)"
                        class="pb-2 transition relative"
                        :class="route().current(tab.route) ? 'text-[#0A2540] font-bold border-b-2 border-[#0A2540]' : 'text-slate-400 hover:text-slate-600'"
                    >
                        {{ tab.label }}
                    </Link>
                </div>

                <!-- TOP ANALYTICS SECTION: 4 METRICS + SYSTEM HEALTH -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    
                    <!-- 4 Metric Cards Grid (6 Cols) -->
                    <div class="lg:col-span-6 grid grid-cols-2 gap-4">
                        
                        <!-- Card 1 -->
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="mt-4">
                                <span class="text-[11px] font-medium text-slate-400 block">Total Pengguna</span>
                                <span class="text-xl font-extrabold text-slate-900">{{ stats.totalUsers }} Akun</span>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-full bg-amber-50 text-[#0A2540] flex items-center justify-center text-xs">
                                <i class="fa-solid fa-city text-[#FFC000]"></i>
                            </div>
                            <div class="mt-4">
                                <span class="text-[11px] font-medium text-slate-400 block">Total Listing Properti</span>
                                <span class="text-xl font-extrabold text-slate-900">{{ stats.totalProperties }} Unit</span>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div class="mt-4">
                                <span class="text-[11px] font-medium text-slate-400 block">Menunggu Verifikasi</span>
                                <span class="text-xl font-extrabold text-slate-900">{{ stats.pendingApprovals }} Item</span>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div class="mt-4">
                                <span class="text-[11px] font-medium text-slate-400 block">Omset Platform (Bulan Ini)</span>
                                <span class="text-base font-extrabold text-slate-900">{{ stats.monthlyRevenue }}</span>
                            </div>
                        </div>

                    </div>

                    <!-- System Health & Quick Actions Card (6 Cols) -->
                    <div class="lg:col-span-6 bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Status Infrastruktur Sistem</h3>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 border border-emerald-200">
                                    Optimal (99.9%)
                                </span>
                            </div>

                            <!-- Server Metrics Bars -->
                            <div class="space-y-3 text-xs">
                                <div>
                                    <div class="flex justify-between text-[11px] mb-1">
                                        <span class="text-slate-500">Penggunaan CPU Server</span>
                                        <span class="font-bold text-slate-700">24%</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-[#0A2540] h-full rounded-full" style="width: 24%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-[11px] mb-1">
                                        <span class="text-slate-500">Kapasitas Database MySQL</span>
                                        <span class="font-bold text-slate-700">42.8 GB / 100 GB</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full rounded-full" style="width: 43%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-[11px] mb-1">
                                        <span class="text-slate-500">Storage Gambar & Berkas (Cloud)</span>
                                        <span class="font-bold text-slate-700">180 GB / 500 GB</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-blue-600 h-full rounded-full" style="width: 36%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 text-[11px]">
                            <span class="text-slate-400">Sinkronisasi Terakhir: Baru saja</span>
                            <button class="text-[#0A2540] font-bold hover:underline flex items-center gap-1">
                                <i class="fa-solid fa-arrows-rotate text-[10px]"></i>
                                <span>Clear Cache</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- BOTTOM 2-COLUMN SECTION: LIVE ACTIVITIES & MODERATION QUEUE -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    
                    <!-- Col 1: Aktivitas Terbaru Sistem (7 Cols) -->
                    <div class="lg:col-span-7 bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Log Aktivitas & Tiket Masuk</h3>
                                <button class="text-[11px] font-bold text-[#0A2540] hover:underline">Lihat Semua Log</button>
                            </div>

                            <div class="divide-y divide-slate-100">
                                <div v-for="(act, index) in recentActivities" :key="index" class="py-3 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-xs shrink-0 font-bold">
                                            <i :class="[
                                                'fa-solid',
                                                act.type === 'user' ? 'fa-user-plus text-blue-600' :
                                                act.type === 'property' ? 'fa-house-chimney text-amber-600' :
                                                act.type === 'finance' ? 'fa-wallet text-emerald-600' :
                                                act.type === 'report' ? 'fa-triangle-exclamation text-rose-500' : 'fa-check text-slate-600'
                                            ]"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ act.title }}</p>
                                            <p class="text-[10px] text-slate-400">{{ act.desc }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-[10px] font-semibold text-slate-400 block mb-1">{{ act.time }}</span>
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ act.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Col 2: Tindakan Cepat Admin (5 Cols) -->
                    <div class="lg:col-span-5 bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">Aksi Moderasi Cepat</h3>
                            <p class="text-[10px] text-slate-400 mb-4">Pintasan tugas penting untuk menjaga keamanan platform</p>

                            <div class="space-y-2.5 text-xs">
                                <button class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 p-3 rounded-xl flex items-center justify-between transition text-left group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-id-card"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 group-hover:text-[#0A2540]">Validasi Identitas Pemilik</p>
                                            <p class="text-[10px] text-slate-400">3 NIK baru menunggu pencocokan Dukcapil</p>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                                </button>

                                <button class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 p-3 rounded-xl flex items-center justify-between transition text-left group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-building-shield"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 group-hover:text-[#0A2540]">Review Listing Properti</p>
                                            <p class="text-[10px] text-slate-400">5 Kos & Rumah baru diunggah owner</p>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                                </button>

                                <button class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 p-3 rounded-xl flex items-center justify-between transition text-left group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-file-invoice-dollar"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 group-hover:text-[#0A2540]">Persetujuan Withdrawal</p>
                                            <p class="text-[10px] text-slate-400">4 Permintaan pencairan dana sewa</p>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 text-[11px] flex items-center justify-between text-slate-400">
                            <span>kusewa.id Security Engine</span>
                            <span class="font-bold text-emerald-600">Secure Protocol Active</span>
                        </div>
                    </div>

                </div>

            </div>
        </main>

    </div>
</template>