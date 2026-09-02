<script setup>
import { computed, ref } from 'vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';
import TagInput from '@/Components/ui/TagInput.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import { Trash2, ChevronDown, ChevronUp, MoreVertical, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    form: Object,
    assetTypeDetails: Object,
    unitLabel: {
        type: String,
        default: 'Unit'
    },
});

const emit = defineEmits([
    'toggleFasilitas',
    'tambahUnit',
    'hapusUnit',
]);

const detailFields = computed(() => {
    let fields = props.assetTypeDetails?.detail_fields ?? [];
    if (props.assetTypeDetails?.name === 'Kos') {
        fields = fields.map(f => {
            if (f.key === 'aturan_jam_malam') {
                return {
                    ...f,
                    key: 'kriteria_penyewa',
                    label: 'Kriteria Calon Penyewa',
                    options: ['Khusus Karyawan', 'Khusus Mahasiswa', 'Bisa untuk Semua'],
                    required: false
                };
            }
            return f;
        });
    }
    return fields;
});
const unitDetailFields = computed(() => props.assetTypeDetails?.unit_detail_fields ?? []);

const updateQuantity = (unit, valStr, event = null) => {
    let val = parseInt(valStr);
    if (isNaN(val) || val < 1) val = 1;

    let terisi = (unit.quantity || 1) - (unit.empty_rooms ?? unit.quantity ?? 1);
    unit.quantity = val;

    if (terisi > unit.quantity) terisi = unit.quantity;
    unit.empty_rooms = unit.quantity - terisi;

    if (event) event.target.value = unit.quantity;
};

const updateEmptyRooms = (unit, valStr, event = null) => {
    let val = parseInt(valStr);
    if (isNaN(val) || val < 0) val = 0;
    if (val > unit.quantity) val = unit.quantity;

    unit.empty_rooms = val;

    if (event) event.target.value = unit.empty_rooms;
};

const toggleExpand = (index) => {
    props.form.units.forEach((u, i) => {
        if (i === index) {
            u.is_expanded = !u.is_expanded;
        } else {
            u.is_expanded = false;
        }
    });
};

</script>

