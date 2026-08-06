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

// ================== KATEGORI & JENIS PROPERTI (identik dengan create.vue) ==================
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

const fasilitasWajibPerJenis = {
    'Kos-kosan': ['Wi-Fi / Internet', 'Kamar Mandi Dalam', 'Kasur & Lemari'],
    'Hotel': ['Wi-Fi / Internet', 'AC (Pendingin)', 'Furnished Lengkap', 'Kamar Mandi Dalam', 'Kasur & Lemari'],
    'Apartemen': ['Wi-Fi / Internet', 'AC (Pendingin)', 'Kamar Mandi Dalam', 'Dapur Bersama / Pribadi'],
    'Guest House': ['Wi-Fi / Internet', 'AC (Pendingin)', 'Kamar Mandi Dalam', 'Kasur & Lemari'],
    'Rusun / Condominium': ['Kamar Mandi Dalam', 'Kasur & Lemari', 'Area Parkir Luas'],
};

const fasilitasWajibKamar = (jenisProperti) => fasilitasWajibPerJenis[jenisProperti] || ['Kamar Mandi Dalam'];

// Saat membangun ulang tipe kamar dari data existing, fasilitas wajib tetap
// dipaksa masuk (menjaga konsistensi dengan aturan yang sama di create.vue).
const buatTipeKamarBaru = (data = {}, jenisProperti = 'Kos-kosan') => {
    const wajib = fasilitasWajibKamar(jenisProperti);
    const existingFasilitas = data.fasilitas_kamar ? [...data.fasilitas_kamar] : [];
    wajib.forEach((item) => {
        if (!existingFasilitas.includes(item)) existingFasilitas.push(item);
    });
    return {
        id: Date.now() + Math.random(),
        nama_tipe_kamar: data.nama_tipe_kamar || '',
        jumlah_kamar: data.jumlah_kamar ?? '',
        kapasitas_orang: data.kapasitas_orang ?? '',
        fasilitas_kamar: existingFasilitas
    };
};

const daftarKategoriFoto = [
    'Fasad Depan / Tampak Utama', 'Ruang Tamu', 'Kamar Tidur Utama', 'Kamar Tidur Tambahan',
    'Dapur', 'Kamar Mandi', 'Ruang Makan', 'Balkon / Rooftop', 'Halaman / Taman',
    'Carport / Garasi', 'Area Parkir', 'Ruang Kerja / Kantor', 'Ruang Rapat / Meeting Room',
    'Resepsionis / Lobby', 'Etalase / Area Display', 'Gudang / Ruang Penyimpanan',
    'Area Produksi / Pabrik', 'Loading Dock / Akses Truk', 'Denah / Site Plan',
    'Akses Jalan Masuk', 'View / Pemandangan Sekitar', 'Titik Display Baliho / Videotron', 'Lainnya'
];

// ================== SKEMA PEMBAYARAN OTOMATIS SESUAI JENIS PROPERTI (identik dengan create.vue) ==================
const tipeSewaByJenisProperti = {
    'Kos-kosan': ['Bulanan', 'Tahunan'],
    'Hotel': ['Harian'],
    'Rumah Tapak': ['Bulanan', 'Tahunan'],
    'Villa': ['Harian', 'Bulanan'],
    'Homestay': ['Harian'],
    'Apartemen': ['Bulanan', 'Tahunan'],
    'Guest House': ['Harian', 'Bulanan'],
    'Rusun / Condominium': ['Bulanan', 'Tahunan'],
    'Ruko (Rumah Toko)': ['Bulanan', 'Tahunan'],
    'Kios / Lapak Pasar': ['Harian', 'Bulanan', 'Tahunan'],
    'Kantor / Workspace': ['Bulanan', 'Tahunan'],
    'Gedung Komersial': ['Bulanan', 'Tahunan'],
    'Food Court / Booth': ['Harian', 'Bulanan'],
    'Gudang Logistik': ['Bulanan', 'Tahunan'],
    'Pabrik / Manufaktur': ['Tahunan'],
    'Cold Storage': ['Bulanan', 'Tahunan'],
    'Lahan / Tanah Kosong': ['Tahunan'],
    'Lahan Pertanian / Perkebunan': ['Tahunan'],
    'Baliho / Reklame': ['Bulanan', 'Tahunan'],
    'Billboard / Videotron': ['Bulanan', 'Tahunan'],
    'Neon Box / Titik Toko': ['Bulanan', 'Tahunan']
};

