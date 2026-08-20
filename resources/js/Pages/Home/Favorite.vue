<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Search, Check, ChevronDown, ChevronLeft } from 'lucide-vue-next';
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3';
import HorizontalAssetCard from '@/Components/ui/HorizontalAssetCard.vue'
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';

const props = defineProps({
    isComponent: { type: Boolean, default: false },
    initialFavorites: {
        type: Array,
        default: () => []
    },
    categoriesList: {
        type: Array,
        default: () => ['Semua']
    }
})

const favorites = ref(props.initialFavorites)
const categories = ref(props.categoriesList)

const search = ref('')
const sort = ref('Terbaru')
const selectedCategory = ref('Semua')

const isSortOpenDesktop = ref(false)
const isSortOpenMobile = ref(false)

const sortOptions = [
    { label: 'Terbaru', icon: 'fa-solid fa-clock-rotate-left' },
    { label: 'Harga Terendah', icon: 'fa-solid fa-arrow-down-short-wide' },
    { label: 'Harga Tertinggi', icon: 'fa-solid fa-arrow-up-wide-short' },
    { label: 'Rating Terendah', icon: 'fa-solid fa-star-half-stroke' },
    { label: 'Rating Tertinggi', icon: 'fa-solid fa-star' },
]

const selectSort = (val) => {
    sort.value = val
    isSortOpenDesktop.value = false
    isSortOpenMobile.value = false
}

const getCategoryName = (item) => {
    return item.type?.category?.name || item.category?.name || 'Lainnya'
}

const getCategoryCount = (categoryName) => {
    if (categoryName === 'Semua') return favorites.value.length
    return favorites.value.filter(item => getCategoryName(item) === categoryName).length
}

const filteredFavorites = computed(() => {
    let data = favorites.value

    if (selectedCategory.value !== 'Semua') {
        data = data.filter(item => getCategoryName(item) === selectedCategory.value)
    }

    if (search.value) {
        data = data.filter(item =>
            item.title.toLowerCase().includes(search.value.toLowerCase())
        )
    }

    switch (sort.value) {
        case 'Harga Terendah':
            data = [...data].sort((a, b) => (a.default_pricing?.price || 0) - (b.default_pricing?.price || 0))
            break

        case 'Harga Tertinggi':
            data = [...data].sort((a, b) => (b.default_pricing?.price || 0) - (a.default_pricing?.price || 0))
            break

        case 'Rating Terendah':
            data = [...data].sort((a, b) => (a.reviews_avg_rating || 0) - (b.reviews_avg_rating || 0))
            break

        case 'Rating Tertinggi':
            data = [...data].sort((a, b) => (b.reviews_avg_rating || 0) - (a.reviews_avg_rating || 0))
            break
    }

    return data
})
</script>

