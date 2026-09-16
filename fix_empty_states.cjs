const fs = require('fs');
const path = require('path');

const filePath = path.resolve('d:/Documents/SMKTI AIRLANGGA/Kelas XIIPPLG/Semester 1/PKL CV Rimbun Komputindo - Utama Web/KuSewa/resources/js/Pages/owner/Income.vue');
let content = fs.readFileSync(filePath, 'utf8');

// Add imports
const importsToAdd = `import SpreadEmptyIllustration from '@/Components/ui/Icons/SpreadEmptyIllustration.vue';
import BookingEmptyIllustration from '@/Components/ui/Icons/BookingEmptyIllustration.vue';
import AssetStatusEmptyIllustration from '@/Components/ui/Icons/AssetStatusEmptyIllustration.vue';
`;

if (!content.includes('SpreadEmptyIllustration')) {
    content = content.replace(
        "import IncomeEmptyIllustration from '@/Components/ui/Icons/IncomeEmptyIllustration.vue';",
        "import IncomeEmptyIllustration from '@/Components/ui/Icons/IncomeEmptyIllustration.vue';\n" + importsToAdd
    );
}

// 1. Sumber Pendapatan (Donut)
const donutRegex = /<CardContent class="p-5 flex-1 flex flex-col items-center justify-center">\s*<!-- Donut Chart -->\s*<div class="relative w-48 h-48 mb-6">([\s\S]*?)<\/div>\s*<!-- Legend -->\s*<div class="w-full space-y-3 px-1">([\s\S]*?)<\/div>\s*<\/CardContent>/;
const newDonut = `<CardContent class="p-5 flex-1 flex flex-col items-center justify-center">
                    <template v-if="donutChartTotal > 0">
                        <!-- Donut Chart -->
                        <div class="relative w-48 h-48 mb-6">$1</div>
                        <!-- Legend -->
                        <div class="w-full space-y-3 px-1">$2</div>
                    </template>
                    <div v-else class="w-full h-full min-h-[250px] flex flex-col items-center justify-center text-slate-400 py-6">
                        <div class="w-32 h-auto mb-4 opacity-60 pointer-events-none">
                            <SpreadEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-base font-black text-[#0A2540] tracking-tight mb-1">Belum ada pendapatan</p>
                        <p class="text-sm text-slate-500 mb-5 text-center font-medium max-w-[280px]">Daftarkan aset dan biarkan calon penyewa menemukannya.</p>
                    </div>
                </CardContent>`;
content = content.replace(donutRegex, newDonut);

// 2. Kontribusi Unit
const kontribusiUnitRegex = /<!-- KONTRIBUSI UNIT -->\s*<Card class="bg-white border border-slate-200\/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">\s*<CardHeader class="p-5 border-b border-slate-100 pb-4">\s*<CardTitle class="text-base font-bold text-slate-800">Kontribusi Unit<\/CardTitle>\s*<p class="text-xs text-slate-500 mt-1">\s*Rincian pendapatan unit pada <span class="font-bold text-\[#0A2540\]">{{ selectedAssetForUnit }}<\/span>\.\s*<\/p>\s*<\/CardHeader>\s*<CardContent class="p-0">\s*<div class="divide-y divide-slate-100 min-h-\[200px\]">([\s\S]*?)<\/div>\s*<\/CardContent>\s*<\/Card>/;
const newKontribusiUnit = `<!-- KONTRIBUSI UNIT -->
            <Card class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">
                <CardHeader class="p-5 border-b border-slate-100 pb-4">
                    <CardTitle class="text-base font-bold text-slate-800">Kontribusi Unit</CardTitle>
                    <p class="text-xs text-slate-500 mt-1">
                        Rincian pendapatan unit pada <span class="font-bold text-[#0A2540]">{{ selectedAssetForUnit }}</span>.
                    </p>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="activeUnitBreakdown.length > 0" class="divide-y divide-slate-100 min-h-[200px]">$1</div>
                    <div v-else class="w-full h-full min-h-[200px] flex flex-col items-center justify-center text-slate-400 py-6">
                        <div class="w-24 h-auto mb-4 opacity-60 pointer-events-none">
                            <AssetStatusEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-sm font-black text-[#0A2540] tracking-tight mb-1">Belum ada unit yang disewa</p>
                        <p class="text-xs text-slate-500 text-center font-medium max-w-[240px]">Unit pada aset ini belum menghasilkan pendapatan.</p>
                    </div>
                </CardContent>
            </Card>`;
content = content.replace(kontribusiUnitRegex, newKontribusiUnit);


// 3. Transaksi Terbaru
const transaksiTerbaruRegex = /<!-- TRANSAKSI TERBARU -->\s*<Card class="bg-white border border-slate-200\/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">\s*<CardHeader class="p-5 border-b border-slate-100 pb-4 flex flex-row items-center justify-between">\s*<div>\s*<CardTitle class="text-base font-bold text-slate-800">Transaksi Terbaru<\/CardTitle>\s*<p class="text-xs text-slate-500 mt-1">Riwayat transaksi pemesanan\.<\/p>\s*<\/div>\s*<Link :href="route\('owner\.dashboard'\)" class="text-\[11px\] font-bold text-\[#0A2540\] bg-\[#FFC000\]\/20 hover:bg-\[#FFC000\]\/30 px-3 py-1\.5 rounded-lg transition-colors hidden sm:block">\s*Semua Transaksi\s*<\/Link>\s*<\/CardHeader>\s*<CardContent class="p-0">\s*<div class="divide-y divide-slate-100 min-h-\[200px\]">([\s\S]*?)<\/div>\s*<\/CardContent>\s*<\/Card>/;
const newTransaksiTerbaru = `<!-- TRANSAKSI TERBARU -->
            <Card class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col">
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
                    <div v-if="recentTransactions.length > 0" class="divide-y divide-slate-100 min-h-[200px]">$1</div>
                    <div v-else class="w-full h-full min-h-[200px] flex flex-col items-center justify-center text-slate-400 py-6">
                        <div class="w-24 h-auto mb-4 opacity-60 pointer-events-none">
                            <BookingEmptyIllustration class="w-full h-auto drop-shadow-sm" />
                        </div>
                        <p class="text-sm font-black text-[#0A2540] tracking-tight mb-1">Belum ada transaksi</p>
                        <p class="text-xs text-slate-500 text-center font-medium max-w-[240px]">Riwayat pembayaran akan muncul di sini.</p>
                    </div>
                </CardContent>
            </Card>`;
content = content.replace(transaksiTerbaruRegex, newTransaksiTerbaru);

fs.writeFileSync(filePath, content, 'utf8');
console.log('Done fixing empty states');
