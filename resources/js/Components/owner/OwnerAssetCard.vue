<script setup>
import { FileEdit, Clock, XCircle, CheckCircle, Power, Image, ChevronRight } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AssetCardSkeleton from '@/Components/ui/AssetCardSkeleton.vue';
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
    if (props.asset.verification_status === 'draft') {
        router.visit(route('owner.asset.edit-draft', props.asset.id));
    } else {
        router.visit(route('owner.asset.show', props.asset.slug || props.asset.id));
    }
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
        class="flex-none w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px] snap-start flex flex-col bg-white rounded-md shadow-sm border border-slate-200/60 hover:border-[#FFC000] hover:shadow-md transition-all duration-300"
    >
        <AssetCardSkeleton v-if="!isIntersecting" />

        <div v-else class="w-full h-full flex flex-col group cursor-pointer relative" @click="navigateToAsset">
            <!-- RIBBON BADGE (Placed outside overflow-hidden to allow overhang) -->
            <div class="absolute top-3 -left-1.5 z-20 pointer-events-none flex flex-col gap-1 items-start">
                <!-- Status Verification Badge -->
                <div v-if="asset.verification_status === 'draft'" class="relative bg-slate-200 text-slate-700 text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <FileEdit class="mr-1" />Draft
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-slate-400 border-l-[6px] border-l-transparent"></div>
                </div>
                <div v-else-if="asset.verification_status === 'pending'" class="relative bg-[#FFC000] text-[#0A2540] text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <Clock class="mr-1" />Menunggu
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-[#B38600] border-l-[6px] border-l-transparent"></div>
                </div>
                <div v-else-if="asset.verification_status === 'rejected'" class="relative bg-rose-600 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <XCircle class="mr-1" />Ditolak
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-rose-800 border-l-[6px] border-l-transparent"></div>
                </div>
                <div v-else-if="asset.verification_status === 'approved'" class="relative bg-[#FFC000] text-[#0A2540] text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <CheckCircle class="mr-1" />Terverifikasi
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-[#B38600] border-l-[6px] border-l-transparent"></div>
                </div>
                <div v-else-if="asset.verification_status === 'inactive'" class="relative bg-slate-500 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <Power class="mr-1" />Nonaktif
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-slate-700 border-l-[6px] border-l-transparent"></div>
                </div>
            </div>

            <div class="aspect-[3/2] w-full relative bg-gray-100 overflow-hidden rounded-t-md">
                <div v-if="asset.first_image?.image && !imageLoaded && !asset.imageError" class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 animate-pulse z-10">
                    <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                </div>

                <div v-if="!img1 || asset.imageError" class="absolute inset-0 w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-300 z-0">
                    <Image class="text-3xl mb-1" />
                    <span class="text-[10px] font-medium">No Image</span>
                </div>
                <div v-else class="absolute inset-0 w-full h-full z-0">
                    <img :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :class="imageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'" loading="lazy" decoding="async" />
                </div>

                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/20 to-transparent pointer-events-none z-10"></div>

                <div v-if="asset.reviews_avg_rating" class="absolute bottom-2 right-2 z-20 bg-[#FFC000] size-7 sm:size-8 rounded-full text-[10px] sm:text-[11px] font-bold text-[#0A2540] flex items-center justify-center shadow-md pointer-events-none">
                    {{ Number(asset.reviews_avg_rating).toFixed(1) }}
                </div>
            </div>

            <div class="flex flex-col flex-grow p-2.5 sm:p-3 bg-white rounded-b-md">
                <!-- Location & Type (Top Context) -->
                <div class="text-[9px] sm:text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate mb-0.5">
                    {{ asset.type || categoryName }} &bull; {{ (asset.city?.name || asset.city) || 'Lokasi tidak diketahui' }}
                </div>

                <!-- Title (Primary Anchor) -->
                <h3 class="font-bold text-sm sm:text-[15px] leading-tight text-[#0A2540] line-clamp-1 mb-1">
                    {{ asset.title }}
                </h3>
                
                <!-- Unit Stats (Secondary Context - Subdued) -->
                <div class="flex items-center gap-1.5 text-[9px] sm:text-[10px] text-slate-500 mb-1">
                    <span><span class="font-bold text-slate-700">{{ asset.total_units || 1 }}</span> Unit</span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span><span class="font-bold text-slate-700">{{ asset.occupied_units || 0 }}</span> Terisi</span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span><span class="font-bold text-slate-700">{{ asset.available_units || 0 }}</span> Sisa</span>
                </div>

                <!-- Price (Bottom Anchor) -->
                <div class="flex items-end justify-between mt-auto pt-3 border-t border-slate-100">
                    <div>
                        <span class="block text-[8px] sm:text-[9px] text-slate-400 font-medium mb-0.5">Mulai dari</span>
                        <div class="font-black text-[13px] sm:text-[15px] text-[#0A2540] leading-none">
                            <template v-if="asset.default_pricing || asset.price">
                                {{ formatRupiah(asset.default_pricing?.price || asset.price) }}<span class="text-[10px] sm:text-[11px] font-bold text-slate-500 ml-0.5">/{{ rentalUnitLabel(asset.default_pricing?.rental_unit || asset.type?.rental_unit || asset.rent_period) }}</span>
                            </template>
                            <span v-else class="text-[11px] font-medium text-slate-400">Hubungi</span>
                        </div>
                    </div>
                    
                    <div class="text-slate-300 group-hover:text-[#FFC000] transition-colors flex items-center justify-center pb-0.5 pr-0.5">
                        <ChevronRight class="text-xs" />
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
