<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AssetCardSkeleton from './AssetCardSkeleton.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    asset: {
        type: Object,
        required: true
    },
    categoryName: {
        type: String,
        required: true
    }
});

const isIntersecting = ref(false);
const imageLoaded = ref(false);
const elRef = ref(null);

let observer = null;

onMounted(() => {
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            isIntersecting.value = true;
            if (observer) {
                observer.disconnect();
            }
        }
    }, {
        // Render 200px sebelum masuk viewport agar perpindahan halus
        rootMargin: '200px'
    });

    if (elRef.value) {
        observer.observe(elRef.value);
    }
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
        observer = null;
    }
});

const toggleFavorite = () => {

    if (props.asset.isFavorite) {
        router.delete(
            route('favorites.destroy', props.asset.favorite_id),
            {
                preserveScroll: true
            }
        );

        return;
    }


    router.post(
        route('favorites.store'),
        {
            asset_id: props.asset.id
        },
        {
            preserveScroll: true
        }
    );
};

const formatRupiah = (value) => {
    if (!value) return 'Hubungi Pemilik';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const periodLabel = {
    hour: 'jam', day: 'hari',
    week: 'minggu', month: 'bulan', year: 'tahun'
};
</script>

<template>
    <!-- Container Kartu: Desain kotak putih, rounded-2xl, shadow -->
    <div ref="elRef" class="flex-none w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px] snap-start flex flex-col bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">

        <!-- Tampilkan skeleton card jika belum mendekati layar -->
        <AssetCardSkeleton v-if="!isIntersecting" />

        <!-- Render konten kartu sebenarnya saat discroll mendekati layar -->
        <Link v-else :href="route('assets.show', asset.id)" class="w-full flex flex-col group cursor-pointer h-full relative">

            <!-- AREA GAMBAR (Rasio 3:2 Lanskap) -->
            <div class="aspect-[3/2] w-full relative bg-gray-100 overflow-hidden">

                <!-- SKELETON INDIVIDUAL GAMBAR (Selama file gambar di-download) -->
                <div v-if="asset.first_image?.image && !imageLoaded && !asset.imageError"
                     class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 animate-pulse z-10">
                    <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                </div>

                <!-- GAMBAR NYATA -->
                <img
                    v-if="asset.first_image?.image_url && !asset.imageError"
                    :src="asset.first_image.image_url"
                    :alt="asset.title"
                    @load="imageLoaded = true"
                    @error="asset.imageError = true"
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    :class="imageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                />

                <!-- Fallback No Image -->
                <div
                    v-else
                    class="w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-300"
                >
                    <i class="fa-solid fa-image text-3xl mb-1"></i>
                    <span class="text-[10px] font-medium">No Image</span>
                </div>

                <!-- Gradient atas & bawah untuk visibilitas elemen tumpang tindih -->
                <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-black/40 to-transparent pointer-events-none z-10"></div>
                <div class="absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-black/30 to-transparent pointer-events-none z-10"></div>

                <!-- Label Kategori (Sudut kiri atas menempel) -->
                <div class="absolute top-0 left-0 z-20 bg-[#0A2540] text-white text-[10px] sm:text-[11px] font-bold px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-br-lg uppercase tracking-wider">
                    {{ categoryName }}
                </div>

                <!-- Tombol Favorit (Meniru ikon bookmark) -->
                <button
                    @click.stop.prevent="toggleFavorite"
                    class="absolute top-2.5 right-2.5 z-20 text-white drop-shadow-md hover:scale-110 transition-transform flex items-center justify-center"
                >
                    <i
                        :class="asset.isFavorite ? 'fa-solid fa-heart text-red-500' : 'fa-regular fa-heart'"
                        class="text-lg sm:text-xl drop-shadow"
                    ></i>
                </button>

                <!-- Rating Bulat (Sudut kanan bawah dalam gambar agar rapi) -->
                <div v-if="asset.reviews_avg_rating" class="absolute bottom-2 right-2 z-20 bg-[#FFC000] size-7 sm:size-8 rounded-full text-[10px] sm:text-[11px] font-bold text-white flex items-center justify-center shadow-md">
                    {{ Number(asset.reviews_avg_rating).toFixed(1) }}
                </div>
            </div>

            <!-- AREA TEKS (Padding disesuaikan) -->
            <div class="flex flex-col flex-grow p-3 sm:p-4 gap-1.5 sm:gap-2 bg-white">

                <!-- Judul -->
                <h3 class="font-semibold text-sm sm:text-[15px] leading-tight text-[#0A2540] group-hover:text-[#FFC000] transition-colors line-clamp-1">
                    {{ asset.title }}
                </h3>

                <!-- Sub-info (Lokasi) -->
                <div class="text-[11px] sm:text-xs text-gray-500 font-medium flex items-center gap-1.5 truncate">
                    <i class="fa-solid fa-location-dot text-[12px] sm:text-[13px] text-[#FFC000] flex-shrink-0"></i>
                    <span class="truncate">
                        {{ [asset.city, asset.address].filter(Boolean).join(', ') || 'Lokasi tidak diketahui' }}
                    </span>
                </div>

                <!-- Harga (Oranye) -->
                <div class="font-bold text-sm sm:text-base text-[#F97316] mt-auto pt-1">
                    <template v-if="asset.primary_pricing">
                        {{ formatRupiah(asset.primary_pricing.price) }}
                        <span class="text-[10px] font-normal text-gray-400">
                            /{{ periodLabel[asset.primary_pricing.period] || asset.primary_pricing.period }}
                        </span>
                    </template>
                    <span v-else class="text-[11px] font-medium text-gray-400">Hubungi Pemilik</span>
                </div>

            </div>
        </Link>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.animate-shimmer {
    animation: shimmer 1.5s infinite;
}
</style>
