<script setup>
import { ref, computed } from 'vue'
import Navbar from '@/Components/Navbar.vue'

// State Navigasi Mobile Aktif ('beranda' | 'aktivitas' | 'inbox' | 'masuk')
const activeTab = ref('beranda')

const search = ref('')
const sort = ref('Terbaru')
const selectedCategory = ref('Semua')

const categories = [
    'Semua',
    'Villa',
    'Hotel',
    'Guest House',
    'Homestay',
    'Baliho'
]

const favorites = ref([
    {
        id: 1,
        title: 'Villa Pondok Jabuk',
        category: 'Villa',
        city: 'Banjarmasin',
        rating: 4.9,
        review: 123,
        price: 2100000,
        available: true,
        saved: '2 hari lalu',
        facilities: ['Wifi', 'Kolam', 'AC', 'Parkir'],
        images: [
            'https://picsum.photos/700/700?1',
            'https://picsum.photos/700/700?2',
            'https://picsum.photos/700/700?3'
        ]
    },
    {
        id: 2,
        title: 'Hotel Aston Banua',
        category: 'Hotel',
        city: 'Banjarbaru',
        rating: 4.8,
        review: 84,
        price: 890000,
        available: true,
        saved: 'Kemarin',
        facilities: ['Wifi', 'Restoran', 'Lift', 'Parkir'],
        images: [
            'https://picsum.photos/700/700?4',
            'https://picsum.photos/700/700?5',
            'https://picsum.photos/700/700?6'
        ]
    },
    {
        id: 3,
        title: 'Homestay Green Village',
        category: 'Homestay',
        city: 'Martapura',
        rating: 4.7,
        review: 56,
        price: 450000,
        available: false,
        saved: '1 minggu lalu',
        facilities: ['Wifi', 'AC', 'TV'],
        images: [
            'https://picsum.photos/700/700?7',
            'https://picsum.photos/700/700?8',
            'https://picsum.photos/700/700?9'
        ]
    }
])

const filteredFavorites = computed(() => {
    let data = favorites.value

    if (selectedCategory.value !== 'Semua') {
        data = data.filter(item => item.category === selectedCategory.value)
    }

    if (search.value) {
        data = data.filter(item =>
            item.title.toLowerCase().includes(search.value.toLowerCase())
        )
    }

    switch (sort.value) {
        case 'Harga Terendah':
            data = [...data].sort((a, b) => a.price - b.price)
            break

        case 'Harga Tertinggi':
            data = [...data].sort((a, b) => b.price - a.price)
            break

        case 'Rating':
            data = [...data].sort((a, b) => b.rating - a.rating)
            break
    }

    return data
})

const removeFavorite = (id) => {
    favorites.value = favorites.value.filter(item => item.id !== id)
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price)
}
</script>

