<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, X, Plus, Trash2, ChevronDown, GripVertical, ChevronLeft, ChevronRight, Building, Loader2, Lock, RotateCcw } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';
import Toast from '@/Components/ui/Toast.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import ResetIllustration from '@/Components/ui/Icons/ResetIllustration.vue';
import draggable from 'vuedraggable';
import { nextTick } from 'vue';

/* ── Props ────────────────────────────────────────────────────────── */
const props = defineProps({
    assetTypes:        { type: Array, default: () => [] },
    galleryCategories: { type: Array, default: () => [] },
});

const page = usePage();

const toastState = ref({ show: false, message: '', type: 'success' });

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toastState.value = { show: true, message: flash.success, type: 'success' };
        setTimeout(() => toastState.value.show = false, 3000);
    } else if (flash?.error) {
        toastState.value = { show: true, message: flash.error, type: 'error' };
        setTimeout(() => toastState.value.show = false, 3000);
    }
}, { deep: true, immediate: true });

/* ── State ────────────────────────────────────────────────────────── */
const selectedType  = ref(null);
const selectedTypeId = ref('');
const galleryScope  = ref('asset');

const isSaving = ref(false);
const isInitializing = ref(false);
let saveTimeout = null;

const draftGallery  = ref([]); // array of objects: { id, name, is_mandatory, sort_order }
const activeEditIndex = ref(null);
const popoverTop = ref(0);
const popoverLeft = ref(0);

const openSettings = (idx, event) => {
    activeEditIndex.value = idx;
    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        // Limit bottom position so the modal doesn't go off-screen
        popoverTop.value = Math.max(16, Math.min(rect.top - 10, window.innerHeight - 450));
        // Position it exactly at the right edge of the list item, plus a tiny gap
        popoverLeft.value = rect.right + 12;
    }
};

const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
};

const closePopover = () => {
    activeEditIndex.value = null;
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
    document.addEventListener('click', closePopover);

    if (props.assetTypes && props.assetTypes.length > 0) {
        const lastSelected = localStorage.getItem('last_selected_asset_type_galeri');
        let typeToSelect = props.assetTypes.find(t => t.id == lastSelected);

        if (!typeToSelect) {
            const sortedTypes = [...props.assetTypes].sort((a, b) => a.name.localeCompare(b.name));
            typeToSelect = sortedTypes[0];
        }

        if (typeToSelect) {
            selectAssetType(typeToSelect.id);
        }
    }
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    document.removeEventListener('click', closePopover);
});

function selectAssetType(id) {
    if (id) {
        selectedTypeId.value = id;
        localStorage.setItem('last_selected_asset_type_galeri', id);
    }
    isDropdownOpen.value = false;
}

watch(selectedTypeId, (newId) => {
    if (newId) {
        selectedType.value = props.assetTypes.find(t => t.id === newId);
        loadDraft();
    } else {
        selectedType.value = null;
    }
});

function loadDraft() {
    if (!selectedType.value) return;
    isInitializing.value = true;
    const t = selectedType.value;

    let draft = JSON.parse(JSON.stringify(
        galleryScope.value === 'asset' ? (t.gallery_categories || []) : (t.unit_gallery_categories || [])
    )).map(cat => {
        cat.key = 'cat_' + cat.id;
        if (cat.min_photos === 0) cat.min_photos = null;
        if (cat.max_photos === 0) cat.max_photos = null;
        return cat;
    });

    // Find masters
    const sampulUtamaMaster = props.galleryCategories.find(c => c.name === 'Sampul Utama');
    const lainnyaMaster = props.galleryCategories.find(c => c.name === 'Lainnya');

    // Enforce sorting
    let sampulUtama = draft.find(c => c.name === 'Sampul Utama');
    let lainnya = draft.find(c => c.name === 'Lainnya');
    draft = draft.filter(c => c.name !== 'Sampul Utama' && c.name !== 'Lainnya');

    if (!sampulUtama && sampulUtamaMaster) {
        sampulUtama = {
            id: sampulUtamaMaster.id,
            name: sampulUtamaMaster.name,
            description: sampulUtamaMaster.description,
            is_mandatory: true,
            min_photos: 1,
            max_photos: null,
            key: 'cat_' + sampulUtamaMaster.id
        };
    }

    if (!lainnya && lainnyaMaster) {
        lainnya = {
            id: lainnyaMaster.id,
            name: lainnyaMaster.name,
            description: lainnyaMaster.description,
            is_mandatory: false,
            min_photos: null,
            max_photos: null,
            key: 'cat_' + lainnyaMaster.id
        };
    }

    if (sampulUtama) {
        sampulUtama.is_mandatory = true;
        draft.unshift(sampulUtama);
    }

    if (lainnya) {
        lainnya.is_mandatory = false;
        draft.push(lainnya);
    }

    draftGallery.value = draft;

    activeEditIndex.value = null;
    nextTick(() => {
        isInitializing.value = false;
    });
}

