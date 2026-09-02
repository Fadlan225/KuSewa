<script setup>
import { CloudUpload, X, Images, Trash2, Info, ChevronRight, ChevronUp, ChevronDown, Camera, Image, MoreVertical, Eye, ArrowRightLeft } from 'lucide-vue-next';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { computed, ref, onMounted, watch } from 'vue';
import TipsModal from './TipsModal.vue';
import NoImageIcon from '@/Components/ui/Icons/NoImageIcon.vue';
import NoImageIllustration from '@/Components/ui/Icons/NoImageIllustration.vue';

const props = defineProps({
    form: Object,
    assetTypeDetails: Object, // { gallery_categories }
    isActive: Boolean,
});

const emit = defineEmits([
    'tambahKategoriFoto', 'hapusKategoriFoto', 'handleFileUpload', 'hapusFoto',
    'handleThumbnailUpload', 'hapusThumbnail', 'gantiFoto', 'pindahkanFoto'
]);

// Gallery categories bersifat GLOBAL — semua kategori tersedia untuk dipilih
const galleryCategoriesAsset = computed(() => props.assetTypeDetails?.gallery_categories ?? []);
const hasGalleryCategories = computed(() => galleryCategoriesAsset.value.length > 0);

const showTipsModal = ref(false);
const hasShownTips = ref(false);
const fileErrors = ref({});

const gantiFotoInput = ref(null);
const replaceTarget = ref({ index: null, fileIndex: null });
const previewImageUrl = ref(null);

const openPreview = (url) => {
    previewImageUrl.value = url;
};
const closePreview = () => {
    previewImageUrl.value = null;
};

const draggedPhoto = ref(null);
const dragOverIndex = ref(null);

const onDragStart = (catIndex, fileIndex) => {
    draggedPhoto.value = { catIndex, fileIndex };
};

const onDrop = (targetCatIndex) => {
    dragOverIndex.value = null;
    if (!draggedPhoto.value) return;

    const { catIndex, fileIndex } = draggedPhoto.value;
    
    if (catIndex !== targetCatIndex) {
        emit('pindahkanFoto', catIndex, fileIndex, targetCatIndex);
    }
    
    draggedPhoto.value = null;
};

const triggerGantiFoto = (index, fileIndex) => {
    replaceTarget.value = { index, fileIndex };
    if (gantiFotoInput.value) {
        gantiFotoInput.value.click();
    }
};

const gantiThumbnailInput = ref(null);
const triggerGantiThumbnail = () => {
    if (gantiThumbnailInput.value) {
        gantiThumbnailInput.value.click();
    }
};

const onGantiFotoChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const { index, fileIndex } = replaceTarget.value;
    const errorKey = `foto_${index}`;

    if (!isValidFile(file)) {
        fileErrors.value[errorKey] = 'Format foto tidak didukung. Harap unggah format JPG, JPEG, atau PNG.';
        event.target.value = null;
        return;
    }

    fileErrors.value[errorKey] = null;
    emit('gantiFoto', event, index, fileIndex);
    event.target.value = null; // reset
};

watch(() => props.isActive, (active) => {
    if (active && !hasShownTips.value) {
        showTipsModal.value = true;
        hasShownTips.value = true;
    }
}, { immediate: true });

const isValidFile = (file) => {
    return ['image/jpeg', 'image/png', 'image/jpg'].includes(file.type);
};

const validateAndUpload = (event, index) => {
    const files = Array.from(event.target.files);
    let valid = true;

    for (const file of files) {
        if (!isValidFile(file)) {
            valid = false;
            break;
        }
    }

    const errorKey = `foto_${index}`;

    if (!valid) {
        fileErrors.value[errorKey] = 'Format foto tidak didukung. Harap unggah format JPG, JPEG, atau PNG.';
        event.target.value = null;
        return;
    }

    fileErrors.value[errorKey] = null;
    emit('handleFileUpload', event, index);
};

const validateAndUploadThumbnail = (event) => {
    const file = event.target.files[0];
    const errorKey = `thumbnail`;

    if (file && !isValidFile(file)) {
        fileErrors.value[errorKey] = 'Format foto tidak didukung. Harap unggah format JPG, JPEG, atau PNG.';
        event.target.value = null;
        return;
    }

    fileErrors.value[errorKey] = null;
    emit('handleThumbnailUpload', event);
};

