import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';

// Global singleton state for Home Page Search
// We keep them outside the function so they are shared across all components

// State kontrol UI
const keywordQuery = ref('');
const isMobileSearchOpen = ref(false);
const isKeywordSheetOpen = ref(false);
const activeSearchStep = ref('aset'); // 'aset', 'lokasi', 'jadwal', 'harga'
const steps = ['aset', 'lokasi', 'jadwal', 'harga'];

const currentStepIndex = computed(() => steps.indexOf(activeSearchStep.value));
const isLastStep = computed(() => currentStepIndex.value === steps.length - 1);

const nextStep = () => {
    if (!isLastStep.value) {
        activeSearchStep.value = steps[currentStepIndex.value + 1];
    } else {
        isMobileSearchOpen.value = false;
    }
};

const prevStep = () => {
    if (currentStepIndex.value > 0) {
        activeSearchStep.value = steps[currentStepIndex.value - 1];
    }
};

const clearCurrentOrAll = () => {
    if (isLastStep.value) {
        selectedAssets.value = [];
        searchQuery.value = '';
        activeSearchStep.value = 'aset';
    }
};

// Data Aset
const assetSearchQuery = ref('');
const assetCategories = [
    {
        name: 'Tempat Tinggal & Penginapan',
        items: ['Rumah', 'Apartemen', 'Villa', 'Kos']
    },
    {
        name: 'Komersial & Bisnis',
        items: ['Ruko', 'Kios', 'Kantor', 'Gedung']
    },
    {
        name: 'Industri & Lahan',
        items: ['Gudang', 'Workshop', 'Tanah', 'Baliho']
    }
];
const selectedAssets = ref([]);

const filteredAssetCategories = computed(() => {
    if (!assetSearchQuery.value) return assetCategories;
    const q = assetSearchQuery.value.toLowerCase();
    return assetCategories.map(cat => ({
        ...cat,
        items: cat.items.filter(item => item.toLowerCase().includes(q))
    })).filter(cat => cat.items.length > 0);
});

const toggleAsset = (item) => {
    const index = selectedAssets.value.indexOf(item);
    if (index > -1) selectedAssets.value.splice(index, 1);
    else selectedAssets.value.push(item);
};

// State Pencarian Lokasi
const searchQuery = ref('');

// Lokasi dari DB (diisi oleh komponen dari page.props.locationSuggestions)
const locationSuggestions = ref([]);

const setLocationSuggestions = (data) => {
    locationSuggestions.value = data || [];
};

const filteredLocations = computed(() => {
    if (!searchQuery.value) return locationSuggestions.value;
    const query = searchQuery.value.toLowerCase();
    return locationSuggestions.value.filter(item =>
        item.title.toLowerCase().includes(query) ||
        (item.desc && item.desc.toLowerCase().includes(query))
    );
});

const isLokasiFullScreen = ref(false);
const openLokasiFullScreen = () => { isLokasiFullScreen.value = true; };
const closeLokasiFullScreen = () => { isLokasiFullScreen.value = false; };

// State Kalender / Jadwal
const startDate = ref(null);
const endDate = ref(null);

const selectDate = (year, month, date) => {
    const selected = new Date(year, month, date);
    if (!startDate.value || (startDate.value && endDate.value)) {
        startDate.value = selected;
        endDate.value = null;
    } else if (selected < startDate.value) {
        startDate.value = selected;
    } else {
        endDate.value = selected;
    }
};

