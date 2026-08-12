<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import AssetGallery from '@/Components/UI/AssetGallery.vue';
import AssetUnitList from '@/Components/UI/AssetUnitList.vue';
import AssetHostProfile from '@/Components/UI/AssetHostProfile.vue';
import AssetSpecifications from '@/Components/UI/AssetSpecifications.vue';
import AssetReviews from '@/Components/UI/AssetReviews.vue';
import AssetFaq from '@/Components/UI/AssetFaq.vue';
import AssetPolicy from '@/Components/UI/AssetPolicy.vue';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { Indonesian } from "flatpickr/dist/l10n/id.js";
import { useHomeSearch } from '@/Composables/useHomeSearch';
import StickySubNavSearch from '@/Components/UI/StickySubNavSearch.vue';
import Navbar from '@/Components/Navbar.vue';
import MobileSearchSheet from '@/Pages/Home/Search/MobileSearchSheet.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    serviceFee: {
        type: [Object, Number],
        default: () => ({ type: 'percentage', value: 5 })
    },
    assetView: {
        type: Object,
        default: null,
    },
    bookedDates: {
        type: Array,
        default: () => []
    }
});

const page = usePage();



const handleFavorite = async () => {
    if (!page.props.auth?.user) {
        window.location.href = '/login';
        return;
    }

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
    // 'year' tidak ada di enum database, dihapus (M7)
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Menghitung harga termurah untuk ditampilkan di card booking
const lowestPrice = computed(() => {
    let allPricings = [];
    if (props.asset.pricings && props.asset.pricings.length > 0) {
        allPricings = props.asset.pricings;
    } else if (props.asset.units && props.asset.units.length > 0) {
        props.asset.units.forEach(unit => {
            if (unit.pricings) {
                allPricings = allPricings.concat(unit.pricings);
            }
        });
    }

    if (allPricings.length === 0) return null;
    return allPricings.reduce((min, p) => p.price < min.price ? p : min, allPricings[0]);
});

import FasilitasModal from './Fasilitas.vue';

// ── Fasilitas & Spesifikasi ────────────────────────────────────────────────
// Sistem BARU: fasilitas dari relasi belongsToMany (asset_facilities pivot)
const assetFacilities = computed(() => props.asset.facilities || []);

const showFasilitasModal = ref(false);
const showFullDescription = ref(false);

const topFacilities = computed(() => {
    let flat = [];
    assetFacilities.value.forEach(f => {
        flat.push(f);
    });
    return flat.slice(0, 10);
});

// Grup fasilitas per kategori untuk tampilan yang rapi
const facilitiesGrouped = computed(() => {
    const groups = {};
    assetFacilities.value.forEach(f => {
        const catName = f.category?.name || 'Lainnya';
        if (!groups[catName]) groups[catName] = { name: catName, icon: f.category?.icon || 'list', facilities: [] };
        groups[catName].facilities.push(f);
    });
    return Object.values(groups);
});



const selectedUnitId = ref(null);

// Form Booking (persiapan)
// K5: Untuk single asset, inisialisasi pricing_id ke harga termurah agar ada default terpilih
const getDefaultPricingId = () => {
    if (props.asset.units && props.asset.units.length > 0) return null;
    if (!props.asset.pricings || props.asset.pricings.length === 0) return null;
    return [...props.asset.pricings].sort((a, b) => a.price - b.price)[0]?.id ?? null;
};

const form = useForm({
    asset_id: props.asset.id,
    pricing_id: getDefaultPricingId(),
    asset_unit_id: null,
});

const showDateError = ref(false);

const submitBooking = () => {
    // Mencegah booking jika memiliki unit tapi belum pilih unit
    if (props.asset.units && props.asset.units.length > 0 && !form.pricing_id) {
        document.getElementById('pilihan-unit')?.scrollIntoView({ behavior: 'smooth' });
        return;
    }

    // BUG 2 FIX: Untuk aset TANPA unit, otomatis ambil pricing_id dari lowestPrice
    if ((!props.asset.units || props.asset.units.length === 0) && !form.pricing_id) {
        if (lowestPrice.value?.id) {
            form.pricing_id = lowestPrice.value.id;
        } else {
            // Tidak ada pricing sama sekali, tidak bisa booking
            alert('Aset ini belum memiliki harga sewa yang tersedia.');
            return;
        }
    }

    let date_start = null;
    let date_end = null;

    // Helper to safely format local date as YYYY-MM-DD
    const toLocalDateStr = (d) => {
        if (!d) return null;
        return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().split('T')[0];
    };

    if (startDate.value) {
        if (activeScheduleMode.value === 'hour') {
            date_start = `${toLocalDateStr(startDate.value)} ${startTime.value}:00`;
            date_end = `${toLocalDateStr(startDate.value)} ${endTime.value}:00`;
        } else if (activeScheduleMode.value === 'month') {
            date_start = toLocalDateStr(startDate.value) + ' 00:00:00';
            if (endDate.value) {
                date_end = toLocalDateStr(endDate.value) + ' 23:59:59';
            }
        } else {
            date_start = toLocalDateStr(startDate.value) + ' 00:00:00';
            if (endDate.value) {
                date_end = toLocalDateStr(endDate.value) + ' 23:59:59';
            }
        }
    }

    const params = {
        pricing_id: form.pricing_id,
        date_start,
        date_end,
        duration: durationCount.value,
        rental_mode: activeScheduleMode.value
    };

    router.get(route('booking.create', { asset: props.asset.id }), params);
};

const handleUnitSelect = ({ unit_id, pricing_id, price }) => {
    form.pricing_id = pricing_id;
    form.asset_unit_id = unit_id;
    selectedUnitId.value = unit_id;

    // Validasi tanggal jika belum diisi atau tidak ada durasi
    if (!startDate.value || durationCount.value === 0) {
        showDateError.value = true;
        const calendarEl = document.getElementById('kalender-sewa');
        if (calendarEl) {
            calendarEl.scrollIntoView({ behavior: 'smooth' });
        }
        return;
    }

    // Biarkan pengguna tetap di posisinya (tidak scroll otomatis ke atas)
};

const handleBottomBarSubmit = () => {
    if (props.asset.units && props.asset.units.length > 0) {
        document.getElementById('pilihan-unit')?.scrollIntoView({ behavior: 'smooth' });
    } else {
        submitBooking();
    }
};



// ==========================================
// KALENDER SEWA (Terhubung dengan Global State)
// ==========================================

const {
    searchQuery, selectedAssets,
    startDate, endDate, startTime, endTime,
    durationMonths, activeScheduleMode, simpleDateString,
    minPrice, maxPrice, parsedMinPrice, parsedMaxPrice,
    maxLimit, formatPriceShort,
    activeSearchStep, isMobileSearchOpen
} = useHomeSearch();

const openMobileSearch = (step) => {
    activeSearchStep.value = step;
    isMobileSearchOpen.value = true;
};

onMounted(() => {
    // Auto-fill global search state when entering detail page
    if (props.asset.city?.name || props.asset.address) {
        searchQuery.value = props.asset.city?.name || props.asset.address || '';
    }
    if (props.asset.type?.name) {
        selectedAssets.value = [props.asset.type.name];
    }

    // Set default rental mode to asset's default if not set
    if (!startDate.value) {
        activeScheduleMode.value = props.asset.type?.rental_unit || 'day';
    }

    // Auto-fill price to match this asset's price
    let assetMinPrice = null;
    let assetMaxPrice = null;

    if (props.asset.units && props.asset.units.length > 0) {
        let allPricings = [];
        props.asset.units.forEach(unit => {
            if (unit.pricings) allPricings = allPricings.concat(unit.pricings);
        });
        if (allPricings.length > 0) {
            assetMinPrice = Math.min(...allPricings.map(p => p.price));
            assetMaxPrice = Math.max(...allPricings.map(p => p.price));
        }
    } else if (props.asset.pricings && props.asset.pricings.length > 0) {
        assetMinPrice = Math.min(...props.asset.pricings.map(p => p.price));
        assetMaxPrice = assetMinPrice;
    }

    if (assetMinPrice !== null && assetMaxPrice !== null) {
        minPrice.value = assetMinPrice;
        maxPrice.value = assetMaxPrice;
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

watch([startDate, durationMonths], ([newStart, newDuration]) => {
    if (activeScheduleMode.value === 'month' && newStart) {
        // Validasi: apakah startDate yang dipilih + durasi baru akan overlap dengan booking yang ada?
        if (props.bookedDates && props.bookedDates.length > 0) {
            const checkStart = new Date(newStart);
            checkStart.setHours(0, 0, 0, 0);
            const checkEnd = new Date(checkStart);
            checkEnd.setMonth(checkEnd.getMonth() + (newDuration || 1));
            checkEnd.setHours(23, 59, 59, 0);

            const isNowInvalid = props.bookedDates.some(booked => {
                const from = new Date(booked.from);
                from.setHours(0, 0, 0, 0);
                const to = new Date(booked.to);
                to.setHours(23, 59, 59, 0);
                return checkStart <= to && checkEnd >= from;
            });

            if (isNowInvalid) {
                // Tanggal yang dipilih tidak lagi valid dengan durasi baru — reset
                startDate.value = null;
                endDate.value = null;
                return;
            }
        }

        // Hitung & set end date berdasarkan start + durasi
        const d = new Date(newStart);
        d.setMonth(d.getMonth() + (newDuration || 1));
        endDate.value = d;
    }

    // Hilangkan error jika user sudah mulai memilih tanggal
    if (newStart) {
        showDateError.value = false;
    }
});

const flatpickrConfig = computed(() => {
    // Akses durationMonths.value di sini secara langsung agar Vue melacak dependency-nya!
    // Jika diakses di dalam closure, Vue TIDAK akan melacaknya.
    const currentDuration = durationMonths.value || 1;
    const bookedRanges = props.bookedDates || [];

    const disableFn = (date) => {
        if (bookedRanges.length === 0) return false;

        // Buat salinan, jangan pernah mutasi objek `date` dari flatpickr!
        const checkStart = new Date(date);
        checkStart.setHours(0, 0, 0, 0);

        if (activeScheduleMode.value === 'month') {
            // Mode bulan: hitung end date berdasarkan durasi, lalu cek overlap
            const checkEnd = new Date(checkStart);
            checkEnd.setMonth(checkEnd.getMonth() + currentDuration);
            checkEnd.setHours(23, 59, 59, 0);

            return bookedRanges.some(booked => {
                const from = new Date(booked.from);
                from.setHours(0, 0, 0, 0);
                const to = new Date(booked.to);
                to.setHours(23, 59, 59, 0);
                // Overlap: A mulai sebelum B selesai DAN A selesai setelah B mulai
                return checkStart <= to && checkEnd >= from;
            });
        }

        // Mode jam (hour): cek apakah tanggal itu sendiri berada dalam range booking
        return bookedRanges.some(booked => {
            const from = new Date(booked.from);
            from.setHours(0, 0, 0, 0);
            const to = new Date(booked.to);
            to.setHours(0, 0, 0, 0);
            return checkStart >= from && checkStart <= to;
        });
    };

    const firstAvailableDate = new Date();
    firstAvailableDate.setHours(0, 0, 0, 0);

    let foundAvailable = false;
    for (let i = 0; i < 365 * 3; i++) { // Cari hingga 3 tahun ke depan
        if (!disableFn(firstAvailableDate)) {
            foundAvailable = true;
            break;
        }
        firstAvailableDate.setDate(firstAvailableDate.getDate() + 1);
    }

    return {
        disable: [disableFn],
        minDate: "today",
        locale: Indonesian,
        altInput: true,
        altFormat: "d M Y",
        dateFormat: "Y-m-d",
        disableMobile: "true",
        onOpen: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 0 && foundAvailable) {
                instance.jumpToDate(firstAvailableDate);
            }
        }
    };
});

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

// K3: Hapus priceMultiplier — kalkulasi ×30 (malam→bulan) sudah usang sejak model paket harga.
// Harga paket adalah harga flat, tidak dikalikan durasi.
const activePrice = computed(() => {
    if (form.pricing_id) {
        let found = null;
        if (props.asset.units && props.asset.units.length > 0) {
            props.asset.units.forEach(unit => {
                const p = unit.pricings?.find(pr => pr.id === form.pricing_id);
                if (p) found = p;
            });
        } else if (props.asset.pricings && props.asset.pricings.length > 0) {
            found = props.asset.pricings.find(p => p.id === form.pricing_id);
        }
        if (found) return found;
    }
    return lowestPrice.value;
});

// K2: subtotal = harga paket flat (tidak dikali durasi), konsisten dengan Booking.vue
const subtotal = computed(() => {
    if (!activePrice.value) return 0;
    return activePrice.value.price;
});

const feeAmount = computed(() => {
    if (props.serviceFee && typeof props.serviceFee === 'object') {
        if (props.serviceFee.type === 'fixed') {
            return Number(props.serviceFee.value);
        }
        return Math.round(subtotal.value * (Number(props.serviceFee.value) / 100));
    }
    // Fallback if passed as simple number
    return Math.round(subtotal.value * (Number(props.serviceFee || 5) / 100));
});

const totalAmount = computed(() => {
    return subtotal.value + feeAmount.value;
});

const selectedUnitName = computed(() => {
    if (!selectedUnitId.value || !props.asset.units) return null;
    const unit = props.asset.units.find(u => u.id === selectedUnitId.value);
    return unit ? unit.name : null;
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


</script>

<template>
    <Head :title="asset.title || 'Detail Aset'" />

    <AppLayout :hideNavbar="true" :hideBottombar="true">

    <MobileSearchSheet />

    <!-- NATURAL SCROLLING MAIN NAVBAR -->
    <Navbar class="hidden md:block !absolute top-0 left-0 w-full z-[80] !transition-none" />

    <!-- UNIFIED STICKY HEADER (Filters Top, Nav Bottom) -->
    <div class="md:mt-16 sticky top-0 z-[70] w-full flex flex-col bg-white shadow-sm border-b border-gray-100">
        <!-- TOP: SEARCH FILTER NAVBAR -->
        <div class="hidden md:block">
            <StickySubNavSearch class="!shadow-none !border-b-0 !static !bg-transparent !py-2" />
        </div>

        <!-- BOTTOM: CUSTOM STICKY NAVBAR -->
        <DetailNavbar :isFavorited="asset.isFavorite" @favorite="handleFavorite" :showBackButton="true" :mobileBackOnly="true" class="!shadow-none !border-b-0 !static !bg-transparent" />

        <!-- MOBILE SUB NAVBAR (Badges for Schedule and Price) -->
        <div class="flex md:hidden items-center gap-2 px-4 pb-3 overflow-x-auto hide-scrollbar">
            <div @click="openMobileSearch('jenis')" class="bg-gray-100 text-[#0A2540] text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition flex-shrink-0">
                <i class="fa-solid fa-layer-group text-gray-500"></i>
                {{ selectedAssets.length ? selectedAssets.join(', ') : (asset.type?.name || 'Semua Tipe') }}
            </div>
            <div @click="openMobileSearch('lokasi')" class="bg-gray-100 text-[#0A2540] text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition flex-shrink-0">
                <i class="fa-solid fa-location-dot text-gray-500"></i>
                {{ searchQuery || asset.city?.name || 'Pilih Lokasi' }}
            </div>
            <div @click="openMobileSearch('jadwal')" class="bg-gray-100 text-[#0A2540] text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition flex-shrink-0">
                <i class="fa-regular fa-calendar text-gray-500"></i>
                {{ formattedDateRange || 'Pilih Jadwal' }}
            </div>
            <div @click="openMobileSearch('harga')" class="bg-gray-100 text-[#0A2540] text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition flex-shrink-0">
                <i class="fa-solid fa-rupiah-sign text-gray-500"></i>
                {{ (parsedMinPrice > 0 || parsedMaxPrice < maxLimit) ? (formatPriceShort(parsedMinPrice) + ' - ' + formatPriceShort(parsedMaxPrice)) : 'Batas Harga' }}
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-[#0A2540] font-sans pb-32 lg:pb-10">

        <!-- HERO GALLERY AND MODAL -->
        <section id="foto" class="scroll-mt-32 md:scroll-mt-40">
            <AssetGallery :images="asset.images" />
        </section>

        <!-- TITLE & HEADER -->
        <div class="mb-6 mt-6 min-w-0">
            <h1 class="text-xl sm:text-3xl font-extrabold text-[#0A2540] mb-3 truncate" :title="asset.title">{{ asset.title }}</h1>

            <div class="flex flex-col gap-2">
                <!-- Rating & Favorit -->
                <div class="flex items-center gap-4 text-sm mt-1">
                    <div class="flex items-center gap-1.5">
                        <div class="flex items-center gap-0.5 text-xs">
                            <i v-for="n in 5" :key="n" class="fa-solid fa-star" :class="n <= Math.round(parseFloat(asset.reviews_avg_rating || 0)) ? 'text-[#FFC000]' : 'text-gray-300'"></i>
                        </div>
                        <span class="text-[#0A2540] font-bold">{{ parseFloat(asset.reviews_avg_rating || 0).toFixed(1) }}</span>
                        <span class="text-gray-500">({{ asset.reviews_count || 0 }} ulasan)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-heart text-red-500"></i>
                        <span class="text-gray-500">{{ asset.favorites_count || 0 }} favorit</span>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="flex items-start gap-2 text-sm text-gray-500">
                    <i class="fa-solid fa-location-dot mt-0.5 text-gray-400"></i>
                    <span class="leading-relaxed">{{ asset.city?.name }}, {{ asset.province?.name }}</span>
                </div>
            </div>
        </div>

        <!-- CONTENT LAYOUT (Kiri: Detail, Kanan: Booking Card) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            <!-- KIRI (Detail) -->
            <div class="lg:col-span-2 space-y-10">

                <AssetSpecifications :detail="asset.detail" />

                <!-- Deskripsi -->
                <div class="py-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-4">Tentang Aset Ini</h3>
                    <div class="text-gray-600 leading-relaxed whitespace-pre-line text-justify relative">
                        <div :class="{ 'line-clamp-4': !showFullDescription, 'overflow-hidden': !showFullDescription }">
                            {{ asset.description }}
                        </div>
                    </div>
                    <button v-if="asset.description && asset.description.length > 200" @click="showFullDescription = !showFullDescription" class="mt-2 text-black font-semibold underline hover:text-gray-700">
                        {{ showFullDescription ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya >' }}
                    </button>
                </div>

                <!-- Fasilitas Utama -->
                <div id="fasilitas" v-if="assetFacilities.length > 0" class="py-8 border-b border-gray-200 scroll-mt-32 md:scroll-mt-40">
                    <h3 class="text-[22px] font-semibold text-[#222222] mb-6">Fasilitas yang ditawarkan</h3>

                    <!-- Grid Top Facilities -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-6">
                        <div v-for="fac in topFacilities" :key="fac.id" class="flex items-start gap-3">
                            <i class="fa-solid fa-check mt-1 text-[#FFC000] text-sm shrink-0"></i>
                            <span class="text-[15px] text-gray-700">{{ fac.name }}</span>
                        </div>
                    </div>

                    <button @click="showFasilitasModal = true" class="mt-8 px-6 py-3 rounded-lg border border-black bg-white hover:bg-gray-50 text-[#222222] font-semibold text-[15px] transition-colors inline-block">
                        Tampilkan ke-{{ assetFacilities.length }} fasilitas
                    </button>
                </div>

                <!-- Modal Fasilitas -->
                <FasilitasModal :show="showFasilitasModal" :facilitiesGrouped="facilitiesGrouped" @close="showFasilitasModal = false" />

            <!-- SEKSI PEMILIHAN UNIT (Jika ada units) -->
            <div v-if="asset.units && asset.units.length > 0" id="pilihan-unit" class="py-10 border-b border-gray-200 scroll-mt-32 md:scroll-mt-40">
                <h2 class="text-2xl font-extrabold text-[#0A2540] mb-6">Unit</h2>
                <AssetUnitList
                    :units="asset.units"
                    :rentalUnitLabel="rentalUnitLabel(activeScheduleMode)"
                    :priceMultiplier="priceMultiplier"
                    :durationCount="durationCount"
                    :startDate="startDate ? startDate.toISOString() : null"
                    :endDate="endDate ? endDate.toISOString() : null"
                    :selectedUnitId="selectedUnitId"
                    @select="handleUnitSelect"
                />
            </div>

            <!-- Lokasi Map Placeholder -->
            <div id="lokasi" class="py-6 border-b border-gray-200 scroll-mt-32 md:scroll-mt-40">
                <h3 class="text-lg font-bold mb-4">Lokasi</h3>
                <p class="text-gray-600 mb-4">{{ [asset.address, asset.village?.name, asset.district?.name, asset.city?.name, asset.province?.name, 'Indonesia'].filter(Boolean).join(', ') }} {{ asset.postal_code || '' }}</p>
                <div class="w-full h-64 bg-gray-200 rounded-xl overflow-hidden relative flex items-center justify-center">
                    <div class="absolute inset-0 bg-cover bg-center opacity-40" style="background-image: url('https://map.viamichelin.com/map/carte?map=viamichelin&z=10&lat=-0.502&lon=117.153&width=800&height=400&format=png&version=latest&layer=background')"></div>
                    <div class="z-10 flex flex-col items-center bg-white/90 p-4 rounded-xl shadow-lg">
                        <i class="fa-solid fa-location-dot text-red-500 text-3xl mb-2"></i>
                        <span class="font-bold">Peta belum diintegrasikan</span>
                    </div>
                </div>
            </div>

            <AssetHostProfile :assetId="asset.id" :ownerProfile="asset.owner_profile" />

            <!-- Kebijakan -->
            <AssetPolicy :policies="asset.policies" />

            <AssetFaq :faqs="asset.faqs ?? []" :assetType="asset.type?.name" />

            </div>
            <!-- KANAN (Booking & Contact Cards) -->
            <div class="lg:col-span-1 lg:row-span-2 order-2 lg:order-2">
                <div class="sticky top-24 flex flex-col gap-6">
                    <!-- Booking Card -->
                    <div class="bg-white shadow-2xl shadow-gray-200/50 rounded-2xl p-6 border border-gray-200">

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
                            <div class="p-3 bg-gray-50 flex justify-between items-center border-b border-gray-200">
                                <span class="text-xs font-semibold text-gray-600">Durasi Sewa</span>
                                <span class="text-sm font-bold" :class="durationCount === 0 ? 'text-red-500' : 'text-[#0A2540]'">{{ durationCount || 0 }} {{ rentalUnitLabel(activeScheduleMode) }}</span>
                            </div>
                            <div v-if="selectedUnitName" class="p-3 bg-[#FFC000]/10 flex justify-between items-center">
                                <span class="text-xs font-semibold text-[#0A2540]">Unit Terpilih</span>
                                <span class="text-sm font-extrabold text-[#0A2540] truncate max-w-[150px]">{{ selectedUnitName }}</span>
                            </div>
                        </div>

                        <!-- BUG 8 FIX: Pesan jika tidak ada pricing -->
                        <div v-if="!lowestPrice && (!asset.units || asset.units.length === 0)" class="text-center text-amber-600 text-xs font-bold mb-4 bg-amber-50 rounded-xl px-4 py-3 border border-amber-200">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            Pemilik belum menetapkan harga sewa. Hubungi pemilik untuk informasi.
                        </div>

                        <!-- K5: Pemilih Paket Harga (hanya untuk single asset tanpa unit) -->
                        <div v-if="!asset.units?.length && asset.pricings?.length > 0" class="mb-5">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pilih Paket Sewa</p>
                            <div class="space-y-2">
                                <button
                                    v-for="p in asset.pricings"
                                    :key="p.id"
                                    type="button"
                                    @click="form.pricing_id = p.id"
                                    class="w-full flex justify-between items-center px-4 py-3 rounded-xl border-2 text-sm transition-all"
                                    :class="form.pricing_id === p.id
                                        ? 'border-[#0A2540] bg-[#0A2540]/5 shadow-sm'
                                        : 'border-gray-200 hover:border-gray-300 bg-white'"
                                >
                                    <span class="font-semibold" :class="form.pricing_id === p.id ? 'text-[#0A2540]' : 'text-gray-700'">
                                        {{ p.duration }} {{ rentalUnitLabel(p.rental_unit) }}
                                    </span>
                                    <span class="font-black" :class="form.pricing_id === p.id ? 'text-[#F97316]' : 'text-[#0A2540]'">
                                        {{ formatRupiah(p.price) }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <button
                            v-if="asset.units && asset.units.length > 0 && !form.pricing_id"
                            @click="() => document.getElementById('pilihan-unit')?.scrollIntoView({ behavior: 'smooth' })"
                            class="w-full py-4 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-extrabold rounded-xl transition-all shadow-lg shadow-[#FFC000]/20 flex justify-center items-center gap-2 text-lg mb-4">
                            Pilih Unit
                        </button>
                        <button
                            v-else
                            @click="submitBooking"
                            :disabled="asset.status !== 'approved' || !lowestPrice || !startDate || durationCount === 0"
                            class="w-full py-4 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-extrabold rounded-xl transition-all shadow-lg shadow-[#FFC000]/20 flex justify-center items-center gap-2 text-lg disabled:opacity-50 disabled:cursor-not-allowed mb-4">
                            Booking Sekarang
                        </button>

                        <p v-if="asset.status !== 'approved'" class="text-center text-red-500 text-xs font-bold mb-4">Aset ini sedang tidak tersedia.</p>


                        <!-- Breakdown -->
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-[#0A2540]">{{ formatRupiah(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span v-if="serviceFee?.type === 'fixed'">Biaya Layanan</span>
                                <span v-else>Biaya Layanan ({{ serviceFee?.value ?? (typeof serviceFee === 'number' ? serviceFee : 5) }}%)</span>
                                <span class="font-semibold text-[#0A2540]">{{ formatRupiah(feeAmount) }}</span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex justify-between font-extrabold text-base text-[#0A2540]">
                                <span>Total</span>
                                <span>{{ formatRupiah(totalAmount) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hubungi Pemilik Card (DESKTOP ONLY) -->
                    <div v-if="asset.owner_profile" class="hidden lg:block bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-xl font-bold text-[#0A2540] mb-4">Hubungi Pemilik</h3>
                        <div class="flex items-center gap-3 border-b-2 border-gray-800 pb-2 focus-within:border-[#FFC000] transition-colors">
                            <i class="fa-regular fa-comment-dots text-2xl text-[#FFC000]"></i>
                            <input v-model="chatMessage" @keyup.enter="startChat" type="text" placeholder="Tanya sesuatu ke pemilik..." class="w-full bg-transparent border-none outline-none text-sm text-gray-700 placeholder-gray-400 focus:ring-0 p-0" />
                            <button @click="startChat" class="text-[#FFC000] font-bold text-sm hover:text-[#e6ad00] transition-colors whitespace-nowrap">
                                kirim
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEKSI ULASAN -->
            <div class="lg:col-span-2 lg:col-start-1 order-3 lg:order-3">
                <div id="ulasan" class="mt-8 mb-10 scroll-mt-32 md:scroll-mt-40">
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

                        <AssetReviews
                            :reviews="asset.reviews"
                            :reviewsCount="asset.reviews_count"
                            :averageRating="asset.reviews_avg_rating"
                        />

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CUSTOM BOTTOM BAR (MOBILE ONLY) -->
    <DetailBottomBar
        :price="totalAmount || lowestPrice?.price || 0"
        :durationCount="durationCount"
        :durationLabel="rentalUnitLabel(activeScheduleMode)"
        :formattedDateRange="formattedDateRange"
        :periodLabel="rentalUnitLabel(activeScheduleMode)"
        :disabled="asset.status !== 'approved' || (!asset.pricings?.length && !asset.units?.length) || !startDate || durationCount === 0"
        :buttonText="(asset.units && asset.units.length > 0) ? 'Pilih Unit' : 'Booking'"
        @submit="handleBottomBarSubmit"
    />

    </AppLayout>
</template>
