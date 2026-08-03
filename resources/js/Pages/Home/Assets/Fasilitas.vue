<script setup>
import { ref, watch, computed, onUnmounted } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    facilitiesGrouped: { type: Array, default: () => [] }
});

const emit = defineEmits(['close']);

// Mobile touch drag handling
const touchStartY = ref(0);
const sheetTransform = ref('translateY(0)');

const onTouchStart = (e) => {
    touchStartY.value = e.touches[0].clientY;
};
const onTouchMove = (e) => {
    const deltaY = e.touches[0].clientY - touchStartY.value;
    if (deltaY > 0) {
        sheetTransform.value = `translateY(${deltaY}px)`;
    }
};
const onTouchEnd = (e) => {
    const deltaY = e.changedTouches[0].clientY - touchStartY.value;
    if (deltaY > 100) {
        close();
    } else {
        sheetTransform.value = 'translateY(0)';
    }
    touchStartY.value = 0;
};

const close = () => {
    sheetTransform.value = 'translateY(0)';
    emit('close');
};

watch(() => props.show, (val) => {
    if (val) {
        sheetTransform.value = 'translateY(0)';
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

const topFacilities = computed(() => {
    let flat = [];
    for (let group of props.facilitiesGrouped) {
        flat.push(...group.facilities);
    }
    return flat.slice(0, 8);
});

</script>

<template>
    <div>
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" @click="close" class="fixed inset-0 bg-black/60 z-[105]"></div>
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out md:transition-opacity md:duration-300 md:ease-out md:scale-95"
            enter-from-class="translate-y-full opacity-0 md:translate-y-0"
            enter-to-class="translate-y-0 opacity-100 md:scale-100"
            leave-active-class="transition-transform duration-200 ease-in md:transition-opacity md:duration-200 md:ease-in md:scale-100"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0 md:translate-y-0 md:scale-95"
        >
            <div v-if="show" class="fixed inset-0 z-[110] flex items-end md:items-center justify-center pointer-events-none px-0 md:px-4">
                <!-- Wrapper Modal / Sheet -->
                <div
                    class="relative w-full md:max-w-2xl bg-white flex flex-col rounded-t-[24px] md:rounded-[24px] shadow-[0_-10px_40px_rgba(0,0,0,0.15)] md:shadow-2xl pointer-events-auto h-[90vh] md:h-auto md:max-h-[85vh] overflow-hidden"
                    :style="{ transform: sheetTransform, transition: touchStartY === 0 ? 'transform 0.2s ease-out' : 'none' }"
                >
                    <!-- Header -->
                    <div class="md:hidden w-full flex justify-center pt-4 pb-2 cursor-grab active:cursor-grabbing touch-none"
                         @touchstart="onTouchStart"
                         @touchmove.prevent="onTouchMove"
                         @touchend="onTouchEnd">
                        <div class="w-12 h-1.5 bg-[#6C757D]/30 rounded-full"></div>
                    </div>
                    
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <h2 class="text-lg md:text-xl font-extrabold text-[#0A2540]">Semua Fasilitas</h2>
                        <button @click="close" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-[#0A2540] transition">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto p-6 hide-scrollbar bg-white">
                        
                        <!-- Top facilities preview (Flat list combined) -->
                        <div class="bg-white border border-[#EBEBEB] rounded-xl p-6 mb-8">
                            <div class="grid grid-cols-3 md:grid-cols-4 gap-y-6 gap-x-2">
                                <div v-for="fac in topFacilities" :key="fac.id" class="flex flex-col items-center justify-start text-center gap-2.5">
                                    <i :class="['fa-solid text-[20px] text-[#222222]', 'fa-' + (fac.category?.icon || 'check')]"></i>
                                    <span class="text-[13px] text-[#222222] leading-tight px-1">{{ fac.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- All Facilities Grouped -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-10">
                            <div v-for="group in facilitiesGrouped" :key="group.name" class="flex flex-col">
                                <div class="flex items-center gap-3 mb-5 pb-2">
                                    <span class="text-[16px] font-semibold text-[#222222] capitalize">{{ group.name }}</span>
                                </div>
                                <ul class="space-y-4 pl-4">
                                    <li v-for="fac in group.facilities" :key="fac.id" class="flex items-start gap-3 text-[15px] text-[#222222]">
                                        <i class="fa-regular fa-circle text-[6px] mt-[8px] shrink-0 text-[#222222]"></i>
                                        <span class="leading-relaxed">{{ fac.name }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>