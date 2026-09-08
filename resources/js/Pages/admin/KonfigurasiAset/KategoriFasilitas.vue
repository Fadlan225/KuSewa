<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, X, Plus, Trash2, ChevronDown, CheckSquare, Building, Layers, PenLine, GripVertical, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';
import draggable from 'vuedraggable';

/* ── Props ────────────────────────────────────────────────────────── */
const props = defineProps({
    jenisAset: { type: Array, default: () => [] },
    kategoriFasilitas: { type: Array, default: () => [] },
    tipeAsetMandatory: { type: Array, default: () => [] },
});

const page = usePage();

/* ── State ────────────────────────────────────────────────────────── */
const selectedTypeId = ref('');
const selectedType = computed(() => props.jenisAset.find(t => t.id === selectedTypeId.value) || null);

const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

const draftCategories = ref([]); // { id: Number, is_mandatory: Boolean }

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

const closePopover = () => {
    activeEditCategoryId.value = null;
    isAddPopoverOpen.value = false;
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

function selectAssetType(id) {
    if (id) {
        selectedTypeId.value = id;
        localStorage.setItem('last_selected_tipe_aset_fas', id);
        
        // Load draft
        const t = props.tipeAsetMandatory.find(tm => tm.id === id);
        if (t) {
            draftCategories.value = [
                ...(t.mandatory_ids || []).map(cid => ({ id: cid, is_mandatory: true })),
                ...(t.optional_ids || []).map(cid => ({ id: cid, is_mandatory: false }))
            ];
        } else {
            draftCategories.value = [];
        }
    }
    isDropdownOpen.value = false;
}

watch(() => props.tipeAsetMandatory, (newVal) => {
    if (selectedTypeId.value) {
        const t = newVal.find(tm => tm.id === selectedTypeId.value);
        if (t) {
            draftCategories.value = [
                ...(t.mandatory_ids || []).map(cid => ({ id: cid, is_mandatory: true })),
                ...(t.optional_ids || []).map(cid => ({ id: cid, is_mandatory: false }))
            ];
        }
    }
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
    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        popoverTop.value = Math.min(rect.top - 10, window.innerHeight - 200);
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
        popoverTop.value = Math.min(rect.top - 10, window.innerHeight - 300);
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

function saveMapping() {
    if (!selectedType.value) return;
    
    const mandatory = draftCategories.value.filter(c => c.is_mandatory).map(c => c.id);
    const optional = draftCategories.value.filter(c => !c.is_mandatory).map(c => c.id);

    router.put(route('admin.mandatory-fas.sync', selectedType.value.id), {
        facility_category_ids: mandatory,
        optional_category_ids: optional
    }, { preserveScroll: true });
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

function openAddFasilitas(categoryId) {
    isEditFasilitas.value = false;
    fasilitasForm.value = { id: null, facility_category_id: categoryId, name: '', is_active: true };
    showFasilitasModal.value = true;
}
function openEditFasilitas(categoryId, fas) {
    isEditFasilitas.value = true;
    fasilitasForm.value = { ...fas, facility_category_id: categoryId };
    showFasilitasModal.value = true;
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

    <DashboardLayout role="Admin" title="Fasilitas Aset" description="Kelola pemetaan Kategori Fasilitas yang wajib diisi untuk tiap Tipe Aset." no-padding>
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

                        <div class="p-2 border-b border-slate-50 flex items-center justify-between">
                            <div class="flex flex-col items-start flex-1 p-2">
                                <span class="text-sm font-bold text-slate-500">Master Data</span>
                            </div>
                            <button @click.stop="openAddMaster" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded-md transition flex items-center gap-1 shrink-0">
                                <Layers :size="12" /> Kelola Master Fasilitas
                            </button>
                        </div>

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
                    <div v-if="page.props.flash?.success" class="bg-emerald-50 px-6 py-3 text-sm font-semibold text-emerald-700 border-b border-emerald-100 flex items-center gap-2">
                        {{ page.props.flash.success }}
                    </div>
                    <div v-if="page.props.flash?.error" class="bg-rose-50 px-6 py-3 text-sm font-semibold text-rose-700 border-b border-rose-100 flex items-center gap-2">
                        {{ page.props.flash.error }}
                    </div>

                    <div class="flex-1 overflow-y-auto bg-slate-50/30 flex flex-col relative [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none;">
                        <div class="flex flex-col h-full">
                            <div class="px-4 pt-4 pb-3 flex flex-col gap-3 sticky top-0 bg-slate-50/95 backdrop-blur z-20 border-b border-slate-200 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-slate-800 text-[13px] whitespace-nowrap">Kategori Fasilitas</h3>
                                    <button @click.stop="openAddPopover($event)" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 transition flex items-center gap-1 shrink-0">
                                        <Plus :size="14" /> Tambah
                                    </button>
                                </div>
                            </div>

                            <div class="flex-1 p-4 lg:p-5">
                                <draggable v-model="draftCategories" item-key="id" handle=".drag-handle" :animation="200" class="space-y-1 pb-2">
                                    <template #item="{ element: catItem }">
                                        <div class="rounded-lg transition-colors border" :class="activeEditCategoryId === catItem.id ? 'bg-slate-100 border-slate-200' : 'border-slate-200/60 hover:bg-slate-50 bg-white'">
                                            <div class="flex items-center px-3 py-2.5 gap-2.5 cursor-pointer group select-none" @click.stop="openEditCategory(catItem.id, $event)">
                                                <div class="drag-handle text-slate-300 group-hover:text-slate-400 cursor-grab active:cursor-grabbing p-0.5 -ml-1 transition-colors" title="Seret untuk memindahkan urutan" @click.stop>
                                                    <GripVertical :size="14" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[13px] font-semibold truncate" :class="activeEditCategoryId === catItem.id ? 'text-slate-800' : 'text-slate-600'">
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
                                    </template>
                                </draggable>
                                
                                <div v-if="draftCategories.length === 0" class="text-center py-16 flex flex-col items-center justify-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-300">
                                        <Layers :size="24" />
                                    </div>
                                    <span class="text-[15px] font-bold text-slate-700">Belum Ada Mapping</span>
                                    <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                        Tambahkan kategori fasilitas untuk tipe aset ini.
                                    </span>
                                </div>
                            </div>

                            <div class="p-5 bg-white border-t border-slate-200 mt-auto sticky bottom-0 z-20 shadow-[0_-15px_30px_-10px_rgba(0,0,0,0.08)]">
                                <button @click="saveMapping" class="w-full rounded-lg bg-[#FFC000] px-5 py-3 text-sm font-bold text-[#0A2540] hover:bg-amber-400 transition shadow-sm flex items-center justify-center gap-2">
                                    Simpan Pemetaan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT CANVAS (LIVE PREVIEW)                    -->
                <!-- ============================================== -->
                <div class="flex-1 w-full bg-slate-50/70 p-6 lg:p-12 relative flex justify-center">
                    <div class="w-full max-w-3xl self-start mt-8 space-y-4">
                        <div v-if="displayCategories.length === 0" class="text-center py-10 text-slate-400 font-semibold border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
                            Belum ada kategori fasilitas yang ditampilkan.
                        </div>

                        <div v-for="cat in displayCategories" :key="cat.id" class="bg-white border border-slate-200 rounded-xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]" :class="!cat.is_mandatory ? 'opacity-90' : ''">
                            <h3 class="text-[15px] font-bold text-slate-800 mb-4">{{ cat.name }} 
                                <span v-if="cat.is_mandatory" class="text-rose-500 font-semibold text-xs ml-1">*wajib</span>
                                <span v-else class="text-slate-500 font-semibold text-xs ml-1">(opsional)</span>
                            </h3>
                            
                            <div v-if="!cat.facilities || cat.facilities.length === 0" class="text-sm text-slate-400 italic">
                                Belum ada fasilitas di kategori ini.
                            </div>
                            <div v-else class="flex flex-wrap gap-2.5">
                                <label v-for="fas in cat.facilities" :key="fas.id" class="flex items-center gap-2 border border-slate-200 rounded-lg px-4 py-2 hover:bg-slate-50 transition-colors cursor-default">
                                    <CheckSquare class="text-emerald-500" :size="16" />
                                    <span class="text-sm font-semibold text-slate-700">{{ fas.name }}</span>
                                </label>
                                <div class="flex items-center gap-2 border border-dashed border-slate-300 rounded-lg px-4 py-2 text-slate-400 opacity-60">
                                    <Plus :size="16" />
                                    <span class="text-sm font-medium">Tambah lainnya...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>

    <!-- Modal Kategori Master (CRUD Kategori Fasilitas) -->
    <Teleport to="body">
        <div v-if="showMasterModal" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/40 p-4" @click.self="showMasterModal = false">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
                    <h3 class="font-bold text-slate-800 text-sm">{{ isEditMaster ? 'Edit' : 'Tambah' }} Master Kategori Fasilitas</h3>
                    <button @click="showMasterModal = false" class="text-slate-400 hover:text-slate-600"><X :size="18" /></button>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    <!-- Form Atas -->
                    <div class="p-5 space-y-4 border-b border-slate-100 bg-white">
                        <div>
                            <label class="text-xs font-bold text-slate-700 mb-1.5 block">Nama Kategori Fasilitas</label>
                            <input v-model="masterForm.name" type="text" class="w-full text-sm px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000]" placeholder="Misal: Fasilitas Kamar, Area Bersama" />
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer mt-2">
                            <input type="checkbox" v-model="masterForm.is_active" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]/30" />
                            <span class="text-xs font-semibold text-slate-600">Status Aktif</span>
                        </label>
                        <div class="flex justify-end gap-2 pt-2">
                            <button v-if="isEditMaster" @click="deleteMaster(masterForm.id)" class="px-3 py-1.5 text-xs font-semibold text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-lg mr-auto">Hapus</button>
                            <button @click="submitMaster" :disabled="!masterForm.name" class="px-4 py-1.5 text-xs font-bold text-[#0A2540] bg-[#FFC000] hover:bg-amber-400 rounded-lg disabled:opacity-50">{{ isEditMaster ? 'Update Master' : 'Simpan Baru' }}</button>
                        </div>
                    </div>

                    <!-- List Bawah -->
                    <div class="p-5 bg-slate-50/50">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Daftar Kategori Fasilitas yang Ada</p>
                        <div class="space-y-1">
                            <div v-for="cat in kategoriFasilitas" :key="cat.id" class="flex items-center justify-between px-3 py-2 bg-white rounded-lg border border-slate-200/60 shadow-sm">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-1.5 h-1.5 rounded-full shrink-0" :class="cat.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                                    <span class="text-xs font-semibold text-slate-800 truncate">{{ cat.name }}</span>
                                </div>
                                <button @click="openEditMaster(cat)" class="text-slate-400 hover:text-slate-700 p-1 transition"><PenLine :size="14" /></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Modal Item Fasilitas (CRUD Fasilitas dalam Kategori) -->
    <Teleport to="body">
        <div v-if="showFasilitasModal" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/40 p-4" @click.self="showFasilitasModal = false">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
                    <h3 class="font-bold text-slate-800 text-sm">{{ isEditFasilitas ? 'Edit' : 'Tambah' }} Fasilitas</h3>
                    <button @click="showFasilitasModal = false" class="text-slate-400 hover:text-slate-600"><X :size="18" /></button>
                </div>
                
                <div class="p-5 space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-700 mb-1.5 block">Nama Fasilitas</label>
                        <input v-model="fasilitasForm.name" type="text" class="w-full text-sm px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000]" placeholder="Misal: Kulkas, Kompor" />
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer mt-2">
                        <input type="checkbox" v-model="fasilitasForm.is_active" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]/30" />
                        <span class="text-xs font-semibold text-slate-600">Status Aktif</span>
                    </label>
                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 mt-4">
                        <button v-if="isEditFasilitas" @click="deleteFasilitas(fasilitasForm.id)" class="px-3 py-1.5 text-xs font-semibold text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-lg mr-auto">Hapus</button>
                        <button @click="submitFasilitas" :disabled="!fasilitasForm.name" class="px-4 py-1.5 text-xs font-bold text-[#0A2540] bg-[#FFC000] hover:bg-amber-400 rounded-lg disabled:opacity-50">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>

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
                    <button @click="confirmAdd" :disabled="!pendingAddId" class="w-full bg-[#0A2540] text-white text-xs font-bold py-2 rounded-lg hover:bg-slate-900 transition disabled:opacity-50">
                        Tambahkan
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Popover Edit Kategori (Manage Fasilitas) -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-full lg:translate-y-0 lg:opacity-0 lg:scale-95"
        enter-to-class="translate-y-0 lg:opacity-100 lg:scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 lg:opacity-100 lg:scale-100"
        leave-to-class="translate-y-full lg:translate-y-0 lg:opacity-0 lg:scale-95"
    >
        <div v-if="activeEditCategoryId !== null" 
             class="fixed lg:absolute inset-x-0 bottom-0 lg:bottom-auto lg:inset-auto z-50 w-full lg:w-[320px] bg-white lg:rounded-xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)] lg:shadow-xl lg:border border-slate-200 popover-desktop flex flex-col"
             :style="{ '--popover-top': popoverTop + 'px', '--popover-left': popoverLeft + 'px' }"
             @click.stop>
             
             <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 rounded-t-xl">
                 <h4 class="font-bold text-slate-800 text-sm">Pengaturan Kategori</h4>
                 <button @click="activeEditCategoryId = null" class="text-slate-400 hover:text-slate-600 transition-colors p-1"><X :size="16"/></button>
             </div>
             
             <div class="p-4 bg-white space-y-4 max-h-[40vh] lg:max-h-[250px] overflow-y-auto custom-scrollbar">
                <div class="space-y-2 pb-3 border-b border-slate-100">
                    <label class="flex items-center gap-2.5 cursor-pointer" v-if="activeDraftCategory">
                        <input type="checkbox" v-model="activeDraftCategory.is_mandatory" class="rounded border-slate-300 w-3.5 h-3.5 text-slate-600 focus:ring-0">
                        <span class="text-xs font-semibold text-slate-600">Kategori Wajib (Required)</span>
                    </label>
                </div>

                <div class="flex flex-col gap-2 pt-1">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Daftar Fasilitas</p>
                    <div v-for="fas in activePreviewCategory?.facilities || []" :key="fas.id" class="flex items-center gap-2 group border-b border-slate-100 pb-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></div>
                        <span class="flex-1 text-[13px] font-semibold text-slate-700 truncate">{{ fas.name }}</span>
                        <button @click="openEditFasilitas(activeEditCategoryId, fas)" class="text-slate-300 hover:text-[#0A2540] transition p-1 opacity-0 group-hover:opacity-100" title="Edit Fasilitas">
                            <PenLine :size="12" />
                        </button>
                    </div>
                    <div v-if="!activePreviewCategory?.facilities?.length" class="text-xs text-slate-400 italic">
                        Belum ada fasilitas.
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <button @click="openAddFasilitas(activeEditCategoryId)" class="w-full text-center px-4 py-2 text-xs font-bold text-[#FFC000] bg-amber-50 hover:bg-[#FFF8E6] rounded-lg border border-[#FFC000]/30 transition-colors inline-flex items-center justify-center gap-2">
                        <Plus :size="14" /> Tambah Fasilitas
                    </button>
                </div>
             </div>
             
             <div class="px-3 py-2 flex flex-col gap-1.5 bg-white rounded-b-xl border-t border-slate-50">
                 <button @click="removeCategory(activeEditCategoryId); activeEditCategoryId = null" class="w-full flex justify-center items-center gap-1.5 py-1.5 rounded-lg border border-rose-200 bg-white text-rose-500 font-semibold text-[11px] hover:bg-rose-50 transition">
                     <Trash2 :size="12" /> Hapus Kategori Ini
                 </button>
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
