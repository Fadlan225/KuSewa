<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    images: {
        type: Array,
        default: () => []
    }
});

const allImages = computed(() => {
    return props.images?.map(img => img.image_url)?.filter(Boolean) ?? [];
});

const hasImages = computed(() => allImages.value.length > 0);
const mainImage = computed(() => allImages.value[0]);
const gridImages = computed(() => allImages.value.slice(1, 5));

const showGalleryModal = ref(false);

// Mobile Gallery Logic
const currentMobileImageIndex = ref(0);
const galleryTransitionName = ref('slide-left');
const touchGalleryStartX = ref(0);
const touchGalleryEndX = ref(0);

const nextImage = () => {
    if (hasImages.value && currentMobileImageIndex.value < allImages.value.length - 1) {
        galleryTransitionName.value = 'slide-left';
        currentMobileImageIndex.value++;
    }
};

const prevImage = () => {
    if (hasImages.value && currentMobileImageIndex.value > 0) {
        galleryTransitionName.value = 'slide-right';
        currentMobileImageIndex.value--;
    }
};

const handleGalleryTouchStart = (e) => { touchGalleryStartX.value = e.changedTouches[0].screenX; };
const handleGalleryTouchEnd = (e) => {
    touchGalleryEndX.value = e.changedTouches[0].screenX;
    if (touchGalleryEndX.value < touchGalleryStartX.value - 50) nextImage();
    if (touchGalleryEndX.value > touchGalleryStartX.value + 50) prevImage();
};
</script>

<template>
    <div>
        <!-- Gallery Modal (Lightbox sederhana) -->
        <div v-if="showGalleryModal" class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center p-4 sm:p-10">
            <button @click="showGalleryModal = false" class="absolute top-6 right-6 text-white bg-white/10 hover:bg-white/20 p-3 rounded-full transition z-50">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <div class="w-full max-w-5xl max-h-full overflow-y-auto no-scrollbar grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="(img, idx) in allImages" :key="idx" class="relative w-full h-auto min-h-[300px] bg-gray-900 rounded-xl shadow-lg overflow-hidden flex items-center justify-center">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-600 z-0">
                        <i class="fa-solid fa-image text-5xl mb-2"></i>
                        <span class="font-medium">No Image</span>
                    </div>
                    <img :src="img" class="w-full h-full object-cover relative z-10" @error="$event.target.style.display='none'" />
                </div>
            </div>
        </div>

        <!-- MOBILE GALLERY CAROUSEL (Mobile Only) -->
        <div id="foto" class="block md:hidden relative w-full h-[300px] sm:h-[400px] rounded-2xl overflow-hidden mb-8 touch-pan-y" @touchstart.passive="handleGalleryTouchStart" @touchend.passive="handleGalleryTouchEnd">
            <transition :name="galleryTransitionName" mode="out-in">
                <div :key="currentMobileImageIndex" class="w-full h-full relative" @click="hasImages && (showGalleryModal = true)">
                    <div v-if="!hasImages" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 z-0 bg-gray-100">
                        <i class="fa-solid fa-image text-6xl mb-3"></i>
                        <span class="font-medium text-sm">Tidak ada foto</span>
                    </div>
                    <img v-else :src="allImages[currentMobileImageIndex]" class="w-full h-full object-cover relative z-10" @error="$event.target.style.display='none'" />
                </div>
            </transition>
            <!-- Indicator & Button -->
            <div v-if="hasImages" class="absolute bottom-4 right-4 bg-black/60 text-white text-xs font-bold px-3 py-1.5 rounded-full z-10 tracking-widest backdrop-blur-sm shadow-md">
                {{ currentMobileImageIndex + 1 }} / {{ allImages.length }}
            </div>

            <!-- Left/Right navigation hints -->
            <button v-if="hasImages && currentMobileImageIndex > 0" @click.stop="prevImage" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-white/70 hover:bg-white text-gray-800 rounded-full z-20 shadow-sm transition">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <button v-if="hasImages && currentMobileImageIndex < allImages.length - 1" @click.stop="nextImage" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-white/70 hover:bg-white text-gray-800 rounded-full z-20 shadow-sm transition">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>

        <!-- HERO GALLERY GRID (Desktop Only) -->
        <div class="hidden md:flex relative rounded-2xl overflow-hidden mb-12 h-[300px] sm:h-[400px] md:h-[500px] gap-2">
            <!-- Left Large Image -->
            <div class="w-full md:w-1/2 h-full cursor-pointer hover:opacity-95 transition bg-gray-100 flex items-center justify-center relative overflow-hidden" @click="hasImages && (showGalleryModal = true)">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 z-0">
                    <i class="fa-solid fa-image text-6xl mb-3"></i>
                    <span class="font-medium text-sm">Tidak ada foto</span>
                </div>
                <img v-if="hasImages" :src="mainImage" class="w-full h-full object-cover relative z-10" alt="Main Image" @error="$event.target.style.display='none'" />
            </div>

            <!-- Right Small Images Grid -->
            <div class="hidden md:grid w-1/2 h-full grid-cols-2 grid-rows-2 gap-2">
                <div v-for="(img, index) in gridImages" :key="index" class="relative h-full w-full cursor-pointer overflow-hidden group bg-gray-100" @click="showGalleryModal = true">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-300 z-0">
                        <i class="fa-solid fa-image text-3xl mb-1"></i>
                        <span class="text-[10px] font-medium">No Image</span>
                    </div>
                    <img :src="img" class="w-full h-full object-cover relative z-10 group-hover:scale-105 transition duration-500" :alt="`Gallery image ${index+1}`" @error="$event.target.style.display='none'" />
                    <div v-if="index === 3 && allImages.length > 5" class="absolute inset-0 bg-black/40 flex items-center justify-center text-white font-bold text-lg z-20">
                        +{{ allImages.length - 5 }} Foto
                    </div>
                </div>
            </div>

            <button v-if="hasImages" @click="showGalleryModal = true" class="absolute bottom-4 right-4 bg-white/95 backdrop-blur-sm px-4 py-2 rounded-xl text-sm font-bold shadow-lg border border-gray-200 hover:bg-gray-50 transition z-10 flex items-center gap-2">
                <i class="fa-solid fa-images"></i> Tampilkan semua foto
            </button>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
    transition: all 0.2s ease-out;
}
.slide-left-enter-from { opacity: 0; transform: translateX(30px); }
.slide-left-leave-to { opacity: 0; transform: translateX(-30px); }
.slide-right-enter-from { opacity: 0; transform: translateX(-30px); }
.slide-right-leave-to { opacity: 0; transform: translateX(30px); }
</style>
