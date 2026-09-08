<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, X, Plus, Trash2, ChevronDown, GripVertical, Lock, Copy, ChevronLeft, ChevronRight, Building } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import EmptyFieldIcon from '@/Components/ui/Icons/EmptyFieldIcon.vue';
import draggable from 'vuedraggable';

/* ── Props ────────────────────────────────────────────────────────── */
const props = defineProps({
    assetTypes: { type: Array, default: () => [] },
});

const page = usePage();

/* ── State ────────────────────────────────────────────────────────── */
const selectedType  = ref(null);    // object tipe aset yang dipilih
const selectedTypeId = ref('');
const fieldScope    = ref('asset');  // 'asset' | 'unit'

// Draft editable fields — copy dari selectedType agar tidak mutate props
const draftFields   = ref([]);
const activeEditIndex = ref(null);
const previewValues = ref({});
const popoverTop = ref(0);
const popoverLeft = ref(0);

const openSettings = (idx, event) => {
    activeEditIndex.value = idx;
    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        // Limit bottom position so the modal doesn't go off-screen
        popoverTop.value = Math.min(rect.top - 10, window.innerHeight - 450);
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
        const lastSelected = localStorage.getItem('last_selected_asset_type_spesifikasi');
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
        localStorage.setItem('last_selected_asset_type_spesifikasi', id);
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
    const t = selectedType.value;
    draftFields.value  = JSON.parse(JSON.stringify(
        fieldScope.value === 'asset' ? (t.detail_fields || []) : (t.unit_detail_fields || [])
    ));
    activeEditIndex.value = null;
}

// Reload draft saat scope berubah
function onFieldScopeChange() { loadDraft(); }

/* ── Field types ──────────────────────────────────────────────────── */
const FIELD_TYPES = [
    { value: 'text', label: 'Teks Pendek' },
    { value: 'number', label: 'Angka Bebas' },
    { value: 'counter', label: 'Angka dengan Tombol - / +' },
    { value: 'select', label: 'Dropdown Pilihan' },
    { value: 'radio', label: 'Tombol Pilihan Tunggal (Pill)' },
    { value: 'checkbox', label: 'Tombol Centang (Ya/Tidak)' },
    { value: 'time', label: 'Input Waktu / Jam' },
    { value: 'room_size', label: 'Ukuran Ruangan (P x L)' },
];
const hasOptions  = (type) => ['select', 'radio'].includes(type);

const generateKey = (text) => {
    if (!text) return '';
    return text.toString().toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^\w\_]+/g, '')
        .replace(/__+/g, '_')
        .replace(/^_|_$/g, '');
};

const updateKey = (field) => {
    field.key = generateKey(field.label);
};

function addField() {
    const defaultLabel = 'Pertanyaan Baru';
    draftFields.value.push({
        key: generateKey(defaultLabel),
        label: defaultLabel,
        type: 'text',
        required: false,
        options: ['Opsi 1', 'Opsi 2']
    });
}

// Remove a draft field
const removeField = (index) => {
    draftFields.value.splice(index, 1);
    if (activeEditIndex.value === index) activeEditIndex.value = null;
};


// Duplicate a draft field
const duplicateField = (index) => {
    const fieldToCopy = draftFields.value[index];
    if (!fieldToCopy) return;

    // Create deep copy
    const copiedField = JSON.parse(JSON.stringify(fieldToCopy));

    // Modify label and regenerate unique key
    copiedField.label = `${copiedField.label} (Copy)`;
    copiedField.key = copiedField.label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');

    // Ensure key uniqueness just in case
    let counter = 1;
    let baseKey = copiedField.key;
    while (draftFields.value.some(f => f.key === copiedField.key)) {
        copiedField.key = `${baseKey}_${counter}`;
        counter++;
    }

    // Insert after the current item
    draftFields.value.splice(index + 1, 0, copiedField);

    // Optionally focus the newly created field
    activeEditIndex.value = index + 1;
};

function addOption(field) {
    if (!field.options) field.options = [];
    field.options.push('');
}

function removeOption(field, idx) {
    field.options.splice(idx, 1);
}

/* ── Save ─────────────────────────────────────────────────────────── */
function saveFields() {
    if (!selectedType.value) return;
    router.put(route('admin.konfigurasi-aset.fields', selectedType.value.id), {
        scope:  fieldScope.value,
        fields: draftFields.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local props reference
            const t = props.assetTypes.find(a => a.id === selectedType.value.id);
            if (t) {
                if (fieldScope.value === 'asset') t.detail_fields = JSON.parse(JSON.stringify(draftFields.value));
                else t.unit_detail_fields = JSON.parse(JSON.stringify(draftFields.value));
                selectedType.value = t;
            }
        },
    });
}
</script>