watch(draftGallery, () => {
    if (!isInitializing.value) {
        isSaving.value = true;
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            saveGallery();
        }, 1000);
    }
}, { deep: true });

const availableGalleryCategories = computed(() => {
    return props.galleryCategories.map(cat => ({
        code: cat.id,
        name: cat.name
    }));
});

function onGalleryScopeChange() { loadDraft(); }

/* ── Gallery toggle ───────────────────────────────────────────────── */
function addGallery() {
    const newCat = {
        id: null,
        name: 'Kategori Baru',
        description: '',
        is_mandatory: false,
        min_photos: null,
        max_photos: null,
        sort_order: draftGallery.value.length + 1,
        key: 'new_' + Date.now()
    };

    const lainnyaIdx = draftGallery.value.findIndex(c => c.name === 'Lainnya');
    if (lainnyaIdx > -1) {
        draftGallery.value.splice(lainnyaIdx, 0, newCat);
    } else {
        draftGallery.value.push(newCat);
    }
}

function checkMove(evt) {
    const draggedName = evt.draggedContext.element.name;
    const relatedName = evt.relatedContext.element.name;
    if (draggedName === 'Sampul Utama' || draggedName === 'Lainnya') return false;
    if (relatedName === 'Sampul Utama' && evt.willInsertAfter === false) return false;
    if (relatedName === 'Lainnya' && evt.willInsertAfter === true) return false;
    return true;
}

function updateGallerySelection(idx, newId) {
    const cat = props.galleryCategories.find(c => c.id === newId);
    if (cat) {
        draftGallery.value[idx].id = cat.id;
        draftGallery.value[idx].name = cat.name;
    }
}

function removeGallery(index) {
    draftGallery.value.splice(index, 1);
    if (activeEditIndex.value === index) activeEditIndex.value = null;
}

/* ── Swipe to Delete ──────────────────────────────────────────────── */
const swipeState = ref({
    index: null,
    startX: 0,
    currentX: 0,
    isSwiping: false,
});

function onTouchStart(e, index, catName) {
    if (e.target.closest('.drag-handle')) return;
    if (catName === 'Sampul Utama' || catName === 'Lainnya') return;
    
    swipeState.value = {
        index,
        startX: e.touches ? e.touches[0].clientX : e.clientX,
        currentX: 0,
        isSwiping: true,
    };
}

function onTouchMove(e) {
    if (!swipeState.value.isSwiping || swipeState.value.index === null) return;
    const currentX = e.touches ? e.touches[0].clientX : e.clientX;
    let diff = currentX - swipeState.value.startX;
    
    if (diff > 0) {
        if (diff > 80) diff = 80 + (diff - 80) * 0.2; // Resistance
        swipeState.value.currentX = diff;
    } else {
        swipeState.value.currentX = 0;
    }
}

function onTouchEnd() {
    if (!swipeState.value.isSwiping) return;
    if (swipeState.value.currentX > 60) {
        removeGallery(swipeState.value.index);
    }
    swipeState.value = { index: null, startX: 0, currentX: 0, isSwiping: false };
}