const availableTipeSewa = computed(() => {
    return tipeSewaByJenisProperti[form.jenis_properti] || ['Harian', 'Bulanan', 'Tahunan'];
});

// ================== LOKASI CASCADING: NEGARA -> PROVINSI -> KOTA (identik dengan create.vue) ==================
const daftarNegara = ['Indonesia', 'Malaysia', 'Singapura', 'Lainnya'];

const lokasiIndonesia = {
    'Kalimantan Timur': ['Samarinda', 'Balikpapan', 'Tenggarong (Kutai Kartanegara)', 'Bontang', 'Sangatta', 'Sendawar', 'Lainnya'],
    'Kalimantan Selatan': ['Banjarmasin', 'Banjarbaru', 'Martapura', 'Kandangan', 'Lainnya'],
    'Kalimantan Barat': ['Pontianak', 'Singkawang', 'Ketapang', 'Lainnya'],
    'Kalimantan Tengah': ['Palangka Raya', 'Sampit', 'Kuala Kapuas', 'Lainnya'],
    'Kalimantan Utara': ['Tanjung Selor', 'Tarakan', 'Nunukan', 'Lainnya'],
    'DKI Jakarta': ['Jakarta Pusat', 'Jakarta Utara', 'Jakarta Barat', 'Jakarta Selatan', 'Jakarta Timur', 'Kepulauan Seribu', 'Lainnya'],
    'Jawa Barat': ['Bandung', 'Bekasi', 'Bogor', 'Depok', 'Cimahi', 'Sukabumi', 'Tasikmalaya', 'Cirebon', 'Lainnya'],
    'Jawa Tengah': ['Semarang', 'Surakarta (Solo)', 'Magelang', 'Salatiga', 'Pekalongan', 'Tegal', 'Lainnya'],
    'Jawa Timur': ['Surabaya', 'Malang', 'Kediri', 'Madiun', 'Batu', 'Mojokerto', 'Lainnya'],
    'DI Yogyakarta': ['Yogyakarta', 'Sleman', 'Bantul', 'Kulon Progo', 'Gunungkidul', 'Lainnya'],
    'Banten': ['Tangerang', 'Tangerang Selatan', 'Serang', 'Cilegon', 'Lainnya'],
    'Bali': ['Denpasar', 'Badung', 'Gianyar', 'Tabanan', 'Buleleng', 'Lainnya'],
    'Sumatera Utara': ['Medan', 'Binjai', 'Pematangsiantar', 'Lainnya'],
    'Sumatera Barat': ['Padang', 'Bukittinggi', 'Payakumbuh', 'Lainnya'],
    'Riau': ['Pekanbaru', 'Dumai', 'Lainnya'],
    'Kepulauan Riau': ['Batam', 'Tanjungpinang', 'Lainnya'],
    'Sumatera Selatan': ['Palembang', 'Lubuklinggau', 'Lainnya'],
    'Lampung': ['Bandar Lampung', 'Metro', 'Lainnya'],
    'Sulawesi Selatan': ['Makassar', 'Parepare', 'Lainnya'],
    'Sulawesi Utara': ['Manado', 'Bitung', 'Lainnya'],
    'Papua': ['Jayapura', 'Lainnya'],
    'Lainnya': ['Lainnya']
};

const lokasiByNegara = {
    'Indonesia': lokasiIndonesia,
    'Malaysia': { 'Lainnya': ['Lainnya'] },
    'Singapura': { 'Lainnya': ['Lainnya'] },
    'Lainnya': { 'Lainnya': ['Lainnya'] }
};

// Menentukan pilihan awal dropdown negara/provinsi/kota dari data properti yang sudah tersimpan.
// Jika nilai tersimpan tidak ada di daftar standar, dropdown otomatis diarahkan ke "Lainnya"
// dan nilai aslinya tetap dipertahankan di field polos (negara/provinsi/kota).
const negaraAwal = (props.property.negara && daftarNegara.includes(props.property.negara))
    ? props.property.negara
    : 'Lainnya';

const provinsiAwal = (() => {
    const data = lokasiByNegara[negaraAwal] || lokasiByNegara['Lainnya'];
    const daftarProvinsi = Object.keys(data);
    if (props.property.provinsi && daftarProvinsi.includes(props.property.provinsi)) return props.property.provinsi;
    return daftarProvinsi[0] || 'Lainnya';
})();

