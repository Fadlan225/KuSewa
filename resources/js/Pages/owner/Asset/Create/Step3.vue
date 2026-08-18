<script setup>
import { computed, ref } from 'vue';
import SearchableSelect from '@/Components/UI/SearchableSelect.vue';
import TagInput from '@/Components/UI/TagInput.vue';

const props = defineProps({
    form: Object,
    assetTypeDetails: Object, // { facilities, detail_fields }
});

const emit = defineEmits([
    'toggleFasilitas',
]);

// Computed: detail fields dari API
const detailFields = computed(() => props.assetTypeDetails?.detail_fields ?? []);

// Computed: fasilitas dari API
const facilitiesFromDB = computed(() => props.assetTypeDetails?.facilities ?? []);

// Flat array for TagInput
const allFacilitiesOptions = computed(() => {
    return facilitiesFromDB.value.map(fac => ({
        code: fac.id,
        name: `${fac.name} (${fac.category?.name || 'Fasilitas Lainnya'})`
    }));
});
</script>

<template>
<div class="space-y-6">
    <!-- STEP 3: DETAIL & FASILITAS ASET -->
    <h2 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-4">
        Detail & Fasilitas Aset
    </h2>

    <!-- ============================================ -->
    <!-- DETAIL SPESIFIKASI ASET (dinamis dari DB) -->
    <!-- ============================================ -->
    <template v-if="detailFields.length > 0">
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">
                Spesifikasi Aset
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div v-for="field in detailFields" :key="field.key">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ field.label }}
                        <span v-if="field.required" class="text-rose-500">*</span>
                    </label>

                    <!-- Input Bintang (Spesifik) -->
                    <input
                        v-if="field.key === 'stars'"
                        type="number"
                        v-model.number="form.detail[field.key]"
                        class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
                        :required="field.required"
                        min="1"
                        max="10"
                        @input="e => {
                            let val = parseInt(e.target.value);
                            if (val < 1) e.target.value = 1;
                            if (val > 10) e.target.value = 10;
                            form.detail[field.key] = e.target.value;
                        }"
                    />

                    <!-- Select -->
                    <select
                        v-else-if="field.type === 'select'"
                        v-model="form.detail[field.key]"
                        class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
                        :required="field.required"
                    >
                        <option value="" disabled>Pilih...</option>
                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                    </select>

                    <!-- Checkbox -->
                    <div v-else-if="field.type === 'checkbox'" class="flex items-center gap-2 mt-1">
                        <input
                            type="checkbox"
                            :id="`detail_${field.key}`"
                            v-model="form.detail[field.key]"
                            class="w-4 h-4 rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540]"
                        />
                        <label :for="`detail_${field.key}`" class="text-sm text-slate-600">Ya</label>
                    </div>

                    <!-- Radio -->
                    <div v-else-if="field.type === 'radio'" class="flex items-center gap-4 mt-2">
                        <label v-for="opt in field.options" :key="opt" class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                            <input
                                type="radio"
                                :name="`detail_${field.key}`"
                                :value="opt"
                                v-model="form.detail[field.key]"
                                class="border-slate-300 text-[#0A2540]"
                            />
                            {{ opt }}
                        </label>
                    </div>

                    <!-- Time -->
                    <input
                        v-else-if="field.type === 'time'"
                        type="time"
                        v-model="form.detail[field.key]"
                        class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
                        :required="field.required"
                    />

                    <!-- Number / Text fallback -->
                    <input
                        v-else
                        :type="field.type"
                        v-model="form.detail[field.key]"
                        :placeholder="field.label"
                        class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
                        :required="field.required"
                    />
                </div>
            </div>
        </div>
    </template>
    <div v-else class="text-sm text-slate-500 italic bg-slate-50 p-4 rounded-md border border-slate-200">
        Tidak ada spesifikasi khusus untuk jenis aset ini.
    </div>

    <!-- ============================================ -->
    <!-- FASILITAS ASET (dari DB, scope = asset) -->
    <!-- ============================================ -->
    <div v-if="allFacilitiesOptions.length > 0" class="border-t border-slate-200 pt-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-2">
            Fasilitas Umum Aset
        </h3>
        <p class="text-xs text-slate-500 mb-4">Ketik atau pilih fasilitas yang tersedia untuk semua penghuni / penyewa di aset ini.</p>

        <TagInput
            v-model="form.facility_ids"
            :options="allFacilitiesOptions"
            placeholder="Cari fasilitas (mis. WiFi, AC, Parkir)..."
        />


    </div>
</div>
</template>
