<script setup>
import { Image, Circle, Check, Ruler, Bed, Users, X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, computed, reactive } from 'vue';

const listImageIndices = reactive({});
const getListImageIndex = (unitId) => listImageIndices[unitId] || 0;
const nextListImage = (unit) => {
    if (!unit.images || unit.images.length <= 1) return;
    listImageIndices[unit.id] = (getListImageIndex(unit.id) + 1) % unit.images.length;
};
const prevListImage = (unit) => {
    if (!unit.images || unit.images.length <= 1) return;
    const len = unit.images.length;
    listImageIndices[unit.id] = (getListImageIndex(unit.id) - 1 + len) % len;
};

let touchStartX = 0;
const onTouchStart = (e) => {
    touchStartX = e.changedTouches[0].screenX;
};
const onTouchEnd = (e, nextCallback, prevCallback) => {
    const touchEndX = e.changedTouches[0].screenX;
    if (touchEndX < touchStartX - 40) nextCallback();
    if (touchEndX > touchStartX + 40) prevCallback();
};

const nextDetailImage = () => {
    if (!selectedDetailUnit.value || !selectedDetailUnit.value.images) return;
    activeDetailImageIndex.value = (activeDetailImageIndex.value + 1) % selectedDetailUnit.value.images.length;
};

const prevDetailImage = () => {
    if (!selectedDetailUnit.value || !selectedDetailUnit.value.images) return;
    activeDetailImageIndex.value = (activeDetailImageIndex.value - 1 + selectedDetailUnit.value.images.length) % selectedDetailUnit.value.images.length;
};
import BottomSheet from '@/Components/UI/BottomSheet.vue';

const props = defineProps({
    units: {
        type: Array,
        required: true,
    },
    rentalUnitLabel: {
        type: String,
        default: 'malam',
    },
    durationCount: {
        type: Number,
        default: 1,
    },
    startDate: {
        type: String,
        default: null,
    },
    endDate: {
        type: String,
        default: null,
    },
    selectedUnitId: {
        type: [Number, String],
        default: null,
    }
});

const emit = defineEmits(['select']);