// Helper to ensure all required categories are present
const ensureCategories = (photosArray) => {
    if (!photosArray) return;

    // 1. Ensure all mandatory categories exist
    const mandatoryCategories = props.assetTypeDetails?.mandatory_categories || [];
    mandatoryCategories.forEach(cat => {
        const exists = photosArray.find(p => p.gallery_category_id === cat.id);
        if (!exists) {
            photosArray.unshift({
                _id: Date.now() + Math.random(),
                gallery_category_id: cat.id,
                gallery_category_name: cat.name,
                is_mandatory: true,
                files: [],
                previews: []
            });
        }
    });

    // 2. Ensure "Lainnya" block exists if there are non-mandatory categories available
    const hasOtherInForm = photosArray.some(p => !p.is_mandatory);
    if (!hasOtherInForm) {
        photosArray.push({
            _id: Date.now() + Math.random(),
            gallery_category_id: null,
            gallery_category_name: 'Lainnya',
            is_mandatory: false,
            files: [],
            previews: []
        });
    }

    // Sort: mandatory first, others last
    photosArray.sort((a, b) => {
        if (a.is_mandatory && !b.is_mandatory) return -1;
        if (!a.is_mandatory && b.is_mandatory) return 1;
        return 0;
    });
};

// Watchers to auto-assign and ensure categories
watch(() => props.form.photos, (photos) => {
    if (galleryCategoriesAsset.value.length === 0) return;
    ensureCategories(photos);
}, { deep: true, immediate: true });

// Juga pantau jika assetTypeDetails baru selesai dimuat
watch(galleryCategoriesAsset, (newVal) => {
    if (newVal && newVal.length > 0) {
        ensureCategories(props.form.photos);
    }
}, { immediate: true });

</script>

