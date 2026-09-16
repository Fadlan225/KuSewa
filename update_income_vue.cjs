const fs = require('fs');
const path = require('path');

const filePath = path.resolve('d:/Documents/SMKTI AIRLANGGA/Kelas XIIPPLG/Semester 1/PKL CV Rimbun Komputindo - Utama Web/KuSewa/resources/js/Pages/owner/Income.vue');
let content = fs.readFileSync(filePath, 'utf8');

// 1. Add props
content = content.replace(
    /recentTransactions: Array,/,
    "recentTransactions: Array,\n    isGlobal: Boolean,\n    hasUnits: Boolean,"
);

// 2. Summary Card: Aset Terbaik -> Unit Terbaik
const asetTerbaikRegex = /<!-- Aset Terbaik -->\s*<div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-slate-100">\s*<div class="flex justify-between items-start mb-1">\s*<p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">\s*<Building class="text-slate-400 w-3.5 h-3.5" \/> <span>Aset Terbaik<\/span>\s*<\/p>\s*<\/div>\s*<p class="text-lg lg:text-xl font-black text-\[#0A2540\] truncate leading-tight">{{ summaryData.asetTerbaik }}<\/p>\s*<div class="flex flex-col gap-0.5 mt-2">\s*<span class="text-xs font-bold text-slate-700">{{ formatCurrency\(summaryData.asetTerbaikIncome\) }}<\/span>\s*<span class="text-\[10px\] text-slate-400 font-normal">{{ summaryData.asetTerbaikPercent }}% dari total pendapatan<\/span>\s*<\/div>\s*<\/div>/;

const newAsetTerbaik = `<!-- Aset / Unit Terbaik -->
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
                </div>`;
content = content.replace(asetTerbaikRegex, newAsetTerbaik);

// 3. Tren Pendapatan Chart classes
content = content.replace(
    /<!-- TREN PENDAPATAN CHART -->\s*<Card class="lg:col-span-2 bg-white/,
    `<!-- TREN PENDAPATAN CHART -->
            <Card :class="isGlobal || hasUnits ? 'lg:col-span-2' : 'lg:col-span-3'" class="bg-white`
);

// 4. Donut Chart - Title & V-if
const donutRegex = /<!-- PENDAPATAN BERDASARKAN ASET \(Donut\) -->\s*<Card class="bg-white border border-slate-200\/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">\s*<CardHeader class="p-5 border-b border-slate-100 pb-4">\s*<CardTitle class="text-base font-bold text-slate-800">Sumber Pendapatan<\/CardTitle>\s*<p class="text-xs text-slate-500 mt-1">Kontribusi masing-masing aset terhadap total pendapatan\.<\/p>\s*<\/CardHeader>/;
const newDonut = `<!-- PENDAPATAN BERDASARKAN ASET / UNIT (Donut) -->
            <Card v-if="isGlobal || hasUnits" class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col p-2">
                <CardHeader class="p-5 border-b border-slate-100 pb-4">
                    <CardTitle class="text-base font-bold text-slate-800">{{ isGlobal ? 'Sumber Pendapatan' : 'Kontribusi Unit' }}</CardTitle>
                    <p class="text-xs text-slate-500 mt-1">{{ isGlobal ? 'Kontribusi masing-masing aset terhadap total pendapatan.' : 'Kontribusi masing-masing unit terhadap pendapatan.' }}</p>
                </CardHeader>`;
content = content.replace(donutRegex, newDonut);

// 5. Kontribusi Unit - V-if
content = content.replace(
    /<!-- KONTRIBUSI UNIT -->\s*<Card class="bg-white border border-slate-200\/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">/,
    `<!-- KONTRIBUSI UNIT -->
            <Card v-if="isGlobal || hasUnits" class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">`
);

// 6. Transaksi Terbaru - classes
content = content.replace(
    /<!-- TRANSAKSI TERBARU -->\s*<Card class="bg-white border border-slate-200\/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">/,
    `<!-- TRANSAKSI TERBARU -->
            <Card :class="isGlobal || hasUnits ? '' : 'lg:col-span-2'" class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">`
);

fs.writeFileSync(filePath, content, 'utf8');
console.log('Successfully refactored Income.vue');
