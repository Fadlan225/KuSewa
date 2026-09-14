<script setup>
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { MapPin, Check, AlertTriangle, ChevronLeft, X, LayoutDashboard } from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import AssetGallery from '@/Components/ui/AssetGallery.vue';
import AssetUnitList from '@/Components/ui/AssetUnitList.vue';
import AssetSpecifications from '@/Components/ui/AssetSpecifications.vue';
import AssetPolicy from '@/Components/ui/AssetPolicy.vue';
import AssetFaq from '@/Components/ui/AssetFaq.vue';
import FasilitasModal from '@/Pages/Home/Assets/Fasilitas.vue';
import AvatarMale from '@/Components/ui/Icons/AvatarMale.vue';
import AvatarFemale from '@/Components/ui/Icons/AvatarFemale.vue';
import AvatarDefault from '@/Components/ui/Icons/AvatarDefault.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    nearbyPlaces: {
        type: Object,
        default: () => ({})
    }
});

const showFullDescription = ref(false);
const showFasilitasModal = ref(false);

const rejectReason   = ref('');
const showRejectForm = ref(false);
const isSubmitting   = ref(false);

const assetFacilities = computed(() => props.asset.facilities || []);

const topFacilities = computed(() => {
    let flat = [];
    assetFacilities.value.forEach(f => flat.push(f));
    return flat.slice(0, 10);
});

const facilitiesGrouped = computed(() => {
    const groups = {};
    assetFacilities.value.forEach(f => {
        const catName = f.category?.name || 'Lainnya';
        if (!groups[catName]) groups[catName] = { name: catName, icon: f.category?.icon || 'list', facilities: [] };
        groups[catName].facilities.push(f);
    });
    return Object.values(groups);
});

const formatRupiah = (value) => value ? `Rp ${Number(value).toLocaleString('id-ID')}` : '-';

const rentalUnitLabel = (unit) => {
    const labels = { hour: "jam", day: "hari", night: "malam", month: "bulan" };
    return labels[unit] ?? "sewa";
};

// Maps
const mapContainer = ref(null);
let mapInstance = null;

const initMap = () => {
    if (!props.asset.latitude || !props.asset.longitude || !mapContainer.value) return;

    const lat = parseFloat(props.asset.latitude);
    const lng = parseFloat(props.asset.longitude);

    mapInstance = L.map(mapContainer.value, { zoomControl: false }).setView([lat, lng], 17);
    L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(mapInstance);

    const customMarkerIcon = L.divIcon({
        className: 'custom-leaflet-pin',
        html: `<div style="position: relative; width: 44px; height: 44px;">
                <div style="width: 44px; height: 44px; background-color: #FFC000; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 2px 2px 6px rgba(0,0,0,0.3); position: absolute; left: 0; top: 0;"></div>
                <div style="width: 22px; height: 22px; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 2; display:flex; align-items:center; justify-content:center;">
                    <svg fill="#0A2540" viewBox="0 0 485 485" class="w-5 h-5"><path d="M485,60H295.969V0H189.031v60H0v278.879h189.031V485h106.938V338.879H485V60z"/></svg>
                </div>
            </div>`,
        iconSize: [44, 52],
        iconAnchor: [22, 52],
    });

    L.marker([lat, lng], { icon: customMarkerIcon }).addTo(mapInstance);
};

let observer = null;

onMounted(() => {
    setTimeout(() => {
        initMap();
    }, 100);

    // Scroll spy untuk navbar shortcut
    const observerOptions = {
        root: null,
        rootMargin: '-130px 0px -60% 0px',
        threshold: 0
    };

    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                activeSection.value = entry.target.id;
            }
        });
    }, observerOptions);

    setTimeout(() => {
        const sections = ['foto', 'informasi', 'fasilitas', 'lokasi', 'kebijakan', 'faq'];
        sections.forEach(id => {
            const el = document.getElementById(id);
            if (el) observer.observe(el);
        });
    }, 300);
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});

// Scroll to section function
const activeSection = ref('foto');
const scrollTo = (id) => {
    const el = document.getElementById(id);
    if (el) {
        const yOffset = -130;
        const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
        window.scrollTo({ top: y, behavior: 'smooth' });
    }
};

// Validasi Actions
const approveAsset = () => {
    isSubmitting.value = true;
    router.patch(route('admin.validasi-aset.approve', props.asset.id), {}, {
        preserveScroll: true,
        onSuccess: () => { isSubmitting.value = false; },
        onError:   () => { isSubmitting.value = false; },
    });
};

