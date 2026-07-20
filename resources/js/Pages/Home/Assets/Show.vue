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

// Form Booking (persiapan)
const form = useForm({
    asset_id: props.asset.id,
    pricing_id: lowestPrice.value ? lowestPrice.value.id : null,
});

const submitBooking = () => {
    form.get(route('booking.create', { asset: props.asset.id })); // Sesuaikan dengan route Anda nanti
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
// KALENDER SEWA (Sama seperti Search/Filter)
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

</script>

<template>
    <Head :title="asset.title || 'Detail Aset'" />

    <AppLayout :hideNavbar="true" :hideBottombar="true">

    <!-- CUSTOM STICKY NAVBAR -->
    <DetailNavbar :isFavorited="asset.isFavorite" @favorite="handleFavorite" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-[#0A2540] font-sans pb-32 lg:pb-10">

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
                    <span class="text-[#0A2540] font-bold">{{ parseFloat(asset.reviews_avg_rating || 0).toFixed(1) }}</span>
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

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
                            {{ asset.owner_profile.user.phone }}
                        </p>
                    </div>

                    <div class="w-14 h-14 rounded-full overflow-hidden shrink-0">
                        <img
                            v-if="asset.owner_profile?.user?.profile_photo"
                            :src="asset.owner_profile.user.profile_photo"
                            class="w-full h-full object-cover"
                        />

                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-[#0A2540] text-white font-bold text-xl"
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
                            <span class="font-bold text-[#0A2540]">{{ specification[key] }}</span>
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
                            <i class="fa-solid fa-check text-green-500"></i>
                            <span class="capitalize">{{ fac }}</span>
                        </div>
                    </div>
                </div>

                <!-- Lokasi Map Placeholder -->
                <div id="lokasi" class="py-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-4">Lokasi</h3>
                    <p class="text-gray-600 mb-4">{{ asset.address }}, {{ asset.city }}, {{ asset.province }}</p>
                    <div class="w-full h-64 bg-gray-200 rounded-xl overflow-hidden relative flex items-center justify-center">
                        <div class="absolute inset-0 bg-cover bg-center opacity-40" style="background-image: url('https://map.viamichelin.com/map/carte?map=viamichelin&z=10&lat=-0.502&lon=117.153&width=800&height=400&format=png&version=latest&layer=background')"></div>
                        <div class="z-10 flex flex-col items-center bg-white/90 p-4 rounded-xl shadow-lg">
                            <i class="fa-solid fa-location-dot text-red-500 text-3xl mb-2"></i>
                            <span class="font-bold">Peta belum diintegrasikan</span>
                        </div>
                    </div>
                </div>

                <!-- SEKSI PEMILIHAN TANGGAL (KALENDER) -->
                <div class="pb-10 border-b border-gray-200">
                    <h2 class="text-2xl font-extrabold text-[#0A2540] mb-1">
                        {{ nightsCount ? `${nightsCount} malam di ${asset.title || 'Kota ini'}` : 'Pilih tanggal sewa' }}
                    </h2>
                    <p class="text-sm text-gray-500 mb-8">{{ formattedDateRange }}</p>

                    <div class="bg-white rounded-2xl relative w-full overflow-hidden touch-pan-y" @touchstart.passive="handleTouchStart" @touchend.passive="handleTouchEnd">
                        <!-- Header Bulan -->
                        <div class="flex justify-between items-center mb-10 px-2 pt-6">
                            <button @click="prevMonth" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition" :class="calendarPage === 0 ? 'opacity-30 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-chevron-left text-[#0A2540] text-sm"></i>
                            </button>
                            <div class="flex gap-8 w-full px-4">
                                <h3 class="flex-1 text-center text-[15px] font-bold text-[#0A2540]">{{ monthsData[calendarPage]?.title }}</h3>
                                <h3 class="flex-1 text-center text-[15px] font-bold text-[#0A2540] hidden sm:block">{{ monthsData[calendarPage + 1]?.title }}</h3>
                            </div>
                            <button @click="nextMonth" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                                <i class="fa-solid fa-chevron-right text-[#0A2540] text-sm"></i>
                            </button>
                        </div>

                        <!-- Grid Kalender -->
                        <div class="relative overflow-hidden min-h-[280px]">
                            <transition :name="transitionName" mode="out-in">
                                <div :key="calendarPage" class="flex gap-12 sm:px-4 w-full">
                                    <!-- Kalender Bulan Kiri -->
                                    <div class="flex-1">
                                        <div class="grid grid-cols-7 gap-y-6 mb-1">
                                            <div v-for="day in daysOfWeek" :key="'d1-'+day" class="text-center text-[11px] font-bold text-[#6C757D]">{{ day }}</div>
                                            <div v-for="i in monthsData[calendarPage]?.emptyDaysStart" :key="'e1-'+i"></div>
                                            <div v-for="date in monthsData[calendarPage]?.daysInMonth" :key="'d1-'+date" class="relative flex justify-center items-center h-10">

                                                <!-- KONEKTOR RENTANG -->
                                                <div v-if="isStartDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isInRange(monthsData[calendarPage].year, monthsData[calendarPage].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isEndDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                                <!-- BULATAN TANGGAL -->
                                                <div class="relative z-10 w-10 h-10 flex flex-col items-center justify-center rounded-full text-[13px] font-bold transition"
                                                    :class="[
                                                        isPastDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date) ? 'text-gray-300 cursor-not-allowed line-through' : 'cursor-pointer hover:border hover:border-[#1A1A1A]',
                                                        { 'bg-[#1A1A1A] text-white shadow-md': isStartDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date) || isEndDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date),
                                                          'text-[#1A1A1A]': isInRange(monthsData[calendarPage].year, monthsData[calendarPage].month, date),
                                                          'text-[#0A2540]': !isStartDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && !isEndDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && !isInRange(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && !isPastDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date) }
                                                    ]"
                                                    @click="!isPastDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && selectDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date)">
                                                    <span>{{ date }}</span>
                                                </div>

                                                <!-- TANDA MULAI & SELESAI -->
                                                <div v-if="isStartDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date)" class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                                <div v-else-if="isEndDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date)" class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kalender Bulan Kanan (Hanya Desktop) -->
                                    <div class="flex-1 hidden sm:block">
                                        <div class="grid grid-cols-7 gap-y-6 mb-1">
                                            <div v-for="day in daysOfWeek" :key="'d2-'+day" class="text-center text-[11px] font-bold text-[#6C757D]">{{ day }}</div>
                                            <div v-for="i in monthsData[calendarPage + 1]?.emptyDaysStart" :key="'e2-'+i"></div>
                                            <div v-for="date in monthsData[calendarPage + 1]?.daysInMonth" :key="'d2-'+date" class="relative flex justify-center items-center h-10">

                                                <!-- KONEKTOR RENTANG -->
                                                <div v-if="isStartDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isInRange(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isEndDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                                <!-- BULATAN TANGGAL -->
                                                <div class="relative z-10 w-10 h-10 flex flex-col items-center justify-center rounded-full text-[13px] font-bold transition"
                                                    :class="[
                                                        isPastDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) ? 'text-gray-300 cursor-not-allowed line-through' : 'cursor-pointer hover:border hover:border-[#1A1A1A]',
                                                        { 'bg-[#1A1A1A] text-white shadow-md': isStartDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) || isEndDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date),
                                                          'text-[#1A1A1A]': isInRange(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date),
                                                          'text-[#0A2540]': !isStartDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && !isEndDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && !isInRange(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && !isPastDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) }
                                                    ]"
                                                    @click="!isPastDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && selectDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)">
                                                    <span>{{ date }}</span>
                                                </div>

                                                <!-- TANDA MULAI & SELESAI -->
                                                <div v-if="isStartDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)" class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                                <div v-else-if="isEndDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)" class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- Tombol Kosongkan Tanggal -->
                        <div class="mt-8 mb-6 mr-2 flex justify-end">
                            <button @click="clearDates" class="text-sm font-bold text-[#0A2540] hover:underline underline-offset-2 transition-all px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg">
                                Kosongkan tanggal
                            </button>
                        </div>
                    </div>
                </div>

            <!-- SEKSI ULASAN -->
            <div id="ulasan" class="mt-12 mb-10">
                <!-- Judul Seksi -->
                <div class="mb-6">
                    <span class="text-primary font-extrabold text-[11px] tracking-widest uppercase">
                        Kepuasan Pelanggan
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-secondary mt-1">
                        Apa Kata Mereka?
                    </h2>
                </div>

                <!-- Container Utama: Summary & Daftar Ulasan -->
                <div class="flex flex-col gap-8">

                    <!-- CARD SUMMARY (Rating Keseluruhan) -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-xl flex flex-col sm:flex-row items-center sm:items-stretch gap-6 sm:gap-0">

                        <!-- Sisi Kiri: Rata-rata -->
                        <div class="flex flex-col items-center justify-center sm:pr-8 sm:border-r border-gray-100 min-w-[150px]">
                            <!-- Tampilkan rating atau strip jika belum ada ulasan -->
                            <span class="text-5xl font-black text-[#0A2540] tracking-tighter">
                                {{ asset.reviews_avg_rating ? parseFloat(asset.reviews_avg_rating).toFixed(1) : '-' }}
                            </span>

                            <!-- Bintang Rata-rata -->
                            <div class="flex items-center gap-1 mt-3">
                                <i v-for="i in 5" :key="i"
                                class="fa-solid fa-star text-sm"
                                :class="i <= Math.round(asset.reviews_avg_rating || 0) ? 'text-[#FFC000]' : 'text-gray-200'">
                                </i>
                            </div>

                            <span class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-wider">
                                {{ asset.reviews_count || 0 }} Penilaian
                            </span>
                        </div>

                        <!-- Sisi Kanan: Progress Bar Breakdown -->
                        <div class="flex-grow sm:pl-8 flex flex-col justify-center gap-2 w-full">
                            <div v-for="item in reviewDistribution" :key="item.star" class="flex items-center gap-3 text-sm">
                                <div class="flex items-center gap-1 w-8 justify-end text-gray-500 font-medium text-xs">
                                    {{ item.star }} <i class="fa-solid fa-star text-[#FFC000] text-[10px]"></i>
                                </div>

                                <!-- Progress Bar dinamis -->
                                <div class="flex-grow h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#FFC000] rounded-full transition-all duration-500" :style="{ width: item.percentage + '%' }"></div>
                                </div>

                                <!-- Jumlah ulasan per bintang -->
                                <div class="w-4 text-xs font-medium text-gray-400 text-right">{{ item.count }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="asset.reviews && asset.reviews.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Card Individual Ulasan -->
                        <div v-for="review in asset.reviews" :key="review.id" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                            <!-- Header Card Ulasan: Profil & Bintang -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#0A2540] flex items-center justify-center text-white font-bold overflow-hidden shrink-0">
                                        <!-- Inisial Nama atau Foto -->
                                        <img v-if="review.user?.profile_photo" :src="review.user.profile_photo" class="w-full h-full object-cover" />
                                        <span v-else>{{ review.user?.name?.charAt(0) || 'U' }}</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#0A2540] text-sm">{{ review.user?.name || 'Anonim' }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(review.created_at) }}</p>
                                    </div>
                                </div>
                                <!-- Bintang Ulasan User -->
                                <div class="flex gap-0.5">
                                    <i v-for="i in 5" :key="i" class="fa-solid fa-star text-[11px]" :class="i <= review.rating ? 'text-[#FFC000]' : 'text-gray-200'"></i>
                                </div>
                            </div>

                            <!-- Teks Ulasan -->
                            <p class="text-sm text-gray-600 leading-relaxed">
                                "{{ review.review }}"
                            </p>
                        </div>

                    </div>

                    <!-- EMPTY STATE (Jika belum ada ulasan) -->
                    <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                            <i class="fa-solid fa-star text-2xl text-gray-200"></i>
                        </div>
                        <h3 class="text-[#0A2540] font-bold text-lg mb-1">Belum Ada Ulasan</h3>
                        <p class="text-sm text-gray-500">Jadilah yang pertama memberikan ulasan!</p>
                    </div>

                </div>
            </div>

            </div>

            <!-- KANAN (Booking Sticky Card) -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 bg-white border border-gray-200 shadow-2xl shadow-gray-200/50 rounded-2xl p-6">
                    <!-- Header Harga Card -->
                    <div class="flex items-end gap-1 mb-6">
                        <span class="text-2xl font-extrabold text-[#0A2540]">{{ formatRupiah(lowestPrice?.price) }}</span>
                        <span class="text-gray-500 mb-1">/ {{ lowestPrice ? periodLabel[lowestPrice.period] : 'opsi' }}</span>
                    </div>

                    <!-- Pilihan Harga Lain -->
                    <div v-if="asset.pricings && asset.pricings.length > 0" class="mb-6 space-y-2">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Opsi Sewa Tersedia</h4>
                        <label v-for="price in asset.pricings" :key="price.id"
                               class="flex justify-between items-center p-3 rounded-xl border-2 cursor-pointer transition-all"
                               :class="form.pricing_id === price.id ? 'border-[#FFC000] bg-[#FFC000]/5' : 'border-gray-100 hover:border-gray-300'">
                            <div class="flex items-center gap-3">
                                <input type="radio" :value="price.id" v-model="form.pricing_id" class="w-4 h-4 text-[#FFC000] focus:ring-[#FFC000]" />
                                <span class="text-sm font-semibold capitalize">{{ periodLabel[price.period] }}</span>
                            </div>
                            <span class="text-sm font-bold">{{ formatRupiah(price.price) }}</span>
                        </label>
                    </div>

                    <!-- Tombol Pesan -->
                    <button
                        @click="submitBooking"
                        :disabled="asset.status !== 'active' || !asset.pricings.length"
                        class="w-full py-4 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-extrabold rounded-xl transition-all shadow-lg shadow-[#FFC000]/20 flex justify-center items-center gap-2 text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Pesan Sekarang
                    </button>

                    <p v-if="asset.status !== 'active'" class="text-center text-red-500 text-xs font-bold mt-3">Aset ini sedang tidak tersedia.</p>
                    <p class="text-center text-gray-400 text-xs mt-4">Anda belum dikenakan biaya apapun.</p>

                    <hr class="my-6 border-gray-100" />

                    <!-- Ringkasan Info (Opsional untuk card) -->
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span class="underline">Hubungi Pemilik</span>
                        <i class="fa-solid fa-message"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- CUSTOM BOTTOM BAR (MOBILE ONLY) -->
    <DetailBottomBar 
        :price="lowestPrice?.price || 0" 
        :nightsCount="nightsCount" 
        :formattedDateRange="formattedDateRange"
        :periodLabel="lowestPrice ? periodLabel[lowestPrice.period] : 'opsi'"
        :disabled="asset.status !== 'active' || !asset.pricings.length"
        @submit="submitBooking"
    />

    </AppLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Calendar Animations */
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
    transition: all 0.2s ease-out;
}
.slide-left-enter-from { opacity: 0; transform: translateX(30px); }
.slide-left-leave-to { opacity: 0; transform: translateX(-30px); }
.slide-right-enter-from { opacity: 0; transform: translateX(-30px); }
.slide-right-leave-to { opacity: 0; transform: translateX(30px); }
</style>