const formatRupiah = (value) => {
    if (!value) return 'Hubungi Pemilik';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

const getAvailableQuantity = (unit) => {
    if (!props.startDate || !props.endDate) return null;

    if (!unit.bookings || unit.bookings.length === 0) return unit.quantity;

    let reqStart = new Date(props.startDate);
    reqStart.setHours(0,0,0,0);
    let reqEnd = new Date(props.endDate);
    reqEnd.setHours(0,0,0,0);

    console.log("Checking unit:", unit.id, "bookings:", unit.bookings);

    let maxDailyBookings = 0;

    // Pastikan iterasi mencakup hari yang sama jika start dan end sama
    let iterationEnd = new Date(reqEnd);
    if (props.rentalUnitLabel !== 'malam') {
        iterationEnd.setDate(iterationEnd.getDate() + 1); // Tambah 1 hari inklusif untuk sewa harian/jam
    }

    // Iterasi per hari untuk mencari puncak jumlah kamar terpakai bersamaan
    for (let d = new Date(reqStart); d < iterationEnd; d.setDate(d.getDate() + 1)) {
        let dailyCount = 0;
        let currentDay = new Date(d);
        for (let b of unit.bookings) {
            // Fix cross browser date parsing (safari doesn't support 'YYYY-MM-DD HH:mm:ss')
            // Laravel might send '2026-09-26 00:00:00' or '2026-09-26T00:00:00.000000Z'
            let bStartStr = b.start_date.split(' ')[0].split('T')[0];
            let bStart = new Date(bStartStr + "T00:00:00");

            let bEndStr = b.end_date.split(' ')[0].split('T')[0];
            let bEnd = new Date(bEndStr + "T00:00:00");

            if (props.rentalUnitLabel === 'malam') {
                // Sewa malam: checkin di currentDay, checkout keesokan harinya
                if (bStart <= currentDay && bEnd > currentDay) {
                    dailyCount++;
                }
            } else {
                // Sewa jam/hari: blokir penuh dari tanggal mulai sampai selesai
                if (bStart <= currentDay && bEnd >= currentDay) {
                    dailyCount++;
                }
            }
        }
        if (dailyCount > maxDailyBookings) {
            maxDailyBookings = dailyCount;
        }
    }

    return Math.max(0, unit.quantity - maxDailyBookings);
};

const getUnitImage = (unit) => {
    if (unit.images && unit.images.length > 0) {
        return unit.images[0].image_url;
    }
    return null;
};

// State for Modal/BottomSheet
const selectedDetailUnit = ref(null);
const isDesktopModalOpen = ref(false);
const isMobileSheetOpen = ref(false);
const activeDetailImageIndex = ref(0);

const openDetail = (unit) => {
    selectedDetailUnit.value = unit;
    activeDetailImageIndex.value = 0;
    if (window.innerWidth >= 768) {
        isDesktopModalOpen.value = true;
    } else {
        isMobileSheetOpen.value = true;
    }
};

const closeDetail = () => {
    isDesktopModalOpen.value = false;
    isMobileSheetOpen.value = false;
    setTimeout(() => {
        selectedDetailUnit.value = null;
    }, 300);
};





// Ambil harga terendah dari sebuah unit
const getLowestPricing = (unit) => {
    if (!unit.pricings || unit.pricings.length === 0) return { price: 0 };
    return unit.pricings.reduce((min, p) => p.price < min.price ? p : min, unit.pricings[0]);
};

const handleSelect = (unit, pricing) => {
    emit('select', {
        unit_id: unit.id,
        pricing_id: pricing.id,
        price: pricing.price
    });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Looping for units -->
        <div v-for="unit in units" :key="unit.id"
             :class="['w-full bg-white sm:bg-white rounded-none sm:rounded-2xl shadow-none hover:shadow-md transition-shadow overflow-hidden group flex flex-col sm:flex-row relative',
                      selectedUnitId === unit.id ? 'border-2 border-[#FFC000] ring-1 ring-[#FFC000]/50 sm:shadow-md z-10' : 'border-b sm:border border-gray-200 sm:border-gray-100 sm:shadow-sm']">

            <!-- MOBILE: Layout sesuai screenshot -->
            <div class="sm:hidden flex flex-col bg-[#F8F9FA] pb-2">
                <!-- Image Carousel Mobile (Before Bottom Sheet) -->
                <div class="relative w-full h-[220px] bg-gray-200 shrink-0 overflow-hidden"
                     @touchstart="onTouchStart"
                     @touchend="(e) => onTouchEnd(e, () => nextListImage(unit), () => prevListImage(unit))">

                    <Transition name="fade">
                        <img v-if="unit.images?.length > 0" :key="getListImageIndex(unit.id)" :src="unit.images[getListImageIndex(unit.id)].image_url" class="w-full h-full object-cover absolute inset-0 cursor-pointer" @click="openDetail(unit)" />
                        <div v-else class="w-full h-full flex items-center justify-center bg-slate-100 absolute inset-0 cursor-pointer" @click="openDetail(unit)">
                            <Image class="text-3xl text-gray-300" />
                        </div>
                    </Transition>

                    <!-- Indicator Dots -->
                    <div v-if="unit.images && unit.images.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-10 pointer-events-none">
                        <div v-for="idx in Math.min(5, unit.images.length)" :key="idx"
                             class="w-2 h-2 rounded-full"
                             :class="(idx-1) === getListImageIndex(unit.id) ? 'bg-white' : 'bg-white/50'">
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Title -->
                    <h3 class="font-extrabold text-[#0A2540] text-lg mb-3">{{ unit.name }}</h3>

                    <!-- Specs Row (Dynamic Details) -->
                    <div class="flex flex-wrap gap-x-4 gap-y-2 text-[11px] font-bold text-[#0A2540] mb-2">
                        <span v-for="(val, key) in (unit.detail || {})" :key="key" class="flex items-center gap-1.5">
                            <Circle class="text-[4px] text-gray-400" /> {{ val }} {{ key }}
                        </span>
                    </div>

                    <!-- Facility Row (Highlights) -->
                    <div v-if="unit.facilities && unit.facilities.length" class="flex flex-wrap gap-x-4 gap-y-2 text-[11px] font-bold text-[#FFC000]">
                        <span v-for="fac in unit.facilities.slice(0, 3)" :key="fac.id" class="flex items-center gap-1.5 text-[#0A2540]">
                            <Check class="text-[#FFC000]" /> {{ fac.name }}
                        </span>
                    </div>

                    <!-- Pricing Card -->
                    <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                        <!-- Card Header -->
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-[#0A2540] text-[13px]">{{ unit.name }}</h4>
                            <button @click="openDetail(unit)" class="text-[#0A2540] underline font-bold text-[12px] hover:text-[#FFC000]">Lihat Rincian</button>
                        </div>

                        <!-- Main Info in Card -->
                        <div class="space-y-2 mb-4 text-[12px] font-bold text-[#0A2540]">
                            <div v-for="(val, key) in Object.entries(unit.detail || {}).slice(0, 2)" :key="key" class="flex items-center gap-3">
                                <Circle class="text-[4px] text-gray-400 w-4 text-center" /> {{ val[1] }} {{ val[0] }}
                            </div>
                        </div>

                        <div class="flex justify-between items-end">
                            <!-- Price Column -->
                            <div class="flex flex-col items-end">
                                <span class="font-extrabold text-[#e65c00] text-lg">{{ formatRupiah(getLowestPricing(unit).price) }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex flex-col items-end mt-4 gap-1">
                            <button @click="getAvailableQuantity(unit) !== 0 ? handleSelect(unit, getLowestPricing(unit)) : null"
                                    :disabled="getAvailableQuantity(unit) === 0"
                                    :class="['font-extrabold py-2 px-8 rounded-full text-xs shadow-sm transition-transform', 
                                    getAvailableQuantity(unit) === 0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' :
                                    (selectedUnitId === unit.id ? 'bg-[#0A2540] text-white active:scale-95' : 'bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] active:scale-95')]">
                                {{ getAvailableQuantity(unit) === 0 ? 'Kamar Penuh' : (selectedUnitId === unit.id ? 'Kamar Terpilih' : 'Pilih Kamar') }}
                            </button>
                            <span v-if="getAvailableQuantity(unit) === 0" class="text-[9px] text-red-500 font-medium text-right leading-tight max-w-[120px]">Ubah jadwal atau pilih unit lain</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DESKTOP: 1 Image (Left) -->
            <div class="hidden sm:block w-[180px] lg:w-[240px] h-[150px] lg:h-[180px] flex-shrink-0 relative bg-slate-100 overflow-hidden cursor-pointer" @click="openDetail(unit)">
                <img v-if="getUnitImage(unit)" :src="getUnitImage(unit)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-300">
                    <Image class="text-3xl mb-1" />
                </div>
                <!-- Overlay detail text -->
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent pt-6 pb-2 text-center pointer-events-none">
                    <span class="text-white text-[11px] font-bold shadow-sm">Lihat Detail</span>
                </div>
            </div>

            <!-- DESKTOP: Info & Price (Right) -->
            <div class="hidden sm:flex flex-row flex-grow p-3 lg:p-4 gap-3 bg-white min-w-0">
                <!-- Info (Name, Specs, Capacity) -->
                <div class="flex flex-col flex-grow justify-between min-w-0 cursor-pointer" @click="openDetail(unit)">
                    <div>
                        <h3 class="font-bold text-[16px] leading-tight text-[#0A2540] truncate mb-1 group-hover:text-[#FFC000] transition-colors">
                            {{ unit.name }}
                        </h3>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-gray-500 font-medium">
                            <div v-if="unit.detail?.luas" class="flex items-center gap-1">
                                <Ruler class="text-[#FFC000]" />
                                <span>{{ unit.detail.luas }}</span>
                            </div>
                            <div v-if="unit.detail?.bed" class="flex items-center gap-1.5">
                                <Bed class="text-[#FFC000]" />
                                <span>{{ unit.detail.bed }}</span>
                            </div>
                            <div v-if="unit.detail?.kapasitas" class="flex items-center gap-1.5">
                                <Users class="text-[#FFC000]" />
                                <span>{{ unit.detail.kapasitas }}</span>
                            </div>
                        </div>

                        <!-- Simplified Facilities -->
                        <div v-if="unit.detail?.fasilitas?.length" class="flex flex-wrap items-center gap-1.5 mt-2.5">
                            <span v-for="fac in unit.detail.fasilitas.slice(0, 3)" :key="fac" class="px-2 py-0.5 bg-[#F8F9FA] text-[#6C757D] rounded border border-[#6C757D]/20 text-[10px] font-semibold truncate max-w-[100px]">
                                {{ fac }}
                            </span>
                            <span v-if="unit.detail.fasilitas.length > 3" class="px-2 py-0.5 bg-[#F8F9FA] text-[#6C757D] rounded border border-[#6C757D]/20 text-[10px] font-semibold">
                                +{{ unit.detail.fasilitas.length - 3 }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 text-[11px] font-bold">
                        <span v-if="getAvailableQuantity(unit) !== null" :class="getAvailableQuantity(unit) > 0 ? 'text-green-600' : 'text-red-500'">
                            {{ getAvailableQuantity(unit) > 0 ? `Tersedia ${getAvailableQuantity(unit)} unit di tanggal ini` : 'Penuh di tanggal ini' }}
                        </span>
                        <span v-else class="text-gray-500">
                            Total {{ unit.quantity }} Unit tipe ini
                        </span>
                    </div>
                </div>

                <!-- Price & Button (Take cheapest pricing for unit) -->
                <div class="w-[200px] shrink-0 flex flex-col justify-between border-l border-[#6C757D]/20 pl-4 min-w-0">
                    <div class="text-left">
                        <div class="font-extrabold text-xl text-[#e65c00] leading-tight truncate">
                            {{ formatRupiah(getLowestPricing(unit).price) }}
                            <span class="text-[10px] font-normal text-[#0A2540] inline">
                                /{{ rentalUnitLabel }}
                            </span>
                        </div>
                        <!-- Action Button -->
                        <div class="mt-4 flex flex-col items-center gap-1.5 w-full">
                            <button @click="getAvailableQuantity(unit) !== 0 ? handleSelect(unit, getLowestPricing(unit)) : null"
                                    :disabled="getAvailableQuantity(unit) === 0"
                                    :class="['w-full font-extrabold py-2 px-8 rounded text-xs shadow-sm transition-transform', 
                                    getAvailableQuantity(unit) === 0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 
                                    (selectedUnitId === unit.id ? 'bg-[#0A2540] text-white active:scale-95' : 'bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] active:scale-95')]">
                                {{ getAvailableQuantity(unit) === 0 ? 'Kamar Penuh' : (selectedUnitId === unit.id ? 'Kamar Terpilih' : 'Pilih Kamar') }}
                            </button>
                            <span v-if="getAvailableQuantity(unit) === 0" class="text-[10px] text-red-500 font-medium text-center leading-tight">Ubah jadwal atau pilih unit lain</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL DESKTOP (Detail Kamar) -->
        <!-- ============================================== -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isDesktopModalOpen && selectedDetailUnit" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4 hidden md:flex">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col" @click.stop>

                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-white shrink-0">
                        <h2 class="text-xl font-extrabold text-[#0A2540]">{{ selectedDetailUnit.name }}</h2>
                        <button @click="closeDetail" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center transition">
                            <X class="" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-grow overflow-y-auto p-6 flex gap-6">

                        <!-- Kiri: Foto -->
                        <div class="w-1/2 flex flex-col gap-3">
                            <div class="relative w-full h-[240px] md:h-[300px] rounded-xl overflow-hidden bg-gray-100 group shrink-0">
                                <img v-if="selectedDetailUnit.images?.length > 0" :src="selectedDetailUnit.images[activeDetailImageIndex].image_url" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                    <Image class="text-4xl mb-2" />
                                    <span class="text-xs">No image</span>
                                </div>

                                <template v-if="selectedDetailUnit.images?.length > 1">
                                    <button @click.prevent="prevDetailImage" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <ChevronLeft class="text-xs" />
                                    </button>
                                    <button @click.prevent="nextDetailImage" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <ChevronRight class="text-xs" />
                                    </button>
                                    <!-- Badges -->
                                    <div class="absolute bottom-3 right-3 bg-black/60 px-2 py-1 rounded text-[10px] text-white font-bold tracking-wider">
                                        {{ activeDetailImageIndex + 1 }} / {{ selectedDetailUnit.images.length }}
                                    </div>
                                </template>
                            </div>

                            <!-- Thumbnail List -->
                            <div v-if="selectedDetailUnit.images?.length > 1" class="flex gap-2 overflow-x-auto pb-2 snap-x">
                                <div v-for="(img, idx) in selectedDetailUnit.images" :key="idx"
                                    @click="activeDetailImageIndex = idx"
                                    class="w-20 aspect-video rounded-lg overflow-hidden shrink-0 cursor-pointer snap-center relative"
                                    :class="activeDetailImageIndex === idx ? 'ring-2 ring-[#FFC000]' : 'opacity-70 hover:opacity-100'">
                                    <img :src="img.image_url" class="w-full h-full object-cover" />
                                </div>
                            </div>
                        </div>

                        <!-- Kanan: Detail & Fasilitas -->
                        <div class="w-1/2 flex flex-col gap-6">
                            <!-- Info Utama -->
                            <div>
                                <h4 class="text-sm font-bold text-[#6C757D] uppercase tracking-wider mb-3">Info Kamar</h4>
                                <div class="space-y-3 text-sm text-[#0A2540]">
                                    <div v-for="(val, key) in (selectedDetailUnit?.detail || {})" :key="key" class="flex items-center gap-3">
                                        <Circle class="text-[6px] w-5 text-center text-gray-400" />
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-500">{{ key }}</span>
                                            <span class="font-medium">{{ val }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-200" />

                            <!-- Fasilitas -->
                            <div>
                                <h4 class="text-sm font-bold text-[#6C757D] uppercase tracking-wider mb-3">Fasilitas Kamar</h4>
                                <ul class="grid grid-cols-2 gap-y-2 gap-x-4 text-sm text-[#0A2540]">
                                    <li v-for="fac in selectedDetailUnit?.facilities" :key="fac.id" class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-[#FFC000] rounded-full shrink-0"></div>
                                        <span>{{ fac.name }}</span>
                                    </li>
                                </ul>
                                <div v-if="!selectedDetailUnit?.facilities?.length" class="text-sm text-gray-500 italic">Belum ada fasilitas.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer (Desktop Modal) -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 shrink-0 flex justify-between items-center">
                        <div>
                            <span class="text-xs text-gray-500 block">Harga mulai dari</span>
                            <div class="font-extrabold text-xl text-[#e65c00]">
                                {{ formatRupiah(getLowestPricing(selectedDetailUnit).price) }}
                                <span class="text-xs font-normal text-[#0A2540]">/{{ rentalUnitLabel }}</span>
                            </div>
                        </div>
                        <button @click="handleSelect(selectedDetailUnit, getLowestPricing(selectedDetailUnit)); closeDetail()" class="bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-extrabold py-3 px-8 rounded-xl transition-all shadow-sm">
                            Pesan Kamar Ini
                        </button>
                    </div>

                </div>
            </div>
        </Transition>


        <!-- ============================================== -->
        <!-- BOTTOM SHEET MOBILE (Detail Kamar) -->
        <!-- ============================================== -->
        <BottomSheet v-model="isMobileSheetOpen" :title="selectedDetailUnit?.name" heightClass="h-[85vh]">
            <template #default>
                <div v-if="selectedDetailUnit" class="flex flex-col h-full overflow-y-auto relative pb-24">

                    <!-- Carousel Mobile (Bottom Sheet) -->
                    <div class="relative w-full h-[250px] bg-gray-100 group shrink-0 overflow-hidden"
                         @touchstart="onTouchStart"
                         @touchend="(e) => onTouchEnd(e, nextDetailImage, prevDetailImage)">
                        <Transition name="fade">
                            <img v-if="selectedDetailUnit.images?.length > 0" :key="activeDetailImageIndex" :src="selectedDetailUnit.images[activeDetailImageIndex].image_url" class="w-full h-full object-cover absolute inset-0" />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400 absolute inset-0">
                                <Image class="text-4xl mb-2" />
                            </div>
                        </Transition>

                        <template v-if="selectedDetailUnit.images?.length > 1">
                            <button @click.prevent="prevDetailImage" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/40 text-white rounded-full flex items-center justify-center active:bg-black/70 z-10">
                                <ChevronLeft class="text-xs" />
                            </button>
                            <button @click.prevent="nextDetailImage" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/40 text-white rounded-full flex items-center justify-center active:bg-black/70 z-10">
                                <ChevronRight class="text-xs" />
                            </button>
                            <div class="absolute bottom-3 right-3 bg-black/60 px-2 py-1 rounded text-[10px] text-white font-bold tracking-wider z-10">
                                {{ activeDetailImageIndex + 1 }} / {{ selectedDetailUnit.images.length }}
                            </div>
                        </template>
                    </div>



                    <!-- Konten Detail Mobile -->
                    <div class="p-5 flex flex-col gap-6">
                        <!-- Info Utama -->
                        <div>
                            <h4 class="text-[11px] font-bold text-[#6C757D] uppercase tracking-wider mb-3">Info Kamar</h4>
                            <div class="space-y-3 text-sm text-[#0A2540]">
                                <div v-for="(val, key) in (selectedDetailUnit?.detail || {})" :key="key" class="flex items-center gap-3">
                                    <Circle class="text-[6px] w-5 text-center text-gray-400" />
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500">{{ key }}</span>
                                        <span class="font-medium">{{ val }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- Fasilitas -->
                        <div>
                            <h4 class="text-[11px] font-bold text-[#6C757D] uppercase tracking-wider mb-3">Fasilitas Kamar</h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div v-for="fac in selectedDetailUnit?.facilities" :key="fac.id" class="flex items-center gap-3">
                                    <Check class="text-[#FFC000] text-sm" />
                                    <span class="text-sm text-gray-700">{{ fac.name }}</span>
                                </div>
                                <div v-if="!selectedDetailUnit?.facilities?.length" class="text-sm text-gray-500 italic">Belum ada fasilitas.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer (Sticky di Mobile) -->
                <div v-if="selectedDetailUnit" class="absolute bottom-0 inset-x-0 p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20 flex justify-between items-center pb-safe">
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] text-gray-500 block mb-0.5">Mulai dari</span>
                        <div class="font-extrabold text-lg text-[#e65c00] truncate pr-2">
                            {{ formatRupiah(getLowestPricing(selectedDetailUnit).price) }}
                            <span class="text-[10px] font-normal text-[#0A2540]">/{{ rentalUnitLabel }}</span>
                        </div>
                    </div>
                    <button @click="handleSelect(selectedDetailUnit, getLowestPricing(selectedDetailUnit)); closeDetail()" class="bg-[#FFC000] active:bg-[#e6ad00] text-[#0A2540] font-extrabold py-3 px-6 rounded-xl transition-all shadow-sm text-sm shrink-0">
                        Pesan
                    </button>
                </div>
            </template>
        </BottomSheet>
    </div>
</template>

<style scoped>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 16px);
}
</style>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
