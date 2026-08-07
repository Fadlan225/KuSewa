<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

const props = defineProps({
    property: {
        type: Object,
        required: true,
    },
});

// --- KELOMPOK KATEGORI & JENIS PROPERTI (identik dengan create.vue) ---
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

const jenisPropertiDenganTipeKamar = ['Kos-kosan', 'Hotel', 'Apartemen', 'Guest House', 'Rusun / Condominium'];

const buatTipeKamarBaru = (data = {}) => ({
    id: Date.now() + Math.random(),
    nama_tipe_kamar: data.nama_tipe_kamar || '',
    jumlah_kamar: data.jumlah_kamar ?? '',
    kapasitas_orang: data.kapasitas_orang ?? '',
    fasilitas_kamar: data.fasilitas_kamar ? [...data.fasilitas_kamar] : []
});

const daftarKategoriFoto = [
    'Fasad Depan / Tampak Utama', 'Ruang Tamu', 'Kamar Tidur Utama', 'Kamar Tidur Tambahan',
    'Dapur', 'Kamar Mandi', 'Ruang Makan', 'Balkon / Rooftop', 'Halaman / Taman',
    'Carport / Garasi', 'Area Parkir', 'Ruang Kerja / Kantor', 'Ruang Rapat / Meeting Room',
    'Resepsionis / Lobby', 'Etalase / Area Display', 'Gudang / Ruang Penyimpanan',
    'Area Produksi / Pabrik', 'Loading Dock / Akses Truk', 'Denah / Site Plan',
    'Akses Jalan Masuk', 'View / Pemandangan Sekitar', 'Titik Display Baliho / Videotron', 'Lainnya'
];

// Inisialisasi tipe_kamar dari data existing
const initialTipeKamar = (props.property.tipe_kamar && props.property.tipe_kamar.length > 0)
    ? props.property.tipe_kamar.map(buatTipeKamarBaru)
    : [buatTipeKamarBaru()];

// Inisialisasi foto_properti dari data existing:
// setiap kategori punya existing_photos (path lama yang dipertahankan) + files (upload baru)
const initialFotoProperti = (props.property.foto_properti && props.property.foto_properti.length > 0)
    ? props.property.foto_properti.map(kategori => {
        const dikenal = daftarKategoriFoto.includes(kategori.nama_ruangan);
        return {
            id: Date.now() + Math.random(),
            nama_ruangan_pilihan: dikenal ? kategori.nama_ruangan : 'Lainnya',
            nama_ruangan: kategori.nama_ruangan,
            existing_photos: kategori.photos.map(p => p.path),
            existing_previews: kategori.photos.map(p => p.url),
            files: [],
            previews: []
        };
    })
    : [{
        id: Date.now(),
        nama_ruangan_pilihan: 'Fasad Depan / Tampak Utama',
        nama_ruangan: 'Fasad Depan / Tampak Utama',
        existing_photos: [],
        existing_previews: [],
        files: [],
        previews: []
    }];

const form = useForm({
    _method: 'PUT',
    nama_properti: props.property.nama_properti || '',
    kategori: props.property.kategori || 'Hunian & Tempat Tinggal',
    jenis_properti: props.property.jenis_properti || 'Kos-kosan',
    sub_kategori_baliho: props.property.sub_kategori_baliho || 'Konvensional',
    tipe_sewa: props.property.tipe_sewa || 'Bulanan',
    deskripsi: props.property.deskripsi || '',

    jumlah_kamar: props.property.jumlah_kamar ?? '',
    kapasitas_orang: props.property.kapasitas_orang ?? '',
    jumlah_lantai: props.property.jumlah_lantai ?? '',
    luas_tanah: props.property.luas_tanah ?? '',
    luas_bangunan: props.property.luas_bangunan ?? '',
    dimensi: props.property.dimensi || '',

    tipe_kamar: initialTipeKamar,

    alamat_lengkap: props.property.alamat_lengkap || '',
    latitude: props.property.latitude || '',
    longitude: props.property.longitude || '',
    province_code: props.property.province_code || '',
    city_code: props.property.city_code || '',
    district_code: props.property.district_code || '',
    village_code: props.property.village_code || '',
    postal_code: props.property.postal_code || '',
    fasilitas: props.property.fasilitas ? [...props.property.fasilitas] : [],

    harga_sewa: props.property.harga_sewa || '',
    deposit: props.property.deposit || '',
    status: props.property.status || 'Tersedia',
    tenant: props.property.tenant || '',

    foto_properti: initialFotoProperti,
});