const rejectAsset = () => {
    if (!rejectReason.value.trim()) return;
    isSubmitting.value = true;
    router.patch(route('admin.validasi-aset.reject', props.asset.id), { reason: rejectReason.value }, {
        preserveScroll: true,
        onSuccess: () => { showRejectForm.value = false; isSubmitting.value = false; },
        onError:   () => { isSubmitting.value = false; },
    });
};

const lowestPrice = computed(() => {
    let allPricings = [];
    if (props.asset.pricings && props.asset.pricings.length > 0) {
        allPricings = props.asset.pricings;
    } else if (props.asset.units && props.asset.units.length > 0) {
        props.asset.units.forEach(unit => {
            if (unit.pricings) allPricings = allPricings.concat(unit.pricings);
        });
    }

    if (allPricings.length === 0) return null;
    return allPricings.reduce((min, p) => p.price < min.price ? p : min, allPricings[0]);
});

const getStatusDetails = (status) => {
    switch (status) {
        case 'pending': return { label: 'Menunggu Validasi', class: 'bg-amber-100 text-amber-800' };
        case 'approved': return { label: 'Aset Aktif / Disetujui', class: 'bg-emerald-100 text-emerald-800' };
        case 'rejected': return { label: 'Ditolak', class: 'bg-rose-100 text-rose-800' };
        default: return { label: status, class: 'bg-gray-100 text-gray-800' };
    }
};

const statusInfo = computed(() => getStatusDetails(props.asset.status));

const translateCategory = (cat) => {
    const categories = {
        'health': 'Fasilitas Kesehatan',
        'public_transport': 'Transportasi Publik',
        'shopping': 'Pusat Perbelanjaan',
        'recreation': 'Tempat Rekreasi',
        'food': 'Kuliner',
        'religious': 'Tempat Ibadah',
        'education': 'Pendidikan',
    };
    return categories[cat] || cat;
};
</script>

