<script setup>
import { computed } from 'vue';

const props = defineProps({
    form: Object,
    allowUnits: Boolean,
    assetTypeDetails: Object, // { gallery_categories: [{id, name, applies_to}] }
});

const emit = defineEmits(['tambahKategoriFoto', 'hapusKategoriFoto', 'handleFileUpload', 'hapusFoto']);

// Gallery categories untuk aset (applies_to = 'asset') — dari DB via API
const galleryCategoriesAsset = computed(() =>
    (props.assetTypeDetails?.gallery_categories ?? [])
        .filter(gc => gc.applies_to === 'asset')
);

// Jika tidak ada galery_categories dari DB, tampilkan pesan info
const hasGalleryCategories = computed(() => galleryCategoriesAsset.value.length > 0);

// Rental unit label untuk tooltip harga
const rentalUnitLabel = computed(() => {
    const map = {
        hour: '/Jam',
        night: '/Malam',
        day: '/Hari',
        month: '/Bulan',
    };
    return map[props.assetTypeDetails?.rental_unit] ?? '';
});
</script>

<template>
<div class="space-y-5">
<!-- STEP 3: HARGA & FOTO -->
    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
        <i class="fa-solid fa-tags text-[#0A2540]"></i>
        <span>Harga Sewa &amp; Upload Foto Aset</span>
    </h2>

    <!-- ============================================ -->
    <!-- HARGA — Tanpa Unit (satu harga untuk aset) -->
    <!-- ============================================ -->
    <div v-if="!allowUnits">
        <label class="block text-xs font-bold text-slate-700 mb-1">
            Harga Sewa (Rp{{ rentalUnitLabel }}) <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
            <input
                v-model="form.price"
                type="number"
                min="0"
                placeholder="1500000"
                class="w-full text-xs pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                required
            />
        </div>
        <p class="text-[10px] text-slate-400 mt-1">Harga sewa per {{ rentalUnitLabel.replace('/', '') || 'periode' }}.</p>
    </div>

    <!-- ============================================ -->
    <!-- HARGA — Dengan Unit (rangkuman dari Step 1) -->
    <!-- ============================================ -->
    <div v-if="allowUnits">
        <div class="flex items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-check text-emerald-500 text-[12px]"></i>
            <p class="text-[11px] text-slate-600 font-semibold">Harga & unit sudah diatur di Step 1. Berikut ringkasannya:</p>
        </div>

        <!-- Preview ringkasan unit + harga -->
        <div class="space-y-2">
            <div
                v-for="(unit, i) in form.units"
                :key="unit._id"
                class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-4 py-3"
            >
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-[#0A2540]/10 flex items-center justify-center text-[10px] font-black text-[#0A2540]">
                        {{ i + 1 }}
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">{{ unit.name || `Unit ${i + 1}` }}</p>
                        <p class="text-[10px] text-slate-400">{{ unit.quantity }} unit tersedia</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-black text-[#0A2540]">
                        Rp {{ unit.price ? Number(unit.price).toLocaleString('id-ID') : '—' }}
                        <span class="text-[10px] font-normal text-slate-400">{{ rentalUnitLabel }}</span>
                    </p>
                    <p v-if="!unit.price || Number(unit.price) <= 0" class="text-[10px] text-rose-500 font-bold mt-0.5">
                        ⚠ Harga belum diisi
                    </p>
                </div>
            </div>
        </div>

        <!-- Warning jika ada unit tanpa harga -->
        <div v-if="form.units.some(u => !u.price || Number(u.price) <= 0)"
             class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm mt-0.5"></i>
            <p class="text-[11px] text-rose-600 font-semibold">
                Ada unit yang belum memiliki harga sewa. Kembali ke <strong>Step 1</strong> untuk mengisi harga setiap unit.
            </p>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- FOTO BERDASARKAN GALERI KATEGORI (dari DB) -->
    <!-- ============================================ -->
    <div class="mt-4 border-t border-slate-100 pt-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <label class="block text-xs font-bold text-slate-700">Foto Aset berdasarkan Kategori</label>
                <p class="text-[10px] text-slate-400">
                    <template v-if="hasGalleryCategories">Pilih kategori foto, lalu unggah foto (bisa lebih dari satu).</template>
                    <template v-else>Kategori foto belum dikonfigurasi untuk jenis aset ini. Silakan hubungi admin.</template>
                </p>
            </div>
            <button
                v-if="hasGalleryCategories"
                type="button"
                @click="emit('tambahKategoriFoto')"
                class="text-[11px] font-bold text-[#0A2540] hover:text-[#FFC000] bg-slate-100 px-3 py-1.5 rounded-lg transition shrink-0 cursor-pointer"
            >
                + Tambah Kategori
            </button>
        </div>

        <!-- Jika tidak ada galery_categories untuk jenis aset ini -->
        <div v-if="!hasGalleryCategories && assetTypeDetails" class="py-6 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-slate-200">
            <i class="fa-solid fa-images text-2xl text-slate-200 block mb-2"></i>
            Kategori galeri foto belum dikonfigurasi untuk jenis aset ini.
            <br>Anda masih bisa melanjutkan pengajuan tanpa foto.
        </div>

        <!-- Daftar foto per kategori -->
        <div v-if="hasGalleryCategories" class="space-y-4">
            <div
                v-for="(fotoGroup, index) in form.photos"
                :key="fotoGroup._id"
                class="border border-slate-200 rounded-2xl p-4 relative group bg-white shadow-xs"
            >
                <!-- Hapus grup foto -->
                <button
                    v-if="form.photos.length > 1"
                    @click.prevent="emit('hapusKategoriFoto', index)"
                    class="absolute top-3 right-3 w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-xs cursor-pointer"
                    title="Hapus Kategori Foto"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                    <!-- Dropdown Kategori Foto (dari DB) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Kategori / Area Foto <span class="text-rose-500">*</span></label>
                        <select
                            v-model="fotoGroup.gallery_category_id"
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:border-[#0A2540] transition"
                            required
                        >
                            <option :value="null" disabled>Pilih kategori foto</option>
                            <option
                                v-for="gc in galleryCategoriesAsset"
                                :key="gc.id"
                                :value="gc.id"
                            >
                                {{ gc.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Upload File Foto -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih File Foto (Bisa Banyak)</label>
                        <input
                            type="file"
                            multiple
                            accept="image/png, image/jpeg, image/webp"
                            @change="emit('handleFileUpload', $event, index)"
                            class="w-full text-[11px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-[#0A2540] file:text-white hover:file:bg-[#123e6b] cursor-pointer"
                        />
                        <p class="text-[10px] text-slate-400 mt-1">Tahan Ctrl untuk pilih beberapa foto sekaligus.</p>
                    </div>
                </div>

                <!-- PREVIEW FOTO -->
                <div v-if="fotoGroup.previews && fotoGroup.previews.length > 0" class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
                    <div
                        v-for="(preview, fileIndex) in fotoGroup.previews"
                        :key="fileIndex"
                        class="relative group/photo"
                    >
                        <img :src="preview" class="w-16 h-16 object-cover rounded-xl border border-slate-200 shadow-xs" />
                        <button
                            @click.prevent="emit('hapusFoto', index, fileIndex)"
                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-xs opacity-0 group-hover/photo:opacity-100 transition cursor-pointer"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div v-else class="text-[10px] text-slate-400 mt-2">
                    Belum ada foto yang diunggah untuk kategori ini.
                </div>
            </div>
        </div>
    </div>
</div>
</template>
