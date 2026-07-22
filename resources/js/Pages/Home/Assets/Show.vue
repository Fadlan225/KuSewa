<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import AssetGallery from '@/Components/UI/AssetGallery.vue';
import CircularMonthSlider from '@/Components/UI/CircularMonthSlider.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    serviceFee: {
        type: Number,
        default: 5
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

const formatRupiah = (value) => {
    if (!value) return 'Hubungi Pemilik';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const rentalUnitLabel = (unit) => {
    const labels = {
        hour: "jam",
        day: "hari",
        night: "malam",
        month: "bulan",
    };

    return labels[unit] ?? "sewa";
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
    let date_start = null;
    let date_end = null;

    if (startDate.value) {
        if (activeScheduleMode.value === 'hour') {
            date_start = `${startDate.value.toISOString().split('T')[0]} ${startTime.value}:00`;
            date_end = `${startDate.value.toISOString().split('T')[0]} ${endTime.value}:00`;
        } else if (activeScheduleMode.value === 'month') {
            date_start = startDate.value.toISOString().split('T')[0] + ' 00:00:00';
            if (endDate.value) {
                date_end = endDate.value.toISOString().split('T')[0] + ' 23:59:59';
            }
        } else {
            date_start = startDate.value.toISOString().split('T')[0] + ' 00:00:00';
            if (endDate.value) {
                date_end = endDate.value.toISOString().split('T')[0] + ' 23:59:59';
            }
        }
    }

    const params = {
        pricing_id: form.pricing_id,
        date_start,
        date_end
    };

    router.get(route('booking.create', { asset: props.asset.id }), params);
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

const activeScheduleMode = computed(() => {
    return props.asset.type?.rental_unit || 'day';
});

const startTime = ref('09:00');
const endTime = ref('10:00');
const durationMonths = ref(1);

// Untuk simple date input (v-model native date butuh format YYYY-MM-DD)
const simpleDateString = computed({
    get() {
        if (!startDate.value) return '';
        // Konversi Date ke 'YYYY-MM-DD' di local timezone
        const d = startDate.value;
        return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().split('T')[0];
    },
    set(val) {
        if (!val) {
            startDate.value = null;
        } else {
            startDate.value = new Date(val);
        }
    }
});

const todayString = computed(() => {
    const today = new Date();
    return new Date(today.getTime() - today.getTimezoneOffset() * 60000).toISOString().split('T')[0];
});

const minTime = computed(() => {
    if (simpleDateString.value === todayString.value) {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    }
    return '00:00';
});

watch([startDate, durationMonths], () => {
    if (activeScheduleMode.value === 'month' && startDate.value) {
        const d = new Date(startDate.value);
        d.setMonth(d.getMonth() + durationMonths.value);
        endDate.value = d;
    }
});

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

    if (activeScheduleMode.value === 'hour' || activeScheduleMode.value === 'month') {
        startDate.value = selected;
    } else {
        if (!startDate.value || (startDate.value && endDate.value)) {
            startDate.value = selected;
            endDate.value = null;
        } else if (selected < startDate.value) {
            startDate.value = selected;
        } else if (selected > startDate.value) {
            endDate.value = selected;
        }
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
    if (activeScheduleMode.value === 'hour') return false;
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

const hoursCount = computed(() => {
    if (activeScheduleMode.value !== 'hour') return 0;
    if (!startTime.value || !endTime.value) return 0;
    const [startH, startM] = startTime.value.split(':').map(Number);
    const [endH, endM] = endTime.value.split(':').map(Number);
    const start = new Date(0, 0, 0, startH, startM);
    const end = new Date(0, 0, 0, endH, endM);
    
    if (end <= start) {
        return 0; // Prevent cross-midnight or negative hours
    }
    const diffMs = end - start;
    return Math.ceil(diffMs / (1000 * 60 * 60));
});

const durationCount = computed(() => {
    if (activeScheduleMode.value === 'hour') return hoursCount.value;
    if (activeScheduleMode.value === 'month') return durationMonths.value;
    return nightsCount.value;
});

const subtotal = computed(() => {
    if (!lowestPrice.value) return 0;
    const count = durationCount.value || 1;
    return lowestPrice.value.price * count;
});

const feeAmount = computed(() => {
    return Math.round(subtotal.value * (props.serviceFee / 100));
});

const totalAmount = computed(() => {
    return subtotal.value + feeAmount.value;
});

const formattedDateRange = computed(() => {
    if (!startDate.value) return '';
    const opt = { day: 'numeric', month: 'short', year: 'numeric' };
    const startStr = startDate.value.toLocaleString('id-ID', opt);

    if (activeScheduleMode.value === 'hour') {
        return `${startStr}, ${startTime.value} - ${endTime.value}`;
    }

    if (activeScheduleMode.value === 'month') {
        if (!endDate.value) return startStr;
        const endStr = endDate.value.toLocaleString('id-ID', opt);
        return `${startStr} - ${endStr} (${durationMonths.value} Bln)`;
    }

    if (!endDate.value) return startStr;
    const endStr = endDate.value.toLocaleString('id-ID', opt);
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
                <div class="flex items-center gap-4 pb-8 border-b border-gray-200">
                    <div class="w-14 h-14 rounded-full overflow-hidden shrink-0">
                        <img
                            v-if="asset.owner_profile?.user?.profile_photo"
                            :src="asset.owner_profile.user.profile_photo"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-[#0A2540] text-white font-bold text-lg uppercase tracking-wider"
                        >
                            {{ asset.owner_profile?.user?.name ? asset.owner_profile.user.name.substring(0, 2) : 'AN' }}
                        </div>
                    </div>

                    <div>
                        <h2 class="flex items-center gap-1.5 text-lg font-extrabold text-[#0A2540]">
                            Pemilik Aset : {{ asset.owner_profile?.user?.name || 'Anonim' }}
                            <svg v-if="asset.owner_profile?.status === 'verified'" class="w-[18px] h-[18px] text-green-500" viewBox="0 0 24 24" fill="currentColor" title="Terverifikasi">
                                <path d="M23 11.99l-2.44-2.79.34-3.69-3.61-.82-1.89-3.2-3.4.14-3.4-.14-1.89 3.2-3.61.82.34 3.69L1 11.99l2.44 2.79-.34 3.69 3.61.82 1.89 3.2 3.4-.14 3.4.14 1.89-3.2 3.61-.82-.34-3.69L23 11.99zm-13.06 5.86l-4.59-4.58 1.41-1.41 3.18 3.18 8.18-8.18 1.41 1.41-9.59 9.58z"/>
                            </svg>
                        </h2>
                        <div class="text-sm text-gray-500 mt-0.5 flex flex-wrap items-center gap-x-1">
                            <span>Informasi Kontak</span>
                            <template v-if="asset.owner_profile?.user?.phone">
                                <span class="font-bold text-gray-300">·</span>
                                <span>{{ asset.owner_profile.user.phone }}</span>
                            </template>
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
                        <span v-if="activeScheduleMode === 'hour' && startDate">Jadwal sewa untuk {{ asset.title || 'Aset ini' }}</span>
                        <span v-else-if="activeScheduleMode === 'month' && startDate">{{ durationMonths }} Bulan di {{ asset.title || 'Sini' }}</span>
                        <span v-else-if="nightsCount && activeScheduleMode === 'day'">{{ nightsCount }} malam di {{ asset.title || 'Kota ini' }}</span>
                        <span v-else>Pilih tanggal sewa</span>
                    </h2>
                    <p class="text-sm text-gray-500 mb-8">{{ formattedDateRange }}</p>

                    <div class="bg-white rounded-2xl relative w-full overflow-hidden touch-pan-y" @touchstart.passive="handleTouchStart" @touchend.passive="handleTouchEnd">
                        <!-- Header Bulan (Hanya untuk kalender grid) -->
                        <div v-if="['day', 'night'].includes(activeScheduleMode)" class="flex justify-between items-center mb-10 px-2 pt-6">
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

                        <!-- Grid Kalender (Hanya untuk Day/Night) -->
                        <div v-if="['day', 'night'].includes(activeScheduleMode)" class="relative overflow-hidden min-h-[280px]">
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

                        <!-- UI KHUSUS HOUR (Jam) -->
                        <div v-if="activeScheduleMode === 'hour'" class="pt-6">
                            <div class="mb-6">
                                <label class="block text-sm font-bold text-[#0A2540] mb-2">Tanggal Sewa</label>
                                <input type="date" :min="todayString" v-model="simpleDateString" class="w-full sm:w-1/2 border border-gray-200 rounded-xl p-3 text-[#0A2540] font-bold text-sm bg-gray-50 focus:bg-white transition outline-none focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000]" />
                            </div>

                            <h4 class="text-sm font-bold text-[#0A2540] mb-4">Tentukan Waktu (Jam)</h4>
                            <div class="flex items-center gap-4 max-w-md">
                                <div class="flex-1">
                                    <label class="block text-[11px] font-bold text-[#6C757D] mb-1.5 uppercase tracking-wider">Mulai</label>
                                    <div class="relative">
                                        <input v-model="startTime" type="time" :min="minTime" class="w-full border border-gray-200 rounded-xl p-3 pr-10 text-[#0A2540] font-bold text-base bg-gray-50 focus:bg-white transition outline-none focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:right-0 [&::-webkit-calendar-picker-indicator]:w-8 [&::-webkit-calendar-picker-indicator]:h-full [&::-webkit-calendar-picker-indicator]:cursor-pointer" />
                                        <i class="fa-regular fa-clock text-lg absolute right-3 top-1/2 -translate-y-1/2 text-[#0A2540] pointer-events-none"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-[11px] font-bold text-[#6C757D] mb-1.5 uppercase tracking-wider">Selesai</label>
                                    <div class="relative">
                                        <input v-model="endTime" type="time" :min="startTime" class="w-full border border-gray-200 rounded-xl p-3 pr-10 text-[#0A2540] font-bold text-base bg-gray-50 focus:bg-white transition outline-none focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:right-0 [&::-webkit-calendar-picker-indicator]:w-8 [&::-webkit-calendar-picker-indicator]:h-full [&::-webkit-calendar-picker-indicator]:cursor-pointer" />
                                        <i class="fa-regular fa-clock text-lg absolute right-3 top-1/2 -translate-y-1/2 text-[#0A2540] pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- UI KHUSUS MONTH (Bulan) -->
                        <div v-if="activeScheduleMode === 'month'" class="pt-6">
                            <div class="mb-8">
                                <label class="block text-sm font-bold text-[#0A2540] mb-2">Mulai Dari Tanggal</label>
                                <input type="date" :min="todayString" v-model="simpleDateString" class="w-full sm:w-1/2 border border-gray-200 rounded-xl p-3 text-[#0A2540] font-bold text-sm bg-gray-50 focus:bg-white transition outline-none focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000]" />
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-[#6C757D] mb-2 text-center">Durasi Sewa (Bulan)</label>
                                <CircularMonthSlider v-model="durationMonths" />
                            </div>
                            <div v-if="endDate" class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                                <span class="text-sm text-gray-500 font-medium">Tanggal Selesai :</span>
                                <span class="text-sm font-bold text-[#0A2540]">
                                    {{ endDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                </span>
                            </div>
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
                <div class="sticky top-24 bg-white shadow-2xl shadow-gray-200/50 rounded-2xl p-6 border border-gray-200">
                    <!-- Price Header -->
                    <div class="flex items-end gap-1 mb-6">
                        <span class="text-2xl font-extrabold text-[#0A2540]">{{ formatRupiah(lowestPrice?.price) }}</span>
                        <span class="text-gray-500 mb-1">/{{ rentalUnitLabel(asset.type?.rental_unit) }}</span>
                    </div>

                    <!-- Date & Duration Box -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden mb-6">
                        <div class="flex border-b border-gray-200">
                            <!-- Mulai -->
                            <div class="flex-1 p-3 border-r border-gray-200">
                                <p class="text-[10px] uppercase font-bold text-gray-500 mb-1">Mulai Sewa</p>
                                <p class="text-sm font-bold text-[#0A2540]">{{ startDate ? startDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Pilih Tanggal' }}</p>
                                <p class="text-xs text-gray-500 mt-0.5" v-if="activeScheduleMode === 'hour'">{{ startTime }}</p>
                            </div>
                            <!-- Selesai -->
                            <div class="flex-1 p-3">
                                <p class="text-[10px] uppercase font-bold text-gray-500 mb-1">Selesai Sewa</p>
                                <p class="text-sm font-bold text-[#0A2540]">{{ (activeScheduleMode === 'hour' && startDate) ? startDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : (endDate ? endDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-') }}</p>
                                <p class="text-xs text-gray-500 mt-0.5" v-if="activeScheduleMode === 'hour'">{{ endTime }}</p>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 flex justify-between items-center">
                            <span class="text-xs font-semibold text-gray-600">Durasi Sewa</span>
                            <span class="text-sm font-bold" :class="durationCount === 0 ? 'text-red-500' : 'text-[#0A2540]'">{{ durationCount || 0 }} {{ rentalUnitLabel(asset.type?.rental_unit) }}</span>
                        </div>
                    </div>

                    <button
                        @click="submitBooking"
                        :disabled="asset.status !== 'active' || !asset.pricings.length || !startDate || durationCount === 0"
                        class="w-full py-4 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-extrabold rounded-xl transition-all shadow-lg shadow-[#FFC000]/20 flex justify-center items-center gap-2 text-lg disabled:opacity-50 disabled:cursor-not-allowed mb-4">
                        Pesan Sekarang
                    </button>

                    <p v-if="asset.status !== 'active'" class="text-center text-red-500 text-xs font-bold mb-4">Aset ini sedang tidak tersedia.</p>

                    <!-- Breakdown -->
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-semibold text-[#0A2540]">{{ formatRupiah(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Biaya Layanan ({{ serviceFee }}%)</span>
                            <span class="font-semibold text-[#0A2540]">{{ formatRupiah(feeAmount) }}</span>
                        </div>
                        <hr class="border-gray-200">
                        <div class="flex justify-between font-extrabold text-base text-[#0A2540]">
                            <span>Total</span>
                            <span>{{ formatRupiah(totalAmount) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- CUSTOM BOTTOM BAR (MOBILE ONLY) -->
    <DetailBottomBar
        :price="totalAmount || lowestPrice?.price || 0"
        :durationCount="durationCount"
        :durationLabel="rentalUnitLabel(asset.type?.rental_unit)"
        :formattedDateRange="formattedDateRange"
        :periodLabel="rentalUnitLabel(asset.type?.rental_unit)"
        :disabled="asset.status !== 'active' || !asset.pricings.length || !startDate || durationCount === 0"
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
