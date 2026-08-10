<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

import RingkasanTab from './show/RingkasanTab.vue';
import UnitTab from './show/UnitTab.vue';
import InformasiTab from './show/InformasiTab.vue';
import HargaTab from './show/HargaTab.vue';
import KetersediaanTab from './show/KetersediaanTab.vue';
import FotoTab from './show/FotoTab.vue';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    galleryCategories: {
        type: Array,
        default: () => [],
    }
});

// Edit Mode State
const isEditing = ref(false);
const activeTab = ref('ketersediaan'); // Set to ketersediaan temporarily to show user the changes

const form = useForm({
    title: props.asset.title || '',
    address: props.asset.address || '',
    description: props.asset.description || '',
});

const toggleEdit = () => {
    if (isEditing.value) {
        form.reset(); // Cancel changes
    }
    isEditing.value = !isEditing.value;
};

const submitForm = () => {
    form.put(route('owner.asset.update', props.asset.slug || props.asset.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

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

const placeholderImage = 'https://placehold.co/800x500?text=Belum+Ada+Foto';
const thumbnail = computed(() => {
    if (props.asset.thumbnailImages && props.asset.thumbnailImages.length > 0) {
        const thumb = props.asset.thumbnailImages[0];
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



</script>

<template>
    <Head :title="`Manajemen: ${asset.title}`" />

    <DashboardLayout>

        <!-- STICKY SAVE BAR (Appears when editing) -->
        <transition enter-active-class="transition ease-out duration-200" enter-from-class="transform -translate-y-full" enter-to-class="transform translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="transform translate-y-0" leave-to-class="transform -translate-y-full">
            <div v-if="isEditing" class="fixed top-[70px] left-0 right-0 z-50 flex items-center justify-center pointer-events-none mt-4">
                <div class="bg-[#0A2540] text-white px-6 py-3 rounded-full shadow-lg pointer-events-auto flex items-center gap-4 border border-[#14385f]">
                    <span class="text-sm font-semibold">Anda sedang dalam Mode Edit</span>
                    <div class="flex items-center gap-2 border-l border-[#14385f] pl-4">
                        <button @click="toggleEdit" class="text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-slate-700 transition" :disabled="form.processing">Batal</button>
                        <button @click="submitForm" class="text-xs font-bold px-4 py-1.5 bg-emerald-500 hover:bg-emerald-600 rounded-lg shadow-sm transition flex items-center gap-1.5" :disabled="form.processing">
                            <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                            <i v-else class="fa-solid fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <div class="w-full space-y-6">

            <!-- COMPACT HEADER SECTION -->
            <div class="flex flex-col md:flex-row gap-6">

                <!-- Left Image -->
                <div class="w-full md:w-[320px] aspect-[4/3] shrink-0 bg-slate-100 relative group rounded-xl overflow-hidden shadow-sm">
                    <img :src="thumbnail" class="w-full h-full object-cover" />
                    <div v-if="isEditing" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                        <button class="bg-white text-slate-800 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">Ubah Foto Utama</button>
                    </div>
                </div>

                <!-- Right Content -->
                <div class="flex flex-col flex-grow min-w-0">
                    <!-- Top Info (Title & Actions) -->
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 mb-4">
                        <div class="flex-1 min-w-0">
                            <!-- Title (View/Edit) -->
                            <div class="flex items-center gap-3 flex-wrap mb-1.5">
                                <h1 v-if="!isEditing" class="text-2xl font-black text-[#0A2540] truncate">{{ asset.title }}</h1>
                                <input v-else v-model="form.title" type="text" class="text-2xl font-black text-slate-900 border-b-2 border-indigo-400 focus:border-indigo-600 focus:ring-0 px-0 py-1 w-full bg-transparent transition" placeholder="Nama Properti" />

                                <span v-if="asset.status === 'pending'" class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1"><i class="fa-solid fa-clock"></i> Menunggu</span>
                                <span v-else-if="asset.status === 'approved'" class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Terverifikasi</span>
                                <span v-else-if="asset.status === 'rejected'" class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1"><i class="fa-solid fa-circle-xmark"></i> Ditolak</span>
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
                            <button @click="toggleEdit" :class="isEditing ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-white border border-slate-200 text-[#0A2540] hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-[11px] font-bold transition shadow-sm">
                                {{ isEditing ? 'Batal Edit' : 'Edit Informasi Aset' }}
                            </button>
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
                <button @click="activeTab = 'unit'" :class="activeTab === 'unit' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Unit <span class="ml-1 bg-slate-100 text-slate-500 py-0.5 px-1.5 rounded-full text-[10px]">{{ asset.units?.length || 0 }}</span></button>
                <button @click="activeTab = 'informasi'" :class="activeTab === 'informasi' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Informasi Aset</button>
                <button @click="activeTab = 'harga'" :class="activeTab === 'harga' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Harga & Aturan</button>
                <button @click="activeTab = 'ketersediaan'" :class="activeTab === 'ketersediaan' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Ketersediaan</button>
                <button @click="activeTab = 'foto'" :class="activeTab === 'foto' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-primary'" class="whitespace-nowrap px-4 py-3 border-b-2 font-bold text-sm transition">Foto & Dokumen</button>
            </div>

                <!-- TAB CONTENTS -->
                <div class="mt-4">
                    <RingkasanTab v-if="activeTab === 'ringkasan'" :asset="asset" :isEditing="isEditing" :form="form" :specItems="specItems" :assetFacilities="assetFacilities" />
                    <UnitTab v-if="activeTab === 'unit'" :asset="asset" />
                    <InformasiTab v-if="activeTab === 'informasi'" :asset="asset" :isEditing="isEditing" :form="form" :specItems="specItems" />
                    <HargaTab v-if="activeTab === 'harga'" :asset="asset" :lowestPrice="lowestPrice" />
                    <KetersediaanTab v-if="activeTab === 'ketersediaan'" :asset="asset" />
                    <FotoTab v-if="activeTab === 'foto'" :asset="asset" :galleryCategories="galleryCategories" />
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
