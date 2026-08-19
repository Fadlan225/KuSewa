<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { FolderPlus, X, Plus, Layers } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    asset: Object,
    assetFacilities: Array,
    masterFacilityCategories: Array,
});

// Local state for facilities to allow UI testing
const localFacilities = ref([...(props.assetFacilities || [])]);

watch(() => props.assetFacilities, (newVals) => {
    localFacilities.value = [...(newVals || [])];
});

const customCategoryIds = ref([]);
const selectedCategoryId = ref('');
const showAddCategory = ref(false);

const groupedFacilities = computed(() => {
    const groups = {};

    // Inisialisasi kategori custom yang ditambahkan secara manual
    customCategoryIds.value.forEach(id => {
        const cat = props.masterFacilityCategories?.find(c => c.id === id);
        if (cat) {
            groups[cat.id] = { id: cat.id, name: cat.name, icon: cat.icon, facilities: [] };
        }
    });

    // Masukkan dari fasilitas yang sudah ada
    localFacilities.value.forEach(fac => {
        const catId = fac.facility_category_id;

        let catName = fac.category?.name || 'Umum';
        let catIcon = fac.category?.icon || 'fa-list';

        if (catId) {
            const masterCat = props.masterFacilityCategories?.find(c => c.id === catId);
            if (masterCat) {
                catName = masterCat.name;
                catIcon = masterCat.icon;
            }

            if (!groups[catId]) {
                groups[catId] = { id: catId, name: catName, icon: catIcon, facilities: [] };
            }
            groups[catId].facilities.push(fac);
        } else {
            if (!groups['umum']) groups['umum'] = { id: 'umum', name: 'Lainnya', icon: 'fa-list', facilities: [] };
            groups['umum'].facilities.push(fac);
        }
    });

    return groups;
});

const availableCategoriesToAdd = computed(() => {
    if (!props.masterFacilityCategories) return [];

    const existingIds = Object.keys(groupedFacilities.value).map(id => id === 'umum' ? 'umum' : Number(id));

    return props.masterFacilityCategories.filter(cat => !existingIds.includes(cat.id));
});

const addCategory = () => {
    if (!selectedCategoryId.value) return;

    if (!customCategoryIds.value.includes(selectedCategoryId.value)) {
        customCategoryIds.value.push(selectedCategoryId.value);
    }

    selectedCategoryId.value = '';
    showAddCategory.value = false;
};

const showAddFacilityModal = ref(false);
const activeCategoryId = ref(null);
const newFacilityId = ref('');

const openAddFacilityModal = (categoryId) => {
    activeCategoryId.value = categoryId;
    newFacilityId.value = '';
    showAddFacilityModal.value = true;
};

const availableFacilitiesForActiveCategory = computed(() => {
    if (!activeCategoryId.value || !props.masterFacilityCategories) return [];

    const category = props.masterFacilityCategories.find(c => c.id === activeCategoryId.value);
    if (!category) return [];

    return category.facilities.filter(mf =>
        !localFacilities.value.some(lf => lf.id === mf.id)
    );
});

const addFacility = () => {
    if (newFacilityId.value && activeCategoryId.value) {
        const category = props.masterFacilityCategories.find(c => c.id === activeCategoryId.value);
        if (category) {
            const facility = category.facilities.find(f => f.id === newFacilityId.value);
            if (facility && !localFacilities.value.some(f => f.id === facility.id)) {
                // Optimistic UI Update
                localFacilities.value.push({
                    id: facility.id,
                    name: facility.name,
                    icon: facility.icon || 'fa-check',
                    facility_category_id: category.id,
                    category: {
                        name: category.name,
                        icon: category.icon
                    }
                });

                // Send request to backend
                router.post(route('owner.asset.facilities.store', props.asset.slug || props.asset.id), {
                    facility_id: facility.id
                }, {
                    preserveScroll: true,
                    onError: () => {
                        // Revert local state on error
                        localFacilities.value = localFacilities.value.filter(f => f.id !== facility.id);
                        alert('Gagal menambahkan fasilitas.');
                    }
                });
            }
        }
        newFacilityId.value = '';
        showAddFacilityModal.value = false;
    }
};

const removeFacility = (id) => {
    // Save previous state for revert
    const previousState = [...localFacilities.value];
    
    // Optimistic UI Update
    localFacilities.value = localFacilities.value.filter(f => f.id !== id);

    // Send request to backend
    router.delete(route('owner.asset.facilities.destroy', [props.asset.slug || props.asset.id, id]), {
        preserveScroll: true,
        onError: () => {
            // Revert local state on error
            localFacilities.value = previousState;
            alert('Gagal menghapus fasilitas.');
        }
    });
};
</script>

