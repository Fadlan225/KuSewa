<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, X, Plus, Trash2, ChevronDown, CheckSquare, ChevronLeft, ChevronRight, Building, PenLine } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

/* ── Props ────────────────────────────────────────────────────────── */
const props = defineProps({
    kategoriAset: { type: Array, default: () => [] },
    jenisAset:    { type: Array, default: () => [] },
});

const page = usePage();

/* ── State ────────────────────────────────────────────────────────── */
const selectedCategoryId = ref('');
const activeEditIndex = ref(null);
const popoverTop = ref(0);
const popoverLeft = ref(0);

const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

/* ── Master Kategori Modal ────────────────────────────────────────── */
const showKategoriModal = ref(false);
const kategoriForm = ref({ id: null, name: '', description: '', is_active: true });
const isEditKategori = ref(false);

/* ── Jenis Aset Form Popover ──────────────────────────────────────── */
// Instead of local draft, we can directly edit items, but let's keep a form state for the popover
const jenisForm = ref({ id: null, name: '', description: '', is_active: true });

const selectedCategory = computed(() => props.kategoriAset.find(k => k.id === selectedCategoryId.value) || null);
const currentJenisList = computed(() => {
    if (!selectedCategoryId.value) return [];
    return props.jenisAset.filter(j => j.category_id === selectedCategoryId.value);
});

// Mock preview data
const mockPreviewFilter = ref('semua');

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

    if (props.kategoriAset && props.kategoriAset.length > 0) {
        const lastSelected = localStorage.getItem('last_selected_kategori_aset');
        let catToSelect = props.kategoriAset.find(t => t.id == lastSelected);
        if (!catToSelect) catToSelect = props.kategoriAset[0];
        if (catToSelect) selectCategory(catToSelect.id);
    }
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    document.removeEventListener('click', closePopover);
});

function selectCategory(id) {
    if (id) {
        selectedCategoryId.value = id;
        localStorage.setItem('last_selected_kategori_aset', id);
    }
    isDropdownOpen.value = false;
}

const openSettings = (jenis, event) => {
    activeEditIndex.value = jenis.id;
    jenisForm.value = { ...jenis };
    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        popoverTop.value = Math.min(rect.top - 10, window.innerHeight - 300);
        popoverLeft.value = rect.right + 12;
    }
};

/* ── KATEGORI ASET (Master) ───────────────────────────────────────── */
function openAddKategori() {
    isEditKategori.value = false;
    kategoriForm.value = { id: null, name: '', description: '', is_active: true };
    showKategoriModal.value = true;
    isDropdownOpen.value = false;
}
function openEditKategori(kategori) {
    isEditKategori.value = true;
    kategoriForm.value = { ...kategori };
    showKategoriModal.value = true;
    isDropdownOpen.value = false;
}
function submitKategori() {
    const routeName = isEditKategori.value ? 'admin.kategori-aset.update' : 'admin.kategori-aset.store';
    const routeArgs = isEditKategori.value ? route(routeName, kategoriForm.value.id) : route(routeName);
    const method = isEditKategori.value ? 'put' : 'post';

    router[method](routeArgs, kategoriForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showKategoriModal.value = false;
            // auto select if new
            if (!isEditKategori.value) {
                setTimeout(() => {
                    const latest = props.kategoriAset[props.kategoriAset.length - 1];
                    if (latest) selectCategory(latest.id);
                }, 100);
            }
        }
    });
}
function deleteKategori(id) {
    if(confirm("Yakin ingin menghapus kategori aset ini?")) {
        router.delete(route('admin.kategori-aset.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                showKategoriModal.value = false;
                if (selectedCategoryId.value === id) selectedCategoryId.value = '';
            }
        });
    }
}

/* ── JENIS ASET (Tipe) ───────────────────────────────────────────── */
function addJenis() {
    if (!selectedCategoryId.value) return;
    activeEditIndex.value = 'new';
    jenisForm.value = { id: null, name: 'Tipe Baru', description: '', is_active: true, category_id: selectedCategoryId.value };
    
    // Position popover manually in center since it's a new item
    popoverTop.value = window.innerHeight / 2 - 150;
    popoverLeft.value = window.innerWidth / 2 - 160;
}

function saveJenis() {
    if (!selectedCategoryId.value) return;
    const isNew = activeEditIndex.value === 'new';
    const routeName = isNew ? 'admin.jenis-aset.store' : 'admin.jenis-aset.update';
    const routeArgs = isNew ? route(routeName) : route(routeName, jenisForm.value.id);
    const method = isNew ? 'post' : 'put';

    const payload = { ...jenisForm.value, category_id: selectedCategoryId.value };

    router[method](routeArgs, payload, {
        preserveScroll: true,
        onSuccess: () => { activeEditIndex.value = null; }
    });
}