<template>
<div class="space-y-6">
    <!-- ============================================ -->
    <!-- DETAIL SPESIFIKASI UMUM (Dinamis dari DB) -->
    <!-- ============================================ -->
    <template v-if="detailFields.length > 0">
        <div class="mb-6 space-y-6">
            <template v-for="field in detailFields" :key="field.key">
                <!-- Custom UI: Tipe Penyewa -->
                <div v-if="field.key === 'tipe_penyewa'">
                    <label class="block text-base font-bold text-slate-800 mb-2">{{ field.label }} <span v-if="field.required" class="text-rose-500">*</span></label>
                    <div class="flex items-center w-full bg-white border border-slate-200 rounded-lg overflow-hidden">
                        <label v-for="(opt, idx) in field.options" :key="opt" class="flex-1 text-center py-3 cursor-pointer transition-colors" :class="[form.detail[field.key] === opt ? 'bg-[#FFF8E6] text-[#0A2540] font-bold' : 'text-slate-500 hover:bg-slate-50', idx < field.options.length - 1 ? 'border-r border-slate-200' : '']">
                            <input type="radio" :name="field.key" :value="opt" v-model="form.detail[field.key]" class="hidden" />
                            <span v-if="opt === 'Putra'">♂ </span><span v-else-if="opt === 'Putri'">♀ </span><span v-else-if="opt === 'Campur'">⚥ </span>{{ opt }}
                        </label>
                    </div>
                </div>

                <!-- Custom UI: Kriteria Penyewa -->
                <div v-else-if="field.key === 'kriteria_penyewa'">
                    <label class="block text-base font-bold text-slate-800 mb-2">{{ field.label }} <span v-if="!field.required" class="text-sm font-normal text-slate-400">(opsional)</span></label>
                    <div class="flex items-center w-full bg-white border border-slate-200 rounded-lg overflow-hidden flex-wrap sm:flex-nowrap">
                        <label v-for="(opt, idx) in field.options" :key="opt" class="flex-1 text-center py-3 cursor-pointer transition-colors w-full sm:w-auto" :class="[form.detail[field.key] === opt ? 'bg-[#FFF8E6] text-[#0A2540] font-bold' : 'text-slate-500 hover:bg-slate-50', idx < field.options.length - 1 ? 'sm:border-r border-b sm:border-b-0 border-slate-200' : '']">
                            <input type="radio" :name="field.key" :value="opt" v-model="form.detail[field.key]" class="hidden" />
                            {{ opt }}
                        </label>
                    </div>
                </div>

                <!-- Generic UI -->
                <div v-else>
                    <label class="block text-base font-bold text-slate-800 mb-1.5">
                        {{ field.label }}
                        <span v-if="field.required" class="text-rose-500">*</span>
                    </label>
                    <input v-if="field.key === 'stars'" type="number" v-model.number="form.detail[field.key]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" :required="field.required" min="1" max="10" @input="e => { let val = parseInt(e.target.value); if (val < 1) e.target.value = 1; if (val > 10) e.target.value = 10; form.detail[field.key] = e.target.value; }" />
                    <select v-else-if="field.type === 'select'" v-model="form.detail[field.key]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" :required="field.required">
                        <option value="" disabled>Pilih...</option>
                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                    <div v-else-if="field.type === 'checkbox'" class="flex items-center gap-2 mt-1">
                        <input type="checkbox" :id="`detail_${field.key}`" v-model="form.detail[field.key]" class="w-4 h-4 rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]" />
                        <label :for="`detail_${field.key}`" class="text-sm text-slate-600">Ya</label>
                    </div>
                    <div v-else-if="field.type === 'radio'" class="flex flex-wrap items-center gap-4 mt-2">
                        <label v-for="opt in field.options" :key="opt" class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                            <input type="radio" :name="`detail_${field.key}`" :value="opt" v-model="form.detail[field.key]" class="border-slate-300 text-[#0A2540]" />
                            {{ opt }}
                        </label>
                    </div>
                    <input v-else-if="field.type === 'time'" type="time" v-model="form.detail[field.key]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" :required="field.required" />
                    <input v-else :type="field.type" v-model="form.detail[field.key]" :placeholder="field.label" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" :required="field.required" />
                </div>
            </template>
        </div>
    </template>

    <!-- ============================================ -->
    <!-- TIPE KAMAR / UNIT -->
    <!-- ============================================ -->
    <template v-if="assetTypeDetails?.allow_units">
        <div class="mt-8 border-t border-slate-200 pt-8">
            <div class="space-y-6">
                <div v-for="(unit, unitIndex) in form.units" :key="unit._id" class="border border-slate-300 rounded-lg relative bg-white shadow-sm">

                    <!-- Header Kamar (Collapsible) -->
                    <div @click="toggleExpand(unitIndex)" class="bg-white p-5 flex items-center justify-between cursor-pointer border-b border-slate-200/60 hover:bg-slate-50 transition-colors rounded-t-lg" :class="{ 'rounded-b-lg border-b-0': unit.is_expanded === false }">
                        <div class="flex items-center gap-4">
                            <div class="text-slate-500 font-bold text-lg">
                                {{ String(unitIndex + 1).padStart(2, '0') }}
                            </div>
                            <div>
                                <p class="text-lg font-bold text-[#0A2540]">{{ unit.name ? 'Tipe ' + unit.name : 'Tipe ' + unitLabel }}</p>
                                <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ unit.quantity || 0 }} {{ unitLabel.toLowerCase() }} &middot; {{ (unit.quantity || 0) - (unit.empty_rooms || 0) }} terisi</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button v-if="form.units.length > 1" type="button" @click.stop="emit('hapusUnit', unitIndex)" class="w-8 h-8 rounded-md hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition flex items-center justify-center cursor-pointer" title="Hapus Tipe">
                                <Trash2 class="w-4 h-4" />
                            </button>
                            <button type="button" class="w-8 h-8 rounded-md hover:bg-slate-200 text-slate-400 transition flex items-center justify-center cursor-pointer">
                                <ChevronUp v-if="unit.is_expanded !== false" class="w-5 h-5" />
                                <ChevronDown v-else class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Body Kamar -->
                    <div v-show="unit.is_expanded !== false" class="p-6">
                        <!-- NAMA TIPE KAMAR -->
                        <div class="mb-8 pb-8 border-b border-slate-200/70">
                            <label class="block text-base font-bold text-slate-800 mb-1.5">Nama Tipe {{ unitLabel }} <span class="text-rose-500">*</span></label>
                            <p class="text-sm text-slate-500 mb-3">Gunakan nama yang membedakan setiap tipe.</p>
                            <div class="flex items-center w-full bg-white border border-slate-300 rounded-md overflow-hidden focus-within:ring-2 focus-within:ring-[#FFC000] transition">
                                <span class="px-4 py-2.5 text-slate-600 font-medium bg-slate-50 border-r border-slate-300">Tipe</span>
                                <input v-model="unit.name" type="text" placeholder="Contoh : Standar, Eksklusif" class="w-full text-sm px-4 py-2.5 focus:outline-none" required />
                            </div>
                        </div>

                        <!-- TOTAL KAMAR -->
                        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-slate-200 rounded-lg gap-4">
                            <div>
                                <label class="block text-base font-bold text-slate-800 mb-1">Total {{ unitLabel }} <span class="text-rose-500">*</span></label>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="updateQuantity(unit, unit.quantity - 1)" class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-md text-slate-600 hover:bg-slate-50 transition cursor-pointer"><span class="text-lg font-bold">-</span></button>
                                <input :value="unit.quantity" type="number" min="1" @input="e => updateQuantity(unit, e.target.value, e)" class="w-24 text-center text-base font-bold border border-slate-200 rounded-md py-1.5 focus:outline-none focus:ring-2 focus:ring-[#FFC000]" />
                                <button type="button" @click="updateQuantity(unit, unit.quantity + 1)" class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-md text-slate-600 hover:bg-slate-50 transition cursor-pointer"><span class="text-lg font-bold">+</span></button>
                            </div>
                        </div>

                        <!-- KAMAR KOSONG & TERISI -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8 pb-8 border-b border-slate-200/70">
                            <div class="p-4 border border-amber-400 bg-amber-50/50 rounded-lg flex flex-col justify-between">
                                <label class="block text-base font-bold text-slate-800 mb-3">{{ unitLabel }} Kosong</label>
                                <div class="flex items-center gap-2 mt-auto">
                                    <button type="button" @click="updateEmptyRooms(unit, (unit.empty_rooms ?? unit.quantity) - 1)" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-md text-slate-600 hover:bg-slate-50 transition cursor-pointer"><span class="text-lg font-bold">-</span></button>
                                    <input :value="unit.empty_rooms ?? unit.quantity" type="number" min="0" :max="unit.quantity" @input="e => updateEmptyRooms(unit, e.target.value, e)" class="flex-1 text-center text-base font-bold border border-slate-200 rounded-md py-1.5 focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white" />
                                    <button type="button" @click="updateEmptyRooms(unit, (unit.empty_rooms ?? unit.quantity) + 1)" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-md text-slate-600 hover:bg-slate-50 transition cursor-pointer"><span class="text-lg font-bold">+</span></button>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg flex flex-col justify-between">
                                <label class="block text-base font-bold text-slate-800 mb-3">Sudah Terisi :</label>
                                <div class="mt-auto flex items-center justify-center">
                                    <span class="text-3xl font-bold text-[#0A2540]">{{ (unit.quantity || 0) - (unit.empty_rooms ?? unit.quantity ?? 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- DYNAMIC UNIT DETAILS FROM DB -->
                        <template v-if="unitDetailFields.length > 0">
                            <template v-for="field in unitDetailFields" :key="field.key">

                                <!-- Custom UI: Kapasitas Penghuni -->
                                <div v-if="field.key === 'kapasitas_penghuni'" class="mb-8 pb-8 border-b border-slate-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <label class="block text-base font-bold text-slate-800 mb-1">{{ field.label }} <span v-if="field.required" class="text-rose-500">*</span></label>
                                        <p class="text-sm text-slate-500">Maksimum per {{ unitLabel.toLowerCase() }}</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <button type="button" @click="unit.detail[field.key] = (unit.detail[field.key] || 1) > 1 ? (unit.detail[field.key] || 1) - 1 : 1" class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-md text-slate-600 hover:bg-slate-50 transition cursor-pointer"><span class="text-lg font-bold">-</span></button>
                                        <input v-model.number="unit.detail[field.key]" type="number" min="1" placeholder="1" @input="e => { let val = parseInt(e.target.value); if(val < 1 || isNaN(val)) { e.target.value = 1; unit.detail[field.key] = 1; } }" class="w-12 text-center text-lg font-bold bg-transparent focus:outline-none focus:ring-0 p-0 border-none" />
                                        <button type="button" @click="unit.detail[field.key] = (unit.detail[field.key] || 1) + 1" class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-md text-slate-600 hover:bg-slate-50 transition cursor-pointer"><span class="text-lg font-bold">+</span></button>
                                    </div>
                                </div>

                                <!-- Custom UI: Ukuran Kamar -->
                                <div v-else-if="field.key === 'ukuran_kamar'" class="mb-8 pb-8 border-b border-slate-200/70">
                                    <label class="block text-base font-bold text-slate-800 mb-3">{{ field.label }} <span v-if="field.required" class="text-rose-500">*</span></label>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <button type="button" @click="unit.detail.ukuran_panjang = 3; unit.detail.ukuran_lebar = 3; unit.detail[field.key] = '3 x 3'" class="px-4 py-2 text-sm font-medium border rounded-md transition cursor-pointer" :class="(unit.detail.ukuran_panjang == 3 && unit.detail.ukuran_lebar == 3) ? 'border-[#FFC000] bg-amber-50 text-[#0A2540] font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">3 x 3 meter</button>
                                        <button type="button" @click="unit.detail.ukuran_panjang = 3; unit.detail.ukuran_lebar = 4; unit.detail[field.key] = '3 x 4'" class="px-4 py-2 text-sm font-medium border rounded-md transition cursor-pointer" :class="(unit.detail.ukuran_panjang == 3 && unit.detail.ukuran_lebar == 4) ? 'border-[#FFC000] bg-amber-50 text-[#0A2540] font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">3 x 4 meter</button>
                                        <button type="button" @click="unit.detail.ukuran_panjang = 4; unit.detail.ukuran_lebar = 4; unit.detail[field.key] = '4 x 4'" class="px-4 py-2 text-sm font-medium border rounded-md transition cursor-pointer" :class="(unit.detail.ukuran_panjang == 4 && unit.detail.ukuran_lebar == 4) ? 'border-[#FFC000] bg-amber-50 text-[#0A2540] font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">4 x 4 meter</button>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input v-model.number="unit.detail.ukuran_panjang" type="number" min="1" placeholder="3" @input="e => { let val = parseFloat(e.target.value); if(val < 1) { e.target.value = 1; unit.detail.ukuran_panjang = 1; } unit.detail[field.key] = `${unit.detail.ukuran_panjang} x ${unit.detail.ukuran_lebar || ''}`; }" class="flex-1 min-w-0 text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] transition" />
                                        <span class="text-slate-400 font-bold shrink-0">x</span>
                                        <input v-model.number="unit.detail.ukuran_lebar" type="number" min="1" placeholder="3" @input="e => { let val = parseFloat(e.target.value); if(val < 1) { e.target.value = 1; unit.detail.ukuran_lebar = 1; } unit.detail[field.key] = `${unit.detail.ukuran_panjang || ''} x ${unit.detail.ukuran_lebar}`; }" class="flex-1 min-w-0 text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] transition" />
                                        <span class="text-sm font-medium text-slate-500 shrink-0">meter</span>
                                    </div>
                                </div>

                                <!-- Generic UI -->
                                <div v-else class="mb-8 pb-8 border-b border-slate-200/70">
                                    <label class="block text-base font-bold text-slate-800 mb-1.5">
                                        {{ field.label }}
                                        <span v-if="field.required" class="text-rose-500">*</span>
                                    </label>
                                    <select v-if="field.type === 'select'" v-model="unit.detail[field.key]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" :required="field.required">
                                        <option value="" disabled>Pilih...</option>
                                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>
                                    <input v-else :type="field.type" :step="field.type === 'number' ? 'any' : undefined" v-model="unit.detail[field.key]" :placeholder="field.label" :required="field.required" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition" />
                                </div>

                            </template>
                        </template>

                        <!-- Deskripsi Kamar -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-base font-bold text-slate-800">Deskripsi Kamar <span class="text-sm font-normal text-slate-400">(opsional)</span></label>
                                <button type="button" class="flex items-center gap-1.5 text-sm font-bold text-[#FFC000] hover:text-amber-500 transition cursor-pointer">
                                    <Sparkles class="w-4 h-4" /> Buat Otomatis
                                </button>
                            </div>
                            <textarea v-model="unit.description" rows="5" placeholder="Deskripsikan keunggulan spesifik kamar ini..." class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Tombol Tambah Tipe Kamar -->
                <button type="button" @click="emit('tambahUnit')" class="w-full py-3.5 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] rounded-lg flex items-center justify-center gap-2 font-bold transition-colors shadow-sm">
                    + Tambah Tipe Kamar
                </button>
            </div>
        </div>
    </template>
</div>
</template>
