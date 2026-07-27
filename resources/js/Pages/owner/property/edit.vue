<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue';

const props = defineProps({
    property: {
        type: Object,
        required: true
    }
});

// Options untuk Select
const categories = ['Kos', 'Apartemen', 'Rumah Kontrakan', 'Kendaraan'];
const rentPeriods = ['Hari', 'Bulan', 'Tahun'];
const statuses = ['Tersedia', 'Tersewa', 'Maintenance'];

// Opsi tipe dinamis berdasarkan kategori
const typeOptions = {
    'Kos': ['Putra', 'Putri', 'Campur'],
    'Apartemen': ['Studio', '1BR', '2BR', '3BR', 'Campur'],
    'Rumah Kontrakan': ['Keluarga', 'Pasutri', 'Umum'],
    'Kendaraan': ['Mobil', 'Motor']
};

// Inisialisasi Form dengan data dari backend
const form = useForm({
    title: props.property.title || '',
    category: props.property.category || 'Kos',
    type: props.property.type || '',
    price: props.property.price || 0,
    rent_period: props.property.rent_period || 'Bulan',
    city: props.property.city || '',
    address: props.property.address || '',
    status: props.property.status || 'Tersedia',
    tenant: props.property.tenant || '',
    image: null,
    _method: 'PUT' // Digunakan jika nanti kirim file via FormData di Laravel
});

// Preview Gambar
const imagePreview = ref(props.property.image || null);

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

// Reset tipe jika kategori berubah
watch(() => form.category, (newCategory) => {
    if (typeOptions[newCategory] && !typeOptions[newCategory].includes(form.type)) {
        form.type = typeOptions[newCategory][0] || '';
    }
});

// Submit Form
const submit = () => {
    // Karena menggunakan Method Spoofing untuk penanganan file upload di Laravel
    form.post(route('owner.property.update', props.property.id), {
        preserveScroll: true,
        onError: (errors) => {
            console.error(errors);
        }
    });
};
</script>

<template>
    <Head :title="`Edit ${form.title || 'Properti'} - kusewa.id`" />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30">

        <!-- SIDEBAR -->
        <Sidebar />

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- TOPBAR HEADER -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                    <Link href="/owner/dashboard" class="hover:text-[#0A2540] transition">Dashboard</Link>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                    <Link href="/owner/property" class="hover:text-[#0A2540] transition">Daftar Properti</Link>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                    <span class="text-slate-800 font-bold">Edit Properti</span>
                </div>
            </header>

            <!-- PAGE CONTENT CONTAINER -->
            <div class="p-6 md:p-8 space-y-6 max-w-[1000px] w-full mx-auto">

                <!-- TITLE ROW -->
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Detail Properti</h1>
                        <p class="text-xs text-slate-400 mt-1">Perbarui informasi, harga, foto, atau status keterisian unit ini.</p>
                    </div>

                    <Link 
                        href="/owner/property" 
                        class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold px-4 py-2 rounded-xl transition flex items-center gap-2 text-xs"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Batal & Kembali</span>
                    </Link>
                </div>

                <!-- FORM CARD -->
                <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8 space-y-6">

                    <!-- SECTION 1: INFORMASI UTAMA -->
                    <div class="space-y-4">
                        <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-building text-[#FFC000]"></i>
                            <span>Informasi Dasar Unit</span>
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Judul Properti -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama / Judul Properti <span class="text-rose-500">*</span></label>
                                <input 
                                    v-model="form.title"
                                    type="text" 
                                    placeholder="Contoh: Kos Exclusive Samarinda Indah #01"
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition"
                                    :class="{ 'border-rose-500': form.errors.title }"
                                />
                                <span v-if="form.errors.title" class="text-[10px] text-rose-500 mt-1 block">{{ form.errors.title }}</span>
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Properti <span class="text-rose-500">*</span></label>
                                <select 
                                    v-model="form.category"
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition font-semibold"
                                >
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>

                            <!-- Tipe Properti -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Properti</label>
                                <select 
                                    v-model="form.type"
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition font-semibold"
                                >
                                    <option v-for="opt in typeOptions[form.category] || []" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: HARGA & STATUS -->
                    <div class="space-y-4 pt-2">
                        <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-tags text-[#FFC000]"></i>
                            <span>Harga & Status Penyewaan</span>
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Harga -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Sewa (Rp) <span class="text-rose-500">*</span></label>
                                <input 
                                    v-model="form.price"
                                    type="number" 
                                    placeholder="1500000"
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition"
                                    :class="{ 'border-rose-500': form.errors.price }"
                                />
                                <span v-if="form.errors.price" class="text-[10px] text-rose-500 mt-1 block">{{ form.errors.price }}</span>
                            </div>

                            <!-- Periode Sewa -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Skema Periode <span class="text-rose-500">*</span></label>
                                <select 
                                    v-model="form.rent_period"
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition font-semibold"
                                >
                                    <option v-for="period in rentPeriods" :key="period" :value="period">Per {{ period }}</option>
                                </select>
                            </div>

                            <!-- Status Unit -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Keterisian <span class="text-rose-500">*</span></label>
                                <select 
                                    v-model="form.status"
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition font-semibold"
                                >
                                    <option value="Tersedia">Tersedia (Kosong)</option>
                                    <option value="Tersewa">Tersewa</option>
                                    <option value="Maintenance">Dalam Perbaikan</option>
                                </select>
                            </div>

                            <!-- Nama Penyewa (Hanya tampil jika status Tersewa) -->
                            <div v-if="form.status === 'Tersewa'" class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penyewa Saat Ini</label>
                                <input 
                                    v-model="form.tenant"
                                    type="text" 
                                    placeholder="Masukkan nama penyewa..."
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: LOKASI -->
                    <div class="space-y-4 pt-2">
                        <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-map-location-dot text-[#FFC000]"></i>
                            <span>Lokasi Properti</span>
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                                <input 
                                    v-model="form.city"
                                    type="text" 
                                    placeholder="Samarinda"
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                                <input 
                                    v-model="form.address"
                                    type="text" 
                                    placeholder="Jl. M. Yamin No. 12, Kel. Gunung Kelua"
                                    class="w-full bg-slate-50 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:bg-white focus:border-[#0A2540] transition"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: FOTO PROPERTI -->
                    <div class="space-y-4 pt-2">
                        <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-image text-[#FFC000]"></i>
                            <span>Foto Properti</span>
                        </h2>

                        <div class="flex flex-col sm:flex-row items-start gap-5">
                            <!-- Image Preview -->
                            <div class="w-full sm:w-48 h-32 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden relative group shrink-0">
                                <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-image text-2xl"></i>
                                </div>
                            </div>

                            <!-- Upload Box -->
                            <div class="flex-1 w-full">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Ganti Foto Utama</label>
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    @change="handleImageUpload"
                                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
                                />
                                <p class="text-[11px] text-slate-400 mt-2">Format yang didukung: JPG, PNG, WEBP. Maksimal 2MB. Biarkan kosong jika tidak ingin mengubah foto.</p>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                        <Link 
                            href="/owner/property" 
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Batal
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-[#0A2540] hover:bg-[#14385f] active:scale-95 text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition text-xs flex items-center gap-2 disabled:opacity-50"
                        >
                            <i v-if="form.processing" class="fa-solid fa-spinner animate-spin"></i>
                            <i v-else class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>

                </form>

            </div>
        </main>

    </div>
</template>