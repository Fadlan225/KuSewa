const fs = require('fs');
const path = require('path');

const filePath = path.resolve('d:/Documents/SMKTI AIRLANGGA/Kelas XIIPPLG/Semester 1/PKL CV Rimbun Komputindo - Utama Web/KuSewa/resources/js/Pages/owner/Income.vue');
let content = fs.readFileSync(filePath, 'utf8');

// Add IncomeEmptyIllustration
if (!content.includes('IncomeEmptyIllustration.vue')) {
    content = content.replace(
        "import { VisXYContainer",
        "import IncomeEmptyIllustration from '@/Components/ui/Icons/IncomeEmptyIllustration.vue';\nimport { VisXYContainer"
    );
}

// Add chartTotalSum
if (!content.includes('const chartTotalSum = computed')) {
    content = content.replace(
        "// Unovis Helpers",
        "const chartTotalSum = computed(() => chartData.value.reduce((acc, curr) => acc + curr.income, 0));\n\n// Unovis Helpers"
    );
}

// Replace Summary Cards
const summaryCardsRegex = /<!-- SUMMARY CARDS -->\s*<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">([\s\S]*?)<\/div>\s*<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">/;
const newSummaryCards = `<!-- SUMMARY CARDS - Clean Panel Design -->
        <div class="bg-white border border-slate-200/80 rounded-xl shadow-sm mb-6 mt-6">
            <div class="grid grid-cols-2 xl:grid-cols-4 border-slate-100">
                <!-- Total Pendapatan -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-b xl:border-b-0 border-slate-100 relative overflow-hidden bg-[#0A2540]">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-xs text-white/70 font-medium tracking-wide flex items-center gap-2">
                                <AppIcon iconClass="fa-solid fa-wallet" class="text-white/50 w-3.5 h-3.5" /> <span>Total Pendapatan</span>
                            </p>
                        </div>
                        <p class="text-2xl lg:text-3xl font-black text-white truncate">{{ formatCurrency(summaryData.totalPendapatan) }}</p>
                        <div class="flex items-center gap-1.5 text-xs font-semibold mt-2" :class="props.summaryData.pendapatanGrowth >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                            <AppIcon iconClass="fa-solid" :class="props.summaryData.pendapatanGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'" />
                            <span>{{ Math.abs(props.summaryData.pendapatanGrowth) }}% <span class="text-white/40 font-normal">dari periode lalu</span></span>
                        </div>
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

                <!-- Aset Terbaik -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-slate-100">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Building class="text-slate-400 w-3.5 h-3.5" /> <span>Aset Terbaik</span>
                        </p>
                    </div>
                    <p class="text-lg lg:text-xl font-black text-[#0A2540] truncate leading-tight">{{ summaryData.asetTerbaik }}</p>
                    <div class="flex flex-col gap-0.5 mt-2">
                        <span class="text-xs font-bold text-slate-700">{{ formatCurrency(summaryData.asetTerbaikIncome) }}</span>
                        <span class="text-[10px] text-slate-400 font-normal">{{ summaryData.asetTerbaikPercent }}% dari total pendapatan</span>
                    </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">`;
content = content.replace(summaryCardsRegex, newSummaryCards);

// Replace Tren Pendapatan Chart
const trenChartRegex = /<!-- TREN PENDAPATAN \(Line Chart\) -->\s*<Card class="lg:col-span-2 border-slate-200\/80 shadow-md rounded-2xl overflow-hidden flex flex-col">([\s\S]*?)<\/Card>/;
const newTrenChart = `<!-- TREN PENDAPATAN CHART -->
            <Card class="lg:col-span-2 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">
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
            </Card>`;
content = content.replace(trenChartRegex, newTrenChart);

// Replace Donut Chart Card wrapper
content = content.replace(
    /<!-- PENDAPATAN BERDASARKAN ASET \(Donut\) -->\s*<Card class="border-slate-200\/80 shadow-md rounded-2xl overflow-hidden flex flex-col">/,
    `<!-- PENDAPATAN BERDASARKAN ASET (Donut) -->
            <Card class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">`
);

// Replace Kontribusi Unit Card wrapper
content = content.replace(
    /<!-- KONTRIBUSI UNIT -->\s*<Card class="border-slate-200\/80 shadow-md rounded-2xl overflow-hidden flex flex-col">/,
    `<!-- KONTRIBUSI UNIT -->
            <Card class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">`
);

// Replace Transaksi Terbaru Card wrapper
content = content.replace(
    /<!-- TRANSAKSI TERBARU -->\s*<Card class="border-slate-200\/80 shadow-md rounded-2xl overflow-hidden flex flex-col">/,
    `<!-- TRANSAKSI TERBARU -->
            <Card class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">`
);

// Add CSS to the end if not present
if (!content.includes('<style>')) {
    content += `

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
`;
}

fs.writeFileSync(filePath, content, 'utf8');
console.log('Done replacing Income.vue');
