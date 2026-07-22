<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import AssetGallery from '@/Components/UI/AssetGallery.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    }
});

const handleFavorite = async () => {
    if (props.asset.isFavorite) {
        // Hapus favorit
        try {
            await axios.delete(`/favorites/${props.asset.favorite_id}`);
            props.asset.isFavorite = false;
            props.asset.favorite_id = null;
            if (props.asset.favorites_count > 0) props.asset.favorites_count--;
        } catch (error) {
            console.error('Gagal menghapus dari favorit', error);
        }
    } else {
        // Tambah favorit
        try {
            const response = await axios.post('/favorites', {
                asset_id: props.asset.id
            });
            if (response.data.success) {
                props.asset.isFavorite = true;
                props.asset.favorite_id = response.data.favorite_id;
                props.asset.favorites_count = (props.asset.favorites_count || 0) + 1;
            }
        } catch (error) {
            if (error.response?.status === 401) {
                // Not logged in
                window.location.href = '/login';
            } else {
                console.error('Gagal menambahkan ke favorit', error);
            }
        }
    }
};

// Helper untuk format rupiah
const formatRupiah = (value) => {
    if (!value) return 'Hubungi Pemilik';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const periodLabel = {
    hour: 'jam',
    day: 'hari',
    week: 'minggu',
    month: 'bulan',
    year: 'tahun'
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Menghitung harga termurah untuk ditampilkan di card booking
const lowestPrice = computed(() => {
    if (!props.asset.pricings || props.asset.pricings.length === 0) return null;
    return props.asset.pricings.reduce((min, p) => p.price < min.price ? p : min, props.asset.pricings[0]);
});

// Fasilitas & Spesifikasi
const specification = computed(() => props.asset.detail || {});
const facilities = computed(() => {
    return Array.isArray(specification.value.facility)
        ? specification.value.facility
        : (specification.value.fasilitas || []);
});
const getSpecKeys = computed(() => {
    const spec = { ...specification.value };
    delete spec.facility;
    delete spec.fasilitas;
    return Object.keys(spec);
});

// Form Booking
const form = useForm({
    asset_id: props.asset.id,
    pricing_id: lowestPrice.value ? lowestPrice.value.id : null,
    start_date: null,
    end_date: null,
});

const submitBooking = () => {
    form.start_date = startDate.value ? startDate.value.toISOString().split('T')[0] : null;
    form.end_date = endDate.value ? endDate.value.toISOString().split('T')[0] : null;
    form.get(route('booking.create', { asset: props.asset.id }));
};

// Menghitung distribusi rating (5 bintang sampai 1 bintang)
const reviewDistribution = computed(() => {
    const counts = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
    if (props.asset.reviews) {
        props.asset.reviews.forEach(r => {
            const rating = Math.round(r.rating);
            if (counts[rating] !== undefined) {
                counts[rating]++;
            }
        });
    }
    const total = props.asset.reviews?.length || 0;

    return [5, 4, 3, 2, 1].map(star => {
        const count = counts[star];
        const percentage = total > 0 ? (count / total) * 100 : 0;
        return { star, count, percentage };
    });
});

// ==========================================
// KALENDER SEWA
// ==========================================
const daysOfWeek = ['Min', 'Sn', 'Sl', 'R', 'Km', 'J', 'Sb'];
const startDate = ref(null);
const endDate = ref(null);
const calendarPage = ref(0);
const transitionName = ref('slide-left');

const monthsData = computed(() => {
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();

    const data = [];
    for (let i = 0; i < 12; i++) {
        const d = new Date(currentYear, currentMonth + i, 1);
        const year = d.getFullYear();
        const month = d.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const firstDayOfWeek = d.getDay();
        const title = d.toLocaleString('id-ID', { month: 'long', year: 'numeric' });

        data.push({
            year,
            month,
            title,
            daysInMonth,
            emptyDaysStart: firstDayOfWeek
        });
    }
    return data;
});

const nextMonth = () => {
    if (calendarPage.value < monthsData.value.length - 2) {
        transitionName.value = 'slide-left';
        calendarPage.value++;
    }
};

const prevMonth = () => {
    if (calendarPage.value > 0) {
        transitionName.value = 'slide-right';
        calendarPage.value--;
    }
};

const selectDate = (year, month, date) => {
    const selected = new Date(year, month, date);

    // Blokir tanggal masa lalu
    const today = new Date();
    today.setHours(0,0,0,0);
    if (selected < today) return;

    if (!startDate.value || (startDate.value && endDate.value)) {
        startDate.value = selected;
        endDate.value = null;
    } else if (selected < startDate.value) {
        startDate.value = selected;
    } else if (selected > startDate.value) {
        endDate.value = selected;
    }
};

const isStartDate = (year, month, date) => {
    if (!startDate.value) return false;
    return startDate.value.getFullYear() === year && startDate.value.getMonth() === month && startDate.value.getDate() === date;
};

const isPastDate = (year, month, date) => {
    const selected = new Date(year, month, date);
    const today = new Date();
    today.setHours(0,0,0,0);
    return selected < today;
};

const isEndDate = (year, month, date) => {
    if (!endDate.value) return false;
    return endDate.value.getFullYear() === year && endDate.value.getMonth() === month && endDate.value.getDate() === date;
};

const isInRange = (year, month, date) => {
    if (!startDate.value || !endDate.value) return false;
    const current = new Date(year, month, date);
    return current > startDate.value && current < endDate.value;
};

const clearDates = () => {
    startDate.value = null;
    endDate.value = null;
};

const nightsCount = computed(() => {
    if (!startDate.value || !endDate.value) return 0;
    const diffTime = Math.abs(endDate.value - startDate.value);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
});

const formattedDateRange = computed(() => {
    if (!startDate.value) return 'Pilih tanggal Anda untuk melihat ketersediaan';
    const startStr = startDate.value.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    if (!endDate.value) return startStr;
    const endStr = endDate.value.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    return `${startStr} - ${endStr}`;
});

// Calendar Touch Gestures
const touchStartX = ref(0);
const touchEndX = ref(0);
const handleTouchStart = (e) => { touchStartX.value = e.changedTouches[0].screenX; };
const handleTouchEnd = (e) => {
    touchEndX.value = e.changedTouches[0].screenX;
    if (touchEndX.value < touchStartX.value - 50) nextMonth();
    if (touchEndX.value > touchStartX.value + 50) prevMonth();
};

// DATA ROOM TYPES (untuk section Pilih Jenis Kamar)
const roomTypes = computed(() => {
    // Jika asset memiliki data kamar dari API, gunakan itu
    if (props.asset.room_types && props.asset.room_types.length > 0) {
        return props.asset.room_types;
    }
    // Data dummy sebagai fallback sesuai desain
    return [
        {
            id: 1,
            name: 'Superior Room',
            price: null,
            priceLabel: 'Bayar online',
            facilities: ['1 king bed', '1 king bed', 'AL', 'AC', 'TV'],
            isVilla: false,
        },
        {
            id: 2,
            name: 'Deluxe Room',
            price: 2250000,
            priceLabel: null,
            facilities: ['1 king bed', '1 king bed', 'AL', 'AC', 'TV'],
            isVilla: false,
        },
        {
            id: 3,
            name: 'Villa Tiga Kamar Tidur',
            price: 2250000,
            priceLabel: null,
            facilities: ['Kapasitas: 6 dewasa', 'Seluruh villa', 'Dapur pribadi', 'TV Layar datar', 'Kolam renang pribadi'],
            isVilla: true,
        }
    ];
});

// Data untuk footer villa
const villaTotal = computed(() => {
    return {
        label: '1 vila seharga',
        price: 288332
    };
});
</script>

<template>
    <Head :title="asset.title || 'Detail Aset'" />

    <AppLayout :hideNavbar="true" :hideBottombar="true">

        <!-- CUSTOM STICKY NAVBAR -->
        <DetailNavbar :isFavorited="asset.isFavorite" @favorite="handleFavorite" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-[#ffc000] font-sans pb-32 lg:pb-10">

            <!-- TITLE & HEADER -->
            <div class="mb-6">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-2">{{ asset.title }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-gray-600">
                    <div class="flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-gray-400"></i>
                        <span class="underline decoration-gray-300">{{ asset.city }}, {{ asset.province }}</span>
                    </div>

                    <div class="flex items-center gap-1">
                        <i class="fa-solid fa-star text-[#FFC000]"></i>
                        <span class="text-[#ffc000] font-bold">{{ parseFloat(asset.reviews_avg_rating || 0).toFixed(1) }}</span>
                        <span class="text-gray-500 underline decoration-gray-300">· {{ asset.reviews_count || 0 }} Ulasan</span>
                    </div>

                    <div class="flex items-center gap-1">
                        <i class="fa-solid fa-heart text-red-500"></i>
                        <span class="text-gray-500">
                            {{ asset.favorites_count || 0 }} favorit
                        </span>
                    </div>
                </div>
            </div>

            <!-- HERO GALLERY AND MODAL -->
            <AssetGallery :images="asset.images" />
            <!-- CONTENT LAYOUT (Kiri: Detail, Kanan: Booking Card) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mt-8">

                <!-- KIRI (Detail) -->
                <div class="lg:col-span-2 space-y-10">

                    <!-- Info Host -->
                    <div class="flex items-center justify-between pb-8 border-b border-gray-200">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl font-extrabold">
                                    {{ asset.owner_profile?.user?.name || 'Anonim' }}
                                </h2>

                                <span
                                    v-if="asset.owner_profile?.status === 'verified'"
                                    class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-semibold"
                                >
                                    Terverifikasi
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 mt-1">
                                Pemilik aset
                            </p>

                            <p
                                v-if="asset.owner_profile?.user?.phone"
                                class="text-sm text-gray-600 mt-2"
                            >
                                <i class="fa-solid fa-phone text-xs mr-1 text-gray-400"></i>
                                {{ asset.owner_profile.user.phone }}
                            </p>
                        </div>

                        <div class="w-14 h-14 rounded-full overflow-hidden shrink-0 border border-gray-200 shadow-sm">
                            <img
                                v-if="asset.owner_profile?.user?.profile_photo"
                                :src="asset.owner_profile.user.profile_photo"
                                class="w-full h-full object-cover"
                            />

                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center bg-[#ffc000] text-white font-bold text-xl"
                            >
                                {{ asset.owner_profile?.user?.name?.charAt(0) || 'O' }}
                            </div>
                        </div>
                    </div>

                    <!-- Spesifikasi Tambahan (JSON) -->
                    <div v-if="getSpecKeys.length > 0" class="py-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">Informasi Umum</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-6 gap-x-4">
                            <div v-for="key in getSpecKeys" :key="key" class="flex flex-col">
                                <span class="text-gray-500 text-sm capitalize mb-1">{{ key.replace(/_/g, ' ') }}</span>
                                <span class="font-bold text-[#ffc000]">{{ specification[key] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="py-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">Tentang Aset Ini</h3>
                        <div class="text-gray-600 leading-relaxed whitespace-pre-line text-justify">
                            {{ asset.description }}
                        </div>
                    </div>

                    <!-- Fasilitas Utama -->
                    <div id="fasilitas" v-if="facilities.length > 0" class="py-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">Fasilitas Utama</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="(fac, idx) in facilities" :key="idx" class="flex items-center gap-3 text-gray-700 font-medium">
                                <div class="w-7 h-7 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                                    <i class="fa-solid fa-check text-xs"></i>
                                </div>
                                <span class="capitalize">{{ fac }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi Map Placeholder -->
                    <div id="lokasi" class="py-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">Lokasi</h3>
                        <p class="text-gray-600 mb-4">{{ asset.address }}, {{ asset.city }}, {{ asset.province }}</p>
                        <div class="w-full h-64 bg-gray-200 rounded-xl overflow-hidden relative flex items-center justify-center shadow-inner">
                            <div class="absolute inset-0 bg-cover bg-center opacity-40" style="background-image: url('https://map.viamichelin.com/map/carte?map=viamichelin&z=10&lat=-0.502&lon=117.153&width=800&height=400&format=png&version=latest&layer=background')"></div>
                            <div class="z-10 flex flex-col items-center bg-white/95 p-4 rounded-2xl shadow-lg border border-gray-100">
                                <i class="fa-solid fa-location-dot text-red-500 text-3xl mb-2"></i>
                                <span class="font-bold text-[#ffc000]">{{ asset.city }}, {{ asset.province }}</span>
                                <span class="text-xs text-gray-500 mt-0.5">Lokasi presisi diberikan setelah konfirmasi sewa</span>
                            </div>
                        </div>
                    </div>
            <!-- SECTION PILIH JENIS KAMAR (DESAIN DARI FOTO) -->
            <div id="pilih-kamar" class="mt-10 mb-8 bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="px-6 pt-6 pb-4">
                    <h2 class="text-2xl font-extrabold text-[#ffc000]">Pilih Jenis Kamar</h2>
                </div>

                <!-- Availability Bar -->
                <div class="mx-6 mb-6 flex flex-wrap items-center gap-3 bg-[#F0F3F7] px-4 py-3 rounded-full text-sm">
                    <span class="flex items-center gap-2 font-medium text-[#ffc000]">
                        <i class="fa-regular fa-calendar"></i>
                        Kam, 23 Jul - Jum, 24 Jul
                    </span>
                    <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                    <span class="flex items-center gap-2 text-gray-700">
                        <i class="fa-regular fa-user"></i>
                        2 dewasa - 0 anak - Jul
                    </span>
                    <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                    <span class="flex items-center gap-2 text-gray-700">
                        <i class="fa-regular fa-door-open"></i>
                        2 dewasa - 0 anak - 1 kamar
                    </span>
                    <button class="ml-auto bg-[#ffc000] text-white font-semibold px-6 py-1.5 rounded-full text-sm hover:bg-[#1a3a5a] transition">
                        Pesan
                    </button>
                </div>

                <!-- Daftar Room Types -->
                <div class="px-6 pb-4 space-y-3">
                    <!-- Loop room types -->
                    <div 
                        v-for="room in roomTypes" 
                        :key="room.id"
                        class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-2xl border border-gray-200 hover:border-gray-400 transition bg-white"
                        :class="room.isVilla ? 'bg-[#F3F7FE] border-[#DCE5F0]' : ''"
                    >
                        <!-- Kiri: Nama & Fasilitas -->
                        <div class="flex-1 min-w-[180px]">
                            <h3 class="font-bold text-[#ffc000] text-lg">{{ room.name }}</h3>
                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                <span 
                                    v-for="(fac, idx) in room.facilities" 
                                    :key="idx"
                                    class="inline-flex items-center gap-1 bg-[#E9EDF3] px-2.5 py-0.5 rounded-full text-xs font-medium text-[#1F2A3E]"
                                >
                                    <i v-if="fac.includes('king')" class="fa-solid fa-bed text-[10px]"></i>
                                    <i v-else-if="fac.includes('dewasa')" class="fa-solid fa-user text-[10px]"></i>
                                    <i v-else-if="fac.includes('Villa') || fac.includes('villa')" class="fa-solid fa-house text-[10px]"></i>
                                    <i v-else-if="fac.includes('Dapur')" class="fa-solid fa-kitchen-set text-[10px]"></i>
                                    <i v-else-if="fac.includes('TV')" class="fa-solid fa-tv text-[10px]"></i>
                                    <i v-else-if="fac.includes('Kolam')" class="fa-solid fa-water-ladder text-[10px]"></i>
                                    {{ fac }}
                                </span>
                            </div>
                        </div>

                        <!-- Kanan: Aksi & Harga -->
                        <div class="flex items-center gap-4 flex-wrap">
                            <button 
                                class="bg-[#ffc000] text-white font-semibold px-4 py-1.5 rounded-full text-sm hover:bg-[#1a3a5a] transition flex items-center gap-1.5"
                            >
                                <i class="fa-regular fa-credit-card text-xs"></i>
                                Pesan sekarang
                            </button>
                            <span class="font-bold text-[#ffc000] whitespace-nowrap">
                                <template v-if="room.price">
                                    Rp {{ room.price.toLocaleString('id-ID') }}
                                </template>
                                <template v-else>
                                    <span class="text-sm font-medium text-[#5F738C]">Bayar online</span>
                                </template>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer: Villa Total -->
                <div class="px-6 py-4 border-t border-gray-200 flex flex-wrap items-center justify-end gap-4">
                    <span class="text-[#1F334A] font-medium">{{ villaTotal.label }}</span>
                    <span class="font-extrabold text-[#ffc000] text-xl">Rp {{ villaTotal.price.toLocaleString('id-ID') }}</span>
                    <button class="bg-[#ffc000] text-white font-semibold px-8 py-2 rounded-full text-sm hover:bg-[#1a3a5a] transition">
                        Pilih Villa
                    </button>
                </div>
            </div>
            <!-- ============================================ -->
            <!-- END SECTION PILIH JENIS KAMAR                 -->
            <!-- ============================================ -->

                    <!-- SEKSI PEMILIHAN TANGGAL (KALENDER) -->
                    <div class="pb-10 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-1">
                            <h2 class="text-2xl font-extrabold text-[#ffc000]">
                                {{ nightsCount ? `${nightsCount} malam di ${asset.title || 'Kota ini'}` : 'Pilih tanggal sewa' }}
                            </h2>
                            <button
                                v-if="startDate"
                                @click="clearDates"
                                class="text-xs text-gray-500 hover:text-black underline font-semibold transition"
                            >
                                Hapus Tanggal
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mb-8">{{ formattedDateRange }}</p>

                        <div class="bg-white rounded-2xl relative w-full overflow-hidden touch-pan-y border border-gray-100 p-2 sm:p-4 shadow-sm" @touchstart.passive="handleTouchStart" @touchend.passive="handleTouchEnd">
                            <!-- Header Bulan -->
                            <div class="flex justify-between items-center mb-6 px-2 pt-2">
                                <button
                                    @click="prevMonth"
                                    class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center transition border border-gray-200"
                                    :class="calendarPage === 0 ? 'opacity-30 cursor-not-allowed' : ''"
                                >
                                    <i class="fa-solid fa-chevron-left text-[#ffc000] text-sm"></i>
                                </button>
                                <div class="flex gap-8 w-full px-4">
                                    <h3 class="flex-1 text-center text-[15px] font-extrabold text-[#ffc000]">{{ monthsData[calendarPage]?.title }}</h3>
                                    <h3 class="flex-1 text-center text-[15px] font-extrabold text-[#ffc000] hidden sm:block">{{ monthsData[calendarPage + 1]?.title }}</h3>
                                </div>
                                <button
                                    @click="nextMonth"
                                    class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center transition border border-gray-200"
                                >
                                    <i class="fa-solid fa-chevron-right text-[#ffc000] text-sm"></i>
                                </button>
                            </div>

                            <!-- Grid Kalender -->
                            <div class="relative overflow-hidden min-h-[280px]">
                                <transition :name="transitionName" mode="out-in">
                                    <div :key="calendarPage" class="flex gap-8 sm:px-2 w-full">
                                        <!-- Kalender Bulan Kiri -->
                                        <div class="flex-1">
                                            <div class="grid grid-cols-7 text-center mb-3">
                                                <div v-for="day in daysOfWeek" :key="'d1-'+day" class="text-xs font-bold text-gray-400 py-1">
                                                    {{ day }}
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-7 gap-y-1 text-center text-sm font-medium">
                                                <div v-for="e in monthsData[calendarPage]?.emptyDaysStart" :key="'e1-'+e" class="h-10"></div>
                                                <div
                                                    v-for="d in monthsData[calendarPage]?.daysInMonth"
                                                    :key="'m1-'+d"
                                                    @click="selectDate(monthsData[calendarPage].year, monthsData[calendarPage].month, d)"
                                                    class="h-10 flex items-center justify-center cursor-pointer transition-all relative text-xs sm:text-sm"
                                                    :class="[
                                                        isPastDate(monthsData[calendarPage].year, monthsData[calendarPage].month, d) ? 'text-gray-300 line-through cursor-not-allowed' : 'hover:bg-gray-100 text-gray-800',
                                                        isStartDate(monthsData[calendarPage].year, monthsData[calendarPage].month, d) ? 'bg-[#ffc000] text-white font-bold rounded-l-full shadow' : '',
                                                        isEndDate(monthsData[calendarPage].year, monthsData[calendarPage].month, d) ? 'bg-[#ffc000] text-white font-bold rounded-r-full shadow' : '',
                                                        isInRange(monthsData[calendarPage].year, monthsData[calendarPage].month, d) ? 'bg-blue-50 text-[#ffc000] font-semibold' : '',
                                                        (isStartDate(monthsData[calendarPage].year, monthsData[calendarPage].month, d) && isEndDate(monthsData[calendarPage].year, monthsData[calendarPage].month, d)) ? 'rounded-full' : ''
                                                    ]"
                                                >
                                                    {{ d }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kalender Bulan Kanan (Kompak/Desktop) -->
                                        <div v-if="monthsData[calendarPage + 1]" class="flex-1 hidden sm:block">
                                            <div class="grid grid-cols-7 text-center mb-3">
                                                <div v-for="day in daysOfWeek" :key="'d2-'+day" class="text-xs font-bold text-gray-400 py-1">
                                                    {{ day }}
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-7 gap-y-1 text-center text-sm font-medium">
                                                <div v-for="e in monthsData[calendarPage + 1]?.emptyDaysStart" :key="'e2-'+e" class="h-10"></div>
                                                <div
                                                    v-for="d in monthsData[calendarPage + 1]?.daysInMonth"
                                                    :key="'m2-'+d"
                                                    @click="selectDate(monthsData[calendarPage + 1].year, monthsData[calendarPage + 1].month, d)"
                                                    class="h-10 flex items-center justify-center cursor-pointer transition-all relative text-xs sm:text-sm"
                                                    :class="[
                                                        isPastDate(monthsData[calendarPage + 1].year, monthsData[calendarPage + 1].month, d) ? 'text-gray-300 line-through cursor-not-allowed' : 'hover:bg-gray-100 text-gray-800',
                                                        isStartDate(monthsData[calendarPage + 1].year, monthsData[calendarPage + 1].month, d) ? 'bg-[#ffc000] text-white font-bold rounded-l-full shadow' : '',
                                                        isEndDate(monthsData[calendarPage + 1].year, monthsData[calendarPage + 1].month, d) ? 'bg-[#ffc000] text-white font-bold rounded-r-full shadow' : '',
                                                        isInRange(monthsData[calendarPage + 1].year, monthsData[calendarPage + 1].month, d) ? 'bg-blue-50 text-[#ffc000] font-semibold' : '',
                                                        (isStartDate(monthsData[calendarPage + 1].year, monthsData[calendarPage + 1].month, d) && isEndDate(monthsData[calendarPage + 1].year, monthsData[calendarPage + 1].month, d)) ? 'rounded-full' : ''
                                                    ]"
                                                >
                                                    {{ d }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <!-- SEKSI ULASAN & RATING -->
                    <div id="ulasan" class="pt-4">
                        <div class="flex items-center gap-2 mb-6">
                            <i class="fa-solid fa-star text-2xl text-[#FFC000]"></i>
                            <h2 class="text-2xl font-extrabold">
                                {{ parseFloat(asset.reviews_avg_rating || 0).toFixed(1) }}
                            </h2>
                            <span class="text-2xl font-extrabold text-gray-300">·</span>
                            <h2 class="text-2xl font-extrabold">
                                {{ asset.reviews_count || 0 }} Ulasan
                            </h2>
                        </div>

                        <!-- Rating Breakdown Bars -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-3 mb-10">
                            <div v-for="item in reviewDistribution" :key="item.star" class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-gray-700 w-3">{{ item.star }}</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div
                                        class="bg-[#ffc000] h-full rounded-full transition-all duration-500"
                                        :style="{ width: `${item.percentage}%` }"
                                    ></div>
                                </div>
                                <span class="text-xs text-gray-500 w-8 text-right">{{ item.count }}</span>
                            </div>
                        </div>

                        <!-- Daftar Ulasan User -->
                        <div v-if="asset.reviews && asset.reviews.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div
                                v-for="review in asset.reviews"
                                :key="review.id"
                                class="p-5 border border-gray-100 rounded-2xl bg-gray-50/50 space-y-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-300 flex-shrink-0">
                                        <img
                                            v-if="review.user?.profile_photo"
                                            :src="review.user.profile_photo"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full bg-[#ffc000] text-white flex items-center justify-center font-bold text-sm">
                                            {{ review.user?.name?.charAt(0) || 'U' }}
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-[#ffc000]">{{ review.user?.name || 'Pengguna' }}</h4>
                                        <p class="text-xs text-gray-400">{{ formatDate(review.created_at) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1">
                                    <i v-for="s in 5" :key="s" class="fa-solid fa-star text-xs" :class="s <= review.rating ? 'text-[#FFC000]' : 'text-gray-300'"></i>
                                </div>

                                <p class="text-sm text-gray-600 leading-relaxed">
                                    {{ review.comment }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="fa-regular fa-comment-dots text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm font-semibold text-gray-500">Belum ada ulasan untuk aset ini</p>
                        </div>
                    </div>

                </div>

                <!-- KANAN (Booking Card Sticky) -->
                <div class="lg:col-span-1">
                    <div class="sticky top-28 bg-white border border-gray-200 rounded-3xl p-6 shadow-xl space-y-6">

                        <!-- Harga & Rating -->
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-2xl font-extrabold text-[#ffc000]">
                                    {{ lowestPrice ? formatRupiah(lowestPrice.price) : 'Hubungi' }}
                                </span>
                                <span v-if="lowestPrice" class="text-sm text-gray-500 font-normal">
                                    / {{ periodLabel[lowestPrice.period] || 'malam' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-xs font-semibold">
                                <i class="fa-solid fa-star text-[#FFC000]"></i>
                                <span>{{ parseFloat(asset.reviews_avg_rating || 0).toFixed(1) }}</span>
                                <span class="text-gray-400">({{ asset.reviews_count || 0 }})</span>
                            </div>
                        </div>

                        <!-- Selector Pilihan Paket Harga (Jika ada > 1 jenis harga) -->
                        <div v-if="asset.pricings && asset.pricings.length > 1" class="space-y-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Pilih Paket Sewa</label>
                            <select
                                v-model="form.pricing_id"
                                class="w-full text-sm border-gray-300 focus:border-[#ffc000] focus:ring-[#ffc000] rounded-xl py-2.5"
                            >
                                <option v-for="p in asset.pricings" :key="p.id" :value="p.id">
                                    {{ formatRupiah(p.price) }} / {{ periodLabel[p.period] || p.period }}
                                </option>
                            </select>
                        </div>

                        <!-- Form/Box Pilihan Tanggal -->
                        <div class="border border-gray-300 rounded-2xl overflow-hidden divide-y divide-gray-300">
                            <div class="p-3 bg-gray-50/50">
                                <label class="block text-[10px] font-extrabold uppercase text-gray-500 tracking-wider">Mulai Sewa</label>
                                <div class="text-sm font-semibold text-[#ffc000] mt-0.5">
                                    {{ startDate ? startDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Pilih tanggal' }}
                                </div>
                            </div>
                            <div class="p-3 bg-gray-50/50">
                                <label class="block text-[10px] font-extrabold uppercase text-gray-500 tracking-wider">Selesai Sewa</label>
                                <div class="text-sm font-semibold text-[#ffc000] mt-0.5">
                                    {{ endDate ? endDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Pilih tanggal' }}
                                </div>
                            </div>
                        </div>

                        <!-- Tombol CTA Sewa -->
                        <button href="#pilih-kamar" 
                            @click="submitBooking"
                            class="w-full bg-[#ffc000] hover:bg-[#071c30] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span onclick="document.getElementById('pilih-kamar').scrollIntoView({ behavior: 'smooth' });" style="cursor: pointer;">Ajukan Sewa Sekarang</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>

                        <p class="text-center text-xs text-gray-500">
                            Anda belum akan dikenakan biaya pada tahap ini
                        </p>

                        <!-- Total Perhitungan Jika Ada Tanggal Pilih -->
                        <div v-if="nightsCount > 0 && lowestPrice" class="pt-4 border-t border-gray-200 space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>{{ formatRupiah(lowestPrice.price) }} x {{ nightsCount }} malam</span>
                                <span>{{ formatRupiah(lowestPrice.price * nightsCount) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-[#ffc000] pt-2 border-t border-gray-100 text-base">
                                <span>Total estimasi</span>
                                <span>{{ formatRupiah(lowestPrice.price * nightsCount) }}</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

        <!-- BOTTOM BAR FOR MOBILE -->
        <DetailBottomBar
            :price="lowestPrice?.price"
            :period="lowestPrice?.period"
            @book="submitBooking"
        />

    </AppLayout>
</template>

<style scoped>
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.25s ease-in-out;
}

.slide-left-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-left-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-right-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>