const showResetModal = ref(false);

function resetToDefault() {
    if (!selectedType.value) return;
    showResetModal.value = true;
}

function executeReset() {
    // We get the master items for mandatory categories
    const sampulUtamaMaster = props.galleryCategories.find(c => c.name === 'Sampul Utama');
    const lainnyaMaster = props.galleryCategories.find(c => c.name === 'Lainnya');
    
    let defaultDraft = [];
    
    if (sampulUtamaMaster) {
        defaultDraft.push({
            id: sampulUtamaMaster.id,
            name: sampulUtamaMaster.name,
            description: sampulUtamaMaster.description,
            is_mandatory: true,
            min_photos: 1,
            max_photos: null,
            key: 'cat_' + sampulUtamaMaster.id
        });
    }
    
    // Hardcoded logic based on asset type
    const assetTypeName = selectedType.value.name.toLowerCase();
    let specificDefaults = [];
    
    if (assetTypeName.includes('rumah') || assetTypeName.includes('gedung') || assetTypeName.includes('ruko') || assetTypeName.includes('kost')) {
        specificDefaults = ['Tampak Depan', 'Ruangan Dalam', 'Kamar Mandi'];
    } else if (assetTypeName.includes('kendaraan') || assetTypeName.includes('mobil') || assetTypeName.includes('motor')) {
        specificDefaults = ['Tampak Samping', 'Tampak Depan Kendaraan', 'Interior/Kabin'];
    } else if (assetTypeName.includes('alat') || assetTypeName.includes('mesin')) {
        specificDefaults = ['Kondisi Fisik', 'Spesifikasi Mesin'];
    } else {
        specificDefaults = ['Foto Detail'];
    }
    
    // Add specific defaults if they exist in master
    specificDefaults.forEach(defName => {
        const masterItem = props.galleryCategories.find(c => c.name.toLowerCase() === defName.toLowerCase());
        if (masterItem) {
            defaultDraft.push({
                id: masterItem.id,
                name: masterItem.name,
                description: masterItem.description || '',
                is_mandatory: true,
                min_photos: 1,
                max_photos: null,
                key: 'cat_' + masterItem.id + '_' + Date.now()
            });
        }
    });

    if (lainnyaMaster) {
        defaultDraft.push({
            id: lainnyaMaster.id,
            name: lainnyaMaster.name,
            description: lainnyaMaster.description,
            is_mandatory: false,
            min_photos: null,
            max_photos: null,
            key: 'cat_' + lainnyaMaster.id
        });
    }

    draftGallery.value = defaultDraft;
    
    showResetModal.value = false;
    toastState.value = { show: true, message: 'Berhasil dikembalikan ke pengaturan default.', type: 'success' };
    setTimeout(() => toastState.value.show = false, 3000);
}

/* ── Save ─────────────────────────────────────────────────────────── */
function saveGallery() {
    if (!selectedType.value) return;
    isSaving.value = true;

    const categoriesPayload = draftGallery.value
        .filter(cat => cat.id !== null)
        .map((cat, index) => ({
            id: cat.id,
            is_mandatory: cat.is_mandatory,
            description: cat.description,
            min_photos: cat.min_photos,
            max_photos: cat.max_photos,
            sort_order: index + 1
        }));

    router.put(route('admin.konfigurasi-aset.gallery', selectedType.value.id), {
        scope:                galleryScope.value,
        categories:           categoriesPayload,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            const t = props.assetTypes.find(a => a.id === selectedType.value.id);
            if (t) {
                const savedCategories = JSON.parse(JSON.stringify(draftGallery.value.map((cat, index) => {
                    cat.sort_order = index + 1;
                    return cat;
                })));
                if (galleryScope.value === 'asset') t.gallery_categories = savedCategories;
                else t.unit_gallery_categories = savedCategories;
                selectedType.value = t;
            }
            setTimeout(() => { isSaving.value = false; }, 300);
        },
        onError: () => {
            isSaving.value = false;
        }
    });
}
</script>

