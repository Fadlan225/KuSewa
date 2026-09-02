<script setup>
import { computed, watch, ref } from 'vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import GroupedSearchableSelect from '@/Components/ui/GroupedSearchableSelect.vue';
import Step2 from './Step2.vue';

const props = defineProps({
    form: Object,
    categories: Array,
    availableTypes: Array,
    assetTypeDetails: Object,  // null | { rental_unit, allow_units, facilities, unit_facilities, detail_fields, unit_detail_fields }
    allowUnits: Boolean,
    currentStep: Number,
    assetTypeName: String,
});

const emit = defineEmits([
    'tambahUnit', 'hapusUnit', 'toggleUnitFasilitas', 'toggleFasilitas',
    'tambahUnitKategoriFoto', 'hapusUnitKategoriFoto', 'handleUnitFileUpload', 'hapusUnitFoto',
    'handleUnitThumbnailUpload', 'hapusUnitThumbnail'
]);
// Computed: label periode sewa
const rentalUnitLabel = computed(() => {
    const map = {
        hour: 'Per Jam',
        night: 'Per Malam',
        day: 'Per Hari',
        month: 'Per Bulan',
    };
    return map[props.assetTypeDetails?.rental_unit] ?? '-';
});

// Computed: detail fields dari API
const detailFields = computed(() => props.assetTypeDetails?.detail_fields ?? []);
const unitDetailFields = computed(() => props.assetTypeDetails?.unit_detail_fields ?? []);

const categoryOptions = computed(() => {
    return props.categories?.map(c => ({ label: c.name, value: c.id })) ?? [];
});

const typeOptions = computed(() => {
    return props.availableTypes?.map(t => ({ label: t.name, value: t.id })) ?? [];
});

// Computed: fasilitas dari API
const facilitiesFromDB = computed(() => props.assetTypeDetails?.facilities ?? []);
const unitFacilitiesFromDB = computed(() => props.assetTypeDetails?.unit_facilities ?? []);

// Computed: kategori galeri (global)
const galleryCategoriesFromDB = computed(() => props.assetTypeDetails?.gallery_categories ?? []);

// Fasilitas aset dropdown
const fasilitasDropdownOpen = ref(false);
const fasilitasDropdownRef = ref(null);

const toggleFasilitasDropdown = () => {
    fasilitasDropdownOpen.value = !fasilitasDropdownOpen.value;
};

// Unit fasilitas dropdown state (per unit index)
const unitFasilitasDropdownOpen = ref(null);

const toggleUnitFasilitasDropdown = (index) => {
    unitFasilitasDropdownOpen.value = unitFasilitasDropdownOpen.value === index ? null : index;
};
</script>

<template>
<div class="space-y-6">
<!-- STEP 1: INFORMASI ASET -->

    <!-- Tipe Aset (Grouped Searchable Select) -->
    <div class="relative z-20">
        <label class="block text-base font-bold text-slate-800 mb-1.5">Tipe Aset <span class="text-rose-500">*</span></label>
        <GroupedSearchableSelect
            v-model="form.asset_type_id"
            :categories="categories"
            placeholder="Cari atau pilih tipe aset..."
        />
        <p class="text-xs text-slate-500 mt-1.5">Pilih tipe aset yang ingin Anda daftarkan.</p>
    </div>

    <!-- Nama Aset (Dynamic) -->
    <div>
        <label class="block text-base font-bold text-slate-800 mb-1.5">Nama {{ assetTypeName || 'Aset' }} <span class="text-rose-500">*</span></label>
        <input
            v-model="form.title"
            type="text"
            :placeholder="'Contoh : ' + (assetTypeName || 'Aset') + ' Suka Maju'"
            class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition"
            required
        />
    </div>



    <div class="pt-6 mt-6 border-t border-slate-200">
        <Step2 
            :form="form" 
            :currentStep="currentStep" 
            :assetTypeName="assetTypeName" 
        />
    </div>

    </div>
</template>