<template>
    <div class="min-h-screen bg-[#F5F5F7] text-[#1D1D1F] font-sans antialiased selection:bg-amber-100 pb-24 sm:pb-0">
        <!-- NAVBAR DESKTOP -->
        <Navbar />

        <!-- MAIN CONTAINER -->
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 pt-16 sm:pt-20 lg:pt-28 pb-12 sm:pb-16">

            <!-- APPLE-STYLE HERO BANNER (RINGKAS & EFISIEN) -->
            <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r from-white/90 via-white/70 to-amber-50/30 backdrop-blur-2xl border border-white/80 p-4 sm:p-6 shadow-sm mb-4 sm:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-6 relative z-10">
                    
                    <!-- Judul & Deskripsi Sederhana -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-xl sm:text-3xl font-bold tracking-tight text-[#1D1D1F]">
                                Properti Favorit
                            </h1>
                            
                            <!-- Stat Indicators (Pill Ringkas) -->
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-400/15 text-amber-800 border border-amber-400/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ favorites.length }} Tersimpan
                                </span>
                                <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-200/50 text-slate-600 border border-slate-200/60">
                                    {{ categories.length - 1 }} Kategori
                                </span>
                            </div>
                        </div>

                        <p class="text-slate-500 text-xs sm:text-sm font-normal">
                            Daftar tempat impian dan akomodasi yang telah Anda simpan.
                        </p>
                    </div>

                </div>

                <!-- Ambient Glow Effect Ringan -->
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-gradient-to-br from-amber-200/20 to-transparent rounded-full blur-2xl pointer-events-none"></div>
            </div>

            <!-- MOBILE SEARCH & KATEGORI -->
            <div class="block lg:hidden mb-5 space-y-2.5">
                <!-- Search Input Mobile -->
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari properti favorit..."
                        class="w-full rounded-xl bg-white/80 border border-slate-200/80 pl-9 pr-3.5 py-2.5 text-xs text-[#1D1D1F] placeholder-slate-400 outline-none focus:ring-2 focus:ring-amber-400/50 shadow-xs"
                    />
                </div>

                <!-- Horizontal Scroll Category Chips (Mobile) -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
                    <button
                        v-for="item in categories"
                        :key="item"
                        @click="selectedCategory = item"
                        class="px-3.5 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all duration-200 flex-shrink-0"
                        :class="selectedCategory === item
                            ? 'bg-[#1D1D1F] text-white shadow-xs font-semibold'
                            : 'bg-white/80 border border-slate-200/80 text-slate-600'"
                    >
                        {{ item }}
                    </button>
                </div>
            </div>

            <!-- BODY CONTENT GRID -->
            <div class="grid grid-cols-12 gap-5 lg:gap-8">

                <!-- SIDEBAR FILTER (Desktop Only) -->
                <aside class="hidden lg:block lg:col-span-3">
                    <div class="bg-white/80 backdrop-blur-xl rounded-[1.5rem] border border-white/80 p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] sticky top-28 space-y-6">
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
                                    class="w-full rounded-xl px-3 py-2 text-left text-xs font-medium transition-all duration-200 flex items-center justify-between"
                                    :class="selectedCategory === item
                                        ? 'bg-[#1D1D1F] text-white shadow-sm'
                                        : 'text-slate-600 hover:bg-slate-100/80'"
                                >
                                    <span>{{ item }}</span>
                                    <i v-if="selectedCategory === item" class="fa-solid fa-check text-[10px] text-amber-400"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown Urutkan -->
                        <div>
                            <h3 class="font-medium text-xs text-slate-400 uppercase tracking-wider px-1 mb-2">Urutkan</h3>
                            <div class="relative">
                                <select
                                    v-model="sort"
                                    class="w-full rounded-xl bg-slate-100/80 border-0 px-3 py-2 text-xs font-medium text-[#1D1D1F] outline-none focus:ring-2 focus:ring-amber-400/50 cursor-pointer appearance-none"
                                >
                                    <option>Terbaru</option>
                                    <option>Harga Terendah</option>
                                    <option>Harga Tertinggi</option>
                                    <option>Rating</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
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
                        <div class="block lg:hidden">
                            <select
                                v-model="sort"
                                class="rounded-xl bg-white/80 border border-slate-200/80 px-2.5 py-1.5 text-xs font-medium text-[#1D1D1F] outline-none"
                            >
                                <option>Terbaru</option>
                                <option>Harga Terendah</option>
                                <option>Harga Tertinggi</option>
                                <option>Rating</option>
                            </select>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="filteredFavorites.length === 0"
                        class="bg-white/80 backdrop-blur-xl rounded-2xl sm:rounded-[1.5rem] border border-white/80 py-12 sm:py-16 px-4 text-center shadow-xs"
                    >
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                            <i class="fa-regular fa-bookmark"></i>
                        </div>
                        <h3 class="text-base font-semibold text-[#1D1D1F]">
                            Belum Ada Favorit
                        </h3>
                        <p class="text-slate-400 text-xs mt-1 max-w-xs mx-auto">
                            Simpan villa atau hotel favoritmu agar mudah ditemukan di kemudian hari.
                        </p>
                    </div>

                    <!-- Card List -->
                    <div v-else class="grid grid-cols-1 sm:grid-cols-1 gap-4 sm:space-y-4 sm:gap-0">
                        <article
                            v-for="asset in filteredFavorites"
                            :key="asset.id"
                            class="bg-white/90 backdrop-blur-md rounded-2xl sm:rounded-[1.5rem] overflow-hidden border border-slate-200/60 shadow-[0_2px_12px_rgba(0,0,0,0.02)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)] hover:border-slate-300/80 transition-all duration-300 group flex flex-col sm:block"
                        >
                            <!-- LAYOUT MOBILE -->
                            <div class="block sm:hidden relative">
                                <div class="relative h-44 bg-slate-100 w-full overflow-hidden">
                                    <img
                                        :src="asset.images[0]"
                                        class="w-full h-full object-cover"
                                        alt="Property Image"
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>
                                    
                                    <span class="absolute top-2.5 left-2.5 bg-[#1D1D1F]/90 text-amber-400 font-extrabold px-2.5 py-0.5 rounded-md text-[9px] uppercase tracking-wider">
                                        {{ asset.category }}
                                    </span>

                                    <button
                                        @click="removeFavorite(asset.id)"
                                        class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-white/90 backdrop-blur-md flex items-center justify-center text-rose-500 shadow-xs active:scale-90 transition"
                                    >
                                        <i class="fa-solid fa-heart text-xs"></i>
                                    </button>

                                    <span class="absolute bottom-2.5 left-2.5 text-white/90 text-[10px] font-medium flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-[9px]"></i> Disimpan {{ asset.saved }}
                                    </span>
                                </div>

                                <div class="p-3.5">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <h3 class="font-bold text-sm text-[#1D1D1F] line-clamp-1">
                                            {{ asset.title }}
                                        </h3>
                                        <div class="flex items-center gap-1 text-[11px] font-bold text-slate-800 bg-amber-50 px-1.5 py-0.5 rounded flex-shrink-0">
                                            <i class="fa-solid fa-star text-amber-400 text-[9px]"></i>
                                            <span>{{ asset.rating }}</span>
                                        </div>
                                    </div>

                                    <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-amber-500 text-[10px]"></i>
                                        {{ asset.city }}
                                    </p>

                                    <div class="mt-2 flex gap-1 flex-wrap">
                                        <span
                                            v-for="fac in asset.facilities"
                                            :key="fac"
                                            class="text-[9px] text-slate-600 bg-slate-100 px-2 py-0.5 rounded font-medium"
                                        >
                                            {{ fac }}
                                        </span>
                                    </div>

                                    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                        <div>
                                            <span class="text-[9px] text-slate-400 font-medium block uppercase tracking-wider">Mulai dari</span>
                                            <span class="text-sm font-extrabold text-[#1D1D1F]">
                                                {{ formatPrice(asset.price) }}
                                            </span>
                                        </div>

                                        <button class="bg-amber-400 hover:bg-amber-500 text-[#1D1D1F] font-bold px-3.5 py-1.5 rounded-lg text-xs transition active:scale-95">
                                            Lihat Detail
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- LAYOUT DESKTOP -->
                            <div class="hidden sm:flex flex-col lg:grid lg:grid-cols-12 lg:items-center">
                                <div class="lg:col-span-4 p-2.5">
                                    <div class="grid grid-cols-3 sm:grid-cols-2 gap-1.5 h-36 sm:h-44 lg:h-[160px]">
                                        <div class="col-span-2 sm:col-span-1 sm:row-span-2 overflow-hidden rounded-xl bg-slate-100 relative">
                                            <img
                                                :src="asset.images[0]"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                                alt="Property Image 1"
                                            >
                                        </div>
                                        <div class="overflow-hidden rounded-xl bg-slate-100 h-full sm:h-[84px] lg:h-[77px]">
                                            <img
                                                :src="asset.images[1]"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                                alt="Property Image 2"
                                            >
                                        </div>
                                        <div class="hidden sm:block overflow-hidden rounded-xl bg-slate-100 h-[84px] lg:h-[77px]">
                                            <img
                                                :src="asset.images[2]"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                                alt="Property Image 3"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:col-span-5 px-4 pb-3 lg:pb-3 lg:py-3 lg:px-2 flex flex-col justify-center">
                                    <div class="flex items-center justify-between sm:justify-start gap-2 mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="bg-amber-400/20 text-amber-900 font-semibold px-2.5 py-0.5 rounded-full text-[10px]">
                                                {{ asset.category }}
                                            </span>
                                            <span class="text-slate-400 text-[10px] flex items-center gap-1 font-medium">
                                                <i class="fa-solid fa-heart text-rose-500 text-[9px]"></i> {{ asset.saved }}
                                            </span>
                                        </div>

                                        <button
                                            @click="removeFavorite(asset.id)"
                                            class="sm:hidden text-slate-400 hover:text-rose-500 p-1"
                                        >
                                            <i class="fa-regular fa-trash-can text-xs"></i>
                                        </button>
                                    </div>

                                    <h3 class="text-base font-semibold text-[#1D1D1F] group-hover:text-amber-600 transition-colors">
                                        {{ asset.title }}
                                    </h3>

                                    <div class="flex items-center gap-1 mt-1 text-xs">
                                        <i class="fa-solid fa-star text-amber-400 text-[11px]"></i>
                                        <span class="font-semibold text-[#1D1D1F]">{{ asset.rating }}</span>
                                        <span class="text-slate-400 text-[11px]">({{ asset.review }} ulasan)</span>
                                    </div>

                                    <div class="flex items-center gap-1.5 text-slate-500 text-xs mt-1.5">
                                        <i class="fa-solid fa-location-dot text-slate-400 text-xs"></i>
                                        <span>{{ asset.city }}</span>
                                    </div>

                                    <div class="mt-2.5 flex gap-1.5 flex-wrap">
                                        <span
                                            v-for="fac in asset.facilities"
                                            :key="fac"
                                            class="text-[10px] text-slate-500 bg-slate-100/80 px-2 py-0.5 rounded-md font-medium"
                                        >
                                            {{ fac }}
                                        </span>
                                    </div>
                                </div>

                                <div class="lg:col-span-3 p-4 lg:p-4 border-t lg:border-t-0 lg:border-l border-slate-100/80 flex sm:flex-row lg:flex-col items-center sm:items-end lg:items-stretch justify-between gap-3 h-full bg-slate-50/40 lg:bg-transparent">
                                    <div class="text-left lg:text-left">
                                        <div class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Mulai dari</div>
                                        <div class="text-base lg:text-lg font-bold text-[#1D1D1F] tracking-tight">
                                            {{ formatPrice(asset.price) }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 line-through">
                                            {{ formatPrice(asset.price * 1.15) }}
                                        </div>
                                    </div>

                                    <div class="flex lg:flex-col items-center gap-2 w-auto sm:w-1/2 lg:w-full">
                                        <button class="w-full bg-amber-400 hover:bg-amber-300 text-[#1D1D1F] font-semibold px-4 py-2 rounded-xl text-xs transition-all duration-200 shadow-xs active:scale-[0.98]">
                                            Lihat Detail
                                        </button>
                                        <button
                                            @click="removeFavorite(asset.id)"
                                            class="hidden sm:flex w-full items-center justify-center gap-1 text-slate-400 hover:text-rose-500 py-1 rounded-xl text-[11px] font-medium transition-colors"
                                        >
                                            <i class="fa-regular fa-trash-can text-[10px]"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                </section>

            </div>

        </div>

        <!-- BOTTOM NAVIGATION BAR (Mobile Only) -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl border-t border-slate-100 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-2 pt-2.5 pb-0 sm:hidden z-50 flex items-center justify-around overflow-hidden">
            
            <!-- Beranda -->
            <button
                @click="activeTab = 'beranda'"
                class="relative flex-1 flex flex-col items-center justify-center pt-1.5 pb-3 transition-colors group"
                :class="activeTab === 'beranda' ? 'text-amber-500 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            >
                <div v-if="activeTab === 'beranda'" class="absolute inset-y-0 w-full bg-amber-400/10 rounded-xl -z-10"></div>
                <i class="fa-solid fa-house text-lg mb-1" :class="activeTab === 'beranda' ? 'text-amber-500' : ''"></i>
                <span class="text-[11px] tracking-tight">Beranda</span>
                <span v-if="activeTab === 'beranda'" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1 bg-amber-400 rounded-t-full"></span>
            </button>

            <!-- Aktivitas -->
            <button
                @click="activeTab = 'aktivitas'"
                class="relative flex-1 flex flex-col items-center justify-center pt-1.5 pb-3 transition-colors group"
                :class="activeTab === 'aktivitas' ? 'text-amber-500 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            >
                <div v-if="activeTab === 'aktivitas'" class="absolute inset-y-0 w-full bg-amber-400/10 rounded-xl -z-10"></div>
                <i class="fa-solid fa-clipboard-list text-lg mb-1" :class="activeTab === 'aktivitas' ? 'text-amber-500' : ''"></i>
                <span class="text-[11px] tracking-tight">Aktivitas</span>
                <span v-if="activeTab === 'aktivitas'" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1 bg-amber-400 rounded-t-full"></span>
            </button>

            <!-- Kotak Masuk -->
            <button
                @click="activeTab = 'inbox'"
                class="relative flex-1 flex flex-col items-center justify-center pt-1.5 pb-3 transition-colors group"
                :class="activeTab === 'inbox' ? 'text-amber-500 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            >
                <div v-if="activeTab === 'inbox'" class="absolute inset-y-0 w-full bg-amber-400/10 rounded-xl -z-10"></div>
                <i class="fa-solid fa-inbox text-lg mb-1" :class="activeTab === 'inbox' ? 'text-amber-500' : ''"></i>
                <span class="text-[11px] tracking-tight">Kotak Masuk</span>
                <span v-if="activeTab === 'inbox'" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1 bg-amber-400 rounded-t-full"></span>
            </button>

            <!-- Masuk / Profil -->
            <button
                @click="activeTab = 'masuk'"
                class="relative flex-1 flex flex-col items-center justify-center pt-1.5 pb-3 transition-colors group"
                :class="activeTab === 'masuk' ? 'text-amber-500 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            >
                <div v-if="activeTab === 'masuk'" class="absolute inset-y-0 w-full bg-amber-400/10 rounded-xl -z-10"></div>
                <i class="fa-solid fa-user text-lg mb-1" :class="activeTab === 'masuk' ? 'text-amber-500' : ''"></i>
                <span class="text-[11px] tracking-tight">Masuk</span>
                <span v-if="activeTab === 'masuk'" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1 bg-amber-400 rounded-t-full"></span>
            </button>

        </nav>
    </div>
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