<template>
    <component :is="isComponent ? 'div' : AppLayout" :hideNavbar="!isComponent" class="w-full">
        <Head title="Favorit" />
        <div :class="isComponent ? '' : 'bg-[#F8F9FA] min-h-screen pb-24 sm:pb-16'">
            <!-- Custom Top Navbar -->
            <div v-if="!isComponent" class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">
                <button @click="router.get(route('aktivitas.hub'))" class="p-2 -ml-2 rounded-full hover:bg-slate-50 transition-colors">
                    <ChevronLeft class="w-6 h-6 text-[#1D1D1F]" />
                </button>
                <h1 class="text-base font-bold text-[#1D1D1F]">Favorit</h1>
                <div class="w-10"></div>
            </div>

            <div :class="isComponent ? 'text-[#1D1D1F]' : 'max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 text-[#1D1D1F]'">

            <!-- DESKTOP COMPONENT HEADER -->
            <div v-if="isComponent" class="flex justify-between items-center mb-4 mt-2">
                <h2 class="text-xl font-bold text-[#1D1D1F]">Favorit</h2>
                
                <div class="relative">
                    <button
                        @click="isSortOpenMobile = !isSortOpenMobile"
                        class="flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs font-medium text-[#1D1D1F] transition-colors shadow-xs"
                    >
                        <i :class="sortOptions.find(o => o.label === sort)?.icon || 'fa-solid fa-clock-rotate-left'" class="text-slate-500 text-[10px]"></i>
                        {{ sort }}
                        <ChevronDown class="text-slate-400 text-[9px] ml-1 transition-transform" :class="isSortOpenMobile ? 'rotate-180' : ''" />
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div v-if="isSortOpenMobile" class="absolute z-50 right-0 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden">
                            <div class="py-1">
                                <button
                                    v-for="option in sortOptions"
                                    :key="option.label"
                                    @click="selectSort(option.label)"
                                    class="w-full flex items-center gap-2 px-3 py-2.5 text-xs text-left"
                                    :class="sort === option.label ? 'bg-amber-50 text-[#0A2540] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#1D1D1F]'"
                                >
                                    <AppIcon :iconClass="[option.icon, sort === option.label ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center" />
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- SEARCH & KATEGORI -->
            <div class="mb-5 space-y-2.5" :class="isComponent ? '' : 'block lg:hidden'">
                <!-- Horizontal Scroll Category Chips (Mobile) -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
                    <button
                        v-for="item in categories"
                        :key="item"
                        @click="selectedCategory = item"
                        class="px-3.5 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all duration-200 flex-shrink-0 flex items-center gap-1.5"
                        :class="selectedCategory === item
                            ? 'bg-[#FFC000] text-[#0A2540] shadow-xs font-semibold'
                            : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    >
                        {{ item }}
                        <span
                            class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-bold px-1"
                            :class="selectedCategory === item ? 'bg-white text-[#0A2540]' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ getCategoryCount(item) }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- BODY CONTENT GRID -->
            <div class="grid grid-cols-12 gap-5 lg:gap-8">

                <!-- CONTENT LIST -->
                <section class="col-span-12">

                    <div class="flex justify-between items-center mb-3 sm:mb-5 px-1">
                        <div v-if="!isComponent" class="flex items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
                            <div>
                                <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1F]">
                                    {{ selectedCategory === 'Semua' ? 'Semua Aset' : 'Aset ' + selectedCategory }}
                                </h2>
                                <p class="text-slate-400 text-xs mt-0.5">
                                    Menampilkan total {{ getCategoryCount(selectedCategory) }} aset
                                </p>
                            </div>

                            <div class="block lg:hidden relative ml-auto">
                                <button
                                    @click="isSortOpenMobile = !isSortOpenMobile"
                                class="flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs font-medium text-[#1D1D1F] transition-colors shadow-xs"
                            >
                                <i :class="sortOptions.find(o => o.label === sort)?.icon || 'fa-solid fa-clock-rotate-left'" class="text-slate-500 text-[10px]"></i>
                                {{ sort }}
                                <ChevronDown class="text-slate-400 text-[9px] ml-1 transition-transform" :class="isSortOpenMobile ? 'rotate-180' : ''" />
                            </button>

                            <!-- Dropdown Menu -->
                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <div v-if="isSortOpenMobile" class="absolute z-50 right-0 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden border border-slate-100">
                                    <div class="py-1">
                                        <button
                                            v-for="option in sortOptions"
                                            :key="option.label"
                                            @click="selectSort(option.label)"
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-xs text-left"
                                            :class="sort === option.label ? 'bg-amber-50 text-[#0A2540] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#1D1D1F]'"
                                        >
                                            <AppIcon :iconClass="[option.icon, sort === option.label ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center text-[10px]" />
                                            {{ option.label }}
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>

                    <!-- EMPTY STATES -->
                    <div
                        v-if="filteredFavorites.length === 0"
                        class="bg-white rounded-2xl sm:rounded-[1.5rem] border border-slate-200/60 py-12 sm:py-16 px-4 text-center shadow-xs flex flex-col items-center justify-center"
                    >
                        <EmptyStateIcon class="w-48 h-48 object-contain mb-6" />

                        <template v-if="props.initialFavorites.length === 0 || filteredFavorites.length === 0">
                            <h2 class="text-xl font-bold text-[#0A2540] mb-2">Tidak Ditemukan</h2>
                            <p class="text-sm text-[#6C757D] mb-6">Ubah filter pencarian Anda.</p>
                            <button
                                @click="selectedCategory = 'Semua'"
                                class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                            >
                                Reset Kategori
                            </button>
                        </template>
                    </div>

                    <!-- Card List -->
                    <div v-else class="grid grid-cols-1 sm:grid-cols-1 gap-4 sm:space-y-4 sm:gap-0">
                        <HorizontalAssetCard
                            v-for="asset in filteredFavorites"
                            :key="asset.id"
                            :asset="asset"
                            :categoryName="getCategoryName(asset)"
                        />
                    </div>

                </section>

            </div>

            </div>
        </div>
    </component>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.15);
    border-radius: 999px;
}
::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.3);
}
</style>