const isStartDate = (year, month, date) => {
    if (!startDate.value) return false;
    return startDate.value.getFullYear() === year && startDate.value.getMonth() === month && startDate.value.getDate() === date;
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

const formattedSchedule = computed(() => {
    if (!startDate.value) return 'Pilih tanggal';
    const opt = { day: 'numeric', month: 'short' };
    const start = new Intl.DateTimeFormat('id-ID', opt).format(startDate.value);
    if (!endDate.value) return start;
    const end = new Intl.DateTimeFormat('id-ID', opt).format(endDate.value);
    return `${start} - ${end}`;
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(date);
};

// State Harga
const minPrice = ref(0);
const maxPrice = ref(10000000);
const sliderTrack = ref(null);
const maxLimit = 10000000;

const priceStep = computed(() => {
    if (maxPrice.value <= 1000000) return 50000;
    if (maxPrice.value <= 10000000) return 100000;
    if (maxPrice.value <= 100000000) return 1000000;
    return 5000000;
});

const priceError = ref('');

const validatePrices = () => {
    let min = parseInt(minPrice.value) || 0;
    let max = parseInt(maxPrice.value) || 0;
    if (min < 0) { min = 0; minPrice.value = 0; }
    if (max >= maxLimit) { max = maxLimit; maxPrice.value = maxLimit; }

    if (min > max) {
        priceError.value = 'Maksimal harga tidak boleh lebih kecil dari minimal.';
    } else {
        priceError.value = '';
    }
};

const parsedMinPrice = computed(() => parseInt(minPrice.value) || 0);
const parsedMaxPrice = computed(() => parseInt(maxPrice.value) || 0);

const formattedMinPrice = computed(() => parsedMinPrice.value.toLocaleString('id-ID'));
const formattedMaxPrice = computed(() => {
    if (parsedMaxPrice.value >= maxLimit) return '> ' + maxLimit.toLocaleString('id-ID');
    return parsedMaxPrice.value.toLocaleString('id-ID');
});

const handleMinPriceInput = (e) => {
    let val = parseInt(e.target.value.replace(/\D/g, '')) || 0;
    minPrice.value = val;
    validatePrices();
    nextTick(() => { e.target.value = formattedMinPrice.value; });
};

const handleMaxPriceInput = (e) => {
    let val = parseInt(e.target.value.replace(/\D/g, '')) || 0;
    if (val > maxLimit) val = maxLimit;
    maxPrice.value = val;
    validatePrices();
    nextTick(() => { e.target.value = formattedMaxPrice.value; });
};

const minPercent = computed(() => {
    let min = parsedMinPrice.value;
    let max = parsedMaxPrice.value;
    if (min > max) min = max;
    let percent = (min / maxLimit) * 100;
    return percent > 100 ? 100 : percent;
});
const maxPercent = computed(() => {
    let min = parsedMinPrice.value;
    let max = parsedMaxPrice.value;
    if (max < min) max = min;
    let percent = (max / maxLimit) * 100;
    return percent > 100 ? 100 : percent;
});

const formatPriceShort = (val) => {
    if (val >= 1000000) return (val / 1000000).toLocaleString('id-ID') + ' Jt';
    if (val >= 1000) return (val / 1000).toLocaleString('id-ID') + ' Rb';
    return val.toLocaleString('id-ID');
};

let activeThumb = ref(null);

const startDrag = (e, type) => {
    activeThumb.value = type;
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('touchmove', onDrag, { passive: false });
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchend', stopDrag);
};

const onDrag = (e) => {
    if (!sliderTrack.value) return;
    const rect = sliderTrack.value.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    let percent = ((clientX - rect.left) / rect.width) * 100;

    if (activeThumb.value === 'min') {
        percent = Math.max(0, Math.min(percent, maxPercent.value - 1));
        let rawPrice = (percent / 100) * maxLimit;
        minPrice.value = Math.round(rawPrice / priceStep.value) * priceStep.value;
    } else {
        percent = Math.max(minPercent.value + 1, Math.min(percent, 100));
        let rawPrice = (percent / 100) * maxLimit;
        maxPrice.value = Math.round(rawPrice / priceStep.value) * priceStep.value;
    }
    validatePrices();
};

const stopDrag = () => {
    activeThumb.value = null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('touchmove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchend', stopDrag);
};

// ── Kalender ─────────────────────────────────────────────────────────────
const daysOfWeek = ['Min', 'Sn', 'Sl', 'R', 'Km', 'J', 'Sb'];
const monthsToShow = ref(4);
const monthsData = computed(() => {
    const data = [];
    let currentMonth = new Date(2026, 6, 1);
    for (let i = 0; i < monthsToShow.value; i++) {
        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();
        const monthName = new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(currentMonth);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const firstDay = new Date(year, month, 1).getDay();
        data.push({
            id: `${year}-${month}`,
            year, month,
            title: `${monthName} ${year}`,
            daysInMonth,
            emptyDaysStart: firstDay
        });
        currentMonth.setMonth(currentMonth.getMonth() + 1);
    }
    return data;
});
const loadMoreMonths = () => { monthsToShow.value += 4; };

// Swipe to close mobile sheet
const touchStartY = ref(0);
const touchCurrentY = ref(0);

const sheetTransform = computed(() => {
    if (touchCurrentY.value > touchStartY.value && touchStartY.value !== 0) {
        return `translateY(${touchCurrentY.value - touchStartY.value}px)`;
    }
    return 'translateY(0)';
});
const onTouchStart = (e) => {
    touchStartY.value = e.touches[0].clientY;
    touchCurrentY.value = e.touches[0].clientY;
};
const onTouchMove = (e) => { touchCurrentY.value = e.touches[0].clientY; };
const onTouchEnd = () => {
    const deltaY = touchCurrentY.value - touchStartY.value;
    if (deltaY > 90) { isMobileSearchOpen.value = false; }
    touchStartY.value = 0;
    touchCurrentY.value = 0;
};

// Swipe to close Lokasi Sheet
const touchStartLokasiY = ref(0);
const touchCurrentLokasiY = ref(0);

const onTouchStartLokasi = (e) => {
    touchStartLokasiY.value = e.touches[0].clientY;
    touchCurrentLokasiY.value = e.touches[0].clientY;
};
const onTouchMoveLokasi = (e) => { touchCurrentLokasiY.value = e.touches[0].clientY; };
const onTouchEndLokasi = () => {
    const deltaY = touchCurrentLokasiY.value - touchStartLokasiY.value;
    if (deltaY > 90) { isLokasiFullScreen.value = false; }
    touchStartLokasiY.value = 0;
    touchCurrentLokasiY.value = 0;
};

const lokasiSheetTransform = computed(() => {
    if (touchCurrentLokasiY.value > touchStartLokasiY.value && touchStartLokasiY.value !== 0) {
        return `translateY(${touchCurrentLokasiY.value - touchStartLokasiY.value}px)`;
    }
    return 'translateY(0)';
});

// Desktop Modal State
const desktopActiveMenu = ref(null);
const desktopCalendarPage = ref(0);

const nextDesktopMonth = () => {
    desktopCalendarPage.value++;
    if (desktopCalendarPage.value + 1 >= monthsToShow.value) {
        monthsToShow.value += 2;
    }
};

const prevDesktopMonth = () => {
    if (desktopCalendarPage.value > 0) desktopCalendarPage.value--;
};

// ── SEARCH FUNCTIONALITY ─────────────────────────────────────────────────

// Suggestions
const suggestions = ref([]);
const isLoadingSuggestions = ref(false);
let suggestDebounce = null;

const fetchSuggestions = (keyword) => {
    clearTimeout(suggestDebounce);
    if (!keyword || keyword.length < 1) {
        suggestions.value = [];
        return;
    }
    isLoadingSuggestions.value = true;
    suggestDebounce = setTimeout(async () => {
        try {
            const res = await fetch(`/search/suggest?q=${encodeURIComponent(keyword)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (res.ok) {
                suggestions.value = await res.json();
            }
        } catch (e) {
            suggestions.value = [];
        } finally {
            isLoadingSuggestions.value = false;
        }
    }, 300);
};

function getXsrfToken() {
    const match = document.cookie.match(new RegExp('(^|;\\s*)XSRF-TOKEN=([^;]*)'));
    return match ? decodeURIComponent(match[2]) : '';
}

const logSearch = async (keyword) => {
    if (!keyword) return;
    try {
        await fetch('/search-logs', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ keyword }),
        });
    } catch (e) {
        // Ignore silently – logging failure shouldn't block the user
    }
};

const performSearch = () => {
    const params = {};

    if (keywordQuery.value.trim()) {
        params.q = keywordQuery.value.trim();
        // Log search (fire and forget)
        logSearch(params.q);
    }
    if (selectedAssets.value.length > 0) {
        params.category = selectedAssets.value;
    }
    if (searchQuery.value.trim()) {
        params.location = searchQuery.value.trim();
    }
    if (startDate.value) {
        params.date_start = startDate.value.toISOString().split('T')[0];
    }
    if (endDate.value) {
        params.date_end = endDate.value.toISOString().split('T')[0];
    }
    if (parsedMinPrice.value > 0) {
        params.min_price = parsedMinPrice.value;
    }
    if (parsedMaxPrice.value < maxLimit) {
        params.max_price = parsedMaxPrice.value;
    }

    router.get(route('assets.search'), params, { preserveState: false });

    // Tutup semua UI
    isMobileSearchOpen.value = false;
    isKeywordSheetOpen.value = false;
    desktopActiveMenu.value = null;
    suggestions.value = [];
};

export function useHomeSearch() {
    return {
        keywordQuery,
        // UI
        isMobileSearchOpen,
        isKeywordSheetOpen,
        activeSearchStep,
        steps,
        currentStepIndex,
        isLastStep,
        nextStep,
        prevStep,
        clearCurrentOrAll,

        // Aset
        assetSearchQuery,
        assetCategories,
        selectedAssets,
        filteredAssetCategories,
        toggleAsset,

        // Lokasi
        searchQuery,
        locationSuggestions,
        setLocationSuggestions,
        filteredLocations,
        isLokasiFullScreen,
        openLokasiFullScreen,
        closeLokasiFullScreen,

        // Jadwal
        startDate,
        endDate,
        selectDate,
        isStartDate,
        isEndDate,
        isInRange,
        formattedSchedule,
        formatDate,

        // Harga
        minPrice,
        maxPrice,
        sliderTrack,
        maxLimit,
        priceStep,
        priceError,
        validatePrices,
        parsedMinPrice,
        parsedMaxPrice,
        formattedMinPrice,
        formattedMaxPrice,
        handleMinPriceInput,
        handleMaxPriceInput,
        minPercent,
        maxPercent,
        formatPriceShort,
        activeThumb,
        startDrag,
        onDrag,
        stopDrag,

        // Kalender
        daysOfWeek,
        monthsToShow,
        monthsData,
        loadMoreMonths,

        // Desktop
        desktopActiveMenu,
        desktopCalendarPage,
        nextDesktopMonth,
        prevDesktopMonth,

        // Mobile Sheet Swipes
        touchStartY,
        touchCurrentY,
        sheetTransform,
        onTouchStart,
        onTouchMove,
        onTouchEnd,

        // Lokasi Sheet Swipes
        touchStartLokasiY,
        touchCurrentLokasiY,
        lokasiSheetTransform,
        onTouchStartLokasi,
        onTouchMoveLokasi,
        onTouchEndLokasi,

        // Search
        suggestions,
        isLoadingSuggestions,
        fetchSuggestions,
        logSearch,
        performSearch,
    };
}
