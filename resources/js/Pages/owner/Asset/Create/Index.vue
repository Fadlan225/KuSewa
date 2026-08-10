<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import Step1 from './Step1.vue';
import Step2 from './Step2.vue';
import Step3 from './Step3.vue';

// Fix bug ikon marker default Leaflet yang tidak muncul di build Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

// --- PROPS dari Controller (data dari DB) ---
const props = defineProps({
    categories: Array, // [{ id, name, types: [{id, name, rental_unit, allow_units}] }]
});

// --- DATA DINAMIS dari API (dimuat setelah owner memilih jenis aset) ---
const assetTypeDetails = ref(null);  // { allow_units, rental_unit, facilities, unit_facilities, gallery_categories, detail_fields, unit_detail_fields }
const isLoadingTypeDetails = ref(false);

const fetchAssetTypeDetails = async (typeId) => {
    if (!typeId) {
        assetTypeDetails.value = null;
        return;
    }
    isLoadingTypeDetails.value = true;
    try {
        const res = await fetch(`/api/asset-type/${typeId}/details`);
        assetTypeDetails.value = await res.json();
    } catch (e) {
        console.error('Gagal memuat detail jenis aset', e);
    } finally {
        isLoadingTypeDetails.value = false;
    }
};

// Helper: apakah jenis aset ini menggunakan unit?
const allowUnits = computed(() => assetTypeDetails.value?.allow_units ?? false);

// Helper: jenis aset yang tersedia sesuai kategori yang dipilih
const availableTypes = computed(() => {
    if (!form.category_id) return [];
    const cat = props.categories?.find(c => c.id === form.category_id);
    return cat ? cat.types : [];
});

// --- INERTIA FORM STATE ---
const makeEmptyUnit = () => ({
    _id: Date.now() + Math.random(),
    name: '',
    quantity: 1,
    price: '',
    detail: {},
    facility_ids: [],
});

const form = useForm({
    // Step 1: Informasi Aset
    title: '',
    description: '',
    category_id: props.categories?.[0]?.id ?? null,
    asset_type_id: props.categories?.[0]?.types?.[0]?.id ?? null,
    detail: {},
    facility_ids: [],  // fasilitas aset (scope = asset)
    units: [makeEmptyUnit()],  // hanya digunakan jika allow_units = true

    // Step 2: Lokasi
    address: '',
    province_code: '',
    city_code: '',
    district_code: '',
    village_code: '',
    postal_code: '',
    latitude: '',
    longitude: '',

    // Step 3: Harga & Foto
    price: '',       // digunakan jika allow_units = false
    photos: [        // array grup foto
        {
            _id: Date.now(),
            gallery_category_id: null,
            files: [],
            previews: [],
        }
    ],
});

// Auto-set asset_type_id saat category berubah
watch(() => form.category_id, (newCatId) => {
    const cat = props.categories?.find(c => c.id === newCatId);
    if (cat && cat.types.length > 0) {
        form.asset_type_id = cat.types[0].id;
    } else {
        form.asset_type_id = null;
    }
    // Reset detail & fasilitas
    form.detail = {};
    form.facility_ids = [];
    form.units = [makeEmptyUnit()];
});

// Load detail jenis aset setiap kali asset_type_id berubah
watch(() => form.asset_type_id, (newTypeId) => {
    fetchAssetTypeDetails(newTypeId);
    form.detail = {};
    form.facility_ids = [];
    form.units = [makeEmptyUnit()];
    form.photos = [{
        _id: Date.now(),
        gallery_category_id: null,
        files: [],
        previews: [],
    }];
});

// Load initial asset type details on mount
onMounted(() => {
    if (form.asset_type_id) {
        fetchAssetTypeDetails(form.asset_type_id);
    }
    window.addEventListener('click', handleClickOutsideFasilitas);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutsideFasilitas);
});

// Navigation State
const currentStep = ref(1);
const showSuccessModal = ref(false);

