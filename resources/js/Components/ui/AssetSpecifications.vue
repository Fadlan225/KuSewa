<script setup>
import { computed } from 'vue';

const props = defineProps({
    detail: {
        type: Object,
        default: () => ({})
    }
});

// Spesifikasi tambahan dari field `detail` JSON (bukan fasilitas)
const specification = computed(() => {
    const d = props.detail || {};
    // Hapus key lama yang sudah tidak relevan
    const cleaned = { ...d };
    delete cleaned.facility;
    delete cleaned.fasilitas;
    return cleaned;
});

// Map nama kunci teknis ke label manusiawi
const specKeyLabels = {
    luas_bangunan:    'Luas Bangunan',
    luas_tanah:       'Luas Tanah',
    jumlah_lantai:    'Jumlah Lantai',
    kapasitas:        'Kapasitas',
    jumlah_kamar:     'Jumlah Kamar',
    jumlah_kamar_mandi: 'Kamar Mandi',
    daya_listrik:     'Daya Listrik',
    lebar:            'Lebar',
    panjang:          'Panjang',
    tinggi:           'Tinggi',
    berat_maksimal:   'Berat Maksimal',
    visibility:       'Visibilitas',
    visibility_tinggi:'Visibilitas Tinggi',
    visibility_rendah:'Visibilitas Rendah',
    ukuran_billboard: 'Ukuran Billboard',
    slot_tersedia:    'Slot Tersedia',
    total_slot:       'Total Slot',
    jam_operasional:  'Jam Operasional',
    area:             'Area',
    kondisi:          'Kondisi',
    sertifikat:       'Sertifikat',
    tahun_dibangun:   'Tahun Dibangun',
};

const formatSpecKey = (key) => {
    return specKeyLabels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const formatSpecValue = (key, val) => {
    if (typeof val === 'boolean') return val ? 'Ya' : 'Tidak';
    if (val === true || val === 'true') return 'Ya';
    if (val === false || val === 'false') return 'Tidak';
    return val;
};

const getSpecKeys = computed(() => Object.keys(specification.value));
</script>

<template>
    <div v-if="getSpecKeys.length > 0" class="py-10 md:py-12 border-b border-gray-100">
        <h3 class="text-[22px] font-bold text-[#222222] mb-6">Spesifikasi Aset</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-6 gap-x-4">
            <div v-for="key in getSpecKeys" :key="key" class="flex flex-col">
                <span class="text-gray-500 text-sm mb-1">{{ formatSpecKey(key) }}</span>
                <span class="font-bold text-[#0A2540]">{{ formatSpecValue(key, specification[key]) }}</span>
            </div>
        </div>
    </div>
</template>