<template>
    <Head :title="`Validasi Aset - ${asset.title}`" />

    <DashboardLayout role="Admin" title="Validasi Aset" description="Periksa detail kelengkapan properti yang diajukan oleh pemilik aset.">
        <template #leftAction>
            <Link :href="route('admin.validasi-aset')" class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-800 transition">
                <ChevronLeft class="w-4 h-4" />
                <span class="hidden sm:inline">Kembali ke Validasi Aset</span>
            </Link>
        </template>

        <template #afterTopbar>
            <!-- STICKY NAVBAR SHORTCUTS -->
            <div class="sticky top-[120px] lg:top-[52px] z-[25] bg-white border-b border-slate-200/80 w-full mt-[-1px] shadow-[0_4px_10px_-4px_rgba(0,0,0,0.05)]">
                <div class="max-w-[1400px] mx-auto px-4 md:px-6 lg:px-8">
                    <div class="flex items-center gap-8 overflow-x-auto hide-scrollbar scroll-smooth">
                        <button @click="scrollTo('foto')" :class="['py-3.5 text-[13px] md:text-[14px] font-bold border-b-[3px] whitespace-nowrap transition-colors mt-[1px]', activeSection === 'foto' ? 'border-[#FFC000] text-[#FFC000]' : 'border-transparent text-gray-500 hover:text-[#0A2540]']">Foto Aset</button>
                        <button @click="scrollTo('informasi')" :class="['py-3.5 text-[13px] md:text-[14px] font-bold border-b-[3px] whitespace-nowrap transition-colors mt-[1px]', activeSection === 'informasi' ? 'border-[#FFC000] text-[#FFC000]' : 'border-transparent text-gray-500 hover:text-[#0A2540]']">Informasi Umum</button>
                        <button v-if="assetFacilities.length > 0" @click="scrollTo('fasilitas')" :class="['py-3.5 text-[13px] md:text-[14px] font-bold border-b-[3px] whitespace-nowrap transition-colors mt-[1px]', activeSection === 'fasilitas' ? 'border-[#FFC000] text-[#FFC000]' : 'border-transparent text-gray-500 hover:text-[#0A2540]']">Fasilitas Aset</button>
                        <button @click="scrollTo('lokasi')" :class="['py-3.5 text-[13px] md:text-[14px] font-bold border-b-[3px] whitespace-nowrap transition-colors mt-[1px]', activeSection === 'lokasi' ? 'border-[#FFC000] text-[#FFC000]' : 'border-transparent text-gray-500 hover:text-[#0A2540]']">Lokasi</button>
                        <button v-if="asset.policies && asset.policies.length > 0" @click="scrollTo('kebijakan')" :class="['py-3.5 text-[13px] md:text-[14px] font-bold border-b-[3px] whitespace-nowrap transition-colors mt-[1px]', activeSection === 'kebijakan' ? 'border-[#FFC000] text-[#FFC000]' : 'border-transparent text-gray-500 hover:text-[#0A2540]']">Kebijakan</button>
                        <button v-if="asset.faqs && asset.faqs.length > 0" @click="scrollTo('faq')" :class="['py-3.5 text-[13px] md:text-[14px] font-bold border-b-[3px] whitespace-nowrap transition-colors mt-[1px]', activeSection === 'faq' ? 'border-[#FFC000] text-[#FFC000]' : 'border-transparent text-gray-500 hover:text-[#0A2540]']">FAQ</button>
                    </div>
                </div>
            </div>
        </template>

        <div class="mt-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 text-[#0A2540]">

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-[#0A2540] font-sans">

                    <!-- HERO GALLERY AND MODAL -->
                    <section id="foto">
                        <AssetGallery :images="asset.images" />
                    </section>

                    <!-- TITLE & HEADER -->
                    <div class="mb-6 mt-6 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-2xl sm:text-[32px] font-extrabold text-[#222222] mb-2 tracking-tight leading-tight">{{ asset.title }}</h1>
                                <!-- Lokasi -->
                                <div class="flex items-start gap-2 text-[14px] text-gray-600">
                                    <MapPin class="mt-0.5 text-gray-400 w-4 h-4" />
                                    <span class="leading-relaxed font-medium">{{ asset.city?.name }}, {{ asset.province?.name }}</span>
                                </div>
                            </div>
                            <!-- Status Badge -->
                            <div class="shrink-0 mt-1">
                                <span :class="['px-3 py-1.5 rounded-full text-xs font-bold border border-transparent shadow-sm', statusInfo.class]">
                                    {{ statusInfo.label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENT LAYOUT (Kiri: Detail, Kanan: Validation Action) -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-8 xl:gap-10">

                        <!-- KIRI (Detail) -->
                        <div class="lg:col-span-7 xl:col-span-8 space-y-10 min-w-0 overflow-hidden">

                            <!-- Informasi Umum -->
                            <div id="informasi">
                                <AssetSpecifications :detail="asset.detail" />

                                <!-- Deskripsi -->
                                <div class="py-10 md:py-12 border-b border-gray-100">
                                    <h3 class="text-[22px] font-bold text-[#222222] mb-4">Tentang Aset Ini</h3>
                                    <div class="text-[15px] text-gray-700 leading-8 whitespace-pre-line text-left relative">
                                        <div :class="{ 'line-clamp-4': !showFullDescription, 'overflow-hidden': !showFullDescription }">
                                            {{ asset.description || 'Tidak ada deskripsi.' }}
                                        </div>
                                    </div>
                                    <button v-if="asset.description && asset.description.length > 200" @click="showFullDescription = !showFullDescription" class="mt-3 text-black font-semibold hover:text-gray-700 underline underline-offset-2">
                                        {{ showFullDescription ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya >' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Fasilitas Utama -->
                            <div id="fasilitas" v-if="assetFacilities.length > 0" class="py-10 md:py-12 border-b border-gray-100">
                                <h3 class="text-[22px] font-semibold text-[#222222] mb-6">Fasilitas yang ditawarkan</h3>
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

                            <!-- Pilihan Unit -->
                            <div v-if="asset.units && asset.units.length > 0" id="pilihan-unit" class="py-10 md:py-12 border-b border-gray-100">
                                <h3 class="text-[22px] font-bold text-[#222222] mb-6">Daftar Unit Tersedia</h3>
                                <AssetUnitList
                                    :units="asset.units"
                                    :rentalUnitLabel="lowestPrice ? rentalUnitLabel(lowestPrice.rental_unit) : 'sewa'"
                                    :durationCount="1"
                                    :startDate="null"
                                    :endDate="null"
                                    :selectedUnitId="null"
                                    :isBookingMode="false"
                                />
                            </div>

                            <!-- Lokasi Map -->
                            <div id="lokasi" class="py-10 md:py-12 border-b border-gray-100">
                                <h3 class="text-[22px] font-bold text-[#222222] mb-4">Lokasi & Lingkungan</h3>
                                <p class="text-[15px] text-gray-700 mb-6 font-medium">{{ [asset.address, asset.village?.name, asset.district?.name, asset.city?.name, asset.province?.name, 'Indonesia'].filter(Boolean).join(', ') }} {{ asset.postal_code || '' }}</p>
                                <div class="w-full h-72 bg-gray-200 rounded-xl overflow-hidden relative mb-6">
                                    <div v-if="asset.latitude && asset.longitude" ref="mapContainer" class="w-full h-full z-0"></div>
                                    <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 p-4 shadow-lg">
                                        <MapPin class="text-red-500 text-3xl mb-2" />
                                        <span class="font-bold">Koordinat lokasi tidak tersedia</span>
                                    </div>
                                </div>

                                <!-- Nearby Places -->
                                <div v-if="Object.keys(nearbyPlaces).length > 0" class="mt-6">
                                    <h4 class="text-[18px] font-bold text-[#222222] mb-5">Jarak ke fasilitas publik</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                                        <div v-for="(places, category) in nearbyPlaces" :key="category" class="flex flex-col gap-2.5 min-w-0">
                                            <h5 class="text-[15px] font-semibold text-gray-800">{{ translateCategory(category) }}</h5>
                                            <ul class="flex flex-col gap-2 pl-3">
                                                <li v-for="place in places" :key="place.name" class="flex items-start gap-2.5">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0 mt-[7px]"></div>
                                                    <div class="flex-1 min-w-0 flex justify-between items-start gap-2">
                                                        <span class="text-[13px] text-gray-600 leading-tight flex-1 min-w-0 break-words pr-2">{{ place.name }}</span>
                                                        <span class="text-[13px] font-medium text-gray-900 whitespace-nowrap shrink-0">{{ place.distance < 1 ? `${Math.round(place.distance * 1000)} m` : `${place.distance.toFixed(2)} km` }}</span>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kebijakan -->
                            <div v-if="asset.policies && asset.policies.length > 0" id="kebijakan" class="py-10 md:py-12 border-b border-gray-100">
                                <AssetPolicy :policies="asset.policies" />
                            </div>

                            <!-- FAQ -->
                            <div v-if="asset.faqs && asset.faqs.length > 0" id="faq" class="py-10 md:py-12 border-b border-gray-100">
                                <AssetFaq :faqs="asset.faqs" :assetType="asset.type?.name" />
                            </div>


                        </div>

                        <!-- KANAN (Validation Action Card) -->
                        <div class="lg:col-span-5 xl:col-span-4 order-2 lg:order-2">
                            <div class="sticky top-24 flex flex-col gap-0">
                                <!-- Action Card -->
                                <div class="bg-white shadow-lg shadow-gray-200/50 rounded-t-lg p-5 md:p-6 border border-gray-100 border-b-0 relative z-10">

                                    <!-- Date & Duration Box (Adapted for Harga) -->
                                    <div class="border border-gray-200 rounded-lg overflow-hidden mb-5">
                                        <div class="px-3.5 py-3 border-b border-gray-200 bg-white text-center">
                                            <p class="text-[10px] md:text-xs uppercase font-bold text-gray-400 mb-1">Harga Mulai Dari</p>
                                            <div v-if="lowestPrice">
                                                <span class="text-[18px] md:text-[22px] font-extrabold text-[#0A2540]">{{ formatRupiah(lowestPrice.price) }}</span>
                                            </div>
                                            <div v-else class="text-amber-600 text-xs font-bold py-1">
                                                <AlertTriangle class="mr-1 inline w-4 h-4" /> Belum ditetapkan
                                            </div>
                                        </div>
                                        <div v-if="lowestPrice" class="px-3.5 py-2.5 bg-gray-50 flex justify-between items-center">
                                            <span class="text-[12px] md:text-[14px] font-semibold text-gray-500">Siklus Sewa</span>
                                            <span class="text-[13px] md:text-[15px] font-bold text-[#0A2540]">Per {{ lowestPrice.duration }} {{ rentalUnitLabel(lowestPrice.rental_unit) }}</span>
                                        </div>
                                    </div>

                                    <div v-if="asset.rejection_reason" class="bg-rose-50 rounded-xl p-4 border border-rose-200 mb-5">
                                        <span class="text-[10px] font-bold uppercase text-rose-500 block mb-1 flex items-center gap-1"><AlertTriangle class="w-3 h-3" /> Alasan Penolakan</span>
                                        <p class="text-xs text-rose-700 leading-relaxed">{{ asset.rejection_reason }}</p>
                                    </div>

                                    <div class="space-y-3" v-if="asset.status === 'pending'">
                                        <!-- Form reject -->
                                        <div v-if="showRejectForm" class="space-y-3 p-4 bg-white border border-slate-200 rounded-xl shadow-sm">
                                            <label class="text-xs font-bold text-slate-700 block">Alasan Penolakan <span class="text-rose-500">*</span></label>
                                            <textarea
                                                v-model="rejectReason"
                                                rows="3"
                                                placeholder="Jelaskan alasan aset ditolak..."
                                                class="w-full text-sm border border-slate-300 focus:ring-2 focus:ring-rose-400 focus:border-rose-400 outline-none rounded-xl px-3 py-2 transition resize-none"
                                            ></textarea>
                                            <div class="flex gap-2">
                                                <button @click="showRejectForm = false" class="flex-1 border border-gray-200 text-gray-600 font-bold px-3 py-2.5 rounded-lg text-xs hover:bg-gray-50 transition">Batal</button>
                                                <button
                                                    @click="rejectAsset"
                                                    :disabled="!rejectReason.trim() || isSubmitting"
                                                    class="flex-1 bg-rose-600 text-white font-bold px-3 py-2.5 rounded-lg text-xs hover:bg-rose-700 disabled:opacity-50 transition"
                                                >{{ isSubmitting ? 'Memproses...' : 'Tolak Aset' }}</button>
                                            </div>
                                        </div>

                                        <div v-else class="flex flex-col gap-2">
                                            <button
                                                @click="approveAsset"
                                                :disabled="isSubmitting"
                                                class="w-full py-3 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-extrabold rounded-lg transition-all shadow-sm flex justify-center items-center gap-1.5 text-[14px] disabled:opacity-50"
                                            >
                                                {{ isSubmitting ? 'Memproses...' : 'Validasi (Terima Aset)' }}
                                            </button>
                                            <button
                                                @click="showRejectForm = true"
                                                :disabled="isSubmitting"
                                                class="w-full py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-extrabold rounded-lg transition-all flex justify-center items-center gap-1.5 text-[14px]"
                                            >
                                                Tolak Pengajuan
                                            </button>
                                        </div>
                                    </div>

                                    <div v-else-if="asset.status === 'approved'" class="text-center bg-[#E8F5E9] text-[#4CAF50] border border-[#C8E6C9] rounded-lg p-3 text-[13px] md:text-[14px] font-bold">
                                        <Check class="w-4 h-4 mx-auto mb-1" />
                                        Aset ini telah disetujui
                                    </div>

                                </div>

                                <!-- Card Pemilik Aset -->
                                <div class="bg-white rounded-b-lg shadow-lg shadow-gray-200/50 border border-gray-100 p-5 md:p-6 border-t border-gray-100 relative z-0">
                                    <h3 class="text-[14px] md:text-[16px] font-bold text-[#0A2540] mb-3">Informasi Pemilik</h3>
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-gray-200 bg-gray-50">
                                            <img
                                                v-if="asset.owner_profile?.user?.profile_photo"
                                                :src="'/storage/' + asset.owner_profile.user.profile_photo"
                                                class="w-full h-full object-cover"
                                            />
                                            <AvatarMale v-else-if="asset.owner_profile?.user?.gender === 'male'" class="w-full h-full" />
                                            <AvatarFemale v-else-if="asset.owner_profile?.user?.gender === 'female'" class="w-full h-full" />
                                            <AvatarDefault v-else class="w-full h-full" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[14px] md:text-[15px] font-bold text-[#0A2540] truncate">{{ asset.owner_profile?.user?.name || '-' }}</p>
                                            <p class="text-[12px] md:text-[13px] text-gray-500 truncate">{{ asset.owner_profile?.user?.email || '-' }}</p>
                                            <p v-if="asset.owner_profile?.user?.phone" class="text-[12px] md:text-[13px] text-gray-500">{{ asset.owner_profile.user.phone }}</p>
                                        </div>
                                    </div>
                                    <Link
                                        v-if="asset.owner_profile?.user?.id"
                                        :href="route('admin.user-management.show', asset.owner_profile.user.id)"
                                        class="w-full flex items-center justify-center gap-1.5 py-2.5 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] hover:bg-[#F1F5F9] transition text-[13px] font-bold text-[#334155]"
                                    >
                                        Lihat Profil Lengkap
                                    </Link>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
