<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue';
import Chart from 'chart.js/auto';

// ========== PROPS ==========
const props = defineProps({
    type: String,
    title: String,
    description: String,
    bookings: { type: Array, default: () => [] },
    statusCounts: { type: Object, default: () => null },
    income: { type: Number, default: 0 },
    fees: { type: Number, default: 0 },
    transactions: { type: Array, default: () => [] },
    status: { type: String, default: 'pending' },
    documents: { type: Array, default: () => [] },
    user: { type: Object, default: () => ({}) },
    faqs: { type: Array, default: () => [] },
});

// ========== DATA KATEGORI & JENIS PROPERTI ==========
const kategoriPropertiGroups = [
    {
        label: 'Hunian & Tempat Tinggal',
        options: ['Kos-kosan', 'Hotel', 'Rumah Tapak', 'Villa', 'Homestay', 'Apartemen', 'Guest House', 'Rusun / Condominium']
    },
    {
        label: 'Komersial & Usaha',
        options: ['Ruko (Rumah Toko)', 'Kios / Lapak Pasar', 'Kantor / Workspace', 'Gedung Komersial', 'Food Court / Booth']
    },
    {
        label: 'Penyimpanan & Industri',
        options: ['Gudang Logistik', 'Pabrik / Manufaktur', 'Cold Storage']
    },
    {
        label: 'Tanah & Lahan Kosong',
        options: ['Lahan / Tanah Kosong', 'Lahan Pertanian / Perkebunan']
    },
    {
        label: 'Media Iklan & Ruang Promosi',
        options: ['Baliho / Reklame', 'Billboard / Videotron', 'Neon Box / Titik Toko']
    }
];

