<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, X, Plus, Trash2, ChevronDown, GripVertical, Building, Loader2, RotateCcw, Settings, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import EmptyFieldIcon from '@/Components/ui/Icons/EmptyFieldIcon.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import Toast from '@/Components/ui/Toast.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import ResetIllustration from '@/Components/ui/Icons/ResetIllustration.vue';
import draggable from 'vuedraggable';

/* ── Props ────────────────────────────────────────────────────────── */
const props = defineProps({
    assetTypes: { type: Array, default: () => [] },
    serviceFees: { type: Array, default: () => [] }
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
const fieldScope    = ref('asset'); // 'asset' | 'unit'

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
    activePopover.value = null;
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
    document.addEventListener('click', (e) => {
        if(activePopover.value) {
            if(!e.target.closest('.popover-desktop') && !e.target.closest('.trigger-btn')) {
                closePopover();
            }
        }
    });

    if (props.assetTypes && props.assetTypes.length > 0) {
        const lastSelected = localStorage.getItem('last_selected_asset_type_payment');
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
});

function selectAssetType(id) {
    if (id) {
        selectedTypeId.value = id;
        localStorage.setItem('last_selected_asset_type_payment', id);
    }
    isDropdownOpen.value = false;
}

watch(selectedTypeId, (newId) => {
    if (newId) {
        selectedType.value = props.assetTypes.find(t => t.id == newId);
        filterDraftFees();
    } else {
        selectedType.value = null;
    }
});

/* ── Service Fees Filtering & Dragging ────────────────────────────── */
const draftFees = ref([]);
const isSavingOrder = ref(false);

const filterDraftFees = () => {
    if (!selectedType.value) {
        draftFees.value = [];
        return;
    }
    const filtered = props.serviceFees.filter(f => 
        f.asset_type_id == selectedType.value.id && 
        (f.scope || 'asset') === fieldScope.value
    );
    filtered.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
    draftFees.value = JSON.parse(JSON.stringify(filtered));
    activeEditIndex.value = null;
};

watch(() => props.serviceFees, () => {
    filterDraftFees();
}, { deep: true });

const activeFee = computed(() => {
    return draftFees.value.length > 0 ? draftFees.value[0] : null;
});

const saveOrder = () => {
    isSavingOrder.value = true;
    const orderedIds = draftFees.value.map(f => f.id);
    
    router.post(route('admin.payment-system.reorder'), { 
        ordered_ids: orderedIds 
    }, {
        preserveScroll: true,
        onFinish: () => { 
            isSavingOrder.value = false;
        }
    });
};

/* ── Popover Form State ───────────────────────────────────────────── */
const activeEditIndex = ref(null);
const activePopover = ref(null); // 'add' | 'edit'
const popoverTop = ref(0);
const popoverLeft = ref(0);

const form = ref({
    id: null,
    asset_type_id: null,
    scope: 'asset',
    name: '',
    description: '',
    fee_type: 'fixed',
    fee_value: 0,
});

const openAddPopover = (event) => {
    if (activePopover.value === 'add') {
        closePopover();
        return;
    }
    activePopover.value = 'add';
    activeEditIndex.value = null;
    form.value = {
        id: null,
        asset_type_id: selectedType.value.id,
        scope: fieldScope.value,
        name: '',
        description: '',
        fee_type: 'fixed',
        fee_value: 0,
    };
    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        popoverTop.value = Math.max(16, Math.min(rect.bottom + 8, window.innerHeight - 450));
        popoverLeft.value = Math.max(16, Math.min(rect.right - 280, window.innerWidth - 300));
    }
};

const openSettings = (idx, event) => {
    if (activeEditIndex.value === idx) {
        closePopover();
        return;
    }
    activeEditIndex.value = idx;
    activePopover.value = 'edit';
    const element = draftFees.value[idx];
    form.value = {
        id: element.id,
        asset_type_id: element.asset_type_id,
        scope: element.scope || fieldScope.value,
        name: element.name,
        description: element.description || '',
        fee_type: element.fee_type,
        fee_value: parseFloat(element.fee_value),
    };
    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        popoverTop.value = Math.max(16, Math.min(rect.top - 10, window.innerHeight - 450));
        popoverLeft.value = rect.right + 12;
    }
};

const isSubmitting = ref(false);

