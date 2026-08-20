<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Receipt, Clock, Wallet, ChevronDown, Image, User, Calendar, Search, Filter, Shield } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
// ========== PROPS ==========
const props = defineProps({
    type:            { type: String },
    title:           { type: String },
    description:     { type: String },
    bookings:        { type: Object, default: () => ({ data: [], meta: { total: 0, from: 0, to: 0, links: [] } }) },
    statusCounts:    { type: Object, default: () => ({}) },
    kategoriGroups:  { type: Array,  default: () => [] },
    income:          { type: Number, default: 0 },
    fees:            { type: Number, default: 0 },
    transactions:    { type: Array,  default: () => [] },
    status:          { type: String, default: 'pending' },
    documents:       { type: Array,  default: () => [] },
    user:            { type: Object, default: () => ({}) },
    faqs:            { type: Array,  default: () => [] },
});

// ========== FILTER STATUS ==========
const urlParams      = new URLSearchParams(window.location.search);
const filterStatus   = ref(urlParams.get('status')    || 'all');
const filterKategori = ref(urlParams.get('kategori')  || 'all');
const filterJenis    = ref(urlParams.get('jenis')     || 'all');
const filterDate     = ref(urlParams.get('date')      || '');

const statusOptions = [
    { value: 'all',       label: 'Semua' },
    { value: 'pending',   label: 'Menunggu' },
    { value: 'confirmed', label: 'Dikonfirmasi' },
    { value: 'active',    label: 'Aktif' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

// ========== KATEGORI & JENIS OPTIONS (dari database via props) ==========
const kategoriOptions = computed(() => [
    { value: 'all', label: 'Semua Kategori' },
    ...props.kategoriGroups.map(g => ({ value: g.label, label: g.label }))
]);

const jenisOptions = computed(() => {
    const base = [{ value: 'all', label: 'Semua Jenis' }];
    if (filterKategori.value === 'all') {
        const all = props.kategoriGroups.flatMap(g => g.options);
        return [...base, ...all.map(j => ({ value: j, label: j }))];
    }
    const group = props.kategoriGroups.find(g => g.label === filterKategori.value);
    return group ? [...base, ...group.options.map(j => ({ value: j, label: j }))] : base;
});

// Reset jenis saat kategori berubah
watch(filterKategori, () => { filterJenis.value = 'all'; });

// ========== DATA BOOKING (dari server, sudah difilter) ==========
const bookingItems    = computed(() => props.bookings?.data || []);
const paginationLinks = computed(() => props.bookings?.meta?.links?.filter(l => l.url) || []);
const paginationMeta  = computed(() => ({
    from:  props.bookings?.meta?.from  || 0,
    to:    props.bookings?.meta?.to    || 0,
    total: props.bookings?.meta?.total || 0,
}));

// Data sudah difilter server, langsung gunakan
const filteredBookings = computed(() => bookingItems.value);

// ========== GROUP BY TANGGAL ==========
const groupedBookings = computed(() => {
    const groups = {};
    filteredBookings.value.forEach(booking => {
        // Ambil tanggal dari created_at (format: 'd M Y, H:i') → ambil bagian tanggalnya
        // Atau fallback ke start_date jika created_at tidak ada
        const raw = booking.created_at || booking.start_date || '';
        // Ambil 'dd MMM YYYY' saja (hapus bagian ', HH:mm' jika ada)
        const dateKey = raw.split(',')[0].trim();
        if (!groups[dateKey]) groups[dateKey] = [];
        groups[dateKey].push(booking);
    });
    // Kembalikan sebagai array: [{ date, bookings }]
    return Object.entries(groups).map(([date, bookings]) => ({ date, bookings }));
});

// ========== STATUS COUNTS (dari server) ==========
const bookingCounts = computed(() => props.statusCounts || {});

// ========== FILTER — kirim ke server ==========
const applyFilter = (status) => {
    filterStatus.value = status;
    const params = {};
    if (filterStatus.value   !== 'all') params.status   = filterStatus.value;
    if (filterKategori.value !== 'all') params.kategori = filterKategori.value;
    if (filterJenis.value    !== 'all') params.jenis    = filterJenis.value;
    router.get(route('owner.bookings'), params, { preserveState: true, preserveScroll: true, replace: true });
};

// ========== TAMPILAN DROPDOWN FILTER ==========
const showKategoriDropdown = ref(false);
const showJenisDropdown = ref(false);

const sendFilters = () => {
    const params = {};
    if (filterStatus.value   !== 'all') params.status   = filterStatus.value;
    if (filterKategori.value !== 'all') params.kategori = filterKategori.value;
    if (filterJenis.value    !== 'all') params.jenis    = filterJenis.value;
    if (filterDate.value)               params.date     = filterDate.value;
    router.get(route('owner.bookings'), params, { preserveState: true, preserveScroll: true, replace: true });
};

const resetFilters = () => {
    filterStatus.value   = 'all';
    filterKategori.value = 'all';
    filterJenis.value    = 'all';
    filterDate.value     = '';
    router.get(route('owner.bookings'), {}, { preserveState: true, preserveScroll: true });
};

// Kirim filter saat berubah
watch([filterKategori, filterJenis, filterDate], sendFilters, { deep: false });

const activeFaq = ref(null);
const formatCurrency = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
const statusLabel = computed(() => ({ pending: 'Menunggu', confirmed: 'Dikonfirmasi', completed: 'Selesai', cancelled: 'Dibatalkan', verified: 'Terverifikasi', rejected: 'Ditolak' }[props.status] || props.status));
const bookingStatusClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    confirmed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    active: 'bg-blue-50 text-blue-700 border-blue-200',
    completed: 'bg-sky-50 text-sky-700 border-sky-200',
    cancelled: 'bg-slate-100 text-slate-600 border-slate-200',
    rejected: 'bg-rose-50 text-rose-700 border-rose-200',
}[status] || 'bg-slate-100 text-slate-600 border-slate-200');
const bookingStatusLabel = (status) => ({
    pending: 'Menunggu',
    confirmed: 'Dikonfirmasi',
    active: 'Aktif',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
}[status] || status);
const bookingStatusIcon = (status) => ({
    pending: 'fa-clock',
    confirmed: 'fa-circle-check',
    active: 'fa-circle-play',
    completed: 'fa-flag-checkered',
    cancelled: 'fa-ban',
    rejected: 'fa-circle-xmark',
}[status] || 'fa-circle-info');

