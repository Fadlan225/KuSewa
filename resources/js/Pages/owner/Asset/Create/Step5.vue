<script setup>
import { computed, onMounted } from 'vue';
import { ChevronUp, ChevronDown, Trash2 } from 'lucide-vue-next';
import PricingEditor from './PricingEditor.vue';

const props = defineProps({
    form: Object,
    allowUnits: Boolean,
    assetTypeDetails: Object,
    unitLabel: {
        type: String,
        default: 'Unit'
    }, // { rental_unit }
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
    <!-- STEP 5: HARGA SEWA -->

    <p class="text-sm text-slate-500 mb-4">
        Atur tarif sewa unit Anda. Anda dapat menambahkan berbagai variasi harga berdasarkan durasi (misal: Harian, Mingguan, Bulanan).
    </p>

    <!-- ============================================ -->
    <!-- HARGA — Tanpa Unit (satu harga untuk aset) -->
    <!-- ============================================ -->
    <div v-if="!allowUnits">
        <PricingEditor 
            :pricings="form.pricings" 
            :detail="form.detail" 
            :assetTypeDetails="assetTypeDetails" 
        />
    </div>

    <!-- ============================================ -->
    <!-- HARGA — Dengan Unit (harga per tipe kamar) -->
    <!-- ============================================ -->
    <div v-else class="space-y-6">
        <div v-for="(unit, unitIndex) in form.units" :key="unit._id" class="border border-slate-300 rounded-lg relative bg-white shadow-sm">
            
            <!-- Header (Collapsible) -->
            <div @click="toggleExpand(unitIndex)" class="bg-white p-5 flex items-center justify-between cursor-pointer border-b border-slate-200/60 hover:bg-slate-50 transition-colors rounded-t-lg" :class="{ 'rounded-b-lg border-b-0': unit.is_expanded === false }">
                <div class="flex items-center gap-4">
                    <div class="text-slate-500 font-bold text-lg">
                        {{ String(unitIndex + 1).padStart(2, '0') }}
                    </div>
                    <div>
                        <p class="text-lg font-bold text-[#0A2540]">{{ unit.name ? 'Tipe ' + unit.name : `Tipe ${unitLabel || 'Unit'}` }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ unit.quantity || 0 }} {{ (unitLabel || 'Unit').toLowerCase() }}<template v-if="unit.detail?.ukuran_kamar"> &middot; {{ unit.detail.ukuran_kamar }} m</template><template v-else-if="unit.detail?.room_size"> &middot; {{ unit.detail.room_size }} m&sup2;</template> &middot; {{ (unit.quantity || 0) - (unit.empty_rooms || 0) }} terisi</p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" class="w-8 h-8 rounded-md hover:bg-slate-200 text-slate-400 transition flex items-center justify-center cursor-pointer">
                        <ChevronUp v-if="unit.is_expanded !== false" class="w-5 h-5" />
                        <ChevronDown v-else class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div v-show="unit.is_expanded !== false" class="p-6">
                <PricingEditor 
                    :pricings="unit.pricings" 
                    :detail="unit.detail" 
                    :assetTypeDetails="assetTypeDetails" 
                />
            </div>
        </div>
    </div>
</div>
</template>
