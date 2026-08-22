<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { DoorOpen, Percent, CalendarCheck, Wallet, BarChart, Map, TrendingUp, LineChart, HelpCircle, Users, CheckCircle, Eye, BellRing } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import AssetIllustration from '@/Components/ui/Icons/AssetIllustration.vue';
import IncomeEmptyIllustration from '@/Components/ui/Icons/IncomeEmptyIllustration.vue';
import SpreadEmptyIllustration from '@/Components/ui/Icons/SpreadEmptyIllustration.vue';
import BookingEmptyIllustration from '@/Components/ui/Icons/BookingEmptyIllustration.vue';
import AssetStatusEmptyIllustration from '@/Components/ui/Icons/AssetStatusEmptyIllustration.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem, SelectGroup } from '@/Components/ui/select';
import { VisXYContainer, VisAxis, VisStackedBar, VisCrosshair, VisTooltip, VisLine } from '@unovis/vue';
import { ChartCrosshair, ChartTooltip } from '@/Components/ui/chart';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
});

const formatCurrency = (v) => 'Rp ' + Number(v || 0).toLocaleString('id-ID');
const formatCompactCurrency = (v) => {
    if (!v) return '0';
    if (v >= 1000000) return (v / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' Jt';
    if (v >= 1000) return (v / 1000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' Rb';
    return v.toLocaleString('id-ID');
};

const pendapatanPersentase = computed(() => {
    const ini = props.stats?.pendapatanBulanIni || 0;
    const lalu = props.stats?.pendapatanBulanLalu || 0;

    if (lalu === 0 && ini === 0) return 0;
    if (lalu === 0 && ini > 0) return 100;

    return ((ini - lalu) / lalu) * 100;
});

const kotaList = computed(() => {
    const data = props.stats?.kotaData || {};
    return Object.keys(data)
        .map(key => ({
            name: key.replace(/^(KOTA|KABUPATEN)\s+/i, ''),
            count: data[key]
        }))
        .sort((a, b) => {
            if (b.count !== a.count) {
                return b.count - a.count; // Sort by count descending
            }
            return a.name.localeCompare(b.name); // Then alphabetically ascending
        });
});

const maxKotaCount = computed(() => {
    if (!kotaList.value.length) return 0;
    return Math.max(...kotaList.value.map(k => k.count));
});

const selectedTime = ref('30days');
const isChartMounted = ref(false);

const selectedTimeLabel = computed(() => {
    switch (selectedTime.value) {
        case '7days': return '7 hari terakhir';
        case '30days': return '30 hari terakhir';
        case '90days': return '90 hari terakhir';
        case '1year': return '365 hari terakhir';
        default: return 'periode terpilih';
    }
});

onMounted(() => {
    setTimeout(() => {
        isChartMounted.value = true;
    }, 150);
});

// Chart Setup (Unovis)
const chartData = computed(() => {
    let rawData = props.stats?.chartData?.[selectedTime.value] || [];

    // Animasikan dari 0 saat pertama kali mount
    return rawData.map((item, index) => {
        return {
            ...item,
            _animIncome: isChartMounted.value ? item.income : 0
        };
    });
});

const chartTotalSum = computed(() => chartData.value.reduce((acc, curr) => acc + curr.income, 0));

const x = (d, i) => i;
const y = [d => d._animIncome];

const tickFormatY = (d) => {
    if (d === 0) return '0';
    if (d >= 1000000) return (d / 1000000).toFixed(1).replace('.0', '') + ' Jt';
    if (d >= 1000) return (d / 1000).toFixed(1).replace('.0', '') + ' Rb';
    return d.toString();
};

const createTickFormatX = (dataRef, periodRef) => {
    return (i) => {
        const data = dataRef.value[i];
        if (!data) return '';

        const period = periodRef.value;
        const total = dataRef.value.length;

        // Khusus 90 hari: Tampilkan awal, akhir, dan tiap awal bulan (tanggal '01')
        if (period === '90days') {
            if (i === 0 || i === total - 1 || data.label.startsWith('01 ')) return data.label;
            return '';
        }

        // Khusus 30 hari: Tampilkan awal, akhir, dan tiap 7 hari
        if (period === '30days') {
            if (i === 0 || i === total - 1 || i % 7 === 0) return data.label;
            return '';
        }

        // 7 hari & 1 tahun: Tampilkan semua label
        return data.label;
    };
};

const tickFormatXIncome = createTickFormatX(chartData, selectedTime);

const formatTooltipDate = (dateStr, isMonthly) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isMonthly) {
        return d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
    }
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const tooltipTemplate = (d) => `
<div class="bg-white border border-slate-200 rounded-none shadow-lg p-4 min-w-[220px] font-sans">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-100">${formatTooltipDate(d.date, selectedTime.value === '1year')}</p>
    <div class="flex flex-col gap-1 px-1">
        <span class="text-xs font-bold text-slate-500">${d.label}</span>
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-[#FFC000]"></div>
            <span class="text-sm font-black text-slate-800">${formatCurrency(d.income)}</span>
        </div>
    </div>
`;

// BOOKING TREND SETUP
const selectedBookingTime = ref('30days');
const selectedBookingTimeLabel = computed(() => {
    switch (selectedBookingTime.value) {
        case '7days': return '7 hari terakhir';
        case '30days': return '30 hari terakhir';
        case '90days': return '90 hari terakhir';
        case '1year': return '365 hari terakhir';
        default: return 'periode terpilih';
    }
});
const bookingChartData = computed(() => {
    let rawData = props.stats?.chartData?.[selectedBookingTime.value] || [];
    return rawData.map((item) => ({
        ...item,
        _animBooking: isChartMounted.value ? item.booking_count : 0
    }));
});
const totalBookingSelectedPeriod = computed(() => {
    return bookingChartData.value.reduce((acc, curr) => acc + (curr.booking_count || 0), 0);
});
const bookingY = [d => d._animBooking];

const tickFormatYBooking = (d) => {
    if (d % 1 !== 0) return '';
    return d.toString();
};

const tickFormatXBooking = createTickFormatX(bookingChartData, selectedBookingTime);

const tooltipTemplateBooking = (d) => `
    <div class="flex flex-col gap-1 px-1">
        <span class="text-xs font-bold text-slate-500">${d.label}</span>
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-[#FFC000]"></div>
            <span class="text-sm font-black text-slate-800">${d.booking_count} <span class="font-normal text-slate-500 text-xs">Pemesanan</span></span>
        </div>
    </div>
`;

// ASSET STATUS SETUP
const assetColorMap = {
    'Aktif': '#10b981',
    'Menunggu Validasi': '#f59e0b',
    'Draf': '#94a3b8',
    'Nonaktif': '#ef4444'
};

const assetChartTotal = computed(() => {
    const data = props.stats?.statusAssetData || [];
    return data.reduce((acc, curr) => acc + curr.value, 0);
});

const assetChartSlices = computed(() => {
    const data = props.stats?.statusAssetData || [];
    const total = assetChartTotal.value;
    if (total === 0) return [];

    let currentOffset = 0;
    const circumference = 2 * Math.PI * 40; // radius=40

    return data.map(item => {
        const percentage = item.value / total;
        const length = percentage * circumference;
        const gap = circumference - length;
        const slice = {
            name: item.name,
            value: item.value,
            color: assetColorMap[item.name] || item.fill || '#cbd5e1',
            dash: length,
            gap: gap,
            offset: currentOffset,
            percentage: Math.round(percentage * 100)
        };
        currentOffset -= length;
        return slice;
    });
});

</script>

<style>
/* CSS Override untuk merubah warna Unovis Bar saat di-hover */
.unovis-chart-container rect {
    transition: fill 0.2s ease, opacity 0.2s ease;
}
.unovis-chart-container rect:hover {
    fill: #FFC000 !important; /* Kuning KitaSewa saat hover */
    opacity: 1 !important;
}
</style>

<template>
    <Head title="Ringkasan Bisnis Anda" />

    <DashboardLayout
        title="Dashboard"
        description="Ringkasan operasional aset & sewa Anda di kitasewa"
        role="Owner"
    >

        <!-- ONBOARDING BANNER -->
        <div v-if="props.stats?.totalUnit === 0" class="bg-white border border-[#E5E7EB] rounded-md mb-6 flex flex-col md:flex-row items-center justify-between p-6 md:p-8 overflow-hidden relative shadow-sm gap-6">
            <div class="flex-1 relative z-10">
                <h2 class="text-2xl font-black text-[#0A2540] mb-2 tracking-tight">Mulai sewakan aset Anda</h2>
                <p class="text-sm text-slate-600 mb-6 max-w-md xl:max-w-lg font-medium leading-relaxed">Tambahkan aset pertama Anda dan lengkapi informasi agar siap ditemukan oleh calon penyewa.</p>
                <Link :href="route('owner.asset.create')" class="inline-flex items-center justify-center bg-[#FFC000] hover:bg-[#e5ac00] text-[#0A2540] font-bold text-sm px-5 py-2.5 rounded transition-colors shadow-sm">
                    <span class="mr-2 text-lg font-black leading-none">+</span> Daftarkan Aset
                </Link>
            </div>
            <div class="hidden md:block w-48 lg:w-56 xl:w-72 shrink-0 pointer-events-none">
                <AssetIllustration class="w-full h-auto drop-shadow-sm" />
            </div>
        </div>

        <!-- STATS OVERVIEW - Clean Panel Design -->
        <div v-else class="bg-white border border-slate-200/80 rounded-xl shadow-sm mb-6">
            <div class="grid grid-cols-2 xl:grid-cols-4 border-slate-100">
                <!-- Pesanan Baru -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-b xl:border-b-0 border-slate-100">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <BellRing class="text-slate-400 w-3.5 h-3.5" /> <span>Pesanan Baru</span>
                        </p>
                        <div class="group relative cursor-help">
                            <HelpCircle class="w-3.5 h-3.5 text-slate-300 hover:text-slate-500 transition-colors" />
                            <div class="absolute bottom-full left-[-10px] sm:left-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] leading-relaxed rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 shadow-lg text-left font-medium">
                                Jumlah pesanan sewa yang baru masuk dan butuh persetujuan Anda segera.
                                <div class="absolute top-full left-[14px] sm:left-3 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ props.stats?.pesananBaru ?? 0 }}</p>
                </div>

                <!-- Penyewa Aktif -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-b xl:border-b-0 xl:border-r border-slate-100">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Users class="text-slate-400 w-3.5 h-3.5" /> <span>Penyewa Aktif</span>
                        </p>
                        <div class="group relative cursor-help">
                            <HelpCircle class="w-3.5 h-3.5 text-slate-300 hover:text-slate-500 transition-colors" />
                            <div class="absolute bottom-full right-[-10px] sm:right-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] leading-relaxed rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 shadow-lg text-left font-medium">
                                Jumlah penyewa yang saat ini sedang dalam masa sewa (aktif) di seluruh aset Anda.
                                <div class="absolute top-full right-[14px] sm:right-3 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ props.stats?.penyewaAktif ?? 0 }}</p>
                </div>

                <!-- Aset Tayang -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-slate-100">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <CheckCircle class="text-slate-400 w-3.5 h-3.5" /> <span>Aset Tayang</span>
                        </p>
                        <div class="group relative cursor-help">
                            <HelpCircle class="w-3.5 h-3.5 text-slate-300 hover:text-slate-500 transition-colors" />
                            <div class="absolute bottom-full left-[-10px] sm:left-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] leading-relaxed rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 shadow-lg text-left font-medium">
                                Total aset Anda yang sudah aktif dan kini tampil di halaman pencarian untuk dilihat calon penyewa.
                                <div class="absolute top-full left-[14px] sm:left-3 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ props.stats?.asetTayang ?? 0 }}</p>
                </div>

                <!-- Total Kunjungan -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Eye class="text-slate-400 w-3.5 h-3.5" /> <span>Kunjungan Profil</span>
                        </p>
                        <div class="group relative cursor-help">
                            <HelpCircle class="w-3.5 h-3.5 text-slate-300 hover:text-slate-500 transition-colors" />
                            <div class="absolute bottom-full right-[-10px] sm:right-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] leading-relaxed rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 shadow-lg text-left font-medium">
                                Total seberapa sering calon penyewa melihat seluruh aset Anda.
                                <div class="absolute top-full right-[14px] sm:right-3 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ props.stats?.totalKunjungan ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">

            <!-- PENDAPATAN CHART -->
            <Card class="xl:col-span-2 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">
                <CardHeader class="relative z-20 flex flex-col items-stretch p-4 sm:flex-row pb-6">
                    <div class="flex flex-1 flex-col justify-center gap-1 text-left">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-base font-black text-slate-800 tracking-tight">Grafik Pendapatan</CardTitle>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Menampilkan pendapatan {{ selectedTimeLabel }}.
                                </p>
                            </div>
                            <Select v-model="selectedTime">
                                <SelectTrigger class="w-[130px] h-9 text-xs font-semibold bg-white border-slate-200 rounded-xl shadow-sm hover:border-[#FFC000] focus:ring-[#FFC000]/30 focus:border-[#FFC000] focus:ring-offset-0 transition-colors">
                                    <SelectValue placeholder="Periode" />
                                </SelectTrigger>
                                <SelectContent class="text-xs rounded-xl border-slate-200 !bg-white !opacity-100 !z-[9999] shadow-xl">
                                    <SelectGroup>
                                        <SelectItem value="7days" class="focus:bg-[#FFC000]/20 cursor-pointer">7 Hari</SelectItem>
                                        <SelectItem value="30days" class="focus:bg-[#FFC000]/20 cursor-pointer">30 Hari</SelectItem>
                                        <SelectItem value="90days" class="focus:bg-[#FFC000]/20 cursor-pointer">90 Hari</SelectItem>
                                        <SelectItem value="1year" class="focus:bg-[#FFC000]/20 cursor-pointer">365 Hari</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="p-4 pt-0 flex-1 min-h-[320px] relative z-0">
                    <div v-if="chartData.length > 0 && chartTotalSum > 0" class="w-full h-full relative text-xs unovis-chart-container z-0">
                        <VisXYContainer :data="chartData" :height="300" :duration="800">
                            <!-- Bar Tipis Kotak, Warna Navy sebagai warna data utama -->
                            <VisStackedBar :x="x" :y="y" color="#0A2540" :roundedCorners="2" :barPadding="0.4" />

                            <!-- Hanya tampilkan grid Horizontal -->
                            <VisAxis type="x" :tickFormat="tickFormatXIncome" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-400" />
                            <VisAxis type="y" :tickFormat="tickFormatY" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-400" />

                            <!-- Custom Tooltip & Crosshair Line -->
                            <VisTooltip />
                            <VisCrosshair :template="tooltipTemplate" color="#0A2540" />
                        </VisXYContainer>
                    </div>

                    <div v-else class="w-full h-full min-h-[300px] flex flex-col items-center justify-center text-slate-400 pt-6 pb-2">
                        <div class="w-32 h-auto mb-4 opacity-60 pointer-events-none">
                            <IncomeEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Aset Anda siap menghasilkan</p>
                        <p class="text-sm text-slate-500 mb-5 text-center font-medium max-w-[280px]">Dapatkan penyewa pertama dan mulai lihat pendapatan Anda di sini.</p>
                        <Link :href="route('owner.asset.index')" class="px-5 py-2.5 bg-[#FFC000] hover:bg-[#e5ac00] text-[#0A2540] shadow-sm hover:shadow rounded text-xs font-bold transition-all">
                            Lihat Aset
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- PERSEBARAN ASSET -->
            <Card class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">
                <CardHeader class="p-5 border-b border-slate-100 pb-4">
                    <CardTitle class="text-sm font-bold text-slate-800">Persebaran Aset</CardTitle>
                    <p class="text-xs text-slate-500 mt-1">Menampilkan persebaran aset berdasarkan kota.</p>
                </CardHeader>
                <CardContent class="p-5 flex-1">
                    <div v-if="kotaList.length" class="space-y-4">
                        <div v-for="(kota, idx) in kotaList" :key="idx" class="w-full flex items-center gap-2">
                            <!-- Bar Chart with Inside Label -->
                            <div class="flex-1 h-8 bg-slate-50 rounded-lg relative border border-slate-100/50">
                                <!-- Animated Bar Fill -->
                                <div class="absolute left-0 top-0 h-full bg-[#0A2540] rounded-lg transition-all duration-1000 ease-out"
                                     :style="{ width: Math.max((kota.count / maxKotaCount) * 100, 2) + '%' }">
                                </div>
                                <!-- Label Inside Left -->
                                <div class="absolute inset-y-0 left-3 flex items-center z-10 pointer-events-none pr-3">
                                    <span class="text-xs font-medium text-white truncate" :title="kota.name">
                                        {{ kota.name }}
                                    </span>
                                </div>
                            </div>
                            <!-- Value Label Right -->
                            <span class="text-xs font-black text-slate-600 shrink-0 w-8 text-right pr-1">
                                {{ kota.count }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="h-full w-full min-h-[220px] flex flex-col items-center justify-center text-slate-400 pt-6 pb-2">
                        <div class="w-32 h-auto mb-4 opacity-60 pointer-events-none">
                            <SpreadEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Mulai tampilkan aset Anda</p>
                        <p class="text-sm text-slate-500 mb-5 text-center font-medium max-w-[240px]">Daftarkan aset dan biarkan calon penyewa menemukannya.</p>
                        <Link :href="route('owner.asset.create')" class="px-5 py-2.5 bg-[#FFC000] hover:bg-[#e5ac00] text-[#0A2540] shadow-sm hover:shadow rounded text-xs font-bold transition-all">
                            Daftarkan Aset
                        </Link>
                    </div>
                </CardContent>

                <!-- Card Footer (Trending Info) -->
                <div v-if="kotaList.length" class="p-4 bg-slate-50 border-t border-slate-100">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <TrendingUp class="text-emerald-500" />
                        <span><span class="font-bold text-emerald-600">{{ kotaList[0].name }}</span> memiliki aset terbanyak bulan ini.</span>
                    </div>
                </div>
            </Card>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
            <!-- BOOKING TREND CHART -->
            <Card class="xl:col-span-2 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-0 sm:p-0">
                <CardHeader class="flex flex-col items-stretch border-b border-gray-200 p-0 sm:flex-row">
                    <div class="flex flex-1 flex-col justify-center gap-1 px-6 py-5 text-left">
                        <CardTitle class="text-base font-black text-slate-800 tracking-tight">Tren Pemesanan</CardTitle>
                        <CardDescription class="text-xs text-slate-400">
                            Menampilkan pemesanan {{ selectedBookingTimeLabel }}.
                        </CardDescription>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center border-t sm:border-t-0 sm:border-l border-slate-100">
                        <div class="px-6 py-4 w-full sm:w-auto flex items-center justify-center">
                            <Select v-model="selectedBookingTime">
                                <SelectTrigger class="w-[130px] h-9 text-xs font-semibold bg-white border-slate-200 rounded-xl shadow-sm hover:border-[#FFC000] focus:ring-[#FFC000]/30 focus:border-[#FFC000] focus:ring-offset-0 transition-colors">
                                    <SelectValue placeholder="Periode" />
                                </SelectTrigger>
                                <SelectContent class="text-xs rounded-xl border-slate-200 !bg-white !opacity-100 !z-[9999] shadow-xl">
                                    <SelectGroup>
                                        <SelectItem value="7days" class="focus:bg-[#FFC000]/20 cursor-pointer">7 Hari</SelectItem>
                                        <SelectItem value="30days" class="focus:bg-[#FFC000]/20 cursor-pointer">30 Hari</SelectItem>
                                        <SelectItem value="90days" class="focus:bg-[#FFC000]/20 cursor-pointer">90 Hari</SelectItem>
                                        <SelectItem value="1year" class="focus:bg-[#FFC000]/20 cursor-pointer">365 Hari</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                        <button class="group w-full flex flex-1 flex-col justify-center gap-1 px-6 py-4 text-left sm:border-l border-gray-200 sm:px-8 sm:py-6 bg-slate-50/50 transition-colors hover:bg-[#FFC000]/10">
                            <span class="text-xs text-slate-500 font-medium group-hover:text-[#0A2540]">Total Booking</span>
                            <span class="text-lg leading-none font-bold sm:text-3xl text-slate-800">{{ totalBookingSelectedPeriod }}</span>
                        </button>
                    </div>
                </CardHeader>
                <CardContent class="p-4 sm:p-6 pb-2">
                    <div class="w-full h-[250px]" v-if="bookingChartData.length > 0 && totalBookingSelectedPeriod > 0">
                        <VisXYContainer :data="bookingChartData" :duration="1000" height="250" :padding="{ top: 10, right: 10, left: 0, bottom: 0 }">
                            <VisLine :x="x" :y="bookingY" color="#0A2540" :lineWidth="3" />
                            <VisAxis type="x" :tickFormat="tickFormatXBooking" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-400" />
                            <VisAxis type="y" :tickFormat="tickFormatYBooking" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-400" />
                            <VisTooltip />
                            <VisCrosshair :template="tooltipTemplateBooking" color="#0A2540" />
                        </VisXYContainer>
                    </div>
                    <!-- Empty State -->
                    <div v-else class="w-full h-full min-h-[250px] flex flex-col items-center justify-center text-slate-400 py-6">
                        <div class="w-32 h-auto mb-4 opacity-60 pointer-events-none">
                            <BookingEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Siap menerima booking pertama?</p>
                        <p class="text-sm text-slate-500 mb-5 text-center font-medium max-w-[280px]">Pastikan aset Anda aktif dan menarik bagi penyewa.</p>
                        <Link :href="route('owner.bookings')" class="px-5 py-2.5 bg-[#FFC000] hover:bg-[#e5ac00] text-[#0A2540] shadow-sm hover:shadow rounded text-xs font-bold transition-all">
                            Lihat Pemesanan
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- STATUS ASET CHART -->
            <Card class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">
                <CardHeader class="p-5 border-b border-slate-100 pb-4">
                    <CardTitle class="text-sm font-bold text-slate-800">Status Aset</CardTitle>
                    <p class="text-xs text-slate-500 mt-1">Ringkasan status seluruh aset Anda.</p>
                </CardHeader>
                <CardContent class="p-5 flex-1 flex flex-col items-center justify-center">
                    <template v-if="assetChartTotal > 0">
                        <!-- SVG Donut Chart -->
                        <div class="relative w-44 h-44 mb-2">
                            <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                                <!-- Background Circle for empty state -->
                                <circle v-if="assetChartTotal === 0" cx="50" cy="50" r="40" fill="transparent" stroke="#f1f5f9" stroke-width="20" />

                                <circle v-for="slice in assetChartSlices" :key="slice.name"
                                        cx="50" cy="50" r="40"
                                        fill="transparent"
                                        :stroke="slice.color"
                                        :stroke-width="20"
                                        :stroke-dasharray="`${slice.dash} ${slice.gap}`"
                                        :stroke-dashoffset="slice.offset"
                                        class="transition-all duration-1000 ease-out"
                                />
                            </svg>
                            <!-- Center Label -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-3xl font-black text-slate-800 leading-none">{{ assetChartTotal }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-1">Total Aset</span>
                            </div>
                        </div>
                        <!-- Legend -->
                        <div class="w-full mt-6 space-y-3 px-2">
                            <div v-for="item in assetChartSlices" :key="item.name" class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-3.5 h-3.5 rounded-full shadow-sm" :style="{ backgroundColor: item.color }"></span>
                                    <span class="text-slate-600 font-medium">{{ item.name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 text-xs">{{ item.percentage }}%</span>
                                    <span class="font-bold text-slate-800 w-6 text-right">{{ item.value }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <!-- Empty State -->
                    <div v-else class="w-full h-full min-h-[250px] flex flex-col items-center justify-center text-slate-400 py-6">
                        <div class="w-32 h-auto mb-4 opacity-60 pointer-events-none">
                            <AssetStatusEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Belum ada aset</p>
                        <p class="text-sm text-slate-500 mb-5 text-center font-medium max-w-[280px]">Daftarkan aset pertama Anda dan mulai sewakan di KitaSewa.</p>
                        <Link :href="route('owner.asset.create')" class="px-5 py-2.5 bg-[#FFC000] hover:bg-[#e5ac00] text-[#0A2540] shadow-sm hover:shadow rounded text-xs font-bold transition-all">
                            Daftarkan Aset
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </DashboardLayout>
</template>
