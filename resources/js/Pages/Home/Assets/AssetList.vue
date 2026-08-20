<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Layers, ArrowDown } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import AssetCardSkeleton from '@/Components/ui/AssetCardSkeleton.vue';
import LazyAssetCard from '@/Components/ui/LazyAssetCard.vue';
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';

const refreshPage = () => {
    window.location.reload();
};

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
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

import { useLocationPermission } from '@/Composables/useLocationPermission';

onMounted(async () => {
    // Inisialisasi localSections dengan data dari props
    localSections.value = [...props.sections];

    // Cek apakah ada section 'nearby'
    const nearbyIndex = localSections.value.findIndex(s => s.id === 'nearby');
    if (nearbyIndex !== -1) {
        if (navigator.geolocation) {
            const { requestLocationPermission } = useLocationPermission();
            const allowed = await requestLocationPermission();

            if (!allowed) {
                isLocating.value = false;
                localSections.value.splice(nearbyIndex, 1);
                return;
            }

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

// Pagination state for dynamic sections
const currentPage = ref(1);
const hasMoreSections = ref(true);
const isLoadingMore = ref(false);

const loadMoreSections = async () => {
    if (isLoadingMore.value || !hasMoreSections.value) return;

    isLoadingMore.value = true;
    try {
        const response = await axios.get('/api/home/sections', {
            params: {
                page: currentPage.value + 1
            }
        });

        const newSections = response.data;

        if (newSections && newSections.length > 0) {
            localSections.value.push(...newSections);
            currentPage.value++;
            if (newSections.length < 5) {
                hasMoreSections.value = false;
            }
        } else {
            hasMoreSections.value = false;
        }
    } catch (error) {
        console.error("Gagal memuat section lainnya", error);
    } finally {
        isLoadingMore.value = false;
    }
};

const getCategoryImage = (categoryName) => {
    const images = {
        'Hunian': 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'Komersial': 'https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'Lahan': 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'Event': 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'Media Iklan': 'https://images.unsplash.com/photo-1557088194-e8180e0c8b66?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
    };
    return images[categoryName] || 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
};
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
            <!-- Grup Kategori Traveloka Style -->
            <!-- <section v-if="props.categories && props.categories.length > 0" class="px-4 sm:px-6 lg:px-8 mb-12 max-w-5xl mx-auto w-full">
                <div class="flex items-center gap-1.5 sm:gap-2 mb-4">
                    <Layers class="text-[#FFC000] text-sm sm:text-base shrink-0" />
                    <h2 class="text-[15px] sm:text-xl md:text-2xl font-extrabold tracking-tight text-[#0A2540]">Temukan berdasarkan kategori</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                    <Link
                        v-for="cat in props.categories.slice(0, 5)"
                        :key="cat.id"
                        :href="route('assets.search', { category: cat.name })"
                        class="relative rounded-xl overflow-hidden group cursor-pointer aspect-video shadow-sm hover:shadow-lg transition-all duration-300"
                    >

                        <img :src="cat.random_image || getCategoryImage(cat.name)" :alt="cat.name" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

                        <div class="absolute inset-0 bg-black/40 group-hover:bg-black/50 transition-colors duration-300"></div>

                        <div class="absolute inset-0 p-3 sm:p-4 flex flex-col items-center justify-center text-white text-center">
                            <h3 class="font-bold text-sm sm:text-base lg:text-lg drop-shadow-md mb-1">{{ cat.name }}</h3>
                            <p class="text-[10px] sm:text-[11px] font-medium text-gray-200 drop-shadow-md">{{ cat.assets_count || 0 }} aset tersedia</p>
                        </div>
                    </Link>


                    <Link
                        :href="route('assets.search')"
                        class="relative rounded-xl overflow-hidden group cursor-pointer aspect-video shadow-sm hover:shadow-lg transition-all duration-300"
                    >
                        <img src="https://images.unsplash.com/photo-1506059612708-99d6c258160e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Semua Kategori" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-black/40 group-hover:bg-black/50 transition-colors duration-300"></div>
                        <div class="absolute inset-0 p-3 sm:p-4 flex flex-col items-center justify-center text-white text-center">
                            <h3 class="font-bold text-sm sm:text-base lg:text-lg drop-shadow-md mb-1">Semua Kategori</h3>
                            <p class="text-[10px] sm:text-[11px] font-medium text-gray-200 drop-shadow-md">{{ props.categories.reduce((a, b) => a + (b.assets_count || 0), 0) }} aset tersedia</p>
                        </div>
                    </Link>
                </div>
            </section> -->

            <template v-if="visibleSections.length > 0">
                <template v-for="section in visibleSections" :key="section.id">
                    <section
                        class="pl-4 sm:pl-6 lg:pl-8"
                        style="content-visibility: auto; contain-intrinsic-size: 0 320px;"
                    >
                        <!-- Section Header -->
                        <div class="flex justify-between items-center mb-4 pr-4 sm:pr-6 lg:pr-8 gap-2 sm:gap-4">
                            <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                                <AppIcon :iconClass="[section.icon, 'text-[#FFC000] text-sm sm:text-base shrink-0']" v-if="section.icon" />
                                <h2 class="text-[15px] sm:text-xl md:text-2xl font-extrabold tracking-tight truncate" :title="section.title">{{ section.title }}</h2>
                            </div>
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

                <!-- Skeleton Loading More -->
                <template v-if="isLoadingMore">
                    <section
                        v-for="s in skeletonSections"
                        :key="'load-more-sk-' + s"
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

                <!-- Load More Button -->
                <div v-if="hasMoreSections && !isLoadingMore" class="flex justify-center pt-4 pb-12">
                    <button
                        @click="loadMoreSections"
                        class="group px-8 py-3 rounded border-2 border-[#FFC000] bg-transparent text-[#FFC000] text-sm font-bold hover:bg-[#FFC000] hover:text-[#0A2540] hover:shadow-md transition-all flex items-center gap-3"
                    >
                        <span>Muat Lebih Banyak</span>
                        <ArrowDown class="transition-transform duration-300 group-hover:translate-y-1" />
                    </button>
                </div>
            </template>

            <!-- ========== EMPTY STATE ========== -->
            <div
                v-else
                class="flex flex-col items-center pt-10 pb-32 px-4 w-full text-center"
            >
                <EmptyStateIcon class="w-48 h-48 object-contain" />

                <template v-if="props.emptyStateType === 'no-data'">
                    <h2 class="text-xl font-bold text-[#0A2540] mb-2">Belum ada aset yang tersedia</h2>
                    <p class="text-sm text-[#6C757D] mb-6 max-w-sm">Aset yang kamu cari mungkin sedang disewa. Coba lagi nanti untuk melihat ketersediaannya.</p>
                    <button
                        @click="refreshPage"
                        class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                    >
                        Segarkan
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
