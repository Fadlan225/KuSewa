<script setup>
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
    router.visit(route('owner.asset.show', props.asset.slug || props.asset.id));
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
        class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-row overflow-hidden group p-2.5 md:p-3 items-center gap-3 md:gap-4 select-none [-webkit-touch-callout:none] w-full cursor-pointer"
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
            <div class="w-16 h-16 md:w-20 md:h-20 shrink-0 relative rounded-lg overflow-hidden bg-slate-100">
                <div v-if="!img1 || asset.imageError" class="absolute inset-0 flex items-center justify-center bg-slate-100 text-slate-300">
                    <i class="fa-solid fa-image text-xl"></i>
                </div>
                <img v-else :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 pointer-events-none" loading="lazy" />
            </div>

            <div class="flex-1 min-w-0 flex flex-col justify-center">
                <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                    <span v-if="asset.verification_status === 'pending'" class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[9px] font-bold flex items-center gap-1"><i class="fa-solid fa-clock"></i> Menunggu</span>
                    <span v-else-if="asset.verification_status === 'approved'" class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[9px] font-bold flex items-center gap-1"><i class="fa-solid fa-check-circle"></i> Terverifikasi</span>
                    <span v-else-if="asset.verification_status === 'rejected'" class="px-1.5 py-0.5 bg-rose-100 text-rose-700 rounded text-[9px] font-bold flex items-center gap-1"><i class="fa-solid fa-circle-xmark"></i> Ditolak</span>
                    <span v-else-if="asset.verification_status === 'inactive'" class="px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded text-[9px] font-bold flex items-center gap-1"><i class="fa-solid fa-eye-slash"></i> Nonaktif</span>

                    <div v-if="asset.reviews_avg_rating" class="flex items-center gap-1.5 ml-auto">
                        <div class="flex items-center gap-[1px] text-[8px]">
                            <i v-for="n in 5" :key="n" class="fa-solid fa-star" :class="n <= Math.round(parseFloat(asset.reviews_avg_rating || 0)) ? 'text-[#FFC000]' : 'text-gray-300'"></i>
                        </div>
                        <span class="text-[#0A2540] text-[9px] font-bold">{{ parseFloat(asset.reviews_avg_rating || 0).toFixed(1) }}</span>
                        <span class="text-gray-500 text-[9px]">({{ asset.reviews_count || 0 }} ulasan)</span>
                    </div>
                </div>

                <h3 class="font-bold text-sm md:text-base text-[#0A2540] truncate group-hover:text-blue-600 transition-colors">
                    {{ asset.title }}
                </h3>

                <div class="text-[10px] md:text-[11px] text-gray-500 font-medium truncate mt-0.5">
                    {{ asset.type || categoryName }} &bull; {{ (asset.city?.name || asset.city) || 'Lokasi tidak diketahui' }}
                </div>

                <div class="flex items-center gap-2 text-[10px] mt-1.5 font-semibold text-slate-500">
                    <span class="font-bold text-slate-700">{{ asset.total_units || 1 }} Unit</span>
                    <span class="text-emerald-500"><i class="fa-solid fa-circle text-[6px] align-middle"></i> {{ asset.occupied_units || 0 }} Terisi</span>
                    <span class="text-slate-300"><i class="fa-solid fa-circle text-[6px] align-middle"></i> {{ asset.available_units || 0 }} Tersedia</span>
                </div>
            </div>

            <div class="shrink-0 flex flex-col items-end justify-between self-stretch py-0.5 pl-3 md:pl-4">
                <div class="text-right flex flex-col items-end">
                    <div class="font-black text-sm md:text-base text-[#F97316] tracking-tight leading-none mt-1">
                        <template v-if="asset.cheapest_unit_price">
                            {{ formatRupiah(asset.cheapest_unit_price) }}<span class="text-[10px] text-gray-500 font-semibold tracking-normal">/{{ rentalUnitLabel(asset.type?.rental_unit || asset.rent_period) }}</span>
                        </template>
                        <template v-else-if="asset.default_pricing || asset.price">
                            {{ formatRupiah(asset.default_pricing?.price || asset.price) }}<span class="text-[10px] text-gray-500 font-semibold tracking-normal">/{{ rentalUnitLabel(asset.type?.rental_unit || asset.rent_period) }}</span>
                        </template>
                        <template v-else>Hubungi</template>
                    </div>
                </div>

                <div class="mt-auto text-[10px] md:text-[11px] font-bold text-blue-600 group-hover:translate-x-1 transition-transform flex items-center gap-1">
                    Kelola <i class="fa-solid fa-arrow-right text-[9px] md:text-[10px]"></i>
                </div>
            </div>
        </template>
    </div>
</template>