const kotaAwal = (() => {
    const data = lokasiByNegara[negaraAwal] || lokasiByNegara['Lainnya'];
    const daftarKota = data[provinsiAwal] || ['Lainnya'];
    if (props.property.kota && daftarKota.includes(props.property.kota)) return props.property.kota;
    return daftarKota[0] || 'Lainnya';
})();

// Inisialisasi tipe_kamar dari data existing
const initialTipeKamar = (props.property.tipe_kamar && props.property.tipe_kamar.length > 0)
    ? props.property.tipe_kamar.map(t => buatTipeKamarBaru(t, props.property.jenis_properti))
    : [buatTipeKamarBaru({}, props.property.jenis_properti || 'Kos-kosan')];

// Inisialisasi foto_properti dari data existing:
// setiap kategori punya existing_photos (path lama yang dipertahankan) + files (upload baru)
const initialFotoProperti = (props.property.foto_properti && props.property.foto_properti.length > 0)
    ? props.property.foto_properti.map(kategori => {
        const dikenal = daftarKategoriFoto.includes(kategori.nama_ruangan);
        return {
            id: Date.now() + Math.random(),
            nama_ruangan_pilihan: dikenal ? kategori.nama_ruangan : 'Lainnya',
            nama_ruangan: kategori.nama_ruangan,
            existing_photos: (kategori.photos || kategori.existing_photos || []).map(p => typeof p === 'string' ? p : p.path),
            existing_previews: (kategori.photos || []).map(p => typeof p === 'string' ? p : p.url),
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

    negara_pilihan: negaraAwal,
    negara: props.property.negara || (negaraAwal !== 'Lainnya' ? negaraAwal : ''),
    provinsi_pilihan: provinsiAwal,
    provinsi: props.property.provinsi || (provinsiAwal !== 'Lainnya' ? provinsiAwal : ''),
    kota_pilihan: kotaAwal,
    kota: props.property.kota || (kotaAwal !== 'Lainnya' ? kotaAwal : ''),
    kecamatan: props.property.kecamatan || '',
    alamat_lengkap: props.property.alamat_lengkap || '',
    latitude: props.property.latitude || '',
    longitude: props.property.longitude || '',

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
        form.tipe_kamar.push(buatTipeKamarBaru({}, newJenis));
    }

    // Tambahkan fasilitas minimum tanpa menghapus fasilitas tambahan yang sudah dipilih.
    const wajib = fasilitasWajibKamar(newJenis);
    form.tipe_kamar.forEach((tipe) => {
        wajib.forEach((fasilitas) => {
            if (!tipe.fasilitas_kamar.includes(fasilitas)) tipe.fasilitas_kamar.push(fasilitas);
        });
    });

    // Sesuaikan skema pembayaran jika yang sedang aktif tidak relevan lagi
    const opsiValid = tipeSewaByJenisProperti[newJenis] || ['Harian', 'Bulanan', 'Tahunan'];
    if (!opsiValid.includes(form.tipe_sewa)) {
        form.tipe_sewa = opsiValid[0];
    }
});

const availableProvinsi = computed(() => {
    const data = lokasiByNegara[form.negara_pilihan];
    return data ? Object.keys(data) : ['Lainnya'];
});

const availableKota = computed(() => {
    const data = lokasiByNegara[form.negara_pilihan];
    if (!data) return ['Lainnya'];
    return data[form.provinsi_pilihan] || ['Lainnya'];
});

const pilihNegara = () => {
    form.negara = form.negara_pilihan === 'Lainnya' ? '' : form.negara_pilihan;
    const daftarProvinsiBaru = availableProvinsi.value;
    form.provinsi_pilihan = daftarProvinsiBaru[0] || 'Lainnya';
    pilihProvinsi();
};

const pilihProvinsi = () => {
    form.provinsi = form.provinsi_pilihan === 'Lainnya' ? '' : form.provinsi_pilihan;
    const daftarKotaBaru = availableKota.value;
    form.kota_pilihan = daftarKotaBaru[0] || 'Lainnya';
    pilihKota();
};

