<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/Components/ui/card';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem, SelectGroup } from '@/Components/ui/select';
import { VisXYContainer, VisAxis, VisLine, VisArea, VisCrosshair, VisTooltip } from '@unovis/vue';
import { Users, Building2, AlertTriangle, LineChart, ShieldCheck, ChevronRight, CheckCircle, BellRing, Eye, MonitorSmartphone } from 'lucide-vue-next';
import AvatarMale from '@/Components/ui/Icons/AvatarMale.vue';
import AvatarFemale from '@/Components/ui/Icons/AvatarFemale.vue';
import AvatarDefault from '@/Components/ui/Icons/AvatarDefault.vue';
import SpreadEmptyIllustration from '@/Components/ui/Icons/SpreadEmptyIllustration.vue';
import EmptyOsChartIcon from '@/Components/ui/Icons/EmptyOsChartIcon.vue';
import EmptyBrowserChartIcon from '@/Components/ui/Icons/EmptyBrowserChartIcon.vue';
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';
import ToggleStatusIllustration from '@/Components/ui/Icons/ToggleStatusIllustration.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    bookingStats: { type: Object, default: () => ({}) },
    recentActivities: { type: Array, default: () => [] },
    quickActions: { type: Array, default: () => [] },
    chartData: { type: Object, default: () => ({ platformGrowth: [], monitoring: [] }) }
});

const isChartMounted = ref(false);

onMounted(() => {
    setTimeout(() => {
        isChartMounted.value = true;
    }, 150);
});

const formatCurrency = (v) => 'Rp ' + Number(v || 0).toLocaleString('id-ID');

// --- PLATFORM GROWTH CHART SETUP ---
const animGrowthData = computed(() => {
    const data = props.chartData?.platformGrowth || [];
    return data.map(d => ({
        ...d,
        users: isChartMounted.value ? d.users : 0,
        owners: isChartMounted.value ? d.owners : 0,
        assets: isChartMounted.value ? d.assets : 0,
        bookings: isChartMounted.value ? d.bookings : 0,
        revenue: isChartMounted.value ? d.revenue : 0,
    }));
});

const growthTotalSum = computed(() => {
    return animGrowthData.value.reduce((acc, curr) => acc + curr.users + curr.owners + curr.assets + curr.bookings + curr.revenue, 0);
});

const growthY = computed(() => {
    return [
        d => d.users,
        d => d.owners
    ];
});

const growthStrokeColors = computed(() => {
    return ['#FFC000', '#94A3B8']; // Kuning untuk Penyewa, Abu untuk Pemilik
});

const growthFillColors = computed(() => {
    return ['url(#fillUsers)', 'url(#fillOwners)'];
});

const x = (d, i) => i;

const tickFormatYGrowth = (d) => {
    if (d % 1 !== 0) return '';
    return d.toLocaleString('id-ID');
};

const tickFormatXGrowth = (i) => {
    const data = animGrowthData.value[i];
    if (!data) return '';
    const d = new Date(data.date);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
};

const tooltipTemplateGrowth = (d) => {
    let rows = '';
    const renderRow = (color, label, value) => `
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-[3px]" style="background-color: ${color}"></div>
                <span class="text-xs text-slate-500 font-medium">${label}</span>
            </div>
            <span class="text-xs font-black text-slate-800">${value}</span>
        </div>
    `;

    rows += renderRow('#FFC000', 'Penyewa', d.users);
    rows += renderRow('#94A3B8', 'Pemilik', d.owners);

    return `
    <div class="flex flex-col gap-1 px-1 py-0.5 min-w-[130px]">
        <span class="text-xs font-bold text-slate-800 mb-2">${new Date(d.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
        <div class="flex flex-col gap-1.5">
            ${rows}
        </div>
    </div>
    `;
};


// --- USER MONITORING CHART SETUP ---
const selectedMonitoringCategory = ref('device');

