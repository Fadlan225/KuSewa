<script setup>
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';
import AssetCardSkeleton from '@/Components/UI/AssetCardSkeleton.vue';
import LazyAssetCard from '@/Components/UI/LazyAssetCard.vue';

const props = defineProps({
    sections: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    },
    emptyStateType: {
        type: String,
        default: 'no-data', 
        validator: (value) => ['no-data', 'filter', 'search'].includes(value)
    },
    searchKeyword: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['goHome', 'resetFilter', 'clearSearch']);

const localSections = ref([]);
const isLocating = ref(true);

onMounted(() => {
    // Inisialisasi localSections dengan data dari props
    localSections.value = [...props.sections];

    // Cek apakah ada section 'nearby'
    const nearbyIndex = localSections.value.findIndex(s => s.id === 'nearby');
    if (nearbyIndex !== -1) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    localStorage.removeItem('location_denied');
                    try {
                        const response = await axios.get(localSections.value[nearbyIndex].api_url, {
                            params: {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            }
                        });
                        
                        if (response.data && response.data.length > 0) {
                            localSections.value[nearbyIndex].assets = response.data;
                        } else {
                            // Kosongkan / hapus jika tidak ada aset di sekitarnya
                            localSections.value.splice(nearbyIndex, 1);
                        }
                    } catch (error) {
                        console.error("Gagal mengambil aset terdekat", error);
                        localSections.value.splice(nearbyIndex, 1);
                    } finally {
                        isLocating.value = false;
                    }
                },
                (error) => {
                    console.warn("Izin lokasi ditolak atau gagal", error);
                    localStorage.setItem('location_denied', 'true');
                    localSections.value.splice(nearbyIndex, 1);
                    isLocating.value = false;
                }
            );
        } else {
            // Geolocation tidak didukung browser
            localSections.value.splice(nearbyIndex, 1);
            isLocating.value = false;
        }
    } else {
        isLocating.value = false;
    }
});

// Computed: section yang punya aset (atau sedang meload 'nearby')
const visibleSections = computed(() =>
    localSections.value.filter(s => (s.assets && s.assets.length > 0) || (s.id === 'nearby' && isLocating.value))
);

// Skeleton sections
const skeletonSections = 3;
const skeletonCards = 6;
</script>

<template>
    <div class="w-full py-8 sm:py-10 space-y-10 sm:space-y-12 text-[#0A2540] font-sans overflow-x-hidden">

        <!-- ========== SKELETON (saat data belum ada) ========== -->
        <template v-if="props.loading">
            <section
                v-for="s in skeletonSections"
                :key="'sk-' + s"
                class="pl-4 sm:pl-6 lg:pl-8 animate-pulse"
            >
                <div class="flex justify-between items-center mb-4 pr-4 sm:pr-6 lg:pr-8">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-gray-200"></div>
                        <div class="h-6 w-32 rounded-lg bg-gray-200"></div>
                    </div>
                    <div class="h-4 w-16 rounded bg-gray-100"></div>
                </div>
                <div class="flex gap-3 sm:gap-4 pb-6 pt-2 overflow-hidden pr-4 sm:pr-6 lg:pr-8">
                    <AssetCardSkeleton v-for="c in skeletonCards" :key="c" />
                </div>
            </section>
        </template>

        <!-- ========== DATA NYATA ========== -->
        <template v-else>
            <template v-if="visibleSections.length > 0">
                <template v-for="section in visibleSections" :key="section.id">
                    <section
                        class="pl-4 sm:pl-6 lg:pl-8"
                        style="content-visibility: auto; contain-intrinsic-size: 0 320px;"
                    >
                        <!-- Section Header -->
                        <div class="flex justify-between items-end mb-4 pr-4 sm:pr-6 lg:pr-8 gap-4">
                            <div class="flex items-center gap-2 min-w-0">
                                <i v-if="section.icon" :class="[section.icon, 'text-[#FFC000] text-base shrink-0']"></i>
                                <h2 class="text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight truncate" :title="section.title">{{ section.title }}</h2>
                            </div>
                            <a href="#" class="text-xs sm:text-sm font-bold text-[#FFC000] hover:text-[#e6ad00] transition-colors flex items-center gap-1 shrink-0 pb-0.5">
                                <span class="hidden sm:inline">Lihat Semua</span>
                                <span class="sm:hidden">Semua</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>

                        <!-- Jika section nearby sedang meload lokasi -->
                        <div v-if="section.id === 'nearby' && isLocating" class="flex gap-3 sm:gap-4 pb-6 pt-2 overflow-hidden pr-4 sm:pr-6 lg:pr-8 animate-pulse">
                            <AssetCardSkeleton v-for="c in skeletonCards" :key="'nearby-sk-'+c" />
                        </div>
                        
                        <!-- Horizontal Scroll - will-change agar GPU-accelerated -->
                        <div
                            v-else
                            class="flex overflow-x-auto gap-3 sm:gap-4 pb-6 pt-2 snap-x snap-mandatory no-scrollbar pr-4 sm:pr-6 lg:pr-8"
                            style="will-change: scroll-position; -webkit-overflow-scrolling: touch;"
                        >
                            <LazyAssetCard
                                v-for="asset in section.assets"
                                :key="asset.id"
                                :asset="asset"
                                :categoryName="asset.type?.category?.name || 'Aset'"
                            />
                        </div>
                    </section>
                </template>
            </template>

            <!-- ========== EMPTY STATE ========== -->
            <div
                v-else
                class="flex flex-col items-center pt-10 pb-32 px-4 w-full text-center"
            >
                <img
                    src="/empty.svg"
                    class="w-48 h-48 object-contain mb-6"
                    alt="Ilustrasi Kosong"
                >

                <template v-if="props.emptyStateType === 'no-data'">
                    <h2 class="text-xl font-bold text-[#0A2540] mb-2">Belum Ada Aset</h2>
                    <p class="text-sm text-[#6C757D] mb-6">Aset sedang disiapkan.</p>
                    <button
                        @click="$emit('goHome')"
                        class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                    >
                        Ke Beranda
                    </button>
                </template>

                <template v-else-if="props.emptyStateType === 'filter'">
                    <h2 class="text-xl font-bold text-[#0A2540] mb-2">Tidak Ditemukan</h2>
                    <p class="text-sm text-[#6C757D] mb-6">Ubah filter pencarian Anda.</p>
                    <button
                        @click="$emit('resetFilter')"
                        class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                    >
                        Reset Filter
                    </button>
                </template>

                <template v-else-if="props.emptyStateType === 'search'">
                    <h2 class="text-xl font-bold text-[#0A2540] mb-2">Hasil Kosong</h2>
                    <p class="text-sm text-[#6C757D] mb-6">Coba kata kunci lain.</p>
                    <button
                        @click="$emit('clearSearch')"
                        class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                    >
                        Hapus Pencarian
                    </button>
                </template>
            </div>
        </template>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
/* GPU acceleration untuk horizontal scroll container */
[style*="will-change"] { transform: translateZ(0); }
</style>
