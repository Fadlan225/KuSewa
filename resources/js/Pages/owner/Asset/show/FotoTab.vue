<script setup>
import { FolderPlus, Eye, Trash2, Loader2, Plus, Image, AlertTriangle, Info, ChevronRight } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ImageViewerModal from '@/Components/ui/ImageViewerModal.vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/Components/ui/card';
import TipsModal from '../Create/TipsModal.vue';
import NoImageIcon from '@/Components/ui/Icons/NoImageIcon.vue';

const props = defineProps({
    asset: Object,
    galleryCategories: {
        type: Array,
        default: () => [],
    },
    mandatoryCategories: {
        type: Array,
        default: () => [],
    }
});

const customCategoryIds = ref([]);
const selectedCategoryId = ref('');
const showAddCategory = ref(false);

const groupedImages = computed(() => {
    const groups = {};
    
    // Inisialisasi kategori mandatory
    props.mandatoryCategories.forEach(id => {
        const cat = props.galleryCategories.find(c => c.id === id);
        if (cat) {
            groups[cat.name] = { id: cat.id, name: cat.name, is_mandatory: true, images: [] };
        }
    });

    // Inisialisasi kategori custom
    customCategoryIds.value.forEach(id => {
        const cat = props.galleryCategories.find(c => c.id === id);
        if (cat) {
            groups[cat.name] = { id: cat.id, name: cat.name, is_mandatory: false, images: [] };
        }
    });

    if (props.asset.images && props.asset.images.length > 0) {
        props.asset.images.forEach(img => {
            // Abaikan thumbnail agar tidak masuk ke kategori galeri biasa
            if (img.is_thumbnail) return;

            const catName = img.gallery_category?.name || 'Umum';
            const catId = img.gallery_category_id || null;
            if (!groups[catName]) {
                groups[catName] = { id: catId, name: catName, is_mandatory: false, images: [] };
            }
            groups[catName].images.push(img);
        });
    }

    return groups;
});

const availableGalleryCategories = computed(() => {
    if (!props.galleryCategories) return [];
    const existingNames = Object.keys(groupedImages.value);
    return props.galleryCategories.filter(cat => !existingNames.includes(cat.name));
});

const searchableCategories = computed(() => {
    return availableGalleryCategories.value.map(cat => ({
        code: cat.id,
        name: cat.name
    }));
});

const addCategory = () => {
    if (!selectedCategoryId.value) return;
    customCategoryIds.value.push(selectedCategoryId.value);
    selectedCategoryId.value = '';
    showAddCategory.value = false;
};

const isUploading = ref(false);
const fileErrors = ref({});
const showTipsModal = ref(false);

const isValidFile = (file) => {
    return ['image/jpeg', 'image/png', 'image/jpg'].includes(file.type);
};

const handleFileUpload = (event, categoryId = 'thumbnail') => {
    const files = event.target.files;
    if (!files.length) return;

    let valid = true;
    for (const file of files) {
        if (!isValidFile(file)) {
            valid = false;
            break;
        }
    }

    if (!valid) {
        fileErrors.value[categoryId] = 'Format foto tidak didukung. Harap unggah format JPG, JPEG, atau PNG.';
        event.target.value = null;
        return;
    }

    fileErrors.value[categoryId] = null;
    const formData = new FormData();

    if (categoryId === 'thumbnail') {
        formData.append('thumbnail', files[0]);
    } else {
        for (let i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
        }
        if (categoryId) {
            formData.append('gallery_category_id', categoryId);
        }
    }

    router.post(route('owner.asset.images.store', props.asset.slug || props.asset.id), formData, {
        preserveScroll: true,
        onStart: () => { isUploading.value = true; },
        onFinish: () => {
            isUploading.value = false;
            event.target.value = '';
        },
    });
};

// Image Viewer State
const showImageViewer = ref(false);
const viewerImages = ref([]);
const viewerInitialIndex = ref(0);

