<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, X, Plus, Trash2, ChevronDown, ChevronUp, CheckSquare, Check, Building, Layers, PenLine, GripVertical, ChevronLeft, ChevronRight, EyeOff, Loader2, RotateCcw } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';
import AssetIllustration from '@/Components/ui/Icons/AssetIllustration.vue';
import Toast from '@/Components/ui/Toast.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import ResetIllustration from '@/Components/ui/Icons/ResetIllustration.vue';
import draggable from 'vuedraggable';
import { nextTick } from 'vue';

/* ── Props ────────────────────────────────────────────────────────── */
const props = defineProps({
    jenisAset: { type: Array, default: () => [] },
    kategoriFasilitas: { type: Array, default: () => [] },
    tipeAsetMandatory: { type: Array, default: () => [] },
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
const selectedTypeId = ref('');
const selectedType = computed(() => props.jenisAset.find(t => t.id === selectedTypeId.value) || null);

const simulatedSelectedFacilities = ref([]);
function toggleSimulation(fasId) {
    const idx = simulatedSelectedFacilities.value.indexOf(fasId);
    if (idx > -1) {
        simulatedSelectedFacilities.value.splice(idx, 1);
    } else {
        simulatedSelectedFacilities.value.push(fasId);
    }
}

const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

const draftCategories = ref([]); // { id: Number, is_mandatory: Boolean }

const isSaving = ref(false);
const isInitializing = ref(false);
let saveTimeout = null;

/* ── Master Modal ─────────────────────────────────────────────────── */
const showMasterModal = ref(false);
const isEditMaster = ref(false);
const masterForm = ref({ id: null, name: '', is_active: true });

/* ── Add Kategori Sidebar Popover ─────────────────────────────────── */
const activeEditCategoryId = ref(null); // ID of the category being edited in the popover
const isAddPopoverOpen = ref(false); // To toggle the Add Category popover
const popoverTop = ref(0);
const popoverLeft = ref(0);
const pendingAddId = ref(null);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
};

const fasilitasPopoverTop = ref(0);
const fasilitasPopoverLeft = ref(0);

const closePopover = () => {
    activeEditCategoryId.value = null;
    isAddPopoverOpen.value = false;
    showFasilitasModal.value = false;
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
    document.addEventListener('click', closePopover);

    if (props.jenisAset && props.jenisAset.length > 0) {
        const lastSelected = localStorage.getItem('last_selected_tipe_aset_fas');
        let typeToSelect = props.jenisAset.find(t => t.id == lastSelected);
        if (!typeToSelect) typeToSelect = props.jenisAset[0];
        if (typeToSelect) selectAssetType(typeToSelect.id);
    }
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    document.removeEventListener('click', closePopover);
});

const fieldScope = ref('asset');

function loadDraft() {
    if (!selectedTypeId.value) return;
    isInitializing.value = true;
    const t = props.tipeAsetMandatory.find(tm => tm.id === selectedTypeId.value);
    if (!t) {
        draftCategories.value = [];
        nextTick(() => { isInitializing.value = false; });
        return;
    }
    
    if (fieldScope.value === 'asset') {
        draftCategories.value = [
            ...(t.mandatory_ids || []).map(cid => ({ id: cid, is_mandatory: true })),
            ...(t.optional_ids || []).map(cid => ({ id: cid, is_mandatory: false }))
        ];
    } else {
        draftCategories.value = [
            ...(t.unit_mandatory_ids || []).map(cid => ({ id: cid, is_mandatory: true })),
            ...(t.unit_optional_ids || []).map(cid => ({ id: cid, is_mandatory: false }))
        ];
    }
    nextTick(() => {
        isInitializing.value = false;
    });
}

watch(draftCategories, () => {
    if (!isInitializing.value) {
        isSaving.value = true;
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            saveMapping();
        }, 1000);
    }
}, { deep: true });

function onFieldScopeChange() { 
    activeEditCategoryId.value = null;
    isAddPopoverOpen.value = false;
    showFasilitasModal.value = false;
    loadDraft(); 
}

function selectAssetType(id) {
    if (id) {
        selectedTypeId.value = id;
        localStorage.setItem('last_selected_tipe_aset_fas', id);
        simulatedSelectedFacilities.value = [];
        loadDraft();
    }
    isDropdownOpen.value = false;
}

watch(() => props.tipeAsetMandatory, (newVal) => {
    if (!isSaving.value) loadDraft();
}, { deep: true });