const pilihKota = () => {
    form.kota = form.kota_pilihan === 'Lainnya' ? '' : form.kota_pilihan;
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

// ================== FASILITAS (identik dengan create.vue, termasuk koreksi penulisan) ==================
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

const aliasFasilitas = {
    'wifi': 'Wi-Fi / Internet', 'wi fi': 'Wi-Fi / Internet', 'wi-fi': 'Wi-Fi / Internet',
    'internet': 'Wi-Fi / Internet', 'jaringan internet': 'Wi-Fi / Internet',
    'ac': 'AC (Pendingin)', 'ac dingin': 'AC (Pendingin)', 'pendingin ruangan': 'AC (Pendingin)',
    'pendingin udara': 'AC (Pendingin)', 'ac sentral': 'AC Sentral', 'central ac': 'AC Sentral',
    'parkir': 'Area Parkir Luas', 'parkiran': 'Area Parkir Luas', 'tempat parkir': 'Area Parkir Luas',
    'lahan parkir': 'Area Parkir Luas', 'carport': 'Carport / Garasi Mobil', 'garasi': 'Carport / Garasi Mobil',
    'garasi mobil': 'Carport / Garasi Mobil', 'kasur': 'Kasur & Lemari', 'lemari': 'Kasur & Lemari',
    'kasur lemari': 'Kasur & Lemari', 'meja belajar': 'Meja & Kursi Belajar', 'meja kursi': 'Meja & Kursi Belajar',
    'kulkas': 'Kulkas', 'lemari es': 'Kulkas', 'water heater': 'Water Heater / Air Panas',
    'air panas': 'Water Heater / Air Panas', 'pemanas air': 'Water Heater / Air Panas',
    'dispenser': 'Dispenser', 'galon': 'Dispenser', 'setrika': 'Setrika & Meja Setrika',
    'jemuran': 'Jemuran Pakaian', 'balkon': 'Balkon / Rooftop', 'rooftop': 'Balkon / Rooftop',
    'ruang tamu': 'Ruang Tamu Bersama', 'tv': 'TV Kabel / Smart TV', 'tv kabel': 'TV Kabel / Smart TV',
    'smart tv': 'TV Kabel / Smart TV', 'televisi': 'TV Kabel / Smart TV', 'kitchen set': 'Kitchen Set',
    'lemari dapur': 'Kitchen Set', 'kompor': 'Kompor / Kitchen Gas', 'kompor gas': 'Kompor / Kitchen Gas',
    'dapur': 'Dapur Bersama / Pribadi', 'dapur bersama': 'Dapur Bersama / Pribadi', 'ruang makan': 'Ruang Makan',
    'gudang': 'Gudang Penyimpanan', 'ruang penyimpanan': 'Gudang Penyimpanan', 'taman': 'Taman / Halaman',
    'halaman': 'Taman / Halaman', 'kamar mandi dalam': 'Kamar Mandi Dalam', 'km dalam': 'Kamar Mandi Dalam',
    'kamar mandi': 'Kamar Mandi Dalam', 'cctv': 'Keamanan / CCTV 24 Jam', 'keamanan': 'Keamanan / CCTV 24 Jam',
    'kamera cctv': 'Keamanan / CCTV 24 Jam', 'satpam': 'Keamanan / CCTV 24 Jam', 'security': 'Keamanan / CCTV 24 Jam',
    'listrik': 'Termasuk Listrik & Air', 'listrik air': 'Termasuk Listrik & Air', 'air': 'Termasuk Listrik & Air',
    'kolam renang': 'Kolam Renang', 'renang': 'Kolam Renang', 'kolam': 'Kolam Renang', 'swimming pool': 'Kolam Renang',
    'bebas banjir': 'Bebas Banjir', 'anti banjir': 'Bebas Banjir', 'tidak banjir': 'Bebas Banjir',
    'akses 24 jam': 'Akses 24 Jam', '24 jam': 'Akses 24 Jam', 'buka 24 jam': 'Akses 24 Jam',
    'furnished': 'Furnished Lengkap', 'full furnished': 'Furnished Lengkap', 'perabotan lengkap': 'Furnished Lengkap',
    'laundry': 'Laundry / Mesin Cuci', 'mesin cuci': 'Laundry / Mesin Cuci', 'cuci baju': 'Laundry / Mesin Cuci',
    'genset': 'Genset / Listrik Cadangan', 'listrik cadangan': 'Genset / Listrik Cadangan', 'generator': 'Genset / Listrik Cadangan',
    'lift': 'Lift / Elevator', 'elevator': 'Lift / Elevator', 'gym': 'Gym / Pusat Kebugaran',
    'fitness': 'Gym / Pusat Kebugaran', 'pusat kebugaran': 'Gym / Pusat Kebugaran', 'musholla': 'Musholla',
    'mushola': 'Musholla', 'masjid': 'Tempat Ibadah', 'tempat ibadah': 'Tempat Ibadah',
    'resepsionis': 'Resepsionis 24 Jam', 'receptionist': 'Resepsionis 24 Jam', 'cleaning service': 'Petugas Kebersihan Rutin',
    'petugas kebersihan': 'Petugas Kebersihan Rutin', 'kebersihan': 'Petugas Kebersihan Rutin',
    'anti hama': 'Pest Control / Anti Hama', 'pest control': 'Pest Control / Anti Hama',
    'fingerprint': 'Akses Kartu / Fingerprint', 'akses kartu': 'Akses Kartu / Fingerprint', 'kartu akses': 'Akses Kartu / Fingerprint',
    'pagar': 'Pagar Keliling', 'pagar keliling': 'Pagar Keliling', 'palang pintu': 'Palang Pintu Otomatis',
    'portal otomatis': 'Palang Pintu Otomatis', 'toilet umum': 'Toilet Umum', 'wc umum': 'Toilet Umum',
    'lobby': 'Ruang Tunggu / Lobby', 'ruang tunggu': 'Ruang Tunggu / Lobby', 'area merokok': 'Area Merokok',
    'smoking area': 'Area Merokok', 'area bermain': 'Area Bermain Anak', 'playground': 'Area Bermain Anak',
    'dekat sekolah': 'Dekat Sekolah / Kampus', 'dekat kampus': 'Dekat Sekolah / Kampus', 'dekat mall': 'Dekat Pusat Perbelanjaan',
    'dekat pusat perbelanjaan': 'Dekat Pusat Perbelanjaan', 'dekat rumah sakit': 'Dekat Rumah Sakit / Klinik',
    'dekat klinik': 'Dekat Rumah Sakit / Klinik', 'dekat halte': 'Dekat Transportasi Umum',
    'dekat angkutan umum': 'Dekat Transportasi Umum', 'transportasi umum': 'Dekat Transportasi Umum',
    'view kota': 'View Kota / Pemandangan', 'pemandangan': 'View Kota / Pemandangan', 'drainase': 'Drainase Baik',
    'saluran air': 'Drainase Baik', 'akses jalan': 'Dekat Jalan Utama / Akses Mudah', 'jalan utama': 'Dekat Jalan Utama / Akses Mudah',
    'meeting room': 'Ruang Rapat / Meeting Room', 'ruang rapat': 'Ruang Rapat / Meeting Room',
    'panel listrik': 'Panel Listrik 3 Phase', '3 phase': 'Panel Listrik 3 Phase', 'listrik 3 phase': 'Panel Listrik 3 Phase',
    'pantry': 'Pantry / Dapur Kantor', 'dapur kantor': 'Pantry / Dapur Kantor', 'meja resepsionis': 'Meja Resepsionis',
    'signage': 'Signage / Papan Nama Toko', 'papan nama': 'Signage / Papan Nama Toko', 'etalase': 'Etalase Kaca',
    'fitting room': 'Ruang Ganti / Fitting Room', 'ruang ganti': 'Ruang Ganti / Fitting Room',
    'loading dock': 'Loading Dock / Akses Truk Besar', 'akses truk': 'Loading Dock / Akses Truk Besar',
    'akses kontainer': 'Akses Jalan Lebar (Muat Kontainer)', 'listrik industri': 'Listrik Daya Besar (Industri)',
    'daya besar': 'Listrik Daya Besar (Industri)', 'data center': 'Ruang Server / Data Center Ready',
    'ruang server': 'Ruang Server / Data Center Ready', 'crane': 'Crane / Alat Angkat Barang',
    'alat angkat': 'Crane / Alat Angkat Barang', 'lantai kuat': 'Lantai Kuat (Heavy Duty Floor)',
    'heavy duty': 'Lantai Kuat (Heavy Duty Floor)', 'ventilasi': 'Ventilasi Industri',
    'pemadam kebakaran': 'Instalasi Pemadam Kebakaran', 'apar': 'Instalasi Pemadam Kebakaran', 'sprinkler': 'Instalasi Pemadam Kebakaran',
    'rak penyimpanan': 'Rak Penyimpanan / Racking System', 'racking': 'Rak Penyimpanan / Racking System',
    'kantor gudang': 'Ruang Kantor di Dalam Gudang', 'sertifikat': 'Sertifikat Lahan (SHM/HGB)',
    'shm': 'Sertifikat Lahan (SHM/HGB)', 'hgb': 'Sertifikat Lahan (SHM/HGB)', 'sumur bor': 'Sumber Air Bersih (Sumur Bor)',
    'air bersih': 'Sumber Air Bersih (Sumur Bor)', 'irigasi': 'Akses Irigasi', 'saluran irigasi': 'Akses Irigasi',
    'tanah rata': 'Kontur Tanah Rata', 'kontur rata': 'Kontur Tanah Rata', 'bebas sengketa': 'Bebas Sengketa',
    'tidak sengketa': 'Bebas Sengketa', 'dekat jalan raya': 'Dekat Jalan Raya', 'pinggir jalan raya': 'Dekat Jalan Raya',
    'tiang baliho': 'Rangka Baja / Tiang Kokoh (Baliho)', 'rangka baja': 'Rangka Baja / Tiang Kokoh (Baliho)',
    'lampu sorot': 'Pencahayaan Sorot / Spotlight (Baliho)', 'spotlight': 'Pencahayaan Sorot / Spotlight (Baliho)',
    'titik strategis': 'Titik Strategis / High Traffic', 'high traffic': 'Titik Strategis / High Traffic',
    'ramai dilalui': 'Titik Strategis / High Traffic', 'ukuran custom': 'Ukuran Custom (Baliho)',
    'ukuran sesuai permintaan': 'Ukuran Custom (Baliho)', 'maintenance': 'Perawatan & Maintenance Rutin (Baliho)',
    'perawatan rutin': 'Perawatan & Maintenance Rutin (Baliho)'
};

const jarakLevenshtein = (a, b) => {
    const baris = Array.from({ length: a.length + 1 }, () => new Array(b.length + 1).fill(0));
    for (let i = 0; i <= a.length; i++) baris[i][0] = i;
    for (let j = 0; j <= b.length; j++) baris[0][j] = j;
    for (let i = 1; i <= a.length; i++) {
        for (let j = 1; j <= b.length; j++) {
            const biaya = a[i - 1] === b[j - 1] ? 0 : 1;
            baris[i][j] = Math.min(baris[i - 1][j] + 1, baris[i][j - 1] + 1, baris[i - 1][j - 1] + biaya);
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
    if (index === -1) form.fasilitas.push(id);
    else form.fasilitas.splice(index, 1);
};

const tambahFasilitasKustom = () => {
    const raw = inputFasilitasKustom.value.trim();
    if (!raw) return;
    const nilaiFinal = koreksiPenulisanFasilitas(raw);
    if (!form.fasilitas.includes(nilaiFinal)) form.fasilitas.push(nilaiFinal);
    inputFasilitasKustom.value = '';
};

const hapusFasilitas = (fas) => {
    const index = form.fasilitas.indexOf(fas);
    if (index > -1) form.fasilitas.splice(index, 1);
};

// ================== FORMAT HARGA RIBUAN (identik dengan create.vue) ==================
const formatRibuan = (nilai) => {
    if (!nilai) return '';
    const angka = nilai.toString().replace(/\D/g, '');
    if (!angka) return '';
    return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const hargaSewaDisplay = computed({
    get: () => formatRibuan(form.harga_sewa),
    set: (nilai) => { form.harga_sewa = nilai.replace(/\D/g, ''); }
});

const depositDisplay = computed({
    get: () => formatRibuan(form.deposit),
    set: (nilai) => { form.deposit = nilai.replace(/\D/g, ''); }
});

// ================== TIPE KAMAR ==================
const tambahTipeKamar = () => form.tipe_kamar.push(buatTipeKamarBaru({}, form.jenis_properti));
const hapusTipeKamar = (index) => form.tipe_kamar.splice(index, 1);

const isFasilitasWajibKamar = (item) => fasilitasWajibKamar(form.jenis_properti).includes(item);

const toggleFasilitasKamar = (tIndex, item) => {
    const list = form.tipe_kamar[tIndex].fasilitas_kamar;
    const idx = list.indexOf(item);
    if (idx !== -1 && isFasilitasWajibKamar(item)) return; // fasilitas wajib tidak bisa dihapus
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
    await nextTick();
    initMap();
});

onBeforeUnmount(() => {
    if (mapInstance) mapInstance.remove();
    window.removeEventListener('click', handleClickOutsideFasilitas);
    window.removeEventListener('click', handleClickOutsideFasilitasKamar);
});

// ================== FOTO PER KATEGORI ==================
const kategoriFotoSudahDipilih = (kategori, index) => form.foto_properti.some((item, itemIndex) => (
    itemIndex !== index && item.nama_ruangan_pilihan === kategori
));

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

const hapusFotoLama = (catIndex, photoIndex) => {
    form.foto_properti[catIndex].existing_photos.splice(photoIndex, 1);
    form.foto_properti[catIndex].existing_previews.splice(photoIndex, 1);
};

// --- SUBMIT ---
const submit = () => {
    if (!form.negara.trim()) { alert('Mohon lengkapi negara.'); return; }
    if (!form.provinsi.trim()) { alert('Mohon lengkapi provinsi.'); return; }
    if (!form.kota.trim()) { alert('Mohon lengkapi kota/kabupaten.'); return; }
    if (!form.latitude || !form.longitude) {
        alert('Mohon tentukan titik lokasi properti Anda di peta terlebih dahulu.');
        return;
    }

    form.post(route('owner.property.update', props.property.id), {
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
                                <option v-for="opsi in availableTipeSewa" :key="opsi" :value="opsi">{{ opsi }}</option>
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
                                                :class="isFasilitasWajibKamar(item) ? 'bg-emerald-50/60' : 'hover:bg-slate-50'"
                                                class="w-full flex items-center gap-2.5 px-3 py-2 text-[11px] text-left transition cursor-pointer">
                                                <span class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition" :class="tipe.fasilitas_kamar.includes(item) ? 'bg-[#0A2540] border-[#0A2540]' : 'border-slate-300 bg-white'">
                                                    <i v-if="tipe.fasilitas_kamar.includes(item)" class="fa-solid fa-check text-[9px] text-white"></i>
                                                </span>
                                                <span :class="tipe.fasilitas_kamar.includes(item) ? 'font-bold text-[#0A2540]' : 'text-slate-600'">{{ item }}</span>
                                                <span v-if="isFasilitasWajibKamar(item)" class="ml-auto text-[9px] text-emerald-600 font-bold">Wajib</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="tipe.fasilitas_kamar.length > 0" class="flex flex-wrap gap-1.5 mt-2">
                                        <div v-for="item in tipe.fasilitas_kamar" :key="item" class="inline-flex items-center gap-1 bg-[#0A2540]/5 border border-[#0A2540]/20 text-[#0A2540] text-[10px] font-bold px-2 py-1 rounded-lg">
                                            <span>{{ item }}</span>
                                            <button v-if="!isFasilitasWajibKamar(item)" type="button" @click.stop="toggleFasilitasKamar(tIndex, item)" class="text-rose-500 hover:text-rose-700 cursor-pointer">
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

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Negara <span class="text-rose-500">*</span></label>
                            <select v-model="form.negara_pilihan" @change="pilihNegara" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option v-for="n in daftarNegara" :key="n" :value="n">{{ n }}</option>
                            </select>
                            <input v-if="form.negara_pilihan === 'Lainnya'" v-model="form.negara" type="text" placeholder="Tulis nama negara"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition mt-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi <span class="text-rose-500">*</span></label>
                            <select v-model="form.provinsi_pilihan" @change="pilihProvinsi" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option v-for="p in availableProvinsi" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <input v-if="form.provinsi_pilihan === 'Lainnya'" v-model="form.provinsi" type="text" placeholder="Tulis nama provinsi"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition mt-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                            <select v-model="form.kota_pilihan" @change="pilihKota" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option v-for="k in availableKota" :key="k" :value="k">{{ k }}</option>
                            </select>
                            <input v-if="form.kota_pilihan === 'Lainnya'" v-model="form.kota" type="text" placeholder="Tulis nama kota/kabupaten"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition mt-2" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan <span class="text-rose-500">*</span></label>
                            <input v-model="form.kecamatan" type="text" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 transition" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                            <input v-model="form.alamat_lengkap" type="text" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 transition" required />
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
                                <input v-model="hargaSewaDisplay" type="text" inputmode="numeric" class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 transition" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Uang Deposit (Opsional)</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input v-model="depositDisplay" type="text" inputmode="numeric" class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 transition" />
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
                                        <option v-for="opt in daftarKategoriFoto" :key="opt" :value="opt" :disabled="kategoriFotoSudahDipilih(opt, index)">{{ opt }}</option>
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