const saveFee = () => {
    isSubmitting.value = true;
    form.value.asset_type_id = selectedType.value.id;
    form.value.scope = fieldScope.value;

    const isEdit = activePopover.value === 'edit';
    const method = isEdit ? 'put' : 'post';
    const routeArgs = isEdit 
        ? route('admin.payment-system.update', form.value.id) 
        : route('admin.payment-system.store');

    router[method](routeArgs, form.value, {
        preserveScroll: true,
        onFinish: () => {
            isSubmitting.value = false;
            closePopover();
        }
    });
};

/* ── Delete Confirmation ─────────────────────────────────────────── */
const confirmDeleteModal = ref(false);
const feeToDelete = ref(null);

const confirmDelete = (id) => {
    feeToDelete.value = id;
    confirmDeleteModal.value = true;
    closePopover();
};

const deleteFee = () => {
    if (feeToDelete.value) {
        router.delete(route('admin.payment-system.destroy', feeToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                confirmDeleteModal.value = false;
            }
        });
    }
};

/* ── Reset to Default ────────────────────────────────────────────── */
const showResetModal = ref(false);
const isResetting = ref(false);

function resetToDefault() {
    if (!selectedType.value) return;
    showResetModal.value = true;
}

const executeReset = () => {
    if (!selectedType.value || isResetting.value) return;
    isResetting.value = true;
    showResetModal.value = false;

    router.post(route('admin.payment-system.reset', selectedType.value.id), {
        scope: fieldScope.value
    }, {
        preserveScroll: true,
        onFinish: () => {
            isResetting.value = false;
            closePopover();
        }
    });
};

/* ── Simulation Calculator ───────────────────────────────────────── */
const simPrice = ref(500000);
const simDuration = ref(1);

const formattedSimPrice = computed({
    get() {
        return simPrice.value ? new Intl.NumberFormat('id-ID').format(simPrice.value) : '';
    },
    set(val) {
        const numericStr = String(val).replace(/\D/g, '');
        simPrice.value = numericStr ? parseInt(numericStr, 10) : 0;
    }
});

const durationLabel = computed(() => {
    if (!selectedType.value) return 'Hari';
    const unitMap = {
        'night': 'Malam',
        'month': 'Bulan',
        'day': 'Hari',
        'hour': 'Jam',
        'week': 'Minggu'
    };
    return unitMap[selectedType.value.default_rental_unit] || 'Hari';
});

const maxDuration = computed(() => {
    if (!selectedType.value) return 366;
    const maxMap = {
        'month': 12,
        'day': 366,
        'night': 366,
        'hour': 24,
        'week': 52
    };
    return maxMap[selectedType.value.default_rental_unit] || 366;
});

// Clamp simDuration if it exceeds max
watch([simDuration, maxDuration], ([newVal, maxVal]) => {
    if (newVal > maxVal) {
        simDuration.value = maxVal;
    }
    if (newVal < 1) {
        simDuration.value = 1;
    }
});

const calculatedFee = computed(() => {
    if (!activeFee.value) return 0;
    const subtotal = simPrice.value * simDuration.value;
    if (activeFee.value.fee_type === 'fixed') {
        return parseFloat(activeFee.value.fee_value) || 0;
    } else {
        return subtotal * ((parseFloat(activeFee.value.fee_value) || 0) / 100);
    }
});

const calculatedSubtotal = computed(() => {
    return (simPrice.value || 0) * (simDuration.value || 1);
});