/* ── MAPPING FASILITAS ────────────────────────────────────────────── */
const displayCategories = computed(() => {
    return draftCategories.value.map(item => {
        const cat = props.kategoriFasilitas.find(k => k.id === item.id);
        return cat ? { ...cat, is_mandatory: item.is_mandatory } : null;
    }).filter(k => k);
});

const availableToAdd = computed(() => {
    return props.kategoriFasilitas
        .filter(k => !draftCategories.value.some(c => c.id === k.id) && k.is_active)
        .map(k => ({ code: k.id, name: k.name }));
});

function openAddPopover(event) {
    isAddPopoverOpen.value = true;
    activeEditCategoryId.value = null;
    pendingAddId.value = null;
    isEditMaster.value = false;
    masterForm.value = { id: null, name: '', is_active: true };
    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        popoverTop.value = Math.max(16, Math.min(rect.top - 10, window.innerHeight - 280));
        popoverLeft.value = rect.right + 12;
    } else {
        popoverTop.value = window.innerHeight / 2 - 100;
        popoverLeft.value = window.innerWidth / 2 - 150;
    }
}

function openEditCategory(catId, event) {
    isAddPopoverOpen.value = false;
    activeEditCategoryId.value = catId;
    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        popoverTop.value = Math.max(16, Math.min(rect.top - 10, window.innerHeight - 340));
        popoverLeft.value = rect.right + 12;
    }
}

function confirmAdd() {
    if (pendingAddId.value && !draftCategories.value.some(c => c.id === pendingAddId.value)) {
        draftCategories.value.push({ id: pendingAddId.value, is_mandatory: false });
    }
    isAddPopoverOpen.value = false;
}

function removeCategory(id) {
    draftCategories.value = draftCategories.value.filter(cat => cat.id !== id);
}

/* ── Swipe to Delete ──────────────────────────────────────────────── */
const swipeState = ref({
    index: null, // we'll use the item ID for index to ensure uniqueness, or array index
    startX: 0,
    currentX: 0,
    isSwiping: false,
});

function onTouchStart(e, id) {
    if (e.target.closest('.drag-handle')) return;
    
    swipeState.value = {
        index: id,
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
        removeCategory(swipeState.value.index);
        activeEditCategoryId.value = null;
    }
    swipeState.value = { index: null, startX: 0, currentX: 0, isSwiping: false };
}

const showResetModal = ref(false);

function resetToDefault() {
    if (!selectedType.value) return;
    showResetModal.value = true;
}

function executeReset() {
    let defaultDraft = [];
    const assetTypeName = selectedType.value.name.toLowerCase();
    let specificDefaults = [];
    
    if (assetTypeName.includes('rumah') || assetTypeName.includes('gedung') || assetTypeName.includes('ruko')) {
        specificDefaults = ['Kamar Tidur', 'Kamar Mandi', 'Dapur', 'Ruang Tamu'];
    } else if (assetTypeName.includes('kendaraan') || assetTypeName.includes('mobil') || assetTypeName.includes('motor')) {
        specificDefaults = ['Fitur Kendaraan', 'Keamanan'];
    } else {
        specificDefaults = ['Fasilitas Umum'];
    }
    
    // Map existing masters
    specificDefaults.forEach(defName => {
        const masterItem = props.kategoriFasilitas.find(c => c.name.toLowerCase() === defName.toLowerCase());
        if (masterItem) {
            defaultDraft.push({
                id: masterItem.id,
                is_mandatory: true
            });
        }
    });

    // Also just pick first 2 random active categories as mandatory if empty, so it's not entirely blank
    if (defaultDraft.length === 0) {
        const activeMasters = props.kategoriFasilitas.filter(k => k.is_active).slice(0, 2);
        activeMasters.forEach(m => {
            defaultDraft.push({ id: m.id, is_mandatory: true });
        });
    }

    draftCategories.value = defaultDraft;
    
    showResetModal.value = false;
    toastState.value = { show: true, message: 'Berhasil dikembalikan ke pengaturan default.', type: 'success' };
    setTimeout(() => toastState.value.show = false, 3000);
}

function saveMapping() {
    if (!selectedType.value) return;
    isSaving.value = true;

    const mandatory = draftCategories.value.filter(c => c.is_mandatory).map(c => c.id);
    const optional = draftCategories.value.filter(c => !c.is_mandatory).map(c => c.id);

    router.put(route('admin.mandatory-fas.sync', selectedType.value.id), {
        scope: fieldScope.value,
        facility_category_ids: mandatory,
        optional_category_ids: optional
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            setTimeout(() => { isSaving.value = false; }, 300);
        },
        onError: () => {
            isSaving.value = false;
        }
    });
}

