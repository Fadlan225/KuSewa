<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

// --- KELOMPOK KATEGORI & JENIS PROPERTI ---
const kategoriPropertiGroups = [
    {
        label: 'Hunian & Tempat Tinggal',
        options: ['Kos-kosan', 'Rumah Tapak', 'Villa', 'Homestay', 'Apartemen', 'Guest House', 'Rusun / Condominium']
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

    // Step 2: Lokasi & Fasilitas
    alamat_lengkap: '',
    kota: 'Samarinda',
    kecamatan: '',
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

// Otomatis ubah nilai 'jenis_properti' ke opsi pertama tiap kali 'kategori' diubah
watch(() => form.kategori, (newKategori) => {
    const group = kategoriPropertiGroups.find(g => g.label === newKategori);
    if (group && group.options.length > 0) {
        form.jenis_properti = group.options[0];
    }
});

// Navigation & Modal State
const currentStep = ref(1);
const showSuccessModal = ref(false);

// --- LOGIKA FASILITAS ---
// (tidak berubah — lihat komentar di bawah untuk detail)
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

const fasilitasPopuler = [
    { id: 'Wi-Fi / Internet', icon: 'fa-wifi' },
    { id: 'AC (Pendingin)', icon: 'fa-snowflake' },
    { id: 'Area Parkir Luas', icon: 'fa-square-parking' },
    { id: 'Keamanan / CCTV 24 Jam', icon: 'fa-shield-halved' },
    { id: 'Termasuk Listrik & Air', icon: 'fa-bolt' },
    { id: 'Furnished Lengkap', icon: 'fa-couch' },
    { id: 'Akses 24 Jam', icon: 'fa-clock' },
    { id: 'Kamar Mandi Dalam', icon: 'fa-bath' }
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
const showSaran = ref(false);

const saranFasilitas = computed(() => {
    const keyword = inputFasilitasKustom.value.trim().toLowerCase();

    if (!keyword) {
        const items = fasilitasPopuler.map(f => f.id).filter(f => !form.fasilitas.includes(f));
        return items.length > 0 ? [{ kategori: 'Populer', icon: 'fa-star', items }] : [];
    }

    const hasilAlias = Object.keys(aliasFasilitas)
        .filter(alias => alias.includes(keyword))
        .map(alias => aliasFasilitas[alias]);

    const hasil = [];
    fasilitasByKategori.forEach(grup => {
        const cocok = [...new Set(
            grup.items.filter(item => item.toLowerCase().includes(keyword) || hasilAlias.includes(item))
        )].filter(item => !form.fasilitas.includes(item));

        if (cocok.length > 0) {
            hasil.push({ kategori: grup.kategori, icon: grup.icon, items: cocok.slice(0, 5) });
        }
    });
    return hasil;
});

const toggleFasilitas = (id) => {
    const index = form.fasilitas.indexOf(id);
    if (index === -1) {
        form.fasilitas.push(id);
    } else {
        form.fasilitas.splice(index, 1);
    }
};

const pilihSaranFasilitas = (item) => {
    if (!form.fasilitas.includes(item)) {
        form.fasilitas.push(item);
    }
    inputFasilitasKustom.value = '';
    showSaran.value = false;
};

const tambahFasilitasKustom = () => {
    const raw = inputFasilitasKustom.value.trim();
    if (!raw) return;

    const nilaiFinal = koreksiPenulisanFasilitas(raw);

    if (!form.fasilitas.includes(nilaiFinal)) {
        form.fasilitas.push(nilaiFinal);
    }
    inputFasilitasKustom.value = '';
    showSaran.value = false;
};

const hapusFasilitas = (fas) => {
    const index = form.fasilitas.indexOf(fas);
    if (index > -1) form.fasilitas.splice(index, 1);
};

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
const submitProperty = () => {
    form.post(route('owner.property.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
        },
        onError: (errors) => {
            console.error('Validasi Gagal:', errors);
        }
    });
};

const closeModalAndRedirect = () => {
    showSuccessModal.value = false;
    router.visit('/owner/property');
};
</script>

<template>
    <Head title="Ajukan Properti Baru - kusewa.id" />

    <div class="min-h-screen bg-[#F3F5F8] text-slate-700 font-sans flex flex-col antialiased selection:bg-[#FFC000]/30">

        <!-- TOPBAR NAVIGATION -->
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <Link href="/owner/property" class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center text-xs hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </Link>
                <div class="flex items-center gap-1.5">
                    <span class="font-black text-xl tracking-tight text-[#0A2540]">
                        kusewa<span class="text-[#FFC000]">.id</span>
                    </span>
                    <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-md ml-2">Tambah Properti</span>
                </div>
            </div>
            <div class="text-xs text-slate-400 hidden sm:block">
                Butuh bantuan? <a href="#" class="text-[#0A2540] font-bold hover:underline">Hubungi CS kusewa</a>
            </div>
        </header>

        <!-- MAIN CONTAINER -->
        <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 space-y-6">

            <!-- HEADER TITLE -->
            <div>
                <h1 class="text-xl font-bold text-slate-900">Ajukan Properti Baru</h1>
                <p class="text-xs text-slate-500 mt-1">Lengkapi data aset Anda agar calon penyewa bisa melihat unit Anda di platform kusewa.id</p>
            </div>

            <!-- STEPPER PROGRESS BAR -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between relative">
                    <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
                    <div
                        class="absolute top-1/2 left-0 h-0.5 bg-[#0A2540] -translate-y-1/2 z-0 transition-all duration-300"
                        :style="{ width: currentStep === 1 ? '0%' : currentStep === 2 ? '50%' : '100%' }"
                    ></div>

                    <div class="relative z-10 flex items-center gap-2 bg-white pr-2">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition', currentStep >= 1 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400']">1</div>
                        <span class="text-xs font-bold hidden sm:inline" :class="currentStep >= 1 ? 'text-slate-900' : 'text-slate-400'">Informasi Utama</span>
                    </div>

                    <div class="relative z-10 flex items-center gap-2 bg-white px-2">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition', currentStep >= 2 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400']">2</div>
                        <span class="text-xs font-bold hidden sm:inline" :class="currentStep >= 2 ? 'text-slate-900' : 'text-slate-400'">Lokasi & Fasilitas</span>
                    </div>

                    <div class="relative z-10 flex items-center gap-2 bg-white pl-2">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition', currentStep === 3 ? 'bg-[#0A2540] text-[#FFC000]' : 'bg-slate-100 text-slate-400']">3</div>
                        <span class="text-xs font-bold hidden sm:inline" :class="currentStep === 3 ? 'text-slate-900' : 'text-slate-400'">Harga & Foto</span>
                    </div>
                </div>
            </div>

            <!-- FORM CARD -->
            <form @submit.prevent="submitProperty" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">

                <!-- STEP 1: INFORMASI UTAMA -->
                <div v-if="currentStep === 1" class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-[#0A2540]"></i>
                        <span>Informasi Dasar Aset</span>
                    </h2>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Properti / Unit <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.nama_properti" type="text" placeholder="Contoh: Villa Melati Eksklusif Samarinda Kota"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition" required
                        />
                    </div>

                    <!-- DROPDOWN KATEGORI & JENIS (CASCADING / TERPISAH) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Utama <span class="text-rose-500">*</span></label>
                            <select v-model="form.kategori" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option v-for="group in kategoriPropertiGroups" :key="group.label" :value="group.label">
                                    {{ group.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Properti <span class="text-rose-500">*</span></label>
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div v-if="['Kos-kosan', 'Apartemen'].includes(form.jenis_properti)">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Kamar</label>
                                <input v-model="form.jumlah_kamar" type="number" placeholder="Berapa kamar?" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                            </div>

                            <template v-if="['Rumah Tapak', 'Villa', 'Homestay', 'Guest House', 'Rusun / Condominium'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Kamar</label>
                                    <input v-model="form.jumlah_kamar" type="number" placeholder="Jumlah kamar tidur" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kapasitas Maks Orang</label>
                                    <input v-model="form.kapasitas_orang" type="number" placeholder="Contoh: 6 orang" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Lantai</label>
                                    <input v-model="form.jumlah_lantai" type="number" placeholder="Contoh: 2" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>

                            <template v-if="['Ruko (Rumah Toko)', 'Kios / Lapak Pasar', 'Kantor / Workspace', 'Gedung Komersial', 'Food Court / Booth'].includes(form.jenis_properti)">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Luas Bangunan (m²)</label>
                                    <input v-model="form.luas_bangunan" type="number" placeholder="Contoh: 50" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Lantai</label>
                                    <input v-model="form.jumlah_lantai" type="number" placeholder="Contoh: 1" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
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
                                    <input v-model="form.luas_tanah" type="number" placeholder="Total luas" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
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
                                    <input v-model="form.dimensi" type="text" placeholder="Contoh: 4x8 meter" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition" />
                                </div>
                            </template>

                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Properti</label>
                        <textarea
                            v-model="form.deskripsi" rows="4" placeholder="Jelaskan keunggulan aset Anda (strategis, fasilitas lengkap, akses jalan mudah, dll.)"
                            class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        ></textarea>
                    </div>
                </div>

                <!-- STEP 2: LOKASI & FASILITAS -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-[#0A2540]"></i>
                        <span>Detail Lokasi & Fasilitas</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                            <select v-model="form.kota" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition">
                                <option value="Samarinda">Samarinda</option>
                                <option value="Balikpapan">Balikpapan</option>
                                <option value="Tenggarong">Tenggarong (Kutai Kartanegara)</option>
                                <option value="Bontang">Bontang</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan <span class="text-rose-500">*</span></label>
                            <input v-model="form.kecamatan" type="text" placeholder="Contoh: Samarinda Ulu" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 transition" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <input v-model="form.alamat_lengkap" type="text" placeholder="Jl. M. Yamin No. 45, RT 12" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 transition" required />
                    </div>

                    <!-- FASILITAS: SEARCH BERKATEGORI + CHIP POPULER RINGKAS -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fasilitas / Kelengkapan Aset</label>
                        <p class="text-[10px] text-slate-400 mb-2">
                            Cari atau ketik fasilitas apa saja (mis. "wifi", "genset", "sertifikat") — hasil dikelompokkan per kategori, dan penulisan otomatis disamakan ke format standar meski ada typo.
                        </p>

                        <div class="flex gap-2 relative">
                            <div class="flex-1 relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-[11px]"></i>
                                <input
                                    v-model="inputFasilitasKustom"
                                    @keyup.enter.prevent="tambahFasilitasKustom"
                                    @focus="showSaran = true"
                                    @blur="() => setTimeout(() => showSaran = false, 150)"
                                    type="text"
                                    placeholder="Cari atau ketik fasilitas..."
                                    class="w-full text-xs pl-8 pr-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540]"
                                />
                                <div
                                    v-if="showSaran && saranFasilitas.length > 0"
                                    class="absolute z-20 top-full left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden max-h-64 overflow-y-auto"
                                >
                                    <div v-for="grup in saranFasilitas" :key="grup.kategori">
                                        <div class="sticky top-0 bg-slate-50 px-3.5 py-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wide flex items-center gap-1.5 border-b border-slate-100">
                                            <i :class="['fa-solid', grup.icon, 'text-[#FFC000] text-[10px]']"></i>
                                            <span>{{ grup.kategori }}</span>
                                        </div>
                                        <button
                                            type="button"
                                            v-for="item in grup.items" :key="item"
                                            @mousedown.prevent="pilihSaranFasilitas(item)"
                                            class="w-full text-left text-xs px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 cursor-pointer transition border-b border-slate-50 last:border-0"
                                        >
                                            <i class="fa-solid fa-plus text-[10px] text-[#FFC000] shrink-0"></i>
                                            <span>{{ item }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click.prevent="tambahFasilitasKustom" class="bg-[#0A2540] text-white text-xs font-bold px-4 rounded-xl hover:bg-[#123e6b] transition cursor-pointer shrink-0">
                                Tambah
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mt-2.5">
                            <button
                                type="button" v-for="item in fasilitasPopuler" :key="item.id" @click="toggleFasilitas(item.id)"
                                :class="['text-[11px] px-2.5 py-1.5 rounded-full border flex items-center gap-1.5 transition cursor-pointer', form.fasilitas.includes(item.id) ? 'border-[#0A2540] bg-[#0A2540]/5 font-bold text-[#0A2540]' : 'border-slate-200 text-slate-500 hover:bg-slate-50']"
                            >
                                <i :class="['fa-solid', item.icon, 'text-[10px]', form.fasilitas.includes(item.id) ? 'text-[#0A2540]' : 'text-slate-400']"></i>
                                <span>{{ item.id }}</span>
                            </button>
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

                <!-- STEP 3: HARGA & FOTO BERDASARKAN KATEGORI RUANGAN/AREA -->
                <div v-if="currentStep === 3" class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-tags text-[#0A2540]"></i>
                        <span>Harga Sewa & Upload Foto Kategori</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Sewa (Rp) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input v-model="form.harga_sewa" type="number" placeholder="1500000" class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 transition" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Uang Deposit (Opsional)</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input v-model="form.deposit" type="number" placeholder="200000" class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 transition" />
                            </div>
                        </div>
                    </div>

                    <!-- FOTO BERDASARKAN KATEGORI RUANGAN/AREA -->
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700">Foto Berdasarkan Kategori Ruangan / Sudut Pandang</label>
                                <p class="text-[10px] text-slate-400">Pilih kategori dari daftar, dan unggah foto (bisa lebih dari satu file sekaligus)</p>
                            </div>
                            <button type="button" @click="tambahKategoriFoto" class="text-[11px] font-bold text-[#0A2540] hover:text-[#FFC000] bg-slate-100 px-3 py-1.5 rounded-lg transition shrink-0 cursor-pointer">
                                + Tambah Kategori Ruangan
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(kategori, index) in form.foto_properti" :key="kategori.id" class="border border-slate-200 rounded-2xl p-4 relative group bg-white shadow-xs">

                                <button v-if="form.foto_properti.length > 1" @click.prevent="hapusKategoriFoto(index)" class="absolute top-3 right-3 w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-xs cursor-pointer" title="Hapus Kategori">
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Kategori Ruangan / Area</label>
                                        <!-- Dropdown standar, bukan input teks bebas — mencegah typo & variasi penulisan antar owner -->
                                        <select
                                            v-model="kategori.nama_ruangan_pilihan"
                                            @change="pilihKategoriFoto(index)"
                                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition"
                                            required
                                        >
                                            <option value="" disabled>Pilih kategori ruangan/area</option>
                                            <option v-for="opt in daftarKategoriFoto" :key="opt" :value="opt">{{ opt }}</option>
                                        </select>

                                        <!-- Muncul hanya jika pilih "Lainnya", untuk kasus yang belum ada di daftar -->
                                        <input
                                            v-if="kategori.nama_ruangan_pilihan === 'Lainnya'"
                                            v-model="kategori.nama_ruangan"
                                            type="text"
                                            placeholder="Tulis nama kategori ruangan/area"
                                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition mt-2"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih File Foto (Bisa Banyak Sekaligus)</label>
                                        <input type="file" multiple accept="image/png, image/jpeg, image/webp" @change="handleFileUpload($event, index)" class="w-full text-[11px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-[#0A2540] file:text-white hover:file:bg-[#123e6b] cursor-pointer" />
                                        <p class="text-[10px] text-slate-400 mt-1">Tahan Ctrl/Cmd untuk pilih beberapa foto sekaligus, atau klik lagi untuk menambah foto lain.</p>
                                    </div>
                                </div>

                                <!-- PREVIEW FOTO -->
                                <div v-if="kategori.previews.length > 0" class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
                                    <div v-for="(preview, fileIndex) in kategori.previews" :key="fileIndex" class="relative group/photo">
                                        <img :src="preview" class="w-16 h-16 object-cover rounded-xl border border-slate-200 shadow-xs" />
                                        <button @click.prevent="hapusFoto(index, fileIndex)" class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-xs opacity-0 group-hover/photo:opacity-100 transition cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="text-[10px] text-slate-400 mt-2">Belum ada foto yang diunggah untuk kategori ini.</div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- BUTTON ACTIONS FOOTER -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" @click="prevStep" v-if="currentStep > 1" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                        Kembali
                    </button>
                    <div v-else></div>

                    <button type="button" @click="nextStep" v-if="currentStep < 3" class="bg-[#0A2540] hover:bg-[#123e6b] text-white text-xs font-bold px-6 py-2.5 rounded-xl transition flex items-center gap-2 cursor-pointer">
                        <span>Lanjut Langkah {{ currentStep + 1 }}</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </button>

                    <button type="submit" v-if="currentStep === 3" :disabled="form.processing" class="bg-[#FFC000] hover:bg-[#e6ad00] disabled:opacity-50 text-[#0A2540] text-xs font-black px-6 py-2.5 rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>{{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan Properti' }}</span>
                    </button>
                </div>

            </form>
        </main>

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
    </div>
</template>