<template>
    <div class="animate-in fade-in duration-300">
        <!-- Fasilitas Aset -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden h-fit">
            <div class="border-b border-slate-100 bg-white px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="font-bold text-slate-800">Fasilitas Aset ({{ localFacilities.length }})</h2>
                <button @click="showAddCategory = true" class="text-xs font-bold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition shadow-sm flex items-center justify-center gap-1.5 w-full sm:w-auto">
                    <FolderPlus class="" /> Tambah Kategori
                </button>
            </div>

            <div class="p-5 md:p-6 space-y-8">
                <!-- Modal/Form Add Category -->
                <div v-if="showAddCategory" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row items-center gap-3 animate-in fade-in zoom-in-95 duration-200">
                    <select v-model="selectedCategoryId" class="text-sm border-slate-300 rounded-lg px-3 py-2 w-full sm:w-auto flex-1 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="" disabled>Pilih Kategori Fasilitas</option>
                        <option v-for="cat in availableCategoriesToAdd" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button @click="addCategory" :disabled="!selectedCategoryId" :class="selectedCategoryId ? 'bg-[#0A2540] hover:bg-slate-800 text-white' : 'bg-slate-300 text-slate-500 cursor-not-allowed'" class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">Tambah</button>
                        <button @click="showAddCategory = false" class="flex-1 sm:flex-none bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold transition">Batal</button>
                    </div>
                </div>

                <!-- Loop per Category -->
                <div v-for="(group, categoryId) in groupedFacilities" :key="categoryId" class="space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-2">
                        <div class="w-6 h-6 rounded bg-blue-50 text-blue-600 flex items-center justify-center">
                            <AppIcon :iconClass="group.icon || 'fa-list'"  />
                        </div>
                        <h4 class="font-bold text-slate-700 text-sm">{{ group.name }}</h4>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ group.facilities.length }} fasilitas</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <!-- Existing Facilities -->
                        <div v-for="fac in group.facilities" :key="fac.id" class="aspect-square rounded-lg border border-slate-200 relative group shadow-sm bg-white flex flex-col items-center justify-center p-3 text-center transition hover:border-slate-300">
                            <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 mb-2 flex items-center justify-center border border-slate-100">
                                <AppIcon :iconClass="fac.icon || 'fa-check'"  />
                            </div>
                            <span class="text-[10px] font-bold text-slate-600 line-clamp-2 leading-tight">{{ fac.name }}</span>

                            <!-- Remove button -->
                            <button @click="removeFacility(fac.id)" class="absolute -top-1 right-2 w-5 h-5 bg-rose-500 text-white rounded-full text-[10px] flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-rose-600" title="Hapus Fasilitas">
                                <X class="" />
                            </button>
                        </div>

                        <!-- Upload Button (Card +) -->
                        <button @click="openAddFacilityModal(group.id)" class="aspect-square rounded-lg border-2 border-dashed border-slate-300 hover:border-[#0A2540] hover:bg-slate-50 bg-slate-50/50 flex flex-col items-center justify-center gap-2 transition group shadow-sm">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-[#0A2540] group-hover:scale-110 transition-transform">
                                <Plus class="text-lg" />
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 group-hover:text-[#0A2540] text-center px-2">Tambah<br>Fasilitas</span>
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="Object.keys(groupedFacilities).length === 0 && !showAddCategory" class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-4 border border-slate-100">
                        <Layers class="text-3xl" />
                    </div>
                    <h3 class="font-bold text-slate-700 mb-1">Belum Ada Kategori Fasilitas</h3>
                    <p class="text-sm text-slate-400 max-w-sm mx-auto mb-4">Aset tanpa fasilitas yang lengkap mungkin kurang diminati penyewa. Tambahkan kategori fasilitas sekarang.</p>
                    <button @click="showAddCategory = true" class="text-xs font-bold text-white bg-primary hover:bg-primary/90 px-4 py-2 rounded-lg transition shadow-sm inline-flex items-center gap-1.5">
                        <Plus class="" /> Tambah Kategori
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL TAMBAH FASILITAS -->
        <div v-if="showAddFacilityModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Tambah Fasilitas Baru</h3>
                    <div class="mb-5">
                        <label class="text-xs font-bold text-slate-500 block mb-1">Pilih Fasilitas</label>
                        <select v-model="newFacilityId" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full transition">
                            <option value="" disabled>-- Pilih Fasilitas --</option>
                            <option v-for="fac in availableFacilitiesForActiveCategory" :key="fac.id" :value="fac.id">
                                {{ fac.name }}
                            </option>
                        </select>
                        <p v-if="availableFacilitiesForActiveCategory.length === 0" class="text-xs text-rose-500 mt-1">Semua fasilitas di kategori ini sudah ditambahkan.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="showAddFacilityModal = false" class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition text-sm">
                            Batal
                        </button>
                        <button @click="addFacility" :disabled="!newFacilityId" :class="newFacilityId ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md' : 'bg-slate-300 text-slate-500 cursor-not-allowed'" class="flex-1 px-4 py-2 font-bold rounded-lg transition text-sm">
                            Tambah
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