/* ── MASTER FASILITAS ─────────────────────────────────────────────── */
function openAddMaster() {
    isEditMaster.value = false;
    masterForm.value = { id: null, name: '', is_active: true };
    showMasterModal.value = true;
    isDropdownOpen.value = false;
}
function openEditMaster(cat) {
    isEditMaster.value = true;
    masterForm.value = { ...cat };
    showMasterModal.value = true;
    isDropdownOpen.value = false;
}
function submitMaster() {
    const routeName = isEditMaster.value ? 'admin.kategori-fas.update' : 'admin.kategori-fas.store';
    const routeArgs = isEditMaster.value ? route(routeName, masterForm.value.id) : route(routeName);
    const method = isEditMaster.value ? 'put' : 'post';

    router[method](routeArgs, masterForm.value, {
        preserveScroll: true,
        onSuccess: () => { showMasterModal.value = false; }
    });
}
function submitMasterFromPopover() {
    isEditMaster.value = false;
    if (!masterForm.value.name) return;
    
    const newName = masterForm.value.name;

    router.post(route('admin.kategori-fas.store'), masterForm.value, {
        preserveScroll: true,
        onSuccess: (page) => {
            masterForm.value.name = '';
            
            // Auto-apply kategori baru ke tipe aset yang sedang dipilih
            const newCat = page.props.kategoriFasilitas.find(k => k.name.toLowerCase() === newName.toLowerCase());
            if (newCat) {
                if (!draftCategories.value.some(c => c.id === newCat.id)) {
                    draftCategories.value.push({ id: newCat.id, is_mandatory: false });
                }
                isAddPopoverOpen.value = false;
                
                toastState.value = { show: true, message: 'Kategori berhasil dibuat & diterapkan!', type: 'success' };
                setTimeout(() => toastState.value.show = false, 3000);
            }
        }
    });
}
function deleteMaster(id) {
    if(confirm("Yakin ingin menghapus kategori fasilitas master ini?")) {
        router.delete(route('admin.kategori-fas.destroy', id), {
            preserveScroll: true,
            onSuccess: () => { showMasterModal.value = false; }
        });
    }
}
/* ── FASILITAS ITEM CRUD ───────────────────────────────────────────── */
const activePreviewCategory = computed(() => {
    return displayCategories.value.find(c => c.id === activeEditCategoryId.value) || null;
});

const activeDraftCategory = computed(() => {
    return draftCategories.value.find(c => c.id === activeEditCategoryId.value) || null;
});

const getCategory = (id) => props.kategoriFasilitas.find(k => k.id === id);

const showFasilitasModal = ref(false);
const isEditFasilitas = ref(false);
const fasilitasForm = ref({ id: null, facility_category_id: null, name: '', is_active: true });

function openAddFasilitas(categoryId, event) {
    isEditFasilitas.value = false;
    fasilitasForm.value = { id: null, facility_category_id: categoryId, name: '', is_active: true };
    showFasilitasModal.value = true;
    
    isAddPopoverOpen.value = false;
    activeEditCategoryId.value = null;

    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        fasilitasPopoverTop.value = Math.min(rect.bottom + 8, window.innerHeight - 320);
        
        const popoverWidth = 280;
        let leftPos = rect.left;
        if (leftPos + popoverWidth > window.innerWidth) {
            leftPos = window.innerWidth - popoverWidth - 16;
        }
        fasilitasPopoverLeft.value = leftPos;
    } else {
        fasilitasPopoverTop.value = window.innerHeight / 2 - 100;
        fasilitasPopoverLeft.value = window.innerWidth / 2 - 140;
    }
}
function openEditFasilitas(categoryId, fas, event) {
    isEditFasilitas.value = true;
    fasilitasForm.value = { ...fas, facility_category_id: categoryId };
    showFasilitasModal.value = true;
    
    isAddPopoverOpen.value = false;
    activeEditCategoryId.value = null;

    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        fasilitasPopoverTop.value = Math.min(rect.bottom + 8, window.innerHeight - 320);
        
        const popoverWidth = 280;
        let leftPos = rect.left;
        if (leftPos + popoverWidth > window.innerWidth) {
            leftPos = window.innerWidth - popoverWidth - 16;
        }
        fasilitasPopoverLeft.value = leftPos;
    } else {
        fasilitasPopoverTop.value = window.innerHeight / 2 - 100;
        fasilitasPopoverLeft.value = window.innerWidth / 2 - 140;
    }
}
function submitFasilitas() {
    const routeName = isEditFasilitas.value ? 'admin.jenis-fas.update' : 'admin.jenis-fas.store';
    const routeArgs = isEditFasilitas.value ? route(routeName, fasilitasForm.value.id) : route(routeName);
    const method = isEditFasilitas.value ? 'put' : 'post';

    router[method](routeArgs, fasilitasForm.value, {
        preserveScroll: true,
        onSuccess: () => { showFasilitasModal.value = false; }
    });
}
function deleteFasilitas(id) {
    if(confirm("Yakin ingin menghapus fasilitas ini?")) {
        router.delete(route('admin.jenis-fas.destroy', id), {
            preserveScroll: true,
            onSuccess: () => { showFasilitasModal.value = false; }
        });
    }
}
</script>

