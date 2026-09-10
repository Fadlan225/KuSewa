<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, X, Plus, Trash2, ChevronDown, GripVertical, Lock, Copy, ChevronLeft, ChevronRight, Building, Loader2, RotateCcw, Settings } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import EmptyFieldIcon from '@/Components/ui/Icons/EmptyFieldIcon.vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import Toast from '@/Components/ui/Toast.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import ResetIllustration from '@/Components/ui/Icons/ResetIllustration.vue';
import draggable from 'vuedraggable';
import { nextTick } from 'vue';

/* ── Props ────────────────────────────────────────────────────────── */
const props = defineProps({
    assetTypes: { type: Array, default: () => [] },
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
const selectedType  = ref(null);    // object tipe aset yang dipilih
const selectedTypeId = ref('');
const fieldScope    = ref('asset');  // 'asset' | 'unit'

const isSaving = ref(false);
const isInitializing = ref(false);
let saveTimeout = null;

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
    isInitializing.value = true;
    const t = selectedType.value;
    draftFields.value  = JSON.parse(JSON.stringify(
        fieldScope.value === 'asset' ? (t.detail_fields || []) : (t.unit_detail_fields || [])
    ));
    activeEditIndex.value = null;
    nextTick(() => {
        isInitializing.value = false;
    });
}

watch(draftFields, () => {
    if (!isInitializing.value) {
        isSaving.value = true;
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            saveFields();
        }, 1000);
    }
}, { deep: true });

// Reload draft saat scope berubah
function onFieldScopeChange() { loadDraft(); }

/* ── Field types ──────────────────────────────────────────────────── */
const FIELD_TYPES = [
    { value: 'text', label: 'Teks Pendek' },
    { value: 'textarea', label: 'Teks Panjang (Textarea)' },
    { value: 'number', label: 'Angka Bebas' },
    { value: 'year', label: 'Tahun (Angka)' },
    { value: 'counter', label: 'Penghitung (Tombol - / +)' },
    { value: 'select', label: 'Dropdown Pilihan' },
    { value: 'searchable_select', label: 'Dropdown dengan Pencarian' },
    { value: 'radio', label: 'Pilihan Tunggal (Radio)' },
    { value: 'checkbox_list', label: 'Kotak Centang (Banyak Pilihan)' },
    { value: 'checkbox', label: 'Toggle (Ya/Tidak)' },
    { value: 'date', label: 'Tanggal (Kalender)' },
    { value: 'time', label: 'Input Waktu / Jam' },
    { value: 'time_range', label: 'Rentang Jam (Mulai - Selesai)' },
    { value: 'room_size', label: 'Dimensi (Panjang x Lebar)' },
];
const hasOptions  = (type) => ['select', 'radio', 'searchable_select', 'room_size', 'checkbox_list'].includes(type);

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
    draftFields.value.unshift({
        key: generateKey(defaultLabel),
        label: defaultLabel,
        type: 'text',
        required: false,
        options: ['A', 'B', 'C']
    });
    
    // Tutup popover pengaturan jika sedang terbuka,
    // karena index berubah akibat penambahan di awal array
    activeEditIndex.value = null;
}

// Remove a draft field
const removeField = (index) => {
    draftFields.value.splice(index, 1);
    if (activeEditIndex.value === index) activeEditIndex.value = null;
};

/* ── Swipe to Delete ──────────────────────────────────────────────── */
const swipeState = ref({
    index: null,
    startX: 0,
    currentX: 0,
    isSwiping: false,
});

function onTouchStart(e, index) {
    if (e.target.closest('.drag-handle')) return;
    
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
        removeField(swipeState.value.index);
    }
    swipeState.value = { index: null, startX: 0, currentX: 0, isSwiping: false };
}

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

/* ── Options Extra Input Logic ────────────────────────────────────────── */
const activeOptionConfig = ref(null);
function toggleOptionConfig(idx, oidx) {
    const key = `${idx}-${oidx}`;
    activeOptionConfig.value = activeOptionConfig.value === key ? null : key;
}
const activeOptionData = computed(() => {
    if (!activeOptionConfig.value) return null;
    const parts = activeOptionConfig.value.split('-');
    const idx = parseInt(parts[0]);
    
    // Pastikan modal input tambahan hanya muncul untuk kolom yang sedang aktif diedit
    if (idx !== activeEditIndex.value) return null;
    
    const oidx = parseInt(parts[1]);
    const field = draftFields.value[idx];
    if (!field || !field.options) return null;
    return { field, opt: field.options[oidx], oidx };
});

