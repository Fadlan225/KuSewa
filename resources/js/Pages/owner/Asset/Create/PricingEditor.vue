<script setup>
import { computed, ref, watch } from 'vue';
import { Check, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    pricings: { type: Array, required: true },
    detail: { type: Object, required: true },
    assetTypeDetails: Object,
});

const emit = defineEmits(['update:pricings', 'update:detail']);

const formatPrice = (val) => {
    if (!val) return '';
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const updatePrice = (obj, key, valStr) => {
    const numericVal = valStr.replace(/\D/g, '');
    obj[key] = numericVal ? parseInt(numericVal, 10) : '';
};

// Base Rental Unit Title
const rentalUnitTitle = computed(() => {
    const map = {
        hour: 'Jam',
        night: 'Malam',
        day: 'Hari',
        week: 'Minggu',
        month: 'Bulan',
    };
    return map[props.assetTypeDetails?.rental_unit] ?? 'Bulan';
});

// Pastikan pricings[0] selalu ada (Base Price)
if (!props.pricings || props.pricings.length === 0) {
    props.pricings.push({ _id: Date.now(), duration: 1, rental_unit: props.assetTypeDetails?.rental_unit || 'month', price: '' });
}
const basePriceObj = computed(() => props.pricings[0]);

const specialPriceOptions = computed(() => {
    const unit = props.assetTypeDetails?.rental_unit || 'month';
    if (unit === 'hour') {
        return [
            { duration: 4, rental_unit: 'hour', label: '4 Jam' },
            { duration: 8, rental_unit: 'hour', label: '8 Jam (Full Day)' },
        ];
    } else if (unit === 'day' || unit === 'night') {
        return [
            { duration: 7, rental_unit: 'day', label: '1 Minggu' },
            { duration: 30, rental_unit: 'day', label: '1 Bulan' },
        ];
    } else if (unit === 'week') {
        return [
            { duration: 4, rental_unit: 'week', label: '1 Bulan' },
            { duration: 12, rental_unit: 'week', label: '3 Bulan' },
        ];
    } else {
        return [
            { duration: 3, rental_unit: 'month', label: '3 bulan' },
            { duration: 6, rental_unit: 'month', label: '6 bulan' },
            { duration: 12, rental_unit: 'month', label: 'Tahun' },
        ];
    }
});

const specialOptionKeys = computed(() => specialPriceOptions.value.map(opt => `${opt.duration}-${opt.rental_unit}`));

// Menentukan apakah opsi diaktifkan
const hasWeeklyPrice = ref(props.pricings.some((p, i) => i !== 0 && p.duration === 1 && p.rental_unit === 'week'));
const hasDailyPrice = ref(props.pricings.some((p, i) => i !== 0 && p.duration === 1 && (p.rental_unit === 'day' || p.rental_unit === 'night')));
const hasSpecialPrice = ref(props.pricings.some((p, i) => i !== 0 && specialOptionKeys.value.includes(`${p.duration}-${p.rental_unit}`)));
const hasDeposit = ref(!!props.detail.deposit_amount);
const hasDp = ref(!!props.detail.dp_percentage);
const hasAdditionalFees = ref(Array.isArray(props.detail.additional_fees) && props.detail.additional_fees.length > 0);

const weeklyPricing = computed(() => props.pricings.find((p, i) => i !== 0 && p.duration === 1 && p.rental_unit === 'week'));
const dailyPricing = computed(() => props.pricings.find((p, i) => i !== 0 && p.duration === 1 && (p.rental_unit === 'day' || p.rental_unit === 'night')));
const specialPricings = computed(() => props.pricings.filter((p, i) => i !== 0 && specialOptionKeys.value.includes(`${p.duration}-${p.rental_unit}`)));

// Watchers for toggles
watch(hasWeeklyPrice, (val) => {
    if (val) {
        if (!props.pricings.some((p, i) => i !== 0 && p.duration === 1 && p.rental_unit === 'week')) {
            props.pricings.push({
                _id: Date.now() + Math.random(),
                duration: 1,
                rental_unit: 'week',
                price: '',
                _label: 'Mingguan'
            });
        }
    } else {
        const index = props.pricings.findIndex((p, i) => i !== 0 && p.duration === 1 && p.rental_unit === 'week');
        if (index !== -1) {
            props.pricings.splice(index, 1);
        }
    }
});

watch(hasDailyPrice, (val) => {
    if (val) {
        if (!props.pricings.some((p, i) => i !== 0 && p.duration === 1 && (p.rental_unit === 'day' || p.rental_unit === 'night'))) {
            props.pricings.push({
                _id: Date.now() + Math.random(),
                duration: 1,
                rental_unit: 'day', // Default to day
                price: '',
                _label: 'Harian'
            });
        }
    } else {
        const index = props.pricings.findIndex((p, i) => i !== 0 && p.duration === 1 && (p.rental_unit === 'day' || p.rental_unit === 'night'));
        if (index !== -1) {
            props.pricings.splice(index, 1);
        }
    }
});

watch(hasSpecialPrice, (val) => {
    if (val) {
        specialPriceOptions.value.forEach(opt => {
            if (!props.pricings.some((p, i) => i !== 0 && p.duration === opt.duration && p.rental_unit === opt.rental_unit)) {
                props.pricings.push({
                    _id: Date.now() + Math.random(),
                    duration: opt.duration,
                    rental_unit: opt.rental_unit,
                    price: '',
                    _label: opt.label // internal use
                });
            }
        });
    } else {
        for (let i = props.pricings.length - 1; i >= 1; i--) {
            const p = props.pricings[i];
            if (specialOptionKeys.value.includes(`${p.duration}-${p.rental_unit}`)) {
                props.pricings.splice(i, 1);
            }
        }
    }
});

watch(hasDp, (val) => {
    if (val) {
        if (!props.detail.dp_percentage) props.detail.dp_percentage = 10;
    } else {
        delete props.detail.dp_percentage;
    }
});

watch(hasDeposit, (val) => {
    if (val) {
        if (props.detail.deposit_amount === undefined) props.detail.deposit_amount = '';
    } else {
        delete props.detail.deposit_amount;
    }
});

watch(hasAdditionalFees, (val) => {
    if (val) {
        if (!props.detail.additional_fees) props.detail.additional_fees = [];
        if (props.detail.additional_fees.length === 0) {
            props.detail.additional_fees.push({ name: '', price: '' });
        }
    } else {
        props.detail.additional_fees = [];
    }
});

const dpOptions = [10, 20, 30, 40, 50];

const addFee = () => {
    if (!props.detail.additional_fees) props.detail.additional_fees = [];
    props.detail.additional_fees.push({ name: '', price: '' });
};

const removeFee = (index) => {
    props.detail.additional_fees.splice(index, 1);
    if (props.detail.additional_fees.length === 0) {
        hasAdditionalFees.value = false;
    }
};

</script>

<template>
    <div>
        <!-- Base Price -->
        <div class="mb-6">
            <h3 class="text-base font-bold text-slate-800 mb-1">Harga Sewa Per {{ rentalUnitTitle }}</h3>
            <p class="text-sm text-slate-500 mb-3">Wajib diisi sebagai harga sewa dasar</p>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base text-slate-500">Rp</span>
                <input 
                    :value="formatPrice(basePriceObj.price)" 
                    @input="updatePrice(basePriceObj, 'price', $event.target.value)" 
                    type="text" 
                    placeholder="2.000.000" 
                    class="w-full text-base pl-12 pr-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" 
                    required 
                />
            </div>
        </div>

        <!-- Conditional Options -->
        <div class="border border-slate-200 rounded-lg p-5">
            <h4 class="text-base font-bold text-slate-800 mb-4">Apakah Anda ingin:</h4>
            
            <div class="space-y-4">
                <!-- Checkbox: Harga Per Minggu — disembunyikan jika base unit adalah jam atau minggu -->
                <div v-if="assetTypeDetails?.rental_unit !== 'week' && assetTypeDetails?.rental_unit !== 'hour'">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex items-center justify-center w-6 h-6 shrink-0 mt-0.5">
                            <input type="checkbox" v-model="hasWeeklyPrice" class="peer appearance-none w-6 h-6 border border-slate-300 rounded cursor-pointer checked:bg-[#FFC000] checked:border-[#FFC000] transition-colors focus:outline-none focus:ring-2 focus:ring-[#FFC000]" />
                            <Check class="w-4 h-4 text-[#0A2540] absolute pointer-events-none opacity-0 peer-checked:opacity-100" />
                        </div>
                        <span class="text-base text-slate-700 group-hover:text-slate-900 transition-colors">Menentukan harga per minggu?</span>
                    </label>
                    <div v-if="hasWeeklyPrice && weeklyPricing" class="ml-9 pl-4 border-l-2 border-slate-100 mt-3">
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-base text-slate-500">Rp</span>
                            <input 
                                :value="formatPrice(weeklyPricing.price)" 
                                @input="updatePrice(weeklyPricing, 'price', $event.target.value)" 
                                type="text" 
                                placeholder="Harga Mingguan"
                                class="w-full text-base pl-12 pr-24 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" 
                            />
                            <span class="absolute right-4 text-base text-slate-600">/ Minggu</span>
                        </div>
                    </div>
                </div>

                <!-- Checkbox: Harga Per Hari -->
                <div v-if="assetTypeDetails?.rental_unit !== 'day' && assetTypeDetails?.rental_unit !== 'night'">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex items-center justify-center w-6 h-6 shrink-0 mt-0.5">
                            <input type="checkbox" v-model="hasDailyPrice" class="peer appearance-none w-6 h-6 border border-slate-300 rounded cursor-pointer checked:bg-[#FFC000] checked:border-[#FFC000] transition-colors focus:outline-none focus:ring-2 focus:ring-[#FFC000]" />
                            <Check class="w-4 h-4 text-[#0A2540] absolute pointer-events-none opacity-0 peer-checked:opacity-100" />
                        </div>
                        <span class="text-base text-slate-700 group-hover:text-slate-900 transition-colors">Menentukan harga per hari?</span>
                    </label>
                    <div v-if="hasDailyPrice && dailyPricing" class="ml-9 pl-4 border-l-2 border-slate-100 mt-3">
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-base text-slate-500">Rp</span>
                            <input 
                                :value="formatPrice(dailyPricing.price)" 
                                @input="updatePrice(dailyPricing, 'price', $event.target.value)" 
                                type="text" 
                                placeholder="Harga Harian"
                                class="w-full text-base pl-12 pr-24 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" 
                            />
                            <span class="absolute right-4 text-base text-slate-600">/ Hari</span>
                        </div>
                    </div>
                </div>

                <!-- Checkbox 1: Harga Spesial -->
                <div>
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex items-center justify-center w-6 h-6 shrink-0 mt-0.5">
                            <input type="checkbox" v-model="hasSpecialPrice" class="peer appearance-none w-6 h-6 border border-slate-300 rounded cursor-pointer checked:bg-[#FFC000] checked:border-[#FFC000] transition-colors focus:outline-none focus:ring-2 focus:ring-[#FFC000]" />
                            <Check class="w-4 h-4 text-[#0A2540] absolute pointer-events-none opacity-0 peer-checked:opacity-100" />
                        </div>
                        <span class="text-base text-slate-700 group-hover:text-slate-900 transition-colors">Menentukan harga spesial untuk durasi sewa lebih lama?</span>
                    </label>

                    <!-- Special Price Inputs -->
                    <div v-if="hasSpecialPrice" class="ml-9 pl-4 border-l-2 border-slate-100 space-y-4 mt-3">
                        <div v-for="(sp, idx) in specialPricings" :key="sp._id || idx">
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-base text-slate-500">Rp</span>
                                <input 
                                    :value="formatPrice(sp.price)" 
                                    @input="updatePrice(sp, 'price', $event.target.value)" 
                                    type="text" 
                                    class="w-full text-base pl-12 pr-24 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" 
                                />
                                <span class="absolute right-4 text-base text-slate-600">/ {{ sp._label || (sp.duration + ' ' + sp.rental_unit) }}</span>
                            </div>
                            <p class="text-sm text-slate-400 mt-1.5" v-if="basePriceObj.price">
                                Harga normal: Rp{{ formatPrice(basePriceObj.price * sp.duration) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Checkbox: DP (Uang Muka) -->
                <div>
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex items-center justify-center w-6 h-6 shrink-0 mt-0.5">
                            <input type="checkbox" v-model="hasDp" class="peer appearance-none w-6 h-6 border border-slate-300 rounded cursor-pointer checked:bg-[#FFC000] checked:border-[#FFC000] transition-colors focus:outline-none focus:ring-2 focus:ring-[#FFC000]" />
                            <Check class="w-4 h-4 text-[#0A2540] absolute pointer-events-none opacity-0 peer-checked:opacity-100" />
                        </div>
                        <span class="text-base text-slate-700 group-hover:text-slate-900 transition-colors">Menetapkan DP (Uang Muka)?</span>
                    </label>

                    <div v-if="hasDp" class="ml-9 mt-3">
                        <div class="border border-slate-200 rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="text-base font-bold text-slate-800 mb-1">DP (Uang Muka)</h4>
                            <p class="text-sm text-slate-600 mb-4">Uang muka/ DP akan diambil dari pembayaran sewa pertama penyewa.</p>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="pct in dpOptions" 
                                    :key="pct" 
                                    type="button" 
                                    @click="detail.dp_percentage = pct" 
                                    class="px-5 py-2 rounded-full border text-base transition-colors flex items-center gap-2"
                                    :class="detail.dp_percentage === pct ? 'border-[#FFC000] text-[#FFC000]' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                >
                                    <Check v-if="detail.dp_percentage === pct" class="w-4 h-4" />
                                    {{ pct }}%
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkbox: Deposit (Uang Jaminan) -->
                <div>
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex items-center justify-center w-6 h-6 shrink-0 mt-0.5">
                            <input type="checkbox" v-model="hasDeposit" class="peer appearance-none w-6 h-6 border border-slate-300 rounded cursor-pointer checked:bg-[#FFC000] checked:border-[#FFC000] transition-colors focus:outline-none focus:ring-2 focus:ring-[#FFC000]" />
                            <Check class="w-4 h-4 text-[#0A2540] absolute pointer-events-none opacity-0 peer-checked:opacity-100" />
                        </div>
                        <span class="text-base text-slate-700 group-hover:text-slate-900 transition-colors">Menentukan deposit (uang jaminan)?</span>
                    </label>

                    <div v-if="hasDeposit" class="ml-9 mt-3">
                        <div class="border border-slate-200 rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="text-base font-bold text-slate-800 mb-1">Deposit</h4>
                            <p class="text-sm text-slate-600 mb-4">Uang jaminan yang harus Anda kembalikan saat durasi sewa berakhir.</p>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base text-slate-500">Rp</span>
                                <input 
                                    :value="formatPrice(detail.deposit_amount)" 
                                    @input="updatePrice(detail, 'deposit_amount', $event.target.value)" 
                                    type="text" 
                                    placeholder="500.000" 
                                    class="w-full text-base pl-12 pr-4 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" 
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkbox 3: Biaya Tambahan — hanya untuk tipe sewa bulanan/mingguan/harian -->
                <div v-if="assetTypeDetails?.rental_unit !== 'hour'">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex items-center justify-center w-6 h-6 shrink-0 mt-0.5">
                            <input type="checkbox" v-model="hasAdditionalFees" class="peer appearance-none w-6 h-6 border border-slate-300 rounded cursor-pointer checked:bg-[#FFC000] checked:border-[#FFC000] transition-colors focus:outline-none focus:ring-2 focus:ring-[#FFC000]" />
                            <Check class="w-4 h-4 text-[#0A2540] absolute pointer-events-none opacity-0 peer-checked:opacity-100" />
                        </div>
                        <span class="text-base text-slate-700 group-hover:text-slate-900 transition-colors">Menetapkan biaya tambahan?</span>
                    </label>

                    <!-- Additional Fees Inputs -->
                    <div v-if="hasAdditionalFees" class="ml-9 mt-3">
                        <p class="text-sm text-slate-600 mb-4">Biaya yang bersifat harga tetap dan ditagih setiap bulan. Contoh seperti Elektronik tambahan, Parkir</p>
                        <div class="space-y-3">
                            <div v-for="(fee, fIdx) in detail.additional_fees" :key="fIdx" class="flex gap-3 items-center">
                                <input 
                                    v-model="fee.name" 
                                    type="text" 
                                    placeholder="Nama Biaya" 
                                    class="flex-1 text-base px-4 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" 
                                />
                                <div class="relative flex-1">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base text-slate-500">Rp</span>
                                    <input 
                                        :value="formatPrice(fee.price)" 
                                        @input="updatePrice(fee, 'price', $event.target.value)" 
                                        type="text" 
                                        class="w-full text-base pl-11 pr-4 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" 
                                    />
                                </div>
                                <button 
                                    type="button" 
                                    @click="removeFee(fIdx)" 
                                    class="w-11 h-11 flex items-center justify-center border border-slate-200 rounded-lg text-slate-500 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50 transition shrink-0"
                                >
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            @click="addFee" 
                            class="mt-4 px-4 py-2 text-sm font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors flex items-center gap-2"
                        >
                            <span>+</span> Tambah Biaya
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