<template>
    <Head title="Fasilitas Aset - Admin Panel" />

    <DashboardLayout role="Admin" title="Fasilitas Aset" description="Kelola pemetaan Kategori Fasilitas yang wajib diisi untuk tiap Tipe Aset." no-padding hide-title>
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
                    <div v-if="isDropdownOpen" class="absolute left-0 mt-2 w-full bg-white rounded-xl shadow-xl border border-slate-100 z-[100] overflow-hidden flex flex-col">

                        <div class="max-h-[300px] overflow-y-auto p-2 space-y-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 pt-1 pb-2">Pilih Tipe Aset</p>

                            <button
                                v-for="type in jenisAset"
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
                                    <span class="text-[10px] capitalize font-medium text-slate-500">{{ type.category?.name || 'Kategori' }}</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </template>

        <div class="flex justify-center w-full min-h-[60vh]">
            <div v-if="!selectedType" class="w-full max-w-4xl flex flex-col items-center justify-center text-slate-400 gap-3 py-32">
                <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center">
                    <Search :size="24" class="text-slate-300" />
                </div>
                <p class="text-sm font-semibold">Pilih Tipe Aset di atas</p>
                <p class="text-xs">untuk mengatur fasilitas wajib</p>
            </div>

            <div v-else class="flex flex-col lg:flex-row w-full items-start">
                <!-- ============================================== -->
                <!-- LEFT SIDEBAR                                   -->
                <!-- ============================================== -->
                <div class="w-full lg:w-[340px] shrink-0 bg-white border-r border-slate-200 flex flex-col h-[calc(100vh-121px)] lg:h-[calc(100vh-61px)] sticky top-[121px] lg:top-[61px] z-20 shadow-[4px_0_24px_-15px_rgba(0,0,0,0.1)]">
                    <div class="flex-1 overflow-y-auto bg-slate-50/30 flex flex-col relative [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none;">
                        <div class="flex flex-col h-full">
                            <div class="px-4 pt-3 pb-3 flex flex-col gap-3 sticky top-0 bg-slate-50/95 backdrop-blur z-20 border-b border-slate-200 shadow-sm">
                                <!-- Top row: Tabs -->
                                <div class="flex items-center w-full bg-slate-50 p-1 rounded-lg border border-slate-200/80 text-xs shadow-[inset_0_1px_2px_rgba(0,0,0,0.02)]">
                                    <button
                                        v-for="s in ['asset', 'unit']"
                                        :key="s"
                                        @click="fieldScope = s; onFieldScopeChange()"
                                        :disabled="s === 'unit' && !selectedType.allow_units"
                                        class="flex-1 py-1.5 rounded-md font-bold transition-all capitalize text-center border"
                                        :class="[
                                            fieldScope === s
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
                                    <h3 class="font-bold text-slate-800 text-[13px] whitespace-nowrap">Kategori Fasilitas</h3>
                                    <button @click.stop="openAddPopover($event)" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 transition flex items-center gap-1 shrink-0">
                                        <Plus :size="14" /> Tambah
                                    </button>
                                </div>
                            </div>

                            <div class="flex-1 p-4 lg:p-5">
                                <draggable v-model="draftCategories" item-key="id" :animation="200" class="space-y-1.5 pb-2">
                                    <template #item="{ element: catItem }">
                                        <div class="relative overflow-hidden rounded-lg">
                                            <!-- Delete Background -->
                                            <div class="absolute inset-0 bg-rose-50 flex items-center justify-start px-4 rounded-lg border border-rose-100/50">
                                                <Trash2 class="text-rose-500 transition-transform duration-300" :class="swipeState.index === catItem.id && swipeState.currentX > 60 ? 'scale-110' : 'scale-90 opacity-70'" :size="18" />
                                            </div>

                                            <!-- Foreground Item -->
                                            <div class="relative z-10 rounded-lg transition-transform border bg-white" 
                                                 :class="[
                                                     activeEditCategoryId === catItem.id ? 'border-[#FFC000] shadow-sm' : 'border-slate-200 hover:border-slate-300 hover:shadow-sm',
                                                     swipeState.index === catItem.id && !swipeState.isSwiping ? 'transition-transform duration-300' : ''
                                                 ]"
                                                 :style="swipeState.index === catItem.id ? `transform: translateX(${swipeState.currentX}px)` : ''"
                                                 style="touch-action: pan-y;"
                                                 @touchstart="(e) => onTouchStart(e, catItem.id)"
                                                 @touchmove="onTouchMove"
                                                 @touchend="onTouchEnd"
                                                 @touchcancel="onTouchEnd">
                                                <div class="flex items-center px-4 py-3 gap-3 cursor-pointer group select-none" @click.stop="openEditCategory(catItem.id, $event)" @contextmenu.prevent.stop="openEditCategory(catItem.id, $event)">
                                                    <div class="drag-handle text-slate-300 group-hover:text-slate-400 cursor-grab active:cursor-grabbing p-0.5 -ml-1 transition-colors" title="Seret untuk memindahkan urutan" @click.stop>
                                                        <GripVertical :size="16" :stroke-width="2.5" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[13px] font-bold truncate" :class="activeEditCategoryId === catItem.id ? 'text-slate-800' : 'text-slate-600 group-hover:text-slate-700'">
                                                            {{ getCategory(catItem.id)?.name || 'Kategori' }}
                                                            <span v-if="catItem.is_mandatory" class="text-rose-500 ml-0.5 text-xs">*</span>
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center pr-1 transition-colors" :class="activeEditCategoryId === catItem.id ? 'text-slate-600' : 'text-slate-400 group-hover:text-slate-500'">
                                                        <ChevronLeft v-if="activeEditCategoryId === catItem.id" :size="16" />
                                                        <ChevronRight v-else :size="16" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </draggable>

                                <div v-if="draftCategories.length === 0" class="text-center py-12 flex flex-col items-center justify-center gap-3">
                                    <div class="w-32 h-32 mb-2">
                                        <AssetIllustration class="w-full h-full object-contain drop-shadow-sm opacity-90" />
                                    </div>
                                    <span class="text-[15px] font-bold text-slate-700">Belum Ada Mapping</span>
                                    <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                        Tambahkan kategori fasilitas untuk tipe aset ini.
                                    </span>
                                    <button @click.stop="openAddPopover($event)" class="mt-4 bg-white border border-slate-200 hover:border-[#FFC000] hover:bg-slate-50 text-[#0A2540] font-semibold px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm focus:outline-none">
                                        <Plus :size="16" class="text-[#FFC000]" /> Tambah Kategori
                                    </button>
                                </div>
                            </div>

                            <div class="p-5 bg-white border-t border-slate-200 mt-auto sticky bottom-0 z-20 shadow-[0_-15px_30px_-10px_rgba(0,0,0,0.08)] flex items-stretch gap-2">
                                <button @click="resetToDefault" class="flex items-center justify-center w-11 shrink-0 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors shadow-sm" title="Kembalikan ke Default">
                                    <RotateCcw :size="20" :stroke-width="2.5" />
                                </button>
                                <button @click="saveMapping" :disabled="isSaving" class="flex-1 rounded-lg bg-[#FFC000] px-5 py-3 text-sm font-bold text-[#0A2540] hover:bg-amber-400 transition shadow-sm flex items-center justify-center gap-2 disabled:opacity-80">
                                    <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin text-[#0A2540]" />
                                    <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Pemetaan' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT CANVAS (LIVE PREVIEW)                    -->
                <!-- ============================================== -->
                <div class="flex-1 w-full bg-slate-50/70 p-6 lg:p-12 relative flex justify-center">
                    <div class="w-full max-w-3xl self-start mt-8 space-y-8">
                        <div v-if="displayCategories.length === 0" class="w-full flex flex-col items-center justify-center py-12 px-4">
                            <div class="max-w-md w-full text-center space-y-6">
                                <div class="w-48 h-48 sm:w-56 sm:h-56 mx-auto mb-2">
                                    <AssetIllustration class="w-full h-full object-contain drop-shadow-sm opacity-90" />
                                </div>
                                <div class="space-y-3">
                                    <h3 class="text-xl sm:text-2xl font-bold text-[#0A2540]">
                                        Belum Ada Kategori Fasilitas
                                    </h3>
                                    <p class="text-sm sm:text-base text-slate-500 leading-relaxed px-4">
                                        Tambahkan kategori fasilitas (seperti Kamar Tidur, Dapur, dll) pada panel di sebelah kiri untuk ditampilkan di halaman properti.
                                    </p>
                                </div>
                                <div class="pt-4 flex justify-center">
                                    <button @click.stop="openAddPopover($event)" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white border border-slate-200 text-[#0A2540] font-bold rounded-xl hover:bg-slate-50 hover:border-slate-300 hover:shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:ring-offset-2">
                                        <Plus :size="18" class="text-[#FFC000]" />
                                        Tambah Kategori
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="w-full space-y-8 pb-16">
                            <div class="mb-4">
                                <h2 class="text-lg font-bold text-[#0A2540] mb-2">Fasilitas {{ selectedType?.name || '' }} {{ fieldScope === 'unit' ? '(Unit)' : '' }}</h2>
                                <p v-if="fieldScope === 'asset'" class="text-sm text-slate-500">Pilih fasilitas yang tersedia secara umum dan dapat digunakan oleh penyewa. Fasilitas ini berlaku untuk seluruh area aset.</p>
                                <p v-else class="text-sm text-slate-500">Pilih fasilitas yang tersedia di dalam unit/kamar yang dapat digunakan langsung secara pribadi oleh penyewa.</p>
                            </div>

                            <!-- Fasilitas Dasar (Wajib) -->
                            <div v-if="displayCategories.filter(c => c.is_mandatory).length > 0">
                                <h3 class="text-base font-bold text-[#0A2540] mb-4">Fasilitas Dasar <span class="text-rose-500">*</span></h3>
                                <div class="space-y-4">
                                    <template v-for="cat in displayCategories.filter(c => c.is_mandatory)" :key="cat.id">
                                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)]">
                                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                                                <h4 class="font-bold text-[#0A2540] text-[15px]">{{ cat.name }}</h4>
                                            </div>
                                            <div class="p-5 flex flex-wrap gap-3">
                                                <div v-if="!cat.facilities || cat.facilities.length === 0" class="text-sm text-slate-400 italic w-full">
                                                    Belum ada fasilitas di kategori ini.
                                                </div>
                                                <template v-else>
                                                    <button v-for="fas in cat.facilities" :key="fas.id" @click.prevent.stop="openEditFasilitas(cat.id, fas, $event)" @contextmenu.prevent.stop="openEditFasilitas(cat.id, fas, $event)" class="border rounded-full px-5 py-2.5 text-[13px] font-semibold transition-all focus:outline-none flex items-center justify-center gap-1.5" :class="fas.is_active ? 'bg-white border-slate-200 text-slate-700 hover:border-[#FFC000] hover:bg-amber-50/50' : 'bg-slate-50 border-slate-200 text-slate-400 opacity-70 border-dashed'">
                                                        <EyeOff v-if="!fas.is_active" :size="14" class="text-slate-400 shrink-0" />
                                                        <span>{{ fas.name }}</span>
                                                    </button>
                                                </template>
                                                <button @click.stop="openAddFasilitas(cat.id, $event)" class="border border-dashed border-slate-300 text-slate-500 rounded-full px-5 py-2.5 text-[13px] font-semibold hover:border-[#FFC000] hover:text-[#FFC000] transition-colors flex items-center gap-1.5 bg-slate-50 hover:bg-amber-50">
                                                    <Plus :size="14" /> Tambah Fasilitas
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Fasilitas Lainnya (Opsional) -->
                            <div v-if="displayCategories.filter(c => !c.is_mandatory).length > 0">
                                <h3 class="text-base font-bold text-[#0A2540] mb-4 mt-8">Fasilitas Lainnya</h3>
                                <div class="space-y-4">
                                    <template v-for="cat in displayCategories.filter(c => !c.is_mandatory)" :key="cat.id">
                                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)]">
                                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                                                <h4 class="font-bold text-[#0A2540] text-[15px]">{{ cat.name }}</h4>
                                                <ChevronUp class="w-4 h-4 text-slate-400" />
                                            </div>
                                            <div class="p-5 flex flex-wrap gap-3">
                                                <div v-if="!cat.facilities || cat.facilities.length === 0" class="text-sm text-slate-400 italic w-full">
                                                    Belum ada fasilitas di kategori ini.
                                                </div>
                                                <template v-else>
                                                    <button v-for="fas in cat.facilities" :key="fas.id" @click.prevent.stop="openEditFasilitas(cat.id, fas, $event)" @contextmenu.prevent.stop="openEditFasilitas(cat.id, fas, $event)" class="border rounded-full px-5 py-2.5 text-[13px] font-semibold transition-all focus:outline-none flex items-center justify-center gap-1.5" :class="fas.is_active ? 'bg-white border-slate-200 text-slate-700 hover:border-[#FFC000] hover:bg-amber-50/50' : 'bg-slate-50 border-slate-200 text-slate-400 opacity-70 border-dashed'">
                                                        <EyeOff v-if="!fas.is_active" :size="14" class="text-slate-400 shrink-0" />
                                                        <span>{{ fas.name }}</span>
                                                    </button>
                                                </template>
                                                <button @click.stop="openAddFasilitas(cat.id, $event)" class="border border-dashed border-slate-300 text-slate-500 rounded-full px-5 py-2.5 text-[13px] font-semibold hover:border-[#FFC000] hover:text-[#FFC000] transition-colors flex items-center gap-1.5 bg-slate-50 hover:bg-amber-50">
                                                    <Plus :size="14" /> Tambah Fasilitas
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Toast :show="toastState.show" :message="toastState.message" :type="toastState.type" />
        
        <!-- Reset Confirmation Modal -->
        <ConfirmModal
            :show="showResetModal"
            type="primary"
            title="Reset ke Default"
            message="Yakin ingin mereset fasilitas ke pengaturan default (bawaan) untuk tipe aset ini? Semua perubahan yang belum disimpan akan hilang."
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

    <!-- Popover Tambah Fasilitas -->
    <Transition
        mode="out-in"
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-90 -translate-y-2"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-90 -translate-y-2"
    >
        <div v-if="showFasilitasModal" class="fixed inset-0 lg:inset-auto z-[100] flex items-center justify-center lg:block p-4 lg:p-0 pointer-events-none popover-desktop lg:origin-top-left" :style="{ '--popover-top': fasilitasPopoverTop + 'px', '--popover-left': fasilitasPopoverLeft + 'px' }">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm lg:hidden pointer-events-auto" @click="showFasilitasModal = false"></div>

            <div class="relative bg-white rounded-lg shadow-lg border border-slate-200 w-[260px] sm:w-[280px] flex flex-col pointer-events-auto" @click.stop>
                <div class="px-5 pt-5 pb-2 flex items-center justify-between">
                    <h3 class="font-bold text-[#0A2540] text-sm">{{ isEditFasilitas ? 'Edit' : 'Tambah' }} Fasilitas</h3>
                    <button @click="showFasilitasModal = false" class="text-slate-400 hover:text-slate-600 transition"><X :size="16" /></button>
                </div>

                <div class="px-5 py-3 space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-700 mb-1.5 block">Nama Fasilitas</label>
                        <input v-model="fasilitasForm.name" type="text" class="w-full text-sm font-medium text-slate-800 bg-slate-50/50 border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all placeholder:text-slate-400" placeholder="Misal: Kulkas, Kompor" />
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer py-1">
                        <input type="checkbox" v-model="fasilitasForm.is_active" :true-value="false" :false-value="true" class="w-4 h-4 rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540] cursor-pointer" />
                        <span class="text-sm font-medium text-slate-700">Sembunyikan Fasilitas ini</span>
                    </label>
                </div>

                <div class="p-3 flex flex-col gap-1 mt-2 mb-1">
                    <button v-if="isEditFasilitas" @click="deleteFasilitas(fasilitasForm.id); showFasilitasModal = false" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-colors text-sm font-medium text-left">
                        <Trash2 :size="16" class="shrink-0" />
                        <span>Hapus Fasilitas</span>
                    </button>
                    <button @click="submitFasilitas" :disabled="!fasilitasForm.name" class="w-full bg-[#FFC000] text-[#0A2540] text-xs font-bold py-2.5 rounded-lg hover:bg-amber-400 transition disabled:opacity-50 text-center">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Popover Tambah Mandatory -->
    <Transition
        mode="out-in"
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-90 -translate-x-2"
        enter-to-class="opacity-100 scale-100 translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100 scale-100 translate-x-0"
        leave-to-class="opacity-0 scale-90 -translate-x-2"
    >
        <div v-if="isAddPopoverOpen" class="fixed inset-0 lg:inset-auto z-[100] flex items-center justify-center lg:block p-4 lg:p-0 pointer-events-none popover-desktop lg:origin-left" :style="{ '--popover-top': popoverTop + 'px', '--popover-left': popoverLeft + 'px' }">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm lg:hidden pointer-events-auto" @click="isAddPopoverOpen = false"></div>

            <div class="relative bg-white rounded-xl shadow-[0_10px_30px_-5px_rgba(0,0,0,0.15)] border border-slate-200 w-full max-w-[300px] flex flex-col pointer-events-auto overflow-visible" @click.stop>
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 rounded-t-xl">
                    <h3 class="font-bold text-slate-700 text-sm">Pilih Kategori Fasilitas</h3>
                    <button @click="isAddPopoverOpen = false" class="text-slate-400 hover:text-rose-500 transition">
                        <X :size="16" />
                    </button>
                </div>

                <div class="p-4 space-y-4 overflow-visible">
                    <div class="relative mt-2">
                        <SearchableSelect
                            :modelValue="pendingAddId"
                            @update:modelValue="pendingAddId = $event"
                            :options="availableToAdd"
                            placeholder="Ketik nama kategori..."
                        />
                    </div>
                    <p v-if="availableToAdd.length === 0" class="text-xs text-rose-500 font-medium">Semua kategori sudah ditambahkan ke tipe ini.</p>
                </div>

                <div class="px-3 py-2 flex flex-col gap-1.5 bg-slate-50 border-t border-slate-100">
                    <button @click="confirmAdd" :disabled="!pendingAddId" class="w-full bg-[#FFC000] text-[#0A2540] text-xs font-bold py-2 rounded-lg hover:bg-amber-400 transition disabled:opacity-50">
                        Tambahkan
                    </button>
                </div>
                <div class="px-4 pt-3 pb-2 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Atau Buat Baru</span>
                </div>
                <div class="px-3 pb-3 bg-slate-50 rounded-b-xl space-y-2">
                    <input v-model="masterForm.name" type="text" placeholder="Nama Kategori Baru..." class="w-full text-sm font-medium text-slate-800 bg-white border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all placeholder:text-slate-400" @keyup.enter="submitMasterFromPopover" />
                    <button @click="submitMasterFromPopover" :disabled="!masterForm.name" class="w-full bg-[#FFC000] text-[#0A2540] text-xs font-bold py-2 rounded-lg hover:bg-amber-400 transition disabled:opacity-50">
                        Simpan Kategori Baru
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Popover Edit Kategori (Manage Fasilitas) -->
    <Transition
        mode="out-in"
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-90 -translate-x-2"
        enter-to-class="opacity-100 scale-100 translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100 scale-100 translate-x-0"
        leave-to-class="opacity-0 scale-90 -translate-x-2"
    >
        <div v-if="activeEditCategoryId !== null"
             class="fixed inset-0 lg:inset-auto z-[100] flex items-center justify-center lg:block p-4 lg:p-0 pointer-events-none popover-desktop lg:origin-left"
             :style="{ '--popover-top': popoverTop + 'px', '--popover-left': popoverLeft + 'px' }">
             <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm lg:hidden pointer-events-auto" @click="activeEditCategoryId = null"></div>

             <div class="relative bg-white rounded-lg shadow-lg border border-slate-200 w-[260px] sm:w-[280px] flex flex-col pointer-events-auto" @click.stop>
                 <div class="px-5 pt-5 pb-2 flex items-center justify-between">
                     <h3 class="font-bold text-[#0A2540] text-sm">Pengaturan Fasilitas</h3>
                     <button @click="activeEditCategoryId = null" class="text-slate-400 hover:text-slate-600 transition">
                         <X :size="16" />
                     </button>
                 </div>

                 <div class="px-5 py-2 space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer py-1" v-if="activeDraftCategory">
                        <input type="checkbox" v-model="activeDraftCategory.is_mandatory" class="w-4 h-4 rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540] cursor-pointer">
                        <span class="text-sm font-medium text-slate-700">Kategori Wajib</span>
                    </label>
                 </div>

                 <div class="p-3 flex flex-col gap-1 mt-2 mb-1">
                     <button @click="removeCategory(activeEditCategoryId); activeEditCategoryId = null" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-colors text-sm font-medium text-left">
                         <Trash2 :size="16" class="shrink-0" />
                         <span>Hapus Kategori</span>
                     </button>
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
