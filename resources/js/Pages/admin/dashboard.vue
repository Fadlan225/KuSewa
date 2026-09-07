<script setup>
import { Download, Users, Building2, AlertTriangle, LineChart, RefreshCw, IdCard, ChevronRight, ShieldCheck, Receipt, UserPlus } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import AvatarMale from '@/Components/ui/Icons/AvatarMale.vue';
import AvatarFemale from '@/Components/ui/Icons/AvatarFemale.vue';
import AvatarDefault from '@/Components/ui/Icons/AvatarDefault.vue';

const props = defineProps({
    admin: {
        type: Object,
        default: () => ({
            name: 'Super Admin',
            role: 'Administrator Utama',
            email: 'admin@kitasewa.id'
        })
    },
    stats: {
        type: Object,
        default: () => ({
            totalUsers: 0,
            totalProperties: 0,
            pendingApprovals: 0,
            monthlyRevenue: 'Rp 0'
        })
    },
    bookingStats: {
        type: Object,
        default: () => ({
            total: 0,
            pending: 0,
            active: 0,
            completed: 0
        })
    },
    recentActivities: { type: Array, default: () => [] },
    quickActions: { type: Array, default: () => [] },
});

const tabs = [
    { label: 'Overview', route: 'admin.dashboard' },
    { label: 'Verifikasi Aset', route: 'admin.validasi-aset' },
    { label: 'Pengguna', route: 'admin.pengajuan-akun' },
    { label: 'Laporan Keuangan', route: 'admin.payment-system' },
];

const formatRupiah = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
</script>

<template>
    <Head title="Admin Dashboard - kitasewa.id" />

    <DashboardLayout role="Admin" title="Panel Kontrol Administrator" description="Pantau seluruh aktivitas platform, verifikasi aset, dan pengguna kitasewa.id">
        <template #header-actions>
            <button class="bg-[#0A2540] hover:bg-slate-800 active:scale-95 text-white font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                <Download class="text-xs text-[#FFC000]" />
                <span>Export Laporan</span>
            </button>
        </template>

            <div class="p-6 space-y-5 max-w-[1400px] w-full mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3">

                    <div class="flex items-center gap-3 text-xs">
                        <button class="bg-[#0A2540] hover:bg-slate-800 active:scale-95 text-white font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                            <Download class="text-xs text-[#FFC000]" />
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
                                <Users class="" />
                            </div>
                            <div class="mt-4">
                                <span class="text-[11px] font-medium text-slate-400 block">Total Pengguna</span>
                                <span class="text-xl font-extrabold text-slate-900">{{ stats.totalUsers }} Akun</span>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-full bg-amber-50 text-[#0A2540] flex items-center justify-center text-xs">
                                <Building2 class="text-[#FFC000]" />
                            </div>
                            <div class="mt-4">
                                <span class="text-[11px] font-medium text-slate-400 block">Total Listing Properti</span>
                                <span class="text-xl font-extrabold text-slate-900">{{ stats.totalProperties }} Unit</span>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-xs">
                                <AlertTriangle class="" />
                            </div>
                            <div class="mt-4">
                                <span class="text-[11px] font-medium text-slate-400 block">Menunggu Verifikasi</span>
                                <span class="text-xl font-extrabold text-slate-900">{{ stats.pendingApprovals }} Item</span>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                <LineChart class="" />
                            </div>
                            <div class="mt-4">
                                <span class="text-[11px] font-medium text-slate-400 block">Omset Platform (Bulan Ini)</span>
                                <span class="text-base font-extrabold text-slate-900">{{ stats.monthlyRevenue }}</span>
                            </div>
                        </div>

                    </div>

                    <!-- Statistik Booking Card (6 Cols) -->
                    <div class="lg:col-span-6 bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Statistik Booking Keseluruhan</h3>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-200">
                                    Total: {{ bookingStats.total }}
                                </span>
                            </div>

                            <!-- Booking Status Bars -->
                            <div class="space-y-3 text-xs">
                                <div>
                                    <div class="flex justify-between text-[11px] mb-1">
                                        <span class="text-slate-500">Menunggu Pembayaran / Persetujuan</span>
                                        <span class="font-bold text-slate-700">{{ bookingStats.pending }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-amber-400 h-full rounded-full" :style="`width: ${bookingStats.total ? (bookingStats.pending / bookingStats.total) * 100 : 0}%`"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-[11px] mb-1">
                                        <span class="text-slate-500">Booking Aktif / Berjalan</span>
                                        <span class="font-bold text-slate-700">{{ bookingStats.active }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-blue-500 h-full rounded-full" :style="`width: ${bookingStats.total ? (bookingStats.active / bookingStats.total) * 100 : 0}%`"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-[11px] mb-1">
                                        <span class="text-slate-500">Booking Selesai</span>
                                        <span class="font-bold text-slate-700">{{ bookingStats.completed }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-emerald-500 h-full rounded-full" :style="`width: ${bookingStats.total ? (bookingStats.completed / bookingStats.total) * 100 : 0}%`"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 text-[11px]">
                            <span class="text-slate-400">Data realtime dari database sistem.</span>
                            <Link :href="route('admin.dashboard')" class="text-[#0A2540] font-bold hover:underline flex items-center gap-1">
                                <RefreshCw class="text-[10px]" />
                                <span>Refresh</span>
                            </Link>
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
                                        <div v-if="act.avatar" class="w-8 h-8 rounded-xl overflow-hidden shrink-0">
                                            <img :src="act.avatar" :alt="act.name" class="w-full h-full object-cover" />
                                        </div>
                                        <div v-else class="w-8 h-8 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-center">
                                            <AvatarMale v-if="act.gender === 'male'" class="w-full h-full" />
                                            <AvatarFemale v-else-if="act.gender === 'female'" class="w-full h-full" />
                                            <AvatarDefault v-else class="w-full h-full" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ act.title }}</p>
                                            <p class="text-[10px] text-slate-400">{{ act.description }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-[10px] font-semibold text-slate-400 block mb-1">{{ act.time }}</span>
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
                                <template v-if="quickActions.length > 0">
                                    <Link v-for="(action, idx) in quickActions" :key="idx" :href="action.link" class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 p-3 rounded-xl flex items-center justify-between transition text-left group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                                                <IdCard class="" />
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 group-hover:text-[#0A2540]">{{ action.title }}</p>
                                                <p class="text-[10px] text-slate-400">{{ action.description }}</p>
                                            </div>
                                        </div>
                                        <ChevronRight class="text-[10px] text-slate-400" />
                                    </Link>
                                </template>
                                <div v-else class="text-center py-6 text-slate-400">
                                    <ShieldCheck class="w-8 h-8 mx-auto mb-2 opacity-30" />
                                    <p class="text-[11px] font-semibold">Tidak ada moderasi prioritas saat ini.</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 text-[11px] flex items-center justify-between text-slate-400">
                            <span>kitasewa.id Security Engine</span>
                            <span class="font-bold text-emerald-600">Secure Protocol Active</span>
                        </div>
                    </div>

                </div>

            </div>
    </DashboardLayout>
</template>
