<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { Search, Locate, Lightbulb, Loader2, MapPin } from 'lucide-vue-next';
import axios from 'axios';
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

let isAutoFilling = false;

watch(() => form.province_code, (newVal, oldVal) => {
    if (isAutoFilling) return;
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
    if (isAutoFilling) return;
    if (newVal) fetchDistricts(newVal);
    if (oldVal && newVal !== oldVal) {
        form.district_code = '';
        form.village_code = '';
        districts.value = [];
        villages.value = [];
    }
});

watch(() => form.district_code, (newVal, oldVal) => {
    if (isAutoFilling) return;
    if (newVal) fetchVillages(newVal);
    if (oldVal && newVal !== oldVal) {
        form.village_code = '';
        villages.value = [];
    }
});

const normalizeRegionName = (name) => {
    if (!name) return '';
    return name.toLowerCase()
        .replace(/^(provinsi|kota|kabupaten|kab\.|kecamatan|kelurahan|desa|daerah khusus ibukota|dki)\s+/g, '')
        .replace(/[^a-z0-9]/g, '');
};

const matchRegion = (list, searchName) => {
    if (!list || !searchName) return null;
    const normalizedSearch = normalizeRegionName(searchName);
    return list.find(item => normalizeRegionName(item.name) === normalizedSearch)
        || list.find(item => normalizeRegionName(item.name).includes(normalizedSearch))
        || list.find(item => normalizedSearch.includes(normalizeRegionName(item.name)));
};

