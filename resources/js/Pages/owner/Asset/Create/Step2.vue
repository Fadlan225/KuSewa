<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Search, Locate } from 'lucide-vue-next';
import { computed, watch, ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import L from 'leaflet';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';

const props = defineProps({ form: Object, currentStep: Number, assetTypeName: String });
const form = props.form;

// --- STATE LOKASI LENGKAP ---
const provinces = ref([]);
const cities = ref([]);
const districts = ref([]);
const villages = ref([]);

const fetchProvinces = async () => {
    try {
        const res = await fetch('/api/provinces');
        const json = await res.json();
        provinces.value = json.data || [];
    } catch (e) {
        console.error('Gagal mengambil data provinsi', e);
    }
};

const fetchCities = async (provinceCode) => {
    try {
        const res = await fetch(`/api/cities?province_code=${provinceCode}`);
        const json = await res.json();
        cities.value = json.data || [];
    } catch (e) {
        console.error('Gagal mengambil data kota', e);
    }
};

const fetchDistricts = async (cityCode) => {
    try {
        const res = await fetch(`/api/districts?city_code=${cityCode}`);
        const json = await res.json();
        districts.value = json.data || [];
    } catch (e) {
        console.error('Gagal mengambil data kecamatan', e);
    }
};

const fetchVillages = async (districtCode) => {
    try {
        const res = await fetch(`/api/villages?district_code=${districtCode}`);
        const json = await res.json();
        villages.value = json.data || [];
    } catch (e) {
        console.error('Gagal mengambil data kelurahan/desa', e);
    }
};

watch(() => form.province_code, (newVal, oldVal) => {
    if (newVal) fetchCities(newVal);
    if (oldVal && newVal !== oldVal) {
        form.city_code = '';
        form.district_code = '';
        form.village_code = '';
        cities.value = [];
        districts.value = [];
        villages.value = [];
    }
});

watch(() => form.city_code, (newVal, oldVal) => {
    if (newVal) fetchDistricts(newVal);
    if (oldVal && newVal !== oldVal) {
        form.district_code = '';
        form.village_code = '';
        districts.value = [];
        villages.value = [];
    }
});

watch(() => form.district_code, (newVal, oldVal) => {
    if (newVal) fetchVillages(newVal);
    if (oldVal && newVal !== oldVal) {
        form.village_code = '';
        villages.value = [];
    }
});



// --- LOGIKA PETA LOKASI (LEAFLET + OPENSTREETMAP) ---
const mapContainer = ref(null);
const cariAlamatInput = ref(form.location_name || '');
const isSearchingAlamat = ref(false);
const isLocatingGPS = ref(false);
let mapInstance = null;
let markerInstance = null;
const searchSuggestions = ref([]);
const showSuggestions = ref(false);
let searchTimeout = null;

// Titik default (sekitar Kalimantan Timur) jika owner belum memilih titik lokasi
const DEFAULT_LAT = -0.5021;
const DEFAULT_LNG = 117.1536;
const DEFAULT_ZOOM = 12;

const initMap = () => {
    if (!mapContainer.value) return;

    // Hancurkan instance lama dulu (mis. saat pindah step lalu balik lagi ke Step 2)
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
        markerInstance = null;
    }

    const lat = form.latitude ? parseFloat(form.latitude) : DEFAULT_LAT;
    const lng = form.longitude ? parseFloat(form.longitude) : DEFAULT_LNG;

    mapInstance = L.map(mapContainer.value, { zoomControl: false }).setView([lat, lng], form.latitude ? 16 : DEFAULT_ZOOM);
    L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(mapInstance);

    if (form.latitude && form.longitude) {
        pasangMarker(lat, lng);
    }

    // Klik di peta -> pasang/pindah marker & isi koordinat form
    mapInstance.on('click', (e) => {
        pasangMarker(e.latlng.lat, e.latlng.lng, true);
    });

    // Perbaiki bug ukuran peta yang kadang blank saat container baru terlihat
    setTimeout(() => mapInstance.invalidateSize(), 200);
};

const isFetchingAddress = ref(false);

const fetchReverseGeocode = async (lat, lng) => {
    isFetchingAddress.value = true;
    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
        const data = await res.json();
        if (data && data.display_name) {
            form.address = data.display_name;
            cariAlamatInput.value = data.name || data.display_name.split(',')[0];
            if (data.address && data.address.postcode) {
                form.postal_code = data.address.postcode.replace(/\D/g, '').slice(0, 5);
            }
        }
    } catch (e) {
        console.error("Gagal reverse geocode", e);
    } finally {
        isFetchingAddress.value = false;
    }
};