// Fasilitas dropdown state
const fasilitasDropdownOpen = ref(false);
const fasilitasDropdownRef = ref(null);

const toggleFasilitasDropdown = () => {
    fasilitasDropdownOpen.value = !fasilitasDropdownOpen.value;
};

const handleClickOutsideFasilitas = (e) => {
    if (fasilitasDropdownRef.value && !fasilitasDropdownRef.value.contains(e.target)) {
        fasilitasDropdownOpen.value = false;
    }
};

const toggleFasilitas = (facilityId) => {
    const index = form.facility_ids.indexOf(facilityId);
    if (index === -1) {
        form.facility_ids.push(facilityId);
    } else {
        form.facility_ids.splice(index, 1);
    }
};

// Unit management
const tambahUnit = () => {
    form.units.push(makeEmptyUnit());
};

const hapusUnit = (index) => {
    if (form.units.length > 1) {
        form.units.splice(index, 1);
    }
};

const toggleUnitFasilitas = (unitIndex, facilityId) => {
    const unit = form.units[unitIndex];
    const idx = unit.facility_ids.indexOf(facilityId);
    if (idx === -1) {
        unit.facility_ids.push(facilityId);
    } else {
        unit.facility_ids.splice(idx, 1);
    }
};

// Foto management
const tambahKategoriFoto = () => {
    form.photos.push({
        _id: Date.now(),
        gallery_category_id: null,
        files: [],
        previews: [],
    });
};

const hapusKategoriFoto = (index) => {
    form.photos[index].previews.forEach(url => URL.revokeObjectURL(url));
    form.photos.splice(index, 1);
};

const handleFileUpload = (event, index) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        form.photos[index].files.push(file);
        form.photos[index].previews.push(URL.createObjectURL(file));
    });
    event.target.value = null;
};

const hapusFoto = (catIndex, fileIndex) => {
    URL.revokeObjectURL(form.photos[catIndex].previews[fileIndex]);
    form.photos[catIndex].files.splice(fileIndex, 1);
    form.photos[catIndex].previews.splice(fileIndex, 1);
};

