<script setup>
import { ChevronLeft, ChevronRight, ChevronDown, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    asset: Object,
});

const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const selectedUnitForCalendar = ref(props.asset.units?.[0]?.id || null);

const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const getCalendarDays = (year, month) => {
    const date = new Date(year, month, 1);
    const days = [];
    while (date.getMonth() === month) {
        days.push(new Date(date));
        date.setDate(date.getDate() + 1);
    }
    let firstDay = days[0].getDay();
    const blanks = Array.from({ length: firstDay }, () => null);
    return [...blanks, ...days];
};

const leftCalendarDays = computed(() => getCalendarDays(currentYear.value, currentMonth.value));
const rightCalendarDays = computed(() => {
    let m = currentMonth.value + 1;
    let y = currentYear.value;
    if (m > 11) { m = 0; y++; }
    return getCalendarDays(y, m);
});

const rightMonthName = computed(() => {
    let m = currentMonth.value + 1;
    if (m > 11) m = 0;
    return monthNames[m];
});
const rightYear = computed(() => {
    let m = currentMonth.value + 1;
    return m > 11 ? currentYear.value + 1 : currentYear.value;
});

// Real DB Booking Logic
const isDateBooked = (date) => {
    if (!date) return false;

    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const formattedDate = `${y}-${m}-${d}`;

    let relevantBookings = [];
    if (props.asset.type?.allow_units && props.asset.units?.length > 0) {
        const unit = props.asset.units.find(u => u.id === selectedUnitForCalendar.value);
        if (unit && unit.bookings) relevantBookings = unit.bookings;
    } else {
        if (props.asset.bookings) relevantBookings = props.asset.bookings;
    }

    return relevantBookings.some(booking => {
        return formattedDate >= booking.start_date && formattedDate < booking.end_date;
    });
};

// Search Availability Logic
const searchCheckIn = ref('');
const searchCheckOut = ref('');
const searchResult = ref(null);

const checkAvailability = () => {
    searchResult.value = null;
    if (!searchCheckIn.value || !searchCheckOut.value) {
        searchResult.value = { status: 'error', message: 'Pilih tanggal Check In dan Check Out.' };
        return;
    }
    if (searchCheckIn.value >= searchCheckOut.value) {
         searchResult.value = { status: 'error', message: 'Check Out harus lebih dari Check In.' };
         return;
    }

    let relevantBookings = [];
    if (props.asset.type?.allow_units && props.asset.units?.length > 0) {
        const unit = props.asset.units.find(u => u.id === selectedUnitForCalendar.value);
        if (unit && unit.bookings) relevantBookings = unit.bookings;
    } else {
        if (props.asset.bookings) relevantBookings = props.asset.bookings;
    }

    const isConflict = relevantBookings.some(booking => {
        return searchCheckIn.value < booking.end_date && searchCheckOut.value > booking.start_date;
    });

    if (isConflict) {
        searchResult.value = { status: 'booked', message: 'Jadwal terisi di rentang tanggal tersebut.' };
    } else {
        searchResult.value = { status: 'available', message: 'Unit tersedia di rentang tanggal tersebut.' };
    }
};
</script>