const calculatedGrandTotal = computed(() => {
    return calculatedSubtotal.value + calculatedFee.value;
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

</script>

<template>
    <Head title="Manajemen Biaya Platform" />

    <DashboardLayout role="Admin" title="Manajemen Biaya Platform" description="Kelola konfigurasi tarif biaya layanan platform per tipe aset." no-padding hide-title>
        
        <!-- Header Left Action: Asset Type Dropdown Picker -->
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
                <p class="text-xs">untuk mengatur konfigurasi tarif biaya layanan platform</p>
            </div>

            <div v-else class="flex flex-col lg:flex-row w-full items-start">

                <!-- ============================================== -->
                <!-- LEFT SIDEBAR (STICKY & FULL HEIGHT)            -->
                <!-- ============================================== -->
                <div class="w-full lg:w-[340px] shrink-0 bg-white border-r border-slate-200 flex flex-col h-[calc(100vh-121px)] lg:h-[calc(100vh-61px)] sticky top-[121px] lg:top-[61px] z-20 shadow-[4px_0_24px_-15px_rgba(0,0,0,0.1)]">

                    <!-- Scrollable Content Area -->
                    <div class="flex-1 overflow-y-auto bg-slate-50/30 flex flex-col relative [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none;">

                        <div class="flex flex-col h-full">

                            <!-- Scope toggle & List Header -->
                            <div class="px-4 pt-3 pb-3 flex flex-col gap-3 sticky top-0 bg-slate-50/95 backdrop-blur z-20 border-b border-slate-200 shadow-sm">
                                <!-- Header + Add Button -->
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-slate-800 text-[13px] whitespace-nowrap">Biaya Platform</h3>
                                    <button @click="openAddPopover($event)" class="trigger-btn text-xs font-bold text-[#FFC000] hover:text-amber-500 transition flex items-center gap-1 shrink-0">
                                        <Plus :size="14" /> Tambah Biaya
                                    </button>
                                </div>
                            </div>

                            <!-- Draggable Builder List -->
                            <div class="flex-1 p-4 lg:p-5 flex flex-col gap-1.5">
                                <draggable 
                                    v-if="draftFees.length > 0"
                                    v-model="draftFees" 
                                    item-key="id" 
                                    handle=".drag-handle" 
                                    class="space-y-1.5 pb-2 mt-1.5 select-none" 
                                    ghost-class="ghost-card"
                                    :animation="200">
                                    <template #item="{ element, index }">
                                        <div class="relative overflow-hidden rounded-lg">
                                            <div class="relative z-10 rounded-lg transition-transform border bg-white" 
                                                 :class="activeEditIndex === index ? 'border-[#FFC000] shadow-sm' : 'border-slate-200 hover:border-slate-300 hover:shadow-sm'">
                                                <div class="flex items-center px-4 py-3 gap-3 cursor-pointer group select-none trigger-btn" @click.stop="openSettings(index, $event)">
                                                    <div class="drag-handle text-slate-300 group-hover:text-slate-400 cursor-grab active:cursor-grabbing p-0.5 -ml-1 transition-colors" title="Seret untuk memindahkan urutan" @click.stop>
                                                        <GripVertical :size="16" :stroke-width="2.5" />
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex items-center gap-2">
                                                        <p class="text-[13px] font-bold truncate" :class="activeEditIndex === index ? 'text-slate-800' : 'text-slate-600 group-hover:text-slate-700'">
                                                            {{ element.name }}
                                                        </p>
                                                        <span v-if="index === 0" class="text-[10px] font-bold bg-[#FFC000]/20 text-[#0A2540] border border-[#FFC000]/30 px-1.5 py-0.5 rounded shrink-0">Aktif</span>
                                                    </div>
                                                    <div class="flex items-center pr-1 transition-colors" :class="activeEditIndex === index ? 'text-slate-600' : 'text-slate-400 group-hover:text-slate-500'">
                                                        <ChevronLeft v-if="activeEditIndex === index" :size="16" />
                                                        <ChevronRight v-else :size="16" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </draggable>

                                <!-- Empty State -->
                                <div v-if="draftFees.length === 0" class="text-center py-16 flex flex-col items-center justify-center gap-3">
                                    <Building class="w-16 h-16 text-slate-200" />
                                    <span class="text-[15px] font-bold text-slate-700">Belum Ada Biaya Platform</span>
                                    <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                        Tambahkan biaya layanan platform untuk tipe aset ini.
                                    </span>
                                    <button @click.stop="openAddPopover($event)" class="trigger-btn mt-4 bg-white border border-slate-200 hover:border-[#FFC000] hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                                        <Plus :size="16" class="text-[#FFC000]" /> Tambah Biaya
                                    </button>
                                </div>
                            </div>

                            <!-- Footer Save Button -->
                            <div class="p-5 bg-white border-t border-slate-200 mt-auto sticky bottom-0 z-20 shadow-[0_-15px_30px_-10px_rgba(0,0,0,0.08)] flex items-stretch gap-2">
                                <button @click="resetToDefault" :disabled="isResetting" class="flex items-center justify-center w-11 shrink-0 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors shadow-sm disabled:opacity-50" title="Kembalikan ke Default">
                                    <Loader2 v-if="isResetting" class="w-4 h-4 animate-spin" />
                                    <RotateCcw v-else :size="20" :stroke-width="2.5" />
                                </button>
                                <button @click="saveOrder" :disabled="isSavingOrder" class="flex-1 rounded-lg bg-[#FFC000] px-5 py-3 text-sm font-bold text-[#0A2540] hover:bg-amber-400 transition shadow-sm flex items-center justify-center gap-2 disabled:opacity-80">
                                    <Loader2 v-if="isSavingOrder" class="w-4 h-4 animate-spin text-[#0A2540]" />
                                    <span>{{ isSavingOrder ? 'Menyimpan...' : 'Simpan Konfigurasi Profil' }}</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ============================================== -->
                <!-- RIGHT CANVAS (LIVE PREVIEW)                    -->
                <!-- ============================================== -->
                <div class="flex-1 w-full bg-slate-50/70 p-6 lg:p-12 relative">
                    <div class="mt-8 max-w-4xl mx-auto pb-20 space-y-4">
                        
                        <div v-if="draftFees.length === 0" class="text-center py-16 flex flex-col items-center justify-center gap-3 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                            <EmptyFieldIcon :size="100" />
                            <span class="text-[15px] font-bold text-slate-700">Preview Biaya Kosong</span>
                            <span class="text-[13px] text-slate-500 text-center px-6 leading-relaxed max-w-sm">
                                Belum ada biaya platform yang ditambahkan. Silakan tambah biaya baru untuk melihat simulasi biaya tagihan.
                            </span>
                            <button @click.stop="openAddPopover($event)" class="trigger-btn mt-2 bg-white border border-slate-200 hover:border-[#FFC000] hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                                <Plus :size="16" class="text-[#FFC000]" /> Tambah Biaya
                            </button>
                        </div>

                        <!-- Card Preview Simulasi -->
                        <div v-else class="relative group transition-all select-none border border-transparent rounded-lg bg-white pr-6 py-5 pl-6 ring-1 ring-slate-200/80 shadow-sm">
                            <label class="block text-base font-bold text-slate-800 mb-4">Simulasi Tagihan Penyewa</label>
                            
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center w-full bg-slate-50 border border-slate-300 rounded-md overflow-hidden sm:flex-nowrap focus-within:ring-2 focus-within:ring-[#FFC000] focus-within:border-[#FFC000] transition-all">
                                    <span class="px-4 py-2.5 text-slate-500 font-medium border-r border-slate-300 bg-slate-50 shrink-0 w-44">Harga Sewa Dasar (Rp)</span>
                                    <input v-model="formattedSimPrice" type="text" @wheel="$event.target.blur()" class="w-full text-sm px-4 py-2.5 bg-white outline-none font-bold text-[#0A2540]" />
                                </div>
                                <div class="flex items-center w-full bg-slate-50 border border-slate-300 rounded-md overflow-hidden sm:flex-nowrap focus-within:ring-2 focus-within:ring-[#FFC000] focus-within:border-[#FFC000] transition-all">
                                    <span class="px-4 py-2.5 text-slate-500 font-medium border-r border-slate-300 bg-slate-50 shrink-0 w-44">Durasi Sewa ({{ durationLabel }})</span>
                                    <input v-model.number="simDuration" type="number" min="1" :max="maxDuration" @wheel="$event.target.blur()" class="w-full text-sm px-4 py-2.5 bg-white outline-none font-bold text-[#0A2540]" />
                                </div>

                                <div class="bg-slate-50 rounded-lg border border-slate-200 p-4 space-y-3 mt-4">
                                    <div class="flex justify-between items-center text-sm font-medium text-slate-600">
                                        <span>Subtotal Biaya Sewa</span>
                                        <span class="font-bold text-slate-800">{{ formatCurrency(calculatedSubtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm font-medium text-slate-600 pb-3 border-b border-dashed border-slate-300">
                                        <div class="flex items-center gap-2">
                                            <span>Biaya Layanan Platform</span>
                                            <span v-if="activeFee" class="px-2 py-0.5 rounded text-[10px] bg-slate-200 text-slate-600 font-medium">
                                                {{ activeFee.fee_type === 'fixed' ? 'Fixed' : activeFee.fee_value + '%' }}
                                            </span>
                                        </div>
                                        <span class="font-bold text-slate-800">{{ formatCurrency(calculatedFee) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-1 text-base font-black text-[#0A2540]">
                                        <span>Total Tagihan Penyewa</span>
                                        <span class="text-amber-500 font-black">{{ formatCurrency(calculatedGrandTotal) }}</span>
                                    </div>
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
            message="Yakin ingin mereset profil biaya untuk tipe aset ini kembali ke tarif default (Rp 5.000 fixed)? Profil kustom yang telah dibuat untuk cakupan ini akan dihapus."
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

    <!-- Unified Popover Form -->
    <Transition
        mode="out-in"
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-90 -translate-x-2"
        enter-to-class="opacity-100 scale-100 translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100 scale-100 translate-x-0"
        leave-to-class="opacity-0 scale-90 -translate-x-2"
    >
        <div v-if="activePopover" :key="activePopover" class="fixed inset-0 lg:inset-auto z-[200] flex items-center justify-center lg:block p-4 lg:p-0 pointer-events-none popover-desktop lg:origin-left" :style="{ '--popover-top': popoverTop + 'px', '--popover-left': popoverLeft + 'px' }">
            <!-- Mobile backdrop -->
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm lg:hidden pointer-events-auto" @click="closePopover"></div>

            <div class="relative bg-white rounded-xl shadow-2xl border border-slate-100 w-full max-w-sm lg:w-[320px] flex flex-col pointer-events-auto popover-container" @click.stop>
                <div class="px-5 pt-5 pb-3 flex items-center justify-between border-b border-slate-100 bg-slate-50/80 rounded-t-xl">
                    <h3 class="font-bold text-[#0A2540] text-sm">
                        {{ activePopover === 'edit' ? 'Edit Biaya Platform' : 'Tambah Biaya Platform' }}
                    </h3>
                    <button @click="closePopover" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-md hover:bg-slate-100">
                        <X :size="16" />
                    </button>
                </div>

                <div class="px-5 py-4 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Nama Biaya Platform <span class="text-rose-500">*</span></label>
                        <input v-model="form.name" type="text" class="w-full text-sm font-medium px-3 py-2.5 bg-slate-50/50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all placeholder:text-slate-400 text-slate-800" placeholder="Contoh: Promo Akhir Tahun" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Tipe Biaya <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="relative flex items-center justify-center p-2.5 border rounded-lg cursor-pointer transition-all"
                                :class="form.fee_type === 'fixed' ? 'bg-[#FFC000]/10 border-[#FFC000] text-[#0A2540]' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
                                <input type="radio" v-model="form.fee_type" value="fixed" class="sr-only">
                                <span class="font-semibold text-xs">Nominal (Rp)</span>
                            </label>
                            <label class="relative flex items-center justify-center p-2.5 border rounded-lg cursor-pointer transition-all"
                                :class="form.fee_type === 'percentage' ? 'bg-[#FFC000]/10 border-[#FFC000] text-[#0A2540]' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
                                <input type="radio" v-model="form.fee_type" value="percentage" class="sr-only">
                                <span class="font-semibold text-xs">Persen (%)</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Nilai Biaya <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span v-if="form.fee_type === 'fixed'" class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-bold text-xs">Rp</span>
                            <input
                                v-model.number="form.fee_value"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                @wheel="$event.target.blur()"
                                class="w-full text-sm font-medium py-2.5 bg-slate-50/50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all text-slate-800"
                                :class="form.fee_type === 'fixed' ? 'pl-9 pr-3' : 'pl-3 pr-9'"
                                placeholder="0">
                            <span v-if="form.fee_type === 'percentage'" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 font-bold text-xs">%</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Deskripsi (Opsional)</label>
                        <textarea v-model="form.description" rows="2" class="w-full text-sm font-medium px-3 py-2.5 bg-slate-50/50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all text-slate-800 placeholder:text-slate-400 resize-none" placeholder="Jelaskan penggunaan profil..."></textarea>
                    </div>
                </div>

                <div class="p-3 bg-slate-50/80 border-t border-slate-100 rounded-b-xl flex flex-col gap-1.5">
                    <button v-if="activePopover === 'edit' && draftFees.length > 1" @click="confirmDelete(form.id)" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-white text-rose-600 font-medium text-sm rounded-lg hover:bg-rose-50 transition-colors">
                        <Trash2 :size="16" /> Hapus Biaya Platform
                    </button>
                    <button @click="saveFee" :disabled="!form.name || form.fee_value === '' || isSubmitting" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-[#FFC000] hover:bg-amber-400 text-[#0A2540] font-bold text-sm rounded-lg transition-colors shadow-sm disabled:opacity-50">
                        <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                        <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Biaya Platform' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <ConfirmModal
        :show="confirmDeleteModal"
        title="Hapus Profil Biaya"
        message="Apakah Anda yakin ingin menghapus profil biaya ini? Jika ini adalah profil aktif, pastikan Anda telah menyiapkan penggantinya."
        confirmText="Ya, Hapus"
        cancelText="Batal"
        @confirm="deleteFee"
        @cancel="confirmDeleteModal = false"
    />
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
