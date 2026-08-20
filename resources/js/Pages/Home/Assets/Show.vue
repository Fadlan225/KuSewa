<script setup>
import { Layers, MapPin, Calendar, Coins, Star, Heart, Check, AlertTriangle, MessageSquareMore } from 'lucide-vue-next';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import DetailBottomBar from '@/Components/ui/DetailBottomBar.vue';
import AssetGallery from '@/Components/ui/AssetGallery.vue';
import AssetUnitList from '@/Components/ui/AssetUnitList.vue';
import AssetHostProfile from '@/Components/ui/AssetHostProfile.vue';
import AssetSpecifications from '@/Components/ui/AssetSpecifications.vue';
import AssetReviews from '@/Components/ui/AssetReviews.vue';
import AssetFaq from '@/Components/ui/AssetFaq.vue';
import AssetPolicy from '@/Components/ui/AssetPolicy.vue';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { Indonesian } from "flatpickr/dist/l10n/id.js";
import { useHomeSearch } from '@/Composables/useHomeSearch';
import StickySubNavSearch from '@/Components/ui/StickySubNavSearch.vue';
import Navbar from '@/Components/Navbar.vue';
import MobileSearchSheet from '@/Pages/Home/Search/MobileSearchSheet.vue';
import LokasiSearchSheet from '@/Pages/Home/Search/LokasiSearchSheet.vue';
import KeywordSearchSheet from '@/Pages/Home/Search/KeywordSearchSheet.vue';
import LazyAssetCard from '@/Components/ui/LazyAssetCard.vue';

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
    },
    nearbyPlaces: {
        type: Object,
        default: () => ({})
    },
    similarAssets: {
        type: Array,
        default: () => []
    }
});

const categoryLabels = {
    health: 'Fasilitas Kesehatan',
    public_transport: 'Transportasi Publik',
    shopping: 'Pusat Perbelanjaan',
    recreation: 'Tempat Rekreasi',
    food: 'Kuliner',
    religious: 'Tempat Ibadah',
    education: 'Pendidikan',
};

const formatDistance = (km) => {
    if (km < 1) {
        return `${Math.round(km * 1000)} m`;
    }
    return `${km.toFixed(2)} km`;
};

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
const showPricingModal = ref(false);
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
const showAllUnits = ref(false);

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

