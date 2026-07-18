<script setup>
import { ref } from 'vue';

const assets = ref([
    { id: 1, title: 'Gudang Logistik Modern', type: 'Gudang', price: 15000000, location: 'Plaju, Palembang', rating: '8.5', image: 'https://images.unsplash.com/photo-1586528116311-ad8ed3c84a0c?q=80&w=600&auto=format&fit=crop', isFavorite: false },
    { id: 2, title: 'Ruang Kantor Premium SCBD', type: 'Gedung', price: 8500000, location: 'Sudirman, Jakarta', rating: '9.2', image: 'https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=600&auto=format&fit=crop', isFavorite: true },
    { id: 3, title: 'Lahan Parkir Terbuka Luas', type: 'Lahan', price: 3000000, location: 'Bintaro, Tangerang', rating: '7.8', image: 'https://images.unsplash.com/photo-1573348722427-f1d6819fdf98?q=80&w=600&auto=format&fit=crop', isFavorite: false },
    { id: 4, title: 'Baliho Strategis Pusat Kota', type: 'Baliho', price: 5000000, location: 'Ancol, Jakarta', rating: '8.9', image: 'https://images.unsplash.com/photo-1559825481-12a05cc00344?q=80&w=600&auto=format&fit=crop', isFavorite: false },
    { id: 5, title: 'Kavling Komersial Siap Bangun', type: 'Lahan', price: 12000000, location: 'BSD City, Tangerang', rating: '8.0', image: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=600&auto=format&fit=crop', isFavorite: false },
    { id: 6, title: 'Ruko 3 Lantai Pusat Bisnis', type: 'Ruko', price: 25000000, location: 'Kelapa Gading, Jakarta', rating: '9.5', image: 'https://images.unsplash.com/photo-1582407947304-fd86f028f716?q=80&w=600&auto=format&fit=crop', isFavorite: true },
    { id: 7, title: 'Gudang Penyimpanan Dingin', type: 'Gudang', price: 18000000, location: 'Tanjung Priok, Jakarta', rating: '8.7', image: 'https://images.unsplash.com/photo-1553413077-190dd305871c?q=80&w=600&auto=format&fit=crop', isFavorite: false },
    { id: 8, title: 'Gedung Coworking Space', type: 'Gedung', price: 10000000, location: 'Kemang, Jakarta', rating: '9.0', image: 'https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?q=80&w=600&auto=format&fit=crop', isFavorite: false },
]);

const recommendedAssets = ref([...assets.value]);
const nearestAssets = ref([...assets.value].reverse());
const popularAssets = ref([...assets.value].sort((a, b) => b.rating - a.rating));
const newAssets = ref([...assets.value].slice(2, 8));

const toggleFavorite = (asset) => {
    asset.isFavorite = !asset.isFavorite;
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <div class="min-h-screen max-w-7xl m-auto py-8 sm:py-10 space-y-10 sm:space-y-12 bg-white text-[#0A2540] font-sans overflow-x-hidden">

        <template v-for="(section, idx) in [
            { title: 'Rekomendasi Untukmu', data: recommendedAssets },
            { title: 'Terdekat Dari Anda', data: nearestAssets },
            { title: 'Aset Rating Tertinggi', data: popularAssets },
            { title: 'Baru Ditambahkan', data: newAssets }
        ]" :key="idx">

            <section v-if="section.data.length > 0" class="pl-4 sm:pl-6 lg:pl-8">
                <!-- Section Header -->
                <div class="flex justify-between items-end mb-4 pr-4 sm:pr-6 lg:pr-8">
                    <h2 class="text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight">{{ section.title }}</h2>
                    <a href="#" class="text-xs sm:text-sm font-bold text-[#FFC000] hover:text-[#e6ad00] transition-colors flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                </div>

                <!-- Horizontal Scroll Container -->
                <!-- pb-8 ditambahkan agar ada ruang untuk lingkaran review yang keluar sedikit dari gambar -->
                <div class="flex overflow-x-auto gap-3 sm:gap-4 pb-6 pt-2 snap-x snap-mandatory no-scrollbar pr-4 sm:pr-6 lg:pr-8">

                    <!-- Card Wrapper -->
                    <!-- Lebar mobile diperkecil ke w-[150px] agar lebih pas di layar HP dan tidak berantakan -->
                    <div v-for="asset in section.data" :key="asset.id"
                         class="flex-none w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px] group cursor-pointer snap-start flex flex-col">

                        <!-- GAMBAR ASSET (Aspect Ratio Poster Netflix) -->
                        <!-- mb-3 memberi jarak untuk lingkaran review yang overlap ke bawah -->
                        <div class="aspect-[5/7] relative rounded-xl overflow-visible bg-gray-100 shadow-sm group-hover:shadow-lg transition-all duration-300 mb-3">

                            <!-- Container clip agar gambar tetap round, tapi badge bulat bisa di overflow-visible dari parent -->
                            <div class="w-full h-full rounded-xl overflow-hidden relative">
                                <img :src="asset.image" :alt="asset.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />

                                <!-- Gradient tipis hanya di atas untuk kontras badge -->
                                <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-black/50 to-transparent pointer-events-none"></div>

                                <!-- TOP BAR: Badge Jenis (Kiri) & Tombol Love (Kanan) -->
                                <div class="absolute top-2 sm:top-2.5 inset-x-2 sm:inset-x-2.5 z-10 flex justify-between items-start">
                                    <!-- Badge Jenis -->
                                    <span class="bg-white/90 backdrop-blur-md text-[#0A2540] text-[10px] sm:text-[11px] font-extrabold px-2 py-0.5 sm:py-1 rounded shadow-sm">
                                        {{ asset.type }}
                                    </span>

                                    <!-- Tombol Love -->
                                    <button @click.stop="toggleFavorite(asset)"
                                            class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center text-white hover:scale-110 transition-all">
                                        <i :class="[
                                            asset.isFavorite ? 'fa-solid fa-heart text-red-500' : 'fa-regular fa-heart text-white',
                                            'text-xs sm:text-sm transition-colors'
                                        ]"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- NETFLIX STYLE RATING BULAT -->
                            <!-- Posisi di kanan bawah, setengah di atas gambar dan setengah di luar gambar (-bottom-3) -->
                            <div class="absolute -bottom-2.5 right-2 sm:right-2.5 z-20">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#FFC000] text-[#0A2540] font-black text-[11px] sm:text-xs flex items-center justify-center shadow-md border-2 border-white">
                                    {{ asset.rating }}
                                </div>
                            </div>

                        </div>

                        <!-- KONTEN TEKS (DI LUAR GAMBAR AGAR TIDAK SESAK) -->
                        <div class="flex flex-col flex-grow px-0.5">
                            <!-- Judul Aset -->
                            <h3 class="font-bold text-xs sm:text-sm leading-snug text-[#0A2540] group-hover:text-[#FFC000] transition-colors line-clamp-2 mb-1">
                                {{ asset.title }}
                            </h3>

                            <!-- Lokasi -->
                            <div class="text-[10px] sm:text-[11px] text-gray-500 font-medium mb-1.5 flex items-center gap-1 truncate">
                                <i class="fa-solid fa-location-dot text-[#FFC000]"></i>
                                <span class="truncate">{{ asset.location }}</span>
                            </div>

                            <!-- Harga -->
                            <div class="font-extrabold text-xs sm:text-sm text-[#0A2540] mt-auto pt-1">
                                {{ formatRupiah(asset.price) }}
                                <span class="text-[10px] font-normal text-gray-400">/thn</span>
                            </div>
                        </div>

                    </div>

                </div>
            </section>
        </template>

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
</style>