const availableJenisProperti = computed(() => {
    const group = kategoriPropertiGroups.find(g => g.label === form.kategori);
    return group ? group.options : [];
});

const isTipeKamarProperti = computed(() => jenisPropertiDenganTipeKamar.includes(form.jenis_properti));

watch(() => form.kategori, (newKategori) => {
    const group = kategoriPropertiGroups.find(g => g.label === newKategori);
    if (group && group.options.length > 0 && !group.options.includes(form.jenis_properti)) {
        form.jenis_properti = group.options[0];
    }
});

watch(() => form.jenis_properti, (newJenis) => {
    if (jenisPropertiDenganTipeKamar.includes(newJenis) && form.tipe_kamar.length === 0) {
        form.tipe_kamar.push(buatTipeKamarBaru());
    }
});

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

const onProvinceChange = () => {
    form.city_code = '';
    form.district_code = '';
    form.village_code = '';
    cities.value = [];
    districts.value = [];
    villages.value = [];
    if (form.province_code) fetchCities(form.province_code);
};

const onCityChange = () => {
    form.district_code = '';
    form.village_code = '';
    districts.value = [];
    villages.value = [];
    if (form.city_code) fetchDistricts(form.city_code);
};

const onDistrictChange = () => {
    form.village_code = '';
    villages.value = [];
    if (form.district_code) fetchVillages(form.district_code);
};

// --- PETA LOKASI (LEAFLET) ---
const mapContainer = ref(null);
const cariAlamatInput = ref('');
const isSearchingAlamat = ref(false);
const isLocatingGPS = ref(false);
let mapInstance = null;
let markerInstance = null;

const DEFAULT_LAT = -0.5021;
const DEFAULT_LNG = 117.1536;
const DEFAULT_ZOOM = 12;

