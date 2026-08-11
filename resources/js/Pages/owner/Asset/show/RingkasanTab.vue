<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
    asset: Object,
    form: Object,
    specItems: Array,
    assetFacilities: Array,
});

const isExpanded = ref(false);
const descTextarea = ref(null);

const resizeTextarea = () => {
    if (!descTextarea.value) return;
    
    if (isExpanded.value) {
        descTextarea.value.style.height = 'auto';
        descTextarea.value.style.height = descTextarea.value.scrollHeight + 'px';
    } else {
        // Collapsed height (~4 lines of text-sm leading-relaxed)
        descTextarea.value.style.height = '100px'; 
    }
};

onMounted(() => {
    nextTick(resizeTextarea);
});

watch(isExpanded, () => {
    nextTick(resizeTextarea);
});

watch(() => props.form.description, () => {
    if (isExpanded.value) {
        nextTick(resizeTextarea);
    }
});

// Helper for dynamic inputs
const isNumberField = (key) => {
    const k = String(key).toLowerCase();
    return ['floor', 'start', 'land', 'building', 'area', 'luas'].some(w => k.includes(w));
};

const isTimeField = (key) => {
    const k = String(key).toLowerCase();
    return ['check_in', 'check_out', 'check in', 'check out', 'waktu', 'jam'].some(w => k.includes(w));
};

const getSuffix = (key) => {
    const k = String(key).toLowerCase();
    if (['area', 'luas', 'land', 'building'].some(w => k.includes(w))) return 'm²';
    return null;
};

const handleNumberInput = (e, key) => {
    if (isNumberField(key)) {
        // Mencegah tanda minus (-) atau huruf e untuk input angka
        if (e.key === '-' || e.key === 'e' || e.key === 'E') {
            e.preventDefault();
        }
    }
};
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-in fade-in duration-300">
        <!-- Deskripsi & Info Singkat -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6">
            <h2 class="font-bold text-slate-800 mb-4 text-base">Tentang {{ asset.title }}</h2>

            <div class="mb-2 relative">
                <textarea 
                    ref="descTextarea"
                    v-model="form.description" 
                    @input="resizeTextarea"
                    class="text-sm text-slate-700 w-full bg-transparent border-none p-0 focus:ring-0 resize-none overflow-hidden leading-relaxed placeholder-slate-400 transition-all duration-300 block" 
                    placeholder="Tuliskan deskripsi menarik tentang properti Anda..."></textarea>
                
                <!-- Fade out effect when collapsed -->
                <div v-if="!isExpanded && form.description && form.description.length > 150" class="absolute bottom-8 left-0 right-0 h-10 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>

                <div v-if="form.errors.description" class="text-xs text-rose-500 mt-1">{{ form.errors.description }}</div>
                
                <button v-if="form.description && form.description.length > 150" @click="isExpanded = !isExpanded" class="text-[#0A2540] font-bold mt-2 hover:underline focus:outline-none transition block">
                    {{ isExpanded ? 'Tampilkan Lebih Sedikit' : 'Baca Selengkapnya' }}
                </button>
            </div>
        </div>

        <!-- Informasi Umum -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6 h-fit">
            <h2 class="font-bold text-slate-800 text-base mb-4">Informasi Umum</h2>
            
            <!-- Asset Details (Editable) -->
            <div v-if="form.detail && Object.keys(form.detail).length > 0" class="space-y-3 text-sm">
                <div v-for="(value, key) in form.detail" :key="key" class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                    <span class="text-slate-500 capitalize w-1/2">{{ String(key).replace(/_/g, ' ') }}</span>
                    <div class="relative w-1/2 flex items-center justify-end">
                        <input 
                            v-model="form.detail[key]" 
                            :type="isTimeField(key) ? 'time' : (isNumberField(key) ? 'number' : 'text')" 
                            :min="isNumberField(key) ? '0' : null"
                            @keydown="handleNumberInput($event, key)"
                            class="text-sm font-semibold text-slate-800 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-1.5 w-full text-right bg-white transition shadow-sm" 
                            :class="{ 'pr-9': getSuffix(key) }"
                        />
                        <span v-if="getSuffix(key)" class="absolute right-3 text-slate-400 text-xs font-bold pointer-events-none">{{ getSuffix(key) }}</span>
                    </div>
                </div>
            </div>
            <div v-else class="text-sm text-slate-400 text-center py-6">
                Belum ada informasi umum yang ditambahkan.
            </div>
        </div>
    </div>
</template>
