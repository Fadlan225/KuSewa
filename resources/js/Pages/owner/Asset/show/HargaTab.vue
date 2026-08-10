<script setup>
const props = defineProps({
    asset: Object,
    lowestPrice: Object,
});

const formatRupiah = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const rentalUnitLabel = (unit) => {
    const labels = { hour: "jam", day: "hari", night: "malam", month: "bulan" };
    return labels[unit] ?? "sewa";
};
</script>

<template>
    <div class="animate-in fade-in duration-300 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Harga / Pricings (Overview if units enabled) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6">
            <h2 class="font-bold text-slate-800 text-base mb-4">Informasi Harga</h2>
            <div v-if="asset.type?.allow_units" class="text-sm text-slate-600">
                Harga dikonfigurasi per masing-masing unit. Silakan lihat tab <b>Unit</b> untuk mengelola harga.
                <div class="mt-4 p-4 bg-slate-50 border border-slate-100 rounded-lg">
                    <span class="text-xs text-slate-500 block mb-1">Harga Sewa Termurah</span>
                    <span class="text-xl font-black text-[#F97316]">
                        {{ lowestPrice ? formatRupiah(lowestPrice.price) : '-' }}
                        <span v-if="lowestPrice" class="text-[11px] text-slate-400 font-semibold">/ {{ rentalUnitLabel(asset.type?.rental_unit) }}</span>
                    </span>
                </div>
            </div>
            <div v-else class="text-sm text-slate-600">
                (Fitur daftar harga untuk single-asset property akan dimunculkan di sini).
            </div>
        </div>

        <!-- Aturan (Placeholder) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6 flex flex-col items-center justify-center text-center">
            <i class="fa-solid fa-file-contract text-4xl text-slate-200 mb-3"></i>
            <h3 class="font-bold text-slate-700 mb-1">Aturan Properti</h3>
            <p class="text-sm text-slate-400 mb-4 max-w-xs">Tentukan aturan khusus seperti kebijakan hewan peliharaan, jam tenang, atau denda kerusakan.</p>
            <button class="text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg transition shadow-sm">
                Tambah Aturan
            </button>
        </div>
    </div>
</template>
