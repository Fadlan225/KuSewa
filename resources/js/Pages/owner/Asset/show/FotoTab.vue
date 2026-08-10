<script setup>
import { ref, computed } from 'vue';

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
            const catName = img.gallery_category?.name || 'Umum';
            const catId = img.gallery_category_id || null;
            if (!groups[catName]) {
                groups[catName] = { id: catId, name: catName, images: [] };
            }
            groups[catName].images.push(img);
        });
    }

    // Jika kosong sama sekali, beri default 'Umum'
    if (Object.keys(groups).length === 0) {
        groups['Umum'] = { id: null, name: 'Umum', images: [] };
    }

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
</script>

<template>
    <div class="animate-in fade-in duration-300">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-white px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="font-bold text-slate-800">Galeri Foto ({{ asset.images?.length || 0 }})</h2>
                <button @click="showAddCategory = true" class="text-xs font-bold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition shadow-sm flex items-center justify-center gap-1.5 w-full sm:w-auto">
                    <i class="fa-solid fa-folder-plus"></i> Tambah Kategori
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
                                <button class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-blue-600 flex items-center justify-center shadow-sm" title="Lihat">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                                <button class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-rose-600 flex items-center justify-center shadow-sm" title="Hapus">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Upload Button (Card +) -->
                        <button class="aspect-square rounded-lg border-2 border-dashed border-slate-300 hover:border-[#0A2540] hover:bg-slate-50 bg-slate-50/50 flex flex-col items-center justify-center gap-2 transition group shadow-sm">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-[#0A2540] group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-plus text-lg"></i>
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 group-hover:text-[#0A2540]">Unggah Foto</span>
                        </button>
                    </div>
                </div>
                
                <div v-if="Object.keys(groupedImages).length === 0" class="text-center py-12">
                    <i class="fa-solid fa-image text-4xl text-slate-200 mb-3"></i>
                    <h3 class="font-bold text-slate-700 mb-1">Belum Ada Foto</h3>
                    <p class="text-sm text-slate-400">Properti tanpa foto akan sulit menarik perhatian penyewa.</p>
                </div>
            </div>
        </div>
    </div>
</template>
