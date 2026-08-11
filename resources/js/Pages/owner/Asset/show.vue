<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

import RingkasanTab from './show/RingkasanTab.vue';
import UnitTab from './show/UnitTab.vue';
import LokasiTab from './show/LokasiTab.vue';
import FasilitasTab from './show/FasilitasTab.vue';
import HargaTab from './show/HargaTab.vue';
import KetersediaanTab from './show/KetersediaanTab.vue';
import FotoTab from './show/FotoTab.vue';
import KebijkanFaqTab from './show/KebijkanFaqTab.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    galleryCategories: {
        type: Array,
        default: () => [],
    },
    masterFacilityCategories: {
        type: Array,
        default: () => [],
    }
});

const activeTab = ref('ringkasan');

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

const proceedDeactivate = () => {
    if (hasActiveBookings.value) return;
    router.delete(route('owner.asset.destroy', props.asset.slug || props.asset.id), {
        onSuccess: () => {
            showDeactivateModal.value = false;
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



// Sub-menu sidebar untuk tab navigasi aset (desktop only)
const assetSubMenu = computed(() => [
    { key: 'ringkasan',    label: 'Ringkasan',       icon: 'fa-solid fa-chart-pie',     active: activeTab.value === 'ringkasan',    onClick: () => activeTab.value = 'ringkasan' },
    { key: 'lokasi',      label: 'Lokasi',           icon: 'fa-solid fa-location-dot',  active: activeTab.value === 'lokasi',       onClick: () => activeTab.value = 'lokasi' },
    { key: 'fasilitas',   label: 'Fasilitas',        icon: 'fa-solid fa-star',          active: activeTab.value === 'fasilitas',    onClick: () => activeTab.value = 'fasilitas' },
    ...(props.asset.type?.allow_units ? [{ key: 'unit', label: 'Unit', icon: 'fa-solid fa-door-open', active: activeTab.value === 'unit', onClick: () => activeTab.value = 'unit' }] : []),
    { key: 'harga',       label: 'Harga & Aturan',  icon: 'fa-solid fa-tag',           active: activeTab.value === 'harga',        onClick: () => activeTab.value = 'harga' },
    { key: 'ketersediaan',label: 'Ketersediaan',     icon: 'fa-solid fa-calendar-check',active: activeTab.value === 'ketersediaan', onClick: () => activeTab.value = 'ketersediaan' },
    { key: 'foto',        label: 'Foto & Dokumen',   icon: 'fa-solid fa-images',        active: activeTab.value === 'foto',         onClick: () => activeTab.value = 'foto' },
    { key: 'kebijakan',   label: 'Kebijakan & FAQ',  icon: 'fa-solid fa-shield-halved', active: activeTab.value === 'kebijakan',    onClick: () => activeTab.value = 'kebijakan' },
]);

</script>

<template>
    <Head :title="`Manajemen: ${asset.title}`" />

    <DashboardLayout
        :title="asset.title"
        description="Kelola informasi, harga, ketersediaan, dan foto aset Anda."
        role="Owner"
        :breadcrumbs="[{ label: 'Dashboard', route: route('owner.dashboard') }, { label: 'Aset & Unit', route: route('owner.asset.index') }, { label: asset.title }]"
        :subMenu="assetSubMenu"
        subMenuParentRouteName="owner.asset.*"
    >
        <div class="w-full space-y-6">

            <!-- COMPACT HEADER SECTION -->
            <div class="flex flex-col md:flex-row gap-6">

                <!-- Left Image -->
                <div class="w-full md:w-[320px] aspect-[4/3] shrink-0 bg-slate-100 relative group rounded-xl overflow-hidden shadow-sm">
                    <img :src="thumbnail" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                        <input type="file" ref="fileInput" class="hidden" accept="image/png, image/jpeg, image/webp" @change="handleThumbnailUpload" />
                        <button @click="triggerThumbnailUpload" :disabled="isUploadingThumbnail" class="bg-white text-slate-800 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm hover:bg-slate-50 transition flex items-center gap-2">
                            <i v-if="isUploadingThumbnail" class="fa-solid fa-spinner fa-spin"></i>
                            <i v-else class="fa-solid fa-camera"></i>
                            {{ isUploadingThumbnail ? 'Mengunggah...' : 'Ubah Foto Utama' }}
                        </button>
                    </div>
                </div>

                <!-- Right Content -->
                <div class="flex flex-col flex-grow min-w-0">
                    <!-- Top Info (Title & Actions) -->
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 mb-4">
                        <div class="flex-1 min-w-0">
                            <!-- Title -->
                            <div class="flex items-center gap-3 flex-wrap mb-1.5">
                                <input v-model="form.title" type="text" class="text-2xl font-black text-[#0A2540] border-b-2 border-transparent hover:border-slate-200 focus:border-indigo-600 focus:ring-0 px-0 py-1 w-full max-w-lg bg-transparent transition truncate" placeholder="Nama Properti" />

                                <span v-if="asset.status === 'pending'" class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1"><i class="fa-solid fa-clock"></i> Menunggu</span>
                                <span v-else-if="asset.status === 'approved'" class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Terverifikasi</span>
                                <span v-else-if="asset.status === 'rejected'" class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1"><i class="fa-solid fa-circle-xmark"></i> Ditolak</span>
                                
                                <span v-if="form.processing" class="text-xs text-slate-400 flex items-center gap-1"><i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...</span>
                                <span v-else-if="form.recentlySuccessful" class="text-xs text-emerald-500 flex items-center gap-1"><i class="fa-solid fa-check"></i> Tersimpan</span>
                            </div>
                            <div v-if="form.errors.title" class="text-xs text-rose-500 mt-1 mb-2">{{ form.errors.title }}</div>

                            <!-- Subtitle / Location -->
                            <div class="text-sm text-slate-500 font-medium mb-1 truncate">
                                {{ asset.type?.category?.name || 'Kategori' }} &bull; {{ asset.address }}, {{ asset.city?.name }}
                            </div>
                            <!-- Asset Code -->
                            <div class="text-xs text-slate-400 font-medium">
                                Kode Aset: {{ asset.slug || asset.id }}
                            </div>
                        </div>

                        <!-- Right actions -->
                        <div class="shrink-0 flex items-center gap-2">
                            <button class="bg-white border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50 px-3 py-2 rounded-lg text-xs transition shadow-sm" @click="confirmDelete" title="Opsi Lanjutan">
                                <i class="fa-solid fa-ellipsis-vertical px-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Stats & Price Card -->
                    <div class="mt-auto bg-white border border-slate-100 rounded-xl shadow-sm flex flex-wrap md:flex-nowrap items-center divide-x divide-slate-100">
                        <div class="flex-1 py-4 px-4 flex flex-col items-center justify-center text-center">
                            <span class="text-lg font-black text-blue-600">{{ totalUnitsCount }}</span>
                            <span class="text-[10px] font-bold text-slate-500 mt-0.5">Total Unit</span>
                        </div>
                        <div class="flex-1 py-4 px-4 flex flex-col items-center justify-center text-center">
                            <span class="text-lg font-black text-rose-500">{{ occupiedUnitsCount }}</span>
                            <span class="text-[10px] font-bold text-slate-500 mt-0.5">Unit Terisi</span>
                        </div>
                        <div class="flex-1 py-4 px-4 flex flex-col items-center justify-center text-center">
                            <span class="text-lg font-black text-emerald-500">{{ availableUnitsCount }}</span>
                            <span class="text-[10px] font-bold text-slate-500 mt-0.5">Unit Tersedia</span>
                        </div>
                        <div class="w-full md:w-auto md:flex-[1.5] py-4 px-6 flex flex-col items-start bg-slate-50/50">
                            <span class="text-[10px] text-slate-400 font-medium mb-0.5">Mulai dari</span>
                            <div class="text-base font-black text-[#F97316]">
                                {{ lowestPrice ? formatRupiah(lowestPrice.price) : '-' }}
                                <span v-if="lowestPrice" class="text-[11px] text-slate-400 font-medium">/ {{ rentalUnitLabel(asset.type?.rental_unit) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB MENU -->
            <div class="bg-white border-b border-slate-200 flex overflow-x-auto hide-scrollbar sticky top-[70px] z-40 px-2 sm:px-6">
                <button @click="activeTab = 'ringkasan'" :class="activeTab === 'ringkasan' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Ringkasan</button>
                <button @click="activeTab = 'lokasi'" :class="activeTab === 'lokasi' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Lokasi</button>
                <button @click="activeTab = 'fasilitas'" :class="activeTab === 'fasilitas' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Fasilitas Aset</button>
                <button v-if="asset.type?.allow_units" @click="activeTab = 'unit'" :class="activeTab === 'unit' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Unit <span class="ml-1 bg-slate-100 text-slate-500 py-0.5 px-1.5 rounded-full text-[10px]">{{ asset.units?.length || 0 }}</span></button>
                <button @click="activeTab = 'harga'" :class="activeTab === 'harga' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Harga & Aturan</button>
                <button @click="activeTab = 'ketersediaan'" :class="activeTab === 'ketersediaan' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Ketersediaan</button>
                <button @click="activeTab = 'foto'" :class="activeTab === 'foto' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Foto & Dokumen</button>
                <button @click="activeTab = 'kebijakan'" :class="activeTab === 'kebijakan' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Kebijakan & FAQ</button>
            </div>

                <!-- TAB CONTENTS -->
                <div class="mt-4">
                    <RingkasanTab v-if="activeTab === 'ringkasan'" :asset="asset" :form="form" :specItems="specItems" :assetFacilities="assetFacilities" />
                    <LokasiTab v-if="activeTab === 'lokasi'" :asset="asset" :form="form" />
                    <FasilitasTab v-if="activeTab === 'fasilitas'" :asset="asset" :assetFacilities="assetFacilities" :masterFacilityCategories="masterFacilityCategories" />
                    <UnitTab v-if="activeTab === 'unit' && asset.type?.allow_units" :asset="asset" :galleryCategories="galleryCategories" />
                    <HargaTab v-if="activeTab === 'harga'" :asset="asset" :lowestPrice="lowestPrice" />
                    <KetersediaanTab v-if="activeTab === 'ketersediaan'" :asset="asset" />
                    <FotoTab v-if="activeTab === 'foto'" :asset="asset" :galleryCategories="galleryCategories" />
                    <KebijkanFaqTab v-if="activeTab === 'kebijakan'" :asset="asset" />
                </div>

        </div>
    </DashboardLayout>

    <!-- MODAL NONAKTIFKAN ASET -->
    <div v-if="showDeactivateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-rose-100 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Nonaktifkan Aset?</h3>

                <div v-if="hasActiveBookings" class="mb-6">
                    <p class="text-sm text-rose-600 font-semibold bg-rose-50 p-3 rounded-lg border border-rose-100 mb-3">
                        Aset ini tidak dapat dinonaktifkan karena sedang memiliki penyewaan yang aktif atau menunggu konfirmasi.
                    </p>
                    <p class="text-xs text-slate-500">
                        Harap selesaikan atau batalkan seluruh pesanan sebelum menonaktifkan aset ini.
                    </p>
                </div>
                <div v-else class="mb-6">
                    <p class="text-sm text-slate-500">
                        Aset ini tidak akan lagi terlihat oleh publik. Anda bisa mengaktifkannya kembali di kemudian hari dengan menghubungi CS.
                    </p>
                </div>

                <div class="flex items-center gap-3 w-full">
                    <button @click="showDeactivateModal = false" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        {{ hasActiveBookings ? 'Tutup' : 'Batal' }}
                    </button>
                    <button v-if="!hasActiveBookings" @click="proceedDeactivate" class="flex-1 px-4 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                        Ya, Nonaktifkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
