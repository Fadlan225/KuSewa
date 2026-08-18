<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/UI/card';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem, SelectGroup } from '@/Components/UI/select';
import { VisXYContainer, VisAxis, VisStackedBar, VisCrosshair, VisTooltip, VisLine } from '@unovis/vue';

const props = defineProps({
    initialPeriod: String,
    summaryData: Object,
    incomeTrendData: Array,
    assetIncomeData: Array,
    unitBreakdowns: Object,
    recentTransactions: Array,
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

        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            
            <!-- TOTAL PENDAPATAN (DOMINAN) -->
            <Card class="col-span-2 md:col-span-1 bg-[#0A2540] border-transparent shadow-lg rounded-2xl overflow-hidden flex flex-col justify-center text-white relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <CardContent class="p-5 flex flex-col justify-center h-full gap-3 relative z-10">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] md:text-xs text-slate-300 font-medium uppercase tracking-wider truncate">Total Pendapatan</p>
                            <p class="text-xl sm:text-2xl lg:text-3xl font-black text-white mt-1 truncate tracking-tight">{{ formatCurrency(summaryData.totalPendapatan) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-semibold" :class="props.summaryData.pendapatanGrowth >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                        <i class="fa-solid" :class="props.summaryData.pendapatanGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                        <span>{{ Math.abs(props.summaryData.pendapatanGrowth) }}% <span class="text-slate-400 font-normal">dari periode lalu</span></span>
                    </div>
                </CardContent>
            </Card>

            <Card class="hover:border-blue-500/50 transition-colors group border-slate-200/80 shadow-md rounded-2xl overflow-hidden flex flex-col justify-center">
                <CardContent class="p-4 flex flex-col justify-center h-full gap-2">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider truncate">Total Transaksi</p>
                            <p class="text-lg lg:text-xl font-black text-slate-800 mt-1 truncate">{{ summaryData.totalTransaksi }} Transaksi</p>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 flex shrink-0 items-center justify-center text-blue-500 group-hover:bg-blue-100 transition-colors">
                            <i class="fa-solid fa-receipt text-xs"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] font-semibold" :class="props.summaryData.transaksiGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                        <i class="fa-solid" :class="props.summaryData.transaksiGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                        <span>{{ Math.abs(props.summaryData.transaksiGrowth) }} transaksi <span class="text-slate-400 font-normal">dari periode lalu</span></span>
                    </div>
                </CardContent>
            </Card>

            <Card class="hover:border-[#FFC000]/50 transition-colors group border-slate-200/80 shadow-md rounded-2xl overflow-hidden flex flex-col justify-center">
                <CardContent class="p-4 flex flex-col justify-center h-full gap-2">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider truncate">Aset Terbaik</p>
                            <p class="text-base font-black text-slate-800 mt-1 truncate leading-tight">{{ summaryData.asetTerbaik }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 flex shrink-0 items-center justify-center text-amber-500 group-hover:bg-amber-100 transition-colors">
                            <i class="fa-solid fa-building text-xs"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-0.5 mt-1">
                        <span class="text-xs font-bold text-slate-700">{{ formatCurrency(summaryData.asetTerbaikIncome) }}</span>
                        <span class="text-[10px] text-slate-400 font-normal">{{ summaryData.asetTerbaikPercent }}% dari total pendapatan</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="hover:border-purple-500/50 transition-colors group border-slate-200/80 shadow-md rounded-2xl overflow-hidden flex flex-col justify-center">
                <CardContent class="p-4 flex flex-col justify-center h-full gap-2">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider truncate">Rata-rata per Transaksi</p>
                            <p class="text-lg lg:text-xl font-black text-slate-800 mt-1 truncate">{{ formatCurrency(summaryData.avgTransaksi) }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 flex shrink-0 items-center justify-center text-purple-500 group-hover:bg-purple-100 transition-colors">
                            <i class="fa-solid fa-calculator text-xs"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] font-medium text-slate-400 mt-1">
                        Diukur dari pendapatan bersih
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            
            <!-- TREN PENDAPATAN (Line Chart) -->
            <Card class="lg:col-span-2 border-slate-200/80 shadow-md rounded-2xl overflow-hidden flex flex-col">
                <CardHeader class="p-5 border-b border-slate-100 flex flex-row items-center justify-between pb-4">
                    <div>
                        <CardTitle class="text-base font-bold text-slate-800">Tren Pendapatan</CardTitle>
                        <p class="text-xs text-slate-500 mt-1">Pendapatan meningkat 18,4% dibanding periode sebelumnya.</p>
                    </div>
                    <!-- Mini filter / Toggle -->
                    <div class="flex items-center bg-slate-100 rounded-lg p-1 hidden sm:flex">
                        <button class="px-3 py-1 text-[11px] font-bold bg-white text-slate-800 rounded shadow-sm">Pendapatan</button>
                        <button class="px-3 py-1 text-[11px] font-medium text-slate-500 hover:text-slate-800 transition">Transaksi</button>
                    </div>
                </CardHeader>
                <CardContent class="p-4 sm:p-6 pb-2 flex-1 min-h-[300px]">
                    <div v-if="chartData.length" class="w-full h-[260px] relative unovis-chart-container text-xs">
                        <VisXYContainer :data="chartData" :height="260" :duration="800" :padding="{ top: 10, right: 10, left: 10, bottom: 0 }">
                            <VisLine :x="x" :y="y" color="#FFC000" :lineWidth="3" />
                            <VisAxis type="x" :tickFormat="tickFormatX" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-400 font-medium" />
                            <VisAxis type="y" :tickFormat="tickFormatY" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-400 font-medium" />
                            <VisTooltip />
                            <VisCrosshair :template="tooltipTemplate" color="#0A2540" />
                        </VisXYContainer>
                    </div>
                </CardContent>
            </Card>

            <!-- PENDAPATAN BERDASARKAN ASET (Donut) -->
            <Card class="border-slate-200/80 shadow-md rounded-2xl overflow-hidden flex flex-col">
                <CardHeader class="p-5 border-b border-slate-100 pb-4">
                    <CardTitle class="text-base font-bold text-slate-800">Sumber Pendapatan</CardTitle>
                    <p class="text-xs text-slate-500 mt-1">Kontribusi masing-masing aset terhadap total pendapatan.</p>
                </CardHeader>
                <CardContent class="p-5 flex-1 flex flex-col items-center justify-center">
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
                </CardContent>
            </Card>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6 pb-12">
            
            <!-- KONTRIBUSI UNIT -->
            <Card class="border-slate-200/80 shadow-md rounded-2xl overflow-hidden flex flex-col">
                <CardHeader class="p-5 border-b border-slate-100 pb-4">
                    <CardTitle class="text-base font-bold text-slate-800">Kontribusi Unit</CardTitle>
                    <p class="text-xs text-slate-500 mt-1">
                        Rincian pendapatan unit pada <span class="font-bold text-[#0A2540]">{{ selectedAssetForUnit }}</span>.
                    </p>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="divide-y divide-slate-100 min-h-[200px]">
                        <div v-for="(unit, idx) in activeUnitBreakdown" :key="idx" class="p-5 hover:bg-slate-50 transition-colors flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 shrink-0">
                                    <i class="fa-solid fa-door-open"></i>
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
                </CardContent>
            </Card>

            <!-- TRANSAKSI TERBARU -->
            <Card class="border-slate-200/80 shadow-md rounded-2xl overflow-hidden flex flex-col">
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
                    <div class="divide-y divide-slate-100 min-h-[200px]">
                        <div v-for="trx in recentTransactions" :key="trx.id" class="p-4 hover:bg-slate-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold text-slate-800">{{ trx.id }}</span>
                                    <span :class="[getStatusClass(trx.status), 'text-[9px] font-bold px-2 py-0.5 rounded-full']">
                                        {{ trx.status }}
                                    </span>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-600">{{ trx.asset }} - {{ trx.unit }}</span>
                                <span class="text-[10px] text-slate-400"><i class="fa-regular fa-calendar mr-1"></i> {{ trx.date }}</span>
                            </div>
                            <div class="text-sm font-black text-[#0A2540]">
                                {{ formatCurrency(trx.total) }}
                            </div>
                        </div>
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