<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue';

const props = defineProps({
    property: {
        type: Object,
        required: true,
    },
});

const jenisPropertiDenganTipeKamar = ['Kos-kosan', 'Hotel', 'Apartemen', 'Guest House', 'Rusun / Condominium'];
const isTipeKamarProperti = (props.property.tipe_kamar?.length > 0);
const isBaliho = ['Baliho / Reklame', 'Billboard / Videotron', 'Neon Box / Titik Toko'].includes(props.property.jenis_properti);

const verificationLabel = (status) => ({
    pending: 'Menunggu Verifikasi',
    approved: 'Terverifikasi',
    rejected: 'Ditolak',
}[status] || 'Menunggu Verifikasi');

const verificationClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected: 'bg-rose-50 text-rose-700 border-rose-200',
}[status] || 'bg-amber-50 text-amber-700 border-amber-200');

const verificationIcon = (status) => ({
    pending: 'fa-clock',
    approved: 'fa-circle-check',
    rejected: 'fa-circle-xmark',
}[status] || 'fa-clock');

const statusClass = (status) => ({
    'Tersewa': 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'Tersedia': 'bg-blue-50 text-blue-700 border-blue-200',
    'Maintenance': 'bg-amber-50 text-amber-700 border-amber-200',
}[status] || 'bg-blue-50 text-blue-700 border-blue-200');

// Satu status publik: selama belum disetujui admin, aset belum tersedia.
const statusTampilan = (property) => property.verification_status !== 'approved'
    ? 'Belum Tersedia'
    : (property.status || 'Tersedia');

const statusTampilanClass = (property) => property.verification_status !== 'approved'
    ? 'bg-amber-50 text-amber-700 border-amber-200'
    : statusClass(property.status);

const placeholderImage = 'https://placehold.co/800x500?text=Belum+Ada+Foto';

// Ambil foto pertama dari kategori manapun sebagai foto utama/hero
const heroImage = (() => {
    const kategoriDenganFoto = (props.property.foto_properti || []).find(k => (k.photos || []).length > 0);
    if (!kategoriDenganFoto) return null;
    const foto = kategoriDenganFoto.photos[0];
    return typeof foto === 'string' ? foto : foto.url;
})();

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatRupiah = (nilai) => `Rp ${Number(nilai || 0).toLocaleString('id-ID')}`;

const fotoUrl = (foto) => typeof foto === 'string' ? foto : foto.url;
</script>