const autoFillRegions = async (address) => {
    if (!address) return;
    isAutoFilling = true;

    try {
        const stateName = address.state || address.region;
        if (stateName) {
            const province = matchRegion(provinces.value, stateName);
            if (province) {
                form.province_code = province.code;
                await fetchCities(province.code);

                const cityName = address.city || address.county || address.town;
                if (cityName) {
                    const city = matchRegion(cities.value, cityName);
                    if (city) {
                        form.city_code = city.code;
                        await fetchDistricts(city.code);

                        const districtName = address.city_district || address.suburb;
                        if (districtName) {
                            const district = matchRegion(districts.value, districtName);
                            if (district) {
                                form.district_code = district.code;
                                await fetchVillages(district.code);

                                const villageName = address.village || address.neighbourhood || address.quarter;
                                if (villageName) {
                                    const village = matchRegion(villages.value, villageName);
                                    if (village) {
                                        form.village_code = village.code;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } catch (e) {
        console.error("Gagal auto-fill wilayah", e);
    } finally {
        setTimeout(() => { isAutoFilling = false; }, 100);
    }
};



// --- LOGIKA PETA LOKASI (LEAFLET + OPENSTREETMAP) ---
const mapContainer = ref(null);
const cariAlamatInput = ref(form.location_name || '');
const isSearchingAlamat = ref(false);
const isLocatingGPS = ref(false);

watch(isLocatingGPS, (val) => {
    if (markerInstance && markerInstance.getElement()) {
        const pingEl = markerInstance.getElement().querySelector('.gps-ping-effect');
        if (pingEl) {
            if (val) {
                pingEl.classList.remove('hidden');
            } else {
                pingEl.classList.add('hidden');
            }
        }
    }
});

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

    mapInstance = L.map(mapContainer.value, {
        zoomControl: false,
        minZoom: 5,
        maxBoundsViscosity: 1.0
    }).setView([lat, lng], form.latitude ? 16 : DEFAULT_ZOOM);
    L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(mapInstance);

    if (form.latitude && form.longitude) {
        pasangMarker(lat, lng);
        fetchNearbyPreview(lat, lng);
    }

    // Klik di peta -> pasang/pindah marker & isi koordinat form
    mapInstance.on('click', (e) => {
        pasangMarker(e.latlng.lat, e.latlng.lng, true);
        fetchNearbyPreview(e.latlng.lat, e.latlng.lng);
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
            if (data.address) {
                autoFillRegions(data.address);
            }
        }
    } catch (e) {
        console.error("Gagal reverse geocode", e);
    } finally {
        isFetchingAddress.value = false;
    }
};

const previewNearbyPlaces = ref({});
const isFetchingNearby = ref(false);

const fetchNearbyPreview = async (lat, lng) => {
    isFetchingNearby.value = true;
    try {
        const res = await axios.get(route('owner.asset.preview-nearby'), { params: { lat, lon: lng } });
        previewNearbyPlaces.value = res.data || {};
    } catch (e) {
        console.error("Gagal memuat tempat terdekat:", e);
    } finally {
        isFetchingNearby.value = false;
    }
};

const categoryLabels = {
    health: 'Fasilitas Kesehatan',
    public_transport: 'Transportasi Publik',
    shopping: 'Pusat Perbelanjaan',
    recreation: 'Tempat Rekreasi',
    food: 'Kuliner',
    religious: 'Tempat Ibadah',
    education: 'Pendidikan',
};

const formatDistance = (km) => {
    if (km < 1) {
        return `${Math.round(km * 1000)} m`;
    }
    return `${km.toFixed(2)} km`;
};

const forRentSvgStr = `<svg fill="#0A2540" viewBox="0 0 485 485" class="w-5 h-5">
    <path d="M485,60H295.969V0H189.031v60H0v278.879h189.031V485h106.938V338.879H485V60z"/>
</svg>`;

const customMarkerIcon = L.divIcon({
    className: 'custom-leaflet-pin',
    html: `
        <div style="position: relative; width: 44px; height: 44px;">
            <div style="width: 44px; height: 44px; background-color: #FFC000; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 2px 2px 6px rgba(0,0,0,0.3); position: absolute; left: 0; top: 0;"></div>
            <div style="width: 22px; height: 22px; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 2; display:flex; align-items:center; justify-content:center;">
                ${forRentSvgStr}
            </div>
        </div>
        <div style="width: 24px; height: 8px; background: rgba(0,0,0,0.3); border-radius: 50%; filter: blur(2px); position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%);"></div>
        <div class="gps-ping-effect hidden absolute" style="left: 22px; top: 52px; transform: translate(-50%, -50%); z-index: -1; pointer-events: none; width: 100px; height: 100px;">
            <div class="w-full h-full bg-[#FFC000] rounded-full animate-ping opacity-75"></div>
        </div>
    `,
    iconSize: [44, 52],
    iconAnchor: [22, 52]
});

const pasangMarker = (lat, lng, doReverseGeocode = false) => {
    form.latitude = lat.toFixed(6);
    form.longitude = lng.toFixed(6);

    if (markerInstance) {
        markerInstance.setLatLng([lat, lng]);
    } else {
        markerInstance = L.marker([lat, lng], { icon: customMarkerIcon, draggable: true }).addTo(mapInstance);
        markerInstance.on('dragend', () => {
            const pos = markerInstance.getLatLng();
            form.latitude = pos.lat.toFixed(6);
            form.longitude = pos.lng.toFixed(6);
            fetchReverseGeocode(pos.lat, pos.lng);
            fetchNearbyPreview(pos.lat, pos.lng);
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
        const lat = form.latitude || -0.5021;
        const lon = form.longitude || 117.1536;
        
        // Gunakan Photon API (lebih bagus untuk typo / partial match dibanding nominatim)
        const res = await fetch(
            `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5&lat=${lat}&lon=${lon}`
        );
        const data = await res.json();
        
        searchSuggestions.value = (data.features || []).map(f => {
            const p = f.properties;
            const displayNameParts = [p.name, p.street, p.district, p.city, p.state].filter(Boolean);
            const displayName = [...new Set(displayNameParts)].join(', ');
            
            return {
                name: p.name || p.street || 'Lokasi',
                display_name: displayName,
                lat: f.geometry.coordinates[1],
                lon: f.geometry.coordinates[0],
                boundingbox: p.extent ? [p.extent[1], p.extent[3], p.extent[0], p.extent[2]] : null,
            };
        });
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
    
    if (cariAlamatInput.value.trim().length < 3) {
        isSearchingAlamat.value = false;
        showSuggestions.value = false;
        searchSuggestions.value = [];
    } else {
        // Jangan paksa skeleton muncul saat user masih ngetik (debounce delay).
        // Biarkan list lama tetap tampil atau dropdown tidak muncul sampai benar-benar fetch data.
        showSuggestions.value = true;
    }

    searchTimeout = setTimeout(() => {
        if (cariAlamatInput.value.trim().length >= 3) {
            fetchSuggestions();
        }
    }, 500);
};

const onSearchFocus = () => {
    if (cariAlamatInput.value.length >= 3 && searchSuggestions.value.length > 0) {
        showSuggestions.value = true;
    }
};

const selectSuggestion = (suggestion) => {
    cariAlamatInput.value = suggestion.name;
    form.location_name = suggestion.display_name;
    showSuggestions.value = false;

    const lat = parseFloat(suggestion.lat);
    const lon = parseFloat(suggestion.lon);

    mapInstance.setMaxBounds(null);
    if (suggestion.boundingbox) {
        const [latMin, latMax, lonMin, lonMax] = suggestion.boundingbox;
        const bounds = [
            [parseFloat(latMin), parseFloat(lonMin)],
            [parseFloat(latMax), parseFloat(lonMax)]
        ];
        mapInstance.fitBounds(bounds, { maxZoom: 17 });
    } else {
        mapInstance.setView([lat, lon], 17);
    }
    
    // pasangMarker dgn true akan memanggil Nominatim Reverse Geocode yang melengkapi semua alamat (rt, kelurahan, dst)
    pasangMarker(lat, lon, true);
    fetchNearbyPreview(lat, lon);
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
            mapInstance.setMaxBounds(null);
            mapInstance.setView([latitude, longitude], 17);
            pasangMarker(latitude, longitude, true);
            fetchNearbyPreview(latitude, longitude);
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
                        class="px-3 text-slate-500 hover:text-[#0A2540] hover:bg-slate-50 transition flex items-center justify-center disabled:opacity-50 disabled:cursor-wait"
                    >
                        <Loader2 v-if="isLocatingGPS" class="w-4 h-4 animate-spin text-[#FFC000]" />
                        <Locate v-else class="w-4 h-4" />
                    </button>
                </div>

                <!-- Suggestions Dropdown -->
                <ul v-if="showSuggestions" class="absolute w-full mt-1 bg-white border border-slate-200 rounded-md shadow-lg max-h-60 overflow-auto z-[500]">
                    <template v-if="isSearchingAlamat">
                        <li class="px-4 py-3 border-b border-slate-100 flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-3.5 h-3.5 rounded-full bg-slate-200 animate-pulse shrink-0"></div>
                                <div class="h-3.5 bg-slate-200 rounded animate-pulse w-2/3"></div>
                            </div>
                            <div class="h-2.5 bg-slate-200 rounded animate-pulse w-1/3 ml-5.5 pl-0.5"></div>
                        </li>
                        <li class="px-4 py-3 border-b border-slate-100 flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-3.5 h-3.5 rounded-full bg-slate-200 animate-pulse shrink-0"></div>
                                <div class="h-3.5 bg-slate-200 rounded animate-pulse w-3/4"></div>
                            </div>
                            <div class="h-2.5 bg-slate-200 rounded animate-pulse w-1/2 ml-5.5 pl-0.5"></div>
                        </li>
                    </template>
                    <li v-else-if="searchSuggestions.length === 0" class="px-4 py-3 text-sm text-slate-500 text-center">
                        Lokasi tidak ditemukan
                    </li>
                    <template v-else>
                        <li
                            v-for="(suggestion, i) in searchSuggestions" :key="i"
                            @click="selectSuggestion(suggestion)"
                            class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer text-sm border-b border-slate-100 last:border-0"
                        >
                            <div class="font-medium text-slate-800 line-clamp-1 flex items-center gap-2">
                                <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                {{ suggestion.name || suggestion.display_name.split(',')[0] }}
                            </div>
                            <div class="text-xs text-slate-500 line-clamp-1 mt-0.5 ml-5.5 pl-0.5">
                                {{ suggestion.display_name }}
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Leaflet Map Container -->
            <div ref="mapContainer" class="w-full h-full z-0"></div>
        </div>

        <!-- Preview Nearby Places -->
        <div v-if="isFetchingNearby || Object.keys(previewNearbyPlaces).length > 0" class="mt-4 mb-6 p-4 border border-slate-200 rounded-md bg-white">
            <h3 class="text-sm font-semibold text-slate-800 mb-5 flex items-center gap-2">
                <MapPin class="w-4 h-4 text-[#FFC000]" />
                Tempat Terdekat dari Lokasi Pin
            </h3>

            <div v-if="!isFetchingNearby && Object.keys(previewNearbyPlaces).length === 0" class="text-sm text-slate-500 italic">
                Belum ada data tempat terdekat.
            </div>

            <!-- SKELETON LOADER -->
            <div v-if="isFetchingNearby" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <div v-for="i in 4" :key="i" class="flex flex-col gap-2.5 min-w-0">
                    <div class="h-[18px] bg-slate-200 rounded animate-pulse w-1/3"></div>
                    <ul class="flex flex-col gap-2 pl-3">
                        <li v-for="j in 3" :key="j" class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0 mt-[7px] animate-pulse"></div>
                            <div class="flex-1 min-w-0 flex justify-between items-start gap-2">
                                <div class="h-[15px] bg-slate-200 rounded animate-pulse w-3/4"></div>
                                <div class="h-[15px] bg-slate-200 rounded animate-pulse w-10 shrink-0"></div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ACTUAL DATA -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <div v-for="(places, category) in previewNearbyPlaces" :key="category" class="flex flex-col gap-2.5 min-w-0">
                    <h5 class="text-[15px] font-semibold text-gray-800">{{ categoryLabels[category] || category }}</h5>
                    <ul class="flex flex-col gap-2 pl-3">
                        <li v-for="place in places" :key="place.name" class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0 mt-[7px]"></div>
                            <div class="flex-1 min-w-0 flex justify-between items-start gap-2">
                                <span class="text-[13px] text-gray-600 leading-tight flex-1 min-w-0 break-words pr-2">{{ place.name }}</span>
                                <span class="text-[13px] font-medium text-gray-900 whitespace-nowrap shrink-0">{{ formatDistance(place.distance) }}</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Info koordinat terpilih -->
        <div class="flex items-center gap-2 mt-3 text-xs" :class="form.latitude ? 'text-emerald-600' : 'text-slate-500'">
            <AppIcon :iconClass="['fa-solid', form.latitude ? 'fa-circle-check' : 'fa-circle-exclamation']" />
            <span v-if="form.latitude">
                Titik lokasi terpilih: {{ form.latitude }}, {{ form.longitude }}
            </span>
            <span v-else>Belum ada titik lokasi dipilih.</span>
        </div>

        <!-- Peringatan lengkapi alamat -->
        <div class="mt-4 flex items-center gap-3 p-3.5 bg-white border border-slate-200 rounded-md">
            <Lightbulb class="w-5 h-5 text-primary shrink-0" />
            <p class="text-[13px] text-slate-700 font-medium">Periksa kembali alamat Anda. Data dari Maps mungkin tidak sepenuhnya sesuai.</p>
        </div>
    </div>

    <div class="w-full h-px bg-slate-200 my-2"></div>

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