const pasangMarker = (lat, lng, doReverseGeocode = false) => {
    form.latitude = lat.toFixed(6);
    form.longitude = lng.toFixed(6);

    if (markerInstance) {
        markerInstance.setLatLng([lat, lng]);
    } else {
        markerInstance = L.marker([lat, lng], { draggable: true }).addTo(mapInstance);
        markerInstance.on('dragend', () => {
            const pos = markerInstance.getLatLng();
            form.latitude = pos.lat.toFixed(6);
            form.longitude = pos.lng.toFixed(6);
            fetchReverseGeocode(pos.lat, pos.lng);
        });
    }

    mapInstance.panTo([lat, lng]);
    if (doReverseGeocode) {
        fetchReverseGeocode(lat, lng);
    }
};

const fetchSuggestions = async () => {
    const query = cariAlamatInput.value.trim();
    if (!query || query.length < 3) {
        searchSuggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    isSearchingAlamat.value = true;
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=id&addressdetails=1&q=${encodeURIComponent(query)}`
        );
        const data = await res.json();
        searchSuggestions.value = data || [];
        showSuggestions.value = searchSuggestions.value.length > 0;
    } catch (err) {
        console.error('Gagal mengambil saran lokasi:', err);
    } finally {
        isSearchingAlamat.value = false;
    }
};

const onSearchInput = () => {
    form.location_name = cariAlamatInput.value;
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchSuggestions();
    }, 500);
};

const onSearchFocus = () => {
    if (cariAlamatInput.value.length >= 3 && searchSuggestions.value.length > 0) {
        showSuggestions.value = true;
    }
};

const selectSuggestion = (suggestion) => {
    cariAlamatInput.value = suggestion.name || (suggestion.display_name ? suggestion.display_name.split(',')[0] : '');
    form.location_name = suggestion.display_name;
    showSuggestions.value = false;

    const lat = parseFloat(suggestion.lat);
    const lon = parseFloat(suggestion.lon);

    mapInstance.setView([lat, lon], 17);
    pasangMarker(lat, lon, false);

    form.address = suggestion.display_name;
    if (suggestion.address && suggestion.address.postcode) {
        form.postal_code = suggestion.address.postcode.replace(/\D/g, '').slice(0, 5);
    }
};

// Fungsi cariLokasiDiPeta dan alert dihapus. User harus memilih dari suggestions.

// Tutup suggestions saat klik di luar
const closeSuggestions = (e) => {
    if (!e.target.closest('.search-container')) {
        showSuggestions.value = false;
    }
};

// Gunakan lokasi GPS perangkat owner saat ini
const gunakanLokasiSaatIni = () => {
    if (!navigator.geolocation) return;
    isLocatingGPS.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const { latitude, longitude } = pos.coords;
            mapInstance.setView([latitude, longitude], 17);
            pasangMarker(latitude, longitude, true);
            isLocatingGPS.value = false;
        },
        (err) => {
            console.error(err);
            isLocatingGPS.value = false;
        }
    );
};

const clearSearch = () => {
    cariAlamatInput.value = '';
    searchSuggestions.value = [];
    showSuggestions.value = false;
};

// Peta hanya perlu di-init saat Step 2 aktif, karena elemen mapContainer
// dilepas dari DOM oleh v-if ketika step lain sedang tampil
watch(() => props.currentStep, async (step) => {
    if (step === 2) {
        await nextTick();
        initMap();
    }
});

onMounted(() => {
    fetchProvinces();
    if (form.province_code) fetchCities(form.province_code);
    if (form.city_code) fetchDistricts(form.city_code);
    if (form.district_code) fetchVillages(form.district_code);
    document.addEventListener('click', closeSuggestions);
});

onBeforeUnmount(() => {
    if (mapInstance) mapInstance.remove();
    document.removeEventListener('click', closeSuggestions);
});


</script>

<template>
<div class="space-y-6">
<!-- STEP 2: LOKASI -->
    <h2 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-4">
        Alamat {{ assetTypeName || 'Properti' }}
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Provinsi <span class="text-rose-500">*</span></label>
            <SearchableSelect
                v-model="form.province_code"
                :options="provinces"
                placeholder="Cari atau pilih provinsi"
            />
        </div>
        <template v-if="form.province_code">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kota<span class="text-rose-500">*</span></label>
                <SearchableSelect
                    v-model="form.city_code"
                    :options="cities"
                    placeholder="Cari atau pilih kota"
                    :disabled="!cities.length"
                />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kecamatan <span class="text-rose-500">*</span></label>
                <SearchableSelect
                    v-model="form.district_code"
                    :options="districts"
                    placeholder="Cari atau pilih kecamatan"
                    :disabled="!districts.length"
                />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kelurahan / Desa <span class="text-rose-500">*</span></label>
                <SearchableSelect
                    v-model="form.village_code"
                    :options="villages"
                    placeholder="Cari atau pilih kelurahan/desa"
                    :disabled="!villages.length"
                />
            </div>
        </template>
    </div>

    <!-- PILIH TITIK KOORDINAT DI PETA -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
            Titik Lokasi di Peta <span class="text-rose-500">*</span>
        </label>
        <p class="text-xs text-slate-500 mb-2.5">
            Cari alamat di kolom pencarian pada peta, gunakan lokasi GPS Anda, atau klik/geser pin langsung di peta untuk menandai titik properti secara presisi.
        </p>

        <!-- Container Peta & Search Overlay -->
        <div class="relative w-full h-[400px] rounded-md border border-slate-300 overflow-hidden z-0 bg-slate-100">
            <!-- Search bar overlay -->
            <div class="absolute top-3 left-3 right-3 sm:left-4 sm:right-4 z-[400] search-container">
                <div class="flex bg-white rounded-md shadow-md overflow-hidden border border-slate-200">
                    <div class="flex items-center justify-center pl-3 pr-2 text-slate-400">
                        <Search class="w-4 h-4" />
                    </div>
                    <input
                        v-model="cariAlamatInput"
                        @input="onSearchInput"
                        @focus="onSearchFocus"
                        type="text"
                        placeholder="Masukan nama lokasi/area/alamat..."
                        class="flex-1 text-sm py-2.5 px-0 border-none focus:outline-none focus:ring-0 placeholder:text-slate-400"
                    />

                    <button v-if="cariAlamatInput" type="button" @click="clearSearch" class="px-2 text-slate-400 hover:text-slate-600 transition flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>

                    <div class="w-[1px] bg-slate-200 my-2"></div>

                    <button
                        type="button" @click.prevent="gunakanLokasiSaatIni" :disabled="isLocatingGPS"
                        title="Gunakan Lokasi Saat Ini"
                        class="px-3 text-slate-500 hover:text-[#0A2540] hover:bg-slate-50 transition flex items-center justify-center disabled:opacity-50"
                    >
                        <Locate class="w-4 h-4" :class="{'animate-pulse text-[#FFC000]': isLocatingGPS}" />
                    </button>
                </div>

                <!-- Suggestions Dropdown -->
                <transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <div
                        v-if="showSuggestions || isSearchingAlamat"
                        class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 shadow-lg rounded-md max-h-60 overflow-y-auto z-50 hide-scrollbar"
                    >
                        <div v-if="isSearchingAlamat" class="p-4 space-y-3">
                            <div class="h-3.5 bg-slate-200/60 rounded w-3/4 animate-pulse"></div>
                            <div class="h-3.5 bg-slate-200/60 rounded w-1/2 animate-pulse"></div>
                            <div class="h-3.5 bg-slate-200/60 rounded w-5/6 animate-pulse"></div>
                        </div>
                        <template v-else>
                            <div v-if="searchSuggestions.length === 0 && cariAlamatInput.length >= 3" class="px-4 py-3 text-sm text-slate-500 text-center">
                                Lokasi tidak ditemukan.
                            </div>
                            <div
                                v-for="(suggestion, idx) in searchSuggestions"
                                :key="idx"
                                @click="selectSuggestion(suggestion)"
                                class="px-4 py-2.5 text-xs text-slate-700 cursor-pointer hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0"
                            >
                                <p class="font-semibold text-slate-800 line-clamp-1">{{ suggestion.name || (suggestion.display_name ? suggestion.display_name.split(',')[0] : '') }}</p>
                                <p class="text-slate-500 line-clamp-1 mt-0.5">{{ suggestion.display_name }}</p>
                            </div>
                        </template>
                    </div>
                </transition>
            </div>

            <!-- Leaflet Map Instance -->
            <div ref="mapContainer" class="w-full h-full z-0"></div>
        </div>

        <!-- Info koordinat terpilih -->
        <div class="flex items-center gap-2 mt-3 text-xs" :class="form.latitude ? 'text-emerald-600' : 'text-slate-500'">
            <AppIcon :iconClass="['fa-solid', form.latitude ? 'fa-circle-check' : 'fa-circle-exclamation']" />
            <span v-if="form.latitude">
                Titik lokasi terpilih: {{ form.latitude }}, {{ form.longitude }}
            </span>
            <span v-else>Belum ada titik lokasi dipilih.</span>
        </div>
    </div>

    <!-- Alamat Lengkap & Kode Pos (Auto-fill dari Peta) -->
    <div class="grid grid-cols-1 sm:grid-cols-[1fr_120px] gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Lengkap <span class="text-rose-500">*</span></label>
            <div v-if="isFetchingAddress" class="h-10 bg-slate-200/60 rounded-md animate-pulse w-full"></div>
            <input v-else v-model="form.address" type="text" placeholder="Jl. M. Yamin No. 45, RT 12" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition" required />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Pos</label>
            <div v-if="isFetchingAddress" class="h-10 bg-slate-200/60 rounded-md animate-pulse w-full"></div>
            <input
                v-else
                :value="form.postal_code"
                @input="e => { form.postal_code = e.target.value.replace(/\D/g, '').slice(0, 5); e.target.value = form.postal_code; }"
                type="text"
                placeholder="50123"
                minlength="5"
                maxlength="5"
                class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition"
            />
        </div>
    </div>

</div>
</template>