// ========== FINANCE CHART ==========
const filterTime = ref('daily');
</script>

<template>
    <DashboardLayout
        :title="title"
        :description="description"
        role="Owner"
    >

        <div class="space-y-6">
            <!-- ==================== BOOKINGS ==================== -->
            <section v-if="type === 'bookings'" class="space-y-4">
                    <!-- METRIC SUMMARY STATS - Clean Panel Design -->
                    <div class="bg-white border border-slate-200/80 rounded-xl shadow-sm mb-6">
                        <div class="grid grid-cols-2 xl:grid-cols-4 border-slate-100">
                            <!-- Total Pesanan -->
                            <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-b xl:border-b-0 border-slate-100">
                                <p class="text-xs text-slate-500 font-medium tracking-wide mb-1 flex items-start gap-2">
                                    <Receipt class="text-blue-500 mt-0.5" /> <span>Total Pesanan</span>
                                </p>
                                <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ bookingCounts.all || 0 }}</p>
                            </div>

                            <!-- Menunggu Konfirmasi -->
                            <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-b xl:border-b-0 xl:border-r border-slate-100">
                                <p class="text-xs text-slate-500 font-medium tracking-wide mb-1 flex items-start gap-2">
                                    <Clock class="text-amber-500 mt-0.5" /> <span>Menunggu Konfirmasi</span>
                                </p>
                                <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ bookingCounts.pending || 0 }}</p>
                            </div>

                            <!-- Menunggu Pembayaran -->
                            <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-slate-100">
                                <p class="text-xs text-slate-500 font-medium tracking-wide mb-1 flex items-start gap-2">
                                    <Wallet class="text-rose-500 mt-0.5" /> <span>Menunggu Pembayaran</span>
                                </p>
                                <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ bookingCounts.confirmed || 0 }}</p>
                            </div>

                            <!-- Verifikasi Pembayaran -->
                            <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center">
                                <p class="text-xs text-slate-500 font-medium tracking-wide mb-1 flex items-start gap-2">
                                    <Receipt class="text-emerald-500 mt-0.5" /> <span>Verifikasi Pembayaran</span>
                                </p>
                                <p class="text-2xl lg:text-3xl font-black text-[#0A2540]">{{ bookingCounts.active || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Status -->
                    <div class="flex flex-wrap items-center gap-2 p-3 bg-white border border-slate-200 rounded-xl overflow-x-auto hide-scrollbar">
                        <button
                            v-for="status in statusOptions"
                            :key="status.value"
                            @click="applyFilter(status.value)"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap',
                                filterStatus === status.value
                                    ? 'bg-[#0A2540] text-white shadow-sm'
                                    : 'bg-transparent text-slate-500 hover:bg-slate-50'
                            ]"
                        >
                            {{ status.label }}
                            <span :class="filterStatus === status.value ? 'text-white/80' : 'text-slate-400'" class="ml-1">
                                ({{ bookingCounts[status.value] }})
                            </span>
                        </button>
                    </div>

                    <!-- Filter Kategori, Jenis & Tanggal -->
                    <div class="flex flex-wrap items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl relative">
                        <!-- Overlay untuk click outside -->
                        <div 
                            v-if="showKategoriDropdown || showJenisDropdown" 
                            @click="showKategoriDropdown = false; showJenisDropdown = false" 
                            class="fixed inset-0 z-40"
                        ></div>

                        <!-- Filter Kategori -->
                        <div class="flex items-center gap-2 relative z-50">
                            <label class="text-xs font-bold text-slate-600">Kategori</label>
                            <div class="relative">
                                <button
                                    @click="showKategoriDropdown = !showKategoriDropdown; showJenisDropdown = false"
                                    class="flex items-center justify-between w-full pl-3 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] cursor-pointer transition font-medium text-slate-700 min-w-[130px]"
                                >
                                    <span class="truncate">{{ kategoriOptions.find(o => o.value === filterKategori)?.label }}</span>
                                    <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400" />
                                </button>
                                
                                <Transition
                                    enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0"
                                    enter-to-class="transform scale-100 opacity-100"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100"
                                    leave-to-class="transform scale-95 opacity-0"
                                >
                                    <div v-if="showKategoriDropdown" class="absolute top-full mt-1 left-0 w-full bg-white border border-slate-200 rounded-lg shadow-lg py-1 max-h-60 overflow-auto z-50">
                                        <button
                                            v-for="opt in kategoriOptions"
                                            :key="opt.value"
                                            @click="filterKategori = opt.value; showKategoriDropdown = false"
                                            :class="[
                                                'w-full text-left px-3 py-2 text-xs transition-colors',
                                                filterKategori === opt.value ? 'bg-[#0A2540]/5 font-bold text-[#0A2540]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                                            ]"
                                        >
                                            {{ opt.label }}
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <!-- Filter Jenis -->
                        <div class="flex items-center gap-2 relative z-50">
                            <label class="text-xs font-bold text-slate-600">Jenis</label>
                            <div class="relative">
                                <button
                                    @click="showJenisDropdown = !showJenisDropdown; showKategoriDropdown = false"
                                    class="flex items-center justify-between w-full pl-3 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] cursor-pointer transition font-medium text-slate-700 min-w-[130px]"
                                >
                                    <span class="truncate">{{ jenisOptions.find(o => o.value === filterJenis)?.label }}</span>
                                    <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400" />
                                </button>
                                
                                <Transition
                                    enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0"
                                    enter-to-class="transform scale-100 opacity-100"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100"
                                    leave-to-class="transform scale-95 opacity-0"
                                >
                                    <div v-if="showJenisDropdown" class="absolute top-full mt-1 left-0 w-full bg-white border border-slate-200 rounded-lg shadow-lg py-1 max-h-60 overflow-auto z-50">
                                        <button
                                            v-for="opt in jenisOptions"
                                            :key="opt.value"
                                            @click="filterJenis = opt.value; showJenisDropdown = false"
                                            :class="[
                                                'w-full text-left px-3 py-2 text-xs transition-colors',
                                                filterJenis === opt.value ? 'bg-[#0A2540]/5 font-bold text-[#0A2540]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                                            ]"
                                        >
                                            {{ opt.label }}
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <div class="w-px h-6 bg-slate-200 mx-1 hidden sm:block"></div>

                        <!-- Filter Tanggal -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-slate-600">Tanggal</label>
                            <input
                                type="date"
                                v-model="filterDate"
                                class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition font-medium text-slate-700 cursor-pointer"
                            />
                        </div>
                        
                        <!-- Reset -->
                        <button
                            v-if="filterKategori !== 'all' || filterJenis !== 'all' || filterDate"
                            @click="resetFilters"
                            class="text-xs font-bold text-slate-400 hover:text-rose-500 transition ml-2"
                        >
                            Reset
                        </button>
                    </div>

                    <!-- Ringkasan -->
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-slate-800">Daftar Pesanan</h2>
                        <span class="text-[11px] text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">
                            {{ filteredBookings.length }} ditampilkan dari {{ paginationMeta.total }} total
                        </span>
                    </div>

                    <!-- Card List — grouped by date -->
                    <div v-if="filteredBookings.length" class="space-y-8">
                        <div v-for="group in groupedBookings" :key="group.date">
                            <!-- Date Header -->
                            <div class="flex items-center gap-4 mb-4">
                                <h3 class="text-sm font-bold text-[#0A2540] flex items-center gap-2 tracking-wide">
                                    {{ group.date }}
                                </h3>
                                <div class="flex-1 h-px bg-slate-100"></div>
                                <span class="text-xs font-semibold text-slate-400">{{ group.bookings.length }} Pesanan</span>
                            </div>

                            <!-- Cards dalam grup ini -->
                            <div class="space-y-2.5">
                                <Link
                                    v-for="booking in group.bookings"
                                    :key="booking.id"
                                    :href="`/owner/bookings/${booking.id}`"
                                    class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-row overflow-hidden group p-2.5 md:p-3 items-center gap-3 md:gap-4 select-none [-webkit-touch-callout:none] w-full cursor-pointer"
                                >
                                    <!-- AREA GAMBAR -->
                                    <div class="w-16 h-16 md:w-20 md:h-20 shrink-0 relative rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center">
                                        <template v-if="booking.asset_image && !booking.imageError">
                                            <img :src="`/storage/${booking.asset_image}`" @error="booking.imageError = true" class="w-full h-full object-cover" alt="Asset" loading="lazy" />
                                        </template>
                                        <div v-else class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                                            <Image class="text-xl md:text-2xl" />
                                        </div>
                                    </div>

                                    <!-- AREA TEKS -->
                                    <div class="flex-1 min-w-0 flex flex-col justify-center">
                                        <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                            <span class="px-1.5 py-0.5 bg-[#6C757D]/10 text-[#6C757D] rounded text-[9px] font-bold">
                                                {{ booking.kategori || '-' }}
                                            </span>
                                            <!-- STATUS BADGE -->
                                            <span :class="['px-1.5 py-0.5 rounded text-[9px] font-bold flex items-center gap-1', bookingStatusClass(booking.status)]">
                                                <AppIcon :iconClass="['fa-solid', bookingStatusIcon(booking.status)]" />
                                                {{ bookingStatusLabel(booking.status) }}
                                            </span>
                                        </div>

                                        <h3 class="font-bold text-sm md:text-base text-[#0A2540] truncate group-hover:text-[#FFC000] transition-colors">
                                            {{ booking.asset }}
                                        </h3>

                                        <div class="text-[10px] md:text-xs text-gray-500 font-medium truncate mt-0.5">
                                            <Receipt class="text-[#FFC000] mr-0.5" />
                                            {{ booking.code }} <span class="mx-1">•</span> <User class="text-slate-400 mr-0.5" /> {{ booking.tenant }}
                                        </div>

                                        <div class="text-[10px] md:text-[11px] text-[#10B981] font-bold mt-1.5 flex items-center gap-1">
                                            <Calendar class="" />
                                            {{ booking.period }}
                                        </div>
                                    </div>

                                    <!-- HARGA & AKSI -->
                                    <div class="shrink-0 flex flex-col items-end justify-between self-stretch py-0.5">
                                        <div class="text-right mb-2 md:mb-0">
                                            <div class="font-black text-sm md:text-base text-[#FFC000] tracking-tight leading-none">
                                                <span>{{ formatCurrency(booking.total) }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-auto flex items-center gap-2">
                                            <div
                                                v-if="booking.status === 'pending'"
                                                class="bg-[#FFC000] text-[#0A2540] text-[9px] md:text-xs font-bold px-3 py-1.5 md:px-4 md:py-1.5 rounded-full hover:bg-[#e6ad00] transition shadow-sm z-30 flex items-center gap-1"
                                            >
                                                <Search class="text-[10px]" /> Tinjau
                                            </div>
                                            <div
                                                v-else
                                                class="bg-[#0A2540] text-white text-[9px] md:text-xs font-bold px-3 py-1.5 md:px-4 md:py-1.5 rounded-full hover:bg-[#1a365d] transition shadow-sm z-30"
                                            >
                                                Lihat Detail
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-if="!filteredBookings.length && !bookingItems.length" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                        <Receipt class="text-3xl text-slate-200" />
                        <p class="mt-3 font-bold text-slate-700">Belum ada pesanan</p>
                        <p class="mt-1 text-sm text-slate-400">Pesanan dari aset Anda akan tampil di halaman ini.</p>
                    </div>
                    <div v-else-if="!filteredBookings.length" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                        <Filter class="text-3xl text-slate-200" />
                        <p class="mt-3 font-bold text-slate-700">Tidak ada pesanan dengan filter ini</p>
                        <button @click="filterKategori = 'all'; filterJenis = 'all'; applyFilter('all')" class="mt-3 px-4 py-2 bg-[#0A2540] text-white text-xs font-bold rounded-lg hover:bg-[#081d33] transition">
                            Lihat Semua Pesanan
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="paginationLinks.length > 0" class="flex items-center justify-center gap-1 pt-2">
                        <Link
                            v-for="link in paginationLinks"
                            :key="link.label"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'text-xs font-bold px-3 py-1.5 rounded-lg transition',
                                link.active ? 'bg-[#0A2540] text-white' : 'text-slate-500 hover:bg-slate-100',
                            ]"
                        />
                    </div>
                </section>

                <!-- ==================== FINANCE ==================== -->
                <template v-else-if="type === 'finance'">
                    <!-- Filter waktu -->
                    <div class="flex flex-wrap items-center gap-2 p-3 bg-white border border-slate-200 rounded-xl">
                        <button
                            v-for="opt in ['daily', 'monthly', 'yearly']"
                            :key="opt"
                            @click="filterTime = opt"
                            :class="[
                                'px-4 py-1.5 rounded-lg text-xs font-bold transition',
                                filterTime === opt
                                    ? 'bg-[#0A2540] text-white'
                                    : 'bg-slate-50 text-slate-600 hover:bg-slate-100'
                            ]"
                        >
                            {{ opt.charAt(0).toUpperCase() + opt.slice(1) }}
                        </button>
                    </div>

                    <!-- Grafik pendapatan -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5">
                        <h2 class="font-bold text-slate-800">Grafik Pendapatan</h2>
                        <div class="h-[250px] mt-3 relative unovis-chart-container">
                            <VisXYContainer v-if="unovisChartData.length" :data="unovisChartData" :padding="{ top: 20, right: 10, left: 20, bottom: 0 }">
                                <VisAxis type="x" :x="x" :tickFormat="tickFormatX" :gridLine="false" :tickLine="false" color="#cbd5e1" tickTextColor="#64748b" />
                                <VisAxis type="y" :tickFormat="tickFormatY" :domainLine="false" :tickLine="false" color="#e2e8f0" tickTextColor="#64748b" />
                                <VisStackedBar :x="x" :y="y" color="#0A2540" :roundedCorners="4" :barPadding="0.3" />
                                <VisCrosshair :template="tooltipTemplate" color="#FFC000" />
                                <VisTooltip />
                            </VisXYContainer>
                            <div v-else class="h-full flex items-center justify-center text-sm text-slate-400">
                                Tidak ada data
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan 3 kartu -->
                    <section class="grid md:grid-cols-3 gap-4">
                        <div class="bg-[#0A2540] text-white p-5 rounded-2xl">
                            <p class="text-xs text-slate-300">Pendapatan Tercatat</p>
                            <p class="mt-2 text-2xl font-black">{{ formatCurrency(income) }}</p>
                        </div>
                        <div class="bg-white border border-slate-200 p-5 rounded-2xl">
                            <p class="text-xs text-slate-400">Biaya Layanan</p>
                            <p class="mt-2 text-2xl font-black text-slate-800">{{ formatCurrency(fees) }}</p>
                        </div>
                        <div class="bg-white border border-slate-200 p-5 rounded-2xl">
                            <p class="text-xs text-slate-400">Pendapatan Bersih</p>
                            <p class="mt-2 text-2xl font-black text-emerald-600">{{ formatCurrency(income - fees) }}</p>
                        </div>
                    </section>

                    <!-- Daftar transaksi terbaru -->
                    <section class="bg-white border border-slate-200 rounded-2xl p-5">
                        <h2 class="font-bold text-slate-800">Transaksi Terbaru</h2>
                        <div v-if="transactions.length" class="mt-4 divide-y divide-slate-100">
                            <div v-for="transaction in transactions" :key="transaction.code" class="py-3 flex justify-between gap-4">
                                <div>
                                    <p class="font-bold text-sm">{{ transaction.asset }}</p>
                                    <p class="text-xs text-slate-400">{{ transaction.code }} · {{ transaction.date }}</p>
                                </div>
                                <p class="font-bold text-emerald-600">{{ formatCurrency(transaction.total) }}</p>
                            </div>
                        </div>
                        <p v-else class="mt-4 text-sm text-slate-400">Belum ada pendapatan yang tercatat.</p>
                    </section>
                </template>

                <!-- ==================== VERIFICATION ==================== -->
                <section v-else-if="type === 'verification'" class="max-w-3xl bg-white border border-slate-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between bg-amber-50 rounded-xl p-4">
                        <div>
                            <p class="text-xs text-amber-700">Status verifikasi</p>
                            <p class="font-black text-amber-900">{{ statusLabel }}</p>
                        </div>
                        <Shield class="text-xl text-amber-500" />
                    </div>
                    <div class="mt-6 space-y-3">
                        <div v-for="document in documents" :key="document.name" class="flex items-center justify-between p-4 border border-slate-100 rounded-xl">
                            <span class="font-semibold text-sm">{{ document.name }}</span>
                            <span :class="document.complete ? 'text-emerald-600' : 'text-amber-600'" class="text-xs font-bold">
                                <AppIcon :iconClass="document.complete ? 'fa-circle-check' : 'fa-clock'" class="fa-solid mr-1" />
                                {{ document.complete ? 'Lengkap' : 'Perlu dilengkapi' }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- ==================== SETTINGS ==================== -->
                <section v-else-if="type === 'settings'" class="max-w-3xl bg-white border border-slate-200 rounded-2xl p-6">
                    <h2 class="font-bold text-slate-800">Informasi Akun</h2>
                    <div class="mt-5 grid sm:grid-cols-2 gap-4">
                        <label class="text-xs font-bold">Nama
                            <input :value="user.name" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500">
                        </label>
                        <label class="text-xs font-bold">Email
                            <input :value="user.email" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500">
                        </label>
                        <label class="text-xs font-bold">Nomor telepon
                            <input :value="user.phone || '-'" disabled class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500">
                        </label>
                        <!-- Tambahan field Tempat, Tanggal Lahir -->
                        <label class="text-xs font-bold">Tempat, Tanggal Lahir
                            <input
                                :value="user.birth_place && user.birth_date ? `${user.birth_place}, ${user.birth_date}` : '-'"
                                disabled
                                class="mt-1 w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500"
                            >
                        </label>
                    </div>
                    <Link :href="route('profile.edit')" class="inline-flex mt-5 bg-[#0A2540] text-white text-xs font-bold px-4 py-2.5 rounded-xl">
                        Ubah profil & kata sandi
                    </Link>
                </section>

                <!-- ==================== HELP ==================== -->
                <section v-else-if="type === 'help'" class="max-w-3xl space-y-3">
                    <div v-for="(faq, index) in faqs" :key="faq.question" class="bg-white border border-slate-200 rounded-xl">
                        <button @click="activeFaq = activeFaq === index ? null : index" class="w-full p-4 text-left flex items-center justify-between font-bold text-sm">
                            <span>{{ faq.question }}</span>
                            <AppIcon :iconClass="activeFaq === index ? 'fa-minus' : 'fa-plus'" class="fa-solid text-slate-400" />
                        </button>
                        <p v-if="activeFaq === index" class="px-4 pb-4 text-sm text-slate-500 leading-relaxed">{{ faq.answer }}</p>
                    </div>
                    <div class="mt-6 bg-[#0A2540] text-white p-5 rounded-2xl">
                        <p class="font-bold">Butuh bantuan langsung?</p>
                        <p class="text-sm text-slate-300 mt-1">Hubungi tim dukungan kitasewa melalui email support@kitasewa.id.</p>
                    </div>
                </section>
        </div>
    </DashboardLayout>
</template>