const initMap = () => {
    if (!mapContainer.value) return;

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

    mapInstance.on('click', (e) => pasangMarker(e.latlng.lat, e.latlng.lng));
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

// --- FASILITAS (identik dengan create.vue) ---
const fasilitasByKategori = [
    {
        kategori: 'Hunian & Kelengkapan Kamar', icon: 'fa-bed',
        items: [
            'Wi-Fi / Internet', 'AC (Pendingin)', 'Kamar Mandi Dalam', 'Furnished Lengkap',
            'Kasur & Lemari', 'Meja & Kursi Belajar', 'Kulkas', 'Water Heater / Air Panas',
            'Dispenser', 'Setrika & Meja Setrika', 'Jemuran Pakaian', 'Balkon / Rooftop',
            'Ruang Tamu Bersama', 'TV Kabel / Smart TV', 'Kitchen Set', 'Kompor / Kitchen Gas',
            'Dapur Bersama / Pribadi', 'Ruang Makan', 'Gudang Penyimpanan',
            'Carport / Garasi Mobil', 'Taman / Halaman'
        ]
    },
    {
        kategori: 'Keamanan & Fasilitas Umum', icon: 'fa-shield-halved',
        items: [
            'Keamanan / CCTV 24 Jam', 'Termasuk Listrik & Air', 'Area Parkir Luas', 'Akses 24 Jam',
            'Kolam Renang', 'Bebas Banjir', 'Laundry / Mesin Cuci', 'Genset / Listrik Cadangan',
            'Lift / Elevator', 'Gym / Pusat Kebugaran', 'Musholla', 'Tempat Ibadah',
            'Resepsionis 24 Jam', 'Petugas Kebersihan Rutin', 'Pest Control / Anti Hama',
            'Akses Kartu / Fingerprint', 'Pagar Keliling', 'Palang Pintu Otomatis',
            'Toilet Umum', 'Ruang Tunggu / Lobby', 'Area Merokok', 'Area Bermain Anak'
        ]
    },
    {
        kategori: 'Lokasi & Lingkungan', icon: 'fa-location-dot',
        items: [
            'Dekat Sekolah / Kampus', 'Dekat Pusat Perbelanjaan', 'Dekat Rumah Sakit / Klinik',
            'Dekat Transportasi Umum', 'View Kota / Pemandangan', 'Drainase Baik',
            'Dekat Jalan Utama / Akses Mudah'
        ]
    },
    {
        kategori: 'Komersial & Perkantoran', icon: 'fa-briefcase',
        items: [
            'Ruang Rapat / Meeting Room', 'Panel Listrik 3 Phase', 'Pantry / Dapur Kantor',
            'Meja Resepsionis', 'Signage / Papan Nama Toko', 'Etalase Kaca',
            'Ruang Ganti / Fitting Room', 'AC Sentral'
        ]
    },
    {
        kategori: 'Gudang, Pabrik & Industri', icon: 'fa-warehouse',
        items: [
            'Loading Dock / Akses Truk Besar', 'Akses Jalan Lebar (Muat Kontainer)',
            'Listrik Daya Besar (Industri)', 'Ruang Server / Data Center Ready',
            'Crane / Alat Angkat Barang', 'Lantai Kuat (Heavy Duty Floor)', 'Ventilasi Industri',
            'Instalasi Pemadam Kebakaran', 'Rak Penyimpanan / Racking System',
            'Ruang Kantor di Dalam Gudang'
        ]
    },
    {
        kategori: 'Tanah & Lahan', icon: 'fa-mountain-sun',
        items: [
            'Sertifikat Lahan (SHM/HGB)', 'Sumber Air Bersih (Sumur Bor)', 'Akses Irigasi',
            'Kontur Tanah Rata', 'Bebas Sengketa', 'Dekat Jalan Raya'
        ]
    },
    {
        kategori: 'Media Iklan & Baliho', icon: 'fa-bullhorn',
        items: [
            'Rangka Baja / Tiang Kokoh (Baliho)', 'Pencahayaan Sorot / Spotlight (Baliho)',
            'Titik Strategis / High Traffic', 'Ukuran Custom (Baliho)',
            'Perawatan & Maintenance Rutin (Baliho)'
        ]
    }
];

const semuaFasilitasStandar = fasilitasByKategori.flatMap(k => k.items);
const inputFasilitasKustom = ref('');

const toggleFasilitas = (id) => {
    const index = form.fasilitas.indexOf(id);
    if (index === -1) form.fasilitas.push(id);
    else form.fasilitas.splice(index, 1);
};

const tambahFasilitasKustom = () => {
    const raw = inputFasilitasKustom.value.trim();
    if (!raw) return;
    const nilaiFinal = semuaFasilitasStandar.find(f => f.toLowerCase() === raw.toLowerCase()) || raw;
    if (!form.fasilitas.includes(nilaiFinal)) form.fasilitas.push(nilaiFinal);
    inputFasilitasKustom.value = '';
};

const hapusFasilitas = (fas) => {
    const index = form.fasilitas.indexOf(fas);
    if (index > -1) form.fasilitas.splice(index, 1);
};

const tambahTipeKamar = () => form.tipe_kamar.push(buatTipeKamarBaru());
const hapusTipeKamar = (index) => form.tipe_kamar.splice(index, 1);

const toggleFasilitasKamar = (tIndex, item) => {
    const list = form.tipe_kamar[tIndex].fasilitas_kamar;
    const idx = list.indexOf(item);
    if (idx === -1) list.push(item);
    else list.splice(idx, 1);
};

const fasilitasKamarDropdownOpen = ref(null);
const toggleFasilitasKamarDropdown = (index) => {
    fasilitasKamarDropdownOpen.value = fasilitasKamarDropdownOpen.value === index ? null : index;
};

const fasilitasDropdownOpen = ref(false);
const activeFasilitasKategori = ref(null);
const fasilitasDropdownRef = ref(null);

const toggleFasilitasDropdown = () => {
    fasilitasDropdownOpen.value = !fasilitasDropdownOpen.value;
    if (!fasilitasDropdownOpen.value) activeFasilitasKategori.value = null;
};

const toggleSubKategoriFasilitas = (kategori) => {
    activeFasilitasKategori.value = activeFasilitasKategori.value === kategori ? null : kategori;
};

const countFasilitasTerpilih = (grup) => grup.items.filter(item => form.fasilitas.includes(item)).length;

const handleClickOutsideFasilitas = (e) => {
    if (fasilitasDropdownRef.value && !fasilitasDropdownRef.value.contains(e.target)) {
        fasilitasDropdownOpen.value = false;
        activeFasilitasKategori.value = null;
    }
};

const handleClickOutsideFasilitasKamar = (e) => {
    if (!e.target.closest('.kamar-fasilitas-dropdown')) {
        fasilitasKamarDropdownOpen.value = null;
    }
};

onMounted(async () => {
    window.addEventListener('click', handleClickOutsideFasilitas);
    window.addEventListener('click', handleClickOutsideFasilitasKamar);
    await fetchProvinces();
    if (form.province_code) await fetchCities(form.province_code);
    if (form.city_code) await fetchDistricts(form.city_code);
    if (form.district_code) await fetchVillages(form.district_code);
    await nextTick();
    initMap();
});

onBeforeUnmount(() => {
    if (mapInstance) mapInstance.remove();
    window.removeEventListener('click', handleClickOutsideFasilitas);
    window.removeEventListener('click', handleClickOutsideFasilitasKamar);
});

// --- FOTO PER KATEGORI ---
const tambahKategoriFoto = () => {
    form.foto_properti.push({
        id: Date.now(),
        nama_ruangan_pilihan: '',
        nama_ruangan: '',
        existing_photos: [],
        existing_previews: [],
        files: [],
        previews: []
    });
};

const hapusKategoriFoto = (index) => {
    form.foto_properti[index].previews.forEach(url => URL.revokeObjectURL(url));
    form.foto_properti.splice(index, 1);
};

const pilihKategoriFoto = (index) => {
    const item = form.foto_properti[index];
    if (item.nama_ruangan_pilihan === 'Lainnya') {
        item.nama_ruangan = '';
    } else {
        item.nama_ruangan = item.nama_ruangan_pilihan;
    }
};

const handleFileUpload = (event, index) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        form.foto_properti[index].files.push(file);
        form.foto_properti[index].previews.push(URL.createObjectURL(file));
    });
    event.target.value = null;
};

