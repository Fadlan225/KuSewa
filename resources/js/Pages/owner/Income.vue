<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Receipt, Building, Calculator, DoorOpen, Calendar, Wallet } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem, SelectGroup } from '@/Components/ui/select';
import IncomeEmptyIllustration from '@/Components/ui/Icons/IncomeEmptyIllustration.vue';
import SpreadEmptyIllustration from '@/Components/ui/Icons/SpreadEmptyIllustration.vue';
import BookingEmptyIllustration from '@/Components/ui/Icons/BookingEmptyIllustration.vue';
import AssetStatusEmptyIllustration from '@/Components/ui/Icons/AssetStatusEmptyIllustration.vue';

import { VisXYContainer, VisAxis, VisStackedBar, VisCrosshair, VisTooltip, VisLine } from '@unovis/vue';

const props = defineProps({
    initialPeriod: String,
    summaryData: Object,
    incomeTrendData: Array,
    assetIncomeData: Array,
    unitBreakdowns: Object,
    recentTransactions: Array,
    isGlobal: Boolean,
    hasUnits: Boolean,
});

const selectedPeriod = ref(props.initialPeriod || 'bulan_ini');

// Update data from backend on period change
const updatePeriod = (val) => {
    selectedPeriod.value = val;
    router.get(route('owner.income'), { period: val }, { preserveState: true, replace: true });
};
const periodLabel = computed(() => {
    switch (selectedPeriod.value) {
        case 'hari_ini': return 'Hari Ini';
        case '7_hari': return '7 Hari Terakhir';
        case 'bulan_ini': return 'Bulan Ini';
        case 'bulan_lalu': return 'Bulan Lalu';
        case '3_bulan': return '3 Bulan Terakhir';
        case 'tahun_ini': return 'Tahun Ini';
        default: return 'Periode Terpilih';
    }
});

const isChartMounted = ref(false);
onMounted(() => {
    setTimeout(() => {
        isChartMounted.value = true;
    }, 150);
});

