<script setup>
import { AlertCircle, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    assetTitle: String,
    assetType: String,
    bookedDates: {
        type: Array,
        default: () => []
    },
    startDate: Date,
    endDate: Date,
    selectedRentalMode: String,
    activeScheduleMode: String,
    startTime: String,
    endTime: String,
    durationMonths: Number,
    nightsCount: Number,
    showDateError: Boolean,
    formattedDateRange: String
});

const emit = defineEmits([
    'update:startDate',
    'update:endDate',
    'update:selectedRentalMode',
    'update:startTime',
    'update:endTime',
    'update:durationMonths',
    'clearError'
]);

const updateStartDate = (val) => { emit('update:startDate', val); emit('clearError'); };
const updateEndDate = (val) => emit('update:endDate', val);
const updateMode = (mode) => emit('update:selectedRentalMode', mode);

const calendarPage = ref(0);
const transitionName = ref('slide-left');
const daysOfWeek = ['Min', 'Sn', 'Sl', 'R', 'Km', 'J', 'Sb'];

// --- Helpers ---
const isPastDate = (year, month, date) => {
    const today = new Date();
    today.setHours(0,0,0,0);
    const check = new Date(year, month, date);
    return check < today;
};

const isDateDisabled = (year, month, date) => {
    if (isPastDate(year, month, date)) return true;
    
    if (props.activeScheduleMode === 'day' && props.startDate && !props.endDate) {
        const check = new Date(year, month, date);
        const start = new Date(props.startDate);
        start.setHours(0,0,0,0);
        if (check < start) return true;
    }
    return false;
};

const isDateBooked = (year, month, date) => {
    if (!props.bookedDates || props.bookedDates.length === 0) return false;
    const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
    const checkDate = new Date(formattedDate);
    checkDate.setHours(0,0,0,0);

    return props.bookedDates.some(booked => {
        const from = new Date(booked.from);
        from.setHours(0,0,0,0);
        const to = new Date(booked.to);
        to.setHours(0,0,0,0);
        return checkDate >= from && checkDate <= to;
    });
};

