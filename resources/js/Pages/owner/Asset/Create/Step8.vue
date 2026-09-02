<script setup>
import { computed, ref, onMounted } from 'vue';
import { ChevronDown, ChevronUp, Check } from 'lucide-vue-next';

const props = defineProps({
    form: Object,
    assetTypeDetails: Object,
});

const emit = defineEmits(['toggleFasilitas']);

// Group facilities by category
const superGroupedFacilities = computed(() => {
    let facilities = [...(props.assetTypeDetails?.facilities ?? [])];
    const mandatoryCategories = props.assetTypeDetails?.mandatory_facility_categories?.map(c => c.name) || [];
    
    // Dynamically inject Perabot Kamar Mandi from unit_facilities if Kamar Mandi Luar/Bersama is selected
    const isLuarOrBersamaSelected = facilities.some(f => 
        (f.name === 'Kamar Mandi Luar' || f.name === 'Kamar Mandi Bersama') && 
        props.form.facility_ids.includes(f.id)
    );

    if (isLuarOrBersamaSelected) {
        const perabotFacilities = (props.assetTypeDetails?.unit_facilities ?? [])
            .filter(f => f.category?.name === 'Perabot Kamar Mandi');
        facilities = facilities.concat(perabotFacilities);
    }

    const mandatoryGroups = {};
    const optionalGroups = {};

    facilities.forEach(fac => {
        const catName = fac.category?.name || 'Lainnya';
        
        if (mandatoryCategories.includes(catName)) {
            if (!mandatoryGroups[catName]) mandatoryGroups[catName] = [];
            mandatoryGroups[catName].push(fac);
        } else {
            if (!optionalGroups[catName]) optionalGroups[catName] = [];
            optionalGroups[catName].push(fac);
        }
    });

    const result = {};
    if (Object.keys(mandatoryGroups).length > 0) {
        result.mandatory = {
            label: 'Fasilitas Dasar',
            categories: mandatoryGroups
        };
    }
    if (Object.keys(optionalGroups).length > 0) {
        result.optional = {
            label: 'Fasilitas Lainnya',
            categories: optionalGroups
        };
    }

    return result;
});

// Toggle facility selection
const toggleFacility = (fac) => {
    // Jika kategori Kamar Mandi, buat menjadi single-select (radio)
    if (fac.category?.name === 'Kamar Mandi') {
        const isAlreadySelected = props.form.facility_ids.includes(fac.id);
        
        // Cari semua fasilitas Kamar Mandi di asset
        const kamarMandiFacilities = props.assetTypeDetails?.facilities
            ?.filter(f => f.category?.name === 'Kamar Mandi')
            ?.map(f => f.id) || [];
            
        // Hapus semua fasilitas Kamar Mandi dari pilihan secara reaktif
        for (let i = props.form.facility_ids.length - 1; i >= 0; i--) {
            if (kamarMandiFacilities.includes(props.form.facility_ids[i])) {
                props.form.facility_ids.splice(i, 1);
            }
        }
        
        // Jika sebelumnya belum dipilih, maka pilih
        if (!isAlreadySelected) {
            props.form.facility_ids.push(fac.id);
        }
        return;
    }

    const index = props.form.facility_ids.indexOf(fac.id);
    if (index === -1) {
        props.form.facility_ids.push(fac.id);
    } else {
        props.form.facility_ids.splice(index, 1);
    }
};

// State for collapsed categories (default open)
const expandedCategories = ref({});

onMounted(() => {
    // Open all categories by default
    const facilities = props.assetTypeDetails?.facilities ?? [];
    facilities.forEach(fac => {
        const catName = fac.category?.name || 'Lainnya';
        expandedCategories.value[catName] = true;
    });
});

const toggleExpand = (catName) => {
    expandedCategories.value[catName] = !expandedCategories.value[catName];
};
</script>

<template>
    <div class="space-y-8">
        <div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Fasilitas {{ assetTypeDetails?.name || 'Aset' }}</h2>
            <p class="text-sm text-slate-500">Pilih fasilitas yang tersedia secara umum dan dapat digunakan oleh penyewa. Fasilitas ini berlaku untuk seluruh aset.</p>
        </div>

        <div v-if="Object.keys(superGroupedFacilities).length === 0" class="text-center py-10 bg-slate-50 rounded-lg border border-slate-200">
            <p class="text-slate-500">Tidak ada fasilitas aset yang tersedia untuk tipe ini.</p>
        </div>

        <div v-else class="space-y-8">
            <div v-for="(group, groupKey) in superGroupedFacilities" :key="groupKey">
                <!-- Super Category Header -->
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    {{ group.label }}
                    <span v-if="groupKey === 'mandatory'" class="text-rose-500 text-xl leading-none">*</span>
                </h3>

                <!-- Sub Categories -->
                <div class="space-y-5">
                    <div v-for="(facilities, catName) in group.categories" :key="catName" 
                         class="border border-slate-300 rounded-lg bg-white overflow-hidden shadow-sm">
                        
                        <!-- Header Kategori -->
                        <div :class="[
                                'px-5 py-4 flex items-center justify-between border-b border-slate-200/60',
                                groupKey !== 'mandatory' ? 'cursor-pointer hover:bg-slate-50 transition-colors' : '',
                                groupKey !== 'mandatory' && !expandedCategories[catName] ? 'border-b-0' : ''
                             ]"
                             @click="groupKey !== 'mandatory' ? toggleExpand(catName) : null">
                            <div>
                                <h4 class="text-base font-bold text-slate-800">{{ catName }}</h4>
                            </div>
                            <button v-if="groupKey !== 'mandatory'" type="button" class="text-slate-400 hover:text-slate-600 transition">
                                <ChevronUp v-if="expandedCategories[catName]" class="w-5 h-5" />
                                <ChevronDown v-else class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Body (Pill Options) -->
                        <div v-show="groupKey === 'mandatory' || expandedCategories[catName]" class="p-5 bg-white">
                            <div class="flex flex-wrap gap-3">
                                <button 
                                    v-for="fac in facilities" :key="fac.id"
                                    type="button"
                                    @click="toggleFacility(fac)"
                                    class="px-4 py-2 text-sm font-medium border rounded-full transition-all duration-200 cursor-pointer flex items-center gap-2"
                                    :class="form.facility_ids.includes(fac.id) 
                                        ? 'border-[#FFC000] bg-[#FFF8E6] text-[#0A2540] font-bold shadow-sm' 
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300'">
                                    <span v-if="form.facility_ids.includes(fac.id)" class="text-[#FFC000]">
                                        <Check class="w-4 h-4" stroke-width="3" />
                                    </span>
                                    {{ fac.name }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
