<script setup>
import { Trash2, ChevronUp, ChevronDown } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';
import TagInput from '@/Components/ui/TagInput.vue';

const props = defineProps({
    form: Object,
    assetTypeDetails: Object,
    unitLabel: {
        type: String,
        default: 'Unit'
    }, // { unit_facilities, unit_detail_fields }
});

const emit = defineEmits([
    'tambahUnit',
    'hapusUnit',
    'toggleUnitFasilitas',
]);

const unitDetailFields = computed(() => props.assetTypeDetails?.unit_detail_fields ?? []);
const unitFacilitiesFromDB = computed(() => props.assetTypeDetails?.unit_facilities ?? []);

// Flat array for TagInput
const allUnitFacilitiesOptions = computed(() => {
    return unitFacilitiesFromDB.value.map(fac => ({
        code: fac.id,
        name: `${fac.name} (${fac.category?.name || 'Fasilitas Lainnya'})`
    }));
});

const toggleExpand = (index) => {
    props.form.units.forEach((u, i) => {
        if (i === index) {
            u.is_expanded = !u.is_expanded;
        } else {
            u.is_expanded = false;
        }
    });
};

onMounted(() => {
    if (props.form.units && props.form.units.length > 0) {
        const hasExpanded = props.form.units.some(u => u.is_expanded);
        if (!hasExpanded) {
            props.form.units[0].is_expanded = true;
        }
    }
});
</script>

