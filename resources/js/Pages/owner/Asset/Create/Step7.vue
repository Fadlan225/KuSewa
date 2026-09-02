<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { CloudUpload, X, Images, Trash2, Info, ChevronRight, ChevronUp, ChevronDown, Camera, Image, MoreVertical, Eye, ArrowRightLeft, Move } from 'lucide-vue-next';
import { computed, ref, onMounted, watch } from 'vue';
import TipsModal from './TipsModal.vue';
import NoImageIcon from '@/Components/ui/Icons/NoImageIcon.vue';
import NoImageIllustration from '@/Components/ui/Icons/NoImageIllustration.vue';

const props = defineProps({
    form: Object,
    allowUnits: Boolean,
    assetTypeDetails: Object, // { gallery_categories }
    unitLabel: String,
    currentUnitIndex: {
        type: Number,
        default: 0
    },
});

const emit = defineEmits([
    'tambahUnitKategoriFoto', 'hapusUnitKategoriFoto', 'handleUnitFileUpload', 'hapusUnitFoto',
    'handleUnitThumbnailUpload', 'hapusUnitThumbnail', 'gantiUnitFoto', 'pindahkanUnitFoto'
]);

const galleryCategoriesAsset = computed(() => props.assetTypeDetails?.gallery_categories ?? []);

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
    const errorKey = `unit_${unitIndex}_photo_${photoIdx}`;
    if (!valid) {
        fileErrors.value[errorKey] = 'Format foto tidak didukung. Harap unggah format JPG, JPEG, atau PNG.';
        event.target.value = null;
        return;
    }
    fileErrors.value[errorKey] = null;
    emit('handleUnitFileUpload', event, unitIndex, photoIdx);
};

const validateAndUploadThumbnail = (event, isUnit = false, unitIndex = null) => {
    const file = event.target.files[0];
    const errorKey = `unit_${unitIndex}_thumbnail`;
    if (file && !isValidFile(file)) {
        fileErrors.value[errorKey] = 'Format foto tidak didukung. Harap unggah format JPG, JPEG, atau PNG.';
        event.target.value = null;
        return;
    }
    fileErrors.value[errorKey] = null;
    emit('handleUnitThumbnailUpload', event, unitIndex);
};

const toggleExpand = (index) => {
    props.form.units.forEach((u, i) => {
        if (i === index) {
            u.is_expanded = !u.is_expanded;
        } else {
            u.is_expanded = false;
        }
    });
};

const previewImageUrl = ref(null);
const openPreview = (url) => { previewImageUrl.value = url; };
const closePreview = () => { previewImageUrl.value = null; };

const draggedPhoto = ref(null);
const dragOverIndex = ref(null); // format: { unitIndex, photoIdx }

const onDragStart = (unitIndex, photoIdx, fileIndex) => {
    draggedPhoto.value = { unitIndex, photoIdx, fileIndex };
};

const onDrop = (targetUnitIndex, targetPhotoIdx) => {
    dragOverIndex.value = null;
    if (!draggedPhoto.value) return;

    const { unitIndex, photoIdx, fileIndex } = draggedPhoto.value;
    
    // Only emit if moving to a different category or unit
    if (photoIdx !== targetPhotoIdx || unitIndex !== targetUnitIndex) {
        emit('pindahkanUnitFoto', unitIndex, photoIdx, fileIndex, targetUnitIndex, targetPhotoIdx);
    }
    
    draggedPhoto.value = null;
};

const gantiFotoInput = ref(null);
const replaceTarget = ref({ unitIndex: null, photoIdx: null, fileIndex: null });

const triggerGantiFoto = (unitIndex, photoIdx, fileIndex) => {
    replaceTarget.value = { unitIndex, photoIdx, fileIndex };
    if (gantiFotoInput.value) {
        gantiFotoInput.value.click();
    }
};

const onGantiFotoChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const { unitIndex, photoIdx, fileIndex } = replaceTarget.value;
    emit('gantiUnitFoto', event, unitIndex, photoIdx, fileIndex);
    event.target.value = null;
};

onMounted(() => {
    if (props.form.units && props.form.units.length > 0) {
        const hasExpanded = props.form.units.some(u => u.is_expanded);
        if (!hasExpanded) {
            props.form.units[0].is_expanded = true;
        }
    }
});

