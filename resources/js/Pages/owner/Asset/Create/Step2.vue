<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Search, Locate } from 'lucide-vue-next';
import { computed, watch, ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import L from 'leaflet';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';

const props = defineProps({ form: Object, currentStep: Number });
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

watch(() => form.province_code, (newVal) => {
    form.city_code = '';
    cities.value = [];
    if (newVal) fetchCities(newVal);
});

watch(() => form.city_code, (newVal) => {
    form.district_code = '';
    districts.value = [];
    if (newVal) fetchDistricts(newVal);
});

watch(() => form.district_code, (newVal) => {
    form.village_code = '';
    villages.value = [];
    if (newVal) fetchVillages(newVal);
});



// --- LOGIKA PETA LOKASI (LEAFLET + OPENSTREETMAP) ---
const mapContainer = ref(null);
const cariAlamatInput = ref('');
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

    mapInstance = L.map(mapContainer.value).setView([lat, lng], form.latitude ? 16 : DEFAULT_ZOOM);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(mapInstance);

    if (form.latitude && form.longitude) {
        pasangMarker(lat, lng);
    }

    // Klik di peta -> pasang/pindah marker & isi koordinat form
    mapInstance.on('click', (e) => {
        pasangMarker(e.latlng.lat, e.latlng.lng);
    });

    // Perbaiki bug ukuran peta yang kadang blank saat container baru terlihat
    setTimeout(() => mapInstance.invalidateSize(), 200);
};

const pasangMarker = (lat, lng) => {
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
        });
    }

    mapInstance.panTo([lat, lng]);
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
            `https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=id&q=${encodeURIComponent(query)}`
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
    cariAlamatInput.value = suggestion.display_name;
    showSuggestions.value = false;
    
    const lat = parseFloat(suggestion.lat);
    const lon = parseFloat(suggestion.lon);
    
    mapInstance.setView([lat, lon], 17);
    pasangMarker(lat, lon);
};

// Fallback jika user tetap menekan tombol Cari atau Enter
const cariLokasiDiPeta = async () => {
    if (searchSuggestions.value.length > 0) {
        selectSuggestion(searchSuggestions.value[0]);
    } else {
        await fetchSuggestions();
        if (searchSuggestions.value.length > 0) {
            selectSuggestion(searchSuggestions.value[0]);
        } else {
            alert('Lokasi tidak ditemukan, coba kata kunci lain atau langsung klik di peta.');
        }
    }
};

// Tutup suggestions saat klik di luar
const closeSuggestions = (e) => {
    if (!e.target.closest('.search-container')) {
        showSuggestions.value = false;
    }
};

// Gunakan lokasi GPS perangkat owner saat ini
const gunakanLokasiSaatIni = () => {
    if (!navigator.geolocation) {
        alert('Perangkat/browser Anda tidak mendukung GPS.');
        return;
    }
    isLocatingGPS.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const { latitude, longitude } = pos.coords;
            mapInstance.setView([latitude, longitude], 17);
            pasangMarker(latitude, longitude);
            isLocatingGPS.value = false;
        },
        (err) => {
            console.error(err);
            alert('Gagal mengambil lokasi GPS. Pastikan izin lokasi diaktifkan.');
            isLocatingGPS.value = false;
        }
    );
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
        Detail Lokasi
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
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-[1fr_120px] gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Lengkap <span class="text-rose-500">*</span></label>
            <input v-model="form.address" type="text" placeholder="Jl. M. Yamin No. 45, RT 12" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent transition" required />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Pos</label>
            <input 
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

    <!-- PILIH TITIK KOORDINAT DI PETA -->
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
            Titik Lokasi di Peta <span class="text-rose-500">*</span>
        </label>
        <p class="text-xs text-slate-500 mb-2.5">
            Cari alamat di kolom bawah, gunakan lokasi GPS Anda, atau klik/geser pin langsung di peta untuk menandai titik properti secara presisi.
        </p>

        <!-- Search bar + tombol GPS -->
        <div class="flex gap-2 mb-3 search-container relative z-20">
            <div class="flex-1 relative">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm" />
                <input
                    v-model="cariAlamatInput"
                    @input="onSearchInput"
                    @focus="onSearchFocus"
                    @keyup.enter.prevent="cariLokasiDiPeta"
                    type="text"
                    placeholder="Cari nama jalan / area di peta..."
                    class="w-full text-sm pl-9 pr-4 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:border-transparent"
                />
                
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
                        v-if="showSuggestions"
                        class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 shadow-lg rounded-md max-h-60 overflow-y-auto z-50 hide-scrollbar"
                    >
                        <div
                            v-for="(suggestion, idx) in searchSuggestions"
                            :key="idx"
                            @click="selectSuggestion(suggestion)"
                            class="px-4 py-2.5 text-xs text-slate-700 cursor-pointer hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0"
                        >
                            <p class="font-semibold text-slate-800 line-clamp-1">{{ suggestion.name || (suggestion.display_name ? suggestion.display_name.split(',')[0] : '') }}</p>
                            <p class="text-slate-500 line-clamp-1 mt-0.5">{{ suggestion.display_name }}</p>
                        </div>
                    </div>
                </transition>
            </div>
            <button
                type="button" @click.prevent="cariLokasiDiPeta" :disabled="isSearchingAlamat"
                class="bg-primary text-secondary text-sm font-semibold px-4 rounded-md hover:bg-primary/90 transition cursor-pointer shrink-0 disabled:opacity-50"
            >
                {{ isSearchingAlamat ? 'Mencari...' : 'Cari' }}
            </button>
            <button
                type="button" @click.prevent="gunakanLokasiSaatIni" :disabled="isLocatingGPS"
                title="Gunakan Lokasi Saya Saat Ini"
                class="bg-slate-100 text-[#0A2540] text-sm font-semibold px-3.5 rounded-md hover:bg-slate-200 transition cursor-pointer shrink-0 disabled:opacity-50"
            >
                <Locate class="" />
            </button>
        </div>

        <!-- Container Peta -->
        <div ref="mapContainer" class="w-full h-80 rounded-md border border-slate-300 relative z-0"></div>

        <!-- Info koordinat terpilih -->
        <div class="flex items-center gap-2 mt-3 text-xs" :class="form.latitude ? 'text-emerald-600' : 'text-slate-500'">
            <AppIcon :iconClass="['fa-solid', form.latitude ? 'fa-circle-check' : 'fa-circle-exclamation']" />
            <span v-if="form.latitude">
                Titik lokasi terpilih: {{ form.latitude }}, {{ form.longitude }}
            </span>
            <span v-else>Belum ada titik lokasi dipilih.</span>
        </div>
    </div>

</div>
</template>