const openImageViewer = (imgUrl) => {
    viewerImages.value = props.asset.images.map(img => img.image_url);
    const idx = viewerImages.value.indexOf(imgUrl);
    viewerInitialIndex.value = idx !== -1 ? idx : 0;
    showImageViewer.value = true;
};

// Delete Modal State
const showDeleteModal = ref(false);
const imageToDelete = ref(null);

const confirmDelete = (imageId) => {
    imageToDelete.value = imageId;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    if (!imageToDelete.value) return;
    router.delete(route('owner.asset.images.destroy', [props.asset.slug || props.asset.id, imageToDelete.value]), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            imageToDelete.value = null;
        }
    });
};
</script>

<template>
    <div class="animate-in fade-in duration-300">
        <!-- Modal Tips -->
        <TipsModal :show="showTipsModal" @close="showTipsModal = false" />
        
        <Card class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <CardHeader class="border-b border-slate-100 bg-white px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 space-y-0">
                <CardTitle class="font-bold text-slate-800 text-base">Galeri Foto ({{ asset.images?.length || 0 }})</CardTitle>
                <button @click="showAddCategory = true" class="text-xs font-bold text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition shadow-sm flex items-center justify-center gap-1.5 w-full sm:w-auto !mt-0">
                    <FolderPlus class="w-4 h-4" /> Tambah Kategori
                </button>
            </CardHeader>
            
            <CardContent class="p-5 md:p-6 space-y-8">
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

                <!-- Modal/Form Add Category -->
                <div v-if="showAddCategory" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row items-center gap-3 animate-in fade-in zoom-in-95 duration-200 overflow-visible z-10 relative">
                    <SearchableSelect 
                        v-model="selectedCategoryId" 
                        :options="searchableCategories" 
                        placeholder="Cari kategori foto..." 
                        class="w-full sm:w-80 flex-1 z-50"
                    />
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button @click="addCategory" :disabled="!selectedCategoryId" :class="selectedCategoryId ? 'bg-[#FFC000] hover:bg-[#e5ac00] text-slate-900' : 'bg-slate-300 text-slate-500 cursor-not-allowed'" class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg text-sm font-bold shadow-sm transition">Tambah</button>
                        <button @click="showAddCategory = false" class="flex-1 sm:flex-none bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2.5 rounded-lg text-sm font-bold transition">Batal</button>
                    </div>
                </div>

                <!-- Thumbnail Aset -->
                <div class="space-y-4 mb-6 pb-6 border-b border-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-2">
                        <h4 class="font-bold text-slate-700 text-sm">Thumbnail Aset <span class="text-rose-500">*</span></h4>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">1 foto utama</span>
                    </div>
                    
                    <div v-if="asset.thumbnail_images && asset.thumbnail_images.length > 0" class="w-48 h-32 rounded-lg border-2 border-[#0A2540] overflow-hidden relative group shadow-sm bg-slate-100">
                        <img :src="asset.thumbnail_images[0].image_url" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                            <button type="button" @click="openImageViewer(asset.thumbnail_images[0].image_url)" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-blue-600 flex items-center justify-center shadow-sm" title="Lihat">
                                <Eye class="text-xs w-4 h-4" />
                            </button>
                            <button type="button" @click="confirmDelete(asset.thumbnail_images[0].id)" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-rose-600 flex items-center justify-center shadow-sm" title="Hapus">
                                <Trash2 class="text-xs w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <div v-else>
                        <div class="border-2 border-dashed border-slate-300 bg-slate-50/50 rounded-lg p-6 text-center hover:bg-slate-100 transition cursor-pointer relative mt-2 w-full max-w-sm">
                            <input
                                type="file"
                                accept="image/png, image/jpeg, image/jpg"
                                @change="handleFileUpload($event, 'thumbnail')"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed disabled:opacity-0"
                                :disabled="isUploading"
                            />
                            <Loader2 v-if="isUploading" class="w-12 h-12 mx-auto text-slate-300 mb-3 opacity-50 animate-spin" />
                            <NoImageIcon v-else class="w-12 h-12 mx-auto text-slate-300 mb-3 opacity-50" />
                            <p class="text-sm font-semibold text-[#0A2540]">{{ isUploading ? 'Mengunggah...' : 'Unggah atau tarik foto sampul ke sini' }}</p>
                            <p class="text-[10px] text-slate-500 mt-1">Format foto harus JPG, JPEG, atau PNG.</p>
                        </div>
                        <p v-if="fileErrors['thumbnail']" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors['thumbnail'] }}</p>
                    </div>
                </div>

                <!-- Loop per Category -->
                <div v-for="(group, categoryName) in groupedImages" :key="categoryName" class="space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-2">
                        <h4 class="font-bold text-slate-700 text-sm">Foto {{ categoryName }} <span v-if="group.is_mandatory" class="text-rose-500">*</span></h4>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ group.images.length }} foto</span>
                    </div>

                    <div>
                        <div class="border-2 border-dashed border-slate-300 bg-slate-50/50 rounded-lg p-6 text-center hover:bg-slate-100 transition cursor-pointer relative mt-2 w-full max-w-3xl">
                            <input
                                type="file"
                                multiple
                                accept="image/png, image/jpeg, image/jpg"
                                @change="handleFileUpload($event, group.id)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed disabled:opacity-0"
                                :disabled="isUploading"
                            />
                            <Loader2 v-if="isUploading" class="w-12 h-12 mx-auto text-slate-300 mb-3 opacity-50 animate-spin" />
                            <NoImageIcon v-else class="w-12 h-12 mx-auto text-slate-300 mb-3 opacity-50" />
                            <p class="text-sm font-semibold text-[#0A2540]">{{ isUploading ? 'Mengunggah...' : 'Unggah atau tarik foto-foto ke sini' }}</p>
                            <p class="text-[10px] text-slate-500 mt-1">Format foto harus JPG, JPEG, atau PNG.</p>
                        </div>
                        <p v-if="fileErrors[group.id]" class="text-rose-500 text-xs font-semibold mt-2">{{ fileErrors[group.id] }}</p>
                    </div>

                    <div v-if="group.images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <!-- Existing Images -->
                        <div v-for="img in group.images" :key="img.id" class="aspect-square rounded-lg border border-slate-200 overflow-hidden relative group shadow-sm bg-slate-100">
                            <img :src="img.image_url" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                <button type="button" @click="openImageViewer(img.image_url)" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-blue-600 flex items-center justify-center shadow-sm" title="Lihat">
                                    <Eye class="text-xs w-4 h-4" />
                                </button>
                                <button type="button" @click="confirmDelete(img.id)" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-rose-600 flex items-center justify-center shadow-sm" title="Hapus">
                                    <Trash2 class="text-xs w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="Object.keys(groupedImages).length === 0" class="text-center py-12 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
                    <Image class="text-4xl text-slate-300 mb-3 mx-auto" />
                    <h3 class="font-bold text-slate-700 mb-1">Belum Ada Foto</h3>
                    <p class="text-sm text-slate-400">Properti tanpa foto akan sulit menarik perhatian penyewa.</p>
                </div>
            </CardContent>
        </Card>

        <!-- Custom Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <AlertTriangle class="text-3xl text-rose-500" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Foto?</h3>
                    <p class="text-sm text-slate-500">Tindakan ini tidak dapat dibatalkan dan foto akan hilang selamanya.</p>
                </div>
                <div class="flex items-center border-t border-slate-100 bg-slate-50">
                    <button @click="showDeleteModal = false" class="flex-1 py-4 text-sm font-bold text-slate-600 hover:bg-slate-100 transition border-r border-slate-100">
                        Batal
                    </button>
                    <button @click="executeDelete" class="flex-1 py-4 text-sm font-bold text-rose-600 hover:bg-rose-50 transition">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>

        <ImageViewerModal
            v-if="showImageViewer"
            :images="viewerImages"
            :initial-index="viewerInitialIndex"
            @close="showImageViewer = false"
        />
    </div>
</template>
