<script setup>
import { computed, watch, ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import L from 'leaflet';

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
        provinces.value = await res.json();
    } catch (e) {
        console.error('Gagal mengambil data provinsi', e);
    }
};

const fetchCities = async (provinceCode) => {
    try {
        const res = await fetch(`/api/cities?province_code=${provinceCode}`);
        cities.value = await res.json();
    } catch (e) {
        console.error('Gagal mengambil data kota', e);
    }
};

const fetchDistricts = async (cityCode) => {
    try {
        const res = await fetch(`/api/districts?city_code=${cityCode}`);
        districts.value = await res.json();
    } catch (e) {
        console.error('Gagal mengambil data kecamatan', e);
    }
};

const fetchVillages = async (districtCode) => {
    try {
        const res = await fetch(`/api/villages?district_code=${districtCode}`);
        villages.value = await res.json();
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

// Cari alamat lewat Nominatim (geocoding gratis dari OpenStreetMap, tanpa API key)
const cariLokasiDiPeta = async () => {
    const query = cariAlamatInput.value.trim();
    if (!query) return;

    isSearchingAlamat.value = true;
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=id&q=${encodeURIComponent(query)}`
        );
        const data = await res.json();
        if (data.length > 0) {
            const { lat, lon } = data[0];
            mapInstance.setView([lat, lon], 17);
            pasangMarker(parseFloat(lat), parseFloat(lon));
        } else {
            alert('Lokasi tidak ditemukan, coba kata kunci lain atau langsung klik di peta.');
        }
    } catch (err) {
        console.error('Gagal mencari lokasi:', err);
        alert('Gagal mencari lokasi. Periksa koneksi internet Anda.');
    } finally {
        isSearchingAlamat.value = false;
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
});

onBeforeUnmount(() => {
    if (mapInstance) mapInstance.remove();
});


</script>

<template>
<div class="space-y-4">
<!-- STEP 2: LOKASI & FASILITAS -->
    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
        <i class="fa-solid fa-map-location-dot text-[#0A2540]"></i>
        <span>Detail Lokasi</span>
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi <span class="text-rose-500">*</span></label>
            <select v-model="form.province_code" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                <option value="" disabled>Pilih Provinsi</option>
                <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kota<span class="text-rose-500">*</span></label>
            <select v-model="form.city_code" :disabled="!cities.length" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition disabled:bg-slate-50 disabled:text-slate-400">
                <option value="" disabled>Pilih Kota</option>
                <option v-for="city in cities" :key="city.code" :value="city.code">{{ city.name }}</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan <span class="text-rose-500">*</span></label>
            <select v-model="form.district_code" :disabled="!districts.length" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition disabled:bg-slate-50 disabled:text-slate-400">
                <option value="" disabled>Pilih Kecamatan</option>
                <option v-for="dist in districts" :key="dist.code" :value="dist.code">{{ dist.name }}</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kelurahan / Desa <span class="text-rose-500">*</span></label>
            <select v-model="form.village_code" :disabled="!villages.length" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition disabled:bg-slate-50 disabled:text-slate-400">
                <option value="" disabled>Pilih Kelurahan/Desa</option>
                <option v-for="vill in villages" :key="vill.code" :value="vill.code">{{ vill.name }}</option>
            </select>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-[1fr_120px] gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
            <input v-model="form.alamat_lengkap" type="text" placeholder="Jl. M. Yamin No. 45, RT 12" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 transition" required />
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Pos</label>
            <input v-model="form.postal_code" type="text" placeholder="50123" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 transition" />
        </div>
    </div>

    <!-- PILIH TITIK KOORDINAT DI PETA -->
    <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">
            Titik Lokasi di Peta <span class="text-rose-500">*</span>
        </label>
        <p class="text-[10px] text-slate-400 mb-2">
            Cari alamat di kolom bawah, gunakan lokasi GPS Anda, atau klik/geser pin langsung di peta untuk menandai titik properti secara presisi.
        </p>

        <!-- Search bar + tombol GPS -->
        <div class="flex gap-2 mb-2">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-[11px]"></i>
                <input
                    v-model="cariAlamatInput"
                    @keyup.enter.prevent="cariLokasiDiPeta"
                    type="text"
                    placeholder="Cari nama jalan / area di peta..."
                    class="w-full text-xs pl-8 pr-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540]"
                />
            </div>
            <button
                type="button" @click.prevent="cariLokasiDiPeta" :disabled="isSearchingAlamat"
                class="bg-[#0A2540] text-white text-xs font-bold px-4 rounded-xl hover:bg-[#123e6b] transition cursor-pointer shrink-0 disabled:opacity-50"
            >
                {{ isSearchingAlamat ? 'Mencari...' : 'Cari' }}
            </button>
            <button
                type="button" @click.prevent="gunakanLokasiSaatIni" :disabled="isLocatingGPS"
                title="Gunakan Lokasi Saya Saat Ini"
                class="bg-slate-100 text-[#0A2540] text-xs font-bold px-3 rounded-xl hover:bg-slate-200 transition cursor-pointer shrink-0 disabled:opacity-50"
            >
                <i class="fa-solid fa-location-crosshairs"></i>
            </button>
        </div>

        <!-- Container Peta -->
        <div ref="mapContainer" class="w-full h-72 rounded-xl border border-slate-200 relative z-0"></div>

        <!-- Info koordinat terpilih -->
        <div class="flex items-center gap-2 mt-2 text-[11px]" :class="form.latitude ? 'text-emerald-600' : 'text-slate-400'">
            <i :class="['fa-solid', form.latitude ? 'fa-circle-check' : 'fa-circle-exclamation']"></i>
            <span v-if="form.latitude">
                Titik lokasi terpilih: {{ form.latitude }}, {{ form.longitude }}
            </span>
            <span v-else>Belum ada titik lokasi dipilih.</span>
        </div>
    </div>

</div>
</template>