// Reset konfigurasi opsi saat berpindah kolom
watch(activeEditIndex, () => {
    activeOptionConfig.value = null;
});
function updateOption(field, oidx, newValue) {
    const oldValue = field.options[oidx];
    if (oldValue === newValue) return;
    field.options[oidx] = newValue;
    if (field.option_configs && field.option_configs[oldValue]) {
        field.option_configs[newValue] = field.option_configs[oldValue];
        delete field.option_configs[oldValue];
    }
}
function initOptionConfig(field, optValue) {
    if (!field.option_configs) field.option_configs = {};
    if (!field.option_configs[optValue]) {
        field.option_configs[optValue] = {
            has_input: false,
            label: '',
            type: 'text',
            placeholder: ''
        };
    }
    return field.option_configs[optValue];
}

const showResetModal = ref(false);

function resetToDefault() {
    if (!selectedType.value) return;
    showResetModal.value = true;
}

const isResetting = ref(false);

async function executeReset() {
    if (!selectedType.value || isResetting.value) return;

    isResetting.value = true;
    showResetModal.value = false;

    try {
        const response = await fetch(
            route('admin.konfigurasi-aset.reset-fields', selectedType.value.id),
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ scope: fieldScope.value }),
            }
        );

        const json = await response.json();

        if (!response.ok) {
            toastState.value = {
                show: true,
                message: json.message ?? 'Gagal mereset ke default.',
                type: 'error',
            };
            setTimeout(() => toastState.value.show = false, 3000);
            return;
        }

        // Update draftFields dari server — tanpa memicu auto-save (gunakan isInitializing)
        isInitializing.value = true;
        draftFields.value = JSON.parse(JSON.stringify(json.fields ?? []));
        activeEditIndex.value = null;

        // Update local props reference agar konsisten
        const t = props.assetTypes.find(a => a.id === selectedType.value.id);
        if (t) {
            if (fieldScope.value === 'asset') t.detail_fields = JSON.parse(JSON.stringify(json.fields ?? []));
            else t.unit_detail_fields = JSON.parse(JSON.stringify(json.fields ?? []));
        }

        await nextTick();
        isInitializing.value = false;

        toastState.value = {
            show: true,
            message: json.message ?? 'Berhasil dikembalikan ke pengaturan default.',
            type: 'success',
        };
        setTimeout(() => toastState.value.show = false, 3000);

    } catch {
        toastState.value = { show: true, message: 'Terjadi kesalahan saat mereset.', type: 'error' };
        setTimeout(() => toastState.value.show = false, 3000);
    } finally {
        isResetting.value = false;
    }
}