const ensureCategories = (photosArray) => {
    if (!photosArray) return;

    // 1. Ensure all mandatory categories exist
    const mandatoryCategories = props.assetTypeDetails?.mandatory_unit_categories || [];
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

watch(() => props.form.units, (units) => {
    if (galleryCategoriesAsset.value.length === 0) return;
    if (!units) return;
    units.forEach(u => {
        if (!u.photos) u.photos = [];
        ensureCategories(u.photos);
    });
}, { deep: true, immediate: true });

watch(galleryCategoriesAsset, (newVal) => {
    if (newVal && newVal.length > 0 && props.form.units) {
        props.form.units.forEach(unit => {
            if (!unit.photos) unit.photos = [];
            ensureCategories(unit.photos);
        });
    }
}, { immediate: true });
</script>

<template>
<div class="space-y-6">

    <!-- GALERI UNIT (Jika Allow Units) -->
    <div v-if="allowUnits && form.units[currentUnitIndex]" class="space-y-4">
        <div class="bg-white">
            <div class="space-y-6" v-for="unit in [form.units[currentUnitIndex]]" :key="currentUnitIndex">
                <!-- JUDUL TIPE UNIT -->
                <div class="pb-4 border-b border-slate-200">
                    <h3 class="text-xl font-bold text-[#0A2540]">
                        Galeri Foto: {{ unit.name ? 'Tipe ' + unit.name : `Tipe ${unitLabel || 'Unit'} ${currentUnitIndex + 1}` }}
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Unggah foto spesifik untuk tipe ini agar penyewa mendapatkan gambaran yang jelas.</p>
                </div>

                <!-- THUMBNAIL UNIT -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">
                        Foto Sampul {{ unitLabel || 'Unit' }}
                    </label>

                    <div v-if="!unit.thumbnail_preview" class="bg-slate-50/50 hover:bg-slate-50 w-full py-8 flex flex-col items-center justify-center transition cursor-pointer relative" style="border-radius: 8px; background-image: url('data:image/svg+xml,%3csvg width=\'100%25\' height=\'100%25\' xmlns=\'http://www.w3.org/2000/svg\'%3e%3crect width=\'100%25\' height=\'100%25\' fill=\'none\' rx=\'8\' ry=\'8\' stroke=\'%2394a3b8\' stroke-width=\'2\' stroke-dasharray=\'8%2c 6\' stroke-dashoffset=\'0\' stroke-linecap=\'square\'/%3e%3c/svg%3e');">
                        <input
                            type="file"
                            accept="image/png, image/jpeg, image/jpg"
                            @change="validateAndUploadThumbnail($event, true, currentUnitIndex)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        />
                        <NoImageIllustration class="w-16 h-16 text-slate-300 mb-3" />
                        <span class="text-sm font-bold text-[#0A2540]">Pilih Foto atau Tarik ke sini</span>
                        <span class="text-xs text-slate-500 mt-1">Format didukung: JPG, JPEG, PNG</span>
                    </div>
                    <div v-else class="relative w-48 h-32 group/thumb mt-3">
                        <img :src="unit.thumbnail_preview" class="w-full h-full object-cover rounded-lg border border-slate-200 shadow-sm" />
                        <Menu as="div" class="absolute top-2 right-2 inline-block text-left z-10" v-slot="{ open }">
                            <MenuButton :class="['w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm transition', open ? 'opacity-100 text-slate-900' : 'opacity-0 group-hover/thumb:opacity-100 text-slate-700 hover:text-slate-900']">
                                <MoreVertical class="w-5 h-5" />
                            </MenuButton>

                            <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                <MenuItems class="absolute left-0 mt-1 w-48 origin-top-left rounded-none bg-white shadow-md focus:outline-none sm:left-auto sm:right-0 sm:origin-top-right">
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <button @click.prevent="openPreview(unit.thumbnail_preview)" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                                <Eye class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                                Preview Foto
                                            </button>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <div class="relative group flex w-full items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 cursor-pointer">
                                                <ArrowRightLeft class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                                Ganti Foto
                                                <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/png, image/jpeg, image/jpg" @change="validateAndUploadThumbnail($event, true, currentUnitIndex)" />
                                            </div>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <button @click.prevent="emit('hapusUnitThumbnail', currentUnitIndex)" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                                <Trash2 class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                                Hapus Foto
                                            </button>
                                        </MenuItem>
                                    </div>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                    <p v-if="fileErrors['unit_' + currentUnitIndex + '_thumbnail']" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['unit_' + currentUnitIndex + '_thumbnail'] }}</p>
                </div>

                <!-- FOTO UNIT (upload per kategori) -->
                <div v-if="galleryCategoriesAsset.length > 0">

                    <div class="space-y-6">
                        <div
                            v-for="(photoGroup, photoIdx) in unit.photos"
                            :key="photoGroup._id"
                            class="mb-4 p-2 rounded-xl transition-all duration-200 -mx-2 border-2"
                            :class="[ dragOverIndex?.currentUnitIndex === currentUnitIndex && dragOverIndex?.photoIdx === photoIdx ? 'bg-[#FFC000]/10 border-dashed border-[#FFC000]' : 'border-transparent' ]"
                            @dragover.prevent="dragOverIndex = { currentUnitIndex, photoIdx }"
                            @dragleave.prevent="dragOverIndex = null"
                            @drop.prevent="onDrop(currentUnitIndex, photoIdx)"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <label class="block text-base font-bold text-slate-800">
                                        {{ photoGroup.is_mandatory ? `Foto ${photoGroup.gallery_category_name}` : 'Foto Lainnya' }}
                                        <span v-if="photoGroup.is_mandatory" class="text-rose-500">*</span>
                                    </label>
                                    <p class="text-sm text-slate-500 mt-0.5">
                                        {{ photoGroup.is_mandatory ? `Pastikan area/fasilitas ${photoGroup.gallery_category_name.toLowerCase()} terlihat dengan jelas.` : `Tambahkan foto ${(unitLabel || 'unit').toLowerCase()} lainnya (Opsional).` }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Jika kosong (Empty State) -->
                                <div v-if="!photoGroup.previews || photoGroup.previews.length === 0" class="bg-slate-50/50 hover:bg-slate-50 w-full py-8 flex flex-col items-center justify-center transition cursor-pointer relative" style="border-radius: 8px; background-image: url('data:image/svg+xml,%3csvg width=\'100%25\' height=\'100%25\' xmlns=\'http://www.w3.org/2000/svg\'%3e%3crect width=\'100%25\' height=\'100%25\' fill=\'none\' rx=\'8\' ry=\'8\' stroke=\'%2394a3b8\' stroke-width=\'2\' stroke-dasharray=\'8%2c 6\' stroke-dashoffset=\'0\' stroke-linecap=\'square\'/%3e%3c/svg%3e');">
                                    <input
                                        type="file"
                                        multiple
                                        accept="image/png, image/jpeg, image/jpg"
                                        @change="validateAndUpload($event, null, true, currentUnitIndex, photoIdx)"
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
                                            @change="validateAndUpload($event, null, true, currentUnitIndex, photoIdx)"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        />
                                        <NoImageIllustration class="w-8 h-8 text-slate-500 mb-1" />
                                        <span class="text-xs font-semibold text-slate-600">Tambah Foto</span>
                                    </div>

                                    <!-- PREVIEW FOTO -->
                                    <div
                                        v-for="(preview, fileIndex) in photoGroup.previews"
                                        :key="fileIndex"
                                        class="relative w-full aspect-[3/2] group/photo cursor-move"
                                        draggable="true"
                                        @dragstart="onDragStart(currentUnitIndex, photoIdx, fileIndex)"
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
                                                            <button @click.prevent="triggerGantiFoto(currentUnitIndex, photoIdx, fileIndex)" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
                                                                <ArrowRightLeft class="mr-4 h-4 w-4 text-slate-500 group-hover:text-slate-700" />
                                                                Ganti Foto
                                                            </button>
                                                        </MenuItem>
                                                        <MenuItem v-slot="{ active }">
                                                            <button @click.prevent="emit('hapusUnitFoto', currentUnitIndex, photoIdx, fileIndex)" :class="[active ? 'bg-slate-50 text-slate-900' : 'text-slate-700', 'group flex w-full items-center px-4 py-2.5 text-sm']">
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
                            <p v-if="fileErrors['unit_' + currentUnitIndex + '_photo_' + photoIdx]" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['unit_' + currentUnitIndex + '_photo_' + photoIdx] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Hidden Input for Replace Foto -->
    <input
        type="file"
        ref="gantiFotoInput"
        class="hidden"
        accept="image/png, image/jpeg, image/jpg"
        @change="onGantiFotoChange"
    />

    <!-- Modal Preview Foto -->
    <div v-if="previewImageUrl" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/80" @click="closePreview">
        <div class="relative w-full h-full max-w-5xl mx-auto flex items-center justify-center p-4 sm:p-10" @click.stop>
            <button @click="closePreview" class="absolute top-4 right-4 sm:top-10 sm:right-10 bg-white hover:bg-slate-100 text-slate-800 rounded p-2 shadow transition">
                <X class="w-5 h-5" />
            </button>
            <img :src="previewImageUrl" class="max-w-full max-h-full object-contain rounded-lg" />
        </div>
    </div>
</template>
