<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
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

watch(showGalleryModal, (isOpen) => {
    if (typeof window !== 'undefined') {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        document.body.style.overflow = '';
    }
});

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

// Gallery Modal Categories Logic (Dummy logic for now)
const activeCategory = ref('Semua');

const galleryCategories = computed(() => {
    const total = allImages.value.length;
    return [
        { name: 'Semua', count: total },
        { name: 'Tampak Luar', count: Math.ceil(total / 3) },
        { name: 'Interior', count: Math.floor(total / 3) },
        { name: 'Fasilitas', count: total - Math.ceil(total / 3) - Math.floor(total / 3) }
    ];
});

const chunkedImagesByCategory = computed(() => {
    let cats = galleryCategories.value.filter(c => c.name !== 'Semua' && c.count > 0);

    // Filter berdasarkan kategori yang dipilih
    if (activeCategory.value !== 'Semua') {
        cats = cats.filter(c => c.name === activeCategory.value);
    }

    return cats.map(cat => {
        let imgs = allImages.value;
        const total = allImages.value.length;
        if (cat.name === 'Tampak Luar') {
            imgs = imgs.slice(0, Math.ceil(total / 3));
        } else if (cat.name === 'Interior') {
            imgs = imgs.slice(Math.ceil(total / 3), Math.ceil(total / 3) * 2);
        } else if (cat.name === 'Fasilitas') {
            imgs = imgs.slice(Math.ceil(total / 3) * 2);
        }

        const urls = imgs.map(url => ({ url }));
        const chunks = [];
        for (let i = 0; i < urls.length; ) {
            chunks.push([urls[i]]); // Full width
            i++;
            if (i < urls.length) {
                const pair = [urls[i]];
                i++;
                if (i < urls.length) {
                    pair.push(urls[i]);
                    i++;
                }
                chunks.push(pair); // Pair (half width)
            }
        }

        return {
            name: cat.name,
            chunks
        };
    });
});

const closeGalleryModal = () => {
    showGalleryModal.value = false;
};
</script>

<template>
    <div>
        <!-- ============================================================ -->
        <!-- GALLERY MODAL (FULLSCREEN / BOTTOM SHEET)                    -->
        <!-- ============================================================ -->
        <transition name="fade">
            <!-- Backdrop -->
            <div v-if="showGalleryModal" class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm" @click="closeGalleryModal"></div>
        </transition>

        <transition name="slide-up">
            <div v-if="showGalleryModal" class="fixed inset-x-0 bottom-0 top-12 md:top-0 md:inset-0 z-[100] bg-white md:bg-white/95 md:backdrop-blur-xl flex flex-col rounded-t-3xl md:rounded-none overflow-hidden shadow-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between px-4 sm:px-8 py-4 border-b border-gray-200/50 bg-transparent sticky top-0 z-10">
                    <h2 class="text-xl md:text-2xl font-extrabold text-[#0A2540]">Galeri Foto</h2>
                    <button @click="closeGalleryModal" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 md:bg-white/50 hover:bg-gray-200 md:hover:bg-white shadow-sm transition-colors">
                        <i class="fa-solid fa-xmark text-xl text-[#0A2540]"></i>
                    </button>
                </div>

                <!-- Tab Filter Kategori -->
                <div class="px-4 sm:px-8 py-4 border-b border-gray-200/50 bg-transparent flex gap-3 overflow-x-auto no-scrollbar">
                    <button
                        v-for="cat in galleryCategories"
                        :key="cat.name"
                        @click="activeCategory = cat.name"
                        class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all backdrop-blur-sm flex items-center gap-2 border border-transparent"
                        :class="activeCategory === cat.name
                            ? 'bg-primary text-white shadow-md'
                            : 'bg-gray-100 md:bg-white/60 text-[#0A2540] hover:bg-gray-200 md:hover:bg-white border-gray-200 shadow-sm'">
                        {{ cat.name }}
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-extrabold flex items-center justify-center min-w-[24px]"
                            :class="activeCategory === cat.name ? 'bg-white text-primary' : 'bg-white text-gray-500 border border-gray-200'">
                            {{ cat.count }}
                        </span>
                    </button>
                </div>

                <!-- Grid Gambar berdasarkan Kategori (Continuous Scroll) -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-8">
                    <div class="max-w-6xl mx-auto space-y-12 sm:space-y-24">

                        <div v-for="cat in chunkedImagesByCategory" :key="cat.name" class="flex flex-col md:flex-row gap-6 md:gap-8">
                            <!-- Category Title (Left on desktop, Top on mobile) -->
                            <div class="w-full md:w-1/3 md:sticky top-0 h-fit pt-2 md:pt-4">
                                <h3 class="text-2xl font-bold text-[#0A2540]">{{ cat.name }}</h3>
                            </div>

                            <!-- Photos for this category -->
                            <div class="w-full md:w-2/3 flex flex-col gap-2 sm:gap-4">
                                <template v-for="(chunk, idx) in cat.chunks" :key="idx">
                                    <!-- Full width image -->
                                    <div v-if="chunk.length === 1" class="w-full aspect-video bg-gray-200 overflow-hidden relative group">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 z-0">
                                            <i class="fa-solid fa-image text-4xl mb-2"></i>
                                        </div>
                                        <img :src="chunk[0].url" class="w-full h-full object-cover relative z-10 transition-transform duration-500 group-hover:scale-105" @error="$event.target.style.display='none'" loading="lazy" />
                                    </div>

                                    <!-- Half width images pair -->
                                    <div v-else-if="chunk.length === 2" class="flex gap-2 sm:gap-4">
                                        <div class="w-1/2 aspect-[4/3] bg-gray-200 overflow-hidden relative group">
                                            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 z-0">
                                                <i class="fa-solid fa-image text-3xl mb-2"></i>
                                            </div>
                                            <img :src="chunk[0].url" class="w-full h-full object-cover relative z-10 transition-transform duration-500 group-hover:scale-105" @error="$event.target.style.display='none'" loading="lazy" />
                                        </div>
                                        <div class="w-1/2 aspect-[4/3] bg-gray-200 overflow-hidden relative group">
                                            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 z-0">
                                                <i class="fa-solid fa-image text-3xl mb-2"></i>
                                            </div>
                                            <img :src="chunk[1].url" class="w-full h-full object-cover relative z-10 transition-transform duration-500 group-hover:scale-105" @error="$event.target.style.display='none'" loading="lazy" />
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </transition>
        <!-- ============================================================ -->

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

            <!-- Tampilkan Semua Foto Mobile -->
            <button v-if="hasImages" @click.stop="showGalleryModal = true" class="absolute bottom-4 left-4 bg-white/90 text-[#0A2540] hover:bg-white text-xs font-bold px-4 py-2 rounded-full z-10 shadow-md backdrop-blur-sm flex items-center gap-2">
                <i class="fa-solid fa-images"></i>
                Lihat Semua Foto
            </button>

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
.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.5s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}
</style>

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
