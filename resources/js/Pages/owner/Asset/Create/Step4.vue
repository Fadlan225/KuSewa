<script setup>
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import CustomSelect from '@/Components/UI/CustomSelect.vue';
import SearchableSelect from '@/Components/UI/SearchableSelect.vue';
import TagInput from '@/Components/UI/TagInput.vue';

const props = defineProps({
    form: Object,
    assetTypeDetails: Object, // { unit_facilities, unit_detail_fields }
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
</script>

<template>
<div class="space-y-6">
    <!-- STEP 4: MANAJEMEN UNIT -->
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <h2 class="text-lg font-bold text-slate-800">
            Manajemen Tipe Unit
        </h2>
        <button
            type="button"
            @click="emit('tambahUnit')"
            class="text-sm font-semibold text-white bg-[#0A2540] hover:bg-[#123e6b] px-4 py-2 rounded-md transition cursor-pointer shrink-0 shadow-sm"
        >
            + Tambah Tipe Unit Baru
        </button>
    </div>

    <p class="text-sm text-slate-500 mb-4">
        Aset Anda memiliki beberapa tipe ruangan/unit (misal: Kamar Standar, Kamar VIP). Tambahkan dan atur spesifikasinya di sini. (Harga sewa akan diatur pada langkah selanjutnya).
    </p>

    <div class="space-y-6">
        <div
            v-for="(unit, unitIndex) in form.units"
            :key="unit._id"
            class="border border-slate-300 rounded-lg p-6 relative bg-white shadow-sm"
        >
            <!-- Tombol Hapus Unit -->
            <button
                v-if="form.units.length > 1"
                type="button"
                @click="emit('hapusUnit', unitIndex)"
                class="absolute top-4 right-4 w-8 h-8 rounded-md bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-sm cursor-pointer shadow-sm"
                title="Hapus Unit"
            >
                <Trash2 class="" />
            </button>

            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-md bg-[#FFC000] text-[#0A2540] font-bold flex items-center justify-center text-sm">
                    {{ unitIndex + 1 }}
                </div>
                <p class="text-sm font-semibold text-slate-700">Tipe Unit ke-{{ unitIndex + 1 }}</p>
            </div>

            <!-- Nama Unit & Jumlah -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Tipe Unit <span class="text-rose-500">*</span></label>
                    <input
                        v-model="unit.name"
                        type="text"
                        placeholder="cth: Kamar Standard"
                        class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
                        required
                    />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Unit <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input
                            v-model="unit.quantity"
                            type="number"
                            min="1"
                            placeholder="1"
                            class="w-full text-sm pl-4 pr-20 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
                            required
                        />
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-semibold">Unit</span>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Unit -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi Unit <span class="text-rose-500">*</span></label>
                <textarea
                    v-model="unit.description"
                    rows="3"
                    minlength="100"
                    required
                    placeholder="Deskripsikan keunggulan spesifik unit ini (misal: Pemandangan kota, dekat tangga)..."
                    class="w-full text-sm px-4 py-2.5 rounded-md border focus:outline-none focus:ring-2 transition resize-none"
                    :class="(unit.description || '').length > 0 && (unit.description || '').length < 100 ? 'border-rose-300 focus:ring-rose-500 focus:border-transparent' : 'border-slate-300 focus:ring-[#0A2540] focus:border-transparent'"
                ></textarea>
                <div class="mt-1.5 flex justify-between items-center text-[11px]">
                    <span :class="(unit.description || '').length > 0 && (unit.description || '').length < 100 ? 'text-rose-500' : 'text-slate-500'">
                        {{ (unit.description || '').length < 100 ? `Minimal 100 karakter (${(unit.description || '').length}/100)` : 'Panjang deskripsi sudah sesuai' }}
                    </span>
                    <span class="text-slate-400 font-medium">{{ (unit.description || '').length }} karakter</span>
                </div>
            </div>

            <!-- Detail Fields Unit (dinamis dari DB) -->
            <div v-if="unitDetailFields.length > 0" class="mb-6 border-t border-slate-200 pt-5">
                <h3 class="text-sm font-semibold text-slate-700 mb-4">
                    Spesifikasi Khusus Unit
                </h3>
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
                            class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
                        />
                    </div>
                </div>
            </div>

            <!-- Fasilitas Unit (dari DB, scope = unit) -->
            <div v-if="allUnitFacilitiesOptions.length > 0" class="border-t border-slate-200 pt-5">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">
                    Fasilitas Khusus Unit
                </h3>
                <p class="text-xs text-slate-500 mb-4">Ketik atau pilih fasilitas yang HANYA terdapat di dalam tipe unit ini.</p>

                <TagInput
                    v-model="unit.facility_ids"
                    :options="allUnitFacilitiesOptions"
                    placeholder="Cari fasilitas unit..."
                />
            </div>
        </div>
    </div>
</div>
</template>
