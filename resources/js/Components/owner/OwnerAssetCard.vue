<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AssetCardSkeleton from '@/Components/UI/AssetCardSkeleton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    asset: { type: Object, required: true },
    categoryName: { type: String, required: true },
});

const rentalUnitLabel = (unit) => {
    const labels = { hour: "jam", day: "hari", night: "malam", month: "bulan" };
    return labels[unit] ?? "sewa";
};

const imgList = computed(() => props.asset.thumbnail_images || props.asset.images || []);
const img1 = computed(() => imgList.value[0]?.image_url || props.asset.first_image?.image_url || props.asset.image || props.asset.thumbnail);

const isDesktop = typeof window !== 'undefined' && window.innerWidth >= 1024;
const isIntersecting = ref(isDesktop);
const imageLoaded = ref(false);
const elRef = ref(null);
let observer = null;

onMounted(() => {
    if (isDesktop) return;
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            isIntersecting.value = true;
            observer?.disconnect();
            observer = null;
        }
    }, { rootMargin: '300px' });
    if (elRef.value) observer.observe(elRef.value);
});

onUnmounted(() => {
    observer?.disconnect();
    observer = null;
});

const navigateToAsset = () => {
    router.visit(route('owner.asset.show', props.asset.slug || props.asset.id));
};

const formatRupiah = (value) => {
    if (!value) return '';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <div
        ref="elRef"
        class="flex-none w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px] snap-start flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300 overflow-hidden"
    >
        <AssetCardSkeleton v-if="!isIntersecting" />

        <div v-else class="w-full h-full flex flex-col group cursor-pointer" @click="navigateToAsset">
            <div class="aspect-[3/2] w-full relative bg-gray-100 overflow-hidden">
                <div v-if="asset.first_image?.image && !imageLoaded && !asset.imageError" class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 animate-pulse z-10">
                    <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                </div>

                <div v-if="!img1 || asset.imageError" class="absolute inset-0 w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-300 z-0">
                    <i class="fa-solid fa-image text-3xl mb-1"></i>
                    <span class="text-[10px] font-medium">No Image</span>
                </div>
                <div v-else class="absolute inset-0 w-full h-full z-0">
                    <img :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :class="imageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'" loading="lazy" decoding="async" />
                </div>

                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/20 to-transparent pointer-events-none z-10"></div>

                <div class="absolute top-2 left-2 z-20 pointer-events-none flex flex-col gap-1 items-start">
                    <span v-if="asset.verification_status === 'pending'" class="bg-amber-500/90 text-white text-[9px] sm:text-[10px] font-bold px-2 py-1 rounded-md shadow-sm backdrop-blur-sm flex items-center gap-1">
                        <i class="fa-solid fa-clock"></i> Menunggu
                    </span>
                    <span v-else-if="asset.verification_status === 'approved'" class="bg-emerald-500/90 text-white text-[9px] sm:text-[10px] font-bold px-2 py-1 rounded-md shadow-sm backdrop-blur-sm flex items-center gap-1">
                        <i class="fa-solid fa-check-circle"></i> Terverifikasi
                    </span>
                    <span v-else-if="asset.verification_status === 'rejected'" class="bg-rose-500/90 text-white text-[9px] sm:text-[10px] font-bold px-2 py-1 rounded-md shadow-sm backdrop-blur-sm flex items-center gap-1">
                        <i class="fa-solid fa-circle-xmark"></i> Ditolak
                    </span>
                    <span v-else-if="asset.verification_status === 'inactive'" class="bg-slate-500/90 text-white text-[9px] sm:text-[10px] font-bold px-2 py-1 rounded-md shadow-sm backdrop-blur-sm flex items-center gap-1">
                        <i class="fa-solid fa-eye-slash"></i> Nonaktif
                    </span>
                </div>

                <div v-if="asset.reviews_avg_rating" class="absolute bottom-2 right-2 z-20 bg-[#FFC000] size-7 sm:size-8 rounded-full text-[10px] sm:text-[11px] font-bold text-white flex items-center justify-center shadow-md pointer-events-none">
                    {{ Number(asset.reviews_avg_rating).toFixed(1) }}
                </div>
            </div>

            <div class="flex flex-col flex-grow p-2.5 sm:p-3 gap-1 bg-white">
                <h3 class="font-semibold text-sm sm:text-[15px] leading-tight text-[#0A2540] group-hover:text-blue-600 transition-colors line-clamp-1">
                    {{ asset.title }}
                </h3>
                
                <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-gray-500 font-medium truncate mt-0.5">
                    <span class="truncate">{{ asset.type || categoryName }} &bull; {{ (asset.city?.name || asset.city) || 'Lokasi tidak diketahui' }}</span>
                </div>

                <div class="flex items-center gap-2 text-[10px] mt-1 font-semibold text-slate-500">
                    <span class="font-bold text-slate-700">{{ asset.total_units || 1 }} Unit</span>
                    <span class="text-emerald-500"><i class="fa-solid fa-circle text-[6px] align-middle"></i> {{ asset.occupied_units || 0 }} Terisi</span>
                    <span class="text-slate-300"><i class="fa-solid fa-circle text-[6px] align-middle"></i> {{ asset.available_units || 0 }} Tersedia</span>
                </div>

                <div class="flex items-end justify-between mt-auto pt-2 border-t border-slate-50">
                    <div class="font-bold text-sm text-[#F97316]">
                        <template v-if="asset.default_pricing || asset.price">
                            {{ formatRupiah(asset.default_pricing?.price || asset.price) }}<span class="text-[9px] font-normal text-gray-400">/{{ rentalUnitLabel(asset.type?.rental_unit || asset.rent_period) }}</span>
                        </template>
                        <span v-else class="text-[11px] font-medium text-gray-400">Hubungi</span>
                    </div>
                    
                    <div class="text-[10px] font-bold text-blue-600 group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                        Kelola <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(100%);  }
}
.animate-shimmer { animation: shimmer 1.5s infinite; }
</style>