/* ── Save ─────────────────────────────────────────────────────────── */
function saveFields() {
    if (!selectedType.value) return;
    isSaving.value = true;
    router.put(route('admin.konfigurasi-aset.fields', selectedType.value.id), {
        scope:  fieldScope.value,
        fields: draftFields.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            // Update local props reference
            const t = props.assetTypes.find(a => a.id === selectedType.value.id);
            if (t) {
                if (fieldScope.value === 'asset') t.detail_fields = JSON.parse(JSON.stringify(draftFields.value));
                else t.unit_detail_fields = JSON.parse(JSON.stringify(draftFields.value));
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
    <Head title="Spesifikasi Form" />

    <DashboardLayout role="Admin" title="Spesifikasi Form" description="Kelola konfigurasi field informasi spesifik per tipe aset." no-padding hide-title>
        <template #leftAction>
            <div class="relative text-left w-full sm:w-[320px]" ref="dropdownRef">
                <button
                    @click="toggleDropdown"
                    class="flex items-center justify-between w-full gap-2.5 bg-white hov    er:bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 hover:border-[#FFC000] hover:shadow-sm transition-all focus:outline-none"
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
                <p class="text-xs">untuk mengatur konfigurasi spesifikasi form</p>
            </div>

            <div v-else class="flex flex-col lg:flex-row w-full items-start">

                <!-- ============================================== -->
                <!-- LEFT SIDEBAR (STICKY & FULL HEIGHT)            -->
                <!-- ============================================== -->
                <div class="w-full lg:w-[340px] shrink-0 bg-white border-r border-slate-200 flex flex-col h-[calc(100vh-121px)] lg:h-[calc(100vh-61px)] sticky top-[121px] lg:top-[61px] z-20 shadow-[4px_0_24px_-15px_rgba(0,0,0,0.1)]">

                    <!-- Scrollable Content Area -->
                    <div class="flex-1 overflow-y-auto bg-slate-50/30 flex flex-col relative [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none;">

                        <!-- ── TAB: Field Informasi ────────────────────────── -->
                        <div class="flex flex-col h-full">

                            <!-- Scope toggle & List Header -->
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
                                    <h3 class="font-bold text-slate-800 text-[13px] whitespace-nowrap">Spesifikasi Form</h3>
                                    <button @click="addField" class="text-xs font-bold text-[#FFC000] hover:text-amber-500 transition flex items-center gap-1 shrink-0">
                                        <Plus :size="14" /> Tambah Kolom
                                    </button>
                                </div>
                            </div>

                            <!-- Draggable Builder List -->
                            <div class="flex-1 p-4 lg:p-5 flex flex-col gap-1.5">
                                <!-- Top Locked Fields (Unit Scope) -->
                                <template v-if="fieldScope === 'unit'">
                                    <div class="relative z-10 rounded-lg border border-slate-200 bg-white">
                                        <div class="flex items-center px-4 py-3 gap-3">
                                            <div class="text-slate-300 p-0.5 -ml-1">
                                                <Lock :size="16" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[13px] font-bold text-[#0A2540]">
                                                    Nama Tipe {{ selectedType?.unit_label || 'Unit' }}
                                                    <span class="text-rose-500 ml-0.5 text-xs">*</span>
                                                </p>
                                            </div>
                                            <div class="flex items-center pr-1 text-slate-400">
                                                <ChevronRight :size="16" />
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <draggable v-model="draftFields" item-key="key" :animation="200" class="space-y-1.5 pb-2" :class="fieldScope === 'unit' ? 'mt-1.5' : ''">
                                    <template #item="{ element: field, index: idx }">
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
                                                 @touchstart="(e) => onTouchStart(e, idx)"
                                                 @touchmove="onTouchMove"
                                                 @touchend="onTouchEnd"
                                                 @touchcancel="onTouchEnd">
                                                <div class="flex items-center px-4 py-3 gap-3 cursor-pointer group select-none" @click.stop="openSettings(idx, $event)" @contextmenu.prevent.stop="openSettings(idx, $event)">
                                                    <div class="drag-handle text-slate-300 group-hover:text-slate-400 cursor-grab active:cursor-grabbing p-0.5 -ml-1 transition-colors" title="Seret untuk memindahkan urutan" @click.stop>
                                                        <GripVertical :size="16" :stroke-width="2.5" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[13px] font-bold truncate" :class="activeEditIndex === idx ? 'text-slate-800' : 'text-slate-600 group-hover:text-slate-700'">
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
                                        </div>
                                    </template>
                                </draggable>

                                <!-- Bottom Locked Fields (Unit Scope) -->
                                <template v-if="fieldScope === 'unit'">
                                    <div class="relative z-10 rounded-lg border border-slate-200 bg-white">
                                        <div class="flex items-center px-4 py-3 gap-3">
                                            <div class="text-slate-300 p-0.5 -ml-1">
                                                <Lock :size="16" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[13px] font-bold text-[#0A2540]">
                                                    Deskripsi {{ selectedType?.unit_label || 'Unit' }}
                                                </p>
                                            </div>
                                            <div class="flex items-center pr-1 text-slate-400">
                                                <ChevronRight :size="16" />
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div v-if="draftFields.length === 0" class="text-center py-16 flex flex-col items-center justify-center gap-3">
                                    <EmptyFieldIcon :size="120" />
                                    <span class="text-[15px] font-bold text-slate-700">Belum Ada Kolom yang Dikonfigurasi</span>
                                    <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                        Tambahkan spesifikasi tambahan untuk tipe aset ini seperti dimensi, fasilitas, aturan, atau detail lainnya.
                                    </span>
                                    <button @click.stop="addField" class="mt-4 bg-white border border-slate-200 hover:border-[#FFC000] hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                                        <Plus :size="16" class="text-[#FFC000]" /> Tambah Kolom
                                    </button>
                                </div>
                            </div>

                            <!-- Footer Save Button -->
                            <div class="p-5 bg-white border-t border-slate-200 mt-auto sticky bottom-0 z-20 shadow-[0_-15px_30px_-10px_rgba(0,0,0,0.08)] flex items-stretch gap-2">
                                <button @click="resetToDefault" :disabled="isResetting" class="flex items-center justify-center w-11 shrink-0 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors shadow-sm disabled:opacity-50" title="Kembalikan ke Default">
                                    <Loader2 v-if="isResetting" class="w-4 h-4 animate-spin" />
                                    <RotateCcw v-else :size="20" :stroke-width="2.5" />
                                </button>
                                <button @click="saveFields" :disabled="isSaving" class="flex-1 rounded-lg bg-[#FFC000] px-5 py-3 text-sm font-bold text-[#0A2540] hover:bg-amber-400 transition shadow-sm flex items-center justify-center gap-2 disabled:opacity-80">
                                    <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin text-[#0A2540]" />
                                    <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Konfigurasi Kolom' }}</span>
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
                                    Belum ada kolom khusus yang ditambahkan. Silakan tambah kolom baru untuk melihat preview form pendaftaran aset.
                                </span>
                                <button @click.stop="addField" class="mt-2 bg-white border border-slate-200 hover:border-[#FFC000] hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                                    <Plus :size="16" class="text-[#FFC000]" /> Tambah Kolom
                                </button>
                            </div>

                            <div v-for="(field, idx) in draftFields" :key="idx" class="relative group transition-all select-none border border-transparent rounded-lg bg-white pr-6 py-5 pl-6 ring-1 ring-slate-200/80 shadow-sm" :class="activeEditIndex === idx ? '!border-[#FFC000] shadow-md bg-amber-50/5' : ''">
                                <label v-if="field.type !== 'checkbox'" class="block text-base font-bold text-slate-800 mb-2">
                                    {{ field.label || 'Label Kosong' }}
                                    <span v-if="field.required" class="text-rose-500">*</span>
                                    <span v-else class="text-sm font-normal text-slate-400">(opsional)</span>
                                </label>

                                <!-- Type: Radio (Pill Style) -->
                                <div v-if="field.type === 'radio'" class="flex items-center w-full bg-white border border-slate-200 rounded-lg overflow-hidden flex-wrap sm:flex-nowrap">
                                    <label v-for="(opt, oidx) in (field.options?.length ? field.options : ['A', 'B', 'C'])" :key="oidx" class="flex-1 text-center w-full sm:w-auto font-medium relative cursor-pointer" :class="[oidx < (field.options?.length || 3) - 1 ? 'sm:border-r border-b sm:border-b-0 border-slate-200' : '']">
                                        <input type="radio" :name="`preview_radio_${idx}`" :value="opt" v-model="previewValues[field.key]" class="peer sr-only" />
                                        <div class="py-3 text-slate-500 peer-checked:bg-[#FFF8E6] peer-checked:text-[#0A2540] peer-checked:font-bold transition-all hover:bg-slate-50">
                                            {{ opt || `Opsi ${oidx+1}` }}
                                        </div>
                                    </label>
                                </div>

                                <!-- Type: Checkbox List (Multiple Choices) -->
                                <div v-else-if="field.type === 'checkbox_list'" class="flex flex-col gap-3">
                                    <label v-for="(opt, oidx) in (field.options?.length ? field.options : ['A', 'B', 'C'])" :key="oidx" class="flex items-center gap-3 cursor-pointer group w-fit">
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" :value="opt"
                                                   :checked="Array.isArray(previewValues[field.key]) && previewValues[field.key].includes(opt)"
                                                   @change="(e) => {
                                                       if (!Array.isArray(previewValues[field.key])) previewValues[field.key] = [];
                                                       if (e.target.checked) {
                                                           if (!previewValues[field.key].includes(opt)) previewValues[field.key].push(opt);
                                                       } else {
                                                           previewValues[field.key] = previewValues[field.key].filter(v => v !== opt);
                                                       }
                                                   }"
                                                   class="peer sr-only" />
                                            <div class="w-5 h-5 border-2 border-slate-300 rounded bg-white peer-checked:bg-[#FFC000] peer-checked:border-[#FFC000] transition-colors"></div>
                                            <svg class="absolute w-3.5 h-3.5 text-[#0A2540] opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900 transition-colors">{{ opt }}</span>
                                    </label>
                                </div>

                                <!-- Type: Select -->
                                <div v-else-if="field.type === 'select'" class="relative flex flex-col gap-3">
                                    <CustomSelect
                                        v-model="previewValues[field.key]"
                                        :options="field.options?.length ? field.options : ['A', 'B', 'C']"
                                        :placeholder="field.placeholder || `Pilih ${field.label || 'Opsi'}...`"
                                        :fullWidth="true"
                                    />
                                    
                                    <!-- Extra Input Preview -->
                                    <div v-if="field.option_configs && field.option_configs[previewValues[field.key]]?.has_input" class="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-2 mt-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">
                                            {{ field.option_configs[previewValues[field.key]].label || 'Input Tambahan' }}
                                        </label>
                                        
                                        <div v-if="field.option_configs[previewValues[field.key]].type === 'time_range'" class="flex items-center gap-3">
                                            <div class="flex-1 relative">
                                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Jam Mulai</label>
                                                <input type="time" v-model="previewValues[`${field.key}_${previewValues[field.key]}_start`]" class="w-full text-sm px-3 py-2 rounded-md border border-slate-300 text-slate-800 bg-white focus:ring-2 focus:ring-[#FFC000] outline-none" />
                                            </div>
                                            <span class="text-slate-400 font-bold shrink-0 mt-4">-</span>
                                            <div class="flex-1 relative">
                                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Jam Selesai</label>
                                                <input type="time" v-model="previewValues[`${field.key}_${previewValues[field.key]}_end`]" class="w-full text-sm px-3 py-2 rounded-md border border-slate-300 text-slate-800 bg-white focus:ring-2 focus:ring-[#FFC000] outline-none" />
                                            </div>
                                        </div>
                                        
                                        <div v-else>
                                            <input :type="field.option_configs[previewValues[field.key]].type === 'number' || field.option_configs[previewValues[field.key]].type === 'decimal' ? 'number' : 'text'" 
                                                   :step="field.option_configs[previewValues[field.key]].type === 'decimal' ? '0.01' : '1'"
                                                   v-model="previewValues[`${field.key}_${previewValues[field.key]}`]" 
                                                   :placeholder="field.option_configs[previewValues[field.key]].placeholder || 'Ketik jawaban...'" 
                                                   class="w-full text-sm px-3 py-2 rounded-md border border-slate-300 text-slate-800 bg-white focus:ring-2 focus:ring-[#FFC000] outline-none" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Type: Counter -->
                                <div v-else-if="field.type === 'counter'" class="flex items-center justify-between w-full border border-slate-300 rounded-lg overflow-hidden bg-white focus-within:border-[#FFC000] focus-within:ring-1 focus-within:ring-[#FFC000] transition-all">
                                    <button type="button" @click="previewValues[field.key] = Math.max(1, (previewValues[field.key] || 1) - 1)" class="w-12 h-11 flex items-center justify-center border-r border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer shrink-0"><span class="text-xl font-bold">-</span></button>
                                    <input type="number" min="1" v-model="previewValues[field.key]" class="flex-1 text-center text-base font-bold text-slate-800 border-0 focus:border-transparent focus:ring-0 shadow-none outline-none m-0 p-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none [-moz-appearance:textfield]" @keydown="['-', '+', 'e', 'E'].includes($event.key) && $event.preventDefault()" @input="if(previewValues[field.key] !== '' && previewValues[field.key] !== null && previewValues[field.key] < 1) previewValues[field.key] = 1;" @blur="if(!previewValues[field.key] || previewValues[field.key] < 1) previewValues[field.key] = 1;" />
                                    <button type="button" @click="previewValues[field.key] = Math.max(1, (previewValues[field.key] || 1) + 1)" class="w-12 h-11 flex items-center justify-center border-l border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer shrink-0"><span class="text-xl font-bold">+</span></button>
                                </div>

                                <!-- Type: Room Size -->
                                <div v-else-if="field.type === 'room_size'" class="mb-2">
                                    <div class="flex flex-wrap gap-3 mb-4">
                                        <button v-for="(opt, oidx) in (field.options?.length && field.options[0] !== 'A' ? field.options : ['3 x 3', '4 x 4', '3 x 4'])" :key="oidx" type="button"
                                                @click="() => {
                                                    const parts = opt.toLowerCase().split('x');
                                                    if(parts.length >= 2) {
                                                        previewValues[`${field.key}_p`] = parseFloat(parts[0].trim()) || 1;
                                                        previewValues[`${field.key}_l`] = parseFloat(parts[1].trim()) || 1;
                                                    }
                                                }"
                                                class="px-5 py-2.5 text-base font-bold border-2 rounded-lg transition-colors" :class="(previewValues[`${field.key}_p`] == parseFloat(opt.toLowerCase().split('x')[0]) && previewValues[`${field.key}_l`] == parseFloat(opt.toLowerCase().split('x')[1])) ? 'bg-[#FFF8E6] border-[#FFC000] text-[#0A2540]' : 'border-slate-200 text-slate-600 bg-white hover:border-slate-300 hover:bg-slate-50'">{{ opt }}</button>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="number" min="1" step="any" v-model="previewValues[`${field.key}_p`]" @keydown="['-', '+', 'e', 'E'].includes($event.key) && $event.preventDefault()" @input="if(previewValues[`${field.key}_p`] !== '' && previewValues[`${field.key}_p`] !== null && previewValues[`${field.key}_p`] < 1) previewValues[`${field.key}_p`] = 1;" @blur="if(!previewValues[`${field.key}_p`] || previewValues[`${field.key}_p`] < 1) previewValues[`${field.key}_p`] = 1;" placeholder="P" class="flex-1 min-w-0 text-sm px-4 py-2.5 rounded-lg border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:font-normal placeholder:text-slate-400" />
                                        <span class="text-slate-400 font-bold shrink-0">x</span>
                                        <input type="number" min="1" step="any" v-model="previewValues[`${field.key}_l`]" @keydown="['-', '+', 'e', 'E'].includes($event.key) && $event.preventDefault()" @input="if(previewValues[`${field.key}_l`] !== '' && previewValues[`${field.key}_l`] !== null && previewValues[`${field.key}_l`] < 1) previewValues[`${field.key}_l`] = 1;" @blur="if(!previewValues[`${field.key}_l`] || previewValues[`${field.key}_l`] < 1) previewValues[`${field.key}_l`] = 1;" placeholder="L" class="flex-1 min-w-0 text-sm px-4 py-2.5 rounded-lg border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:font-normal placeholder:text-slate-400" />
                                        <span class="text-sm font-medium text-slate-500 shrink-0">Meter</span>
                                    </div>
                                </div>

                                <!-- Type: Checkbox (Toggle style) -->
                                <div v-else-if="field.type === 'checkbox'" class="flex items-center justify-between w-full cursor-pointer py-1" @click="previewValues[field.key] = !previewValues[field.key]">
                                    <label class="block text-base font-bold text-slate-800 cursor-pointer pointer-events-none">
                                        {{ field.label || 'Label Kosong' }}
                                        <span v-if="field.required" class="text-rose-500">*</span>
                                        <span v-else class="text-sm font-normal text-slate-400">(opsional)</span>
                                    </label>
                                    <div class="flex items-center shrink-0 ml-4">
                                        <div class="w-11 h-6 rounded-full relative transition-colors" :class="previewValues[field.key] ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                            <div class="absolute top-[2px] left-[2px] bg-white shadow-sm border border-slate-100 rounded-full h-5 w-5 transition-transform" :class="previewValues[field.key] ? 'translate-x-5' : 'translate-x-0'"></div>
                                        </div>
                                        <span class="ml-3 text-sm font-bold w-10 text-left" :class="previewValues[field.key] ? 'text-[#0A2540]' : 'text-slate-500'">{{ previewValues[field.key] ? 'Ya' : 'Tidak' }}</span>
                                    </div>
                                </div>

                                <!-- Type: Date -->
                                <div v-else-if="field.type === 'date'">
                                    <input type="date" v-model="previewValues[field.key]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all" />
                                </div>

                                <!-- Type: Time -->
                                <div v-else-if="field.type === 'time'">
                                    <input type="time" v-model="previewValues[field.key]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all" />
                                </div>
                                
                                <!-- Type: Time Range -->
                                <div v-else-if="field.type === 'time_range'" class="flex items-center gap-3 w-full">
                                    <div class="flex-1 relative">
                                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Jam Mulai</label>
                                        <input type="time" v-model="previewValues[`${field.key}_start`]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all" />
                                    </div>
                                    <span class="text-slate-400 font-bold shrink-0 mt-6">-</span>
                                    <div class="flex-1 relative">
                                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Jam Selesai</label>
                                        <input type="time" v-model="previewValues[`${field.key}_end`]" class="w-full text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all" />
                                    </div>
                                </div>

                                <!-- Type: Year -->
                                <div v-else-if="field.type === 'year'">
                                    <input type="number" min="1900" max="2100" v-model="previewValues[field.key]" @keydown="['-', '+', 'e', 'E', '.', ','].includes($event.key) && $event.preventDefault()" :placeholder="field.placeholder || field.label || 'Misal: 2024'" class="w-full text-sm px-4 py-2.5 rounded-lg border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:font-normal placeholder:text-slate-400" />
                                </div>

                                <!-- Type: Searchable Select -->
                                <div v-else-if="field.type === 'searchable_select'">
                                    <SearchableSelect
                                        v-model="previewValues[field.key]"
                                        :options="(field.options?.length ? field.options : ['A', 'B', 'C']).map(opt => ({ code: opt, name: opt }))"
                                        :placeholder="field.placeholder || `Cari ${field.label || 'Opsi'}...`"
                                    />
                                </div>

                                <!-- Type: Textarea -->
                                <div v-else-if="field.type === 'textarea'">
                                    <textarea v-model="previewValues[field.key]" :placeholder="field.placeholder || field.label || 'Ketik jawaban di sini...'" class="w-full h-24 text-sm px-4 py-2.5 rounded-md border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:font-normal placeholder:text-slate-400 resize-none"></textarea>
                                </div>

                                <!-- Default: Text, Number -->
                                <div v-else>
                                    <input :type="field.type === 'number' ? 'number' : 'text'" :min="field.type === 'number' ? '1' : null" v-model="previewValues[field.key]" @keydown="(field.type === 'number' && ['-', '+', 'e', 'E'].includes($event.key)) && $event.preventDefault()" @input="(field.type === 'number' && previewValues[field.key] !== '' && previewValues[field.key] !== null && previewValues[field.key] < 1) && (previewValues[field.key] = 1)" @blur="(field.type === 'number' && (!previewValues[field.key] || previewValues[field.key] < 1)) && (previewValues[field.key] = 1)" :placeholder="field.placeholder || field.label || 'Ketik jawaban di sini...'" class="w-full text-sm px-4 py-2.5 rounded-lg border border-slate-300 text-slate-800 bg-white font-medium focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none transition-all placeholder:font-normal placeholder:text-slate-400" />
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
        <Toast :show="toastState.show" :message="toastState.message" :type="toastState.type" />
        
        <!-- Reset Confirmation Modal -->
        <ConfirmModal
            :show="showResetModal"
            type="primary"
            title="Reset ke Default"
            message="Yakin ingin mereset spesifikasi ke pengaturan default (bawaan) untuk tipe aset ini? Semua perubahan yang belum disimpan akan hilang."
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
            <div class="relative bg-white rounded-lg shadow-lg border border-slate-200 w-[260px] sm:w-[280px] flex flex-col pointer-events-auto overflow-hidden" :style="{ maxHeight: 'calc(100vh - ' + popoverTop + 'px - 24px)' }" @click.stop>
                <div class="px-5 pt-5 pb-2 flex items-center justify-between">
                    <h3 class="font-bold text-[#0A2540] text-sm">Pengaturan Kolom</h3>
                    <button @click="activeEditIndex = null" class="text-slate-400 hover:text-slate-600 transition">
                        <X :size="16" />
                    </button>
                </div>

                <div class="px-5 py-2 space-y-4 overflow-y-auto custom-scrollbar">
                    <template v-if="draftFields[activeEditIndex]">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600">Nama Kolom</label>
                            <input v-model="draftFields[activeEditIndex].label" @input="updateKey(draftFields[activeEditIndex])" type="text" class="w-full text-sm font-medium text-slate-800 bg-slate-50/50 border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all placeholder:text-slate-400" />
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600">Tipe Kolom</label>
                            <CustomSelect
                                v-model="draftFields[activeEditIndex].type"
                                :options="FIELD_TYPES"
                                placeholder="Pilih Tipe Kolom"
                            />
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer py-1">
                            <input type="checkbox" v-model="draftFields[activeEditIndex].required" class="w-4 h-4 rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540] cursor-pointer">
                            <span class="text-sm font-medium text-slate-700">Wajib Diisi</span>
                        </label>

                        <!-- Pengaturan Placeholder -->
                        <div class="space-y-1.5" v-if="['text', 'textarea', 'number', 'year', 'select', 'searchable_select'].includes(draftFields[activeEditIndex].type)">
                            <label class="block text-xs font-semibold text-slate-600">Teks Petunjuk (Placeholder)</label>
                            <input v-model="draftFields[activeEditIndex].placeholder" type="text" placeholder="Contoh: Masukkan data..." class="w-full text-sm font-medium text-slate-800 bg-slate-50/50 border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all placeholder:text-slate-400" />
                        </div>

                        <!-- Opsi Dropdown jika select/radio/searchable_select -->
                        <div v-if="hasOptions(draftFields[activeEditIndex].type)" class="pt-3">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-slate-600">Opsi Pilihan</label>
                                <button @click="addOption(draftFields[activeEditIndex])" class="text-slate-400 hover:text-[#0A2540] transition p-1 rounded-md hover:bg-slate-100" title="Tambah Opsi">
                                    <Plus :size="14" />
                                </button>
                            </div>
                            <div class="space-y-2 max-h-[220px] overflow-y-auto overflow-x-hidden pr-1 custom-scrollbar">
                                <div v-for="(opt, oidx) in draftFields[activeEditIndex].options" :key="oidx" class="flex flex-col gap-1.5 w-full">
                                    <div class="flex items-center gap-1.5 group w-full">
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></div>
                                        <input type="text" :value="opt" @input="(e) => updateOption(draftFields[activeEditIndex], oidx, e.target.value)" class="flex-1 min-w-0 text-sm text-slate-700 bg-transparent border-0 border-b border-slate-200 px-1 py-1 focus:ring-0 focus:border-slate-400 transition-colors" placeholder="Ketik opsi..." />
                                        <button v-if="draftFields[activeEditIndex].type === 'select'" @click="toggleOptionConfig(activeEditIndex, oidx)" class="text-slate-400 hover:text-amber-500 transition p-1 shrink-0" title="Konfigurasi Tambahan">
                                            <Settings :size="14" />
                                        </button>
                                        <button @click="removeOption(draftFields[activeEditIndex], oidx)" class="text-slate-400 hover:text-rose-500 transition p-1 opacity-0 group-hover:opacity-100 shrink-0" title="Hapus Opsi">
                                            <X :size="14" />
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="p-3 flex flex-col gap-1 mt-2 mb-1">
                    <button @click="duplicateField(activeEditIndex); activeEditIndex = draftFields.length - 1" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-[#0A2540] transition-colors text-sm font-medium text-left">
                        <Copy :size="16" class="shrink-0" />
                        <span>Duplikat Kolom</span>
                    </button>
                    <button @click="removeField(activeEditIndex)" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-colors text-sm font-medium text-left">
                        <Trash2 :size="16" class="shrink-0" />
                        <span>Hapus Kolom</span>
                    </button>
                </div>
            </div>

            <!-- Nested Popover (Side Panel) -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-x-3"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 -translate-x-3"
            >
                <div v-if="activeOptionData" class="absolute inset-x-4 top-1/2 -translate-y-1/2 lg:translate-y-0 lg:inset-x-auto lg:left-full lg:top-8 lg:ml-3 bg-white rounded-xl shadow-[0_0_20px_rgba(0,0,0,0.1)] border border-slate-200 w-auto lg:w-[320px] flex flex-col pointer-events-auto z-10" @click.stop>
                    <!-- Triangle Arrow (Desktop only) -->
                    <div class="hidden lg:block absolute top-8 -left-[7px] w-3 h-3 bg-white border-l border-b border-slate-200 rotate-45"></div>
                    
                    <div class="relative z-10 bg-white rounded-xl overflow-hidden flex flex-col">
                        <!-- Header -->
                        <div class="px-5 py-4 flex items-center justify-between">
                            <h3 class="font-bold text-[#0A2540] text-[15px]">Input Tambahan</h3>
                            <button @click="activeOptionConfig = null" class="text-slate-400 hover:text-rose-500 transition">
                                <X :size="18" />
                            </button>
                        </div>
                        
                        <!-- Body -->
                        <div class="px-5 pb-5 space-y-5">
                            <p class="text-[13px] text-slate-500 -mt-2 leading-relaxed">Atur konfigurasi input tambahan untuk opsi pilihan <span class="font-bold text-[#FFC000]">{{ activeOptionData.opt }}</span>.</p>
                            
                            <label class="flex items-center gap-3 cursor-pointer">
                                <!-- Toggle UI (Kitasewa Style) -->
                                <div class="relative inline-flex items-center shrink-0">
                                    <input type="checkbox" v-model="initOptionConfig(activeOptionData.field, activeOptionData.opt).has_input" class="sr-only peer">
                                    <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-[16px] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FFC000]"></div>
                                </div>
                                <span class="text-[13px] font-bold text-slate-700">Memiliki input tambahan?</span>
                            </label>
                            
                            <div v-if="initOptionConfig(activeOptionData.field, activeOptionData.opt).has_input" class="space-y-4 pt-1">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-slate-500">Label Input</label>
                                    <input type="text" v-model="initOptionConfig(activeOptionData.field, activeOptionData.opt).label" placeholder="Misal: Masukkan keterangan" class="w-full text-[13px] px-3 py-2.5 border border-slate-200 rounded-lg focus:ring-1 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none bg-white transition-all text-slate-800 font-medium" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-slate-500">Tipe Input</label>
                                    <CustomSelect
                                        v-model="initOptionConfig(activeOptionData.field, activeOptionData.opt).type"
                                        :options="[
                                            { value: 'text', label: 'Teks' },
                                            { value: 'number', label: 'Angka' },
                                            { value: 'decimal', label: 'Desimal' }
                                        ]"
                                        placeholder="Pilih Tipe Input"
                                        :fullWidth="true"
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-slate-500">Teks Petunjuk (Placeholder)</label>
                                    <input type="text" v-model="initOptionConfig(activeOptionData.field, activeOptionData.opt).placeholder" placeholder="Teks petunjuk..." class="w-full text-[13px] px-3 py-2.5 border border-slate-200 rounded-lg focus:ring-1 focus:ring-[#FFC000] focus:border-[#FFC000] outline-none bg-white transition-all text-slate-800 font-medium placeholder:font-normal" />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="px-5 py-4 border-t border-slate-100 flex justify-end gap-2 bg-white">
                            <button @click="activeOptionConfig = null" class="px-5 py-2 rounded-lg border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                            <button @click="activeOptionConfig = null" class="px-6 py-2 rounded-lg bg-[#FFC000] hover:bg-amber-400 text-[#0A2540] text-sm font-bold transition-colors">Simpan</button>
                        </div>
                    </div>
                </div>
            </Transition>
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