// ========== FILTER STATUS ==========
const urlParams = new URLSearchParams(window.location.search);
const filterStatus = ref(urlParams.get('status') || 'all');
const statusOptions = [
    { value: 'all', label: 'Semua Status' },
    { value: 'pending', label: 'Menunggu' },
    { value: 'confirmed', label: 'Dikonfirmasi' },
    { value: 'active', label: 'Aktif' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

// ========== FILTER KATEGORI & JENIS ==========
const filterKategori = ref('all');
const filterJenis = ref('all');

const kategoriOptions = computed(() => {
    return [
        { value: 'all', label: 'Semua Kategori' },
        ...kategoriPropertiGroups.map(group => ({
            value: group.label,
            label: group.label
        }))
    ];
});

const jenisOptions = computed(() => {
    if (filterKategori.value === 'all') {
        const allJenis = new Set();
        bookingItems.value.forEach(booking => {
            const jenis = booking.jenis || booking.property?.jenis;
            if (jenis) allJenis.add(jenis);
        });
        return [
            { value: 'all', label: 'Semua Jenis' },
            ...Array.from(allJenis).map(j => ({ value: j, label: j }))
        ];
    }

    const selectedGroup = kategoriPropertiGroups.find(g => g.label === filterKategori.value);
    if (!selectedGroup) return [{ value: 'all', label: 'Semua Jenis' }];

    return [
        { value: 'all', label: 'Semua Jenis' },
        ...selectedGroup.options.map(j => ({ value: j, label: j }))
    ];
});

watch(filterKategori, () => {
    filterJenis.value = 'all';
});

// ========== DATA BOOKING ==========
const bookingItems = computed(() => props.bookings?.data || []);
const paginationLinks = computed(() => props.bookings?.meta?.links?.filter(l => l.url) || []);
const paginationMeta = computed(() => ({
    from: props.bookings?.meta?.from || 0,
    to: props.bookings?.meta?.to || 0,
    total: props.bookings?.meta?.total || 0,
}));

// ========== FILTERED BOOKINGS ==========
const filteredBookings = computed(() => {
    let list = bookingItems.value;

    if (filterStatus.value !== 'all') {
        list = list.filter(booking => booking.status === filterStatus.value);
    }

    if (filterKategori.value !== 'all') {
        list = list.filter(booking => {
            const kategori = booking.kategori || booking.property?.kategori;
            return kategori === filterKategori.value;
        });
    }

    if (filterJenis.value !== 'all') {
        list = list.filter(booking => {
            const jenis = booking.jenis || booking.property?.jenis;
            return jenis === filterJenis.value;
        });
    }

    return list;
});

// ========== JUMLAH PER STATUS ==========
const bookingCounts = computed(() => {
    if (props.statusCounts) {
        return props.statusCounts;
    }
    const counts = {};
    ['all', 'pending', 'confirmed', 'active', 'completed', 'cancelled'].forEach(status => {
        counts[status] = status === 'all' 
            ? bookingItems.value.length 
            : bookingItems.value.filter(b => b.status === status).length;
    });
    return counts;
});

// ========== FUNGSI LAINNYA ==========
const applyFilter = (status) => {
    filterStatus.value = status;
    router.get('/owner/bookings', status === 'all' ? {} : { status }, { preserveState: true, preserveScroll: true });
};

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
const chartCanvas = ref(null);
let chartInstance = null;

const chartData = computed(() => {
    const transactions = props.transactions || [];
    if (!transactions.length) {
        return {
            labels: ['Tidak ada data'],
            datasets: [{ label: 'Pendapatan', data: [0], backgroundColor: '#0A2540' }]
        };
    }

    const groups = {};
    transactions.forEach(t => {
        const date = new Date(t.date);
        let key;
        if (filterTime.value === 'daily') {
            key = date.toISOString().slice(0, 10);
        } else if (filterTime.value === 'monthly') {
            key = date.toISOString().slice(0, 7);
        } else {
            key = date.toISOString().slice(0, 4);
        }
        if (!groups[key]) groups[key] = 0;
        groups[key] += t.total || 0;
    });

    const sortedKeys = Object.keys(groups).sort();
    return {
        labels: sortedKeys,
        datasets: [{
            label: 'Pendapatan',
            data: sortedKeys.map(k => groups[k]),
            backgroundColor: '#0A2540',
            borderRadius: 4,
        }]
    };
});

const renderChart = () => {
    if (chartInstance) chartInstance.destroy();
    if (!chartCanvas.value) return;

    const ctx = chartCanvas.value.getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: chartData.value,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `Rp ${ctx.raw.toLocaleString('id-ID')}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (val) => 'Rp ' + val.toLocaleString('id-ID') }
                }
            }
        }
    });
};

onMounted(renderChart);
watch([filterTime, () => props.transactions], renderChart, { deep: true });
</script>

<template>
    <Head :title="`${title} - kusewa.id`" />
    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans flex">
        <Sidebar />
        <main class="flex-1 min-w-0 p-6 md:p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                <header class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold text-[#FFC000] uppercase tracking-wider">Owner Center</p>
                        <h1 class="mt-1 text-2xl font-black text-slate-900">{{ title }}</h1>
                        <p class="mt-1 text-sm text-slate-500">{{ description }}</p>
                    </div>
                    <Link v-if="type === 'bookings'" :href="route('owner.property.index')" class="bg-[#0A2540] text-white text-xs font-bold px-4 py-2.5 rounded-xl">Kelola Aset</Link>
                </header>

                <!-- ==================== BOOKINGS ==================== -->
                <section v-if="type === 'bookings'" class="space-y-4">
                    <!-- Filter Status -->
                    <div class="flex flex-wrap items-center gap-2 p-3 bg-white border border-slate-200 rounded-xl">
                        <button
                            v-for="status in statusOptions"
                            :key="status.value"
                            @click="applyFilter(status.value)"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                                filterStatus === status.value 
                                    ? 'bg-[#0A2540] text-white' 
                                    : 'bg-slate-50 text-slate-600 hover:bg-slate-100'
                            ]"
                        >
                            {{ status.label }}
                            <span :class="filterStatus === status.value ? 'text-white/80' : 'text-slate-400'" class="ml-1">
                                ({{ bookingCounts[status.value] }})
                            </span>
                        </button>
                    </div>

                    <!-- Filter Kategori & Jenis -->
                    <div class="flex flex-wrap items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-slate-600">Kategori</label>
                            <select
                                v-model="filterKategori"
                                class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2540]"
                            >
                                <option v-for="opt in kategoriOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-slate-600">Jenis</label>
                            <select
                                v-model="filterJenis"
                                class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2540]"
                            >
                                <option v-for="opt in jenisOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                        <button
                            v-if="filterKategori !== 'all' || filterJenis !== 'all'"
                            @click="filterKategori = 'all'; filterJenis = 'all'"
                            class="text-xs text-slate-400 hover:text-slate-700 underline"
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

                    <!-- Card List -->
                    <div v-if="filteredBookings.length" class="space-y-3">
                        <div
                            v-for="booking in filteredBookings"
                            :key="booking.id"
                            class="bg-white border border-slate-200/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:border-slate-300 transition"
                        >
                            <div class="flex items-start gap-4 flex-1 min-w-0">
                                <div :class="[
                                    'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm',
                                    booking.status === 'pending' ? 'bg-amber-50 text-amber-500' :
                                    booking.status === 'confirmed' ? 'bg-emerald-50 text-emerald-500' :
                                    booking.status === 'completed' ? 'bg-sky-50 text-sky-500' :
                                    'bg-slate-100 text-slate-400'
                                ]">
                                    <i :class="['fa-solid', bookingStatusIcon(booking.status)]"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-800 text-sm">{{ booking.asset }}</span>
                                        <span :class="['text-[10px] font-extrabold px-2 py-0.5 rounded-full border', bookingStatusClass(booking.status)]">
                                            {{ bookingStatusLabel(booking.status) }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        {{ booking.code }} · {{ booking.tenant }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        <i class="fa-regular fa-calendar text-[10px] mr-1"></i>{{ booking.period }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        Kategori: {{ booking.kategori || booking.property?.kategori || '-' }} | 
                                        Jenis: {{ booking.jenis || booking.property?.jenis || '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 sm:flex-col sm:items-end sm:gap-1 shrink-0">
                                <span class="font-extrabold text-[#0A2540] text-sm">{{ formatCurrency(booking.total) }}</span>
                                <Link
                                    v-if="booking.status === 'pending'"
                                    :href="`/owner/bookings/${booking.id}`"
                                    class="text-[11px] font-bold bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] px-3 py-1.5 rounded-lg transition flex items-center gap-1"
                                >
                                    <i class="fa-solid fa-magnifying-glass text-[10px]"></i> Tinjau
                                </Link>
                                <Link
                                    v-else
                                    :href="`/owner/bookings/${booking.id}`"
                                    class="text-[11px] font-medium text-slate-400 hover:text-[#0A2540] transition"
                                >
                                    Lihat Detail
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-if="!filteredBookings.length && !bookingItems.length" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                        <i class="fa-solid fa-receipt text-3xl text-slate-200"></i>
                        <p class="mt-3 font-bold text-slate-700">Belum ada pesanan</p>
                        <p class="mt-1 text-sm text-slate-400">Pesanan dari aset Anda akan tampil di halaman ini.</p>
                    </div>
                    <div v-else-if="!filteredBookings.length" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                        <i class="fa-solid fa-filter text-3xl text-slate-200"></i>
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
                        <div class="h-64 mt-3">
                            <canvas ref="chartCanvas"></canvas>
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
                        <i class="fa-solid fa-shield-halved text-xl text-amber-500"></i>
                    </div>
                    <div class="mt-6 space-y-3">
                        <div v-for="document in documents" :key="document.name" class="flex items-center justify-between p-4 border border-slate-100 rounded-xl">
                            <span class="font-semibold text-sm">{{ document.name }}</span>
                            <span :class="document.complete ? 'text-emerald-600' : 'text-amber-600'" class="text-xs font-bold">
                                <i :class="document.complete ? 'fa-circle-check' : 'fa-clock'" class="fa-solid mr-1"></i>
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
                            <i :class="activeFaq === index ? 'fa-minus' : 'fa-plus'" class="fa-solid text-slate-400"></i>
                        </button>
                        <p v-if="activeFaq === index" class="px-4 pb-4 text-sm text-slate-500 leading-relaxed">{{ faq.answer }}</p>
                    </div>
                    <div class="mt-6 bg-[#0A2540] text-white p-5 rounded-2xl">
                        <p class="font-bold">Butuh bantuan langsung?</p>
                        <p class="text-sm text-slate-300 mt-1">Hubungi tim dukungan kusewa melalui email support@kusewa.id.</p>
                    </div>
                </section>
            </div>
        </main>
    </div>
</template>