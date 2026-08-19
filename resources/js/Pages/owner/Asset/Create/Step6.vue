<script setup>
import { CloudUpload, X, Images, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import SearchableSelect from '@/Components/UI/SearchableSelect.vue';

const props = defineProps({
    form: Object,
    allowUnits: Boolean,
    assetTypeDetails: Object, // { gallery_categories }
});

const emit = defineEmits([
    'tambahKategoriFoto', 'hapusKategoriFoto', 'handleFileUpload', 'hapusFoto',
    'handleThumbnailUpload', 'hapusThumbnail',
    'tambahUnitKategoriFoto', 'hapusUnitKategoriFoto', 'handleUnitFileUpload', 'hapusUnitFoto',
    'handleUnitThumbnailUpload', 'hapusUnitThumbnail'
]);

// Gallery categories bersifat GLOBAL — semua kategori tersedia untuk dipilih
const galleryCategoriesAsset = computed(() => props.assetTypeDetails?.gallery_categories ?? []);
const hasGalleryCategories = computed(() => galleryCategoriesAsset.value.length > 0);

const galleryCategoriesOptions = computed(() => {
    return galleryCategoriesAsset.value.map(gc => ({
        code: gc.id,
        name: gc.name
    }));
});
</script>

<template>
<div class="space-y-6">
    <!-- STEP 6: GALERI MEDIA -->
    <div class="border-b border-slate-200 pb-4">
        <h2 class="text-lg font-bold text-slate-800">
            Galeri & Foto Aset
        </h2>
        <p class="text-sm text-slate-500 mt-2">Unggah foto terbaik dari aset Anda untuk menarik minat calon penyewa.</p>
    </div>

    <!-- ============================================ -->
    <!-- GALERI ASET UTAMA -->
    <!-- ============================================ -->
    <div class="space-y-4">
        <h3 class="text-sm font-semibold text-slate-700 border-l-4 border-[#FFC000] pl-3">Foto Aset Utama</h3>

        <!-- THUMBNAIL ASET -->
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">
            <label class="block text-sm font-semibold text-slate-700 mb-3">
                Foto Sampul Utama <span class="text-rose-500">*</span>
                <span class="text-xs text-slate-500 font-normal ml-1">(Akan ditampilkan paling depan)</span>
            </label>

            <div v-if="!form.thumbnail_preview" class="border-2 border-dashed border-slate-300 bg-white rounded-lg p-6 text-center hover:bg-slate-100 transition cursor-pointer relative max-w-sm">
                <input
                    type="file"
                    accept="image/png, image/jpeg, image/webp"
                    @change="emit('handleThumbnailUpload', $event)"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                />
                <CloudUpload class="text-3xl text-[#0A2540] mb-3 opacity-50" />
                <p class="text-sm font-semibold text-[#0A2540]">Klik untuk upload foto sampul</p>
                <p class="text-xs text-slate-500 mt-1.5">Format: JPG, PNG, WEBP (Maks. 5MB)</p>
            </div>
            <div v-else class="relative w-48 h-32 group/thumb">
                <img :src="form.thumbnail_preview" class="w-full h-full object-cover rounded-lg border-2 border-[#0A2540]" />
                <button
                    type="button"
                    @click="emit('hapusThumbnail')"
                    class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow opacity-0 group-hover/thumb:opacity-100 transition cursor-pointer"
                    title="Hapus Thumbnail"
                >
                    <X class="" />
                </button>
            </div>
        </div>

        <!-- FOTO ASET BERDASARKAN KATEGORI -->
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-5 mt-4">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Album Foto Aset</label>
                    <p class="text-xs text-slate-500 mt-1">
                        <template v-if="hasGalleryCategories">Pisahkan foto berdasarkan ruangan/area (misal: Tampak Depan, Lobi, Parkiran).</template>
                        <template v-else>Kategori foto belum dikonfigurasi.</template>
                    </p>
                </div>
                <button
                    v-if="hasGalleryCategories"
                    type="button"
                    @click="emit('tambahKategoriFoto')"
                    class="text-sm font-semibold text-[#0A2540] hover:text-white bg-[#0A2540]/10 hover:bg-[#0A2540] px-4 py-2 rounded-md transition shrink-0 cursor-pointer shadow-sm"
                >
                    + Tambah Album
                </button>
            </div>

            <!-- Jika tidak ada galery_categories untuk jenis aset ini -->
            <div v-if="!hasGalleryCategories && assetTypeDetails" class="py-6 text-center text-sm text-slate-500 bg-white rounded-lg border border-slate-200 shadow-sm">
                <Images class="text-3xl text-slate-300 block mb-3" />
                Kategori galeri foto belum dikonfigurasi untuk jenis aset ini.
            </div>

            <!-- Daftar foto per kategori -->
            <div v-if="hasGalleryCategories" class="space-y-4">
                <div
                    v-for="(fotoGroup, index) in form.photos"
                    :key="fotoGroup._id"
                    class="border border-slate-200 rounded-lg p-5 relative group bg-white shadow-sm"
                >
                    <!-- Hapus grup foto -->
                    <button
                        v-if="form.photos.length > 1"
                        @click.prevent="emit('hapusKategoriFoto', index)"
                        class="absolute top-4 right-4 w-8 h-8 rounded-md bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-sm cursor-pointer"
                        title="Hapus Album"
                    >
                        <Trash2 class="" />
                    </button>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Area/Ruangan <span class="text-rose-500">*</span></label>
                            <SearchableSelect
                                v-model="fotoGroup.gallery_category_id"
                                :options="galleryCategoriesOptions"
                                placeholder="Pilih kategori area..."
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pilih File Foto (Bisa Banyak)</label>
                            <input
                                type="file"
                                multiple
                                accept="image/png, image/jpeg, image/webp"
                                @change="emit('handleFileUpload', $event, index)"
                                class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0A2540] file:text-white hover:file:bg-[#123e6b] cursor-pointer"
                            />
                        </div>
                    </div>

                    <!-- PREVIEW FOTO -->
                    <div v-if="fotoGroup.previews && fotoGroup.previews.length > 0" class="flex flex-wrap gap-2 pt-3 border-t border-slate-100 mt-2">
                        <div
                            v-for="(preview, fileIndex) in fotoGroup.previews"
                            :key="fileIndex"
                            class="relative group/photo"
                        >
                            <img :src="preview" class="w-16 h-16 object-cover rounded-lg border border-slate-200 shadow-sm" />
                            <button
                                @click.prevent="emit('hapusFoto', index, fileIndex)"
                                class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm opacity-0 group-hover/photo:opacity-100 transition cursor-pointer"
                            >
                                <X class="" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- GALERI UNIT (Jika Allow Units) -->
    <!-- ============================================ -->
    <div v-if="allowUnits" class="space-y-4 pt-6 border-t border-slate-200">
        <h3 class="text-sm font-semibold text-slate-700 border-l-4 border-[#FFC000] pl-3">Foto Tiap Tipe Unit</h3>
        <p class="text-sm text-slate-500 mb-4">Karena properti Anda memiliki tipe unit, unggah foto spesifik untuk masing-masing tipe unit tersebut di bawah ini.</p>

        <div
            v-for="(unit, unitIndex) in form.units"
            :key="unit._id"
            class="bg-white border border-slate-300 rounded-lg p-6 shadow-sm"
        >
            <div class="flex items-center gap-3 mb-5 border-b border-slate-200 pb-4">
                <div class="w-8 h-8 rounded-md bg-[#FFC000] text-[#0A2540] font-bold flex items-center justify-center text-sm">
                    {{ unitIndex + 1 }}
                </div>
                <h4 class="text-sm font-semibold text-slate-800">{{ unit.name || `Tipe Unit ${unitIndex + 1}` }}</h4>
            </div>

            <div class="space-y-6">
                <!-- THUMBNAIL UNIT -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">
                        Foto Sampul Unit
                    </label>

                    <div v-if="!unit.thumbnail_preview" class="border-2 border-dashed border-slate-300 bg-slate-50 rounded-lg p-5 text-center hover:bg-slate-100 transition cursor-pointer relative max-w-xs">
                        <input
                            type="file"
                            accept="image/png, image/jpeg, image/webp"
                            @change="emit('handleUnitThumbnailUpload', $event, unitIndex)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        />
                        <CloudUpload class="text-2xl text-slate-400 mb-3" />
                        <p class="text-xs font-semibold text-slate-500">Upload sampul tipe unit ini</p>
                    </div>
                    <div v-else class="relative w-32 h-24 group/thumb">
                        <img :src="unit.thumbnail_preview" class="w-full h-full object-cover rounded-lg border border-slate-300 shadow-sm" />
                        <button
                            type="button"
                            @click="emit('hapusUnitThumbnail', unitIndex)"
                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow opacity-0 group-hover/thumb:opacity-100 transition cursor-pointer"
                            title="Hapus Thumbnail"
                        >
                            <X class="" />
                        </button>
                    </div>
                </div>

                <!-- FOTO UNIT (upload per kategori) -->
                <div v-if="galleryCategoriesAsset.length > 0">
                    <div class="flex items-center justify-between mb-4 mt-5 pt-5 border-t border-slate-200">
                        <label class="block text-sm font-semibold text-slate-700">
                            Album Foto Unit
                        </label>
                        <button
                            type="button"
                            @click="emit('tambahUnitKategoriFoto', unitIndex)"
                            class="text-sm font-semibold text-[#0A2540] hover:text-white bg-[#0A2540]/10 hover:bg-[#0A2540] px-4 py-2 rounded-md transition cursor-pointer shadow-sm"
                        >
                            + Tambah Album Unit
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(photoGroup, photoIdx) in unit.photos"
                            :key="photoGroup._id"
                            class="border border-slate-300 rounded-lg p-5 relative bg-slate-50"
                        >
                            <button
                                v-if="unit.photos.length > 1"
                                type="button"
                                @click="emit('hapusUnitKategoriFoto', unitIndex, photoIdx)"
                                class="absolute top-3 right-3 w-8 h-8 rounded-md bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-sm cursor-pointer"
                                title="Hapus Kategori Foto"
                            >
                                <Trash2 class="" />
                            </button>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Area <span class="text-rose-500">*</span></label>
                                    <SearchableSelect
                                        v-model="photoGroup.gallery_category_id"
                                        :options="galleryCategoriesOptions"
                                        placeholder="Pilih area..."
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pilih File Foto</label>
                                    <input
                                        type="file"
                                        multiple
                                        accept="image/png, image/jpeg, image/webp"
                                        @change="emit('handleUnitFileUpload', $event, unitIndex, photoIdx)"
                                        class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0A2540] file:text-white hover:file:bg-[#123e6b] cursor-pointer"
                                    />
                                </div>
                            </div>

                            <!-- Preview foto unit -->
                            <div v-if="photoGroup.previews && photoGroup.previews.length > 0" class="flex flex-wrap gap-1.5 pt-2 border-t border-slate-100 mt-2">
                                <div
                                    v-for="(preview, fileIdx) in photoGroup.previews"
                                    :key="fileIdx"
                                    class="relative group/photo"
                                >
                                    <img :src="preview" class="w-14 h-14 object-cover rounded-lg border border-slate-200" />
                                    <button
                                        type="button"
                                        @click="emit('hapusUnitFoto', unitIndex, photoIdx, fileIdx)"
                                        class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white rounded-full flex items-center justify-center text-[9px] shadow opacity-0 group-hover/photo:opacity-100 transition cursor-pointer"
                                    >
                                        <X class="" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