const animMonitoringData = computed(() => {
    const data = props.chartData?.monitoring || [];
    return data.map(d => ({
        ...d,
        desktop: isChartMounted.value ? d.desktop : 0,
        mobile: isChartMounted.value ? d.mobile : 0,
        tablet: isChartMounted.value ? d.tablet : 0,
        chrome: isChartMounted.value ? d.chrome : 0,
        safari: isChartMounted.value ? d.safari : 0,
        edge: isChartMounted.value ? d.edge : 0,
        firefox: isChartMounted.value ? d.firefox : 0,
        opera: isChartMounted.value ? d.opera : 0,
        other_browser: isChartMounted.value ? d.other_browser : 0,
        windows: isChartMounted.value ? d.windows : 0,
        macos: isChartMounted.value ? d.macos : 0,
        linux: isChartMounted.value ? d.linux : 0,
        android: isChartMounted.value ? d.android : 0,
        ios: isChartMounted.value ? d.ios : 0,
    }));
});

const monitoringTotalSum = computed(() => {
    return animMonitoringData.value.reduce((acc, curr) => acc + curr.desktop + curr.mobile + curr.tablet, 0);
});

// BROWSER DATA AGGREGATION
const realBrowserData = computed(() => {
    const data = props.chartData?.monitoring || [];
    let chrome = 0, safari = 0, edge = 0, firefox = 0, opera = 0, lainnya = 0;
    data.forEach(d => {
        chrome += d.chrome || 0;
        safari += d.safari || 0;
        edge += d.edge || 0;
        firefox += d.firefox || 0;
        opera += d.opera || 0;
        lainnya += d.other_browser || 0;
    });

    return [
        { name: 'Chrome', count: chrome },
        { name: 'Safari', count: safari },
        { name: 'Edge', count: edge },
        { name: 'Firefox', count: firefox },
        { name: 'Opera', count: opera },
        { name: 'Lainnya', count: lainnya }
    ].sort((a, b) => b.count - a.count);
});

const maxBrowserCount = computed(() => {
    return Math.max(...realBrowserData.value.map(d => d.count), 1);
});

const browserTotalCount = computed(() => realBrowserData.value.reduce((acc, curr) => acc + curr.count, 0));
const topBrowser = computed(() => realBrowserData.value[0] || { name: 'None', count: 0 });
const topBrowserPercentage = computed(() => {
    if (browserTotalCount.value === 0) return 0;
    return Math.round((topBrowser.value.count / browserTotalCount.value) * 100);
});

// Computed data untuk Y-axis yang tidak bertumpuk (digunakan oleh VisArea untuk fill gradients bertumpuk alami)
const monitoringY = computed(() => {
    if (selectedMonitoringCategory.value === 'device') {
        return [
            (d) => d.mobile,
            (d) => d.desktop,
            (d) => d.tablet,
        ];
    } else { // OS
        return [
            (d) => d.android,
            (d) => d.ios,
            (d) => d.windows,
            (d) => d.linux,
        ];
    }
});

const monitoringStrokeColors = computed(() => {
    if (selectedMonitoringCategory.value === 'device') {
        return ['#FFC000', '#FFD659', '#94A3B8'];
    } else {
        return ['#FFC000', '#FFD659', '#94A3B8', '#CBD5E1'];
    }
});

const monitoringFillColors = computed(() => {
    if (selectedMonitoringCategory.value === 'device') {
        return ['url(#fillMobile)', 'url(#fillDesktop)', 'url(#fillTablet)'];
    } else {
        return ['url(#fillMobile)', 'url(#fillDesktop)', 'url(#fillTablet)', 'url(#grad-slate-300)'];
    }
});

const tickFormatXMonitoring = (i) => {
    const data = animMonitoringData.value[i];
    if (!data) return '';
    const d = new Date(data.date);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
};

const tickFormatYMonitoring = (d) => {
    if (d % 1 !== 0) return '';
    return d.toLocaleString('id-ID');
};