// Format helpers
const formatCurrency = (v) => 'Rp ' + Number(v || 0).toLocaleString('id-ID');
const formatCompactCurrency = (v) => {
    if (!v) return '0';
    if (v >= 1000000) return (v / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' Jt';
    if (v >= 1000) return (v / 1000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' Rb';
    return v.toLocaleString('id-ID');
};

// We already have summaryData, incomeTrendData, assetIncomeData, unitBreakdowns, and recentTransactions from props

const chartData = computed(() => {
    return props.incomeTrendData.map((item, i) => ({
        ...item,
        _animIncome: isChartMounted.value ? item.income : 0,
        x: i
    }));
});

const chartTotalSum = computed(() => chartData.value.reduce((acc, curr) => acc + curr.income, 0));

// Unovis Helpers
const x = (d) => d.x;
const y = [d => d._animIncome];
const tickFormatY = (d) => formatCompactCurrency(d);
const tickFormatX = (i) => chartData.value[i]?.label || '';
const tooltipTemplate = (d) => `
    <div class="flex flex-col gap-1 px-1 py-1 font-sans">
        <span class="text-xs font-bold text-slate-500">${d.label}</span>
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-[#FFC000]"></div>
            <span class="text-sm font-black text-slate-800">${formatCurrency(d.income)}</span>
        </div>
    </div>
`;

const donutChartTotal = computed(() => props.assetIncomeData.reduce((acc, curr) => acc + curr.income, 0));

const activeAssetHover = ref(null);

const donutSlices = computed(() => {
    const total = donutChartTotal.value;
    if (total === 0) return [];

    let currentOffset = 0;
    const circumference = 2 * Math.PI * 40; // r=40

    return props.assetIncomeData.map(item => {
        const percentage = item.income / total;
        const length = percentage * circumference;
        const gap = circumference - length;
        const slice = {
            ...item,
            dash: length,
            gap: gap,
            offset: currentOffset,
            isHovered: activeAssetHover.value === item.name || selectedAssetForUnit.value === item.name
        };
        currentOffset -= length;
        return slice;
    });
});

// Unit Breakdown Data
const defaultSelectedAsset = computed(() => props.assetIncomeData[0]?.name || '-');
const selectedAssetForUnit = ref(defaultSelectedAsset.value);

const activeUnitBreakdown = computed(() => {
    const assetKey = selectedAssetForUnit.value;
    return props.unitBreakdowns[assetKey] || [];
});

const getStatusClass = (status) => {
    switch (status) {
        case 'Dibayar': return 'bg-emerald-50 text-emerald-600 border border-emerald-200';
        case 'Selesai': return 'bg-blue-50 text-blue-600 border border-blue-200';
        case 'Menunggu Pembayaran': return 'bg-amber-50 text-amber-600 border border-amber-200';
        case 'Dibatalkan': return 'bg-rose-50 text-rose-600 border border-rose-200';
        default: return 'bg-gray-50 text-gray-600 border border-gray-200';
    }
};

</script>

<template>
    <Head title="Pendapatan - Owner" />

    <DashboardLayout
        title="Pendapatan"
        description="Pantau pendapatan bersih dari seluruh aset Anda."
        role="Owner"
    >
        <!-- HEADER KANAN (Filter) -->
        <template #actions>
            <Select :model-value="selectedPeriod" @update:model-value="updatePeriod">
                <SelectTrigger class="w-[180px] h-10 text-sm font-semibold bg-white border-slate-200 rounded-xl shadow-sm focus:ring-slate-200 focus:border-slate-200 focus:ring-offset-0">
                    <SelectValue placeholder="Pilih Periode" />
                </SelectTrigger>
                <SelectContent class="text-sm rounded-xl border-slate-200 shadow-xl z-[9999] bg-white">
                    <SelectGroup>
                        <SelectItem value="hari_ini" class="focus:bg-[#FFC000]/20 cursor-pointer">Hari ini</SelectItem>
                        <SelectItem value="7_hari" class="focus:bg-[#FFC000]/20 cursor-pointer">7 Hari</SelectItem>
                        <SelectItem value="bulan_ini" class="focus:bg-[#FFC000]/20 cursor-pointer">Bulan Ini</SelectItem>
                        <SelectItem value="bulan_lalu" class="focus:bg-[#FFC000]/20 cursor-pointer">Bulan Lalu</SelectItem>
                        <SelectItem value="3_bulan" class="focus:bg-[#FFC000]/20 cursor-pointer">3 Bulan</SelectItem>
                        <SelectItem value="tahun_ini" class="focus:bg-[#FFC000]/20 cursor-pointer">Tahun Ini</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </template>

        <!-- SUMMARY CARDS - Clean Panel Design -->
        <div class="bg-white border border-slate-200/80 rounded-xl shadow-sm mb-6 mt-6">
            <div class="grid grid-cols-2 xl:grid-cols-4 border-slate-100">
                <!-- Total Pendapatan -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-b xl:border-b-0 border-slate-100">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Wallet class="text-slate-400 w-3.5 h-3.5" /> <span>Total Pendapatan</span>
                        </p>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-[#0A2540] truncate">{{ formatCurrency(summaryData.totalPendapatan) }}</p>
                    <div class="flex items-center gap-1.5 text-xs font-semibold mt-2" :class="props.summaryData.pendapatanGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                        <AppIcon iconClass="fa-solid" :class="props.summaryData.pendapatanGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'" />
                        <span>{{ Math.abs(props.summaryData.pendapatanGrowth) }}% <span class="text-slate-400 font-normal">dari periode lalu</span></span>
                    </div>
                </div>

                <!-- Total Transaksi -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-b xl:border-b-0 xl:border-r border-slate-100">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Receipt class="text-slate-400 w-3.5 h-3.5" /> <span>Total Transaksi</span>
                        </p>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ summaryData.totalTransaksi }} <span class="text-sm font-bold text-slate-400">Trx</span></p>
                    <div class="flex items-center gap-1.5 text-[10px] font-semibold mt-2" :class="props.summaryData.transaksiGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                        <AppIcon iconClass="fa-solid" :class="props.summaryData.transaksiGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'" />
                        <span>{{ Math.abs(props.summaryData.transaksiGrowth) }} trx <span class="text-slate-400 font-normal">dari periode lalu</span></span>
                    </div>
                </div>

                <!-- Aset / Unit Terbaik -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-slate-100">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Building class="text-slate-400 w-3.5 h-3.5" /> 
                            <span>{{ isGlobal ? 'Aset Terbaik' : 'Unit Terbaik' }}</span>
                        </p>
                    </div>
                    <template v-if="isGlobal || hasUnits">
                        <p class="text-lg lg:text-xl font-black text-[#0A2540] truncate leading-tight">{{ summaryData.asetTerbaik }}</p>
                        <div class="flex flex-col gap-0.5 mt-2">
                            <span class="text-xs font-bold text-slate-700">{{ formatCurrency(summaryData.asetTerbaikIncome) }}</span>
                            <span class="text-[10px] text-slate-400 font-normal">{{ summaryData.asetTerbaikPercent }}% dari total pendapatan</span>
                        </div>
                    </template>
                    <template v-else>
                        <p class="text-lg lg:text-xl font-black text-[#0A2540] truncate leading-tight">-</p>
                        <div class="flex flex-col gap-0.5 mt-2">
                            <span class="text-xs font-bold text-slate-700">Rp 0</span>
                            <span class="text-[10px] text-slate-400 font-normal">Tidak ada unit</span>
                        </div>
                    </template>
                </div>

                <!-- Rata-rata per Transaksi -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Calculator class="text-slate-400 w-3.5 h-3.5" /> <span>Rata-rata Transaksi</span>
                        </p>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ formatCurrency(summaryData.avgTransaksi) }}</p>
                    <div class="flex items-center gap-1.5 text-[10px] font-medium text-slate-400 mt-2">
                        Diukur dari pendapatan bersih
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            
            <!-- TREN PENDAPATAN CHART -->
            <Card :class="isGlobal || hasUnits ? 'lg:col-span-2' : 'lg:col-span-3'" class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">
                <CardHeader class="relative z-20 flex flex-col items-stretch p-4 sm:flex-row pb-6">
                    <div class="flex flex-1 flex-col justify-center gap-1 text-left">
                        <CardTitle class="text-base font-black text-slate-800 tracking-tight">Tren Pendapatan</CardTitle>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Menampilkan pendapatan {{ periodLabel }}.
                        </p>
                    </div>
                </CardHeader>
                <CardContent class="p-4 pt-0 flex-1 min-h-[320px] relative z-0">
                    <div v-if="chartData.length > 0 && chartTotalSum > 0" class="w-full h-full relative text-xs unovis-chart-container z-0">
                        <VisXYContainer :data="chartData" :height="300" :duration="800">
                            <!-- Bar Tipis Kotak, Warna Navy sebagai warna data utama -->
                            <VisStackedBar :x="x" :y="y" color="#0A2540" :roundedCorners="2" :barPadding="0.4" />
                            <VisAxis type="x" :tickFormat="tickFormatX" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-400" />
                            <VisAxis type="y" :tickFormat="tickFormatY" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-400" />
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

            <!-- PENDAPATAN BERDASARKAN ASET / UNIT (Donut) -->
            <Card v-if="isGlobal || hasUnits" class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">
                <CardHeader class="p-5 border-b border-slate-100 pb-4">
                    <CardTitle class="text-base font-bold text-slate-800">{{ isGlobal ? 'Sumber Pendapatan' : 'Kontribusi Unit' }}</CardTitle>
                    <p class="text-xs text-slate-500 mt-1">{{ isGlobal ? 'Kontribusi masing-masing aset terhadap total pendapatan.' : 'Kontribusi masing-masing unit terhadap pendapatan.' }}</p>
                </CardHeader>
                <CardContent class="p-5 flex-1 flex flex-col items-center justify-center">
                    <template v-if="donutChartTotal > 0">
                        <!-- Donut Chart -->
                        <div class="relative w-48 h-48 mb-6">
                        <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                            <circle v-for="slice in donutSlices" :key="slice.name"
                                    cx="50" cy="50" r="40"
                                    fill="transparent"
                                    :stroke="slice.color"
                                    :stroke-width="slice.isHovered ? 24 : 20"
                                    :stroke-dasharray="`${slice.dash} ${slice.gap}`"
                                    :stroke-dashoffset="slice.offset"
                                    class="transition-all duration-500 ease-out cursor-pointer"
                                    @mouseenter="activeAssetHover = slice.name; selectedAssetForUnit = slice.name"
                                    @mouseleave="activeAssetHover = null"
                            />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Total</span>
                            <span class="text-sm font-black text-slate-800 leading-none">{{ formatCompactCurrency(donutChartTotal) }}</span>
                        </div>
                    </div>
                        <!-- Legend -->
                        <div class="w-full space-y-3 px-1">
                        <div v-for="item in donutSlices" :key="item.name" 
                             @mouseenter="activeAssetHover = item.name; selectedAssetForUnit = item.name"
                             @mouseleave="activeAssetHover = null"
                             @click="selectedAssetForUnit = item.name"
                             class="flex items-center justify-between text-sm cursor-pointer p-2 rounded-xl transition-colors border"
                             :class="selectedAssetForUnit === item.name ? 'bg-slate-50 border-slate-200' : 'border-transparent hover:bg-slate-50/50'">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full shadow-sm shrink-0" :style="{ backgroundColor: item.color }"></span>
                                <div class="flex flex-col">
                                    <span class="text-slate-800 font-bold text-xs truncate max-w-[120px]">{{ item.name }}</span>
                                    <span class="text-slate-400 text-[10px]">{{ formatCompactCurrency(item.income) }}</span>
                                </div>
                            </div>
                            <div class="text-slate-600 font-black text-xs">{{ item.percent }}%</div>
                        </div>
                    </div>
                    </template>
                    <div v-else class="w-full h-full min-h-[250px] flex flex-col items-center justify-center text-slate-400 py-6">
                        <div class="w-32 h-auto mb-4 opacity-60 pointer-events-none">
                            <SpreadEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Belum ada pendapatan</p>
                        <p class="text-sm text-slate-500 mb-5 text-center font-medium max-w-[280px]">Daftarkan aset dan biarkan calon penyewa menemukannya.</p>
                    </div>
                </CardContent>
            </Card>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6 pb-12">
            
            <!-- KONTRIBUSI UNIT -->
            <Card v-if="isGlobal || hasUnits" class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">
                <CardHeader class="p-5 border-b border-slate-100 pb-4">
                    <CardTitle class="text-base font-bold text-slate-800">Kontribusi Unit</CardTitle>
                    <p class="text-xs text-slate-500 mt-1">
                        Rincian pendapatan unit pada <span class="font-bold text-[#0A2540]">{{ selectedAssetForUnit }}</span>.
                    </p>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="activeUnitBreakdown.length > 0" class="divide-y divide-slate-100 min-h-[200px]">
                        <div v-for="(unit, idx) in activeUnitBreakdown" :key="idx" class="p-5 hover:bg-slate-50 transition-colors flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 shrink-0">
                                    <DoorOpen class="" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800">{{ unit.name }}</span>
                                    <span class="text-xs font-semibold text-slate-500 mt-0.5">{{ formatCurrency(unit.income) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Progress Bar -->
                                <div class="w-24 h-2 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                    <div class="h-full bg-emerald-500 rounded-full" :style="{ width: unit.percent + '%' }"></div>
                                </div>
                                <span class="text-xs font-black text-slate-700 w-8 text-right">{{ unit.percent }}%</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="w-full h-full min-h-[200px] flex flex-col items-center justify-center text-slate-400 py-6">
                        <div class="w-24 h-auto mb-4 opacity-60 pointer-events-none">
                            <AssetStatusEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-sm font-black text-[#0A2540] tracking-tight mb-1">Belum ada unit yang disewa</p>
                        <p class="text-xs text-slate-500 text-center font-medium max-w-[240px]">Unit pada aset ini belum menghasilkan pendapatan.</p>
                    </div>
                </CardContent>
            </Card>

            <!-- TRANSAKSI TERBARU -->
            <Card :class="isGlobal || hasUnits ? '' : 'lg:col-span-2'" class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">
                <CardHeader class="p-5 border-b border-slate-100 pb-4 flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base font-bold text-slate-800">Transaksi Terbaru</CardTitle>
                        <p class="text-xs text-slate-500 mt-1">Riwayat transaksi pemesanan.</p>
                    </div>
                    <Link :href="route('owner.dashboard')" class="text-[11px] font-bold text-[#0A2540] bg-[#FFC000]/20 hover:bg-[#FFC000]/30 px-3 py-1.5 rounded-lg transition-colors hidden sm:block">
                        Semua Transaksi
                    </Link>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="recentTransactions.length > 0" class="divide-y divide-slate-100 min-h-[200px]">
                        <div v-for="trx in recentTransactions" :key="trx.id" class="p-4 hover:bg-slate-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold text-slate-800">{{ trx.id }}</span>
                                    <span :class="[getStatusClass(trx.status), 'text-[9px] font-bold px-2 py-0.5 rounded-full']">
                                        {{ trx.status }}
                                    </span>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-600">{{ trx.asset }} - {{ trx.unit }}</span>
                                <span class="text-[10px] text-slate-400"><Calendar class="mr-1" /> {{ trx.date }}</span>
                            </div>
                            <div class="text-sm font-black text-[#0A2540]">
                                {{ formatCurrency(trx.total) }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="w-full h-full min-h-[200px] flex flex-col items-center justify-center text-slate-400 py-6">
                        <div class="w-24 h-auto mb-4 opacity-60 pointer-events-none">
                            <BookingEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-sm font-black text-[#0A2540] tracking-tight mb-1">Belum ada transaksi</p>
                        <p class="text-xs text-slate-500 text-center font-medium max-w-[240px]">Riwayat pembayaran akan muncul di sini.</p>
                    </div>
                </CardContent>
            </Card>

        </div>

    </DashboardLayout>
</template>

<style scoped>
.unovis-chart-container {
    transition: all 0.3s ease;
}
</style>

<style scoped>
/* CSS Override untuk merubah warna Unovis Bar saat di-hover */
.unovis-chart-container rect {
    transition: fill 0.2s ease, opacity 0.2s ease;
}
.unovis-chart-container rect:hover {
    fill: #FFC000 !important; /* Kuning KitaSewa saat hover */
    opacity: 1 !important;
}
</style>
