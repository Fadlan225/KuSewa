<script setup>
import { Loader2, Camera, Clock, CheckCircle, XCircle, Check, Play, Pause, AlertTriangle, LineChart, Settings } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

import InformasiDasarTab from './show/InformasiDasarTab.vue';
import UnitTab from './show/UnitTab.vue';
import LokasiFasilitasTab from './show/LokasiFasilitasTab.vue';
import HargaTab from './show/HargaTab.vue';
import KetersediaanTab from './show/KetersediaanTab.vue';
import FotoTab from './show/FotoTab.vue';
import KebijkanFaqTab from './show/KebijkanFaqTab.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import AssetIllustration from '@/Components/ui/Icons/AssetIllustration.vue';
const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    galleryCategories: {
        type: Array,
        default: () => [],
    },
    mandatoryCategories: {
        type: Array,
        default: () => [],
    },
    masterFacilityCategories: {
        type: Array,
        default: () => [],
    },
    nearbyPlaces: {
        type: Object,
        default: () => ({}),
    },
    chartData: {
        type: Array,
        default: () => [],
    },
    ratingDistribution: {
        type: Array,
        default: () => [],
    }
});

const activeTab = ref('informasi');
const isSettingsOpen = ref(false);

const form = useForm({
    title: props.asset.title || '',
    address: props.asset.address || '',
    postal_code: props.asset.postal_code || '',
    description: props.asset.description || '',
    detail: props.asset.detail || {},
});

const submitForm = () => {
    form.put(route('owner.asset.update', props.asset.slug || props.asset.id), {
        preserveScroll: true,
    });
};

let saveTimeout = null;
watch(() => form.data(), (newVal, oldVal) => {
    // Terapkan auto-save dengan debounce (1 detik)
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        submitForm();
    }, 1000);
}, { deep: true });

// Helper Formatting
const formatRupiah = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
};

const assetFacilities = computed(() => props.asset.facilities || []);

const lowestPrice = computed(() => {
    let allPricings = [];
    if (props.asset.pricings && props.asset.pricings.length > 0) {
        allPricings = props.asset.pricings;
    } else if (props.asset.units && props.asset.units.length > 0) {
        props.asset.units.forEach(unit => {
            if (unit.pricings) {
                allPricings = allPricings.concat(unit.pricings);
            }
        });
    }
    if (allPricings.length === 0) return null;
    return allPricings.reduce((min, p) => p.price < min.price ? p : min, allPricings[0]);
});

const rentalUnitLabel = (unit) => {
    const labels = { hour: "jam", day: "hari", night: "malam", month: "bulan" };
    return labels[unit] ?? "sewa";
};

const showDeactivateModal = ref(false);

const hasActiveBookings = computed(() => {
    if (props.asset.bookings && props.asset.bookings.length > 0) return true;
    if (props.asset.units && props.asset.units.some(u => u.bookings && u.bookings.length > 0)) return true;
    return false;
});

const confirmDelete = () => {
    showDeactivateModal.value = true;
};

const isTogglingStatus = ref(false);

const proceedToggleStatus = (actionType = null) => {
    if (actionType === 'deactivate' && hasActiveBookings.value) return;

    router.patch(route('owner.asset.toggle-status', props.asset.slug || props.asset.id), {}, {
        preserveScroll: true,
        onStart: () => { isTogglingStatus.value = true; },
        onSuccess: () => {
            showDeactivateModal.value = false;
        },
        onFinish: () => {
            isTogglingStatus.value = false;
        }
    });
};

const fileInput = ref(null);
const isUploadingThumbnail = ref(false);

const triggerThumbnailUpload = () => {
    fileInput.value.click();
};

const handleThumbnailUpload = (event) => {
    const files = event.target.files;
    if (!files.length) return;

    const formData = new FormData();
    formData.append('thumbnail', files[0]);

    router.post(route('owner.asset.images.store', props.asset.slug || props.asset.id), formData, {
        preserveScroll: true,
        onStart: () => { isUploadingThumbnail.value = true; },
        onFinish: () => {
            isUploadingThumbnail.value = false;
            event.target.value = '';
        },
    });
};

const placeholderImage = 'https://placehold.co/800x500?text=Belum+Ada+Foto';
const thumbnail = computed(() => {
    if (props.asset.thumbnail_images && props.asset.thumbnail_images.length > 0) {
        const thumb = props.asset.thumbnail_images[0];
        return thumb.image_url || thumb.url || placeholderImage;
    }
    if (props.asset.images && props.asset.images.length > 0) {
        return props.asset.images[0].image_url || placeholderImage;
    }
    return placeholderImage;
});

const specItems = computed(() => {
    if (!props.asset.detail) return [];
    try {
        const details = typeof props.asset.detail === 'string' ? JSON.parse(props.asset.detail) : props.asset.detail;
        return Object.entries(details).map(([key, value]) => ({
            label: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            value: value
        }));
    } catch {
        return [];
    }
});

const totalUnitsCount = computed(() => {
    if (props.asset.type?.allow_units) {
        return props.asset.units?.length || 1;
    }
    return 1;
});

const occupiedUnitsCount = computed(() => {
    if (props.asset.type?.allow_units) {
        return props.asset.units?.filter(u => u.bookings && u.bookings.length > 0).length || 0;
    } else {
        return props.asset.bookings && props.asset.bookings.length > 0 ? 1 : 0;
    }
});

const availableUnitsCount = computed(() => {
    return totalUnitsCount.value - occupiedUnitsCount.value;
});



