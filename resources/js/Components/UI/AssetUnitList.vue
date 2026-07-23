<script setup>
import { ref, computed } from 'vue';
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
    }
});

const emit = defineEmits(['select']);

const formatRupiah = (value) => {
    if (!value) return 'Hubungi Pemilik';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
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

const nextDetailImage = () => {
    if (!selectedDetailUnit.value || !selectedDetailUnit.value.images) return;
    activeDetailImageIndex.value = (activeDetailImageIndex.value + 1) % selectedDetailUnit.value.images.length;
};

const prevDetailImage = () => {
    if (!selectedDetailUnit.value || !selectedDetailUnit.value.images) return;
    activeDetailImageIndex.value = (activeDetailImageIndex.value - 1 + selectedDetailUnit.value.images.length) % selectedDetailUnit.value.images.length;
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
        <div v-for="unit in units" :key="unit.id" class="w-full bg-white sm:bg-white rounded-none sm:rounded-2xl shadow-none sm:shadow-sm border-b sm:border border-gray-200 sm:border-gray-100 hover:shadow-md transition-shadow overflow-hidden group flex flex-col sm:flex-row relative">

            <!-- MOBILE: Layout sesuai screenshot -->
            <div class="sm:hidden flex flex-col bg-[#F8F9FA] pb-2">
                <!-- Image Carousel Mobile -->
                <div class="relative w-full aspect-[16/9] bg-gray-200" @click="openDetail(unit)">
                    <img v-if="getUnitImage(unit)" :src="getUnitImage(unit)" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center bg-slate-100">
                        <i class="fa-solid fa-image text-3xl text-gray-300"></i>
                    </div>
                    <!-- Indicator Dots (Simulasi) -->
                    <div v-if="unit.images && unit.images.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-white"></div>
                        <div class="w-2 h-2 rounded-full bg-white/50" v-for="n in Math.min(3, unit.images.length - 1)" :key="n"></div>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Title -->
                    <h3 class="font-extrabold text-[#0A2540] text-lg mb-3">{{ unit.name }}</h3>

                    <!-- Specs Row (Luas, Bed, View) -->
                    <div class="flex flex-wrap gap-x-4 gap-y-2 text-[11px] font-bold text-[#0A2540] mb-2">
                        <span v-if="unit.detail?.luas" class="flex items-center gap-1.5"><i class="fa-solid fa-ruler-combined"></i> {{ unit.detail.luas }}</span>
                        <span v-if="unit.detail?.bed" class="flex items-center gap-1.5"><i class="fa-solid fa-bed"></i> {{ unit.detail.bed }}</span>
                        <span v-if="unit.detail?.view" class="flex items-center gap-1.5"><i class="fa-solid fa-mountain-sun"></i> {{ unit.detail.view }}</span>
                    </div>

                    <!-- Facility Row (Highlights) -->
                    <div v-if="unit.detail?.fasilitas?.length" class="flex flex-wrap gap-x-4 gap-y-2 text-[11px] font-bold text-[#FFC000]">
                        <span v-for="fac in unit.detail.fasilitas.slice(0, 3)" :key="fac" class="flex items-center gap-1.5 text-[#0A2540]">
                            <i class="fa-solid fa-check text-[#FFC000]"></i> {{ fac }}
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
                            <div v-if="unit.detail?.kapasitas" class="flex items-center gap-3">
                                <i class="fa-solid fa-user-group w-4 text-center"></i> {{ unit.detail.kapasitas }}
                            </div>
                            <div v-if="unit.detail?.bed" class="flex items-center gap-3">
                                <i class="fa-solid fa-bed w-4 text-center"></i> {{ unit.detail.bed }}
                            </div>
                        </div>

                        <div class="flex justify-between items-end">
                            <!-- Price Column -->
                            <div class="flex flex-col items-end">
                                <span class="text-[11px] text-gray-400 font-bold line-through mb-0.5">{{ formatRupiah(getLowestPricing(unit).price * 1.1) }}</span>
                                <span class="font-extrabold text-[#e65c00] text-lg">{{ formatRupiah(getLowestPricing(unit).price) }}</span>
                                <span class="text-[10px] text-gray-500 mt-1">Total {{ formatRupiah(getLowestPricing(unit).price * 1.11) }}</span>
                                <span class="text-[9px] text-gray-400">(termasuk pajak & biaya)</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex justify-end mt-4">
                            <button @click="handleSelect(unit, getLowestPricing(unit))" class="bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-extrabold py-2 px-8 rounded-full text-xs shadow-sm transition-transform">
                                Pilih
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DESKTOP: 1 Image (Left) -->
            <div class="hidden sm:block w-[220px] lg:w-[260px] flex-shrink-0 aspect-[4/3] lg:aspect-[16/9] relative bg-slate-100 overflow-hidden cursor-pointer" @click="openDetail(unit)">
                <img v-if="getUnitImage(unit)" :src="getUnitImage(unit)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-300">
                    <i class="fa-solid fa-image text-3xl mb-1"></i>
                </div>
                <!-- Overlay detail text -->
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent pt-6 pb-2 text-center pointer-events-none">
                    <span class="text-white text-xs font-bold shadow-sm">Lihat Detail</span>
                </div>
            </div>

            <!-- DESKTOP: Info & Price (Right) -->
            <div class="hidden sm:flex flex-row flex-grow p-4 gap-3 bg-white min-w-0">
                <!-- Info (Name, Specs, Capacity) -->
                <div class="flex flex-col flex-grow justify-between min-w-0 cursor-pointer" @click="openDetail(unit)">
                    <div>
                        <h3 class="font-bold text-lg leading-tight text-[#0A2540] truncate mb-1.5 group-hover:text-[#FFC000] transition-colors">
                            {{ unit.name }}
                        </h3>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 font-medium">
                            <div v-if="unit.detail?.luas" class="flex items-center gap-1.5">
                                <i class="fa-solid fa-ruler-combined text-[#FFC000]"></i>
                                <span>{{ unit.detail.luas }}</span>
                            </div>
                            <div v-if="unit.detail?.bed" class="flex items-center gap-1.5">
                                <i class="fa-solid fa-bed text-[#FFC000]"></i>
                                <span>{{ unit.detail.bed }}</span>
                            </div>
                            <div v-if="unit.detail?.kapasitas" class="flex items-center gap-1.5">
                                <i class="fa-solid fa-user-group text-[#FFC000]"></i>
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

                    <div class="mt-2 text-[11px] font-bold text-red-500">
                        Sisa {{ unit.quantity }} unit!
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
                        <span class="text-[10px] text-gray-400 block mt-0.5">Di luar pajak & biaya</span>
                    </div>

                    <button @click="handleSelect(unit, getLowestPricing(unit))" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] text-xs font-extrabold py-2 px-4 rounded-lg transition-all shadow-sm">
                        Pesan
                    </button>
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
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-grow overflow-y-auto p-6 flex gap-6">

                        <!-- Kiri: Foto -->
                        <div class="w-1/2 flex flex-col gap-3">
                            <div class="relative w-full aspect-[4/3] rounded-xl overflow-hidden bg-gray-100 group">
                                <img v-if="selectedDetailUnit.images?.length > 0" :src="selectedDetailUnit.images[activeDetailImageIndex].image_url" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-image text-4xl mb-2"></i>
                                    <span class="text-xs">No image</span>
                                </div>

                                <template v-if="selectedDetailUnit.images?.length > 1">
                                    <button @click.prevent="prevDetailImage" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                    </button>
                                    <button @click.prevent="nextDetailImage" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
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
                                    <div v-if="selectedDetailUnit.detail?.luas" class="flex items-center gap-3">
                                        <i class="fa-solid fa-ruler-combined w-5 text-center text-[#FFC000]"></i>
                                        <span class="font-medium">{{ selectedDetailUnit.detail.luas }}</span>
                                    </div>
                                    <div v-if="selectedDetailUnit.detail?.bed" class="flex items-center gap-3">
                                        <i class="fa-solid fa-bed w-5 text-center text-[#FFC000]"></i>
                                        <span class="font-medium">{{ selectedDetailUnit.detail.bed }}</span>
                                    </div>
                                    <div v-if="selectedDetailUnit.detail?.kapasitas" class="flex items-center gap-3">
                                        <i class="fa-solid fa-user-group w-5 text-center text-[#FFC000]"></i>
                                        <span class="font-medium">{{ selectedDetailUnit.detail.kapasitas }}</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-200" />

                            <!-- Fasilitas -->
                            <div v-if="selectedDetailUnit.detail?.fasilitas?.length">
                                <h4 class="text-sm font-bold text-[#6C757D] uppercase tracking-wider mb-3">Fasilitas Kamar</h4>
                                <ul class="grid grid-cols-2 gap-y-2 gap-x-4 text-sm text-[#0A2540]">
                                    <li v-for="fac in selectedDetailUnit.detail.fasilitas" :key="fac" class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-[#FFC000] rounded-full shrink-0"></div>
                                        <span>{{ fac }}</span>
                                    </li>
                                </ul>
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

                    <!-- Carousel Mobile -->
                    <div class="relative w-full aspect-[4/3] bg-gray-100 group shrink-0">
                        <img v-if="selectedDetailUnit.images?.length > 0" :src="selectedDetailUnit.images[activeDetailImageIndex].image_url" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                            <i class="fa-solid fa-image text-4xl mb-2"></i>
                        </div>

                        <template v-if="selectedDetailUnit.images?.length > 1">
                            <button @click.prevent="prevDetailImage" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/40 text-white rounded-full flex items-center justify-center active:bg-black/70">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            <button @click.prevent="nextDetailImage" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/40 text-white rounded-full flex items-center justify-center active:bg-black/70">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                            <div class="absolute bottom-3 right-3 bg-black/60 px-2 py-1 rounded text-[10px] text-white font-bold tracking-wider">
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
                                <div v-if="selectedDetailUnit.detail?.luas" class="flex items-center gap-3">
                                    <i class="fa-solid fa-ruler-combined w-5 text-center text-[#FFC000]"></i>
                                    <span class="font-medium">{{ selectedDetailUnit.detail.luas }}</span>
                                </div>
                                <div v-if="selectedDetailUnit.detail?.bed" class="flex items-center gap-3">
                                    <i class="fa-solid fa-bed w-5 text-center text-[#FFC000]"></i>
                                    <span class="font-medium">{{ selectedDetailUnit.detail.bed }}</span>
                                </div>
                                <div v-if="selectedDetailUnit.detail?.kapasitas" class="flex items-center gap-3">
                                    <i class="fa-solid fa-user-group w-5 text-center text-[#FFC000]"></i>
                                    <span class="font-medium">{{ selectedDetailUnit.detail.kapasitas }}</span>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- Fasilitas -->
                        <div v-if="selectedDetailUnit.detail?.fasilitas?.length">
                            <h4 class="text-[11px] font-bold text-[#6C757D] uppercase tracking-wider mb-3">Fasilitas Kamar</h4>
                            <ul class="flex flex-col gap-3 text-sm text-[#0A2540]">
                                <li v-for="fac in selectedDetailUnit.detail.fasilitas" :key="fac" class="flex items-center gap-3">
                                    <div class="w-1.5 h-1.5 bg-[#FFC000] rounded-full shrink-0"></div>
                                    <span>{{ fac }}</span>
                                </li>
                            </ul>
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