<template>
    <Head title="Spesifikasi Aset - Admin Panel" />

    <DashboardLayout role="Admin" title="Spesifikasi Aset" description="Kelola konfigurasi field informasi spesifik per tipe aset." no-padding>
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
                        <div class="p-2 border-b border-slate-50">
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
                <p class="text-xs">untuk mengatur konfigurasi spesifikasi aset</p>
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

                        <!-- ── TAB: Field Informasi ────────────────────────── -->
                        <div class="flex flex-col h-full">

                            <!-- Scope toggle & List Header -->
                            <div class="px-4 pt-3 pb-3 flex flex-col gap-3 sticky top-0 bg-slate-50/95 backdrop-blur z-20 border-b border-slate-200 shadow-sm">
                                <!-- Top row: Tabs -->
                                <div class="flex items-center w-full bg-white p-1 rounded-lg border border-slate-200/80 text-[11px] shadow-sm">
                                    <button
                                        v-for="s in ['asset', 'unit']"
                                        :key="s"
                                        @click="fieldScope = s; onFieldScopeChange()"
                                        :disabled="s === 'unit' && !selectedType.allow_units"
                                        class="flex-1 py-1.5 rounded-md font-bold transition capitalize text-center"
                                        :class="[
                                            fieldScope === s ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50',
                                            s === 'unit' && !selectedType.allow_units ? 'opacity-40 cursor-not-allowed' : ''
                                        ]"
                                    >
                                        {{ s === 'asset' ? 'Aset' : 'Unit' }}
                                    </button>
                                </div>

                                <!-- Bottom row: Title + Add Button -->
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-slate-800 text-[13px] whitespace-nowrap">Field Spesifikasi</h3>
                                    <button @click="addField" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 transition flex items-center gap-1 shrink-0">
                                        <Plus :size="14" /> Tambah
                                    </button>
                                </div>
                            </div>

                            <!-- Draggable Builder List -->
                            <div class="flex-1 p-4 lg:p-5">
                                <draggable v-model="draftFields" item-key="key" handle=".drag-handle" :animation="200" class="space-y-1 pb-2">
                                    <template #item="{ element: field, index: idx }">
                                        <div class="rounded-lg transition-colors border" :class="activeEditIndex === idx ? 'bg-slate-100 border-slate-200' : 'border-slate-200/60 hover:bg-slate-50'">
                                            <div class="flex items-center px-3 py-2.5 gap-2.5 cursor-pointer group select-none" @click.stop="openSettings(idx, $event)" @contextmenu.prevent.stop="openSettings(idx, $event)">
                                                <div class="drag-handle text-slate-300 group-hover:text-slate-400 cursor-grab active:cursor-grabbing p-0.5 -ml-1 transition-colors" title="Seret untuk memindahkan urutan" @click.stop>
                                                    <GripVertical :size="14" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[13px] font-semibold truncate" :class="activeEditIndex === idx ? 'text-slate-800' : 'text-slate-600'">
                                                        {{ field.label || 'Label Kosong' }}
                                                        <span v-if="field.required" class="text-rose-500 ml-0.5 text-xs">*</span>
                                                    </p>
                                                </div>
                                                <div class="flex items-center pr-1 transition-colors" :class="activeEditIndex === idx ? 'text-slate-600' : 'text-slate-400 group-hover:text-slate-500'">
                                                    <ChevronLeft v-if="activeEditIndex === idx" :size="16" />
                                                    <ChevronRight v-else :size="16" />
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </draggable>
                            </div>
                            
                            <div v-if="draftFields.length === 0" class="text-center py-16 flex flex-col items-center justify-center gap-3">
                                <EmptyFieldIcon :size="120" />
                                <span class="text-[15px] font-bold text-slate-700">Belum Ada Field yang Dikonfigurasi</span>
                                <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                    Tambahkan spesifikasi tambahan untuk tipe aset ini seperti dimensi, fasilitas, aturan, atau detail lainnya.
                                </span>
                                <button @click.stop="addField" class="mt-4 bg-white border border-slate-200 hover:border-[#FFC000] hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                                    <Plus :size="16" class="text-[#FFC000]" /> Tambah Field
                                </button>
                            </div>

                            <!-- Footer Save Button -->
                            <div class="p-5 bg-white border-t border-slate-200 mt-auto sticky bottom-0 z-20 shadow-[0_-15px_30px_-10px_rgba(0,0,0,0.08)]">
                                <button @click="saveFields" class="w-full rounded-lg bg-[#FFC000] px-5 py-3 text-sm font-bold text-[#0A2540] hover:bg-amber-400 transition shadow-sm flex items-center justify-center gap-2">
                                    Simpan Konfigurasi Field
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT CANVAS (LIVE PREVIEW)                    -->
                <!-- ============================================== -->
                <div class="flex-1 w-full bg-slate-50/70 p-6 lg:p-12 relative">


                    <div class="mt-8 max-w-4xl mx-auto pb-20">
                        <!-- MOCK WRAPPER TOP -->
                        <div v-if="fieldScope === 'unit'" class="border border-slate-300 rounded-t-lg bg-white shadow-sm overflow-hidden mb-[-1px] relative z-0">
                            <!-- Header Kamar -->
                            <div class="bg-slate-50/50 p-5 flex items-center justify-between border-b border-slate-200/60">
                                <div class="flex items-center gap-4">
                                    <div class="text-slate-400 font-bold text-lg">01</div>
                                    <div>
                                        <p class="text-lg font-bold text-[#0A2540]">Tipe {{ selectedType?.unit_label || 'Unit' }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ previewValues['total_kamar'] || 1 }} {{ selectedType?.unit_label?.toLowerCase() || 'unit' }} &middot; {{ Math.max(0, (previewValues['total_kamar'] || 1) - (previewValues['kamar_kosong'] ?? 1)) }} terisi</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 bg-slate-100 text-slate-500 px-3 py-1.5 rounded-full text-[10px] font-bold border border-slate-200 shadow-sm uppercase tracking-wider">
                                    <Lock :size="12" /> Bawaan Sistem
                                </div>
                            </div>
                            <div class="p-6">
                                <!-- NAMA TIPE KAMAR -->
                                <div class="mb-8 pb-8 border-b border-slate-200/70">
                                    <label class="block text-base font-bold text-slate-800 mb-1.5">Nama Tipe {{ selectedType?.unit_label || 'Unit' }} <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center w-full bg-slate-50 border border-slate-300 rounded-md overflow-hidden focus-within:ring-2 focus-within:ring-[#FFC000] focus-within:border-[#FFC000] transition-all">
                                        <span class="px-4 py-2.5 text-slate-500 font-medium border-r border-slate-300 bg-slate-50 shrink-0">Tipe</span>
                                        <input type="text" placeholder="Contoh : Standar, Eksklusif" class="w-full text-sm px-4 py-2.5 bg-white outline-none font-medium text-slate-800 placeholder:font-normal placeholder:text-slate-400" />
                                    </div>
                                </div>
                                <!-- TOTAL KAMAR -->
                                <div class="mb-4 flex sm:items-center justify-between p-4 border border-slate-200 rounded-lg gap-4">
                                    <label class="block text-base font-bold text-slate-800 mb-1">Total {{ selectedType?.unit_label || 'Unit' }} <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="previewValues['total_kamar'] = Math.max(1, (previewValues['total_kamar'] || 1) - 1); previewValues['kamar_kosong'] = Math.min(previewValues['kamar_kosong'] ?? 1, previewValues['total_kamar'])" class="w-9 h-9 border border-slate-300 rounded-md flex items-center justify-center text-slate-600 bg-white hover:bg-slate-50 transition-colors cursor-pointer font-bold">-</button>
                                        <div class="w-24 text-center text-base font-bold border border-slate-300 rounded-md py-1.5 text-slate-800 bg-white">{{ previewValues['total_kamar'] || 1 }}</div>
                                        <button type="button" @click="previewValues['total_kamar'] = (previewValues['total_kamar'] || 1) + 1" class="w-9 h-9 border border-slate-300 rounded-md flex items-center justify-center text-slate-600 bg-white hover:bg-slate-50 transition-colors cursor-pointer font-bold">+</button>
                                    </div>
                                </div>
                                <!-- KAMAR KOSONG & TERISI -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="p-4 border border-amber-400 bg-amber-50/50 rounded-lg flex flex-col justify-between">
                                        <label class="block text-base font-bold text-slate-800 mb-3">{{ selectedType?.unit_label || 'Unit' }} Kosong</label>
                                        <div class="flex items-center gap-2 mt-auto">
                                            <button type="button" @click="previewValues['kamar_kosong'] = Math.max(0, (previewValues['kamar_kosong'] ?? 1) - 1)" class="w-9 h-9 border border-slate-300 bg-white rounded-md flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer font-bold">-</button>
                                            <div class="flex-1 text-center text-base font-bold border border-slate-300 bg-white rounded-md py-1.5 text-slate-800">{{ previewValues['kamar_kosong'] ?? 1 }}</div>
                                            <button type="button" @click="previewValues['kamar_kosong'] = Math.min((previewValues['total_kamar'] || 1), (previewValues['kamar_kosong'] ?? 1) + 1)" class="w-9 h-9 border border-slate-300 bg-white rounded-md flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer font-bold">+</button>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg flex flex-col justify-between">
                                        <label class="block text-base font-bold text-slate-800 mb-3">Sudah Terisi :</label>
                                        <div class="mt-auto flex items-center justify-center">
                                            <span class="text-3xl font-bold text-[#0A2540]">{{ Math.max(0, (previewValues['total_kamar'] || 1) - (previewValues['kamar_kosong'] ?? 1)) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DRAFT FIELDS RENDERING -->
                        <div class="space-y-4" :class="fieldScope === 'unit' ? 'px-6 py-6 border-x border-slate-300 bg-white shadow-sm' : ''">
                            <div v-if="draftFields.length === 0" class="text-center py-16 flex flex-col items-center justify-center gap-3 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                                <EmptyFieldIcon :size="100" />
                                <span class="text-[15px] font-bold text-slate-700">Preview Form Kosong</span>
                                <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                    Belum ada field khusus yang ditambahkan. Silakan tambah field baru untuk melihat preview form pendaftaran aset.
                                </span>
                                <button @click.stop="addField" class="mt-2 bg-white border border-slate-200 hover:border-[#FFC000] hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                                    <Plus :size="16" class="text-[#FFC000]" /> Tambah Field
                                </button>
                            </div>

                            <div v-for="(field, idx) in draftFields" :key="idx" class="relative group transition-all select-none border border-transparent rounded-2xl bg-white pr-6 py-5 pl-6 ring-1 ring-slate-200/80 shadow-sm" :class="activeEditIndex === idx ? '!border-[#FFC000] shadow-md bg-amber-50/5' : ''">
                                <label class="block text-base font-bold text-slate-800 mb-2">
                                    {{ field.label || 'Label Kosong' }}
                                    <span v-if="field.required" class="text-rose-500">*</span>
                                    <span v-else class="text-sm font-normal text-slate-400">(opsional)</span>
                                </label>

                                <!-- Type: Radio (Pill Style) -->
                                <div v-if="field.type === 'radio'" class="flex items-center w-full bg-white border border-slate-200 rounded-lg overflow-hidden flex-wrap sm:flex-nowrap">
                                    <label v-for="(opt, oidx) in (field.options?.length ? field.options : ['Opsi 1', 'Opsi 2'])" :key="oidx" class="flex-1 text-center w-full sm:w-auto font-medium relative cursor-pointer" :class="[oidx < (field.options?.length || 2) - 1 ? 'sm:border-r border-b sm:border-b-0 border-slate-200' : '']">
                                        <input type="radio" :name="`preview_radio_${idx}`" :value="opt" v-model="previewValues[field.key]" class="peer sr-only" />
                                        <div class="py-3 text-slate-500 peer-checked:bg-[#FFF8E6] peer-checked:text-[#0A2540] peer-checked:font-bold transition-all hover:bg-slate-50">
                                            {{ opt || `Opsi ${oidx+1}` }}
                                        </div>
                                    </label>
                                </div>

                                <!-- Type: Select -->
                                <div v-else-if="field.type === 'select'" class="relative">
                                    <select v-model="previewValues[field.key]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium appearance-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all cursor-pointer">
                                        <option :value="undefined" disabled selected>Pilih...</option>
                                        <option v-for="(opt, oidx) in (field.options?.length ? field.options : ['Opsi 1', 'Opsi 2'])" :key="oidx" :value="opt">{{ opt }}</option>
                                    </select>
                                    <ChevronDown class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" />
                                </div>

                                <!-- Type: Counter -->
                                <div v-else-if="field.type === 'counter'" class="flex items-center gap-4">
                                    <button type="button" @click="previewValues[field.key] = Math.max(0, (previewValues[field.key] || 0) - 1)" class="w-9 h-9 flex items-center justify-center border border-slate-300 rounded-md text-slate-600 bg-white hover:bg-slate-50 transition-colors cursor-pointer"><span class="text-lg font-bold">-</span></button>
                                    <div class="w-12 text-center text-lg font-bold text-slate-800">{{ previewValues[field.key] || 0 }}</div>
                                    <button type="button" @click="previewValues[field.key] = (previewValues[field.key] || 0) + 1" class="w-9 h-9 flex items-center justify-center border border-slate-300 rounded-md text-slate-600 bg-white hover:bg-slate-50 transition-colors cursor-pointer"><span class="text-lg font-bold">+</span></button>
                                </div>

                                <!-- Type: Room Size -->
                                <div v-else-if="field.type === 'room_size'" class="mb-2">
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <button type="button" @click="previewValues[`${field.key}_p`] = 3; previewValues[`${field.key}_l`] = 3" class="px-4 py-2 text-sm font-medium border rounded-md transition-colors" :class="previewValues[`${field.key}_p`] == 3 && previewValues[`${field.key}_l`] == 3 ? 'bg-[#FFF8E6] border-[#FFC000] text-[#0A2540]' : 'border-slate-300 text-slate-600 bg-white hover:bg-slate-50'">3 x 3 meter</button>
                                        <button type="button" @click="previewValues[`${field.key}_p`] = 3; previewValues[`${field.key}_l`] = 4" class="px-4 py-2 text-sm font-medium border rounded-md transition-colors" :class="previewValues[`${field.key}_p`] == 3 && previewValues[`${field.key}_l`] == 4 ? 'bg-[#FFF8E6] border-[#FFC000] text-[#0A2540]' : 'border-slate-300 text-slate-600 bg-white hover:bg-slate-50'">3 x 4 meter</button>
                                        <button type="button" @click="previewValues[`${field.key}_p`] = 4; previewValues[`${field.key}_l`] = 4" class="px-4 py-2 text-sm font-medium border rounded-md transition-colors" :class="previewValues[`${field.key}_p`] == 4 && previewValues[`${field.key}_l`] == 4 ? 'bg-[#FFF8E6] border-[#FFC000] text-[#0A2540]' : 'border-slate-300 text-slate-600 bg-white hover:bg-slate-50'">4 x 4 meter</button>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="number" v-model="previewValues[`${field.key}_p`]" placeholder="P" class="flex-1 min-w-0 text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:font-normal placeholder:text-slate-400" />
                                        <span class="text-slate-400 font-bold shrink-0">x</span>
                                        <input type="number" v-model="previewValues[`${field.key}_l`]" placeholder="L" class="flex-1 min-w-0 text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:font-normal placeholder:text-slate-400" />
                                        <span class="text-sm font-medium text-slate-500 shrink-0">meter</span>
                                    </div>
                                </div>

                                <!-- Type: Checkbox (Toggle style) -->
                                <div v-else-if="field.type === 'checkbox'" class="flex items-center w-fit cursor-pointer" @click="previewValues[field.key] = !previewValues[field.key]">
                                    <div class="w-11 h-6 rounded-full relative transition-colors" :class="previewValues[field.key] ? 'bg-emerald-500' : 'bg-slate-200'">
                                        <div class="absolute top-[2px] left-[2px] bg-white border border-slate-100 rounded-full h-5 w-5 transition-transform" :class="previewValues[field.key] ? 'translate-x-5' : 'translate-x-0'"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium" :class="previewValues[field.key] ? 'text-[#0A2540] font-bold' : 'text-slate-700'">Ya</span>
                                </div>

                                <!-- Type: Time -->
                                <div v-else-if="field.type === 'time'">
                                    <input type="time" v-model="previewValues[field.key]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all" />
                                </div>

                                <!-- Default: Text, Number -->
                                <div v-else>
                                    <input :type="field.type === 'number' ? 'number' : 'text'" v-model="previewValues[field.key]" :placeholder="field.label || 'Ketik jawaban di sini...'" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:font-normal placeholder:text-slate-400" />
                                </div>
                            </div>
                        </div>

                        <!-- MOCK WRAPPER BOTTOM -->
                        <div v-if="fieldScope === 'unit'" class="border border-slate-300 rounded-b-lg bg-white shadow-sm overflow-hidden mt-[-1px] relative z-0">
                            <div class="p-6">
                                <!-- Deskripsi Kamar -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-base font-bold text-slate-800">Deskripsi Kamar <span class="text-sm font-normal text-slate-400">(opsional)</span></label>
                                        <div class="flex items-center gap-1.5 bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-[10px] font-bold border border-slate-200 shadow-sm uppercase tracking-wider">
                                            <Lock :size="12" /> Bawaan Sistem
                                        </div>
                                    </div>
                                    <textarea placeholder="Deskripsikan keunggulan spesifik kamar ini..." class="w-full h-24 rounded-md border border-slate-300 bg-white p-3 text-sm text-slate-800 focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:text-slate-400 resize-none"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
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
            <div class="relative bg-white rounded-xl shadow-[0_10px_30px_-5px_rgba(0,0,0,0.15)] border border-slate-200 w-full max-w-[320px] flex flex-col max-h-[90vh] lg:max-h-[calc(100vh-80px)] pointer-events-auto overflow-hidden" @click.stop>
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 rounded-t-xl">
                    <h3 class="font-bold text-slate-700 text-sm">Pengaturan Field</h3>
                    <button @click="activeEditIndex = null" class="text-slate-400 hover:text-rose-500 transition">
                        <X :size="16" />
                    </button>
                </div>

                <div class="p-4 space-y-4 overflow-y-auto">
                    <template v-if="draftFields[activeEditIndex]">
                        <!-- Floating Label Input: Field Name -->
                        <div class="relative mt-1">
                            <label class="absolute -top-2 left-2 px-1 text-[10px] font-bold text-slate-400 bg-white">Field Name</label>
                            <input v-model="draftFields[activeEditIndex].label" @input="updateKey(draftFields[activeEditIndex])" type="text" class="w-full text-xs font-semibold text-slate-800 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#FFC000] focus:border-[#FFC000]" />
                        </div>

                        <!-- Floating Label Select: Field Type -->
                        <div class="relative mt-2">
                            <label class="absolute -top-2 left-2 px-1 text-[10px] font-bold text-slate-400 bg-white">Field Type</label>
                            <select v-model="draftFields[activeEditIndex].type" class="w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#FFC000] focus:border-[#FFC000] font-semibold text-slate-800">
                                <option v-for="ft in FIELD_TYPES" :key="ft.value" :value="ft.value">{{ ft.label }}</option>
                            </select>
                        </div>

                        <div class="space-y-2 pt-1">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" v-model="draftFields[activeEditIndex].required" class="rounded border-slate-300 w-3.5 h-3.5 text-slate-600 focus:ring-0">
                                <span class="text-xs font-semibold text-slate-600">Required Field</span>
                            </label>


                        </div>

                        <!-- Opsi Dropdown jika select/radio -->
                        <div v-if="['select', 'radio'].includes(draftFields[activeEditIndex].type)" class="pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Opsi Pilihan</label>
                                <button @click="addOption(draftFields[activeEditIndex])" class="text-[#FFC000] hover:text-amber-500 transition p-1" title="Tambah Opsi">
                                    <Plus :size="14" />
                                </button>
                            </div>
                        <div class="space-y-1.5 max-h-[150px] overflow-y-auto pr-1 custom-scrollbar">
                            <div v-for="(opt, oidx) in draftFields[activeEditIndex].options" :key="oidx" class="flex items-center gap-1.5 group">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                                <input type="text" v-model="draftFields[activeEditIndex].options[oidx]" class="flex-1 text-xs bg-white border-0 border-b border-slate-200 px-1 py-1 focus:ring-0 focus:border-[#FFC000]" placeholder="Ketik opsi..." />
                                <button @click="removeOption(draftFields[activeEditIndex], oidx)" class="text-slate-300 hover:text-rose-500 transition p-1 opacity-0 group-hover:opacity-100">
                                    <X :size="12" />
                                </button>
                            </div>
                        </div>
                    </div>
                    </template>

                </div>

                <div class="px-3 py-2 flex flex-col gap-1.5 bg-white rounded-b-xl border-t border-slate-50">
                    <button @click="duplicateField(activeEditIndex); activeEditIndex = draftFields.length - 1" class="w-full flex justify-center items-center gap-1.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold text-[11px] hover:bg-slate-50 transition">
                        <Copy :size="12" /> Duplicate Field
                    </button>
                    <button @click="removeField(activeEditIndex)" class="w-full flex justify-center items-center gap-1.5 py-1.5 rounded-lg border border-rose-200 bg-white text-rose-500 font-semibold text-[11px] hover:bg-rose-50 transition">
                        <Trash2 :size="12" /> Delete Field
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