const monthsData = computed(() => {
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();

    const data = [];
    let generatedCount = 0;

    for (let i = 0; generatedCount < 12; i++) {
        if (i > 36) break;

        const d = new Date(currentYear, currentMonth + i, 1);
        const year = d.getFullYear();
        const month = d.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const firstDayOfWeek = d.getDay();
        const title = d.toLocaleString('id-ID', { month: 'long', year: 'numeric' });

        const weeks = [];
        let currentWeek = new Array(firstDayOfWeek).fill(null);

        for (let date = 1; date <= daysInMonth; date++) {
            currentWeek.push(date);
            if (currentWeek.length === 7) {
                weeks.push(currentWeek);
                currentWeek = [];
            }
        }
        if (currentWeek.length > 0) {
            while (currentWeek.length < 7) currentWeek.push(null);
            weeks.push(currentWeek);
        }

        const availableWeeks = weeks.filter(week => {
            return week.some(date => {
                if (date === null) return false;
                return !(isPastDate(year, month, date) || isDateBooked(year, month, date));
            });
        });

        if (availableWeeks.length > 0) {
            data.push({ year, month, title, weeks: availableWeeks });
            generatedCount++;
        }
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
    const today = new Date();
    today.setHours(0,0,0,0);
    if (selected < today) return;
    if (isDateBooked(year, month, date)) return;

    if (props.activeScheduleMode === 'hour' || props.activeScheduleMode === 'month') {
        updateStartDate(selected);
    } else {
        if (!props.startDate || (props.startDate && props.endDate)) {
            updateStartDate(selected);
            updateEndDate(null);
        } else if (selected < props.startDate) {
            let hasBookedBetween = false;
            if (props.bookedDates && props.bookedDates.length > 0) {
                let tempDate = new Date(selected);
                while (tempDate <= props.startDate) {
                    if (isDateBooked(tempDate.getFullYear(), tempDate.getMonth(), tempDate.getDate())) {
                        hasBookedBetween = true;
                        break;
                    }
                    tempDate.setDate(tempDate.getDate() + 1);
                }
            }
            if (hasBookedBetween) {
                updateStartDate(selected);
            } else {
                updateEndDate(props.startDate);
                updateStartDate(selected);
            }
        } else if (selected > props.startDate) {
            let hasBookedBetween = false;
            if (props.bookedDates && props.bookedDates.length > 0) {
                let tempDate = new Date(props.startDate);
                while (tempDate <= selected) {
                    if (isDateBooked(tempDate.getFullYear(), tempDate.getMonth(), tempDate.getDate())) {
                        hasBookedBetween = true;
                        break;
                    }
                    tempDate.setDate(tempDate.getDate() + 1);
                }
            }
            if (hasBookedBetween) {
                updateStartDate(selected);
            } else {
                updateEndDate(selected);
            }
        }
    }
};

const isStartDateFn = (year, month, date) => {
    if (!props.startDate) return false;
    return props.startDate.getFullYear() === year && props.startDate.getMonth() === month && props.startDate.getDate() === date;
};

const isEndDateFn = (year, month, date) => {
    if (!props.endDate) return false;
    return props.endDate.getFullYear() === year && props.endDate.getMonth() === month && props.endDate.getDate() === date;
};

const isInRangeFn = (year, month, date) => {
    if (props.activeScheduleMode === 'hour') return false;
    if (!props.startDate || !props.endDate) return false;
    const current = new Date(year, month, date);
    return current > props.startDate && current < props.endDate;
};

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
    <div id="kalender-sewa" class="pb-10 border-b border-gray-200">
        <!-- Toggle Sewa Harian / Bulanan (Khusus Apartemen) -->
        <div v-if="assetType === 'Apartemen'" class="flex items-center gap-2 mb-6 bg-slate-100 p-1.5 rounded-xl w-fit">
            <button
                @click="updateMode('night')"
                class="px-5 py-2 rounded-lg text-sm font-bold transition-all"
                :class="activeScheduleMode === 'night' ? 'bg-white text-[#0A2540] shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                Sewa Harian
            </button>
            <button
                @click="updateMode('month')"
                class="px-5 py-2 rounded-lg text-sm font-bold transition-all"
                :class="activeScheduleMode === 'month' ? 'bg-white text-[#0A2540] shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                Sewa Bulanan
            </button>
        </div>
        <h2 class="text-2xl font-extrabold text-[#0A2540] mb-1">
            <span v-if="activeScheduleMode === 'hour' && startDate">Jadwal sewa untuk {{ assetTitle || 'Aset ini' }}</span>
            <span v-else-if="activeScheduleMode === 'month' && startDate">{{ durationMonths }} Bulan di {{ assetTitle || 'Sini' }}</span>
            <span v-else-if="nightsCount && activeScheduleMode === 'day'">{{ nightsCount }} malam di {{ assetTitle || 'Kota ini' }}</span>
            <span v-else>Pilih tanggal sewa</span>
        </h2>

        <!-- Error Alert -->
        <div v-if="showDateError" class="mt-3 mb-2 text-red-500 font-bold text-sm bg-red-50 p-3.5 rounded-xl border border-red-200 flex items-center gap-2">
            <AlertCircle class="text-lg" />
            Silakan pilih tanggal penyewaan terlebih dahulu!
        </div>

        <p class="text-sm text-gray-500 mb-8 mt-1">{{ formattedDateRange }}</p>

        <div class="bg-white rounded-2xl relative w-full overflow-hidden touch-pan-y" @touchstart.passive="handleTouchStart" @touchend.passive="handleTouchEnd">
            <!-- Header Bulan (Hanya untuk kalender grid) -->
            <div v-if="['day', 'night'].includes(activeScheduleMode)" class="flex justify-between items-center mb-10 px-2 pt-6">
                <button @click="prevMonth" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition" :class="calendarPage === 0 ? 'opacity-30 cursor-not-allowed' : ''">
                    <ChevronLeft class="text-[#0A2540] text-sm" />
                </button>
                <div class="flex gap-8 w-full px-4">
                    <h3 class="flex-1 text-center text-[15px] font-bold text-[#0A2540]">{{ monthsData[calendarPage]?.title }}</h3>
                    <h3 class="flex-1 text-center text-[15px] font-bold text-[#0A2540] hidden sm:block">{{ monthsData[calendarPage + 1]?.title }}</h3>
                </div>
                <button @click="nextMonth" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                    <ChevronRight class="text-[#0A2540] text-sm" />
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

                                <template v-for="(week, wIdx) in monthsData[calendarPage]?.weeks" :key="'w1-'+wIdx">
                                    <div v-for="(date, dIdx) in week" :key="'d1-'+wIdx+'-'+dIdx" class="relative flex justify-center items-center h-10">
                                        <template v-if="date">
                                            <!-- KONEKTOR RENTANG -->
                                            <div v-if="isStartDateFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                            <div v-else-if="isInRangeFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                            <div v-else-if="isEndDateFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                            <!-- BULATAN TANGGAL -->
                                            <div class="relative z-10 w-10 h-10 flex flex-col items-center justify-center rounded-full text-[13px] font-bold transition"
                                                :class="[
                                                    (isDateDisabled(monthsData[calendarPage].year, monthsData[calendarPage].month, date) || isDateBooked(monthsData[calendarPage].year, monthsData[calendarPage].month, date)) ? 'text-gray-300 cursor-not-allowed line-through' : 'cursor-pointer hover:border hover:border-[#1A1A1A]',
                                                    { 'bg-[#1A1A1A] text-white shadow-md hover:bg-[#1A1A1A]': isStartDateFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date) || isEndDateFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date),
                                                    'text-[#1A1A1A]': isInRangeFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date),
                                                    'text-[#0A2540]': !isStartDateFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && !isEndDateFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && !isInRangeFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && !isDateDisabled(monthsData[calendarPage].year, monthsData[calendarPage].month, date) && !isDateBooked(monthsData[calendarPage].year, monthsData[calendarPage].month, date),
                                                    'bg-red-50 text-red-500': isDateBooked(monthsData[calendarPage].year, monthsData[calendarPage].month, date) }
                                                ]"
                                                @click="!(isDateDisabled(monthsData[calendarPage].year, monthsData[calendarPage].month, date) || isDateBooked(monthsData[calendarPage].year, monthsData[calendarPage].month, date)) && selectDate(monthsData[calendarPage].year, monthsData[calendarPage].month, date)">
                                                <span>{{ date }}</span>
                                            </div>

                                            <!-- TANDA MULAI & SELESAI -->
                                            <div v-if="isStartDateFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date)" class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                            <div v-else-if="isEndDateFn(monthsData[calendarPage].year, monthsData[calendarPage].month, date)" class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Kalender Bulan Kanan (Hanya Desktop) -->
                        <div class="flex-1 hidden sm:block">
                            <div class="grid grid-cols-7 gap-y-6 mb-1">
                                <div v-for="day in daysOfWeek" :key="'d2-'+day" class="text-center text-[11px] font-bold text-[#6C757D]">{{ day }}</div>

                                <template v-for="(week, wIdx) in monthsData[calendarPage + 1]?.weeks" :key="'w2-'+wIdx">
                                    <div v-for="(date, dIdx) in week" :key="'d2-'+wIdx+'-'+dIdx" class="relative flex justify-center items-center h-10">
                                        <template v-if="date">
                                            <!-- KONEKTOR RENTANG -->
                                            <div v-if="isStartDateFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && endDate" class="absolute right-0 w-1/2 h-full bg-[#F2F2F2]"></div>
                                            <div v-else-if="isInRangeFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)" class="absolute inset-0 w-full h-full bg-[#F2F2F2]"></div>
                                            <div v-else-if="isEndDateFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)" class="absolute left-0 w-1/2 h-full bg-[#F2F2F2]"></div>

                                            <!-- BULATAN TANGGAL -->
                                            <div class="relative z-10 w-10 h-10 flex flex-col items-center justify-center rounded-full text-[13px] font-bold transition"
                                                :class="[
                                                    (isDateDisabled(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) || isDateBooked(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)) ? 'text-gray-300 cursor-not-allowed line-through' : 'cursor-pointer hover:border hover:border-[#1A1A1A]',
                                                    { 'bg-[#1A1A1A] text-white shadow-md hover:bg-[#1A1A1A]': isStartDateFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) || isEndDateFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date),
                                                    'text-[#1A1A1A]': isInRangeFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date),
                                                    'text-[#0A2540]': !isStartDateFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && !isEndDateFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && !isInRangeFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && !isDateDisabled(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) && !isDateBooked(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date),
                                                    'bg-red-50 text-red-500': isDateBooked(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) }
                                                ]"
                                                @click="!(isDateDisabled(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date) || isDateBooked(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)) && selectDate(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)">
                                                <span>{{ date }}</span>
                                            </div>

                                            <!-- TANDA MULAI & SELESAI -->
                                            <div v-if="isStartDateFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)" class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-[#0A2540] whitespace-nowrap">Mulai</div>
                                            <div v-else-if="isEndDateFn(monthsData[calendarPage+1].year, monthsData[calendarPage+1].month, date)" class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-[#0A2540] whitespace-nowrap">Selesai</div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            
            <slot></slot>
        </div>
    </div>
</template>
<style scoped>
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: absolute;
    width: 100%;
}
.slide-left-enter-from { opacity: 0; transform: translateX(30px); }
.slide-left-leave-to { opacity: 0; transform: translateX(-30px); }
.slide-right-enter-from { opacity: 0; transform: translateX(-30px); }
.slide-right-leave-to { opacity: 0; transform: translateX(30px); }
</style>
