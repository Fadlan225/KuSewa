<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import Step1 from './Step1.vue';
import Step2 from './Step2.vue';
import Step3 from './Step3.vue';

// Fix bug ikon marker default Leaflet yang tidak muncul di build Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

// --- KELOMPOK KATEGORI & JENIS PROPERTI ---
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

// Jenis properti yang biasanya punya BANYAK VARIAN KAMAR berbeda
// (mis. Standard, Deluxe, Suite) dengan jumlah unit & fasilitas masing-masing berbeda.
// Untuk jenis ini, owner mengisi "Tipe Kamar" alih-alih satu angka jumlah kamar saja.
const jenisPropertiDenganTipeKamar = ['Kos-kosan', 'Hotel', 'Apartemen', 'Guest House', 'Rusun / Condominium'];

const buatTipeKamarBaru = () => ({
    id: Date.now() + Math.random(),
    nama_tipe_kamar: '',
    jumlah_kamar: '',
    kapasitas_orang: '',
    fasilitas_kamar: []
});

// Form Data State menggunakan Inertia useForm
const form = useForm({
    // Step 1: Informasi Dasar
    nama_properti: '',
    kategori: 'Hunian & Tempat Tinggal', // Default Kategori Utama
    jenis_properti: 'Kos-kosan', // Default Jenis Properti
    sub_kategori_baliho: 'Konvensional', // Khusus baliho
    tipe_sewa: 'Bulanan',
    deskripsi: '',

    // Field Dinamis (Step 1)
    jumlah_kamar: '',
    kapasitas_orang: '',
    jumlah_lantai: '',
    luas_tanah: '',
    luas_bangunan: '',
    dimensi: '',
    tahun_dibangun: '',
    daya_listrik: '',
    sumber_air: 'PDAM',
    kapasitas_parkir: '',
    pemandangan: 'Kota',
    bintang: '3',
    waktu_checkin: '14:00',
    waktu_checkout: '12:00',
    aturan_jam_malam: false,
    parkir_motor: false,
    kamar_mandi_dalam: false,
    tinggi_plafon: '',
    sertifikat: 'SHM',
    kontur_tanah: 'Datar',
    sisi_baliho: '1',
    orientasi_baliho: 'Horizontal',
    penerangan_baliho: false,
    resolusi_layar: '',

    // Khusus jenis properti dengan banyak varian kamar (Kos-kosan, Hotel, Apartemen, Guest House, Rusun)
    tipe_kamar: [buatTipeKamarBaru()],

    // Step 2: Lokasi & Fasilitas
    alamat_lengkap: '',
    latitude: '',
    longitude: '',
    province_code: '',
    city_code: '',
    district_code: '',
    village_code: '',
    postal_code: '',
    fasilitas: [],

    // Step 3: Harga & Foto
    harga_sewa: '',
    deposit: '',
    foto_properti: [
        {
            id: Date.now(),
            nama_ruangan_pilihan: 'Fasad Depan / Tampak Utama', // nilai dropdown
            nama_ruangan: 'Fasad Depan / Tampak Utama',         // nilai final yang dikirim
            files: [],
            previews: []
        }
    ]
});