// --- NAVIGASI ---
const nextStep = () => {
    if (currentStep.value < 3) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

// --- SUBMIT ---
const validationErrors = ref({});
const showValidationAlert = ref(false);

const validateStep1 = () => {
    const errors = {};
    if (!form.title.trim()) errors.title = 'Nama aset wajib diisi.';
    if (!form.description.trim() || form.description.trim().length < 20)
        errors.description = 'Deskripsi wajib diisi (minimal 20 karakter).';
    if (!form.category_id) errors.category_id = 'Kategori wajib dipilih.';
    if (!form.asset_type_id) errors.asset_type_id = 'Jenis aset wajib dipilih.';
    return errors;
};

const validateStep2 = () => {
    const errors = {};
    if (!form.address.trim()) errors.address = 'Alamat lengkap wajib diisi.';
    if (!form.province_code) errors.province_code = 'Provinsi wajib dipilih.';
    if (!form.city_code) errors.city_code = 'Kota wajib dipilih.';
    if (!form.district_code) errors.district_code = 'Kecamatan wajib dipilih.';
    if (!form.village_code) errors.village_code = 'Kelurahan/Desa wajib dipilih.';
    if (!form.latitude || !form.longitude) errors.latitude = 'Titik lokasi di peta wajib ditentukan.';
    return errors;
};

const validateStep3 = () => {
    const errors = {};
    if (!allowUnits.value) {
        if (!form.price || Number(form.price) <= 0)
            errors.price = 'Harga sewa wajib diisi.';
    } else {
        form.units.forEach((unit, i) => {
            if (!unit.name.trim()) errors[`units.${i}.name`] = 'Nama unit wajib diisi.';
            if (!unit.quantity || Number(unit.quantity) < 1) errors[`units.${i}.quantity`] = 'Jumlah unit wajib diisi.';
            if (!unit.price || Number(unit.price) <= 0) errors[`units.${i}.price`] = 'Harga unit wajib diisi.';
        });
    }
    return errors;
};

const submitProperty = () => {
    showValidationAlert.value = false;
    validationErrors.value = {};

    // Client-side validation per step
    const errS1 = validateStep1();
    if (Object.keys(errS1).length > 0) {
        validationErrors.value = errS1;
        showValidationAlert.value = true;
        currentStep.value = 1;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    const errS2 = validateStep2();
    if (Object.keys(errS2).length > 0) {
        validationErrors.value = errS2;
        showValidationAlert.value = true;
        currentStep.value = 2;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    const errS3 = validateStep3();
    if (Object.keys(errS3).length > 0) {
        validationErrors.value = errS3;
        showValidationAlert.value = true;
        currentStep.value = 3;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    // Kirim sebagai FormData karena ada file upload
    // Inertia useForm menangani multipart secara otomatis
    form.transform((data) => {
        // Hapus field yang tidak relevan
        const payload = { ...data };
        if (allowUnits.value) {
            delete payload.price;
            delete payload.facility_ids;
        } else {
            delete payload.units;
        }
        return payload;
    }).post(route('owner.asset.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
        },
        onError: (errors) => {
            showValidationAlert.value = true;
            validationErrors.value = errors;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });
};

const closeModalAndRedirect = () => {
    showSuccessModal.value = false;
    router.visit(route('owner.asset.index'));
};
</script>

<template>
    <DashboardLayout 
        title="Ajukan Aset Baru" 
        description="Lengkapi data aset Anda agar calon penyewa bisa melihat unit Anda di platform kusewa." 
        role="Owner"
        :breadcrumbs="[{ label: 'Aset', route: '/owner/asset' }, { label: 'Ajukan Aset Baru' }]"
    >
        <Head title="Ajukan Aset Baru" />

        <div class="pb-32 font-sans text-[#0A2540]">
            <div class="max-w-6xl mx-auto py-2">

                <div class="flex flex-col lg:flex-row gap-12 lg:gap-16 items-start">

                    <!-- SIDEBAR NAV (Sticky) -->
                    <div class="w-full lg:w-[260px] shrink-0 lg:sticky lg:top-32 hidden md:block">
                        <div class="flex flex-col relative before:absolute before:left-5 before:top-4 before:bottom-4 before:w-[2px] before:bg-slate-200 before:-z-10">

                            <div class="flex items-start gap-4 mb-8 relative z-10 transition-colors">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 ring-4 ring-[#F8F9FA]"
                                     :class="currentStep === 1 ? 'bg-[#0A2540] text-white shadow-md shadow-[#0A2540]/20' : currentStep > 1 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-white border-2 border-slate-200 text-slate-400'">
                                    <i v-if="currentStep > 1" class="fa-solid fa-check text-sm"></i>
                                    <span v-else class="font-bold">1</span>
                                </div>
                                <div class="mt-0.5">
                                    <h3 class="text-[15px] transition-colors duration-300" :class="currentStep === 1 ? 'font-bold text-[#0A2540]' : 'font-semibold text-[#0A2540]/80'">Informasi Utama</h3>
                                    <p class="text-[13px] mt-0.5 transition-colors duration-300" :class="currentStep === 1 ? 'text-[#0A2540]/70' : 'text-slate-400'">Informasi Dasar Aset</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 mb-8 relative z-10 transition-colors">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 ring-4 ring-[#F8F9FA]"
                                     :class="currentStep === 2 ? 'bg-[#0A2540] text-white shadow-md shadow-[#0A2540]/20' : currentStep > 2 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-white border-2 border-slate-200 text-slate-400'">
                                    <i v-if="currentStep > 2" class="fa-solid fa-check text-sm"></i>
                                    <span v-else class="font-bold">2</span>
                                </div>
                                <div class="mt-0.5">
                                    <h3 class="text-[15px] transition-colors duration-300" :class="currentStep === 2 ? 'font-bold text-[#0A2540]' : 'font-semibold text-[#0A2540]/80'">Lokasi</h3>
                                    <p class="text-[13px] mt-0.5 transition-colors duration-300" :class="currentStep === 2 ? 'text-[#0A2540]/70' : 'text-slate-400'">Alamat Lengkap Aset</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 mb-8 relative z-10 transition-colors">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 ring-4 ring-[#F8F9FA]"
                                     :class="currentStep === 3 ? 'bg-[#0A2540] text-white shadow-md shadow-[#0A2540]/20' : currentStep > 3 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-white border-2 border-slate-200 text-slate-400'">
                                    <i v-if="currentStep > 3" class="fa-solid fa-check text-sm"></i>
                                    <span v-else class="font-bold">3</span>
                                </div>
                                <div class="mt-0.5">
                                    <h3 class="text-[15px] transition-colors duration-300" :class="currentStep === 3 ? 'font-bold text-[#0A2540]' : 'font-semibold text-[#0A2540]/80'">Harga & Foto</h3>
                                    <p class="text-[13px] mt-0.5 transition-colors duration-300" :class="currentStep === 3 ? 'text-[#0A2540]/70' : 'text-slate-400'">Harga Sewa & Galeri</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Horizontal Stepper for Mobile -->
                    <div class="w-full md:hidden mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200/50 flex justify-between items-center relative">
                        <div class="absolute top-1/2 left-8 right-8 h-[2px] bg-slate-100 -translate-y-1/2 -z-0"></div>

                        <div class="relative z-10 bg-white px-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors shadow-sm ring-4 ring-white"
                                 :class="currentStep === 1 ? 'bg-[#0A2540] text-white' : currentStep > 1 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-[#F8F9FA] border-2 border-slate-200/50 text-slate-400'">
                                <i v-if="currentStep > 1" class="fa-solid fa-check text-[10px]"></i>
                                <span v-else>1</span>
                            </div>
                        </div>

                        <div class="relative z-10 bg-white px-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors shadow-sm ring-4 ring-white"
                                 :class="currentStep === 2 ? 'bg-[#0A2540] text-white' : currentStep > 2 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-[#F8F9FA] border-2 border-slate-200/50 text-slate-400'">
                                <i v-if="currentStep > 2" class="fa-solid fa-check text-[10px]"></i>
                                <span v-else>2</span>
                            </div>
                        </div>

                        <div class="relative z-10 bg-white px-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors shadow-sm ring-4 ring-white"
                                 :class="currentStep === 3 ? 'bg-[#0A2540] text-white' : currentStep > 3 ? 'bg-[#FFC000] text-[#0A2540]' : 'bg-[#F8F9FA] border-2 border-slate-200/50 text-slate-400'">
                                <i v-if="currentStep > 3" class="fa-solid fa-check text-[10px]"></i>
                                <span v-else>3</span>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENT AREA -->
                    <div class="flex-grow w-full max-w-[720px]">

                        <!-- VALIDATION ALERT -->
                        <div v-if="showValidationAlert && Object.keys(validationErrors).length > 0"
                             class="mb-4 p-4 bg-rose-50 border border-rose-200 rounded-2xl">
                            <p class="text-xs font-bold text-rose-700 mb-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                Terdapat kesalahan pada formulir:
                            </p>
                            <ul class="list-disc list-inside text-xs text-rose-600 space-y-0.5">
                                <li v-for="(msg, key) in validationErrors" :key="key">{{ msg }}</li>
                            </ul>
                        </div>

                        <!-- LOADING TYPE DETAILS -->
                        <div v-if="isLoadingTypeDetails && currentStep === 1" class="mb-3 flex items-center gap-2 text-xs text-slate-500">
                            <svg class="animate-spin h-3 w-3 text-[#0A2540]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memuat data jenis aset...
                        </div>

            <!-- FORM CARD -->
            <form @submit.prevent="submitProperty" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">

                <!-- STEP 1 -->
                <Step1
                    v-show="currentStep === 1"
                    :form="form"
                    :categories="categories"
                    :availableTypes="availableTypes"
                    :assetTypeDetails="assetTypeDetails"
                    :allowUnits="allowUnits"
                    @tambahUnit="tambahUnit"
                    @hapusUnit="hapusUnit"
                    @toggleUnitFasilitas="toggleUnitFasilitas"
                    @toggleFasilitas="toggleFasilitas"
                />

                <!-- STEP 2 -->
                <Step2 v-show="currentStep === 2" :form="form" :currentStep="currentStep" />

                <!-- STEP 3 -->
                <Step3
                    v-show="currentStep === 3"
                    :form="form"
                    :allowUnits="allowUnits"
                    :assetTypeDetails="assetTypeDetails"
                    @tambahKategoriFoto="tambahKategoriFoto"
                    @hapusKategoriFoto="hapusKategoriFoto"
                    @handleFileUpload="handleFileUpload"
                    @hapusFoto="hapusFoto"
                />

                    </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- STICKY BOTTOM ACTION BAR (Mobile) -->
        <DetailBottomBar class="md:hidden"
            :buttonText="currentStep < 3 ? 'Selanjutnya' : 'Selesaikan'"
            @submit="currentStep < 3 ? nextStep() : submitProperty()"
            :disabled="form.processing">

            <template #left-content>
                <button type="button" @click="prevStep" class="h-[48px] px-6 rounded-[12px] border border-muted/30 text-secondary font-semibold text-[14px] hover:bg-background transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </button>
            </template>
        </DetailBottomBar>

        <!-- STICKY BOTTOM ACTION BAR (Desktop) -->
        <div class="fixed bottom-0 left-0 lg:left-[260px] right-0 bg-white border-t border-muted/20 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] z-40 hidden md:block">
            <div class="max-w-6xl mx-auto px-6 lg:px-8 h-20 flex items-center justify-between">
                <div>
                    <button type="button" @click="prevStep" class="h-[48px] px-6 rounded-[12px] border border-muted/30 text-[#0A2540] font-semibold text-[14px] hover:bg-slate-50 transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Kembali
                    </button>
                </div>

                <div>
                    <button v-if="currentStep < 3" type="button" @click="nextStep" :disabled="form.processing" class="h-[48px] px-8 rounded-[12px] bg-[#0A2540] text-white font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                    <button v-else type="button" @click="submitProperty" :disabled="form.processing" class="h-[48px] px-8 rounded-[12px] bg-[#FFC000] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-1 h-4 w-4 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        {{ form.processing ? 'Memproses...' : 'Kirim Pengajuan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- POP-UP SUCCESS MODAL -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full text-center shadow-2xl border border-slate-100 space-y-5 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#0A2540] via-[#FFC000] to-[#0A2540]"></div>

                        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-[#FFC000] flex items-center justify-center mx-auto text-2xl relative shadow-inner">
                            <i class="fa-solid fa-paper-plane text-[#0A2540]"></i>
                            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
                            </span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Pengajuan Berhasil Dikirim!</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Data aset Anda sudah masuk ke sistem dan saat ini sedang dalam proses review admin.</p>
                        </div>

                        <div class="bg-amber-50/60 border border-amber-200/80 rounded-2xl p-3.5 flex items-center gap-3 text-left">
                            <div class="w-8 h-8 rounded-xl bg-[#FFC000]/20 text-[#0A2540] flex items-center justify-center shrink-0 text-sm">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Status Pengajuan</span>
                                <span class="text-xs font-black text-[#0A2540]">Menunggu Persetujuan Admin</span>
                            </div>
                        </div>

                        <button type="button" @click="closeModalAndRedirect" class="w-full bg-[#0A2540] text-white font-bold text-xs py-3.5 rounded-xl hover:bg-[#123e6b] transition cursor-pointer">
                            Kembali ke Halaman Properti
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </DashboardLayout>
</template>
