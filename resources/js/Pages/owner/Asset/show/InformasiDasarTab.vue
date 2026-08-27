<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/Components/ui/card';
import { Loader2, Check, Pencil } from 'lucide-vue-next';

const props = defineProps({
    asset: Object,
    form: Object,
    specItems: Array,
});

const isExpanded = ref(false);
const descTextarea = ref(null);

const resizeTextarea = () => {
    if (!descTextarea.value) return;
    
    if (isExpanded.value) {
        descTextarea.value.style.height = 'auto';
        descTextarea.value.style.height = descTextarea.value.scrollHeight + 'px';
    } else {
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
        if (e.key === '-' || e.key === 'e' || e.key === 'E') {
            e.preventDefault();
        }
    }
};
</script>

<template>
    <div class="flex flex-col gap-6 animate-in fade-in duration-300">
        <!-- Basic form placeholder for now to replace the removed top section -->
        <Card class="bg-white rounded-xl shadow-sm border border-slate-100">
            <CardContent class="p-6">
            <h3 class="text-lg font-extrabold text-slate-900 mb-4">Informasi Dasar</h3>
            <div class="space-y-4 max-w-2xl">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nama Aset</label>
                    <div class="flex items-center gap-2 group/input">
                        <div class="relative flex-1">
                            <input v-model="form.title" type="text" class="w-full border border-slate-200 bg-slate-50 hover:bg-white focus:bg-white hover:border-slate-300 rounded-lg focus:ring-4 focus:ring-[#FFC000]/10 focus:border-[#FFC000] focus:outline-none transition-all px-4 py-2.5 pr-10 text-slate-800 font-semibold shadow-sm" />
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-300 group-hover/input:text-[#FFC000] transition-colors">
                                <Pencil class="w-4 h-4" />
                            </div>
                        </div>
                        <span v-if="form.processing" class="text-xs text-slate-400 shrink-0"><Loader2 class="animate-spin w-4 h-4" /></span>
                        <span v-else-if="form.recentlySuccessful" class="text-xs text-emerald-500 shrink-0"><Check class="w-4 h-4" /></span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Kategori</label>
                        <input type="text" disabled :value="asset.type?.category?.name || 'Kategori'" class="w-full border-slate-200 rounded-lg bg-slate-50 text-slate-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Tipe Aset</label>
                        <input type="text" disabled :value="asset.type?.name || 'Tipe Aset'" class="w-full border-slate-200 rounded-lg bg-slate-50 text-slate-500" />
                    </div>
                </div>
            </div>
            </CardContent>
        </Card>

        <!-- Deskripsi / Tentang Aset -->
        <Card class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <CardContent class="p-5 md:p-6">
            <h2 class="font-extrabold text-slate-900 mb-4 text-base">Tentang {{ asset.title }}</h2>

            <div class="mb-2 relative group/textarea">
                <textarea 
                    ref="descTextarea"
                    v-model="form.description" 
                    @input="resizeTextarea"
                    class="text-sm text-slate-700 w-full bg-slate-50 hover:bg-white border border-slate-200 group-hover/textarea:border-slate-300 focus:border-[#FFC000] focus:outline-none focus:bg-white focus:ring-4 focus:ring-[#FFC000]/10 rounded-xl px-4 py-4 pr-10 resize-none overflow-hidden leading-relaxed placeholder-slate-400 transition-all duration-300 block shadow-sm" 
                    placeholder="Tuliskan deskripsi menarik tentang aset Anda..."></textarea>
                
                <div class="absolute top-4 right-4 flex items-center pointer-events-none text-slate-300 group-hover/textarea:text-[#FFC000] transition-colors">
                    <Pencil class="w-4 h-4" />
                </div>
                
                <!-- Fade out effect when collapsed -->
                <div v-if="!isExpanded && form.description && form.description.length > 150" class="absolute bottom-[2px] left-[2px] right-[2px] h-12 bg-gradient-to-t from-white via-white/80 to-transparent pointer-events-none rounded-b-xl border-b border-slate-200"></div>

                <div v-if="form.errors.description" class="text-xs text-rose-500 mt-1">{{ form.errors.description }}</div>
                
                <button v-if="form.description && form.description.length > 150" @click="isExpanded = !isExpanded" class="text-yellow-600 hover:text-yellow-700 font-bold mt-3 hover:underline focus:outline-none transition block px-2">
                    {{ isExpanded ? 'Tampilkan Lebih Sedikit' : 'Baca Selengkapnya' }}
                </button>
            </div>
            </CardContent>
        </Card>

        <!-- Spesifikasi Dasar -->
        <Card class="bg-white rounded-xl border border-slate-200 shadow-sm h-fit">
            <CardContent class="p-5 md:p-6">
            <h2 class="font-extrabold text-slate-900 text-base mb-4">Spesifikasi Dasar</h2>
            
            <div v-if="form.detail && Object.keys(form.detail).length > 0" class="space-y-3 text-sm">
                <div v-for="(value, key) in form.detail" :key="key" class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                    <span class="text-slate-500 capitalize w-1/2">{{ String(key).replace(/_/g, ' ') }}</span>
                    <div class="relative w-1/2 flex items-center justify-end group/input">
                        <input 
                            v-model="form.detail[key]" 
                            :type="isTimeField(key) ? 'time' : (isNumberField(key) ? 'number' : 'text')" 
                            :min="isNumberField(key) ? '0' : null"
                            @keydown="handleNumberInput($event, key)"
                            class="text-sm font-semibold text-slate-800 border border-slate-200 focus:outline-none focus:border-[#FFC000] focus:ring-4 focus:ring-[#FFC000]/10 bg-slate-50 hover:bg-white focus:bg-white hover:border-slate-300 rounded-lg pl-3 py-2 w-full text-right transition-all shadow-sm" 
                            :class="{ 'pr-14': getSuffix(key), 'pr-8': !getSuffix(key) }"
                        />
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-300 group-hover/input:text-[#FFC000] transition-colors">
                            <span v-if="getSuffix(key)" class="text-xs font-bold mr-1">{{ getSuffix(key) }}</span>
                            <Pencil class="w-3.5 h-3.5" />
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-sm text-slate-400 text-center py-6">
                Belum ada informasi umum yang ditambahkan.
            </div>
            </CardContent>
        </Card>
    </div>
</template>