const tooltipTemplateMonitoring = (d) => {
    let rows = '';
    const renderRow = (color, label, value) => `
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-[3px]" style="background-color: ${color}"></div>
                <span class="text-xs text-slate-500 font-medium">${label}</span>
            </div>
            <span class="text-xs font-black text-slate-800">${value}</span>
        </div>
    `;

    if (selectedMonitoringCategory.value === 'device') {
        rows += renderRow('#FFC000', 'Mobile', d.mobile);
        rows += renderRow('#FFD659', 'Desktop', d.desktop);
        rows += renderRow('#94A3B8', 'Tablet', d.tablet);
    } else {
        rows += renderRow('#FFC000', 'Android', d.android);
        rows += renderRow('#FFD659', 'iOS', d.ios);
        rows += renderRow('#94A3B8', 'Windows', d.windows);
        rows += renderRow('#CBD5E1', 'Linux', d.linux);
    }

    return `
    <div class="flex flex-col gap-1 px-1 py-0.5 min-w-[130px]">
        <span class="text-xs font-bold text-slate-800 mb-2">${new Date(d.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
        <div class="flex flex-col gap-1.5">
            ${rows}
        </div>
    </div>
    `;
};

// Referensi ke komponen Line untuk mengisolasi crosshair
const growthLine = ref(null);
const monitoringLine = ref(null);

// Accessor warna untuk memastikan crosshair menggunakan warna yang tepat
const crosshairColorGrowth = (d, i) => {
    return growthStrokeColors.value[i % growthStrokeColors.value.length] || '#FFC000';
};
const crosshairColorMonitoring = (d, i) => {
    return monitoringStrokeColors.value[i % monitoringStrokeColors.value.length] || '#FFC000';
};

// CSS Variables untuk sinkronisasi warna default Unovis
const growthCssVars = computed(() => {
    const vars = {};
    growthStrokeColors.value.forEach((color, idx) => {
        vars[`--vis-color${idx}`] = color;
    });
    return vars;
});

const monitoringCssVars = computed(() => {
    const vars = {};
    monitoringStrokeColors.value.forEach((color, idx) => {
        vars[`--vis-color${idx}`] = color;
    });
    return vars;
});
</script>

<style>
/* Sembunyikan semua titik crosshair secara default */
.unovis-chart-container .vis-crosshair circle {
    opacity: 0 !important;
}

/* Tampilkan titik yang benar dan berikan border putih tebal (Growth Chart: 2 lines di akhir) */
.growth-crosshair .vis-crosshair circle:nth-last-child(2) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }
.growth-crosshair .vis-crosshair circle:nth-last-child(1) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }

/* Tampilkan titik yang benar dan berikan border putih tebal (Monitoring Chart: ambil dari akhir karena ada VisArea) */
.monitoring-lines-3 .vis-crosshair circle:nth-last-child(3) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }
.monitoring-lines-3 .vis-crosshair circle:nth-last-child(2) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }
.monitoring-lines-3 .vis-crosshair circle:nth-last-child(1) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }

.monitoring-lines-4 .vis-crosshair circle:nth-last-child(4) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }
.monitoring-lines-4 .vis-crosshair circle:nth-last-child(3) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }
.monitoring-lines-4 .vis-crosshair circle:nth-last-child(2) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }
.monitoring-lines-4 .vis-crosshair circle:nth-last-child(1) { opacity: 1 !important; stroke: #ffffff !important; stroke-width: 2.5px !important; r: 5px !important; }
</style>

<template>
    <Head title="Overview" />

    <DashboardLayout role="Admin" title="Panel Kontrol Administrator" description="Pantau seluruh aktivitas platform, verifikasi aset, dan pengguna kitasewa.id">
        <div class="p-4 md:p-6 space-y-6 max-w-[1400px] w-full mx-auto">

            <!-- TOP METRICS SECTION -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <!-- Total Pengguna -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm flex flex-col justify-center transition-all hover:shadow-md">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-[11px] font-semibold uppercase text-slate-400 flex items-center gap-2">
                            <Users class="text-slate-400 w-3.5 h-3.5" /> <span>Total Pengguna</span>
                        </p>
                    </div>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.totalUsers ?? 0 }}</p>
                </div>

                <!-- Total Aset -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm flex flex-col justify-center transition-all hover:shadow-md">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-[11px] font-semibold uppercase text-slate-400 flex items-center gap-2">
                            <Building2 class="text-slate-400 w-3.5 h-3.5" /> <span>Total Listing Properti</span>
                        </p>
                    </div>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.totalProperties ?? 0 }}</p>
                </div>

                <!-- Menunggu Verifikasi -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm flex flex-col justify-center transition-all hover:shadow-md">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-[11px] font-semibold uppercase text-slate-400 flex items-center gap-2">
                            <AlertTriangle class="text-[#FFC000] w-3.5 h-3.5" /> <span class="text-slate-400">Menunggu Verifikasi</span>
                        </p>
                    </div>
                    <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ stats.pendingApprovals ?? 0 }}</p>
                </div>

                <!-- Omset Platform -->
                <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm flex flex-col justify-center transition-all hover:shadow-md">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-[11px] font-semibold uppercase text-slate-400 flex items-center gap-2">
                            <LineChart class="text-slate-400 w-3.5 h-3.5" /> <span>Omset (Bulan Ini)</span>
                        </p>
                    </div>
                    <p class="mt-3 text-2xl lg:text-3xl font-extrabold text-[#0A2540] truncate">{{ stats.monthlyRevenue ?? 'Rp 0' }}</p>
                </div>
            </div>

            <!-- CHARTS SECTION -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">

                <!-- PLATFORM GROWTH CHART -->
                <Card class="col-span-1 xl:col-span-3 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">
                    <CardHeader class="relative z-20 flex flex-col items-stretch p-4 sm:flex-row pb-6">
                        <div class="flex flex-1 flex-col justify-center gap-1 text-left">
                            <CardTitle class="text-base font-black text-slate-800 tracking-tight">Pertumbuhan Pengguna</CardTitle>
                            <CardDescription class="text-xs text-slate-400 mt-0.5">
                                Perbandingan pendaftaran penyewa dan pemilik 30 hari terakhir.
                            </CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4 pt-0 flex-1 min-h-[300px] relative z-0 flex flex-col">
                        <div v-if="animGrowthData.length > 0 && growthTotalSum > 0" class="w-full flex-1 relative flex flex-col">
                            <div class="w-full h-[250px] relative text-xs unovis-chart-container z-0 growth-crosshair" :style="growthCssVars">
                                <!-- SVG Gradients -->
                                <svg style="width:0; height:0; position:absolute;" aria-hidden="true" focusable="false">
                                    <defs>
                                        <linearGradient id="fillUsers" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#FFC000" stop-opacity="0.3"/>
                                            <stop offset="95%" stop-color="#FFC000" stop-opacity="0.0"/>
                                        </linearGradient>
                                        <linearGradient id="fillOwners" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#94A3B8" stop-opacity="0.3"/>
                                            <stop offset="95%" stop-color="#94A3B8" stop-opacity="0.0"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <VisXYContainer :data="animGrowthData" :height="250" :duration="800" :margin="{ top: 30 }">
                                    <VisArea v-for="(yAcc, idx) in growthY" :key="idx" :x="x" :y="yAcc" :color="growthFillColors[idx]" :opacity="1" :curveType="'natural'" />
                                    <VisLine ref="growthLine" :x="x" :y="growthY" :color="growthStrokeColors" :lineWidth="2" :curveType="'natural'" />
                                    <VisAxis type="x" :tickFormat="tickFormatXGrowth" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-400" />
                                    <VisAxis type="y" :tickFormat="tickFormatYGrowth" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-400" />
                                    <VisTooltip :horizontalShift="30" :verticalShift="30" />
                                    <VisCrosshair :template="tooltipTemplateGrowth" :color="crosshairColorGrowth" />
                                </VisXYContainer>
                            </div>
                            <!-- LEGEND -->
                            <div class="flex items-center justify-center gap-6 mt-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded bg-[#FFC000]"></div>
                                    <span class="text-xs font-medium text-slate-600">Penyewa</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded bg-[#94A3B8]"></div>
                                    <span class="text-xs font-medium text-slate-600">Pemilik</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="w-full h-[250px] flex flex-col items-center justify-center text-slate-400 py-6">
                            <div class="w-32 h-auto mb-4 opacity-60 pointer-events-none">
                                <SpreadEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                            </div>
                            <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Belum ada data pertumbuhan</p>
                            <p class="text-sm text-slate-500 mb-5 text-center font-medium max-w-[280px]">Platform belum mencatat aktivitas signifikan di periode ini.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- USER MONITORING CHART (AREA) -->
                <Card class="col-span-1 xl:col-span-2 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">
                    <CardHeader class="relative z-20 flex flex-col items-stretch p-4 pb-6">
                        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3">
                            <div class="flex-1 pr-2">
                                <CardTitle class="text-base font-black text-slate-800 tracking-tight">Monitoring Pengguna</CardTitle>
                                <CardDescription class="text-xs text-slate-400 mt-0.5">
                                    Perangkat dan lingkungan pengunjung 30 hari terakhir.
                                </CardDescription>
                            </div>
                            <div class="shrink-0">
                                <Select v-model="selectedMonitoringCategory">
                                    <SelectTrigger class="w-full xl:w-[140px] h-9 text-xs font-semibold bg-white border-slate-200 rounded-xl shadow-sm hover:border-[#FFC000] focus:ring-[#FFC000]/30 focus:border-[#FFC000] focus:ring-offset-0 transition-colors">
                                        <SelectValue placeholder="Kategori" />
                                    </SelectTrigger>
                                    <SelectContent :body-lock="false" class="text-xs rounded-xl border !border-slate-200 !bg-white !opacity-100 !z-[9999] shadow-xl">
                                        <SelectGroup>
                                            <SelectItem value="device" class="focus:bg-[#FFC000]/20 cursor-pointer">Perangkat</SelectItem>
                                            <SelectItem value="os" class="focus:bg-[#FFC000]/20 cursor-pointer">Sistem Operasi</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4 pt-0 flex-1 min-h-[300px] relative z-0 flex flex-col">
                        <div v-if="animMonitoringData.length > 0 && monitoringTotalSum > 0" class="w-full flex-1 relative flex flex-col">
                            <div class="w-full h-[250px] relative text-xs unovis-chart-container z-0 monitoring-crosshair" :style="monitoringCssVars">
                                <!-- SVG Gradients -->
                                <svg style="width:0; height:0; position:absolute;" aria-hidden="true" focusable="false">
                                    <defs>
                                        <linearGradient id="fillMobile" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#FFC000" stop-opacity="0.3"/>
                                            <stop offset="95%" stop-color="#FFC000" stop-opacity="0.0"/>
                                        </linearGradient>
                                        <linearGradient id="fillDesktop" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#FFD659" stop-opacity="0.3"/>
                                            <stop offset="95%" stop-color="#FFD659" stop-opacity="0.0"/>
                                        </linearGradient>
                                        <linearGradient id="fillTablet" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#94A3B8" stop-opacity="0.3"/>
                                            <stop offset="95%" stop-color="#94A3B8" stop-opacity="0.0"/>
                                        </linearGradient>
                                        <linearGradient id="grad-slate-600" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#475569" stop-opacity="0.2"/>
                                            <stop offset="95%" stop-color="#475569" stop-opacity="0"/>
                                        </linearGradient>
                                        <linearGradient id="grad-slate-500" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#64748B" stop-opacity="0.2"/>
                                            <stop offset="95%" stop-color="#64748B" stop-opacity="0"/>
                                        </linearGradient>
                                        <linearGradient id="grad-slate-400" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#94A3B8" stop-opacity="0.2"/>
                                            <stop offset="95%" stop-color="#94A3B8" stop-opacity="0"/>
                                        </linearGradient>
                                        <linearGradient id="grad-slate-300" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#CBD5E1" stop-opacity="0.2"/>
                                            <stop offset="95%" stop-color="#CBD5E1" stop-opacity="0"/>
                                        </linearGradient>
                                        <linearGradient id="grad-slate-200" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stop-color="#E2E8F0" stop-opacity="0.2"/>
                                            <stop offset="95%" stop-color="#E2E8F0" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <VisXYContainer :data="animMonitoringData" :height="250" :duration="800" :margin="{ top: 30 }">
                                    <VisArea v-for="(yAcc, idx) in monitoringY" :key="idx" :x="x" :y="yAcc" :color="monitoringFillColors[idx]" :opacity="1" :curveType="'natural'" />
                                    <VisLine ref="monitoringLine" :x="x" :y="monitoringY" :color="monitoringStrokeColors" :lineWidth="2" :curveType="'natural'" />
                                    <VisAxis type="x" :tickFormat="tickFormatXMonitoring" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-400" />
                                    <VisAxis type="y" :tickFormat="tickFormatYMonitoring" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-400" />
                                    <VisTooltip :horizontalShift="30" :verticalShift="30" />
                                    <VisCrosshair :template="tooltipTemplateMonitoring" :color="crosshairColorMonitoring" />
                                </VisXYContainer>
                            </div>
                            <!-- LEGEND -->
                            <div class="flex items-center justify-center gap-4 sm:gap-6 mt-6 flex-wrap">
                                <template v-if="selectedMonitoringCategory === 'device'">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded bg-[#FFC000]"></div>
                                        <span class="text-xs font-medium text-slate-600">Mobile</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded bg-[#FFD659]"></div>
                                        <span class="text-xs font-medium text-slate-600">Desktop</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded bg-[#94A3B8]"></div>
                                        <span class="text-xs font-medium text-slate-600">Tablet</span>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded bg-[#FFC000]"></div>
                                        <span class="text-xs font-medium text-slate-600">Android</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded bg-[#FFD659]"></div>
                                        <span class="text-xs font-medium text-slate-600">iOS</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded bg-[#94A3B8]"></div>
                                        <span class="text-xs font-medium text-slate-600">Windows</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded bg-[#CBD5E1]"></div>
                                        <span class="text-xs font-medium text-slate-600">Linux</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                            <div v-else class="w-full h-[300px] flex flex-col items-center justify-center text-slate-400 py-6">
                                <div class="w-32 h-auto mb-4 opacity-60 pointer-events-none">
                                    <EmptyOsChartIcon class="w-full h-auto drop-shadow-sm text-slate-300" />
                                </div>
                                <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Belum ada kunjungan</p>
                                <p class="text-sm text-slate-500 text-center font-medium max-w-[280px]">Data perangkat dan OS akan muncul setelah ada pengunjung platform.</p>
                            </div>
                    </CardContent>
                </Card>

                <!-- BROWSER CHART -->
                <Card class="col-span-1 xl:col-span-1 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">
                    <CardHeader class="relative z-20 flex flex-col items-stretch p-4 sm:flex-row pb-6">
                        <div class="flex flex-1 flex-col justify-center gap-1 text-left">
                            <CardTitle class="text-base font-black text-slate-800 tracking-tight">Browser</CardTitle>
                            <CardDescription class="text-xs text-slate-400 mt-0.5">Penggunaan browser bulan ini</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4 pt-0 flex-1 relative z-0 flex flex-col justify-center">
                        <div v-if="browserTotalCount > 0" class="w-full flex flex-col gap-4 mt-2">
                            <div v-for="(item, idx) in realBrowserData" :key="idx" class="flex items-center gap-4 relative group cursor-pointer">
                                <!-- Y Axis Label -->
                                <div class="w-14 text-xs text-slate-500 font-medium text-right shrink-0">
                                    {{ item.name }}
                                </div>
                                <!-- Bar -->
                                <div class="flex-1 h-7">
                                    <div class="bg-[#FFC000] h-full rounded-[5px] transition-all duration-1000 ease-out"
                                         :style="{ width: isChartMounted ? Math.max((item.count / maxBrowserCount * 100), 2) + '%' : '0%' }">
                                    </div>
                                </div>
                                <!-- Tooltip -->
                                <div class="absolute left-20 -top-8 bg-white border border-slate-100 shadow-lg text-slate-700 text-xs px-3 py-2 rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none z-50 flex items-center gap-2">
                                    <div class="w-2 h-2 rounded bg-[#FFC000]"></div>
                                    <span class="text-slate-500">{{ item.name }}</span>
                                    <span class="font-bold ml-1">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="w-full flex flex-col items-center justify-center text-slate-400 py-8">
                            <div class="w-28 h-auto mb-4 opacity-60 pointer-events-none">
                                <EmptyBrowserChartIcon class="w-full h-auto drop-shadow-sm text-slate-300" />
                            </div>
                            <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Data Browser Kosong</p>
                        </div>
                    </CardContent>
                    <CardFooter class="flex-col items-start gap-1.5 text-sm p-4 pt-4 border-t border-slate-100 bg-slate-50/50 mt-2">
                        <div class="flex items-start justify-between w-full gap-2 font-semibold text-slate-700">
                            <template v-if="browserTotalCount > 0">
                                <span class="leading-tight">{{ topBrowser.name }} mendominasi {{ topBrowserPercentage }}%</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
                            </template>
                            <template v-else>
                                Belum ada data browser
                            </template>
                        </div>
                        <div class="leading-tight text-slate-400 text-[11px]">
                            Menampilkan penggunaan browser 30 hari terakhir
                        </div>
                    </CardFooter>
                </Card>
            </div>

            <!-- BOTTOM SECTION: LIVE ACTIVITIES & MODERATION QUEUE -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mt-6">

                <!-- Col 1: Aktivitas Terbaru Sistem (7 Cols) -->
                <Card class="xl:col-span-7 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl p-0 flex flex-col">
                    <CardHeader class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-sm font-bold text-slate-800">Log Aktivitas</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="p-5 flex-1 flex flex-col relative">
                        <div v-if="recentActivities && recentActivities.length > 0" class="divide-y divide-slate-100">
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
                        <div v-else class="flex flex-col items-center justify-center text-slate-400 py-10 flex-1">
                            <div class="w-32 h-auto mb-4 opacity-70 pointer-events-none">
                                <EmptyStateIcon class="w-full h-auto drop-shadow-sm text-slate-300" />
                            </div>
                            <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Belum Ada Aktivitas</p>
                            <p class="text-sm text-slate-500 text-center font-medium max-w-[280px]">Belum ada rekaman aktivitas dari pengguna untuk saat ini.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Col 2: Tindakan Cepat Admin (5 Cols) -->
                <Card class="xl:col-span-5 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl p-0 flex flex-col">
                    <CardHeader class="p-5 border-b border-slate-100">
                        <CardTitle class="text-sm font-bold text-slate-800">Tindakan Cepat</CardTitle>
                        <CardDescription class="text-xs text-slate-400 mt-1">Pintasan prioritas untuk mengelola platform</CardDescription>
                    </CardHeader>
                    <CardContent class="p-5 flex-1 flex flex-col justify-between relative">
                        <div v-if="quickActions && quickActions.length > 0" class="space-y-2.5 text-xs">
                            <Link v-for="(action, idx) in quickActions" :key="idx" :href="action.link" class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 p-3 rounded-xl flex items-center justify-between transition text-left group">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                                        <ShieldCheck class="" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 group-hover:text-[#0A2540]">{{ action.title }}</p>
                                        <p class="text-[10px] text-slate-400">{{ action.description }}</p>
                                    </div>
                                </div>
                                <ChevronRight class="text-[10px] text-slate-400" />
                            </Link>
                        </div>
                        <div v-else class="text-center py-10 flex-1 flex flex-col items-center justify-center text-slate-400">
                            <div class="w-28 h-auto mb-4 opacity-70 pointer-events-none">
                                <ToggleStatusIllustration class="w-full h-auto drop-shadow-sm text-slate-300" />
                            </div>
                            <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Aman & Terkendali</p>
                            <p class="text-[11px] font-semibold max-w-[200px]">Tidak ada tindakan prioritas yang perlu dilakukan saat ini.</p>
                        </div>

                    </CardContent>
                </Card>

            </div>

        </div>
    </DashboardLayout>
</template>
