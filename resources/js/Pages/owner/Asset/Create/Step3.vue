<script setup>
import { computed, watch, ref } from 'vue';

const props = defineProps({ form: Object });
const form = props.form;

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


</script>

<template>
<div class="space-y-4">
<!-- STEP 3: HARGA & FOTO BERDASARKAN KATEGORI RUANGAN/AREA -->
    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
        <i class="fa-solid fa-tags text-[#0A2540]"></i>
        <span>Harga Sewa & Upload Foto Aset</span>
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
</template>