<template>
<div class="space-y-6">
    <!-- STEP 4: MANAJEMEN UNIT -->
    <div class="flex items-center justify-end border-b border-slate-200 pb-4">
        <button
            type="button"
            @click="emit('tambahUnit')"
            class="text-sm font-semibold text-white bg-[#0A2540] hover:bg-[#123e6b] px-4 py-2 rounded-md transition cursor-pointer shrink-0 shadow-sm"
        >
            + Tambah Tipe {{ unitLabel }} Baru
        </button>
    </div>

    <p class="text-sm text-slate-500 mb-4">
        Aset Anda memiliki beberapa tipe ruangan/unit (misal: Kamar Standar, Kamar VIP). Tambahkan dan atur spesifikasinya di sini. (Harga sewa akan diatur pada langkah selanjutnya).
    </p>

    <div class="space-y-6">
        <div
            v-for="(unit, unitIndex) in form.units"
            :key="unit._id"
            class="border border-slate-300 rounded-lg relative bg-white shadow-sm"
        >
            <!-- Header (Collapsible) -->
            <div @click="toggleExpand(unitIndex)" class="bg-white p-5 flex items-center justify-between cursor-pointer border-b border-slate-200/60 hover:bg-slate-50 transition-colors rounded-t-lg" :class="{ 'rounded-b-lg border-b-0': unit.is_expanded === false }">
                <div class="flex items-center gap-4">
                    <div class="text-slate-500 font-bold text-lg">
                        {{ String(unitIndex + 1).padStart(2, '0') }}
                    </div>
                    <div v-if="assetTypeDetails?.name === 'Kos'">
                        <h4 class="text-base font-bold text-slate-800">{{ unit.name ? 'Tipe ' + unit.name : `Tipe ${unitLabel} ${unitIndex + 1}` }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ unit.quantity || 0 }} {{ unitLabel.toLowerCase() }} &middot; {{ unit.detail.ukuran_kamar || '?' }} m &middot; {{ (unit.quantity || 0) - (unit.empty_rooms || 0) }} terisi</p>
                    </div>
                    <div v-else>
                        <h4 class="text-base font-bold text-slate-800">{{ unit.name ? 'Tipe ' + unit.name : `Tipe ${unitLabel} ${unitIndex + 1}` }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ unit.quantity || 0 }} {{ unitLabel.toLowerCase() }} &middot; {{ (unit.quantity || 0) - (unit.empty_rooms || 0) }} terisi</p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click.stop="emit('hapusUnit', unitIndex)" class="w-8 h-8 rounded-md hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition flex items-center justify-center cursor-pointer" title="Hapus Tipe">
                        <Trash2 class="w-4 h-4" />
                    </button>
                    <button type="button" class="w-8 h-8 rounded-md hover:bg-slate-200 text-slate-400 transition flex items-center justify-center cursor-pointer">
                        <ChevronUp v-if="unit.is_expanded !== false" class="w-5 h-5" />
                        <ChevronDown v-else class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div v-show="unit.is_expanded !== false" class="p-6">

            <div v-if="assetTypeDetails?.name === 'Kos'">
                <!-- KOS: NAMA TIPE KAMAR -->
                <div class="mb-5">
                    <label class="block text-base font-bold text-slate-800 mb-1.5">Nama Tipe Kamar</label>
                    <p class="text-sm text-slate-500 mb-2">Gunakan nama yang membedakan setiap tipe kamar.</p>
                    <div class="flex items-center w-full bg-white border border-slate-300 rounded-md overflow-hidden focus-within:ring-2 focus-within:ring-[#FFC000] transition">
                        <span class="px-4 py-2.5 text-slate-600 font-medium bg-slate-50 border-r border-slate-300">Tipe</span>
                        <input
                            v-model="unit.name"
                            type="text"
                            placeholder="A"
                            class="w-full text-sm px-4 py-2.5 focus:outline-none"
                            required
                        />
                    </div>
                </div>

                <!-- KOS: TOTAL KAMAR -->
                <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-slate-200 rounded-xl gap-4">
                    <div>
                        <label class="block text-base font-bold text-slate-800 mb-1">Total Kamar</label>
                        <p class="text-sm text-slate-500">Maksimal 100 kamar</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="unit.quantity > 1 ? unit.quantity-- : null" class="w-10 h-10 flex items-center justify-center border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            <span class="text-lg font-bold">-</span>
                        </button>
                        <input
                            v-model.number="unit.quantity"
                            type="number"
                            min="1"
                            max="100"
                            class="w-20 text-center text-base font-bold border border-slate-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-[#FFC000]"
                        />
                        <button type="button" @click="unit.quantity < 100 ? unit.quantity++ : null" class="w-10 h-10 flex items-center justify-center border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            <span class="text-lg font-bold">+</span>
                        </button>
                    </div>
                </div>

                <!-- KOS: KAMAR KOSONG & TERISI -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <!-- Kamar Kosong -->
                    <div class="p-4 border-2 border-emerald-200 bg-emerald-50/30 rounded-xl flex flex-col justify-between">
                        <label class="block text-base font-bold text-slate-800 mb-3">Kamar Kosong</label>
                        <div class="flex items-center gap-3 mt-auto">
                            <button type="button" @click="unit.empty_rooms > 0 ? unit.empty_rooms-- : null" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                                <span class="text-lg font-bold">-</span>
                            </button>
                            <input
                                v-model.number="unit.empty_rooms"
                                type="number"
                                min="0"
                                :max="unit.quantity"
                                @input="e => {
                                    let val = parseInt(e.target.value);
                                    if(val > unit.quantity) unit.empty_rooms = unit.quantity;
                                    if(val < 0) unit.empty_rooms = 0;
                                }"
                                class="flex-1 text-center text-base font-bold border border-slate-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                            <button type="button" @click="unit.empty_rooms < unit.quantity ? unit.empty_rooms++ : null" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                                <span class="text-lg font-bold">+</span>
                            </button>
                        </div>
                    </div>
                    <!-- Sudah Terisi -->
                    <div class="p-4 bg-slate-100 border border-slate-200 rounded-xl flex flex-col justify-between">
                        <label class="block text-base font-bold text-slate-800 mb-3">Sudah Terisi :</label>
                        <div class="mt-auto flex items-center justify-center">
                            <span class="text-3xl font-bold text-[#0A2540]">{{ (unit.quantity || 0) - (unit.empty_rooms || 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- KOS: KAPASITAS PENGHUNI -->
                <div class="mb-6">
                    <label class="block text-base font-bold text-slate-800 mb-1.5">Kapasitas Penghuni</label>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="unit.detail.kapasitas_penghuni = (unit.detail.kapasitas_penghuni || 1) > 1 ? (unit.detail.kapasitas_penghuni || 1) - 1 : 1" class="w-10 h-10 flex items-center justify-center border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            <span class="text-lg font-bold">-</span>
                        </button>
                        <input
                            v-model.number="unit.detail.kapasitas_penghuni"
                            type="number"
                            min="1"
                            placeholder="1"
                            @input="e => {
                                let val = parseInt(e.target.value);
                                if(val < 1 || isNaN(val)) { e.target.value = 1; unit.detail.kapasitas_penghuni = 1; }
                            }"
                            class="w-20 text-center text-base font-bold border border-slate-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-[#FFC000]"
                        />
                        <button type="button" @click="unit.detail.kapasitas_penghuni = (unit.detail.kapasitas_penghuni || 1) + 1" class="w-10 h-10 flex items-center justify-center border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            <span class="text-lg font-bold">+</span>
                        </button>
                        <span class="text-sm font-semibold text-slate-500">Orang / Kamar</span>
                    </div>
                </div>

                <!-- KOS: UKURAN KAMAR -->
                <div class="mb-6">
                    <label class="block text-base font-bold text-slate-800 mb-3">Ukuran Kamar</label>
                    <!-- Presets -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button type="button" @click="unit.detail.ukuran_panjang = 3; unit.detail.ukuran_lebar = 3; unit.detail.ukuran_kamar = '3 x 3'" class="px-4 py-1.5 text-sm font-medium border border-slate-200 rounded-full hover:bg-slate-50 text-slate-700 transition cursor-pointer" :class="(unit.detail.ukuran_panjang == 3 && unit.detail.ukuran_lebar == 3) ? 'bg-[#0A2540] text-white border-[#0A2540]' : ''">3 x 3 meter</button>
                        <button type="button" @click="unit.detail.ukuran_panjang = 3; unit.detail.ukuran_lebar = 4; unit.detail.ukuran_kamar = '3 x 4'" class="px-4 py-1.5 text-sm font-medium border border-slate-200 rounded-full hover:bg-slate-50 text-slate-700 transition cursor-pointer" :class="(unit.detail.ukuran_panjang == 3 && unit.detail.ukuran_lebar == 4) ? 'bg-[#0A2540] text-white border-[#0A2540]' : ''">3 x 4 meter</button>
                        <button type="button" @click="unit.detail.ukuran_panjang = 4; unit.detail.ukuran_lebar = 4; unit.detail.ukuran_kamar = '4 x 4'" class="px-4 py-1.5 text-sm font-medium border border-slate-200 rounded-full hover:bg-slate-50 text-slate-700 transition cursor-pointer" :class="(unit.detail.ukuran_panjang == 4 && unit.detail.ukuran_lebar == 4) ? 'bg-[#0A2540] text-white border-[#0A2540]' : ''">4 x 4 meter</button>
                    </div>
                    <!-- Manual Input -->
                    <div class="flex items-center gap-3">
                        <input
                            v-model.number="unit.detail.ukuran_panjang"
                            type="number"
                            min="1"
                            placeholder="3"
                            @input="e => {
                                let val = parseFloat(e.target.value);
                                if(val < 1) { e.target.value = 1; unit.detail.ukuran_panjang = 1; }
                                unit.detail.ukuran_kamar = `${unit.detail.ukuran_panjang} x ${unit.detail.ukuran_lebar || ''}`;
                            }"
                            class="flex-1 max-w-[120px] text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] transition"
                        />
                        <span class="text-slate-400 font-bold">x</span>
                        <input
                            v-model.number="unit.detail.ukuran_lebar"
                            type="number"
                            min="1"
                            placeholder="3"
                            @input="e => {
                                let val = parseFloat(e.target.value);
                                if(val < 1) { e.target.value = 1; unit.detail.ukuran_lebar = 1; }
                                unit.detail.ukuran_kamar = `${unit.detail.ukuran_panjang || ''} x ${unit.detail.ukuran_lebar}`;
                            }"
                            class="flex-1 max-w-[120px] text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] transition"
                        />
                        <span class="text-sm font-medium text-slate-500">meter</span>
                    </div>
                </div>
            </div>

            <!-- NON-KOS: GENERIK -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-base font-bold text-slate-800 mb-1.5">Nama Tipe {{ unitLabel }} <span class="text-rose-500">*</span></label>
                    <input
                        v-model="unit.name"
                        type="text"
                        placeholder="cth: Kamar Standard"
                        class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition"
                        required
                    />
                </div>
                <div>
                    <label class="block text-base font-bold text-slate-800 mb-1.5">Jumlah Unit <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input
                            v-model="unit.quantity"
                            type="number"
                            min="1"
                            placeholder="1"
                            class="w-full text-sm pl-4 pr-20 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition"
                            required
                        />
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-semibold">Unit</span>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Unit (Untuk semua tipe aset yang allow units, baik kos maupun non-kos) -->
            <div class="mb-6">
                <label class="block text-base font-bold text-slate-800 mb-1.5">Deskripsi Unit <span class="text-rose-500">*</span></label>
                <textarea
                    v-model="unit.description"
                    rows="3"
                    minlength="100"
                    required
                    placeholder="Deskripsikan keunggulan spesifik unit ini (misal: Pemandangan kota, dekat tangga)..."
                    class="w-full text-sm px-4 py-2.5 rounded-md border focus:outline-none focus:ring-2 transition resize-none"
                    :class="(unit.description || '').length > 0 && (unit.description || '').length < 100 ? 'border-rose-300 focus:ring-rose-500 focus:border-transparent' : 'border-slate-300 focus:ring-[#FFC000] focus:border-transparent'"
                ></textarea>
                <div class="mt-1.5 flex justify-between items-center text-[11px]">
                    <span :class="(unit.description || '').length > 0 && (unit.description || '').length < 100 ? 'text-rose-500' : 'text-slate-500'">
                        {{ (unit.description || '').length < 100 ? `Minimal 100 karakter (${(unit.description || '').length}/100)` : 'Panjang deskripsi sudah sesuai' }}
                    </span>
                    <span class="text-slate-400 font-medium">{{ (unit.description || '').length }} karakter</span>
                </div>
            </div>

            <!-- Detail Fields Unit (dinamis dari DB, HANYA UNTUK NON-KOS) -->
            <div v-if="unitDetailFields.length > 0 && assetTypeDetails?.name !== 'Kos'" class="mb-6 border-t border-slate-200 pt-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div v-for="field in unitDetailFields" :key="field.key">
                        <label class="block text-sm font-semibold text-slate-600 mb-1.5">
                            {{ field.label }}
                            <span v-if="field.required" class="text-rose-500">*</span>
                        </label>

                        <CustomSelect
                            v-if="field.type === 'select'"
                            v-model="unit.detail[field.key]"
                            :options="field.options.map(o => ({ label: o, value: o }))"
                            placeholder="Pilih..."
                            class="w-full"
                        />

                        <input
                            v-else
                            :type="field.type"
                            :step="field.type === 'number' ? 'any' : undefined"
                            :min="field.key === 'room_size' ? 1 : undefined"
                            v-model="unit.detail[field.key]"
                            :placeholder="field.label"
                            :required="field.required"
                            @input="e => {
                                if (field.key === 'room_size' && e.target.value !== '') {
                                    let val = parseFloat(e.target.value);
                                    if (val < 1) {
                                        e.target.value = 1;
                                        unit.detail[field.key] = 1;
                                    }
                                }
                            }"
                            class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition"
                        />
                    </div>
                </div>
            </div>

            <!-- Fasilitas Unit (dari DB, scope = unit) -->
            <div v-if="allUnitFacilitiesOptions.length > 0" class="border-t border-slate-200 pt-5">

                <p class="text-xs text-slate-500 mb-4">Ketik atau pilih fasilitas yang HANYA terdapat di dalam Tipe {{ unitLabel }} ini.</p>

                <TagInput
                    v-model="unit.facility_ids"
                    :options="allUnitFacilitiesOptions"
                    placeholder="Cari fasilitas unit..."
                />
            </div>
            </div>
        </div>
    </div>
</div>
</template>
