<script setup>
import { computed, watch, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({ form: Object });
const form = props.form;

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



// --- LOGIKA DROPDOWN CASCADING ---
// Mendapatkan daftar jenis properti sesuai kategori utama yang dipilih
const availableJenisProperti = computed(() => {
    const group = kategoriPropertiGroups.find(g => g.label === form.kategori);
    return group ? group.options : [];
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



const fasilitasKamarItems = [
    'Wi-Fi / Internet', 'AC (Pendingin)', 'Kamar Mandi Dalam', 'Furnished Lengkap',
    'Kasur & Lemari', 'Meja & Kursi Belajar', 'Kulkas', 'Water Heater / Air Panas',
    'Dispenser', 'Setrika & Meja Setrika', 'Jemuran Pakaian', 'Balkon / Rooftop',
    'Ruang Tamu Bersama', 'TV Kabel / Smart TV', 'Kitchen Set', 'Kompor / Kitchen Gas',
    'Dapur Bersama / Pribadi', 'Ruang Makan', 'Gudang Penyimpanan',
    'Carport / Garasi Mobil', 'Taman / Halaman'
];

// --- LOGIKA FASILITAS ASET (UMUM) ---
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

// --- LOGIKA NESTED DROPDOWN FASILITAS UMUM ---
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

const countFasilitasTerpilih = (grup) => {
    return grup.items.filter(item => form.fasilitas.includes(item)).length;
};

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

onMounted(() => {
    window.addEventListener('click', handleClickOutsideFasilitas);
    window.addEventListener('click', handleClickOutsideFasilitasKamar);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutsideFasilitas);
    window.removeEventListener('click', handleClickOutsideFasilitasKamar);
});

</script>

<template>
<div class="space-y-4">
<!-- STEP 1: INFORMASI UTAMA -->

                    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-[#0A2540]"></i>
                        <span>Informasi Dasar Aset</span>
                    </h2>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Aset<span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.nama_properti" type="text" placeholder="Contoh: Villa Melati Eksklusif Samarinda Kota"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition" required
                        />
                    </div>

                    <!-- DROPDOWN KATEGORI & JENIS (CASCADING / TERPISAH) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Aset<span class="text-rose-500">*</span></label>
                            <select v-model="form.kategori" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option v-for="group in kategoriPropertiGroups" :key="group.label" :value="group.label">
                                    {{ group.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis / Tipe Aset<span class="text-rose-500">*</span></label>
                            <select v-model="form.jenis_properti" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option v-for="jenis in availableJenisProperti" :key="jenis" :value="jenis">
                                    {{ jenis }}
                                </option>
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

                    <!-- KUMPULAN FIELD DINAMIS BERDASARKAN JENIS PROPERTI -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-4">
                        <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-sliders text-[#0A2540]"></i>
                            Detail Spesifik: {{ form.jenis_properti }}
                        </h3>

                        <!-- 1. DETAIL SPESIFIK ASET (General Fields) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <!-- (A) Hunian & Tempat Tinggal (Mencakup yang memiliki tipe kamar maupun tidak) -->
                            <template v-if="['Rumah Tapak', 'Villa', 'Homestay', 'Guest House', 'Kontrakan', 'Kos-kosan', 'Hotel', 'Apartemen', 'Rusun / Condominium'].includes(form.jenis_properti)">
                                <!-- Luas Bangunan: Tampil untuk semua Hunian -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Bangunan (m²)</label>
                                    <input v-model="form.luas_bangunan" type="number" placeholder="Contoh: 120" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                
                                <!-- Luas Tanah: Hanya tampil jika bukan Kos-kosan, Apartemen, Rusun, Homestay (sesuai AssetSeeder) -->
                                <div v-if="['Rumah Tapak', 'Villa', 'Kontrakan', 'Hotel'].includes(form.jenis_properti)">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Tanah (m²)</label>
                                    <input v-model="form.luas_tanah" type="number" placeholder="Contoh: 150" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>

                                <!-- Jumlah Lantai: Tampil untuk semua Hunian -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Lantai</label>
                                    <input v-model="form.jumlah_lantai" type="number" placeholder="Contoh: 2" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>

                                <!-- Tahun Dibangun: Tampil untuk semua Hunian kecuali Hotel -->
                                <div v-if="form.jenis_properti !== 'Hotel'">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tahun Dibangun</label>
                                    <input v-model="form.tahun_dibangun" type="number" placeholder="Contoh: 2020" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>

                                <!-- Daya Listrik & Sumber Air: Tampil untuk Rumah Tapak, Villa, Kontrakan -->
                                <template v-if="['Rumah Tapak', 'Villa', 'Kontrakan'].includes(form.jenis_properti)">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Daya Listrik (VA)</label>
                                        <select v-model="form.daya_listrik" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                            <option value="">Pilih Daya Listrik</option>
                                            <option value="900">900 VA</option>
                                            <option value="1300">1300 VA</option>
                                            <option value="2200">2200 VA</option>
                                            <option value="3500">3500 VA</option>
                                            <option value="4400">4400 VA</option>
                                            <option value="5500">> 5500 VA</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Sumber Air</label>
                                        <select v-model="form.sumber_air" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                            <option value="PDAM">PDAM</option>
                                            <option value="Sumur Bor">Sumur Bor / Tanah</option>
                                        </select>
                                    </div>
                                </template>

                                <!-- Kapasitas Parkir: Tampil untuk semua Hunian kecuali Hotel -->
                                <div v-if="form.jenis_properti !== 'Hotel'">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kapasitas Parkir (Mobil/Motor)</label>
                                    <input v-model="form.kapasitas_parkir" type="text" placeholder="Contoh: 10 Mobil & 20 Motor" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>

                                <!-- Kapasitas Maksimal Tamu: Hanya tampil jika properti TIDAK memiliki pembagian kamar/unit builder -->
                                <div v-if="!isTipeKamarProperti">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kapasitas Maksimal Tamu</label>
                                    <input v-model="form.kapasitas_orang" type="number" placeholder="Contoh: 6 Orang" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>

                                <!-- Khusus Villa: Pemandangan / View -->
                                <div v-if="form.jenis_properti === 'Villa'">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Pemandangan / View</label>
                                    <select v-model="form.pemandangan" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                        <option value="Pantai">Pantai</option>
                                        <option value="Pegunungan">Pegunungan / Bukit</option>
                                        <option value="Hutan">Hutan / Alam</option>
                                        <option value="Kota">Pemandangan Kota</option>
                                        <option value="Danau">Danau / Sungai</option>
                                    </select>
                                </div>

                                <!-- Khusus Hotel: Bintang, Waktu Check-in, Waktu Check-out -->
                                <template v-if="form.jenis_properti === 'Hotel'">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Bintang Hotel</label>
                                        <select v-model="form.bintang" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                            <option value="1">Bintang 1</option>
                                            <option value="2">Bintang 2</option>
                                            <option value="3">Bintang 3</option>
                                            <option value="4">Bintang 4</option>
                                            <option value="5">Bintang 5</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Waktu Check-in</label>
                                        <input v-model="form.waktu_checkin" type="time" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Waktu Check-out</label>
                                        <input v-model="form.waktu_checkout" type="time" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                    </div>
                                </template>

                                <!-- Khusus Kos-kosan: Aturan Jam Malam, Parkir Motor -->
                                <template v-if="form.jenis_properti === 'Kos-kosan'">
                                    <div class="flex items-center gap-2 mt-2">
                                        <input type="checkbox" id="aturan_jam_malam" v-model="form.aturan_jam_malam" class="w-4 h-4 text-[#0A2540] rounded border-slate-300" />
                                        <label for="aturan_jam_malam" class="text-xs text-slate-700 cursor-pointer">Ada Aturan Jam Malam</label>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <input type="checkbox" id="parkir_motor" v-model="form.parkir_motor" class="w-4 h-4 text-[#0A2540] rounded border-slate-300" />
                                        <label for="parkir_motor" class="text-xs text-slate-700 cursor-pointer">Tersedia Parkir Motor</label>
                                    </div>
                                </template>
                            </template>

                            <!-- (B) Komersial & Usaha -->
                            <template v-if="['Ruko (Rumah Toko)', 'Kios / Lapak Pasar', 'Kantor / Workspace', 'Gedung Komersial', 'Food Court / Booth'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Bangunan (m²)</label>
                                    <input v-model="form.luas_bangunan" type="number" placeholder="Contoh: 50" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Lantai</label>
                                    <input v-model="form.jumlah_lantai" type="number" placeholder="Contoh: 1" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Daya Listrik (VA)</label>
                                    <select v-model="form.daya_listrik" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                        <option value="">Pilih Daya Listrik</option>
                                        <option value="900">900 VA</option>
                                        <option value="1300">1300 VA</option>
                                        <option value="2200">2200 VA</option>
                                        <option value="3500">3500 VA</option>
                                        <option value="4400">4400 VA</option>
                                        <option value="11000">> 11000 VA</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kamar Mandi Dalam</label>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <label class="flex items-center gap-1 cursor-pointer text-xs">
                                            <input type="radio" v-model="form.kamar_mandi_dalam" :value="true" class="w-3.5 h-3.5 text-[#0A2540]" /> Ya
                                        </label>
                                        <label class="flex items-center gap-1 cursor-pointer text-xs">
                                            <input type="radio" v-model="form.kamar_mandi_dalam" :value="false" class="w-3.5 h-3.5 text-[#0A2540]" /> Tidak
                                        </label>
                                    </div>
                                </div>

                                <template v-if="['Kantor / Workspace', 'Gedung Komersial'].includes(form.jenis_properti)">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Kapasitas Orang</label>
                                        <input v-model="form.kapasitas_orang" type="number" placeholder="Contoh: 50" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tinggi Plafon (Meter)</label>
                                        <input v-model="form.tinggi_plafon" type="number" placeholder="Contoh: 3" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                    </div>
                                </template>
                            </template>

                            <!-- (C) Penyimpanan & Industri -->
                            <template v-if="['Gudang Logistik', 'Pabrik / Manufaktur', 'Cold Storage'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Tanah (m²)</label>
                                    <input v-model="form.luas_tanah" type="number" placeholder="Total Luas Tanah" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Bangunan (m²)</label>
                                    <input v-model="form.luas_bangunan" type="number" placeholder="Luas Area Tertutup" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tinggi Plafon / Langit-langit (m)</label>
                                    <input v-model="form.tinggi_plafon" type="number" placeholder="Contoh: 10" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tahun Dibangun</label>
                                    <input v-model="form.tahun_dibangun" type="number" placeholder="Contoh: 2015" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>

                            <!-- (D) Tanah & Lahan Kosong -->
                            <template v-if="['Lahan / Tanah Kosong', 'Lahan Pertanian / Perkebunan'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Tanah (m²)</label>
                                    <input v-model="form.luas_tanah" type="number" placeholder="Contoh: 5000" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Sertifikat Kepemilikan</label>
                                    <select v-model="form.sertifikat" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                        <option value="SHM">SHM (Sertifikat Hak Milik)</option>
                                        <option value="HGB">HGB (Hak Guna Bangunan)</option>
                                        <option value="AJB">AJB (Akta Jual Beli)</option>
                                        <option value="Girik">Girik / Petok D</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kontur / Topografi Tanah</label>
                                    <select v-model="form.kontur_tanah" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                        <option value="Datar">Datar</option>
                                        <option value="Miring">Miring / Bergelombang</option>
                                        <option value="Bukit">Berbukit / Terasering</option>
                                    </select>
                                </div>
                            </template>

                            <!-- (E) Media Iklan -->
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
                                    <input v-model="form.dimensi" type="text" placeholder="Contoh: 4x8 meter" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Sisi Tampil</label>
                                    <select v-model="form.sisi_baliho" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                        <option value="1">1 Sisi (Hanya dari satu arah)</option>
                                        <option value="2">2 Sisi (Bisa dilihat dari 2 arah)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Orientasi</label>
                                    <select v-model="form.orientasi_baliho" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition">
                                        <option value="Horizontal">Horizontal (Landscape)</option>
                                        <option value="Vertical">Vertikal (Portrait)</option>
                                    </select>
                                </div>
                                <div class="col-span-1 sm:col-span-2 flex items-center gap-2 mt-2">
                                    <input type="checkbox" id="penerangan_baliho" v-model="form.penerangan_baliho" class="w-4 h-4 text-[#0A2540] rounded border-slate-300" />
                                    <label for="penerangan_baliho" class="text-xs text-slate-700 cursor-pointer">Tersedia Penerangan (Lampu Sorot) untuk Malam Hari</label>
                                </div>
                                <div v-if="form.sub_kategori_baliho === 'Elektronik'">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Resolusi Layar</label>
                                    <input v-model="form.resolusi_layar" type="text" placeholder="Contoh: 1920x1080 px" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>

                        </div>

                        <!-- 2. BUILDER TIPE KAMAR / UNIT: Ditampilkan di bawah detail spesifik aset jika jenis properti memiliki unit -->
                        <div v-if="isTipeKamarProperti" class="space-y-3 border-t border-slate-200 pt-4 mt-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold text-[#0A2540]">Tipe Kamar / Unit <span class="text-rose-500">*</span></label>
                                <button type="button" @click="tambahTipeKamar" class="text-[10px] font-bold text-white bg-[#0A2540] hover:bg-[#123e6b] px-2.5 py-1.5 rounded-lg transition cursor-pointer">
                                    + Tambah Tipe Kamar
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 -mt-2">
                                Tambahkan tiap tipe kamar yang tersedia (mis. Standard, Deluxe, Suite) beserta jumlah unit, kapasitas, dan fasilitasnya masing-masing.
                            </p>

                            <div v-for="(tipe, tIndex) in form.tipe_kamar" :key="tipe.id" class="bg-white border border-slate-200 rounded-xl p-3.5 relative space-y-3 shadow-xs">
                                <button
                                    v-if="form.tipe_kamar.length > 1" type="button" @click.prevent="hapusTipeKamar(tIndex)"
                                    class="absolute top-3 right-3 w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-[10px] cursor-pointer"
                                    title="Hapus Tipe Kamar"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pr-8">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Nama Tipe Kamar <span class="text-rose-500">*</span></label>
                                        <input v-model="tipe.nama_tipe_kamar" type="text" placeholder="Contoh: Deluxe Room" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Jumlah Unit</label>
                                        <input v-model="tipe.jumlah_kamar" type="number" placeholder="Contoh: 5" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Kapasitas Orang / Kamar</label>
                                        <input v-model="tipe.kapasitas_orang" type="number" placeholder="Contoh: 2" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-1.5">Fasilitas Tipe Kamar Ini</label>

                                    <div class="relative kamar-fasilitas-dropdown">
                                        <button
                                            type="button"
                                            @click.stop="toggleFasilitasKamarDropdown(tIndex)"
                                            class="w-full text-[11px] px-3 py-2 rounded-xl border border-slate-200 flex items-center justify-between hover:border-[#0A2540] transition cursor-pointer bg-white"
                                        >
                                            <span :class="tipe.fasilitas_kamar.length > 0 ? 'font-bold text-[#0A2540]' : 'text-slate-400'">
                                                {{ tipe.fasilitas_kamar.length > 0 ? `${tipe.fasilitas_kamar.length} fasilitas dipilih` : 'Pilih Fasilitas Kamar' }}
                                            </span>
                                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition" :class="{ 'rotate-180': fasilitasKamarDropdownOpen === tIndex }"></i>
                                        </button>

                                        <div
                                            v-if="fasilitasKamarDropdownOpen === tIndex"
                                            class="absolute z-20 mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto"
                                        >
                                            <button
                                                type="button" v-for="item in fasilitasByKategori[0].items" :key="item"
                                                @click.stop="toggleFasilitasKamar(tIndex, item)"
                                                class="w-full flex items-center gap-2.5 px-3 py-2 text-[11px] text-left hover:bg-slate-50 transition cursor-pointer"
                                            >
                                                <span
                                                    class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition"
                                                    :class="tipe.fasilitas_kamar.includes(item) ? 'bg-[#0A2540] border-[#0A2540]' : 'border-slate-300 bg-white'"
                                                >
                                                    <i v-if="tipe.fasilitas_kamar.includes(item)" class="fa-solid fa-check text-[9px] text-white"></i>
                                                </span>
                                                <span :class="tipe.fasilitas_kamar.includes(item) ? 'font-bold text-[#0A2540]' : 'text-slate-600'">
                                                    {{ item }}
                                                </span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- CHIP FASILITAS KAMAR TERPILIH -->
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
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Aset</label>
                        <textarea
                            v-model="form.deskripsi" rows="4" placeholder="Jelaskan keunggulan aset Anda (strategis, fasilitas lengkap, akses jalan mudah, dll.)"
                            class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        ></textarea>
                    </div>

                    <!-- FASILITAS UMUM ASET -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fasilitas Umum / Kelengkapan Aset <span class="text-rose-500">*</span></label>
                        <p class="text-[10px] text-slate-400 mb-2">
                            Pilih fasilitas umum yang tersedia pada aset Anda secara keseluruhan (bukan fasilitas di dalam kamar, jika ada).
                        </p>

                        <div class="relative" ref="fasilitasDropdownRef">
                            <!-- TOMBOL UTAMA -->
                            <button
                                type="button"
                                @click.stop="toggleFasilitasDropdown"
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 flex items-center justify-between hover:border-[#0A2540] transition cursor-pointer bg-white"
                            >
                                <span :class="form.fasilitas.length > 0 ? 'font-bold text-[#0A2540]' : 'text-slate-400'">
                                    {{ form.fasilitas.length > 0 ? `${form.fasilitas.length} fasilitas dipilih` : 'Pilih Fasilitas Umum Aset' }}
                                </span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition" :class="{ 'rotate-180': fasilitasDropdownOpen }"></i>
                            </button>

                            <!-- DROPDOWN UTAMA: LIST JENIS FASILITAS -->
                            <div
                                v-if="fasilitasDropdownOpen"
                                class="absolute z-30 mt-1.5 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-80 overflow-y-auto"
                            >
                                <div v-for="grup in fasilitasByKategori" :key="grup.kategori" class="border-b border-slate-100 last:border-0">

                                    <!-- HEADER JENIS FASILITAS -->
                                    <button
                                        type="button"
                                        @click.stop="toggleSubKategoriFasilitas(grup.kategori)"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                                    >
                                        <span class="flex items-center gap-2">
                                            <i :class="['fa-solid', grup.icon, 'text-[#FFC000] text-[11px] w-3.5 text-center']"></i>
                                            <span>{{ grup.kategori }}</span>
                                            <span
                                                v-if="countFasilitasTerpilih(grup) > 0"
                                                class="text-[10px] bg-[#0A2540] text-white font-bold px-1.5 py-0.5 rounded-full leading-none"
                                            >
                                                {{ countFasilitasTerpilih(grup) }}
                                            </span>
                                        </span>
                                        <i
                                            class="fa-solid fa-chevron-right text-[10px] text-slate-400 transition"
                                            :class="{ 'rotate-90 text-[#0A2540]': activeFasilitasKategori === grup.kategori }"
                                        ></i>
                                    </button>

                                    <!-- SUB-DROPDOWN NAMA FASILITAS -->
                                    <div v-if="activeFasilitasKategori === grup.kategori" class="bg-slate-50 border-t border-slate-100">
                                        <button
                                            type="button"
                                            v-for="item in grup.items" :key="item"
                                            @click.stop="toggleFasilitas(item)"
                                            class="w-full flex items-center gap-2.5 pl-8 pr-3.5 py-2 text-[11px] text-left hover:bg-white transition cursor-pointer"
                                        >
                                            <span
                                                class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition"
                                                :class="form.fasilitas.includes(item) ? 'bg-[#0A2540] border-[#0A2540]' : 'border-slate-300 bg-white'"
                                            >
                                                <i v-if="form.fasilitas.includes(item)" class="fa-solid fa-check text-[9px] text-white"></i>
                                            </span>
                                            <span :class="form.fasilitas.includes(item) ? 'font-bold text-[#0A2540]' : 'text-slate-600'">
                                                {{ item }}
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <!-- TAMBAH FASILITAS MANUAL -->
                                <div class="p-2.5 bg-slate-50 border-t border-slate-100">
                                    <div class="flex gap-1.5">
                                        <input
                                            v-model="inputFasilitasKustom"
                                            @keyup.enter.prevent="tambahFasilitasKustom"
                                            @click.stop
                                            type="text"
                                            placeholder="Fasilitas tidak ada di daftar? Ketik di sini..."
                                            class="flex-1 text-[11px] px-2.5 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                                        />
                                        <button
                                            type="button"
                                            @click.stop="tambahFasilitasKustom"
                                            class="bg-[#0A2540] text-white text-[11px] font-bold px-3 rounded-lg hover:bg-[#123e6b] transition cursor-pointer shrink-0"
                                        >
                                            Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CHIP FASILITAS TERPILIH -->
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


</template>
