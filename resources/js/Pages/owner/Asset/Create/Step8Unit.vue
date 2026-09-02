<script setup>
import { computed, ref, onMounted } from 'vue';
import { ChevronDown, ChevronUp, Check } from 'lucide-vue-next';

const props = defineProps({
    form: Object,
    assetTypeDetails: Object,
    unitIndex: Number, // Passed from Index.vue loop
    unitLabel: {
        type: String,
        default: 'Unit'
    }
});

const emit = defineEmits([]);

const currentUnit = computed(() => {
    return props.form.units[props.unitIndex];
});

// Group facilities by category
const superGroupedFacilities = computed(() => {
    let facilities = [...(props.assetTypeDetails?.unit_facilities ?? [])];
    const mandatoryCategories = props.assetTypeDetails?.mandatory_unit_facility_categories?.map(c => c.name) || [];
    
    // Remove Perabot Kamar Mandi if Kamar Mandi Dalam is NOT selected in asset scope
    const assetFacilities = props.assetTypeDetails?.facilities ?? [];
    const isDalamSelected = assetFacilities.some(f => 
        f.name === 'Kamar Mandi Dalam' && 
        props.form.facility_ids.includes(f.id)
    );

    if (!isDalamSelected) {
        facilities = facilities.filter(f => f.category?.name !== 'Perabot Kamar Mandi');
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

// Toggle facility selection for the current unit
const toggleFacility = (facilityId) => {
    const unit = currentUnit.value;
    if (!unit.facility_ids) unit.facility_ids = [];
    
    const index = unit.facility_ids.indexOf(facilityId);
    if (index === -1) {
        unit.facility_ids.push(facilityId);
    } else {
        unit.facility_ids.splice(index, 1);
    }
};

// State for collapsed categories (default open)
const expandedCategories = ref({});

onMounted(() => {
    // Ensure facility_ids array exists for all units
    props.form.units.forEach(u => {
        if (!u.facility_ids) u.facility_ids = [];
    });

    // Open all categories by default
    const facilities = props.assetTypeDetails?.unit_facilities ?? [];
    facilities.forEach(fac => {
        const catName = fac.category?.name || 'Lainnya';
        expandedCategories.value[catName] = true;
    });
});

const toggleExpand = (catName) => {
    expandedCategories.value[catName] = !expandedCategories.value[catName];
};

const nextUnit = () => {
    if (currentUnitIndex.value < props.form.units.length - 1) {
        currentUnitIndex.value++;
    }
};

const prevUnit = () => {
    if (currentUnitIndex.value > 0) {
        currentUnitIndex.value--;
    }
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Fasilitas Khusus Tipe {{ currentUnit.name || (unitIndex + 1) }}</h2>
            <p class="text-sm text-slate-500">Pilih fasilitas spesifik yang tersedia di dalam {{ unitLabel.toLowerCase() }} ini.</p>
        </div>

        <div v-if="Object.keys(superGroupedFacilities).length === 0" class="text-center py-10 bg-slate-50 rounded-lg border border-slate-200">
            <p class="text-slate-500">Tidak ada fasilitas {{ unitLabel.toLowerCase() }} yang tersedia untuk tipe ini.</p>
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
                        <div @click="toggleExpand(catName)" 
                             class="px-5 py-4 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors border-b border-slate-200/60"
                             :class="{ 'border-b-0': !expandedCategories[catName] }">
                            <div>
                                <h4 class="text-base font-bold text-slate-800">{{ catName }}</h4>
                            </div>
                            <button type="button" class="text-slate-400 hover:text-slate-600 transition">
                                <ChevronUp v-if="expandedCategories[catName]" class="w-5 h-5" />
                                <ChevronDown v-else class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Body (Pill Options) -->
                        <div v-show="expandedCategories[catName]" class="p-5 bg-white">
                            <div class="flex flex-wrap gap-3">
                                <button 
                                    v-for="fac in facilities" :key="fac.id"
                                    type="button"
                                    @click="toggleFacility(fac.id)"
                                    class="px-4 py-2 text-sm font-medium border rounded-full transition-all duration-200 cursor-pointer flex items-center gap-2"
                                    :class="(currentUnit.facility_ids && currentUnit.facility_ids.includes(fac.id))
                                        ? 'border-[#FFC000] bg-[#FFF8E6] text-[#0A2540] font-bold shadow-sm' 
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300'">
                                    <span v-if="currentUnit.facility_ids && currentUnit.facility_ids.includes(fac.id)" class="text-[#FFC000]">
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
