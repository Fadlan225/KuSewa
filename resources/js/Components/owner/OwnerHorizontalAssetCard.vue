<script setup>
import { FileEdit, Clock, XCircle, CheckCircle, Power, Image, ChevronRight } from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    asset: { type: Object, required: true },
    categoryName: { type: String, required: true },
});

const isDesktop = typeof window !== 'undefined' && window.innerWidth >= 1024;
const isIntersecting = ref(isDesktop);
const imageLoaded = ref(false);
const elRef = ref(null);
let observer = null;

const imgList = computed(() => props.asset.thumbnail_images || props.asset.images || []);
const img1 = computed(() => imgList.value[0]?.image_url || props.asset.first_image?.image_url || props.asset.image || props.asset.thumbnail);

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

const rentalUnitLabel = (unit) => {
    const labels = { hour: "jam", day: "hari", night: "malam", month: "bulan" };
    return labels[unit] ?? "sewa";
};

const availabilityText = computed(() => {
    if (props.asset.available_at) {
        return `Tersedia ${props.asset.available_at}`;
    }
    return "Tersedia Sekarang";
});
</script>

<template>
    <div
        ref="elRef"
        class="bg-white rounded-md shadow-sm border border-slate-200/60 hover:shadow-md hover:border-[#FFC000] transition-all flex flex-row overflow-hidden group p-2.5 md:p-3 items-center gap-3 md:gap-4 select-none [-webkit-touch-callout:none] w-full cursor-pointer"
        @click="navigateToAsset"
    >
        <div v-if="!isIntersecting" class="flex flex-row w-full animate-pulse items-center gap-3 md:gap-4">
            <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-200 rounded-lg shrink-0"></div>
            <div class="flex-1 space-y-2">
                <div class="w-16 h-3 bg-slate-200 rounded"></div>
                <div class="w-3/4 h-4 bg-slate-200 rounded"></div>
                <div class="w-1/2 h-3 bg-slate-200 rounded"></div>
            </div>
            <div class="shrink-0 flex flex-col items-end gap-2">
                <div class="w-16 h-4 bg-slate-200 rounded"></div>
                <div class="w-6 h-6 bg-slate-200 rounded-full mt-2"></div>
            </div>
        </div>

        <template v-else>
            <div class="relative shrink-0">
                <!-- Ribbon Badge Status -->
                <div class="absolute top-1 -left-1.5 z-20 pointer-events-none flex flex-col gap-1 items-start">
                    <div v-if="asset.verification_status === 'draft'" class="relative bg-slate-200 text-slate-700 text-[8px] md:text-[9px] font-black px-1.5 py-0.5 rounded-r-md shadow-sm">
                        <FileEdit class="mr-1" />Draft
                        <div class="absolute left-0 -bottom-1 w-0 h-0 border-t-[4px] border-t-slate-400 border-l-[4px] border-l-transparent"></div>
                    </div>
                    <div v-else-if="asset.verification_status === 'pending'" class="relative bg-[#FFC000] text-[#0A2540] text-[8px] md:text-[9px] font-black px-1.5 py-0.5 rounded-r-md shadow-sm">
                        <Clock class="mr-1" />Menunggu
                        <div class="absolute left-0 -bottom-1 w-0 h-0 border-t-[4px] border-t-[#B38600] border-l-[4px] border-l-transparent"></div>
                    </div>
                    <div v-else-if="asset.verification_status === 'rejected'" class="relative bg-rose-600 text-white text-[8px] md:text-[9px] font-black px-1.5 py-0.5 rounded-r-md shadow-sm">
                        <XCircle class="mr-1" />Ditolak
                        <div class="absolute left-0 -bottom-1 w-0 h-0 border-t-[4px] border-t-rose-800 border-l-[4px] border-l-transparent"></div>
                    </div>
                    <div v-else-if="asset.verification_status === 'approved'" class="relative bg-[#FFC000] text-[#0A2540] text-[8px] md:text-[9px] font-black px-1.5 py-0.5 rounded-r-md shadow-sm">
                        <CheckCircle class="mr-1" />Terverifikasi
                        <div class="absolute left-0 -bottom-1 w-0 h-0 border-t-[4px] border-t-[#B38600] border-l-[4px] border-l-transparent"></div>
                    </div>
                    <div v-else-if="asset.verification_status === 'inactive'" class="relative bg-slate-500 text-white text-[8px] md:text-[9px] font-black px-1.5 py-0.5 rounded-r-md shadow-sm">
                        <Power class="mr-1" />Nonaktif
                        <div class="absolute left-0 -bottom-1 w-0 h-0 border-t-[4px] border-t-slate-700 border-l-[4px] border-l-transparent"></div>
                    </div>
                </div>

                <div class="w-16 h-16 md:w-20 md:h-20 shrink-0 relative rounded-md overflow-hidden bg-slate-100">
                    <div v-if="!img1 || asset.imageError" class="absolute inset-0 flex items-center justify-center bg-slate-100 text-slate-300">
                        <Image class="text-xl" />
                    </div>
                    <img v-else :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 pointer-events-none" loading="lazy" />
                    
                    <div class="absolute inset-x-0 bottom-0 h-6 bg-gradient-to-t from-black/20 to-transparent pointer-events-none z-10"></div>
                </div>

                <!-- Rating Badge -->
                <div v-if="asset.reviews_avg_rating" class="absolute bottom-1 right-1 z-20 bg-[#FFC000] size-5 md:size-6 rounded-full text-[8px] md:text-[9px] font-bold text-[#0A2540] flex items-center justify-center shadow-md pointer-events-none">
                    {{ Number(asset.reviews_avg_rating).toFixed(1) }}
                </div>
            </div>

            <div class="flex-1 min-w-0 flex flex-col justify-center py-0.5">
                <div class="text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate mb-0.5">
                    {{ asset.type || categoryName }} &bull; {{ (asset.city?.name || asset.city) || 'Lokasi tidak diketahui' }}
                </div>

                <h3 class="font-bold text-sm md:text-base text-[#0A2540] truncate transition-colors mb-1">
                    {{ asset.title }}
                </h3>

                <div class="flex items-center gap-1.5 text-[9px] md:text-[10px] text-slate-500">
                    <span><span class="font-bold text-slate-700">{{ asset.total_units || 1 }}</span> Unit</span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span><span class="font-bold text-slate-700">{{ asset.occupied_units || 0 }}</span> Terisi</span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span><span class="font-bold text-slate-700">{{ asset.available_units || 0 }}</span> Sisa</span>
                </div>
            </div>

            <div class="shrink-0 flex flex-col items-end justify-between self-stretch py-0.5 pl-3 border-l border-slate-100 ml-1 md:ml-2 min-w-[90px]">
                <div class="text-right flex flex-col items-end">
                    <span class="block text-[8px] md:text-[9px] text-slate-400 font-medium mb-0.5">Mulai dari</span>
                    <div class="font-black text-[13px] md:text-[15px] text-[#0A2540] tracking-tight leading-none">
                        <template v-if="asset.cheapest_unit_price">
                            {{ formatRupiah(asset.cheapest_unit_price) }}<span class="text-[10px] md:text-[11px] text-slate-500 font-bold tracking-normal ml-1">/{{ rentalUnitLabel(asset.cheapest_unit_rental_unit || asset.default_pricing?.rental_unit || asset.type?.rental_unit || asset.rent_period) }}</span>
                        </template>
                        <template v-else-if="asset.default_pricing || asset.price">
                            {{ formatRupiah(asset.default_pricing?.price || asset.price) }}<span class="text-[10px] md:text-[11px] text-slate-500 font-bold tracking-normal ml-1">/{{ rentalUnitLabel(asset.default_pricing?.rental_unit || asset.type?.rental_unit || asset.rent_period) }}</span>
                        </template>
                        <template v-else>Hubungi</template>
                    </div>
                </div>

                <div class="mt-auto text-slate-300 group-hover:text-[#FFC000] transition-colors flex items-center justify-center">
                    <ChevronRight class="text-xs md:text-sm" />
                </div>
            </div>
        </template>
    </div>
</template>