<template>
    <Head title="Kategori Galeri - Admin Panel" />

    <DashboardLayout role="Admin" title="Kategori Galeri" description="Kelola konfigurasi kategori galeri wajib per tipe aset." no-padding hide-title>
        <template #leftAction>
            <div class="relative text-left w-full sm:w-[320px]" ref="dropdownRef">
                <button
                    @click="toggleDropdown"
                    class="flex items-center justify-between w-full gap-2.5 bg-white hover:bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 hover:border-[#FFC000] hover:shadow-sm transition-all focus:outline-none"
                >
                    <template v-if="!selectedType">
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                                <Building class="w-4 h-4" />
                            </div>
                            <div class="flex flex-col items-start flex-1 min-w-0">
                                <span class="text-xs text-slate-500 font-semibold leading-tight truncate w-full text-left">Pilih Konfigurasi</span>
                                <span class="text-sm font-black text-[#0A2540] leading-tight truncate w-full text-left">Pilih Tipe Aset...</span>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded bg-slate-50 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                                <Building class="w-4 h-4 text-[#FFC000]" />
                            </div>
                            <div class="flex flex-col items-start flex-1 min-w-0 text-left">
                                <span class="text-xs text-slate-500 font-semibold leading-tight truncate w-full text-left">Konfigurasi Aset</span>
                                <span class="text-sm font-black text-[#0A2540] leading-tight truncate w-full text-left">{{ selectedType.name }}</span>
                            </div>
                        </div>
                    </template>
                    <ChevronDown class="w-4 h-4 text-slate-400 ml-1 transition-transform" :class="isDropdownOpen ? 'rotate-180' : ''" />
                </button>

                <Transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <div v-if="isDropdownOpen" class="absolute left-0 mt-2 w-full bg-white rounded-xl shadow-xl border border-slate-100 z-[100] overflow-hidden">

                        <!-- Header Dropdown -->
                        <div class="p-2 border-b border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-3 w-full p-2 text-left">
                                <div class="flex flex-col items-start flex-1">
                                    <span class="text-sm font-bold text-slate-500">Konfigurasi Tipe Aset</span>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ assetTypes.length }} tipe tersedia</span>
                                </div>
                            </div>
                        </div>

                        <div class="max-h-[300px] overflow-y-auto p-2 space-y-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 pt-1 pb-2">Pilih Tipe Aset</p>

                            <button
                                v-for="type in assetTypes"
                                :key="type.id"
                                @click="selectAssetType(type.id)"
                                class="flex items-center gap-3 w-full p-2 rounded-lg hover:bg-slate-50 transition-colors group text-left border border-transparent"
                                :class="selectedTypeId === type.id ? 'bg-[#FFF8E6] !border-[#FFC000]/30' : ''"
                            >
                                <div class="w-8 h-8 rounded bg-slate-50 shrink-0 overflow-hidden border border-slate-200 flex items-center justify-center">
                                    <Building class="w-4 h-4" :class="selectedTypeId === type.id ? 'text-[#FFC000]' : 'text-slate-400'" />
                                </div>
                                <div class="flex flex-col items-start flex-1 min-w-0">
                                    <span class="text-sm font-bold text-[#0A2540] truncate w-full">{{ type.name }}</span>
                                    <span class="text-[10px] capitalize font-medium text-slate-500">{{ type.category }}</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </template>

        <div class="flex justify-center w-full min-h-[60vh]">
            <!-- Empty state -->
            <div v-if="!selectedType" class="w-full max-w-4xl flex flex-col items-center justify-center text-slate-400 gap-3 py-32">
                <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center">
                    <Search :size="24" class="text-slate-300" />
                </div>
                <p class="text-sm font-semibold">Pilih tipe aset di atas</p>
                <p class="text-xs">untuk mengatur konfigurasi kategori galeri wajib</p>
            </div>

            <div v-else class="flex flex-col lg:flex-row w-full items-start">

                <!-- ============================================== -->
                <!-- LEFT SIDEBAR (STICKY & FULL HEIGHT)            -->
                <!-- ============================================== -->
                <div class="w-full lg:w-[340px] shrink-0 bg-white border-r border-slate-200 flex flex-col h-[calc(100vh-121px)] lg:h-[calc(100vh-61px)] sticky top-[121px] lg:top-[61px] z-20 shadow-[4px_0_24px_-15px_rgba(0,0,0,0.1)]">

                    <!-- Scrollable Content Area -->
                    <div class="flex-1 overflow-y-auto bg-slate-50/30 flex flex-col relative [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none;">

                        <!-- ── TAB: Galeri Wajib ──────────────────────────── -->
                        <div class="flex flex-col h-full">
                            <!-- Scope toggle & Header -->
                            <div class="px-4 pt-3 pb-3 flex flex-col gap-3 sticky top-0 bg-slate-50/95 backdrop-blur z-20 border-b border-slate-200 shadow-sm">
                                <!-- Top row: Tabs -->
                                <div class="flex items-center w-full bg-slate-50 p-1 rounded-lg border border-slate-200/80 text-xs shadow-[inset_0_1px_2px_rgba(0,0,0,0.02)]">
                                    <button
                                        v-for="s in ['asset', 'unit']"
                                        :key="s"
                                        @click="galleryScope = s; onGalleryScopeChange()"
                                        :disabled="s === 'unit' && !selectedType.allow_units"
                                        class="flex-1 py-1.5 rounded-md font-bold transition-all capitalize text-center border"
                                        :class="[
                                            galleryScope === s
                                                ? 'bg-[#FFC000] border-amber-400 shadow-[inset_0_2px_4px_rgba(0,0,0,0.15)] text-[#0A2540]'
                                                : 'border-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-700',
                                            s === 'unit' && !selectedType.allow_units ? 'opacity-40 cursor-not-allowed' : ''
                                        ]"
                                    >
                                        {{ s === 'asset' ? 'Aset' : 'Unit' }}
                                    </button>
                                </div>

                                <!-- Bottom row: Title + Add Button -->
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-slate-800 text-[13px] whitespace-nowrap">Kategori Galeri</h3>
                                    <button @click.stop="addGallery" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 transition flex items-center gap-1 shrink-0">
                                        <Plus :size="14" /> Tambah
                                    </button>
                                </div>
                            </div>

                            <div class="flex-1 p-4 lg:p-5">
                                <draggable v-model="draftGallery" item-key="key" :animation="200" :move="checkMove" class="space-y-1.5 pb-2">
                                    <template #item="{ element: cat, index: idx }">
                                        <div class="relative overflow-hidden rounded-lg">
                                            <!-- Delete Background -->
                                            <div class="absolute inset-0 bg-rose-50 flex items-center justify-start px-4 rounded-lg border border-rose-100/50">
                                                <Trash2 class="text-rose-500 transition-transform duration-300" :class="swipeState.index === idx && swipeState.currentX > 60 ? 'scale-110' : 'scale-90 opacity-70'" :size="18" />
                                            </div>

                                            <!-- Foreground Item -->
                                            <div class="relative z-10 rounded-lg transition-transform border bg-white" 
                                                 :class="[
                                                     activeEditIndex === idx ? 'border-[#FFC000] shadow-sm' : 'border-slate-200 hover:border-slate-300 hover:shadow-sm',
                                                     swipeState.index === idx && !swipeState.isSwiping ? 'transition-transform duration-300' : ''
                                                 ]"
                                                 :style="swipeState.index === idx ? `transform: translateX(${swipeState.currentX}px)` : ''"
                                                 style="touch-action: pan-y;"
                                                 @touchstart="(e) => onTouchStart(e, idx, cat.name)"
                                                 @touchmove="onTouchMove"
                                                 @touchend="onTouchEnd"
                                                 @touchcancel="onTouchEnd">
                                                <div class="flex items-center px-4 py-3 gap-3 cursor-pointer group select-none" @click.stop="openSettings(idx, $event)" @contextmenu.prevent.stop="openSettings(idx, $event)">
                                                    <div v-if="cat.name !== 'Sampul Utama' && cat.name !== 'Lainnya'" class="drag-handle text-slate-300 group-hover:text-slate-400 cursor-grab active:cursor-grabbing p-0.5 -ml-1 transition-colors" title="Seret untuk memindahkan urutan" @click.stop>
                                                        <GripVertical :size="16" :stroke-width="2.5" />
                                                    </div>
                                                    <div v-else class="text-slate-300 p-0.5 -ml-1 cursor-not-allowed opacity-50" title="Kategori ini tidak dapat dipindahkan">
                                                        <Lock :size="16" :stroke-width="2.5" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[13px] font-bold truncate" :class="activeEditIndex === idx ? 'text-slate-800' : 'text-slate-600 group-hover:text-slate-700'">
                                                            {{ cat.name || 'Pilih Kategori' }}
                                                            <span v-if="cat.is_mandatory" class="text-rose-500 ml-0.5 text-xs">*</span>
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center pr-1 transition-colors" :class="activeEditIndex === idx ? 'text-slate-600' : 'text-slate-400 group-hover:text-slate-500'">
                                                        <ChevronLeft v-if="activeEditIndex === idx" :size="16" />
                                                        <ChevronRight v-else :size="16" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </draggable>

                                <div v-if="draftGallery.length === 0" class="text-center py-16 flex flex-col items-center justify-center gap-3">
                                    <div class="relative w-20 h-20 mb-2 flex items-center justify-center">
                                        <div class="absolute inset-0 bg-white border border-slate-200 rounded-xl shadow-sm rotate-6"></div>
                                        <div class="absolute inset-0 bg-white border border-slate-200 rounded-xl shadow-sm -rotate-3 flex items-center justify-center overflow-hidden">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21 19V5C21 3.9 20.1 3 19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19ZM8.5 13.5L11 16.51L14.5 12L19 18H5L8.5 13.5Z" fill="#334155"/>
                                                <circle cx="8" cy="8" r="2" fill="#FFC000"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="text-[15px] font-bold text-slate-700">Belum Ada Kategori Galeri yang Dikonfigurasi</span>
                                    <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                        Tambahkan kategori galeri untuk menentukan kategori foto yang wajib atau opsional pada setiap tipe aset.
                                    </span>
                                    <button @click.stop="addGallery" class="mt-4 bg-white border border-slate-200 hover:border-[#FFC000] hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                                        <Plus :size="16" class="text-[#FFC000]" /> Tambah Konfigurasi
                                    </button>
                                </div>
                            </div>

                            <!-- Footer Save Button -->
                            <div class="p-5 bg-white border-t border-slate-200 mt-auto sticky bottom-0 z-20 shadow-[0_-15px_30px_-10px_rgba(0,0,0,0.08)] flex items-stretch gap-2">
                                <button @click="resetToDefault" class="flex items-center justify-center w-11 shrink-0 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors shadow-sm" title="Kembalikan ke Default">
                                    <RotateCcw :size="20" :stroke-width="2.5" />
                                </button>
                                <button @click="saveGallery" :disabled="isSaving" class="flex-1 rounded-lg bg-[#FFC000] px-5 py-3 text-sm font-bold text-[#0A2540] hover:bg-amber-400 transition shadow-sm flex items-center justify-center gap-2 disabled:opacity-80">
                                    <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin text-[#0A2540]" />
                                    <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Konfigurasi Galeri' }}</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT CANVAS (LIVE PREVIEW)                    -->
                <!-- ============================================== -->
                <div class="flex-1 w-full bg-slate-50/70 p-6 lg:p-12 relative">


                    <!-- Live Preview Galeri Wajib -->
                    <div class="mt-8 max-w-4xl mx-auto pb-20">
                        <div class="space-y-8">

                            <!-- Dynamic List from draftGallery -->
                            <div v-for="(draftCat, index) in draftGallery" :key="draftCat.key || index" class="pointer-events-none transition-all duration-300">
                                <div class="flex items-baseline gap-2 mb-2">
                                    <label class="text-base font-bold text-[#0A2540]">{{ draftCat.name || 'Kategori Baru' }} <span v-if="draftCat.is_mandatory" class="text-rose-500">*</span></label>
                                    <span v-if="draftCat.name && draftCat.name !== 'Kategori Baru'" class="text-xs text-slate-500 font-medium">{{ draftCat.description || 'Pastikan area/fasilitas ' + draftCat.name.toLowerCase() + ' terlihat dengan jelas.' }}</span>
                                </div>
                                <div class="w-full rounded-2xl border-2 border-dashed border-slate-300 bg-white flex flex-col items-center justify-center py-10 shadow-sm">
                                    <div class="relative w-16 h-16 mb-4 flex items-center justify-center">
                                        <div class="absolute inset-0 bg-white border border-slate-200 rounded-lg shadow-sm rotate-6"></div>
                                        <div class="absolute inset-0 bg-white border border-slate-200 rounded-lg shadow-sm -rotate-3 flex items-center justify-center overflow-hidden">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21 19V5C21 3.9 20.1 3 19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19ZM8.5 13.5L11 16.51L14.5 12L19 18H5L8.5 13.5Z" fill="#334155"/>
                                                <circle cx="8" cy="8" r="2" fill="#FFC000"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-[#0A2540] mb-1">Pilih Foto atau Tarik ke sini</p>
                                    <p class="text-xs text-slate-400 font-medium mb-1">Format didukung: JPG, JPEG, PNG</p>
                                    <template v-if="draftCat.name !== 'Sampul Utama'">
                                        <p v-if="draftCat.min_photos > 0 || draftCat.max_photos > 0" class="text-[11px] font-bold text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md">
                                            <span>Min: {{ draftCat.min_photos > 0 ? draftCat.min_photos : 'Tidak Ada' }}</span>
                                            <span class="mx-1">&bull;</span>
                                            <span>Max: {{ draftCat.max_photos > 0 ? draftCat.max_photos : 'Tidak Ada' }}</span>
                                        </p>
                                    </template>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <Toast :show="toastState.show" :message="toastState.message" :type="toastState.type" />
        
        <!-- Reset Confirmation Modal -->
        <ConfirmModal
            :show="showResetModal"
            type="primary"
            title="Reset ke Default"
            message="Yakin ingin mereset konfigurasi galeri ke pengaturan default (bawaan) untuk tipe aset ini? Semua perubahan yang belum disimpan akan hilang."
            confirmText="Ya, Reset"
            cancelText="Batal"
            @confirm="executeReset"
            @cancel="showResetModal = false"
        >
            <template #icon>
                <div class="w-32 h-32 mx-auto mb-2 flex items-center justify-center">
                    <ResetIllustration />
                </div>
            </template>
        </ConfirmModal>

    </DashboardLayout>

    <!-- Field Settings Popover -->
    <Transition
        mode="out-in"
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-90 -translate-x-2"
        enter-to-class="opacity-100 scale-100 translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100 scale-100 translate-x-0"
        leave-to-class="opacity-0 scale-90 -translate-x-2"
    >
        <div v-if="activeEditIndex !== null" :key="activeEditIndex" class="fixed inset-0 lg:inset-auto z-[100] flex items-center justify-center lg:block p-4 lg:p-0 pointer-events-none popover-desktop lg:origin-left" :style="{ '--popover-top': popoverTop + 'px', '--popover-left': popoverLeft + 'px' }">
            <!-- Mobile backdrop -->
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm lg:hidden pointer-events-auto" @click="activeEditIndex = null"></div>

            <!-- Popover Content -->
            <div class="relative bg-white rounded-lg shadow-lg border border-slate-200 w-[260px] sm:w-[280px] flex flex-col max-h-[90vh] lg:max-h-[calc(100vh-80px)] pointer-events-auto" @click.stop>
                <div class="px-5 pt-5 pb-2 flex items-center justify-between">
                    <h3 class="font-bold text-[#0A2540] text-sm">Pengaturan Galeri</h3>
                    <button @click="activeEditIndex = null" class="text-slate-400 hover:text-slate-600 transition">
                        <X :size="16" />
                    </button>
                </div>

                <div class="px-5 py-2 space-y-4 overflow-y-auto custom-scrollbar">
                    <template v-if="draftGallery[activeEditIndex]">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600">Pilih Kategori Galeri</label>
                            <SearchableSelect
                                :modelValue="draftGallery[activeEditIndex].id"
                                @update:modelValue="updateGallerySelection(activeEditIndex, $event)"
                                :options="availableGalleryCategories"
                                placeholder="Ketik nama kategori..."
                                :disabled="draftGallery[activeEditIndex].name === 'Sampul Utama' || draftGallery[activeEditIndex].name === 'Lainnya'"
                            />
                        </div>

                        <div class="space-y-1.5 pt-1">
                            <label class="block text-xs font-semibold text-slate-600">Penjelasan Singkat</label>
                            <textarea v-model="draftGallery[activeEditIndex].description" rows="2" class="w-full text-sm font-medium text-slate-800 bg-slate-50/50 border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all placeholder:text-slate-400 resize-none" placeholder="Contoh: Pastikan area terlihat jelas..."></textarea>
                        </div>

                        <template v-if="draftGallery[activeEditIndex].name !== 'Sampul Utama'">
                            <div class="flex gap-3 pt-1">
                                <div class="flex-1 space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-600">Min Upload</label>
                                    <input type="number" min="0" v-model.number="draftGallery[activeEditIndex].min_photos" placeholder="Tidak Ada" class="w-full text-sm font-medium text-slate-800 bg-slate-50/50 border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all placeholder:text-slate-400">
                                </div>
                                <div class="flex-1 space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-600">Max Upload</label>
                                    <input type="number" min="0" v-model.number="draftGallery[activeEditIndex].max_photos" placeholder="Tidak Ada" class="w-full text-sm font-medium text-slate-800 bg-slate-50/50 border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all placeholder:text-slate-400">
                                </div>
                            </div>
                        </template>

                        <label class="flex items-center gap-3 cursor-pointer py-1" :class="(draftGallery[activeEditIndex].name === 'Sampul Utama' || draftGallery[activeEditIndex].name === 'Lainnya') ? 'opacity-50 pointer-events-none' : ''">
                            <input type="checkbox" v-model="draftGallery[activeEditIndex].is_mandatory" :disabled="draftGallery[activeEditIndex].name === 'Sampul Utama' || draftGallery[activeEditIndex].name === 'Lainnya'" class="w-4 h-4 rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540] cursor-pointer">
                            <span class="text-sm font-medium text-slate-700">Wajib Diupload</span>
                        </label>
                    </template>
                </div>

                <div class="p-3 flex flex-col gap-1 mt-2 mb-1">
                    <template v-if="draftGallery[activeEditIndex] && (draftGallery[activeEditIndex].name === 'Sampul Utama' || draftGallery[activeEditIndex].name === 'Lainnya')">
                        <div class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 bg-slate-50 cursor-not-allowed text-sm font-medium text-left">
                            <Lock :size="16" class="shrink-0" />
                            <span>Kategori Default Wajib</span>
                        </div>
                    </template>
                    <template v-else>
                        <button @click="removeGallery(activeEditIndex)" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-colors text-sm font-medium text-left">
                            <Trash2 :size="16" class="shrink-0" />
                            <span>Hapus Galeri</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
@media (min-width: 1024px) {
  .popover-desktop {
    top: var(--popover-top) !important;
    left: var(--popover-left) !important;
    bottom: auto !important;
  }
}
</style>