<template>
<div class="space-y-6">
    <!-- Input hidden untuk Ganti Foto -->
    <input type="file" ref="gantiFotoInput" class="hidden" accept="image/png, image/jpeg, image/jpg" @change="onGantiFotoChange" />
    <input type="file" ref="gantiThumbnailInput" class="hidden" accept="image/png, image/jpeg, image/jpg" @change="validateAndUploadThumbnail($event)" />

    <!-- Modal Tips -->
    <TipsModal :show="showTipsModal" @close="showTipsModal = false" />

    <!-- ============================================ -->
    <!-- GALERI ASET UTAMA -->
    <!-- ============================================ -->
    <div class="space-y-4">

        <!-- JUDUL ASET -->
        <div class="pb-4 border-b border-slate-200">
            <h3 class="text-xl font-bold text-[#0A2540]">
                Galeri Foto: {{ form.title || 'Aset' }}
            </h3>
            <p class="text-sm text-slate-500 mt-1">Unggah foto-foto fasilitas umum dan area luar dari properti Anda.</p>
        </div>

        <!-- Banner Tips -->
        <div @click="showTipsModal = true" class="bg-[#FFC000]/10 border border-[#FFC000]/30 rounded-lg px-4 py-2.5 flex items-center justify-between cursor-pointer hover:bg-[#FFC000]/20 transition-colors shadow-sm">
            <div class="flex items-center gap-4">
                <Image :size="20" class="text-[#0A2540] shrink-0" />
                <div>
                    <h4 class="font-bold text-[#0A2540] text-sm">Perhatian, 4 tips foto ini buat iklan makin menarik dan cepat tayang!</h4>
                    <p class="text-xs text-slate-600 mt-0.5">Klik untuk melihat panduan lengkap foto yang baik.</p>
                </div>
            </div>
            <ChevronRight class="w-5 h-5 text-[#0A2540] shrink-0" />
        </div>

        <!-- THUMBNAIL ASET -->
        <div class="mb-10">
            <label class="block text-sm font-semibold text-slate-700 mb-3">
                Foto Sampul Utama <span class="text-rose-500">*</span>
                <span class="text-xs text-slate-500 font-normal ml-1">(Akan ditampilkan paling depan)</span>
            </label>

            <div v-if="!form.thumbnail_preview" class="bg-slate-50/50 hover:bg-slate-50 w-full py-8 flex flex-col items-center justify-center transition cursor-pointer relative" style="border-radius: 8px; background-image: url('data:image/svg+xml,%3csvg width=\'100%25\' height=\'100%25\' xmlns=\'http://www.w3.org/2000/svg\'%3e%3crect width=\'100%25\' height=\'100%25\' fill=\'none\' rx=\'8\' ry=\'8\' stroke=\'%2394a3b8\' stroke-width=\'2\' stroke-dasharray=\'8%2c 6\' stroke-dashoffset=\'0\' stroke-linecap=\'square\'/%3e%3c/svg%3e');">
                <input
                    type="file"
                    accept="image/png, image/jpeg, image/jpg"
                    @change="validateAndUploadThumbnail($event)"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                />
                <NoImageIllustration class="w-16 h-16 text-slate-300 mb-3" />
                <span class="text-sm font-bold text-[#0A2540]">Pilih Foto atau Tarik ke sini</span>
                <span class="text-xs text-slate-500 mt-1">Format didukung: JPG, JPEG, PNG</span>
            </div>
            <div v-else class="relative w-48 h-32 group/thumb mt-3">
                <img :src="form.thumbnail_preview" class="w-full h-full object-cover rounded-xl border border-slate-200 shadow-sm" />
                <Menu as="div" class="absolute top-2 right-2 inline-block text-left z-10" v-slot="{ open }">
                    <MenuButton :class="['w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm transition', open ? 'opacity-100 text-slate-900' : 'opacity-0 group-hover/thumb:opacity-100 text-slate-700 hover:text-slate-900']">
                        <MoreVertical class="w-5 h-5" />
                    </MenuButton>

                    <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                        <MenuItems class="absolute left-0 mt-1 w-48 origin-top-left rounded-none bg-white shadow-md focus:outline-none sm:left-auto sm:right-0 sm:origin-top-right">
                            <div class="py-1">
                                <MenuItem v-slot="{ active }">
                                    <button @click.prevent="openPreview(form.thumbnail_preview)" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                        <Eye class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                        Preview Foto
                                    </button>
                                </MenuItem>
                                <MenuItem v-slot="{ active }">
                                    <button @click.prevent="triggerGantiThumbnail()" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                        <ArrowRightLeft class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                        Ganti Foto
                                    </button>
                                </MenuItem>
                                <MenuItem v-slot="{ active }">
                                    <button @click.prevent="emit('hapusThumbnail')" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                        <Trash2 class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                        Hapus Foto
                                    </button>
                                </MenuItem>
                            </div>
                        </MenuItems>
                    </transition>
                </Menu>
            </div>
            <p v-if="fileErrors['thumbnail']" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['thumbnail'] }}</p>
        </div>

        <!-- FOTO ASET BERDASARKAN KATEGORI -->
        <div class="mt-10 border-t border-slate-200 pt-10">

            <!-- Jika tidak ada galery_categories untuk jenis aset ini -->
            <div v-if="!hasGalleryCategories" class="text-sm text-slate-500 italic bg-slate-50 p-4 rounded-md border border-slate-200">
                Kategori foto belum dikonfigurasi.
            </div>

            <!-- Daftar foto per kategori -->
            <div v-if="hasGalleryCategories" class="space-y-12">
                    <div
                        v-for="(fotoGroup, index) in form.photos"
                        :key="fotoGroup._id"
                        class="mb-2 p-2 rounded-xl transition-all duration-200 -mx-2 border-2"
                        :class="[ dragOverIndex === index ? 'bg-[#FFC000]/10 border-dashed border-[#FFC000]' : 'border-transparent' ]"
                        @dragover.prevent="dragOverIndex = index"
                    @dragleave.prevent="dragOverIndex = null"
                    @drop.prevent="onDrop(index)"
                >
                    <!-- Kategori Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                {{ fotoGroup.gallery_category_name || 'Lainnya' }}
                                <span v-if="fotoGroup.is_mandatory" class="text-rose-500">*</span>
                            </label>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ fotoGroup.is_mandatory ? `Pastikan area/fasilitas ${fotoGroup.gallery_category_name.toLowerCase()} terlihat dengan jelas.` : 'Tambahkan foto lainnya yang mendeskripsikan aset Anda (Opsional).' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Jika kosong (Empty State) -->
                        <div v-if="!fotoGroup.previews || fotoGroup.previews.length === 0" class="bg-slate-50/50 hover:bg-slate-50 w-full py-8 flex flex-col items-center justify-center transition cursor-pointer relative" style="border-radius: 8px; background-image: url('data:image/svg+xml,%3csvg width=\'100%25\' height=\'100%25\' xmlns=\'http://www.w3.org/2000/svg\'%3e%3crect width=\'100%25\' height=\'100%25\' fill=\'none\' rx=\'8\' ry=\'8\' stroke=\'%2394a3b8\' stroke-width=\'2\' stroke-dasharray=\'8%2c 6\' stroke-dashoffset=\'0\' stroke-linecap=\'square\'/%3e%3c/svg%3e');">
                            <input
                                type="file"
                                multiple
                                accept="image/png, image/jpeg, image/jpg"
                                @change="validateAndUpload($event, index)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            />
                            <NoImageIllustration class="w-16 h-16 text-slate-300 mb-3" />
                            <span class="text-sm font-bold text-[#0A2540]">Pilih Foto atau Tarik ke sini</span>
                            <span class="text-xs text-slate-500 mt-1">Format didukung: JPG, JPEG, PNG</span>
                        </div>

                        <!-- Jika sudah ada foto -->
                        <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 pb-2">
                            <!-- Area Upload Tambahan -->
                            <div class="relative w-full aspect-[3/2] flex flex-col items-center justify-center transition cursor-pointer bg-slate-50/50 hover:bg-slate-50" style="border-radius: 8px; background-image: url('data:image/svg+xml,%3csvg width=\'100%25\' height=\'100%25\' xmlns=\'http://www.w3.org/2000/svg\'%3e%3crect width=\'100%25\' height=\'100%25\' fill=\'none\' rx=\'8\' ry=\'8\' stroke=\'%2394a3b8\' stroke-width=\'2\' stroke-dasharray=\'8%2c 6\' stroke-dashoffset=\'0\' stroke-linecap=\'square\'/%3e%3c/svg%3e');">
                                <input
                                    type="file"
                                    multiple
                                    accept="image/png, image/jpeg, image/jpg"
                                    @change="validateAndUpload($event, index)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                />
                                <NoImageIllustration class="w-8 h-8 text-slate-500 mb-1" />
                                <span class="text-xs font-semibold text-slate-600">Tambah Foto</span>
                            </div>

                            <!-- PREVIEW FOTO -->
                            <div
                                v-for="(preview, fileIndex) in fotoGroup.previews"
                                :key="fileIndex"
                                class="relative w-full aspect-[3/2] group/photo cursor-move"
                                draggable="true"
                                @dragstart="onDragStart(index, fileIndex)"
                            >
                                <img :src="preview" class="w-full h-full object-cover rounded-lg border border-slate-200 shadow-sm" />
                                <Menu as="div" class="absolute top-2 right-2 inline-block text-left z-10" v-slot="{ open }">
                                    <MenuButton :class="['w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm transition', open ? 'opacity-100 text-slate-900' : 'opacity-0 group-hover/photo:opacity-100 text-slate-700 hover:text-slate-900']">
                                        <MoreVertical class="w-5 h-5" />
                                    </MenuButton>

                                    <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                        <MenuItems class="absolute right-0 mt-1 w-48 origin-top-right rounded-none bg-white shadow-md focus:outline-none">
                                            <div class="py-1">
                                                <MenuItem v-slot="{ active }">
                                                    <button @click.prevent="openPreview(preview)" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                                        <Eye class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                                        Preview Foto
                                                    </button>
                                                </MenuItem>
                                                <MenuItem v-slot="{ active }">
                                                    <button @click.prevent="triggerGantiFoto(index, fileIndex)" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                                        <ArrowRightLeft class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                                        Ganti Foto
                                                    </button>
                                                </MenuItem>
                                                <MenuItem v-slot="{ active }">
                                                    <button @click.prevent="emit('hapusFoto', index, fileIndex)" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                                        <Trash2 class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                                        Hapus Foto
                                                    </button>
                                                </MenuItem>
                                            </div>
                                        </MenuItems>
                                    </transition>
                                </Menu>
                            </div>
                        </div>
                    </div>
                    <p v-if="fileErrors['foto_' + index]" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['foto_' + index] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview Foto -->
    <div v-if="previewImageUrl" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/80" @click="closePreview">
        <div class="relative w-full h-full max-w-5xl mx-auto flex items-center justify-center p-4 sm:p-10" @click.stop>
            <button @click="closePreview" class="absolute top-4 right-4 sm:top-10 sm:right-10 bg-white hover:bg-slate-100 text-slate-800 rounded p-2 shadow transition">
                <X class="w-5 h-5" />
            </button>
            <img :src="previewImageUrl" class="max-w-full max-h-full object-contain rounded-lg" />
        </div>
    </div>
</div>
</template>