<template>
    <Head :title="`${property.nama_properti} - kusewa.id`" />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30">

        <Sidebar />

        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- TOPBAR HEADER -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                    <Link href="/owner/dashboard" class="hover:text-[#0A2540] transition">Dashboard</Link>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                    <Link href="/owner/property" class="hover:text-[#0A2540] transition">Daftar Properti & Aset</Link>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                    <span class="text-slate-800 font-bold line-clamp-1 max-w-[220px]">{{ property.nama_properti }}</span>
                </div>

                <Link
                    :href="`/owner/property/${property.id}/edit`"
                    class="bg-[#0A2540] hover:bg-[#14385f] active:scale-95 text-white font-bold px-4 py-2 rounded-xl shadow-md transition flex items-center gap-2 text-xs"
                >
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                    <span>Edit Unit</span>
                </Link>
            </header>

            <!-- PAGE CONTENT -->
            <div class="p-6 md:p-8 space-y-6 max-w-[1100px] w-full mx-auto">

                <Link href="/owner/property" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-[#0A2540] transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Kembali ke Daftar Properti
                </Link>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                    <!-- LEFT: Foto Utama & Deskripsi -->
                    <div class="lg:col-span-3 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <div class="relative h-72 bg-slate-100">
                                <img :src="heroImage || placeholderImage" :alt="property.nama_properti" class="w-full h-full object-cover" />
                                <div class="absolute top-3 left-3">
                                    <span class="bg-[#0A2540]/90 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                                        {{ property.kategori }}
                                    </span>
                                </div>
                                <div class="absolute top-3 right-3">
                                    <span :class="['text-[10px] font-black px-2.5 py-1 rounded-lg shadow-xs border backdrop-blur-md', statusTampilanClass(property)]">
                                        {{ statusTampilan(property) }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h1 class="text-lg font-black text-slate-900 leading-snug">{{ property.nama_properti }}</h1>
                                    <span :class="['text-[10px] font-bold px-2.5 py-1 rounded-full border shrink-0', statusTampilanClass(property)]">
                                        {{ statusTampilan(property) }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-[#FFC000]"></i>
                                    {{ property.alamat_lengkap }}<span v-if="property.kecamatan">, {{ property.kecamatan }}</span><span v-if="property.kota">, {{ property.kota }}</span>
                                </p>

                                <p v-if="property.verification_status === 'rejected' && property.verification_note"
                                   class="text-xs text-rose-600 bg-rose-50 rounded-lg p-3">
                                    <span class="font-bold block mb-1">Catatan admin:</span>
                                    {{ property.verification_note }}
                                </p>
                            </div>
                        </div>

                        <!-- INFORMASI PROPERTI -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-4">Informasi Properti</h3>
                            <div class="grid grid-cols-2 gap-4 text-xs">
                                <div><span class="text-slate-400 block">Kategori</span><b>{{ property.kategori || '-' }}</b></div>
                                <div><span class="text-slate-400 block">Jenis Properti</span><b>{{ property.jenis_properti || '-' }}</b></div>
                                <div><span class="text-slate-400 block">Skema Pembayaran</span><b>{{ property.tipe_sewa || '-' }}</b></div>
                                <div><span class="text-slate-400 block">Deposit</span><b>{{ formatRupiah(property.deposit) }}</b></div>

                                <template v-if="isBaliho">
                                    <div><span class="text-slate-400 block">Jenis Tampilan</span><b>{{ property.sub_kategori_baliho || '-' }}</b></div>
                                    <div><span class="text-slate-400 block">Dimensi</span><b>{{ property.dimensi || '-' }}</b></div>
                                </template>
                                <template v-else-if="!isTipeKamarProperti">
                                    <div v-if="property.jumlah_kamar"><span class="text-slate-400 block">Jumlah Kamar</span><b>{{ property.jumlah_kamar }}</b></div>
                                    <div v-if="property.kapasitas_orang"><span class="text-slate-400 block">Kapasitas</span><b>{{ property.kapasitas_orang }} orang</b></div>
                                    <div v-if="property.jumlah_lantai"><span class="text-slate-400 block">Jumlah Lantai</span><b>{{ property.jumlah_lantai }}</b></div>
                                    <div v-if="property.luas_tanah || property.luas_bangunan"><span class="text-slate-400 block">Luas Tanah / Bangunan</span><b>{{ property.luas_tanah || '-' }} / {{ property.luas_bangunan || '-' }} m²</b></div>
                                </template>
                            </div>
                            <div v-if="property.deskripsi" class="border-t border-slate-100 mt-4 pt-4 text-xs">
                                <span class="text-slate-400 block mb-1">Deskripsi</span>{{ property.deskripsi }}
                            </div>
                        </div>

                        <!-- TIPE KAMAR (khusus Kos-kosan, Hotel, Apartemen, Guest House, Rusun) -->
                        <div v-if="isTipeKamarProperti" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Tipe Kamar / Unit</h3>
                            <div v-for="tipe in property.tipe_kamar" :key="tipe.nama_tipe_kamar" class="border border-slate-100 rounded-xl p-3.5 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-[#0A2540]">{{ tipe.nama_tipe_kamar }}</span>
                                    <span class="text-[10px] text-slate-400">{{ tipe.jumlah_kamar || '-' }} unit · {{ tipe.kapasitas_orang || '-' }} orang/kamar</span>
                                </div>
                                <div v-if="tipe.fasilitas_kamar?.length" class="flex flex-wrap gap-1.5">
                                    <span v-for="item in tipe.fasilitas_kamar" :key="item" class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full">{{ item }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- FASILITAS -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-4">Fasilitas</h3>
                            <div v-if="property.fasilitas?.length" class="flex flex-wrap gap-2">
                                <span v-for="item in property.fasilitas" :key="item" class="text-[11px] bg-slate-100 px-2.5 py-1 rounded-full">{{ item }}</span>
                            </div>
                            <span v-else class="text-xs text-slate-400">Belum ada fasilitas.</span>
                        </div>

                        <!-- GALERI FOTO PER KATEGORI -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-4">Galeri Foto</h3>
                            <div v-if="property.foto_properti?.length" class="space-y-4">
                                <div v-for="kategori in property.foto_properti" :key="kategori.nama_ruangan" v-show="(kategori.photos || []).length > 0">
                                    <p class="text-[11px] font-bold text-slate-600 mb-2">{{ kategori.nama_ruangan }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        <img v-for="(foto, i) in kategori.photos" :key="i" :src="fotoUrl(foto)" class="w-20 h-20 object-cover rounded-xl border border-slate-200" />
                                    </div>
                                </div>
                            </div>
                            <span v-else class="text-xs text-slate-400">Belum ada foto yang diunggah.</span>
                        </div>
                    </div>

                    <!-- RIGHT: Info Cards -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-1">
                            <span class="text-[11px] font-medium text-slate-400 block">Harga Sewa</span>
                            <span class="text-2xl font-black text-[#0A2540]">
                                {{ formatRupiah(property.harga_sewa) }}
                                <span class="text-xs font-normal text-slate-400">/{{ property.tipe_sewa }}</span>
                            </span>
                        </div>

                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Detail Aset</h3>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Jenis Properti</span>
                                <span class="font-bold text-slate-700 text-right">{{ property.jenis_properti || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Kategori</span>
                                <span class="font-bold text-slate-700">{{ property.kategori || '-' }}</span>
                            </div>
                            <div v-if="property.status === 'Tersewa' && property.tenant" class="flex items-center justify-between text-xs pt-3 border-t border-slate-100">
                                <span class="text-slate-400">Penyewa</span>
                                <span class="font-bold text-slate-700">{{ property.tenant }}</span>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-2">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-1">Lokasi</h3>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Negara</span>
                                <span class="font-bold text-slate-700">{{ property.negara || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Provinsi</span>
                                <span class="font-bold text-slate-700">{{ property.provinsi || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Kota / Kabupaten</span>
                                <span class="font-bold text-slate-700">{{ property.kota || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Kecamatan</span>
                                <span class="font-bold text-slate-700">{{ property.kecamatan || '-' }}</span>
                            </div>
                            <div v-if="property.latitude && property.longitude" class="text-[10px] text-slate-400 pt-2 border-t border-slate-100">
                                Titik koordinat: {{ property.latitude }}, {{ property.longitude }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                :href="`/owner/property/${property.id}/edit`"
                                class="flex-1 bg-[#0A2540] hover:bg-[#14385f] text-white font-bold px-4 py-2.5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-xs"
                            >
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                Edit Unit
                            </Link>
                            <button class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 rounded-xl transition" title="Hapus Unit">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>