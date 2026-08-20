<script setup>
import { FolderPlus, Eye, Trash2, Loader2, Plus, Image, AlertTriangle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ImageViewerModal from '@/Components/ui/ImageViewerModal.vue';

const props = defineProps({
    asset: Object,
    galleryCategories: {
        type: Array,
        default: () => [],
    }
});

const customCategoryIds = ref([]);
const selectedCategoryId = ref('');
const showAddCategory = ref(false);

const groupedImages = computed(() => {
    const groups = {};
    
    // Inisialisasi kategori custom
    customCategoryIds.value.forEach(id => {
        const cat = props.galleryCategories.find(c => c.id === id);
        if (cat) {
            groups[cat.name] = { id: cat.id, name: cat.name, images: [] };
        }
    });

    if (props.asset.images && props.asset.images.length > 0) {
        props.asset.images.forEach(img => {
            // Abaikan thumbnail agar tidak masuk ke kategori galeri biasa
            if (img.is_thumbnail) return;

            const catName = img.gallery_category?.name || 'Umum';
            const catId = img.gallery_category_id || null;
            if (!groups[catName]) {
                groups[catName] = { id: catId, name: catName, images: [] };
            }
            groups[catName].images.push(img);
        });
    }

    // Jika kosong sama sekali (tidak ada foto selain thumbnail)
    // Jangan buat default 'Umum' secara paksa, biarkan kosong agar UI menampilkan "Belum Ada Foto"


    return groups;
});

const availableGalleryCategories = computed(() => {
    if (!props.galleryCategories) return [];
    
    // Filter kategori yang belum ada di groupedImages
    const existingNames = Object.keys(groupedImages.value);
    
    return props.galleryCategories.filter(cat => !existingNames.includes(cat.name));
});

const addCategory = () => {
    if (!selectedCategoryId.value) return;
    
    customCategoryIds.value.push(selectedCategoryId.value);
    selectedCategoryId.value = '';
    showAddCategory.value = false;
};

const fileInput = ref(null);
const uploadTargetCategoryId = ref(null);
const isUploading = ref(false);

const triggerUpload = (categoryId) => {
    uploadTargetCategoryId.value = categoryId;
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const files = event.target.files;
    if (!files.length) return;

    const formData = new FormData();

    if (uploadTargetCategoryId.value === 'thumbnail') {
        // Kirim HANYA field thumbnail — jangan tambahkan images[]
        formData.append('thumbnail', files[0]);
    } else {
        // Upload foto galeri biasa
        for (let i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
        }
        if (uploadTargetCategoryId.value) {
            formData.append('gallery_category_id', uploadTargetCategoryId.value);
        }
    }

    router.post(route('owner.asset.images.store', props.asset.slug || props.asset.id), formData, {
        preserveScroll: true,
        onStart: () => { isUploading.value = true; },
        onFinish: () => {
            isUploading.value = false;
            event.target.value = ''; // Reset input
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
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-white px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="font-bold text-slate-800">Galeri Foto ({{ asset.images?.length || 0 }})</h2>
                <button @click="showAddCategory = true" class="text-xs font-bold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition shadow-sm flex items-center justify-center gap-1.5 w-full sm:w-auto">
                    <FolderPlus class="" /> Tambah Kategori
                </button>
            </div>
            
            <div class="p-5 md:p-6 space-y-8">
                <!-- Modal/Form Add Category -->
                <div v-if="showAddCategory" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row items-center gap-3 animate-in fade-in zoom-in-95 duration-200">
                    <select v-model="selectedCategoryId" class="text-sm border-slate-300 rounded-lg px-3 py-2 w-full sm:w-auto flex-1 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="" disabled>Pilih Kategori</option>
                        <option v-for="cat in availableGalleryCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button @click="addCategory" :disabled="!selectedCategoryId" :class="selectedCategoryId ? 'bg-[#0A2540] hover:bg-slate-800 text-white' : 'bg-slate-300 text-slate-500 cursor-not-allowed'" class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">Tambah</button>
                        <button @click="showAddCategory = false" class="flex-1 sm:flex-none bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold transition">Batal</button>
                    </div>
                </div>

                <!-- Thumbnail Aset -->
                <div class="space-y-4 mb-6 pb-6 border-b border-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-2">
                        <h4 class="font-bold text-slate-700 text-sm">Thumbnail Aset</h4>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">1 foto utama</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <div v-if="asset.thumbnail_images && asset.thumbnail_images.length > 0" class="aspect-square rounded-lg border border-slate-200 overflow-hidden relative group shadow-sm bg-slate-100">
                            <img :src="asset.thumbnail_images[0].image_url" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                <button type="button" @click="openImageViewer(asset.thumbnail_images[0].image_url)" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-blue-600 flex items-center justify-center shadow-sm" title="Lihat">
                                    <Eye class="text-xs" />
                                </button>
                                <button type="button" @click="confirmDelete(asset.thumbnail_images[0].id)" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-rose-600 flex items-center justify-center shadow-sm" title="Hapus">
                                    <Trash2 class="text-xs" />
                                </button>
                            </div>
                        </div>
                        <button v-else @click="triggerUpload('thumbnail')" :disabled="isUploading" class="aspect-square rounded-lg border-2 border-dashed border-slate-300 hover:border-[#0A2540] hover:bg-slate-50 bg-slate-50/50 flex flex-col items-center justify-center gap-2 transition group shadow-sm">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-[#0A2540] group-hover:scale-110 transition-transform">
                                <Loader2 v-if="isUploading && uploadTargetCategoryId === 'thumbnail'" class="text-lg animate-spin" />
                                <Plus v-else class="text-lg" />
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 group-hover:text-[#0A2540]">
                                {{ isUploading && uploadTargetCategoryId === 'thumbnail' ? 'Mengunggah...' : 'Unggah Thumbnail' }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Loop per Category -->
                <div v-for="(group, categoryName) in groupedImages" :key="categoryName" class="space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-2">
                        <h4 class="font-bold text-slate-700 text-sm">{{ categoryName }}</h4>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ group.images.length }} foto</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <!-- Existing Images -->
                        <div v-for="img in group.images" :key="img.id" class="aspect-square rounded-lg border border-slate-200 overflow-hidden relative group shadow-sm bg-slate-100">
                            <img :src="img.image_url" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                <button type="button" @click="openImageViewer(img.image_url)" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-blue-600 flex items-center justify-center shadow-sm" title="Lihat">
                                    <Eye class="text-xs" />
                                </button>
                                <button type="button" @click="confirmDelete(img.id)" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-rose-600 flex items-center justify-center shadow-sm" title="Hapus">
                                    <Trash2 class="text-xs" />
                                </button>
                            </div>
                        </div>

                        <!-- Upload Button (Card +) -->
                        <button @click="triggerUpload(group.id)" :disabled="isUploading" class="aspect-square rounded-lg border-2 border-dashed border-slate-300 hover:border-[#0A2540] hover:bg-slate-50 bg-slate-50/50 flex flex-col items-center justify-center gap-2 transition group shadow-sm">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-[#0A2540] group-hover:scale-110 transition-transform">
                                <Loader2 v-if="isUploading && uploadTargetCategoryId === group.id" class="text-lg animate-spin" />
                                <Plus v-else class="text-lg" />
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 group-hover:text-[#0A2540]">
                                {{ isUploading && uploadTargetCategoryId === group.id ? 'Mengunggah...' : 'Unggah Foto' }}
                            </span>
                        </button>
                    </div>
                </div>
                
                <div v-if="Object.keys(groupedImages).length === 0" class="text-center py-12">
                    <Image class="text-4xl text-slate-200 mb-3" />
                    <h3 class="font-bold text-slate-700 mb-1">Belum Ada Foto</h3>
                    <p class="text-sm text-slate-400">Properti tanpa foto akan sulit menarik perhatian penyewa.</p>
                </div>
            </div>
        </div>
        
        <!-- Hidden File Input -->
        <input type="file" ref="fileInput" class="hidden" multiple accept="image/*" @change="handleFileUpload" />

        <!-- Custom Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-rose-100 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <AlertTriangle class="text-2xl" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Foto?</h3>
                    <p class="text-sm text-slate-500">Tindakan ini tidak dapat dibatalkan. Foto akan dihapus secara permanen.</p>
                </div>
                <div class="flex border-t border-slate-100">
                    <button @click="showDeleteModal = false" class="flex-1 py-3.5 text-slate-600 font-bold text-sm hover:bg-slate-50 transition border-r border-slate-100">Batal</button>
                    <button @click="executeDelete" class="flex-1 py-3.5 text-rose-600 font-bold text-sm hover:bg-rose-50 transition">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Fullscreen Image Viewer -->
        <ImageViewerModal 
            :show="showImageViewer" 
            :images="viewerImages" 
            :initial-index="viewerInitialIndex" 
            @close="showImageViewer = false" 
        />
    </div>
</template>