// --- LOGIKA DROPDOWN CASCADING ---
// Mendapatkan daftar jenis properti sesuai kategori utama yang dipilih
const availableJenisProperti = computed(() => {
    const group = kategoriPropertiGroups.find(g => g.label === form.kategori);
    return group ? group.options : [];
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

// True jika jenis properti yang sedang dipilih perlu diisi lewat builder "Tipe Kamar"
const isTipeKamarProperti = computed(() => jenisPropertiDenganTipeKamar.includes(form.jenis_properti));

// Otomatis ubah nilai 'jenis_properti' ke opsi pertama tiap kali 'kategori' diubah
watch(() => form.kategori, (newKategori) => {
    const group = kategoriPropertiGroups.find(g => g.label === newKategori);
    if (group && group.options.length > 0) {
        form.jenis_properti = group.options[0];
    }
});

// Pastikan selalu ada minimal 1 baris "Tipe Kamar" saat owner pindah ke jenis properti
// yang membutuhkan builder tipe kamar (Kos-kosan, Hotel, Apartemen, Guest House, Rusun)
watch(() => form.jenis_properti, (newJenis) => {
    if (jenisPropertiDenganTipeKamar.includes(newJenis) && form.tipe_kamar.length === 0) {
        form.tipe_kamar.push(buatTipeKamarBaru());
    }
});

// Navigation & Modal State
const currentStep = ref(1);
const showSuccessModal = ref(false);

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
watch(currentStep, async (step) => {
    if (step === 2) {
        await nextTick();
        initMap();
    }
});

// --- LOGIKA FASILITAS ---
const fasilitasByKategori = [
    {
        kategori: 'Hunian & Kelengkapan Kamar',
        icon: 'fa-bed',
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
        kategori: 'Keamanan & Fasilitas Umum',
        icon: 'fa-shield-halved',
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
        kategori: 'Lokasi & Lingkungan',
        icon: 'fa-location-dot',
        items: [
            'Dekat Sekolah / Kampus', 'Dekat Pusat Perbelanjaan', 'Dekat Rumah Sakit / Klinik',
            'Dekat Transportasi Umum', 'View Kota / Pemandangan', 'Drainase Baik',
            'Dekat Jalan Utama / Akses Mudah'
        ]
    },
    {
        kategori: 'Komersial & Perkantoran',
        icon: 'fa-briefcase',
        items: [
            'Ruang Rapat / Meeting Room', 'Panel Listrik 3 Phase', 'Pantry / Dapur Kantor',
            'Meja Resepsionis', 'Signage / Papan Nama Toko', 'Etalase Kaca',
            'Ruang Ganti / Fitting Room', 'AC Sentral'
        ]
    },
    {
        kategori: 'Gudang, Pabrik & Industri',
        icon: 'fa-warehouse',
        items: [
            'Loading Dock / Akses Truk Besar', 'Akses Jalan Lebar (Muat Kontainer)',
            'Listrik Daya Besar (Industri)', 'Ruang Server / Data Center Ready',
            'Crane / Alat Angkat Barang', 'Lantai Kuat (Heavy Duty Floor)', 'Ventilasi Industri',
            'Instalasi Pemadam Kebakaran', 'Rak Penyimpanan / Racking System',
            'Ruang Kantor di Dalam Gudang'
        ]
    },
    {
        kategori: 'Tanah & Lahan',
        icon: 'fa-mountain-sun',
        items: [
            'Sertifikat Lahan (SHM/HGB)', 'Sumber Air Bersih (Sumur Bor)', 'Akses Irigasi',
            'Kontur Tanah Rata', 'Bebas Sengketa', 'Dekat Jalan Raya'
        ]
    },
    {
        kategori: 'Media Iklan & Baliho',
        icon: 'fa-bullhorn',
        items: [
            'Rangka Baja / Tiang Kokoh (Baliho)', 'Pencahayaan Sorot / Spotlight (Baliho)',
            'Titik Strategis / High Traffic', 'Ukuran Custom (Baliho)',
            'Perawatan & Maintenance Rutin (Baliho)'
        ]
    }
];

const semuaFasilitasStandar = fasilitasByKategori.flatMap(k => k.items);

const aliasFasilitas = {
    'wifi': 'Wi-Fi / Internet',
    'wi fi': 'Wi-Fi / Internet',
    'wi-fi': 'Wi-Fi / Internet',
    'internet': 'Wi-Fi / Internet',
    'jaringan internet': 'Wi-Fi / Internet',
    'ac': 'AC (Pendingin)',
    'ac dingin': 'AC (Pendingin)',
    'pendingin ruangan': 'AC (Pendingin)',
    'pendingin udara': 'AC (Pendingin)',
    'ac sentral': 'AC Sentral',
    'central ac': 'AC Sentral',
    'parkir': 'Area Parkir Luas',
    'parkiran': 'Area Parkir Luas',
    'tempat parkir': 'Area Parkir Luas',
    'lahan parkir': 'Area Parkir Luas',
    'carport': 'Carport / Garasi Mobil',
    'garasi': 'Carport / Garasi Mobil',
    'garasi mobil': 'Carport / Garasi Mobil',
    'kasur': 'Kasur & Lemari',
    'lemari': 'Kasur & Lemari',
    'kasur lemari': 'Kasur & Lemari',
    'meja belajar': 'Meja & Kursi Belajar',
    'meja kursi': 'Meja & Kursi Belajar',
    'kulkas': 'Kulkas',
    'lemari es': 'Kulkas',
    'water heater': 'Water Heater / Air Panas',
    'air panas': 'Water Heater / Air Panas',
    'pemanas air': 'Water Heater / Air Panas',
    'dispenser': 'Dispenser',
    'galon': 'Dispenser',
    'setrika': 'Setrika & Meja Setrika',
    'jemuran': 'Jemuran Pakaian',
    'balkon': 'Balkon / Rooftop',
    'rooftop': 'Balkon / Rooftop',
    'ruang tamu': 'Ruang Tamu Bersama',
    'tv': 'TV Kabel / Smart TV',
    'tv kabel': 'TV Kabel / Smart TV',
    'smart tv': 'TV Kabel / Smart TV',
    'televisi': 'TV Kabel / Smart TV',
    'kitchen set': 'Kitchen Set',
    'lemari dapur': 'Kitchen Set',
    'kompor': 'Kompor / Kitchen Gas',
    'kompor gas': 'Kompor / Kitchen Gas',
    'dapur': 'Dapur Bersama / Pribadi',
    'dapur bersama': 'Dapur Bersama / Pribadi',
    'ruang makan': 'Ruang Makan',
    'gudang': 'Gudang Penyimpanan',
    'ruang penyimpanan': 'Gudang Penyimpanan',
    'taman': 'Taman / Halaman',
    'halaman': 'Taman / Halaman',
    'kamar mandi dalam': 'Kamar Mandi Dalam',
    'km dalam': 'Kamar Mandi Dalam',
    'kamar mandi': 'Kamar Mandi Dalam',
    'cctv': 'Keamanan / CCTV 24 Jam',
    'keamanan': 'Keamanan / CCTV 24 Jam',
    'kamera cctv': 'Keamanan / CCTV 24 Jam',
    'satpam': 'Keamanan / CCTV 24 Jam',
    'security': 'Keamanan / CCTV 24 Jam',
    'listrik': 'Termasuk Listrik & Air',
    'listrik air': 'Termasuk Listrik & Air',
    'air': 'Termasuk Listrik & Air',
    'kolam renang': 'Kolam Renang',
    'renang': 'Kolam Renang',
    'kolam': 'Kolam Renang',
    'swimming pool': 'Kolam Renang',
    'bebas banjir': 'Bebas Banjir',
    'anti banjir': 'Bebas Banjir',
    'tidak banjir': 'Bebas Banjir',
    'akses 24 jam': 'Akses 24 Jam',
    '24 jam': 'Akses 24 Jam',
    'buka 24 jam': 'Akses 24 Jam',
    'furnished': 'Furnished Lengkap',
    'full furnished': 'Furnished Lengkap',
    'perabotan lengkap': 'Furnished Lengkap',
    'laundry': 'Laundry / Mesin Cuci',
    'mesin cuci': 'Laundry / Mesin Cuci',
    'cuci baju': 'Laundry / Mesin Cuci',
    'genset': 'Genset / Listrik Cadangan',
    'listrik cadangan': 'Genset / Listrik Cadangan',
    'generator': 'Genset / Listrik Cadangan',
    'lift': 'Lift / Elevator',
    'elevator': 'Lift / Elevator',
    'gym': 'Gym / Pusat Kebugaran',
    'fitness': 'Gym / Pusat Kebugaran',
    'pusat kebugaran': 'Gym / Pusat Kebugaran',
    'musholla': 'Musholla',
    'mushola': 'Musholla',
    'masjid': 'Tempat Ibadah',
    'tempat ibadah': 'Tempat Ibadah',
    'resepsionis': 'Resepsionis 24 Jam',
    'receptionist': 'Resepsionis 24 Jam',
    'cleaning service': 'Petugas Kebersihan Rutin',
    'petugas kebersihan': 'Petugas Kebersihan Rutin',
    'kebersihan': 'Petugas Kebersihan Rutin',
    'anti hama': 'Pest Control / Anti Hama',
    'pest control': 'Pest Control / Anti Hama',
    'fingerprint': 'Akses Kartu / Fingerprint',
    'akses kartu': 'Akses Kartu / Fingerprint',
    'kartu akses': 'Akses Kartu / Fingerprint',
    'pagar': 'Pagar Keliling',
    'pagar keliling': 'Pagar Keliling',
    'palang pintu': 'Palang Pintu Otomatis',
    'portal otomatis': 'Palang Pintu Otomatis',
    'toilet umum': 'Toilet Umum',
    'wc umum': 'Toilet Umum',
    'lobby': 'Ruang Tunggu / Lobby',
    'ruang tunggu': 'Ruang Tunggu / Lobby',
    'area merokok': 'Area Merokok',
    'smoking area': 'Area Merokok',
    'area bermain': 'Area Bermain Anak',
    'playground': 'Area Bermain Anak',
    'dekat sekolah': 'Dekat Sekolah / Kampus',
    'dekat kampus': 'Dekat Sekolah / Kampus',
    'dekat mall': 'Dekat Pusat Perbelanjaan',
    'dekat pusat perbelanjaan': 'Dekat Pusat Perbelanjaan',
    'dekat rumah sakit': 'Dekat Rumah Sakit / Klinik',
    'dekat klinik': 'Dekat Rumah Sakit / Klinik',
    'dekat halte': 'Dekat Transportasi Umum',
    'dekat angkutan umum': 'Dekat Transportasi Umum',
    'transportasi umum': 'Dekat Transportasi Umum',
    'view kota': 'View Kota / Pemandangan',
    'pemandangan': 'View Kota / Pemandangan',
    'drainase': 'Drainase Baik',
    'saluran air': 'Drainase Baik',
    'akses jalan': 'Dekat Jalan Utama / Akses Mudah',
    'jalan utama': 'Dekat Jalan Utama / Akses Mudah',
    'meeting room': 'Ruang Rapat / Meeting Room',
    'ruang rapat': 'Ruang Rapat / Meeting Room',
    'panel listrik': 'Panel Listrik 3 Phase',
    '3 phase': 'Panel Listrik 3 Phase',
    'listrik 3 phase': 'Panel Listrik 3 Phase',
    'pantry': 'Pantry / Dapur Kantor',
    'dapur kantor': 'Pantry / Dapur Kantor',
    'meja resepsionis': 'Meja Resepsionis',
    'signage': 'Signage / Papan Nama Toko',
    'papan nama': 'Signage / Papan Nama Toko',
    'etalase': 'Etalase Kaca',
    'fitting room': 'Ruang Ganti / Fitting Room',
    'ruang ganti': 'Ruang Ganti / Fitting Room',
    'loading dock': 'Loading Dock / Akses Truk Besar',
    'akses truk': 'Loading Dock / Akses Truk Besar',
    'akses kontainer': 'Akses Jalan Lebar (Muat Kontainer)',
    'listrik industri': 'Listrik Daya Besar (Industri)',
    'daya besar': 'Listrik Daya Besar (Industri)',
    'data center': 'Ruang Server / Data Center Ready',
    'ruang server': 'Ruang Server / Data Center Ready',
    'crane': 'Crane / Alat Angkat Barang',
    'alat angkat': 'Crane / Alat Angkat Barang',
    'lantai kuat': 'Lantai Kuat (Heavy Duty Floor)',
    'heavy duty': 'Lantai Kuat (Heavy Duty Floor)',
    'ventilasi': 'Ventilasi Industri',
    'pemadam kebakaran': 'Instalasi Pemadam Kebakaran',
    'apar': 'Instalasi Pemadam Kebakaran',
    'sprinkler': 'Instalasi Pemadam Kebakaran',
    'rak penyimpanan': 'Rak Penyimpanan / Racking System',
    'racking': 'Rak Penyimpanan / Racking System',
    'kantor gudang': 'Ruang Kantor di Dalam Gudang',
    'sertifikat': 'Sertifikat Lahan (SHM/HGB)',
    'shm': 'Sertifikat Lahan (SHM/HGB)',
    'hgb': 'Sertifikat Lahan (SHM/HGB)',
    'sumur bor': 'Sumber Air Bersih (Sumur Bor)',
    'air bersih': 'Sumber Air Bersih (Sumur Bor)',
    'irigasi': 'Akses Irigasi',
    'saluran irigasi': 'Akses Irigasi',
    'tanah rata': 'Kontur Tanah Rata',
    'kontur rata': 'Kontur Tanah Rata',
    'bebas sengketa': 'Bebas Sengketa',
    'tidak sengketa': 'Bebas Sengketa',
    'dekat jalan raya': 'Dekat Jalan Raya',
    'pinggir jalan raya': 'Dekat Jalan Raya',
    'tiang baliho': 'Rangka Baja / Tiang Kokoh (Baliho)',
    'rangka baja': 'Rangka Baja / Tiang Kokoh (Baliho)',
    'lampu sorot': 'Pencahayaan Sorot / Spotlight (Baliho)',
    'spotlight': 'Pencahayaan Sorot / Spotlight (Baliho)',
    'titik strategis': 'Titik Strategis / High Traffic',
    'high traffic': 'Titik Strategis / High Traffic',
    'ramai dilalui': 'Titik Strategis / High Traffic',
    'ukuran custom': 'Ukuran Custom (Baliho)',
    'ukuran sesuai permintaan': 'Ukuran Custom (Baliho)',
    'maintenance': 'Perawatan & Maintenance Rutin (Baliho)',
    'perawatan rutin': 'Perawatan & Maintenance Rutin (Baliho)'
};

const jarakLevenshtein = (a, b) => {
    const baris = Array.from({ length: a.length + 1 }, () => new Array(b.length + 1).fill(0));
    for (let i = 0; i <= a.length; i++) baris[i][0] = i;
    for (let j = 0; j <= b.length; j++) baris[0][j] = j;
    for (let i = 1; i <= a.length; i++) {
        for (let j = 1; j <= b.length; j++) {
            const biaya = a[i - 1] === b[j - 1] ? 0 : 1;
            baris[i][j] = Math.min(
                baris[i - 1][j] + 1,
                baris[i][j - 1] + 1,
                baris[i - 1][j - 1] + biaya
            );
        }
    }
    return baris[a.length][b.length];
};

const koreksiPenulisanFasilitas = (teks) => {
    const key = teks.trim().toLowerCase();
    if (!key) return teks.trim();

    if (aliasFasilitas[key]) return aliasFasilitas[key];

    const cocokPersis = semuaFasilitasStandar.find(f => f.toLowerCase() === key);
    if (cocokPersis) return cocokPersis;

    const kandidat = [
        ...semuaFasilitasStandar.map(f => ({ teks: f, nilai: f })),
        ...Object.keys(aliasFasilitas).map(a => ({ teks: a, nilai: aliasFasilitas[a] }))
    ];

    let terbaik = null;
    let skorTerbaik = 0;
    kandidat.forEach(({ teks: kandidatTeks, nilai }) => {
        const jarak = jarakLevenshtein(key, kandidatTeks.toLowerCase());
        const skor = 1 - jarak / Math.max(key.length, kandidatTeks.length);
        if (skor > skorTerbaik) {
            skorTerbaik = skor;
            terbaik = nilai;
        }
    });

    if (skorTerbaik >= 0.8) return terbaik;

    return teks.trim().replace(/\s+/g, ' ');
};

const inputFasilitasKustom = ref('');

const toggleFasilitas = (id) => {
    const index = form.fasilitas.indexOf(id);
    if (index === -1) {
        form.fasilitas.push(id);
    } else {
        form.fasilitas.splice(index, 1);
    }
};

const tambahFasilitasKustom = () => {
    const raw = inputFasilitasKustom.value.trim();
    if (!raw) return;

    const nilaiFinal = koreksiPenulisanFasilitas(raw);

    if (!form.fasilitas.includes(nilaiFinal)) {
        form.fasilitas.push(nilaiFinal);
    }
    inputFasilitasKustom.value = '';
};

const hapusFasilitas = (fas) => {
    const index = form.fasilitas.indexOf(fas);
    if (index > -1) form.fasilitas.splice(index, 1);
};

// --- LOGIKA TIPE KAMAR (khusus Kos-kosan, Hotel, Apartemen, Guest House, Rusun) ---
const tambahTipeKamar = () => {
    form.tipe_kamar.push(buatTipeKamarBaru());
};

const hapusTipeKamar = (index) => {
    form.tipe_kamar.splice(index, 1);
};

const toggleFasilitasKamar = (tIndex, item) => {
    const list = form.tipe_kamar[tIndex].fasilitas_kamar;
    const idx = list.indexOf(item);
    if (idx === -1) {
        list.push(item);
    } else {
        list.splice(idx, 1);
    }
};

// Index tipe kamar yang dropdown fasilitasnya sedang terbuka (hanya 1 yang bisa terbuka sekaligus)
const fasilitasKamarDropdownOpen = ref(null);

const toggleFasilitasKamarDropdown = (index) => {
    fasilitasKamarDropdownOpen.value = fasilitasKamarDropdownOpen.value === index ? null : index;
};

// --- LOGIKA NESTED DROPDOWN FASILITAS (dropdown di dalam dropdown) ---
// Struktur: Pilih Fasilitas -> [Jenis Fasilitas] -> [Nama Fasilitas]
const fasilitasDropdownOpen = ref(false);
const activeFasilitasKategori = ref(null);
const fasilitasDropdownRef = ref(null);

const toggleFasilitasDropdown = () => {
    fasilitasDropdownOpen.value = !fasilitasDropdownOpen.value;
    if (!fasilitasDropdownOpen.value) activeFasilitasKategori.value = null;
};

// Buka/tutup sub-dropdown nama fasilitas di dalam kategori (jenis fasilitas) yang diklik
const toggleSubKategoriFasilitas = (kategori) => {
    activeFasilitasKategori.value = activeFasilitasKategori.value === kategori ? null : kategori;
};

// Hitung berapa item sudah dipilih di tiap kategori, untuk badge kecil di label jenis fasilitas
const countFasilitasTerpilih = (grup) => {
    return grup.items.filter(item => form.fasilitas.includes(item)).length;
};

// Tutup dropdown kalau klik di luar area dropdown
const handleClickOutsideFasilitas = (e) => {
    if (fasilitasDropdownRef.value && !fasilitasDropdownRef.value.contains(e.target)) {
        fasilitasDropdownOpen.value = false;
        activeFasilitasKategori.value = null;
    }
};

// Tutup dropdown fasilitas tipe kamar kalau klik di luar area dropdown-nya
const handleClickOutsideFasilitasKamar = (e) => {
    if (!e.target.closest('.kamar-fasilitas-dropdown')) {
        fasilitasKamarDropdownOpen.value = null;
    }
};

onMounted(() => {
    fetchProvinces();
    window.addEventListener('click', handleClickOutsideFasilitas);
    window.addEventListener('click', handleClickOutsideFasilitasKamar);
});

onBeforeUnmount(() => {
    if (mapInstance) mapInstance.remove();
    window.removeEventListener('click', handleClickOutsideFasilitas);
    window.removeEventListener('click', handleClickOutsideFasilitasKamar);
});

// --- LOGIKA FOTO KATEGORI ---

// Daftar standar nama kategori ruangan/area untuk dropdown foto.
// Mencakup berbagai jenis properti (hunian, komersial, gudang, tanah, baliho)
// supaya owner tinggal pilih, tidak ketik manual -> tidak ada typo/variasi penulisan.
// "Lainnya" selalu jadi opsi terakhir untuk kasus yang belum tercakup.
const daftarKategoriFoto = [
    'Fasad Depan / Tampak Utama',
    'Ruang Tamu',
    'Kamar Tidur Utama',
    'Kamar Tidur Tambahan',
    'Dapur',
    'Kamar Mandi',
    'Ruang Makan',
    'Balkon / Rooftop',
    'Halaman / Taman',
    'Carport / Garasi',
    'Area Parkir',
    'Ruang Kerja / Kantor',
    'Ruang Rapat / Meeting Room',
    'Resepsionis / Lobby',
    'Etalase / Area Display',
    'Gudang / Ruang Penyimpanan',
    'Area Produksi / Pabrik',
    'Loading Dock / Akses Truk',
    'Denah / Site Plan',
    'Akses Jalan Masuk',
    'View / Pemandangan Sekitar',
    'Titik Display Baliho / Videotron',
    'Lainnya'
];

const tambahKategoriFoto = () => {
    form.foto_properti.push({
        id: Date.now(),
        nama_ruangan_pilihan: '',
        nama_ruangan: '',
        files: [],
        previews: []
    });
};

const hapusKategoriFoto = (index) => {
    form.foto_properti[index].previews.forEach(url => URL.revokeObjectURL(url));
    form.foto_properti.splice(index, 1);
};

// Dipanggil saat owner memilih opsi dari dropdown kategori ruangan/area.
// Jika bukan "Lainnya", nilai final langsung mengikuti pilihan dropdown (standar & konsisten).
// Jika "Lainnya", nama_ruangan dikosongkan dulu agar owner mengisi manual lewat input custom.
const pilihKategoriFoto = (index) => {
    const item = form.foto_properti[index];
    if (item.nama_ruangan_pilihan === 'Lainnya') {
        item.nama_ruangan = '';
    } else {
        item.nama_ruangan = item.nama_ruangan_pilihan;
    }
};

const handleFileUpload = (event, index) => {
    // multiple file dari satu kali pilih (Ctrl/Cmd+klik) DITAMBAHKAN ke array yang sudah ada,
    // jadi owner juga bisa klik "Choose Files" berkali-kali untuk kategori yang sama —
    // foto lama tidak akan tertimpa/hilang.
    const files = Array.from(event.target.files);
    files.forEach(file => {
        form.foto_properti[index].files.push(file);
        form.foto_properti[index].previews.push(URL.createObjectURL(file));
    });
    event.target.value = null;
};

const hapusFoto = (catIndex, fileIndex) => {
    URL.revokeObjectURL(form.foto_properti[catIndex].previews[fileIndex]);
    form.foto_properti[catIndex].files.splice(fileIndex, 1);
    form.foto_properti[catIndex].previews.splice(fileIndex, 1);
};

// --- NAVIGASI STEPPER ---
const nextStep = () => {
    if (currentStep.value < 3) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

// --- SUBMIT FORM ---
const validationErrors = ref({});
const showValidationAlert = ref(false);

const validateForm = () => {
    const errors = {};
    if (!form.nama_properti.trim()) errors.nama_properti = 'Nama properti wajib diisi';
    if (!form.kategori) errors.kategori = 'Kategori wajib dipilih';
    if (!form.jenis_properti) errors.jenis_properti = 'Jenis properti wajib dipilih';
    if (!form.tipe_sewa) errors.tipe_sewa = 'Tipe sewa wajib dipilih';
    if (!form.alamat_lengkap.trim()) errors.alamat_lengkap = 'Alamat lengkap wajib diisi';
    if (!form.province_code) errors.province_code = 'Provinsi wajib dipilih';
    if (!form.city_code) errors.city_code = 'Kota wajib dipilih';
    if (!form.district_code) errors.district_code = 'Kecamatan wajib dipilih';
    if (!form.village_code) errors.village_code = 'Kelurahan/Desa wajib dipilih';
    if (!form.harga_sewa || Number(form.harga_sewa) <= 0) errors.harga_sewa = 'Harga sewa wajib diisi (minimal > 0)';

    validationErrors.value = errors;
    return Object.keys(errors).length === 0;
};

const submitProperty = () => {
    showValidationAlert.value = false;

    // Validasi client-side
    if (!validateForm()) {
        showValidationAlert.value = true;
        currentStep.value = 1;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    // Pastikan owner sudah menandai titik koordinat sebelum data terkirim
    if (!form.latitude || !form.longitude) {
        alert('Mohon tentukan titik lokasi properti Anda di peta terlebih dahulu.');
        currentStep.value = 2;
        return;
    }

    form.post(route('owner.asset.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
        },
        onError: (errors) => {
            showValidationAlert.value = true;
            validationErrors.value = errors;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
};

const closeModalAndRedirect = () => {
    showSuccessModal.value = false;
    router.visit('/owner/property');
};
</script>

<template>
    <AppLayout hideNavbar hideBottombar>
        <Head title="Ajukan Aset Baru" />

        <DetailNavbar title="Ajukan Properti Baru" backUrl="/owner/asset" :showSections="false" :showShare="false" :showFavorite="false" />

        <div class="min-h-screen bg-[#F8F9FA] pb-32 font-sans text-[#0A2540]">
            <div class="max-w-6xl mx-auto px-6 lg:px-8 py-10 lg:py-12">
                <!-- HEADER TITLE -->
                <div class="mb-8">
                    <h1 class="text-[22px] font-semibold text-[#0A2540]">Ajukan Aset Baru</h1>
                    <p class="text-[14px] text-slate-500 mt-1.5 leading-relaxed">Lengkapi data aset Anda agar calon penyewa bisa melihat unit Anda di platform kusewa.</p>
                </div>

                <div class="flex flex-col lg:flex-row gap-12 lg:gap-16 items-start">

                    <!-- SIDEBAR NAV (Sticky) -->
                    <div class="w-full lg:w-[260px] shrink-0 lg:sticky lg:top-32 hidden md:block">
                        <div class="flex flex-col relative before:absolute before:left-5 before:top-4 before:bottom-4 before:w-[2px] before:bg-slate-200 before:-z-10">

                            <div class="flex items-start gap-4 mb-8 relative z-10 transition-colors">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 ring-4 ring-[#F8F9FA]"
                                     :class="currentStep === 1 ? 'bg-[#0A2540] text-white shadow-md shadow-[#0A2540]/20' : currentStep > 1 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-white border-2 border-slate-200 text-slate-400'">
                                    <i v-if="currentStep > 1" class="fa-solid fa-check text-sm"></i>
                                    <span v-else class="font-bold">1</span>
                                </div>
                                <div class="mt-0.5">
                                    <h3 class="text-[15px] transition-colors duration-300" :class="currentStep === 1 ? 'font-bold text-[#0A2540]' : 'font-semibold text-[#0A2540]/80'">Informasi Utama</h3>
                                    <p class="text-[13px] mt-0.5 transition-colors duration-300" :class="currentStep === 1 ? 'text-[#0A2540]/70' : 'text-slate-400'">Informasi Dasar Aset</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 mb-8 relative z-10 transition-colors">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 ring-4 ring-[#F8F9FA]"
                                     :class="currentStep === 2 ? 'bg-[#0A2540] text-white shadow-md shadow-[#0A2540]/20' : currentStep > 2 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-white border-2 border-slate-200 text-slate-400'">
                                    <i v-if="currentStep > 2" class="fa-solid fa-check text-sm"></i>
                                    <span v-else class="font-bold">2</span>
                                </div>
                                <div class="mt-0.5">
                                    <h3 class="text-[15px] transition-colors duration-300" :class="currentStep === 2 ? 'font-bold text-[#0A2540]' : 'font-semibold text-[#0A2540]/80'">Lokasi</h3>
                                    <p class="text-[13px] mt-0.5 transition-colors duration-300" :class="currentStep === 2 ? 'text-[#0A2540]/70' : 'text-slate-400'">Alamat Lengkap Aset</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 mb-8 relative z-10 transition-colors">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 ring-4 ring-[#F8F9FA]"
                                     :class="currentStep === 3 ? 'bg-[#0A2540] text-white shadow-md shadow-[#0A2540]/20' : currentStep > 3 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-white border-2 border-slate-200 text-slate-400'">
                                    <i v-if="currentStep > 3" class="fa-solid fa-check text-sm"></i>
                                    <span v-else class="font-bold">3</span>
                                </div>
                                <div class="mt-0.5">
                                    <h3 class="text-[15px] transition-colors duration-300" :class="currentStep === 3 ? 'font-bold text-[#0A2540]' : 'font-semibold text-[#0A2540]/80'">Harga & Foto</h3>
                                    <p class="text-[13px] mt-0.5 transition-colors duration-300" :class="currentStep === 3 ? 'text-[#0A2540]/70' : 'text-slate-400'">Harga Sewa & Galeri</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Horizontal Stepper for Mobile -->
                    <div class="w-full md:hidden mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200/50 flex justify-between items-center relative">
                        <div class="absolute top-1/2 left-8 right-8 h-[2px] bg-slate-100 -translate-y-1/2 -z-0"></div>

                        <div class="relative z-10 bg-white px-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors shadow-sm ring-4 ring-white"
                                 :class="currentStep === 1 ? 'bg-[#0A2540] text-white' : currentStep > 1 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-[#F8F9FA] border-2 border-slate-200/50 text-slate-400'">
                                <i v-if="currentStep > 1" class="fa-solid fa-check text-[10px]"></i>
                                <span v-else>1</span>
                            </div>
                        </div>

                        <div class="relative z-10 bg-white px-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors shadow-sm ring-4 ring-white"
                                 :class="currentStep === 2 ? 'bg-[#0A2540] text-white' : currentStep > 2 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-[#F8F9FA] border-2 border-slate-200/50 text-slate-400'">
                                <i v-if="currentStep > 2" class="fa-solid fa-check text-[10px]"></i>
                                <span v-else>2</span>
                            </div>
                        </div>

                        <div class="relative z-10 bg-white px-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors shadow-sm ring-4 ring-white"
                                 :class="currentStep === 3 ? 'bg-[#0A2540] text-white' : currentStep > 3 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-[#F8F9FA] border-2 border-slate-200/50 text-slate-400'">
                                <i v-if="currentStep > 3" class="fa-solid fa-check text-[10px]"></i>
                                <span v-else>3</span>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENT AREA -->
                    <div class="flex-grow w-full max-w-[720px]">

            <!-- FORM CARD -->
            <form @submit.prevent="submitProperty" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">

                <!-- STEP 1 -->
                <Step1 v-show="currentStep === 1" :form="form" />

                <!-- STEP 2 -->
                <Step2 v-show="currentStep === 2" :form="form" :currentStep="currentStep" />

                <!-- STEP 3 -->
                <Step3 v-show="currentStep === 3" :form="form" />

                    </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- STICKY BOTTOM ACTION BAR (Mobile) -->
        <DetailBottomBar class="md:hidden"
            :buttonText="currentStep < 3 ? 'Selanjutnya' : 'Selesaikan'"
            @submit="currentStep < 3 ? nextStep() : submitProperty()"
            :disabled="form.processing">

            <template #left-content>
                <button type="button" @click="prevStep" class="h-[48px] px-6 rounded-[12px] border border-muted/30 text-secondary font-semibold text-[14px] hover:bg-background transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </button>
            </template>
        </DetailBottomBar>

        <!-- STICKY BOTTOM ACTION BAR (Desktop) -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-muted/20 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] z-40 hidden md:block">
            <div class="max-w-6xl mx-auto px-6 lg:px-8 h-20 flex items-center justify-between">
                <div>
                    <button type="button" @click="prevStep" class="h-[48px] px-6 rounded-[12px] border border-muted/30 text-[#0A2540] font-semibold text-[14px] hover:bg-slate-50 transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Kembali
                    </button>
                </div>

                <div>
                    <button v-if="currentStep < 3" type="button" @click="nextStep" :disabled="form.processing" class="h-[48px] px-8 rounded-[12px] bg-[#0A2540] text-white font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                    <button v-else type="button" @click="submitProperty" :disabled="form.processing" class="h-[48px] px-8 rounded-[12px] bg-[#FFC000] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-1 h-4 w-4 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        {{ form.processing ? 'Memproses...' : 'Kirim Pengajuan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- POP-UP SUCCESS MODAL -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full text-center shadow-2xl border border-slate-100 space-y-5 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#0A2540] via-[#FFC000] to-[#0A2540]"></div>

                        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-[#FFC000] flex items-center justify-center mx-auto text-2xl relative shadow-inner">
                            <i class="fa-solid fa-paper-plane text-[#0A2540]"></i>
                            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
                            </span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Pengajuan Berhasil Dikirim!</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Data unit Anda sudah masuk ke sistem dan saat ini sedang dalam proses review admin.</p>
                        </div>

                        <div class="bg-amber-50/60 border border-amber-200/80 rounded-2xl p-3.5 flex items-center gap-3 text-left">
                            <div class="w-8 h-8 rounded-xl bg-[#FFC000]/20 text-[#0A2540] flex items-center justify-center shrink-0 text-sm">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Status Pengajuan</span>
                                <span class="text-xs font-black text-[#0A2540]">Menunggu Persetujuan Admin</span>
                            </div>
                        </div>

                        <button type="button" @click="closeModalAndRedirect" class="w-full bg-[#0A2540] text-white font-bold text-xs py-3.5 rounded-xl hover:bg-[#123e6b] transition cursor-pointer">
                            Kembali ke Halaman Properti
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