const hapusFotoBaru = (catIndex, fileIndex) => {
    URL.revokeObjectURL(form.foto_properti[catIndex].previews[fileIndex]);
    form.foto_properti[catIndex].files.splice(fileIndex, 1);
    form.foto_properti[catIndex].previews.splice(fileIndex, 1);
};

// Menghapus foto LAMA (yang sudah tersimpan di server) dari daftar yang dipertahankan.
// Foto ini baru benar-benar dihapus dari storage setelah form disubmit.
const hapusFotoLama = (catIndex, photoIndex) => {
    form.foto_properti[catIndex].existing_photos.splice(photoIndex, 1);
    form.foto_properti[catIndex].existing_previews.splice(photoIndex, 1);
};

// --- SUBMIT ---
const submit = () => {
    if (!form.latitude || !form.longitude) {
        alert('Mohon tentukan titik lokasi properti Anda di peta terlebih dahulu.');
        return;
    }

    form.post(route('owner.asset.update', props.property.id), {
        preserveScroll: true,
        onError: (errors) => {
            console.error('Validasi Gagal:', errors);
        }
    });
};
</script>

<template>
    <Head :title="`Edit ${form.nama_properti || 'Properti'} - kusewa.id`" />

    <div class="min-h-screen bg-[#F3F5F8] text-slate-700 font-sans flex flex-col antialiased selection:bg-[#FFC000]/30">

        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <Link href="/owner/property" class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center text-xs hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </Link>
                <div class="flex items-center gap-1.5">
                    <span class="font-black text-xl tracking-tight text-[#0A2540]">
                        kusewa<span class="text-[#FFC000]">.id</span>
                    </span>
                    <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-md ml-2">Edit Properti</span>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 space-y-6">

            <div>
                <h1 class="text-xl font-bold text-slate-900">Edit Detail Properti</h1>
                <p class="text-xs text-slate-500 mt-1">Perbarui data aset Anda. Setelah disimpan, perubahan akan diverifikasi ulang oleh admin sebelum tampil kembali ke publik.</p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex items-start gap-2.5 text-xs text-amber-800">
                <i class="fa-solid fa-circle-info mt-0.5"></i>
                <span>Properti ini akan berstatus <b>Menunggu Verifikasi</b> lagi setelah perubahan disimpan, sampai admin menyetujuinya.</span>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-8">

                <!-- INFORMASI UTAMA -->
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-[#0A2540]"></i>
                        <span>Informasi Dasar Aset</span>
                    </h2>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Properti / Unit <span class="text-rose-500">*</span></label>
                        <input v-model="form.nama_properti" type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition" required />
                        <span v-if="form.errors.nama_properti" class="text-[10px] text-rose-500 mt-1 block">{{ form.errors.nama_properti }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Utama <span class="text-rose-500">*</span></label>
                            <select v-model="form.kategori" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option v-for="group in kategoriPropertiGroups" :key="group.label" :value="group.label">{{ group.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Properti <span class="text-rose-500">*</span></label>
                            <select v-model="form.jenis_properti" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option v-for="jenis in availableJenisProperti" :key="jenis" :value="jenis">{{ jenis }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Skema Pembayaran <span class="text-rose-500">*</span></label>
                            <select v-model="form.tipe_sewa" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option value="Harian">Harian</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Tahunan">Tahunan</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-4">
                        <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-sliders text-[#0A2540]"></i>
                            Detail Spesifik: {{ form.jenis_properti }}
                        </h3>

                        <div v-if="isTipeKamarProperti" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold text-slate-700">Tipe Kamar / Unit <span class="text-rose-500">*</span></label>
                                <button type="button" @click="tambahTipeKamar" class="text-[10px] font-bold text-[#0A2540] hover:text-[#FFC000] bg-white border border-slate-200 px-2.5 py-1 rounded-lg transition cursor-pointer">
                                    + Tambah Tipe Kamar
                                </button>
                            </div>

                            <div v-for="(tipe, tIndex) in form.tipe_kamar" :key="tipe.id" class="bg-white border border-slate-200 rounded-xl p-3.5 relative space-y-3">
                                <button v-if="form.tipe_kamar.length > 1" type="button" @click.prevent="hapusTipeKamar(tIndex)"
                                    class="absolute top-3 right-3 w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-[10px] cursor-pointer">
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pr-8">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Nama Tipe Kamar <span class="text-rose-500">*</span></label>
                                        <input v-model="tipe.nama_tipe_kamar" type="text" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Jumlah Unit</label>
                                        <input v-model="tipe.jumlah_kamar" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Kapasitas Orang / Kamar</label>
                                        <input v-model="tipe.kapasitas_orang" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-1.5">Fasilitas Tipe Kamar Ini</label>
                                    <div class="relative kamar-fasilitas-dropdown">
                                        <button type="button" @click.stop="toggleFasilitasKamarDropdown(tIndex)"
                                            class="w-full text-[11px] px-3 py-2 rounded-xl border border-slate-200 flex items-center justify-between hover:border-[#0A2540] transition cursor-pointer bg-white">
                                            <span :class="tipe.fasilitas_kamar.length > 0 ? 'font-bold text-[#0A2540]' : 'text-slate-400'">
                                                {{ tipe.fasilitas_kamar.length > 0 ? `${tipe.fasilitas_kamar.length} fasilitas dipilih` : 'Pilih Fasilitas Kamar' }}
                                            </span>
                                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition" :class="{ 'rotate-180': fasilitasKamarDropdownOpen === tIndex }"></i>
                                        </button>
                                        <div v-if="fasilitasKamarDropdownOpen === tIndex" class="absolute z-20 mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                                            <button type="button" v-for="item in fasilitasByKategori[0].items" :key="item" @click.stop="toggleFasilitasKamar(tIndex, item)"
                                                class="w-full flex items-center gap-2.5 px-3 py-2 text-[11px] text-left hover:bg-slate-50 transition cursor-pointer">
                                                <span class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition" :class="tipe.fasilitas_kamar.includes(item) ? 'bg-[#0A2540] border-[#0A2540]' : 'border-slate-300 bg-white'">
                                                    <i v-if="tipe.fasilitas_kamar.includes(item)" class="fa-solid fa-check text-[9px] text-white"></i>
                                                </span>
                                                <span :class="tipe.fasilitas_kamar.includes(item) ? 'font-bold text-[#0A2540]' : 'text-slate-600'">{{ item }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="tipe.fasilitas_kamar.length > 0" class="flex flex-wrap gap-1.5 mt-2">
                                        <div v-for="item in tipe.fasilitas_kamar" :key="item" class="inline-flex items-center gap-1 bg-[#0A2540]/5 border border-[#0A2540]/20 text-[#0A2540] text-[10px] font-bold px-2 py-1 rounded-lg">
                                            <span>{{ item }}</span>
                                            <button type="button" @click.stop="toggleFasilitasKamar(tIndex, item)" class="text-rose-500 hover:text-rose-700 cursor-pointer">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <template v-if="['Rumah Tapak', 'Villa', 'Homestay'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Kamar</label>
                                    <input v-model="form.jumlah_kamar" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kapasitas Maks Orang</label>
                                    <input v-model="form.kapasitas_orang" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Lantai</label>
                                    <input v-model="form.jumlah_lantai" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>

                            <template v-if="['Ruko (Rumah Toko)', 'Kios / Lapak Pasar', 'Kantor / Workspace', 'Gedung Komersial', 'Food Court / Booth'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Bangunan (m²)</label>
                                    <input v-model="form.luas_bangunan" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Lantai</label>
                                    <input v-model="form.jumlah_lantai" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>

                            <template v-if="['Gudang Logistik', 'Pabrik / Manufaktur', 'Cold Storage'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Tanah (m²)</label>
                                    <input v-model="form.luas_tanah" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Bangunan (m²)</label>
                                    <input v-model="form.luas_bangunan" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>

                            <template v-if="['Lahan / Tanah Kosong', 'Lahan Pertanian / Perkebunan'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Tanah (m² / Hektar)</label>
                                    <input v-model="form.luas_tanah" type="number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>

                            <template v-if="['Baliho / Reklame', 'Billboard / Videotron', 'Neon Box / Titik Toko'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Tampilan Baliho <span class="text-rose-500">*</span></label>
                                    <select v-model="form.sub_kategori_baliho" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                        <option value="Konvensional">Konvensional (Cetak Spanduk/Banner Fisik)</option>
                                        <option value="Elektronik">Elektronik (Ditampilkan via Videotron/Digital LED)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Dimensi / Ukuran Layar (m)</label>
                                    <input v-model="form.dimensi" type="text" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Properti</label>
                        <textarea v-model="form.deskripsi" rows="4" class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"></textarea>
                    </div>
                </div>

                <!-- LOKASI & FASILITAS -->
                <div class="space-y-4 pt-2 border-t border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800 pb-1 pt-4 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-[#0A2540]"></i>
                        <span>Detail Lokasi & Fasilitas</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi <span class="text-rose-500">*</span></label>
                            <select v-model="form.province_code" @change="onProvinceChange" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option value="" disabled>Pilih Provinsi</option>
                                <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                            <select v-model="form.city_code" @change="onCityChange" :disabled="!cities.length" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition disabled:bg-slate-50 disabled:text-slate-400">
                                <option value="" disabled>Pilih Kota</option>
                                <option v-for="city in cities" :key="city.code" :value="city.code">{{ city.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan <span class="text-rose-500">*</span></label>
                            <select v-model="form.district_code" @change="onDistrictChange" :disabled="!districts.length" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition disabled:bg-slate-50 disabled:text-slate-400">
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
                            <input v-model="form.alamat_lengkap" type="text" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 transition" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Pos</label>
                            <input v-model="form.postal_code" type="text" placeholder="50123" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Titik Lokasi di Peta <span class="text-rose-500">*</span></label>
                        <div class="flex gap-2 mb-2">
                            <div class="flex-1 relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-[11px]"></i>
                                <input v-model="cariAlamatInput" @keyup.enter.prevent="cariLokasiDiPeta" type="text" placeholder="Cari nama jalan / area di peta..."
                                    class="w-full text-xs pl-8 pr-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540]" />
                            </div>
                            <button type="button" @click.prevent="cariLokasiDiPeta" :disabled="isSearchingAlamat"
                                class="bg-[#0A2540] text-white text-xs font-bold px-4 rounded-xl hover:bg-[#123e6b] transition cursor-pointer shrink-0 disabled:opacity-50">
                                {{ isSearchingAlamat ? 'Mencari...' : 'Cari' }}
                            </button>
                            <button type="button" @click.prevent="gunakanLokasiSaatIni" :disabled="isLocatingGPS" title="Gunakan Lokasi Saya Saat Ini"
                                class="bg-slate-100 text-[#0A2540] text-xs font-bold px-3 rounded-xl hover:bg-slate-200 transition cursor-pointer shrink-0 disabled:opacity-50">
                                <i class="fa-solid fa-location-crosshairs"></i>
                            </button>
                        </div>
                        <div ref="mapContainer" class="w-full h-72 rounded-xl border border-slate-200 z-0"></div>
                        <div class="flex items-center gap-2 mt-2 text-[11px]" :class="form.latitude ? 'text-emerald-600' : 'text-slate-400'">
                            <i :class="['fa-solid', form.latitude ? 'fa-circle-check' : 'fa-circle-exclamation']"></i>
                            <span v-if="form.latitude">Titik lokasi terpilih: {{ form.latitude }}, {{ form.longitude }}</span>
                            <span v-else>Belum ada titik lokasi dipilih.</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fasilitas / Kelengkapan Aset</label>
                        <div class="relative" ref="fasilitasDropdownRef">
                            <button type="button" @click.stop="toggleFasilitasDropdown"
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 flex items-center justify-between hover:border-[#0A2540] transition cursor-pointer bg-white">
                                <span :class="form.fasilitas.length > 0 ? 'font-bold text-[#0A2540]' : 'text-slate-400'">
                                    {{ form.fasilitas.length > 0 ? `${form.fasilitas.length} fasilitas dipilih` : 'Pilih Fasilitas' }}
                                </span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition" :class="{ 'rotate-180': fasilitasDropdownOpen }"></i>
                            </button>

                            <div v-if="fasilitasDropdownOpen" class="absolute z-30 mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-80 overflow-y-auto">
                                <div v-for="grup in fasilitasByKategori" :key="grup.kategori" class="border-b border-slate-100 last:border-0">
                                    <button type="button" @click.stop="toggleSubKategoriFasilitas(grup.kategori)"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                                        <span class="flex items-center gap-2">
                                            <i :class="['fa-solid', grup.icon, 'text-[#FFC000] text-[11px] w-3.5 text-center']"></i>
                                            <span>{{ grup.kategori }}</span>
                                            <span v-if="countFasilitasTerpilih(grup) > 0" class="text-[10px] bg-[#0A2540] text-white font-bold px-1.5 py-0.5 rounded-full leading-none">
                                                {{ countFasilitasTerpilih(grup) }}
                                            </span>
                                        </span>
                                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 transition" :class="{ 'rotate-90 text-[#0A2540]': activeFasilitasKategori === grup.kategori }"></i>
                                    </button>
                                    <div v-if="activeFasilitasKategori === grup.kategori" class="bg-slate-50 border-t border-slate-100">
                                        <button type="button" v-for="item in grup.items" :key="item" @click.stop="toggleFasilitas(item)"
                                            class="w-full flex items-center gap-2.5 pl-8 pr-3.5 py-2 text-[11px] text-left hover:bg-white transition cursor-pointer">
                                            <span class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition" :class="form.fasilitas.includes(item) ? 'bg-[#0A2540] border-[#0A2540]' : 'border-slate-300 bg-white'">
                                                <i v-if="form.fasilitas.includes(item)" class="fa-solid fa-check text-[9px] text-white"></i>
                                            </span>
                                            <span :class="form.fasilitas.includes(item) ? 'font-bold text-[#0A2540]' : 'text-slate-600'">{{ item }}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-2.5 bg-slate-50 border-t border-slate-100">
                                    <div class="flex gap-1.5">
                                        <input v-model="inputFasilitasKustom" @keyup.enter.prevent="tambahFasilitasKustom" @click.stop type="text"
                                            placeholder="Fasilitas tidak ada di daftar? Ketik di sini..."
                                            class="flex-1 text-[11px] px-2.5 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#0A2540] transition" />
                                        <button type="button" @click.stop="tambahFasilitasKustom"
                                            class="bg-[#0A2540] text-white text-[11px] font-bold px-3 rounded-lg hover:bg-[#123e6b] transition cursor-pointer shrink-0">
                                            Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <div v-if="form.fasilitas.length > 0" class="flex flex-wrap gap-2">
                                <div v-for="fas in form.fasilitas" :key="fas" class="inline-flex items-center gap-1.5 bg-[#0A2540]/5 border border-[#0A2540]/20 text-[#0A2540] text-[11px] font-bold px-2.5 py-1 rounded-lg">
                                    <span>{{ fas }}</span>
                                    <button type="button" @click.prevent="hapusFasilitas(fas)" class="text-rose-500 hover:text-rose-700 focus:outline-none cursor-pointer">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-[10px] text-slate-400">Belum ada fasilitas dipilih.</p>
                        </div>
                    </div>
                </div>

                <!-- STATUS & HARGA -->
                <div class="space-y-4 pt-2 border-t border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800 pb-1 pt-4 flex items-center gap-2">
                        <i class="fa-solid fa-tags text-[#0A2540]"></i>
                        <span>Harga & Status Penyewaan</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Sewa (Rp) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input v-model="form.harga_sewa" type="number" class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 transition" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Uang Deposit (Opsional)</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input v-model="form.deposit" type="number" class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 transition" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status Keterisian <span class="text-rose-500">*</span></label>
                            <select v-model="form.status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition font-semibold">
                                <option value="Tersedia">Tersedia (Kosong)</option>
                                <option value="Tersewa">Tersewa</option>
                                <option value="Maintenance">Dalam Perbaikan</option>
                            </select>
                        </div>
                        <div v-if="form.status === 'Tersewa'" class="sm:col-span-3">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penyewa Saat Ini</label>
                            <input v-model="form.tenant" type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition" />
                        </div>
                    </div>
                </div>

                <!-- FOTO PER KATEGORI -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-3 pt-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Foto Berdasarkan Kategori Ruangan / Sudut Pandang</label>
                            <p class="text-[10px] text-slate-400">Hapus foto lama yang tidak relevan, atau tambahkan foto baru.</p>
                        </div>
                        <button type="button" @click="tambahKategoriFoto" class="text-[11px] font-bold text-[#0A2540] hover:text-[#FFC000] bg-slate-100 px-3 py-1.5 rounded-lg transition shrink-0 cursor-pointer">
                            + Tambah Kategori Ruangan
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(kategori, index) in form.foto_properti" :key="kategori.id" class="border border-slate-200 rounded-2xl p-4 relative group bg-white shadow-xs">
                            <button v-if="form.foto_properti.length > 1" @click.prevent="hapusKategoriFoto(index)"
                                class="absolute top-3 right-3 w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-xs cursor-pointer" title="Hapus Kategori">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Kategori Ruangan / Area</label>
                                    <select v-model="kategori.nama_ruangan_pilihan" @change="pilihKategoriFoto(index)"
                                        class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" required>
                                        <option value="" disabled>Pilih kategori ruangan/area</option>
                                        <option v-for="opt in daftarKategoriFoto" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>
                                    <input v-if="kategori.nama_ruangan_pilihan === 'Lainnya'" v-model="kategori.nama_ruangan" type="text"
                                        placeholder="Tulis nama kategori ruangan/area"
                                        class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition mt-2" required />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Tambah Foto Baru (Bisa Banyak Sekaligus)</label>
                                    <input type="file" multiple accept="image/png, image/jpeg, image/webp" @change="handleFileUpload($event, index)"
                                        class="w-full text-[11px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-[#0A2540] file:text-white hover:file:bg-[#123e6b] cursor-pointer" />
                                </div>
                            </div>

                            <!-- FOTO LAMA (existing) -->
                            <div v-if="kategori.existing_previews.length > 0" class="mb-2">
                                <p class="text-[10px] font-bold text-slate-500 mb-1.5">Foto Tersimpan</p>
                                <div class="flex flex-wrap gap-2 pb-2 border-b border-slate-100">
                                    <div v-for="(preview, photoIndex) in kategori.existing_previews" :key="'lama-' + photoIndex" class="relative group/photo">
                                        <img :src="preview" class="w-16 h-16 object-cover rounded-xl border border-slate-200 shadow-xs" />
                                        <button @click.prevent="hapusFotoLama(index, photoIndex)"
                                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-xs opacity-0 group-hover/photo:opacity-100 transition cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- FOTO BARU (belum tersimpan) -->
                            <div v-if="kategori.previews.length > 0" class="pt-2">
                                <p class="text-[10px] font-bold text-emerald-600 mb-1.5">Foto Baru</p>
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="(preview, fileIndex) in kategori.previews" :key="'baru-' + fileIndex" class="relative group/photo">
                                        <img :src="preview" class="w-16 h-16 object-cover rounded-xl border border-emerald-300 shadow-xs" />
                                        <button @click.prevent="hapusFotoBaru(index, fileIndex)"
                                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-xs opacity-0 group-hover/photo:opacity-100 transition cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="kategori.existing_previews.length === 0 && kategori.previews.length === 0" class="text-[10px] text-slate-400">
                                Belum ada foto untuk kategori ini.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <Link href="/owner/property" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-[#0A2540] hover:bg-[#14385f] active:scale-95 text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition text-xs flex items-center gap-2 disabled:opacity-50">
                        <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                        <i v-else class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>

            </form>
        </main>
    </div>
</template>