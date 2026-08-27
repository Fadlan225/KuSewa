<script setup>
import { CloudUpload, X, Images, Trash2, Info, ChevronRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';
import TipsModal from './TipsModal.vue';
import NoImageIcon from '@/Components/ui/Icons/NoImageIcon.vue';

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

const showTipsModal = ref(false);
const fileErrors = ref({});

const isValidFile = (file) => {
    return ['image/jpeg', 'image/png', 'image/jpg'].includes(file.type);
};

const validateAndUpload = (event, index, isUnit = false, unitIndex = null, photoIdx = null) => {
    const files = Array.from(event.target.files);
    let valid = true;
    
    for (const file of files) {
        if (!isValidFile(file)) {
            valid = false;
            break;
        }
    }
    
    const errorKey = isUnit ? `unit_${unitIndex}_photo_${photoIdx}` : `foto_${index}`;
    
    if (!valid) {
        fileErrors.value[errorKey] = 'Format foto tidak didukung. Harap unggah format JPG, JPEG, atau PNG.';
        event.target.value = null;
        return;
    }
    
    fileErrors.value[errorKey] = null;
    
    if (isUnit) {
        emit('handleUnitFileUpload', event, unitIndex, photoIdx);
    } else {
        emit('handleFileUpload', event, index);
    }
};

const validateAndUploadThumbnail = (event, isUnit = false, unitIndex = null) => {
    const file = event.target.files[0];
    const errorKey = isUnit ? `unit_${unitIndex}_thumbnail` : `thumbnail`;
    
    if (file && !isValidFile(file)) {
        fileErrors.value[errorKey] = 'Format foto tidak didukung. Harap unggah format JPG, JPEG, atau PNG.';
        event.target.value = null;
        return;
    }
    
    fileErrors.value[errorKey] = null;
    
    if (isUnit) {
        emit('handleUnitThumbnailUpload', event, unitIndex);
    } else {
        emit('handleThumbnailUpload', event);
    }
};
</script>

<template>
<div class="space-y-6">
    <!-- Modal Tips -->
    <TipsModal :show="showTipsModal" @close="showTipsModal = false" />

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
        
        <!-- Banner Tips -->
        <div @click="showTipsModal = true" class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-center justify-between cursor-pointer hover:bg-blue-100 transition-colors shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                    <Info :size="20" />
                </div>
                <div>
                    <h4 class="font-bold text-blue-900 text-sm">Perhatian, 4 tips foto ini buat iklan makin menarik dan cepat tayang!</h4>
                    <p class="text-xs text-blue-700 mt-0.5">Klik untuk melihat panduan lengkap foto yang baik.</p>
                </div>
            </div>
            <ChevronRight class="text-blue-500 shrink-0" />
        </div>

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
                    accept="image/png, image/jpeg, image/jpg"
                    @change="validateAndUploadThumbnail($event)"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                />
                <NoImageIcon class="w-12 h-12 mx-auto text-slate-300 mb-3 opacity-50" />
                <p class="text-sm font-semibold text-[#0A2540]">Unggah atau tarik foto sampul ke sini</p>
                <p class="text-xs text-slate-500 mt-1.5">Format foto harus JPG, JPEG, atau PNG.</p>
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
            <p v-if="fileErrors['thumbnail']" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['thumbnail'] }}</p>
        </div>

        <!-- FOTO ASET BERDASARKAN KATEGORI -->
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-5 mt-4">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Album Foto Aset</label>
                    <p class="text-xs text-slate-500 mt-1">
                        <template v-if="hasGalleryCategories">Unggah foto sesuai kategori. Kategori wajib ditandai bintang merah.</template>
                        <template v-else>Kategori foto belum dikonfigurasi.</template>
                    </p>
                </div>
                <button
                    v-if="hasGalleryCategories"
                    type="button"
                    @click="emit('tambahKategoriFoto')"
                    class="text-sm font-semibold text-[#0A2540] hover:text-white bg-[#0A2540]/10 hover:bg-[#0A2540] px-4 py-2 rounded-md transition shrink-0 cursor-pointer shadow-sm"
                >
                    + Foto Tambahan
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
                    <!-- Hapus grup foto (Hanya jika BUKAN mandatory dan ada lebih dari 1 total) -->
                    <button
                        v-if="!fotoGroup.is_mandatory && form.photos.length > 1"
                        @click.prevent="emit('hapusKategoriFoto', index)"
                        class="absolute top-4 right-4 w-8 h-8 rounded-md bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-sm cursor-pointer"
                        title="Hapus Album"
                    >
                        <Trash2 class="" />
                    </button>

                    <div class="mb-2">
                        <!-- Judul Jika Wajib -->
                        <div v-if="fotoGroup.is_mandatory" class="mb-3">
                            <label class="block text-sm font-bold text-slate-800">Foto {{ fotoGroup.gallery_category_name }} <span class="text-rose-500">*</span></label>
                            <p class="text-xs text-slate-500 mt-0.5">Pastikan area/fasilitas {{ fotoGroup.gallery_category_name.toLowerCase() }} terlihat dengan jelas.</p>
                        </div>
                        
                        <!-- Pilihan Dropdown Jika Tidak Wajib -->
                        <div v-else class="mb-3 max-w-sm">
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pilih Kategori <span class="text-rose-500">*</span></label>
                            <SearchableSelect
                                v-model="fotoGroup.gallery_category_id"
                                :options="galleryCategoriesOptions"
                                placeholder="Pilih kategori area..."
                            />
                        </div>

                        <!-- Area Upload (Dropzone Baru) -->
                        <div class="border-2 border-dashed border-slate-300 bg-slate-50/50 rounded-lg p-6 text-center hover:bg-slate-100 transition cursor-pointer relative mt-2 w-full max-w-3xl">
                            <input
                                type="file"
                                multiple
                                accept="image/png, image/jpeg, image/jpg"
                                @change="validateAndUpload($event, index)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            />
                            <NoImageIcon class="w-12 h-12 mx-auto text-slate-300 mb-3 opacity-50" />
                            <p class="text-sm font-semibold text-[#0A2540]">Unggah atau tarik foto-foto ke sini</p>
                            <p class="text-xs text-slate-500 mt-1.5">Format foto harus JPG, JPEG, atau PNG.</p>
                        </div>
                        <p v-if="fileErrors['foto_' + index]" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['foto_' + index] }}</p>
                    </div>

                    <!-- PREVIEW FOTO -->
                    <div v-if="fotoGroup.previews && fotoGroup.previews.length > 0" class="flex flex-wrap gap-3 pt-4 border-t border-slate-100 mt-4">
                        <div
                            v-for="(preview, fileIndex) in fotoGroup.previews"
                            :key="fileIndex"
                            class="relative group/photo"
                        >
                            <img :src="preview" class="w-20 h-20 object-cover rounded-lg border border-slate-200 shadow-sm" />
                            <button
                                @click.prevent="emit('hapusFoto', index, fileIndex)"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm opacity-0 group-hover/photo:opacity-100 transition cursor-pointer"
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

                    <div v-if="!unit.thumbnail_preview" class="border-2 border-dashed border-slate-300 bg-slate-50 rounded-lg p-5 text-center hover:bg-slate-100 transition cursor-pointer relative max-w-sm">
                        <input
                            type="file"
                            accept="image/png, image/jpeg, image/jpg"
                            @change="validateAndUploadThumbnail($event, true, unitIndex)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        />
                        <NoImageIcon class="w-10 h-10 mx-auto text-slate-300 mb-2 opacity-50" />
                        <p class="text-xs font-semibold text-[#0A2540]">Unggah sampul tipe unit ini</p>
                        <p class="text-[10px] text-slate-500 mt-1">JPG, JPEG, PNG.</p>
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
                    <p v-if="fileErrors['unit_' + unitIndex + '_thumbnail']" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['unit_' + unitIndex + '_thumbnail'] }}</p>
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

                            <div class="mb-2">
                                <div class="mb-3 max-w-sm">
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pilih Area <span class="text-rose-500">*</span></label>
                                    <SearchableSelect
                                        v-model="photoGroup.gallery_category_id"
                                        :options="galleryCategoriesOptions"
                                        placeholder="Pilih area..."
                                    />
                                </div>
                                <div class="border-2 border-dashed border-slate-300 bg-white rounded-lg p-5 text-center hover:bg-slate-100 transition cursor-pointer relative mt-2 w-full max-w-2xl">
                                    <input
                                        type="file"
                                        multiple
                                        accept="image/png, image/jpeg, image/jpg"
                                        @change="validateAndUpload($event, null, true, unitIndex, photoIdx)"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                    <NoImageIcon class="w-10 h-10 mx-auto text-slate-300 mb-2 opacity-50" />
                                    <p class="text-sm font-semibold text-[#0A2540]">Unggah foto-foto unit ke sini</p>
                                    <p class="text-[10px] text-slate-500 mt-1">JPG, JPEG, PNG.</p>
                                </div>
                                <p v-if="fileErrors['unit_' + unitIndex + '_photo_' + photoIdx]" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['unit_' + unitIndex + '_photo_' + photoIdx] }}</p>
                            </div>

                            <!-- Preview foto unit -->
                            <div v-if="photoGroup.previews && photoGroup.previews.length > 0" class="flex flex-wrap gap-2 pt-4 border-t border-slate-100 mt-4">
                                <div
                                    v-for="(preview, fileIdx) in photoGroup.previews"
                                    :key="fileIdx"
                                    class="relative group/photo"
                                >
                                    <img :src="preview" class="w-16 h-16 object-cover rounded-lg border border-slate-200" />
                                    <button
                                        type="button"
                                        @click="emit('hapusUnitFoto', unitIndex, photoIdx, fileIdx)"
                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow opacity-0 group-hover/photo:opacity-100 transition cursor-pointer"
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