const scrollToUnit = () => {
    document.getElementById('pilihan-unit')?.scrollIntoView({ behavior: 'smooth' });
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
    return Number(activePrice.value.price);
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

// Hubungi Pemilik / Chat logic
const chatMessage = ref('');
const startChat = () => {
    if (!page.props.auth?.user) {
        window.location.href = '/login';
        return;
    }

    const payload = {
        asset_id: props.asset.id,
        owner_profile_id: props.asset.owner_profile?.id
    };

    // Jika dipanggil dari input Hubungi Pemilik yang punya pesan
    if (chatMessage.value.trim()) {
        payload.message = chatMessage.value.trim();
    }

    router.post(route('chat.start'), payload, {
        onSuccess: () => {
            chatMessage.value = '';
        },
        onError: (errors) => {
            console.error('Failed to start chat:', errors);
        }
    });
};

// Scroll logic for Detail Navbar animation
const showDetailNav = ref(false);
const handleWindowScroll = () => {
    showDetailNav.value = window.scrollY > 400;
};

onMounted(() => {
    window.addEventListener('scroll', handleWindowScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleWindowScroll);
});

</script>

<template>
    <Head :title="asset.title || 'Detail Aset'" />

    <AppLayout :hideNavbar="true" :hideBottombar="true">

    <MobileSearchSheet />
    <LokasiSearchSheet />
    <KeywordSearchSheet />

    <!-- NATURAL SCROLLING MAIN NAVBAR -->
    <Navbar class="hidden md:block !absolute top-0 left-0 w-full z-[80] !transition-none" />

    <!-- STICKY CONTAINER FOR SUB NAV & DETAIL NAV -->
    <div class="hidden md:block sticky top-0 left-0 w-full z-[75] mt-[64px]">
        
        <!-- FILTER PENCARIAN -->
        <div class="w-full bg-white shadow-sm border-b border-gray-100 relative z-20">
            <StickySubNavSearch class="!shadow-none !border-b-0 !static !bg-transparent !py-2" />
        </div>

        <!-- FLOATING DETAIL NAVBAR (Slides Down on Scroll from under Filter) -->
        <div class="absolute w-full left-0 top-full overflow-hidden pointer-events-none z-10">
            <div class="transition-transform duration-300 ease-out shadow-md bg-white pointer-events-auto"
                 :class="showDetailNav ? 'translate-y-0' : '-translate-y-full'">
                <DetailNavbar :isFavorited="asset.isFavorite" @favorite="handleFavorite" :showBackButton="true" :mobileBackOnly="true" class="!shadow-none !border-b-0" />
            </div>
        </div>
    </div>

    <!-- UNIFIED STICKY HEADER (Mobile Only) -->
    <div class="md:hidden sticky top-0 z-[70] w-full flex flex-col bg-white shadow-sm border-b border-gray-100 mt-[64px]">
        <!-- MOBILE SUB NAVBAR (Badges for Schedule and Price) -->
        <div class="flex md:hidden items-center gap-2 px-4 pb-3 overflow-x-auto hide-scrollbar">
            <div @click="openMobileSearch('jenis')" class="bg-gray-100 text-[#0A2540] text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition flex-shrink-0">
                <Layers class="text-gray-500" />
                {{ selectedAssets.length ? selectedAssets.join(', ') : (asset.type?.name || 'Semua Tipe') }}
            </div>
            <div @click="openMobileSearch('lokasi')" class="bg-gray-100 text-[#0A2540] text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition flex-shrink-0">
                <MapPin class="text-gray-500" />
                {{ searchQuery || asset.city?.name || 'Pilih Lokasi' }}
            </div>
            <div @click="openMobileSearch('jadwal')" class="bg-gray-100 text-[#0A2540] text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition flex-shrink-0">
                <Calendar class="text-gray-500" />
                {{ formattedDateRange || 'Pilih Jadwal' }}
            </div>
            <div @click="openMobileSearch('harga')" class="bg-gray-100 text-[#0A2540] text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition flex-shrink-0">
                <Coins class="text-gray-500" />
                {{ (parsedMinPrice > 0 || parsedMaxPrice < maxLimit) ? (formatPriceShort(parsedMinPrice) + ' - ' + formatPriceShort(parsedMaxPrice)) : 'Batas Harga' }}
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-[#0A2540] font-sans pb-32 lg:pb-10">

        <!-- HERO GALLERY AND MODAL -->
        <section id="foto" class="scroll-mt-32 md:scroll-mt-[180px]">
            <AssetGallery :images="asset.images" />
        </section>

        <!-- TITLE & HEADER -->
        <div class="mb-6 mt-6 min-w-0">
            <h1 class="text-2xl sm:text-[32px] font-extrabold text-[#222222] mb-2 tracking-tight leading-tight" :title="asset.title">{{ asset.title }}</h1>

            <div class="flex flex-col gap-2.5">
                <!-- Rating & Favorit -->
                <div class="flex items-center gap-4 mt-1">
                    <div class="flex items-center gap-1.5">
                        <div class="flex items-center gap-0.5">
                            <Star v-for="n in 5" :key="n" class="w-4 h-4" :class="n <= Math.round(parseFloat(asset.reviews_avg_rating || 0)) ? 'text-[#FFC000] fill-[#FFC000]' : 'text-gray-300 fill-gray-300'" />
                        </div>
                        <span class="text-[#222222] font-bold text-[14px]">{{ parseFloat(asset.reviews_avg_rating || 0).toFixed(1) }}</span>
                        <span class="text-gray-500 text-[14px]">({{ asset.reviews_count || 0 }} ulasan)</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[14px]">
                        <Heart class="text-red-500 w-4 h-4" />
                        <span class="text-gray-500">{{ asset.favorites_count || 0 }} favorit</span>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="flex items-start gap-2 text-[14px] text-gray-600">
                    <MapPin class="mt-0.5 text-gray-400 w-4 h-4" />
                    <span class="leading-relaxed font-medium">{{ asset.city?.name }}, {{ asset.province?.name }}</span>
                </div>
            </div>
        </div>

        <!-- CONTENT LAYOUT (Kiri: Detail, Kanan: Booking Card) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">

            <!-- KIRI (Detail) -->
            <div class="lg:col-span-2 space-y-10 min-w-0 overflow-hidden">

                <!-- Informasi Umum Wrapper -->
                <div id="informasi" class="scroll-mt-32 md:scroll-mt-40">
                    <AssetSpecifications :detail="asset.detail" />

                    <!-- Deskripsi -->
                    <div class="py-10 md:py-12 border-b border-gray-100">
                        <h3 class="text-[22px] font-bold text-[#222222] mb-4">Tentang Aset Ini</h3>
                        <div class="text-[15px] text-gray-700 leading-8 whitespace-pre-line text-left relative">
                            <div :class="{ 'line-clamp-4': !showFullDescription, 'overflow-hidden': !showFullDescription }">
                                {{ asset.description }}
                            </div>
                        </div>
                        <button v-if="asset.description && asset.description.length > 200" @click="showFullDescription = !showFullDescription" class="mt-3 text-black font-semibold hover:text-gray-700 underline underline-offset-2">
                            {{ showFullDescription ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya >' }}
                        </button>
                    </div>
                </div>

                <!-- Fasilitas Utama -->
                <div id="fasilitas" v-if="assetFacilities.length > 0" class="py-10 md:py-12 border-b border-gray-100 scroll-mt-32 md:scroll-mt-40">
                    <h3 class="text-[22px] font-semibold text-[#222222] mb-6">Fasilitas yang ditawarkan</h3>

                    <!-- Grid Top Facilities -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-6">
                        <div v-for="fac in topFacilities" :key="fac.id" class="flex items-start gap-3">
                            <Check class="mt-1 text-[#FFC000] text-sm shrink-0" />
                            <span class="text-[15px] text-gray-700">{{ fac.name }}</span>
                        </div>
                    </div>

                    <button v-if="assetFacilities.length > topFacilities.length" @click="showFasilitasModal = true" class="mt-8 px-6 py-3 rounded-md border border-gray-200 bg-white hover:bg-gray-50 text-[#222222] font-semibold text-[15px] transition-colors inline-block">
                        Tampilkan seluruh fasilitas
                    </button>
                </div>

                <!-- Modal Fasilitas -->
                <FasilitasModal :show="showFasilitasModal" :facilitiesGrouped="facilitiesGrouped" @close="showFasilitasModal = false" />

            <!-- SEKSI PEMILIHAN UNIT (Jika ada units) -->
            <div v-if="asset.units && asset.units.length > 0" id="pilihan-unit" class="py-10 md:py-12 border-b border-gray-100 scroll-mt-32 md:scroll-mt-40">
                <h3 class="text-[22px] font-bold text-[#222222] mb-6">Pilihan Unit</h3>
                <AssetUnitList
                    :units="showAllUnits ? asset.units : asset.units.slice(0, 3)"
                    :rentalUnitLabel="rentalUnitLabel(activeScheduleMode)"
                    :durationCount="durationCount"
                    :startDate="startDate ? startDate.toISOString() : null"
                    :endDate="endDate ? endDate.toISOString() : null"
                    :selectedUnitId="selectedUnitId"
                    @select="handleUnitSelect"
                />
                <button
                    v-if="asset.units.length > 3 && !showAllUnits"
                    @click="showAllUnits = true"
                    class="w-full mt-5 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-[#222222] font-bold rounded-lg transition-colors text-[15px]"
                >
                    Lihat {{ asset.units.length - 3 }} Unit Lainnya
                </button>
            </div>

            <!-- Lokasi Map -->
            <div id="lokasi" class="py-10 md:py-12 border-b border-gray-100 scroll-mt-32 md:scroll-mt-40">
                <h3 class="text-[22px] font-bold text-[#222222] mb-4">Lokasi dan lingkungan sekitar</h3>
                <p class="text-[15px] text-gray-700 mb-6 font-medium">{{ [asset.address, asset.village?.name, asset.district?.name, asset.city?.name, asset.province?.name, 'Indonesia'].filter(Boolean).join(', ') }} {{ asset.postal_code || '' }}</p>
                <div class="w-full h-72 bg-gray-200 rounded-xl overflow-hidden relative mb-6">
                    <iframe
                        v-if="asset.latitude && asset.longitude"
                        width="100%"
                        height="100%"
                        frameborder="0"
                        scrolling="no"
                        marginheight="0"
                        marginwidth="0"
                        :src="`https://www.openstreetmap.org/export/embed.html?bbox=${parseFloat(asset.longitude)-0.02}%2C${parseFloat(asset.latitude)-0.01}%2C${parseFloat(asset.longitude)+0.02}%2C${parseFloat(asset.latitude)+0.01}&amp;layer=mapnik&amp;marker=${asset.latitude}%2C${asset.longitude}`"
                        style="border: 0;"
                    ></iframe>
                    <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 p-4 shadow-lg">
                        <MapPin class="text-red-500 text-3xl mb-2" />
                        <span class="font-bold">Koordinat lokasi tidak tersedia</span>
                    </div>
                </div>

                <!-- Info Lokasi Sekitar -->
                <div v-if="Object.keys(nearbyPlaces).length > 0" class="mt-8">
                    <h4 class="text-[18px] font-bold text-[#222222] mb-5">Jarak ke fasilitas publik</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                        <div v-for="(places, category) in nearbyPlaces" :key="category" class="flex flex-col gap-2.5 min-w-0">
                            <h5 class="text-[15px] font-semibold text-gray-800">{{ categoryLabels[category] || category }}</h5>
                            <ul class="flex flex-col gap-2 pl-3">
                                <li v-for="place in places" :key="place.name" class="flex items-start gap-2.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0 mt-[7px]"></div>
                                    <div class="flex-1 min-w-0 flex justify-between items-start gap-2">
                                        <span class="text-[13px] text-gray-600 leading-tight flex-1 min-w-0 break-words pr-2">{{ place.name }}</span>
                                        <span class="text-[13px] font-medium text-gray-900 whitespace-nowrap shrink-0">{{ formatDistance(place.distance) }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kebijakan Wrapper -->
            <div id="kebijakan" class="scroll-mt-32 md:scroll-mt-40">
                <!-- Kebijakan -->
                <AssetPolicy :policies="asset.policies" />

                <AssetFaq :faqs="asset.faqs ?? []" :assetType="asset.type?.name" />
            </div>

            </div>
            <!-- KANAN (Booking & Contact Cards) -->
            <div class="lg:col-span-1 lg:row-span-2 order-2 lg:order-2">
                <div class="sticky top-32 md:top-40 flex flex-col gap-0">
                    <!-- Booking Card -->
                    <div class="bg-white shadow-lg shadow-gray-200/50 rounded-t-lg p-5 md:p-6 border border-gray-100 border-b-0 relative z-10">

                        <!-- Date & Duration Box -->
                        <div class="border border-gray-200 rounded-lg overflow-hidden mb-4">
                            <div class="flex border-b border-gray-200">
                                <!-- Mulai -->
                                <div class="flex-1 px-3.5 py-3 border-r border-gray-200 bg-white">
                                    <p class="text-[10px] md:text-xs uppercase font-bold text-gray-400 mb-1">Mulai Sewa</p>
                                    <p class="text-[13px] md:text-[15px] font-bold text-[#0A2540]">{{ startDate ? startDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Pilih Tanggal' }}</p>
                                    <p class="text-[11px] md:text-[13px] text-gray-400 mt-0.5" v-if="activeScheduleMode === 'hour'">{{ startTime }}</p>
                                </div>
                                <!-- Selesai -->
                                <div class="flex-1 px-3.5 py-3 bg-white">
                                    <p class="text-[10px] md:text-xs uppercase font-bold text-gray-400 mb-1">Selesai Sewa</p>
                                    <p class="text-[13px] md:text-[15px] font-bold text-[#0A2540]">{{ (activeScheduleMode === 'hour' && startDate) ? startDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : (endDate ? endDate.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-') }}</p>
                                    <p class="text-[11px] md:text-[13px] text-gray-400 mt-0.5" v-if="activeScheduleMode === 'hour'">{{ endTime }}</p>
                                </div>
                            </div>
                            <div class="px-3.5 py-3 bg-gray-50 flex justify-between items-center border-b border-gray-200">
                                <span class="text-[12px] md:text-[14px] font-semibold text-gray-500">Durasi Sewa</span>
                                <span class="text-[13px] md:text-[15px] font-bold" :class="durationCount === 0 ? 'text-red-500' : 'text-[#0A2540]'">{{ durationCount || 0 }} {{ rentalUnitLabel(activeScheduleMode) }}</span>
                            </div>
                            <div v-if="selectedUnitName" class="px-3.5 py-2.5 bg-[#FFC000]/10 flex justify-between items-center">
                                <span class="text-[12px] md:text-[14px] font-semibold text-[#0A2540]">Unit Terpilih</span>
                                <span class="text-[13px] md:text-[15px] font-extrabold text-[#0A2540] truncate max-w-[130px] md:max-w-[150px]">{{ selectedUnitName }}</span>
                            </div>
                        </div>

                        <!-- BUG 8 FIX: Pesan jika tidak ada pricing -->
                        <div v-if="!lowestPrice && (!asset.units || asset.units.length === 0)" class="text-center text-amber-600 text-[12px] md:text-sm font-bold mb-4 bg-amber-50 rounded-lg px-3.5 py-3 border border-amber-200">
                            <AlertTriangle class="mr-1 inline w-4 h-4" />
                            Pemilik belum menetapkan harga.
                        </div>

                        <!-- K5: Pemilih Paket Harga (hanya untuk single asset tanpa unit) -->
                        <div v-if="!asset.units?.length && asset.pricings?.length > 0" class="mb-4">
                            <p class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-wider mb-2.5">Pilih Paket Sewa</p>
                            <div class="space-y-2">
                                <button
                                    v-for="p in asset.pricings.slice(0, 3)"
                                    :key="p.id"
                                    type="button"
                                    @click="form.pricing_id = p.id"
                                    class="w-full flex justify-between items-center px-3.5 py-3 rounded-lg border-2 text-[13px] md:text-[15px] transition-all"
                                    :class="form.pricing_id === p.id
                                        ? 'border-[#0A2540] bg-[#0A2540]/5 shadow-sm'
                                        : 'border-gray-100 hover:border-gray-200 bg-white'"
                                >
                                    <span class="font-semibold" :class="form.pricing_id === p.id ? 'text-[#0A2540]' : 'text-gray-600'">
                                        {{ p.duration }} {{ rentalUnitLabel(p.rental_unit) }}
                                    </span>
                                    <span class="font-black" :class="form.pricing_id === p.id ? 'text-[#F97316]' : 'text-[#0A2540]'">
                                        {{ formatRupiah(p.price) }}
                                    </span>
                                </button>
                            </div>
                            <div class="relative mt-3" v-if="asset.pricings.length > 3">
                                <button
                                    @click="showPricingModal = !showPricingModal"
                                    class="text-[12px] md:text-sm font-bold text-[#FFC000] hover:text-[#e6ad00] underline"
                                >
                                    Lihat harga sewa lainnya
                                </button>

                                <!-- Layar penutup kasat mata untuk menutup popover saat di-klik di luar -->
                                <div v-if="showPricingModal" class="fixed inset-0 z-40" @click="showPricingModal = false"></div>

                                <transition
                                    enter-active-class="transition ease-out duration-200 origin-right"
                                    enter-from-class="opacity-0 scale-95 translate-x-4"
                                    enter-to-class="opacity-100 scale-100 translate-x-0"
                                    leave-active-class="transition ease-in duration-150 origin-right"
                                    leave-from-class="opacity-100 scale-100 translate-x-0"
                                    leave-to-class="opacity-0 scale-95 translate-x-4"
                                >
                                    <div v-if="showPricingModal" class="absolute right-full bottom-0 mr-4 w-[280px] md:w-[320px] bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.2)] border border-gray-100 z-50 overflow-hidden flex flex-col">
                                        <div class="p-3 md:p-4 overflow-y-auto space-y-2 max-h-[40vh] bg-white">
                                            <button
                                                v-for="p in asset.pricings"
                                                :key="p.id"
                                                type="button"
                                                @click="form.pricing_id = p.id; showPricingModal = false"
                                                class="w-full flex justify-between items-center px-3 py-2.5 rounded-lg border-2 text-[13px] md:text-[14px] transition-all"
                                                :class="form.pricing_id === p.id
                                                    ? 'border-[#0A2540] bg-[#0A2540]/5 shadow-sm'
                                                    : 'border-transparent hover:border-gray-200 hover:bg-gray-50 bg-white'"
                                            >
                                                <span class="font-medium" :class="form.pricing_id === p.id ? 'text-[#0A2540] font-bold' : 'text-gray-600'">
                                                    {{ p.duration }} {{ rentalUnitLabel(p.rental_unit) }}
                                                </span>
                                                <span class="font-bold" :class="form.pricing_id === p.id ? 'text-[#F97316]' : 'text-[#0A2540]'">
                                                    {{ formatRupiah(p.price) }}
                                                </span>
                                            </button>
                                        </div>
                                        <div class="px-4 py-2 border-t border-gray-100 flex justify-end bg-gray-50/80">
                                            <button @click="showPricingModal = false" class="font-bold text-[#FFC000] hover:text-[#e6ad00] text-[12px] md:text-[13px]">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>

                        <button
                            v-if="asset.units && asset.units.length > 0 && !form.pricing_id"
                            @click="scrollToUnit"
                            class="w-full py-3 md:py-3.5 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-extrabold rounded-lg transition-all shadow-sm flex justify-center items-center gap-1.5 text-[14px] md:text-[15px] mb-4">
                            Pilih Unit
                        </button>
                        <button
                            v-else
                            @click="submitBooking"
                            :disabled="asset.status !== 'approved' || !lowestPrice || !startDate || durationCount === 0"
                            class="w-full py-3 md:py-3.5 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-extrabold rounded-lg transition-all shadow-sm flex justify-center items-center gap-1.5 text-[14px] md:text-[15px] disabled:opacity-50 disabled:cursor-not-allowed mb-4">
                            Booking Sekarang
                        </button>

                        <p v-if="asset.status !== 'approved'" class="text-center text-red-500 text-xs font-bold mb-3 mt-[-10px]">Aset ini sedang tidak tersedia.</p>


                        <!-- Breakdown -->
                        <div class="space-y-2.5 text-[13px] md:text-[15px]">
                            <div class="flex justify-between text-gray-500">
                                <span>Subtotal</span>
                                <span class="font-semibold text-gray-700">{{ formatRupiah(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span v-if="serviceFee?.type === 'fixed'">Biaya Layanan</span>
                                <span v-else>Layanan ({{ serviceFee?.value ?? (typeof serviceFee === 'number' ? serviceFee : 5) }}%)</span>
                                <span class="font-semibold text-gray-700">{{ formatRupiah(feeAmount) }}</span>
                            </div>
                            <hr class="border-gray-100 my-2">
                            <div class="flex justify-between font-extrabold text-[16px] md:text-lg text-[#0A2540]">
                                <span>Total</span>
                                <span>{{ formatRupiah(totalAmount) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hubungi Pemilik Card (DESKTOP ONLY) -->
                    <div v-if="asset.owner_profile" class="hidden lg:block bg-white rounded-b-lg shadow-lg shadow-gray-200/50 border border-gray-100 p-5 md:p-6 border-t border-gray-100 relative z-0">
                        <h3 class="text-[14px] md:text-[16px] font-bold text-[#0A2540] mb-3">Hubungi Pemilik</h3>
                        <div class="flex items-center gap-3 border-b border-gray-400 pb-2 focus-within:border-[#FFC000] transition-colors">
                            <MessageSquareMore class="text-xl md:text-2xl text-[#FFC000]" />
                            <input v-model="chatMessage" @keyup.enter="startChat" type="text" placeholder="Tanya sesuatu..." class="w-full bg-transparent border-none outline-none text-[13px] md:text-[15px] text-gray-700 placeholder-gray-400 focus:ring-0 p-0" />
                            <button @click="startChat" class="text-[#FFC000] font-bold text-[13px] md:text-[15px] hover:text-[#e6ad00] transition-colors whitespace-nowrap">
                                Kirim
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

                <!-- Pemilik Aset -->
                <div id="pemilik" class="scroll-mt-32 md:scroll-mt-40">
                    <AssetHostProfile :assetId="asset.id" :ownerProfile="asset.owner_profile" />
                </div>
            </div>

            <!-- Kamu mungkin juga suka (Rekomendasi) -->
            <div v-if="similarAssets && similarAssets.length > 0" class="lg:col-span-3 order-4 mt-8 mb-8 pt-8 border-t border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-[#222222]">Kamu mungkin juga suka</h2>
                    <Link :href="route('assets.search', { location: asset.city?.name, 'type[]': asset.type?.name })" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-semibold text-[#222222] hover:bg-gray-50 transition-colors hidden md:block">
                        Lihat semua
                    </Link>
                </div>

                <div class="flex overflow-x-auto gap-4 pb-6 snap-x hide-scrollbar">
                    <div v-for="simAsset in similarAssets" :key="simAsset.id" class="w-[240px] md:w-[260px] shrink-0 snap-start">
                        <LazyAssetCard :asset="simAsset" />
                    </div>
                </div>

                <div class="mt-2 md:hidden">
                    <Link :href="route('assets.search', { location: asset.city?.name, 'type[]': asset.type?.name })" class="block w-full text-center px-4 py-3 border border-gray-200 rounded-lg text-sm font-bold text-[#222222] hover:bg-gray-50 transition-colors">
                        Lihat semua
                    </Link>
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
        :buttonText="(asset.units && asset.units.length > 0 && !selectedUnitId) ? 'Pilih Unit' : 'Ajukan Sewa'"
        @submit="handleBottomBarSubmit"
        @tanya-pemilik="startChat"
    />

    </AppLayout>
</template>