<template>
    <div class="animate-in fade-in duration-300">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
            <!-- Navigation & Filter -->
            <!-- Mobile Nav -->
            <div class="flex justify-between md:hidden mb-4">
                <button @click="prevMonth" class="text-slate-400 hover:text-slate-700 transition px-4 py-2"><ChevronLeft class="text-xl" /></button>
                <button @click="nextMonth" class="text-slate-400 hover:text-slate-700 transition px-4 py-2"><ChevronRight class="text-xl" /></button>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 relative">
                <!-- Desktop Nav Left -->
                <button @click="prevMonth" class="hidden md:block text-slate-400 hover:text-slate-700 transition px-2 py-1 absolute left-0"><ChevronLeft class="text-lg" /></button>

                <!-- Search Bar Pill -->
                <div class="flex-1 flex justify-center w-full px-0 md:px-12">
                    <div class="flex flex-col sm:flex-row items-center bg-white p-1.5 rounded-2xl sm:rounded-full border border-slate-200 shadow-sm w-full max-w-fit mx-auto gap-2 sm:gap-0">

                        <!-- Unit Select -->
                        <div v-if="asset.type?.allow_units && asset.units?.length > 0" class="w-full sm:w-auto px-4 border-b sm:border-b-0 sm:border-r border-slate-100 flex items-center relative">
                            <select v-model="selectedUnitForCalendar" class="w-full text-sm border-none focus:ring-0 py-2.5 bg-transparent text-slate-700 font-medium cursor-pointer appearance-none pr-8">
                                <option v-for="u in asset.units" :key="u.id" :value="u.id">Unit: {{ u.name }}</option>
                            </select>
                            <ChevronDown class="absolute right-4 text-slate-400 pointer-events-none text-xs" />
                        </div>

                        <!-- Dates -->
                        <div class="flex items-center px-4 w-full sm:w-auto justify-center">
                            <input type="date" v-model="searchCheckIn" class="text-sm border-none focus:ring-0 py-2.5 bg-transparent text-slate-700 font-medium w-full max-w-[130px] cursor-pointer" title="Check In">
                            <span class="text-slate-300 px-2">-</span>
                            <input type="date" v-model="searchCheckOut" class="text-sm border-none focus:ring-0 py-2.5 bg-transparent text-slate-700 font-medium w-full max-w-[130px] cursor-pointer" title="Check Out">
                        </div>

                        <!-- Button -->
                        <div class="px-1.5 pb-1.5 sm:pb-0 w-full sm:w-auto mt-1 sm:mt-0">
                            <button @click="checkAvailability" class="w-full sm:w-auto bg-[#0A2540] hover:bg-slate-800 text-white rounded-xl sm:rounded-full px-7 py-2.5 text-sm font-bold transition">
                                Cek
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Nav Right -->
                <button @click="nextMonth" class="hidden md:block text-slate-400 hover:text-slate-700 transition px-2 py-1 absolute right-0"><ChevronRight class="text-lg" /></button>
            </div>

            <!-- Search Result Alert -->
            <div v-if="searchResult" class="mb-6 max-w-xl mx-auto flex justify-center animate-in fade-in slide-in-from-top-2">
                <div :class="{'bg-rose-50 border-rose-200 text-rose-700': searchResult.status === 'error' || searchResult.status === 'booked', 'bg-emerald-50 border-emerald-200 text-emerald-700': searchResult.status === 'available'}" class="px-4 py-3 rounded-xl border font-semibold text-sm w-full text-center flex items-center justify-center gap-2">
                    {{ searchResult.message }}
                    <button @click="searchResult = null" class="ml-2 opacity-50 hover:opacity-100 transition"><X class="" /></button>
                </div>
            </div>

            <!-- Dual Calendars Container -->
            <div class="flex flex-col md:flex-row gap-8 lg:gap-16 w-full justify-center">
                <!-- LEFT CALENDAR -->
                <div class="flex-1 max-w-xs mx-auto w-full">
                    <h3 class="text-center font-bold text-[#0A2540] text-base mb-6">{{ monthNames[currentMonth] }} {{ currentYear }}</h3>
                    <div class="grid grid-cols-7 gap-y-4 gap-x-1 mb-4">
                        <div v-for="day in ['Min', 'Sn', 'Sl', 'R', 'Km', 'J', 'Sb']" :key="day" class="text-center text-xs font-bold text-slate-500">
                            {{ day }}
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-y-3 gap-x-1">
                        <div v-for="(day, index) in leftCalendarDays" :key="'l'+index" class="flex items-center justify-center">
                            <div v-if="day" :class="isDateBooked(day) ? 'text-slate-300 cursor-not-allowed' : 'text-[#0A2540] font-bold hover:bg-slate-100 cursor-pointer'" class="w-8 h-8 flex items-center justify-center rounded-full text-sm transition">
                                {{ day.getDate() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CALENDAR (Desktop Only or Stacked on Mobile) -->
                <div class="flex-1 max-w-xs mx-auto w-full hidden md:block">
                    <h3 class="text-center font-bold text-[#0A2540] text-base mb-6">{{ rightMonthName }} {{ rightYear }}</h3>
                    <div class="grid grid-cols-7 gap-y-4 gap-x-1 mb-4">
                        <div v-for="day in ['Min', 'Sn', 'Sl', 'R', 'Km', 'J', 'Sb']" :key="'r'+day" class="text-center text-xs font-bold text-slate-500">
                            {{ day }}
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-y-3 gap-x-1">
                        <div v-for="(day, index) in rightCalendarDays" :key="'r_day'+index" class="flex items-center justify-center">
                            <div v-if="day" :class="isDateBooked(day) ? 'text-slate-300 cursor-not-allowed' : 'text-[#0A2540] font-bold hover:bg-slate-100 cursor-pointer'" class="w-8 h-8 flex items-center justify-center rounded-full text-sm transition">
                                {{ day.getDate() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</template>