// Workspace Navigation Tabs
const workspaceTabs = computed(() => [
    { key: 'informasi',    label: 'Informasi Dasar',       active: activeTab.value === 'informasi' },
    { key: 'lokasi',       label: 'Lokasi & Fasilitas',    active: activeTab.value === 'lokasi' },
    { key: 'harga',        label: 'Harga & Kebijakan',     active: activeTab.value === 'harga' },
    ...(props.asset.type?.allow_units ? [{ key: 'unit', label: 'Daftar Unit', active: activeTab.value === 'unit' }] : []),
    { key: 'foto',         label: 'Foto & Dokumen',        active: activeTab.value === 'foto' },
]);

</script>

<template>
    <Head :title="`Manajemen: ${asset.title}`" />

    <DashboardLayout
        :title="asset.title"
        role="Owner"
    >
        <!-- ACTION SLOT FOR TOPBAR: Status Toggle -->
        <template #action>
            <div class="relative z-40">
                <button
                    @click="isSettingsOpen = !isSettingsOpen"
                    class="p-2.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition duration-200 focus:outline-none flex items-center justify-center"
                    title="Pengaturan Aset"
                >
                    <Settings class="w-5 h-5" />
                </button>

                <!-- Backdrop for closing -->
                <div v-if="isSettingsOpen" @click="isSettingsOpen = false" class="fixed inset-0 z-40"></div>

                <!-- Menu -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform scale-100 opacity-100 translate-y-0"
                    leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                >
                    <div v-if="isSettingsOpen" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50 origin-top-right overflow-hidden">
                        <div class="px-4 py-2 border-b border-slate-50 mb-1">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pengaturan Lanjutan</p>
                        </div>

                        <button v-if="asset.status === 'inactive'" @click="proceedToggleStatus('activate'); isSettingsOpen = false" class="w-full text-left px-4 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 transition flex items-center gap-2" :disabled="isTogglingStatus">
                            <Play class="w-4 h-4" />
                            Aktifkan Aset
                        </button>
                        <button v-else @click="confirmDelete(); isSettingsOpen = false" class="w-full text-left px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition flex items-center gap-2" :disabled="isTogglingStatus">
                            <Pause class="w-4 h-4" />
                            Nonaktifkan Aset
                        </button>
                    </div>
                </Transition>
            </div>
        </template>

        <template #afterTopbar>
            <!-- HORIZONTAL TAB NAVIGATION (Workspace style) -->
            <div class="bg-white border-b border-slate-200 flex overflow-x-auto hide-scrollbar sticky top-[120px] lg:top-[60px] z-20 px-4 md:px-6 lg:px-8 -mt-[1px]">
                <button
                    v-for="tab in workspaceTabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    :class="tab.active ? 'border-[#FFC000] text-slate-900 font-extrabold' : 'border-transparent text-slate-500 font-semibold hover:text-slate-800'"
                    class="whitespace-nowrap px-5 py-3.5 border-b-[3px] text-sm transition-all duration-200"
                >
                    {{ tab.label }}
                </button>
            </div>
        </template>

        <div class="w-full flex flex-col pt-4 md:pt-6">

            <!-- TAB CONTENT -->
            <div class="p-4 md:p-6 lg:p-8">
                <!-- 2. Informasi Dasar -->
                <div v-if="activeTab === 'informasi'" class="space-y-6">
                    <InformasiDasarTab :asset="asset" :form="form" :specItems="specItems" />
                </div>

                <!-- 3. Lokasi & Fasilitas -->
                <div v-if="activeTab === 'lokasi'" class="space-y-6">
                    <LokasiFasilitasTab :asset="asset" :form="form" :assetFacilities="assetFacilities" :masterFacilityCategories="masterFacilityCategories" :nearbyPlaces="nearbyPlaces" />
                </div>

                <!-- 4. Harga & Kebijakan -->
                <div v-if="activeTab === 'harga'" class="space-y-6">
                    <HargaTab :asset="asset" :lowestPrice="lowestPrice" />
                    <KebijkanFaqTab :asset="asset" />
                </div>

                <!-- 5. Daftar Unit -->
                <UnitTab v-if="activeTab === 'unit' && asset.type?.allow_units" :asset="asset" :galleryCategories="galleryCategories" />

                <!-- 6. Foto & Dokumen -->
                <FotoTab v-if="activeTab === 'foto'" :asset="asset" :galleryCategories="galleryCategories" :mandatoryCategories="mandatoryCategories" />

            </div>
        </div>
    </DashboardLayout>

    <!-- MODAL NONAKTIFKAN ASET -->
    <ConfirmModal
        :show="showDeactivateModal"
        type="primary"
        title="Nonaktifkan aset ini?"
        :message="hasActiveBookings
            ? 'Aset ini tidak dapat dinonaktifkan karena sedang memiliki penyewaan yang aktif atau menunggu konfirmasi. Harap selesaikan atau batalkan seluruh pesanan terlebih dahulu.'
            : 'Aset Anda tidak akan ditampilkan kepada calon penyewa selama dinonaktifkan. Anda bisa mengaktifkannya kembali kapan saja.'"
        :confirmText="hasActiveBookings ? 'Oke, Paham' : (isTogglingStatus ? 'Memproses...' : 'Nonaktifkan Aset')"
        cancelText="Batal"
        @confirm="hasActiveBookings ? showDeactivateModal = false : proceedToggleStatus('deactivate')"
        @cancel="showDeactivateModal = false"
    >
        <template #icon>
            <div class="w-48 h-auto mx-auto mb-2 drop-shadow-sm pointer-events-none">
                <AssetIllustration class="w-full h-full" />
            </div>
        </template>
    </ConfirmModal>
</template>
