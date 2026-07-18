<script setup>
import { useHomeSearch } from '@/Composables/useHomeSearch';

const {
    isMobileSearchOpen,
    desktopActiveMenu,
    selectedAssets,
    searchQuery,
    formattedSchedule,
    parsedMinPrice,
    parsedMaxPrice,
    maxLimit,
    formatPriceShort,
    assetCategories,
    filteredLocations,
    desktopCalendarPage,
    prevDesktopMonth,
    nextDesktopMonth,
    monthsData,
    daysOfWeek,
    selectDate,
    isStartDate,
    isEndDate,
    isInRange,
    endDate,
    formattedMinPrice,
    formattedMaxPrice,
    handleMinPriceInput,
    handleMaxPriceInput,
    priceError,
    minPercent,
    maxPercent,
    startDrag,
    activeThumb
} = useHomeSearch();
</script>
<template>
    <div>
        <div class="relative w-full h-[450px] lg:h-[500px] bg-cover bg-center" style="background-image: url('/public.png');">

            <div class="absolute inset-0 bg-black/50"></div>

            <div class="relative z-10 flex flex-col justify-center h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="max-w-3xl mt-8 md:mt-0 text-center md:text-left">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight">
                        Semua Kebutuhan <span class="text-[#FFC000]">Sewa, </span><br class="hidden sm:block">
                        <span class="text-[#FFC000]">Dalam</span> Satu Platform
                    </h1>
                    <p class="mt-4 text-base sm:text-lg text-white/90 font-medium max-w-xl leading-relaxed mx-auto md:mx-0">
                        Temukan lahan, gedung, gudang, baliho, hunian, dan berbagai aset lainnya dengan mudah.
                    </p>
                </div>

                <!-- ==================== DESKTOP SEARCH BAR ==================== -->
                <!-- Overlay untuk menutup modal jika di klik di luar -->
                <div v-if="desktopActiveMenu" @click="desktopActiveMenu = null" class="fixed inset-0 z-40 bg-black/10 transition-opacity"></div>

                <div class="hidden md:flex mt-8 flex-col items-center w-full max-w-[850px] relative z-50">
                    <div class="bg-white rounded-full p-1 flex flex-row items-center w-full shadow-xl border border-[#6C757D]/10 relative transition-all duration-300">

                        <!-- Item 1: Jenis Aset -->
                        <div @click="desktopActiveMenu = desktopActiveMenu === 'jenis' ? null : 'jenis'" class="flex-1 flex items-center justify-between px-6 1 cursor-pointer group hover:bg-[#F8F9FA] rounded-full transition-all duration-200 w-full" :class="desktopActiveMenu === 'jenis' ? 'bg-[#F8F9FA] shadow-inner' : ''">
                            <div class="flex flex-col">
                                <span class="text-[13px] font-bold text-[#0A2540] tracking-wide">Jenis Aset</span>
                                <span class="text-[12px] text-[#6C757D] truncate max-w-[120px]" :class="selectedAssets.length ? 'text-[#0A2540] font-medium' : ''">
                                    {{ selectedAssets.length ? selectedAssets.join(', ') : 'Semua Aset' }}
                                </span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="h-6 w-px bg-[#6C757D]/20 mx-0.5"></div>

                        <!-- Item 2: Lokasi -->
                        <div @click="desktopActiveMenu = desktopActiveMenu === 'lokasi' ? null : 'lokasi'" class="flex-1 flex items-center justify-between px-6 py-1 cursor-pointer group hover:bg-[#F8F9FA] rounded-full transition-all duration-200 w-full" :class="desktopActiveMenu === 'lokasi' ? 'bg-[#F8F9FA] shadow-inner' : ''">
                            <div class="flex flex-col">
                                <span class="text-[13px] font-bold text-[#0A2540] tracking-wide">Lokasi</span>
                                <span class="text-[12px] text-[#6C757D] truncate max-w-[120px]">{{ searchQuery || 'Cari destinasi' }}</span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="h-6 w-px bg-[#6C757D]/20 mx-0.5"></div>

                        <!-- Item 3: Jadwal -->
                        <div @click="desktopActiveMenu = desktopActiveMenu === 'jadwal' ? null : 'jadwal'" class="flex-1 flex items-center justify-between px-6 py-1 cursor-pointer group hover:bg-[#F8F9FA] rounded-full transition-all duration-200 w-full" :class="desktopActiveMenu === 'jadwal' ? 'bg-[#F8F9FA] shadow-inner' : ''">
                            <div class="flex flex-col">
                                <span class="text-[13px] font-bold text-[#0A2540] tracking-wide">Jadwal</span>
                                <span class="text-[12px] text-[#6C757D] truncate max-w-[120px]">{{ formattedSchedule }}</span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="h-6 w-px bg-[#6C757D]/20 mx-0.5"></div>

                        <!-- Item 4: Rentang Harga -->
                        <div @click="desktopActiveMenu = desktopActiveMenu === 'harga' ? null : 'harga'" class="flex-1 flex items-center justify-between pl-6 pr-2 py-1 cursor-pointer group hover:bg-[#F8F9FA] rounded-full transition-all duration-200 w-full" :class="desktopActiveMenu === 'harga' ? 'bg-[#F8F9FA] shadow-inner' : ''">
                            <div class="flex flex-col">
                                <span class="text-[13px] font-bold text-[#0A2540] tracking-wide">Harga</span>
                                <span class="text-[12px] text-[#6C757D]" :class="(parsedMinPrice > 0 || parsedMaxPrice < maxLimit) ? 'text-[#0A2540] font-medium' : ''">
                                    {{ (parsedMinPrice > 0 || parsedMaxPrice < maxLimit) ? (formatPriceShort(parsedMinPrice) + ' - ' + formatPriceShort(parsedMaxPrice)) : 'Budget Anda' }}
                                </span>
                            </div>
                            <!-- Tombol Search -->
                            <button class="bg-[#FFC000] hover:bg-[#e6ad00] w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 shadow-sm hover:shadow-md flex-shrink-0 ml-4 active:scale-95">
                                <i class="fa-solid fa-magnifying-glass text-[#0A2540] text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- DESKTOP MODAL DROPDOWN -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="transform scale-95 opacity-0 -translate-y-4"
                        enter-to-class="transform scale-100 opacity-100 translate-y-0"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="transform scale-100 opacity-100 translate-y-0"
                        leave-to-class="transform scale-95 opacity-0 -translate-y-4"
                    >
                        <div v-if="desktopActiveMenu" class="absolute top-[110%] w-full bg-white rounded-[32px] shadow-2xl border border-[#6C757D]/10 p-6 z-50 origin-top flex flex-col max-h-[75vh] overflow-y-auto hide-scrollbar">

                            <!-- ================== DESKTOP: JENIS ASET ================== -->
                            <div v-if="desktopActiveMenu === 'jenis'" class="w-full px-2">
                                <h2 class="text-lg font-extrabold text-[#0A2540] mb-4">Pilih Jenis Aset</h2>

                                <div class="mb-4">
                                    <label class="inline-flex items-center gap-3 cursor-pointer group">
                                        <div class="w-4 h-4 rounded flex items-center justify-center transition border border-[#6C757D]/30 group-hover:border-[#0A2540]"
                                            :class="selectedAssets.length === 0 ? 'bg-[#0A2540] border-[#0A2540]' : 'bg-transparent'">
                                            <i v-if="selectedAssets.length === 0" class="fa-solid fa-check text-white text-[9px]"></i>
                                        </div>
                                        <span class="text-[13px] font-bold text-[#0A2540] group-hover:font-medium transition">Semua</span>
                                        <input type="checkbox" :checked="selectedAssets.length === 0" @change="selectedAssets = []" class="hidden">
                                    </label>
                                </div>

                                <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                                    <div v-for="category in assetCategories" :key="category.name">
                                        <h3 class="text-[11px] font-bold text-[#6C757D] uppercase tracking-wider mb-2">{{ category.name }}</h3>
                                        <div class="space-y-2">
                                            <label v-for="item in category.items" :key="item" class="flex items-center gap-3 cursor-pointer group">
                                                <div class="w-4 h-4 rounded flex items-center justify-center transition border border-[#6C757D]/30 group-hover:border-[#0A2540]"
                                                    :class="selectedAssets.includes(item) ? 'bg-[#0A2540] border-[#0A2540]' : 'bg-transparent'">
                                                    <i v-if="selectedAssets.includes(item)" class="fa-solid fa-check text-white text-[9px]"></i>
                                                </div>
                                                <span class="text-[13px] text-[#0A2540] group-hover:font-medium transition">{{ item }}</span>
                                                <input type="checkbox" :value="item" v-model="selectedAssets" class="hidden">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ================== DESKTOP: LOKASI ================== -->
                            <div v-if="desktopActiveMenu === 'lokasi'" class="w-full max-w-sm mx-auto">
                                <h2 class="text-lg font-extrabold text-[#0A2540] mb-3">Pencarian Lokasi</h2>
                                <div class="flex items-center gap-3 border border-[#6C757D]/30 rounded-xl p-2 bg-white mb-4 focus-within:border-[#0A2540] focus-within:ring-2 focus-within:ring-[#0A2540]/20 transition">
                                    <i class="fa-solid fa-magnifying-glass text-[#0A2540] pl-1 text-sm"></i>
                                    <input v-model="searchQuery" type="text" placeholder="Cari destinasi..." class="w-full outline-none text-[#0A2540] font-medium text-sm bg-transparent">
                                </div>

                                <h3 class="text-[11px] font-bold text-[#6C757D] mb-3 uppercase tracking-wider">Disarankan</h3>
                                <div class="space-y-2 max-h-[200px] overflow-y-auto pr-2">
                                    <div v-for="item in filteredLocations" :key="item.id" @click="searchQuery = item.title; desktopActiveMenu = 'jadwal'" class="flex gap-3 items-center cursor-pointer group hover:bg-gray-50 p-2 -mx-2 rounded-xl transition">
                                        <div :class="`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${item.bg}`">
                                            <i :class="`${item.icon} ${item.iconColor} text-sm`"></i>
                                        </div>
                                        <div class="border-b border-[#6C757D]/10 pb-2 pt-1 w-full group-last:border-0">
                                            <h4 class="font-bold text-[13px] text-[#0A2540]">{{ item.title }}</h4>
                                            <p class="text-[11px] text-[#6C757D] mt-0.5 truncate">{{ item.desc }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ================== DESKTOP: JADWAL (DUAL CALENDAR) ================== -->
                            <div v-if="desktopActiveMenu === 'jadwal'" class="w-full flex flex-col">

                                <div class="flex justify-between items-center mb-4 px-2">
                                    <button @click="prevDesktopMonth" class="w-8 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition" :class="desktopCalendarPage === 0 ? 'opacity-30 cursor-not-allowed' : ''">
                                        <i class="fa-solid fa-chevron-left text-[#0A2540] text-sm"></i>
                                    </button>
                                    <div class="flex gap-8 w-full px-4">
                                        <h3 class="flex-1 text-center text-[15px] font-bold text-[#0A2540]">{{ monthsData[desktopCalendarPage]?.title }}</h3>
                                        <h3 class="flex-1 text-center text-[15px] font-bold text-[#0A2540]">{{ monthsData[desktopCalendarPage + 1]?.title }}</h3>
                                    </div>
                                    <button @click="nextDesktopMonth" class="w-8 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                                        <i class="fa-solid fa-chevron-right text-[#0A2540] text-sm"></i>
                                    </button>
                                </div>

                                <div class="flex gap-8 px-2">
                                    <!-- Kalender Kiri -->
                                    <div class="flex-1">
                                        <div class="grid grid-cols-7 gap-y-5 mb-1">
                                            <div v-for="day in daysOfWeek" :key="'d1-'+day" class="text-center text-[11px] font-bold text-[#6C757D]">{{ day }}</div>
                                            <div v-for="i in monthsData[desktopCalendarPage]?.emptyDaysStart" :key="'e1-'+i"></div>
                                            <div v-for="date in monthsData[desktopCalendarPage]?.daysInMonth" :key="'d1-'+date" class="relative flex justify-center items-center h-10" @click="selectDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)">
                                                <!-- KONEKTOR RENTANG -->
                                                <div v-if="isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                                <!-- TANGGAL -->
                                                <div class="relative z-10 w-9 h-9 flex flex-col items-center justify-center rounded-full text-[13px] font-bold cursor-pointer transition"
                                                    :class="{ 'bg-[#1A1A1A] text-white shadow-md': isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) || isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date),
                                                            'text-[#1A1A1A]': isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date),
                                                            'text-[#0A2540] hover:border hover:border-[#1A1A1A]': !isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) && !isInRange(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date) }">
                                                    <span>{{ date }}</span>
                                                </div>
                                                <div v-if="isStartDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                                <div v-else-if="isEndDate(monthsData[desktopCalendarPage].year, monthsData[desktopCalendarPage].month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kalender Kanan -->
                                    <div class="flex-1">
                                        <div class="grid grid-cols-7 gap-y-5 mb-1">
                                            <div v-for="day in daysOfWeek" :key="'d2-'+day" class="text-center text-[11px] font-bold text-[#6C757D]">{{ day }}</div>
                                            <div v-for="i in monthsData[desktopCalendarPage + 1]?.emptyDaysStart" :key="'e2-'+i"></div>
                                            <div v-for="date in monthsData[desktopCalendarPage + 1]?.daysInMonth" :key="'d2-'+date" class="relative flex justify-center items-center h-10" @click="selectDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)">
                                                <!-- KONEKTOR RENTANG -->
                                                <div v-if="isStartDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isInRange(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                                <div v-else-if="isEndDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                                <!-- TANGGAL -->
                                                <div class="relative z-10 w-9 h-9 flex flex-col items-center justify-center rounded-full text-[13px] font-bold cursor-pointer transition"
                                                    :class="{ 'bg-[#1A1A1A] text-white shadow-md': isStartDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) || isEndDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date),
                                                            'text-[#1A1A1A]': isInRange(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date),
                                                            'text-[#0A2540] hover:border hover:border-[#1A1A1A]': !isStartDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) && !isEndDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) && !isInRange(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date) }">
                                                    <span>{{ date }}</span>
                                                </div>
                                                <div v-if="isStartDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                                <div v-else-if="isEndDate(monthsData[desktopCalendarPage+1].year, monthsData[desktopCalendarPage+1].month, date)" class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ================== DESKTOP: RENTANG HARGA ================== -->
                            <div v-if="desktopActiveMenu === 'harga'" class="w-full max-w-sm mx-auto">
                                <h2 class="text-lg font-extrabold text-[#0A2540] mb-2">Rentang Harga</h2>
                                <p class="text-[12px] text-[#6C757D] mb-5">Sesuaikan batas anggaran yang Anda inginkan.</p>

                                <!-- Input Harga -->
                                <div class="flex gap-3 mb-1">
                                    <div class="flex-1 border rounded-xl p-3 bg-white transition-colors" :class="priceError ? 'border-red-500' : 'border-[#6C757D]/30'">
                                        <label class="block text-[11px] font-bold mb-1 transition-colors" :class="priceError ? 'text-red-500' : 'text-[#6C757D]'">Min Harga</label>
                                        <div class="flex items-center text-[#0A2540] font-bold text-sm">
                                            <span class="mr-1">Rp</span>
                                            <input :value="formattedMinPrice" @input="handleMinPriceInput" type="text" class="w-full outline-none bg-transparent" />
                                        </div>
                                    </div>
                                    <div class="flex-1 border rounded-xl p-3 bg-white transition-colors" :class="priceError ? 'border-red-500' : 'border-[#6C757D]/30'">
                                        <label class="block text-[11px] font-bold mb-1 transition-colors" :class="priceError ? 'text-red-500' : 'text-[#6C757D]'">Maks Harga</label>
                                        <div class="flex items-center text-[#0A2540] font-bold text-sm">
                                            <span class="mr-1">Rp</span>
                                            <input :value="formattedMaxPrice" @input="handleMaxPriceInput" type="text" class="w-full outline-none bg-transparent" />
                                        </div>
                                    </div>
                                </div>
                                <p v-if="priceError" class="text-[11px] font-bold text-red-500 mt-1 mb-6">{{ priceError }}</p>
                                <div v-else class="mb-6 mt-1 h-[16px]"></div>

                                <!-- Slider -->
                                <div class="mb-2 mt-8 relative h-1.5 mx-2" ref="sliderTrack">
                                    <!-- Background track -->
                                    <div class="absolute inset-0 bg-[#6C757D]/20 rounded-full"></div>
                                    <!-- Active track -->
                                    <div class="absolute h-full bg-[#0A2540] rounded-full" :style="`left: ${minPercent}%; right: ${100 - maxPercent}%`"></div>

                                    <!-- Min Thumb & Tooltip -->
                                    <div @mousedown="startDrag($event, 'min')" @touchstart.prevent="startDrag($event, 'min')"
                                        class="absolute top-1/2 -translate-y-1/2 w-5 h-5 bg-white border-[3px] border-[#0A2540] rounded-full shadow-sm z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                        :style="`left: calc(${minPercent}% - 10px)`">
                                        <div v-show="activeThumb === 'min'" class="absolute -top-[45px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[11px] font-bold px-2.5 py-1.5 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[30px]">
                                            {{ parsedMinPrice >= maxLimit ? '> 10 Jt' : formatPriceShort(parsedMinPrice) }}
                                            <div class="absolute -bottom-[4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                        </div>
                                    </div>

                                    <!-- Max Thumb & Tooltip -->
                                    <div @mousedown="startDrag($event, 'max')" @touchstart.prevent="startDrag($event, 'max')"
                                        class="absolute top-1/2 -translate-y-1/2 w-5 h-5 bg-white border-[3px] border-[#0A2540] rounded-full shadow-sm z-20 cursor-grab active:cursor-grabbing hover:scale-110 transition-transform"
                                        :style="`left: calc(${maxPercent}% - 10px)`">
                                        <div v-show="activeThumb === 'max'" class="absolute -top-[45px] left-1/2 -translate-x-1/2 bg-[#0A2540] text-white text-[11px] font-bold px-2.5 py-1.5 rounded-full whitespace-nowrap shadow-lg flex items-center justify-center min-w-[30px]">
                                            {{ parsedMaxPrice >= maxLimit ? '> 10 Jt' : formatPriceShort(parsedMaxPrice) }}
                                            <div class="absolute -bottom-[4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-[#0A2540] rotate-45 rounded-sm -z-10"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between mt-3 mx-2 text-[10px] font-bold text-[#6C757D]">
                                    <span>Rp0</span>
                                    <span>>Rp10 jt</span>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- ==================== MOBILE SEARCH TRIGGER ==================== -->
                <div class="md:hidden mt-6 w-full px-2">
                    <button
                        @click="isMobileSearchOpen = true"
                        class="w-full bg-white rounded-full py-2 px-6 flex items-center justify-center gap-1.5 shadow-lg text-[#0A2540] font-bold text-base active:scale-95 transition-transform"
                    >
                        <i class="fa-solid fa-magnifying-glass text-[#0A2540]"></i>
                        Mulai pencarian
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
