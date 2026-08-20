<script setup>
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';

const props = defineProps({
    form: Object,
    allowUnits: Boolean,
    assetTypeDetails: Object, // { rental_unit }
});

const rentalUnitOptions = [
    { label: 'Jam', value: 'hour' },
    { label: 'Hari', value: 'day' },
    { label: 'Malam', value: 'night' },
    { label: 'Minggu', value: 'week' },
    { label: 'Bulan', value: 'month' }
];

const formatPrice = (val) => {
    if (!val) return '';
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const updatePrice = (pricing, val) => {
    const numericVal = val.replace(/\D/g, '');
    pricing.price = numericVal ? parseInt(numericVal, 10) : '';
};

// Rental unit label untuk tooltip harga
const rentalUnitLabel = computed(() => {
    const map = {
        hour: '/Jam',
        night: '/Malam',
        day: '/Hari',
        month: '/Bulan',
    };
    return map[props.assetTypeDetails?.rental_unit] ?? '';
});
</script>

<template>
<div class="space-y-6">
    <!-- STEP 5: HARGA SEWA -->
    <h2 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-4">
        Pengaturan Harga Sewa
    </h2>

    <p class="text-sm text-slate-500 mb-4">
        Atur tarif sewa unit Anda. Anda dapat menambahkan berbagai variasi harga berdasarkan durasi (misal: Harian, Mingguan, Bulanan).
    </p>

    <!-- ============================================ -->
    <!-- HARGA — Tanpa Unit (satu harga untuk aset) -->
    <!-- ============================================ -->
    <div v-if="!allowUnits">
        <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-3">
            <label class="block text-sm font-semibold text-slate-700">
                Daftar Harga Sewa <span class="text-rose-500">*</span>
            </label>
            <button
                type="button"
                @click="form.pricings.push({ _id: Date.now(), duration: 1, rental_unit: assetTypeDetails?.rental_unit || 'month', price: '' })"
                class="text-sm font-semibold text-white bg-[#0A2540] hover:bg-[#123e6b] px-4 py-2 rounded-md transition cursor-pointer shadow-sm"
            >
                + Tambah Varian Harga
            </button>
        </div>
        <div class="space-y-4">
            <div v-for="(pricing, pIdx) in form.pricings" :key="pricing._id || pIdx" class="flex gap-4 items-center bg-white shadow-sm p-5 rounded-lg border border-slate-300 relative">
                <div class="w-1/4">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Durasi</label>
                    <input v-model="pricing.duration" type="number" min="1" class="w-full text-sm px-3 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition" required />
                </div>
                <div class="w-1/4">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Satuan</label>
                    <CustomSelect v-model="pricing.rental_unit" :options="rentalUnitOptions" placeholder="Pilih..." class="w-full" required />
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tarif Harga</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                        <input :value="formatPrice(pricing.price)" @input="updatePrice(pricing, $event.target.value)" type="text" placeholder="1.500.000" class="w-full text-sm pl-11 pr-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition" required />
                    </div>
                </div>
                <button
                    v-if="form.pricings.length > 1"
                    type="button"
                    @click="form.pricings.splice(pIdx, 1)"
                    class="w-10 h-10 rounded-md bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 mt-5 hover:bg-rose-500 hover:text-white transition cursor-pointer"
                    title="Hapus Varian Harga"
                >
                    <Trash2 class="text-sm" />
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- HARGA — Dengan Unit (Atur harga per unit) -->
    <!-- ============================================ -->
    <div v-if="allowUnits" class="space-y-6">
        <div
            v-for="(unit, unitIndex) in form.units"
            :key="unit._id"
            class="border border-slate-300 rounded-lg p-6 relative bg-white shadow-sm"
        >
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-md bg-[#FFC000] text-[#0A2540] font-bold flex items-center justify-center text-sm">
                    {{ unitIndex + 1 }}
                </div>
                <p class="text-sm font-semibold text-slate-800">{{ unit.name || `Tipe Unit ${unitIndex + 1}` }}</p>
                <span class="text-xs bg-slate-100 text-slate-500 px-2.5 py-1 rounded-md">{{ unit.quantity || 0 }} Unit</span>
            </div>

            <!-- Daftar Harga Sewa Unit -->
            <div class="mb-2">
                <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-3">
                    <label class="block text-sm font-semibold text-slate-700">
                        Harga Sewa Tipe Ini <span class="text-rose-500">*</span>
                    </label>
                    <button
                        type="button"
                        @click="unit.pricings.push({ _id: Date.now(), duration: 1, rental_unit: assetTypeDetails?.rental_unit || 'month', price: '' })"
                        class="text-sm font-semibold text-white bg-[#0A2540] hover:bg-[#123e6b] px-4 py-2 rounded-md transition cursor-pointer shadow-sm"
                    >
                        + Tambah Varian Harga
                    </button>
                </div>

                <div class="space-y-4">
                    <div v-for="(pricing, pIdx) in unit.pricings" :key="pricing._id || pIdx" class="flex gap-4 items-center bg-slate-50 p-5 rounded-lg border border-slate-300 relative">
                        <div class="w-1/4">
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Durasi</label>
                            <input v-model="pricing.duration" type="number" min="1" class="w-full text-sm px-3 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition" required />
                        </div>
                        <div class="w-1/4">
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Satuan</label>
                            <CustomSelect v-model="pricing.rental_unit" :options="rentalUnitOptions" placeholder="Pilih..." class="w-full" required />
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tarif Harga</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                                <input :value="formatPrice(pricing.price)" @input="updatePrice(pricing, $event.target.value)" type="text" placeholder="100.000" class="w-full text-sm pl-11 pr-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition" required />
                            </div>
                        </div>
                        <button
                            v-if="unit.pricings.length > 1"
                            type="button"
                            @click="unit.pricings.splice(pIdx, 1)"
                            class="w-10 h-10 rounded-md bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 mt-5 hover:bg-rose-500 hover:text-white transition cursor-pointer"
                            title="Hapus Varian Harga"
                        >
                            <Trash2 class="text-sm" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
