<script setup>
import { computed, watch, ref } from 'vue';
import CustomSelect from '@/Components/UI/CustomSelect.vue';

const props = defineProps({
    form: Object,
    categories: Array,
    availableTypes: Array,
    assetTypeDetails: Object,  // null | { rental_unit, allow_units, facilities, unit_facilities, detail_fields, unit_detail_fields }
    allowUnits: Boolean,
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
    <h2 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-4">
        Informasi Dasar Aset
    </h2>

    <!-- Nama Aset -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Aset <span class="text-rose-500">*</span></label>
        <input
            v-model="form.title"
            type="text"
            placeholder="cth: Kost Pak Budi, Hotel Bintang Lima, Gudang Jl. Sudirman"
            class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
            required
        />
    </div>

    <!-- Deskripsi -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi Aset <span class="text-rose-500">*</span></label>
        <textarea
            v-model="form.description"
            rows="4"
            minlength="100"
            placeholder="Deskripsikan aset Anda secara lengkap. Sebutkan keunggulan, kondisi, dan informasi penting lainnya..."
            class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition resize-none"
            required
        ></textarea>
        <p class="text-xs text-slate-500 mt-1.5">Minimal 100 karakter. {{ form.description?.length ?? 0 }} karakter.</p>
    </div>

    <!-- Kategori & Jenis Aset -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Kategori -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori Aset <span class="text-rose-500">*</span></label>
            <CustomSelect
                v-model="form.category_id"
                :options="categoryOptions"
                placeholder="Pilih Kategori"
            />
        </div>

        <!-- Jenis / Tipe Aset -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Aset <span class="text-rose-500">*</span></label>
            <CustomSelect
                v-model="form.asset_type_id"
                :options="typeOptions"
                :disabled="!availableTypes || availableTypes.length === 0"
                placeholder="Pilih Jenis"
            />
        </div>
    </div>

    </div>
</template>