function deleteJenis(id) {
    if(confirm("Yakin ingin menghapus tipe aset ini?")) {
        router.delete(route('admin.jenis-aset.destroy', id), {
            preserveScroll: true,
            onSuccess: () => { activeEditIndex.value = null; }
        });
    }
}
</script>

<template>
    <Head title="Kategori & Tipe Aset - Admin Panel" />

    <DashboardLayout role="Admin" title="Kategori & Tipe Aset" description="Kelola master data Kategori Aset dan jenis tipe di dalamnya." no-padding>
        <template #leftAction>
            <div class="relative text-left w-full sm:w-[320px]" ref="dropdownRef">
                <button
                    @click="toggleDropdown"
                    class="flex items-center justify-between w-full gap-2.5 bg-white hover:bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 hover:border-[#FFC000] hover:shadow-sm transition-all focus:outline-none"
                >
                    <template v-if="!selectedCategory">
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                                <Building class="w-4 h-4" />
                            </div>
                            <div class="flex flex-col items-start flex-1 min-w-0">
                                <span class="text-xs text-slate-500 font-semibold leading-tight truncate w-full text-left">Pilih Konfigurasi</span>
                                <span class="text-sm font-black text-[#0A2540] leading-tight truncate w-full text-left">Kategori Aset...</span>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded bg-slate-50 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                                <Building class="w-4 h-4 text-[#FFC000]" />
                            </div>
                            <div class="flex flex-col items-start flex-1 min-w-0 text-left">
                                <span class="text-xs text-slate-500 font-semibold leading-tight truncate w-full text-left">Kategori Aset Terpilih</span>
                                <span class="text-sm font-black text-[#0A2540] leading-tight truncate w-full text-left">{{ selectedCategory.name }}</span>
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

                        <!-- Header Dropdown -->
                        <div class="p-2 border-b border-slate-50 flex items-center justify-between">
                            <div class="flex flex-col items-start flex-1 p-2">
                                <span class="text-sm font-bold text-slate-500">Kategori Aset Master</span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ kategoriAset.length }} tersedia</span>
                            </div>
                            <button @click="openAddKategori" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded-md transition flex items-center gap-1 shrink-0">
                                <Plus :size="12" /> Kategori Baru
                            </button>
                        </div>

                        <div class="max-h-[300px] overflow-y-auto p-2 space-y-1">
                            <div
                                v-for="cat in kategoriAset"
                                :key="cat.id"
                                class="flex items-center gap-1 w-full rounded-lg group transition-colors border border-transparent"
                                :class="selectedCategoryId === cat.id ? 'bg-[#FFF8E6] !border-[#FFC000]/30' : 'hover:bg-slate-50'"
                            >
                                <button @click="selectCategory(cat.id)" class="flex items-center gap-3 flex-1 p-2 min-w-0 text-left">
                                    <div class="w-8 h-8 rounded bg-slate-50 shrink-0 overflow-hidden border border-slate-200 flex items-center justify-center">
                                        <Building class="w-4 h-4" :class="selectedCategoryId === cat.id ? 'text-[#FFC000]' : 'text-slate-400'" />
                                    </div>
                                    <div class="flex flex-col items-start flex-1 min-w-0">
                                        <span class="text-sm font-bold text-[#0A2540] truncate w-full">{{ cat.name }}</span>
                                        <span class="text-[10px] font-medium" :class="cat.is_active ? 'text-emerald-500' : 'text-rose-500'">
                                            {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </button>
                                <button @click="openEditKategori(cat)" class="p-2 text-slate-300 hover:text-slate-600 transition opacity-0 group-hover:opacity-100" title="Edit Kategori">
                                    <PenLine :size="14" />
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </template>

        <div class="flex justify-center w-full min-h-[60vh]">
            <!-- Empty state -->
            <div v-if="!selectedCategory" class="w-full max-w-4xl flex flex-col items-center justify-center text-slate-400 gap-3 py-32">
                <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center">
                    <Search :size="24" class="text-slate-300" />
                </div>
                <p class="text-sm font-semibold">Pilih atau Tambah Kategori Aset di atas</p>
                <p class="text-xs">untuk mengatur Tipe Aset (Jenis) di dalamnya</p>
            </div>

            <div v-else class="flex flex-col lg:flex-row w-full items-start">

                <!-- ============================================== -->
                <!-- LEFT SIDEBAR (STICKY & FULL HEIGHT)            -->
                <!-- ============================================== -->
                <div class="w-full lg:w-[340px] shrink-0 bg-white border-r border-slate-200 flex flex-col h-[calc(100vh-121px)] lg:h-[calc(100vh-61px)] sticky top-[121px] lg:top-[61px] z-20 shadow-[4px_0_24px_-15px_rgba(0,0,0,0.1)]">

                    <!-- Flash Messages -->
                    <div v-if="page.props.flash?.success" class="bg-emerald-50 px-6 py-3 text-sm font-semibold text-emerald-700 border-b border-emerald-100 flex items-center gap-2">
                        {{ page.props.flash.success }}
                    </div>
                    <div v-if="page.props.flash?.error" class="bg-rose-50 px-6 py-3 text-sm font-semibold text-rose-700 border-b border-rose-100 flex items-center gap-2">
                        {{ page.props.flash.error }}
                    </div>

                    <!-- Scrollable Content Area -->
                    <div class="flex-1 overflow-y-auto bg-slate-50/30 flex flex-col relative [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none;">

                        <div class="flex flex-col h-full">

                            <!-- List Header -->
                            <div class="px-4 pt-4 pb-3 flex flex-col gap-3 sticky top-0 bg-slate-50/95 backdrop-blur z-20 border-b border-slate-200 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-slate-800 text-[13px] whitespace-nowrap">Daftar Tipe Aset</h3>
                                    <button @click.stop="addJenis" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 transition flex items-center gap-1 shrink-0">
                                        <Plus :size="14" /> Tambah
                                    </button>
                                </div>
                            </div>

                            <div class="flex-1 p-4 lg:p-5 space-y-1">
                                <div v-for="jenis in currentJenisList" :key="jenis.id" class="rounded-lg transition-colors border" :class="activeEditIndex === jenis.id ? 'bg-slate-100 border-slate-200' : 'border-slate-200/60 hover:bg-slate-50 bg-white'">
                                    <div class="flex items-center px-3 py-2.5 gap-2.5 cursor-pointer group select-none" @click.stop="openSettings(jenis, $event)" @contextmenu.prevent.stop="openSettings(jenis, $event)">
                                        <div class="w-1.5 h-1.5 rounded-full shrink-0" :class="jenis.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold truncate" :class="activeEditIndex === jenis.id ? 'text-slate-800' : 'text-slate-600'">
                                                {{ jenis.name }}
                                            </p>
                                        </div>
                                        <div class="flex items-center pr-1 transition-colors" :class="activeEditIndex === jenis.id ? 'text-slate-600' : 'text-slate-400 group-hover:text-slate-500'">
                                            <ChevronLeft v-if="activeEditIndex === jenis.id" :size="16" />
                                            <ChevronRight v-else :size="16" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="currentJenisList.length === 0" class="text-center py-16 flex flex-col items-center justify-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-300">
                                        <CheckSquare :size="24" />
                                    </div>
                                    <span class="text-[15px] font-bold text-slate-700">Belum Ada Tipe Aset</span>
                                    <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                        Tambahkan variasi tipe untuk kategori <b>{{ selectedCategory.name }}</b> (misal: Standar, VIP).
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT CANVAS (LIVE PREVIEW)                    -->
                <!-- ============================================== -->
                <div class="flex-1 w-full bg-slate-50/70 p-6 lg:p-12 relative flex items-center justify-center">

                    <div class="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden mb-20 pointer-events-none">
                        <!-- Mock Header -->
                        <div class="bg-[#0A2540] p-6 text-white">
                            <h2 class="text-xl font-bold">Cari {{ selectedCategory?.name || 'Aset' }}</h2>
                            <p class="text-xs text-white/70 mt-1">Preview filter pencarian di aplikasi</p>
                        </div>
                        
                        <div class="p-6">
                            <label class="text-sm font-bold text-slate-800 mb-3 block">Pilih Tipe Aset</label>
                            <div class="flex flex-wrap gap-2">
                                <div class="px-4 py-2 rounded-full border text-xs font-semibold transition-colors" :class="mockPreviewFilter === 'semua' ? 'bg-[#FFC000] border-[#FFC000] text-[#0A2540]' : 'border-slate-200 text-slate-600'">
                                    Semua Tipe
                                </div>
                                <template v-if="currentJenisList.length > 0">
                                    <div v-for="jenis in currentJenisList" :key="'mock-'+jenis.id" class="px-4 py-2 rounded-full border text-xs font-semibold transition-colors" :class="[
                                            mockPreviewFilter === jenis.name ? 'bg-[#FFC000] border-[#FFC000] text-[#0A2540]' : 'border-slate-200 text-slate-600',
                                            !jenis.is_active ? 'opacity-40 line-through' : ''
                                        ]">
                                        {{ jenis.name }}
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="px-4 py-2 rounded-full border border-dashed border-slate-300 text-slate-400 text-xs font-semibold">
                                        Tipe Aset 1
                                    </div>
                                    <div class="px-4 py-2 rounded-full border border-dashed border-slate-300 text-slate-400 text-xs font-semibold">
                                        Tipe Aset 2
                                    </div>
                                </template>
                            </div>

                            <button class="w-full mt-6 bg-[#0A2540] text-white font-bold text-sm py-3 rounded-xl">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </DashboardLayout>

    <!-- Modal Kategori Aset Master -->
    <Teleport to="body">
        <div v-if="showKategoriModal" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/40 p-4" @click.self="showKategoriModal = false">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-md overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-sm">{{ isEditKategori ? 'Edit Kategori Aset' : 'Tambah Kategori Aset' }}</h3>
                    <button @click="showKategoriModal = false" class="text-slate-400 hover:text-slate-600"><X :size="18" /></button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-700 mb-1.5 block">Nama Kategori</label>
                        <input v-model="kategoriForm.name" type="text" class="w-full text-sm px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000]" placeholder="Misal: Kost, Apartemen" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 mb-1.5 block">Deskripsi Singkat</label>
                        <textarea v-model="kategoriForm.description" rows="2" class="w-full text-sm px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000] resize-none" placeholder="Opsional..."></textarea>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer mt-2">
                        <input type="checkbox" v-model="kategoriForm.is_active" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]/30" />
                        <span class="text-xs font-semibold text-slate-600">Kategori Aktif</span>
                    </label>
                </div>
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex justify-between items-center">
                    <button v-if="isEditKategori" @click="deleteKategori(kategoriForm.id)" class="text-rose-500 hover:bg-rose-50 p-2 rounded-lg transition"><Trash2 :size="16" /></button>
                    <div v-else></div>
                    <div class="flex gap-2">
                        <button @click="showKategoriModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-200 rounded-lg">Batal</button>
                        <button @click="submitKategori" :disabled="!kategoriForm.name" class="px-4 py-2 text-xs font-bold text-[#0A2540] bg-[#FFC000] hover:bg-amber-400 rounded-lg disabled:opacity-50">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Field Settings Popover (Tipe Aset) -->
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
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm lg:hidden pointer-events-auto" @click="activeEditIndex = null"></div>

            <div class="relative bg-white rounded-xl shadow-[0_10px_30px_-5px_rgba(0,0,0,0.15)] border border-slate-200 w-full max-w-[300px] flex flex-col pointer-events-auto overflow-hidden" @click.stop>
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 rounded-t-xl">
                    <h3 class="font-bold text-slate-700 text-sm">{{ activeEditIndex === 'new' ? 'Tambah Tipe Aset' : 'Edit Tipe Aset' }}</h3>
                    <button @click="activeEditIndex = null" class="text-slate-400 hover:text-rose-500 transition">
                        <X :size="16" />
                    </button>
                </div>

                <div class="p-4 space-y-4">
                    <div class="relative mt-1">
                        <label class="absolute -top-2 left-2 px-1 text-[10px] font-bold text-slate-400 bg-white">Nama Tipe</label>
                        <input v-model="jenisForm.name" type="text" class="w-full text-xs font-semibold text-slate-800 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#FFC000] focus:border-[#FFC000]" />
                    </div>

                    <div class="relative mt-2">
                        <label class="absolute -top-2 left-2 px-1 text-[10px] font-bold text-slate-400 bg-white">Deskripsi</label>
                        <textarea v-model="jenisForm.description" rows="2" class="w-full text-xs font-medium text-slate-800 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#FFC000] focus:border-[#FFC000] resize-none"></textarea>
                    </div>

                    <div class="space-y-2 pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" v-model="jenisForm.is_active" class="rounded border-slate-300 w-3.5 h-3.5 text-[#0A2540] focus:ring-[#FFC000]/30">
                            <span class="text-xs font-semibold text-slate-600">Status Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="px-3 py-2 flex flex-col gap-1.5 bg-slate-50 border-t border-slate-100">
                    <button @click="saveJenis" :disabled="!jenisForm.name" class="w-full bg-[#0A2540] text-white text-xs font-bold py-2 rounded-lg hover:bg-slate-900 transition disabled:opacity-50">
                        Simpan Tipe
                    </button>
                    <button v-if="activeEditIndex !== 'new'" @click="deleteJenis(jenisForm.id)" class="w-full text-rose-500 bg-white border border-rose-200 text-xs font-bold py-1.5 rounded-lg hover:bg-rose-50 transition">
                        Hapus Tipe
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
