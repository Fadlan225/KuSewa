<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import HorizontalAssetCard from '@/Components/UI/HorizontalAssetCard.vue'

const props = defineProps({
    initialFavorites: {
        type: Array,
        default: () => []
    },
    categoriesList: {
        type: Array,
        default: () => ['Semua']
    }
})

// Karena optimistic UI pada HorizontalAssetCard bisa men-toggle favorite secara lokal,
// namun card tetap tampil (meski isFavorite = false), biarkan saja.
// Kalau ingin otomatis hilang saat unfavorite, kita bisa filter yang isFavorite === true.
// Tapi biasanya di halaman Favorit lebih nyaman jika card tidak langsung hilang saat
// batal favorit, sehingga user bisa membatalkannya. Kita ikuti saja pola standar.
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
    return item.category?.name || 'Lainnya'
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
    <AppLayout>
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-12 pb-24 sm:pb-16 text-[#1D1D1F]">

            <!-- MOBILE SEARCH & KATEGORI -->
            <div class="block lg:hidden mb-5 space-y-2.5">
                <!-- Search Input Mobile -->
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari properti favorit..."
                        class="w-full rounded-xl bg-white border border-slate-200 pl-9 pr-3.5 py-2.5 text-xs text-[#1D1D1F] placeholder-slate-400 outline-none focus:ring-2 focus:ring-amber-400/50 shadow-xs"
                    />
                </div>

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

                <!-- SIDEBAR FILTER (Desktop Only) -->
                <aside class="hidden lg:block lg:col-span-3">
                    <div class="bg-white backdrop-blur-xl rounded-[1.5rem] border border-slate-100 p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] sticky top-24 space-y-6">
                        <div>
                            <h2 class="text-base font-semibold text-[#1D1D1F] px-1">Filter & Cari</h2>

                            <!-- Search Field -->
                            <div class="mt-3 relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari properti..."
                                    class="w-full rounded-xl bg-slate-100/80 border-0 pl-9 pr-3.5 py-2 text-xs text-[#1D1D1F] placeholder-slate-400 outline-none focus:ring-2 focus:ring-amber-400/50 transition-all"
                                />
                            </div>
                        </div>

                        <!-- Kategori Filter -->
                        <div>
                            <h3 class="font-medium text-xs text-slate-400 uppercase tracking-wider px-1 mb-2">Kategori</h3>
                            <div class="space-y-1">
                                <button
                                    v-for="item in categories"
                                    :key="item"
                                    @click="selectedCategory = item"
                                    class="w-full rounded-xl px-3 py-2 text-left text-xs font-medium transition-all duration-200 flex items-center justify-between group"
                                    :class="selectedCategory === item
                                        ? 'bg-[#FFC000] text-[#0A2540] shadow-sm'
                                        : 'text-slate-600 hover:bg-slate-100/80'"
                                >
                                    <div class="flex items-center gap-2">
                                        <span>{{ item }}</span>
                                        <span 
                                            class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-bold px-1 transition-colors"
                                            :class="selectedCategory === item ? 'bg-white text-[#0A2540]' : 'bg-slate-200 text-slate-500 group-hover:bg-slate-200/80'"
                                        >
                                            {{ getCategoryCount(item) }}
                                        </span>
                                    </div>
                                    <i v-if="selectedCategory === item" class="fa-solid fa-check text-[10px] text-[#0A2540]"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown Urutkan -->
                        <div>
                            <h3 class="font-medium text-xs text-slate-400 uppercase tracking-wider px-1 mb-2">Urutkan</h3>
                            <div class="relative">
                                <button
                                    @click="isSortOpenDesktop = !isSortOpenDesktop"
                                    class="w-full flex items-center justify-between rounded-xl bg-slate-100/80 hover:bg-slate-200/60 border-0 px-3 py-2 text-xs font-medium text-[#1D1D1F] transition-colors"
                                >
                                    <div class="flex items-center gap-2">
                                        <i :class="sortOptions.find(o => o.label === sort)?.icon || 'fa-solid fa-clock-rotate-left'" class="text-slate-500 w-3 text-center"></i>
                                        {{ sort }}
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform" :class="isSortOpenDesktop ? 'rotate-180' : ''"></i>
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
                                    <div v-if="isSortOpenDesktop" class="absolute z-50 left-0 right-0 mt-2 w-full origin-top-left rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden">
                                        <div class="py-1">
                                            <button
                                                v-for="option in sortOptions"
                                                :key="option.label"
                                                @click="selectSort(option.label)"
                                                class="w-full flex items-center gap-2 px-3 py-2.5 text-xs text-left"
                                                :class="sort === option.label ? 'bg-amber-50 text-[#0A2540] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#1D1D1F]'"
                                            >
                                                <i :class="[option.icon, sort === option.label ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center"></i>
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- CONTENT LIST -->
                <section class="col-span-12 lg:col-span-9">

                    <div class="flex justify-between items-center mb-3 sm:mb-5 px-1">
                        <div>
                            <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1F]">
                                Daftar Favorit
                            </h2>
                            <p class="text-slate-400 text-xs">
                                {{ filteredFavorites.length }} tempat ditemukan
                            </p>
                        </div>

                        <!-- Sort Dropdown untuk Mobile -->
                        <div class="block lg:hidden relative">
                            <button
                                @click="isSortOpenMobile = !isSortOpenMobile"
                                class="flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs font-medium text-[#1D1D1F] transition-colors shadow-xs"
                            >
                                <i :class="sortOptions.find(o => o.label === sort)?.icon || 'fa-solid fa-clock-rotate-left'" class="text-slate-500 text-[10px]"></i>
                                {{ sort }}
                                <i class="fa-solid fa-chevron-down text-slate-400 text-[9px] ml-1 transition-transform" :class="isSortOpenMobile ? 'rotate-180' : ''"></i>
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
                                            <i :class="[option.icon, sort === option.label ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center text-[10px]"></i>
                                            {{ option.label }}
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- EMPTY STATES -->
                    <div
                        v-if="filteredFavorites.length === 0"
                        class="bg-white rounded-2xl sm:rounded-[1.5rem] border border-slate-200/60 py-12 sm:py-16 px-4 text-center shadow-xs flex flex-col items-center justify-center"
                    >
                        <img
                            src="/empty.svg"
                            class="w-48 h-48 object-contain mb-6"
                            alt="Ilustrasi Kosong"
                        >

                        <template v-if="props.initialFavorites.length === 0">
                            <h2 class="text-xl font-bold text-[#0A2540] mb-2">Belum Ada Favorit</h2>
                            <p class="text-sm text-[#6C757D] mb-6">Simpan properti favoritmu agar mudah ditemukan di kemudian hari.</p>
                            <button
                                @click="$inertia.visit(route('Home'))"
                                class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                            >
                                Ke Beranda
                            </button>
                        </template>

                        <template v-else-if="search">
                            <h2 class="text-xl font-bold text-[#0A2540] mb-2">Hasil Kosong</h2>
                            <p class="text-sm text-[#6C757D] mb-6">Coba kata kunci lain.</p>
                            <button
                                @click="search = ''"
                                class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors"
                            >
                                Hapus Pencarian
                            </button>
                        </template>

                        <template v-else>
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
                            :categoryName="asset.category?.name || 'Lainnya'"
                        />
                    </div>

                </section>

            </div>

        </div>
    </AppLayout>
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
