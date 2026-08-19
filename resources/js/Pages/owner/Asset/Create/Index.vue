<script setup>
import { Send, History } from 'lucide-vue-next';
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import Step1 from './Step1.vue';
import Step2 from './Step2.vue';
import Step3 from './Step3.vue';
import Step4 from './Step4.vue';
import Step5 from './Step5.vue';
import Step6 from './Step6.vue';
import Step7 from './Step7.vue';

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
    draftData: Object,
    draftId: Number,
});

// --- DATA DINAMIS dari API ---
const assetTypeDetails = ref(null);
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

const allowUnits = computed(() => assetTypeDetails.value?.allow_units ?? false);

const availableTypes = computed(() => {
    if (!form.category_id) return [];
    const cat = props.categories?.find(c => c.id === form.category_id);
    return cat ? cat.types : [];
});

const makeEmptyUnit = () => ({
    _id: Date.now() + Math.random(),
    name: '',
    quantity: 1,
    pricings: [{ _id: Date.now(), duration: 1, rental_unit: 'month', price: '' }],
    detail: {},
    facility_ids: [],
    thumbnail: null,
    thumbnail_preview: null,
    photos: [{ _id: Date.now(), gallery_category_id: null, files: [], previews: [] }],
});

const form = useForm(props.draftData ? { ...props.draftData, draft_id: props.draftId } : {
    draft_id: props.draftId ?? null,
    title: '',
    description: '',
    category_id: props.categories?.[0]?.id ?? null,
    asset_type_id: props.categories?.[0]?.types?.[0]?.id ?? null,
    detail: {},
    facility_ids: [],
    units: [makeEmptyUnit()],
    address: '',
    province_code: '',
    city_code: '',
    district_code: '',
    village_code: '',
    postal_code: '',
    latitude: '',
    longitude: '',
    pricings: [{ _id: Date.now(), duration: 1, rental_unit: 'month', price: '' }],
    thumbnail: null,
    thumbnail_preview: null,
    photos: [{
        _id: Date.now(),
        gallery_category_id: null,
        files: [],
        previews: [],
    }],
    faqs: [],
    policies: [],
});

watch(() => form.category_id, (newCatId, oldCatId) => {
    if (props.draftData && !oldCatId) return; // Mencegah reset saat resume draft
    const cat = props.categories?.find(c => c.id === newCatId);
    if (cat && cat.types.length > 0) {
        form.asset_type_id = cat.types[0].id;
    } else {
        form.asset_type_id = null;
    }
    form.detail = {};
    form.facility_ids = [];
    form.units = [makeEmptyUnit()];
});

watch(() => form.asset_type_id, (newTypeId, oldTypeId) => {
    fetchAssetTypeDetails(newTypeId);
    if (props.draftData && !oldTypeId) return; // Mencegah reset saat resume draft
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

onMounted(() => {
    if (form.asset_type_id) {
        fetchAssetTypeDetails(form.asset_type_id);
    }
    window.addEventListener('click', handleClickOutsideFasilitas);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutsideFasilitas);
});

const currentStep = ref(1);
const showSuccessModal = ref(false);

const steps = computed(() => {
    if (allowUnits.value) {
        return [
            { id: 1, title: 'Informasi Utama', component: 'Step1' },
            { id: 2, title: 'Lokasi', component: 'Step2' },
            { id: 3, title: 'Detail & Fasilitas', component: 'Step3' },
            { id: 4, title: 'Tipe Unit', component: 'Step4' },
            { id: 5, title: 'Harga Sewa', component: 'Step5' },
            { id: 6, title: 'Galeri Foto', component: 'Step6' },
            { id: 7, title: 'Kebijakan & FAQ', component: 'Step7' },
        ];
    } else {
        return [
            { id: 1, title: 'Informasi Utama', component: 'Step1' },
            { id: 2, title: 'Lokasi', component: 'Step2' },
            { id: 3, title: 'Detail & Fasilitas', component: 'Step3' },
            { id: 4, title: 'Harga Sewa', component: 'Step5' },
            { id: 5, title: 'Galeri Foto', component: 'Step6' },
            { id: 6, title: 'Kebijakan & FAQ', component: 'Step7' },
        ];
    }
});

const subMenuSteps = computed(() => {
    return steps.value.map((step, index) => {
        const isActive = currentStep.value === (index + 1);
        const isCompleted = currentStep.value > (index + 1);
        return {
            key: `step-${index}`,
            label: step.title,
            icon: isCompleted ? 'fa-solid fa-check-circle text-emerald-500' : (isActive ? 'fa-solid fa-circle-dot text-[#FFC000]' : 'fa-regular fa-circle text-slate-300'),
            active: isActive,
            onClick: () => {
                // Di mode draft, kita bisa pindah step asalkan tervalidasi
                if (index + 1 < currentStep.value) {
                    currentStep.value = index + 1;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        };
    });
});

const fasilitasDropdownOpen = ref(false);
const fasilitasDropdownRef = ref(null);

const isStepTitleVisible = (index) => {
    const stepNum = index + 1;
    const total = steps.value.length;
    if (currentStep.value === 1) return stepNum <= 3;
    if (currentStep.value === total) return stepNum >= total - 2;
    return stepNum >= currentStep.value - 1 && stepNum <= currentStep.value + 1;
};

const toggleFasilitasDropdown = () => { fasilitasDropdownOpen.value = !fasilitasDropdownOpen.value; };
const handleClickOutsideFasilitas = (e) => { if (fasilitasDropdownRef.value && !fasilitasDropdownRef.value.contains(e.target)) fasilitasDropdownOpen.value = false; };
const toggleFasilitas = (facilityId) => {
    const index = form.facility_ids.indexOf(facilityId);
    if (index === -1) form.facility_ids.push(facilityId);
    else form.facility_ids.splice(index, 1);
};

const tambahUnit = () => form.units.push(makeEmptyUnit());
const hapusUnit = (index) => { if (form.units.length > 1) form.units.splice(index, 1); };
const toggleUnitFasilitas = (unitIndex, facilityId) => {
    const unit = form.units[unitIndex];
    const idx = unit.facility_ids.indexOf(facilityId);
    if (idx === -1) unit.facility_ids.push(facilityId);
    else unit.facility_ids.splice(idx, 1);
};

const tambahUnitKategoriFoto = (unitIndex) => {
    form.units[unitIndex].photos.push({ _id: Date.now(), gallery_category_id: null, files: [], previews: [] });
};
const hapusUnitKategoriFoto = (unitIndex, photoIndex) => {
    form.units[unitIndex].photos[photoIndex].previews.forEach(url => URL.revokeObjectURL(url));
    form.units[unitIndex].photos.splice(photoIndex, 1);
};

const handleUnitFileUpload = async (event, unitIndex, photoIndex) => {
    const files = Array.from(event.target.files);
    for (const file of files) {
        const previewUrl = URL.createObjectURL(file);
        form.units[unitIndex].photos[photoIndex].previews.push(previewUrl);
        
        // Asynchronous Upload for Draft
        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await fetch(route('owner.asset.upload-temp'), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: formData
            });
            const data = await res.json();
            form.units[unitIndex].photos[photoIndex].files.push(data.path);
        } catch (e) { console.error(e); }
    }
    event.target.value = null;
};
const hapusUnitFoto = (unitIndex, photoIndex, fileIndex) => {
    URL.revokeObjectURL(form.units[unitIndex].photos[photoIndex].previews[fileIndex]);
    form.units[unitIndex].photos[photoIndex].files.splice(fileIndex, 1);
    form.units[unitIndex].photos[photoIndex].previews.splice(fileIndex, 1);
};

const handleUnitThumbnailUpload = async (event, unitIndex) => {
    const file = event.target.files[0];
    if (file) {
        if (form.units[unitIndex].thumbnail_preview) URL.revokeObjectURL(form.units[unitIndex].thumbnail_preview);
        form.units[unitIndex].thumbnail_preview = URL.createObjectURL(file);
        
        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await fetch(route('owner.asset.upload-temp'), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: formData
            });
            const data = await res.json();
            form.units[unitIndex].thumbnail = data.path;
        } catch (e) { console.error(e); }
    }
    event.target.value = null;
};
const hapusUnitThumbnail = (unitIndex) => {
    if (form.units[unitIndex].thumbnail_preview) URL.revokeObjectURL(form.units[unitIndex].thumbnail_preview);
    form.units[unitIndex].thumbnail = null;
    form.units[unitIndex].thumbnail_preview = null;
};

const tambahKategoriFoto = () => {
    form.photos.push({ _id: Date.now(), gallery_category_id: null, files: [], previews: [] });
};
const hapusKategoriFoto = (index) => {
    form.photos[index].previews.forEach(url => URL.revokeObjectURL(url));
    form.photos.splice(index, 1);
};

const handleFileUpload = async (event, index) => {
    const files = Array.from(event.target.files);
    for (const file of files) {
        const previewUrl = URL.createObjectURL(file);
        form.photos[index].previews.push(previewUrl);
        
        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await fetch(route('owner.asset.upload-temp'), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: formData
            });
            const data = await res.json();
            form.photos[index].files.push(data.path);
        } catch (e) { console.error(e); }
    }
    event.target.value = null;
};
const hapusFoto = (catIndex, fileIndex) => {
    URL.revokeObjectURL(form.photos[catIndex].previews[fileIndex]);
    form.photos[catIndex].files.splice(fileIndex, 1);
    form.photos[catIndex].previews.splice(fileIndex, 1);
};

const handleThumbnailUpload = async (event) => {
    const file = event.target.files[0];
    if (file) {
        if (form.thumbnail_preview) URL.revokeObjectURL(form.thumbnail_preview);
        form.thumbnail_preview = URL.createObjectURL(file);
        
        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await fetch(route('owner.asset.upload-temp'), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: formData
            });
            const data = await res.json();
            form.thumbnail = data.path;
        } catch (e) { console.error(e); }
    }
    event.target.value = null;
};
const hapusThumbnail = () => {
    if (form.thumbnail_preview) URL.revokeObjectURL(form.thumbnail_preview);
    form.thumbnail = null;
    form.thumbnail_preview = null;
};

// --- VALIDATION AND AUTO-SAVE ---
const validationErrors = ref({});
const showValidationAlert = ref(false);
const isSavingDraft = ref(false);

const saveDraft = async () => {
    try {
        isSavingDraft.value = true;
        const payload = JSON.parse(JSON.stringify(form)); // Remove Vue reactiveness
        const res = await fetch(route('owner.asset.auto-save'), {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.draft_id && !form.draft_id) {
            form.draft_id = data.draft_id;
        }
    } catch (e) {
        console.error("Gagal menyimpan draft:", e);
    } finally {
        isSavingDraft.value = false;
    }
};

const nextStep = async () => {
    showValidationAlert.value = false;
    validationErrors.value = {};
    let err = {};

    if (currentStep.value === 1) err = validateStep1();
    else if (currentStep.value === 2) err = validateStep2();
    else if (currentStep.value === 3) err = validateStep3();
    else if (currentStep.value === 4) err = validateStep4();
    else if (currentStep.value === 5) err = validateStep5();
    else if (currentStep.value === 6) err = validateStep6();

    if (Object.keys(err).length > 0) {
        validationErrors.value = err;
        showValidationAlert.value = true;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    // Lolos validasi, save draft & next step
    await saveDraft();

    if (currentStep.value < steps.value.length) {
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const addFaq = () => form.faqs.push({ question: '', answer: '' });
const removeFaq = (idx) => form.faqs.splice(idx, 1);
const addPolicy = () => form.policies.push({ title: '', description: '' });
const removePolicy = (idx) => form.policies.splice(idx, 1);

const validateStep1 = () => {
    const errors = {};
    if (!form.title?.trim()) errors.title = 'Nama aset wajib diisi.';
    if (!form.description?.trim() || form.description.trim().length < 100)
        errors.description = 'Deskripsi wajib diisi (minimal 100 karakter).';
    if (!form.category_id) errors.category_id = 'Kategori wajib dipilih.';
    if (!form.asset_type_id) errors.asset_type_id = 'Jenis aset wajib dipilih.';
    return errors;
};

const validateStep2 = () => {
    const errors = {};
    if (!form.address?.trim()) errors.address = 'Alamat lengkap wajib diisi.';
    if (!form.province_code) errors.province_code = 'Provinsi wajib dipilih.';
    if (!form.city_code) errors.city_code = 'Kota wajib dipilih.';
    if (!form.district_code) errors.district_code = 'Kecamatan wajib dipilih.';
    if (!form.village_code) errors.village_code = 'Kelurahan/Desa wajib dipilih.';
    if (!form.latitude || !form.longitude) errors.latitude = 'Titik lokasi di peta wajib ditentukan.';
    return errors;
};

const validateStep3 = () => {
    const errors = {};
    return errors;
};

const validateStep4 = () => { // Jika units, maka Step4 = Tipe Unit. Jika tidak, maka Harga Sewa (Step5 component)
    const errors = {};
    if (steps.value[3].component === 'Step4') {
        form.units.forEach((unit, i) => {
            if (!unit.name?.trim()) errors[`units.${i}.name`] = 'Nama unit wajib diisi.';
            if (!unit.quantity || Number(unit.quantity) < 1) errors[`units.${i}.quantity`] = 'Jumlah unit wajib diisi.';
        });
    } else if (steps.value[3].component === 'Step5') {
        if (!form.pricings || form.pricings.length === 0) {
            errors.pricings = 'Paket harga sewa wajib diisi.';
        } else {
            form.pricings.forEach((p, i) => {
                if (!p.price || Number(p.price) <= 0) errors[`pricings.${i}.price`] = 'Harga sewa wajib diisi.';
            });
        }
    }
    return errors;
};

const validateStep5 = () => { // Jika units, maka Step5 = Harga Sewa. Jika tidak, maka Step6 component (Galeri Foto)
    const errors = {};
    if (steps.value[4].component === 'Step5') { // allowUnits = true
        form.units.forEach((unit, i) => {
            if (!unit.pricings || unit.pricings.length === 0) {
                errors[`units.${i}.pricings`] = 'Paket harga unit wajib diisi.';
            } else {
                unit.pricings.forEach((p, pIdx) => {
                    if (!p.price || Number(p.price) <= 0) errors[`units.${i}.pricings.${pIdx}.price`] = 'Harga unit wajib diisi.';
                });
            }
        });
    } else if (steps.value[4].component === 'Step6') {
        if (!form.thumbnail) errors.thumbnail = 'Foto sampul utama wajib diunggah.';
    }
    return errors;
};

const validateStep6 = () => { // Jika units, maka Step6 = Galeri Foto
    const errors = {};
    if (steps.value[5].component === 'Step6') {
        if (!form.thumbnail) errors.thumbnail = 'Foto sampul utama wajib diunggah.';
    }
    return errors;
};

const validateStep7 = () => { // Kebijakan & FAQ (Optional)
    return {};
};

const submitProperty = async () => {
    showValidationAlert.value = false;
    validationErrors.value = {};

    // Hanya validasi step terakhir saat submit
    const err = steps.value.length === 6 ? validateStep6() : validateStep7(); // asumsikan step terakhir

    if (Object.keys(err).length > 0) {
        validationErrors.value = err;
        showValidationAlert.value = true;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    // Auto save draft for the last time before submitting (optional, but good for safety)
    await saveDraft();

    form.transform((data) => {
        const payload = { ...data };
        
        // Bersihkan data yang tidak perlu sebelum dikirim
        if (payload.units) {
            payload.units.forEach(u => {
                delete u._id;
                delete u.thumbnail_preview;
                if (u.pricings) u.pricings.forEach(p => delete p._id);
                if (u.photos) u.photos.forEach(p => {
                    delete p._id;
                    delete p.previews;
                });
            });
        }
        
        if (payload.photos) {
            payload.photos.forEach(p => {
                delete p._id;
                delete p.previews;
            });
        }
        if (payload.pricings) {
            payload.pricings.forEach(p => delete p._id);
        }
        delete payload.thumbnail_preview;

        // Bersihkan FAQ dan Policy kosong
        if (payload.faqs) {
            payload.faqs = payload.faqs.filter(f => f.question?.trim() && f.answer?.trim());
        }
        if (payload.policies) {
            payload.policies = payload.policies.filter(p => p.title?.trim());
        }

        if (allowUnits.value) {
            delete payload.price;
            delete payload.facility_ids;

            // Hapus grup foto unit yang tidak ada filenya, dan bersihkan thumbnail_preview
            if (payload.units) {
                payload.units = payload.units.map(unit => {
                    const u = {
                        ...unit,
                        photos: unit.photos ? unit.photos.filter(p => p.files && p.files.length > 0) : []
                    };
                    delete u.thumbnail_preview;
                    return u;
                });
            }
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
        :subMenu="subMenuSteps"
        subMenuParentRouteName="owner.asset.*"
    >
        <Head title="Ajukan Aset Baru" />

        <div class="pb-32 font-sans text-[#0A2540]">
            <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6 lg:gap-8">

                <!-- LEFT SIDEBAR: VERTICAL STEPPER (Dihapus karena sudah pindah ke subMenu global) -->
                <div class="md:hidden shrink-0">
                    <!-- MOBILE HORIZONTAL STEPPER -->
                    <div class="bg-white rounded-lg p-4 border border-slate-200/80 shadow-sm mb-2">
                        <div class="flex items-center w-full relative">
                            <div v-for="(step, index) in steps" :key="step.id" class="flex-1 flex flex-col items-center relative group">
                                <div class="flex items-center gap-2 mb-2 relative z-10 bg-white px-2">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors shrink-0"
                                         :class="currentStep > (index + 1) ? 'bg-[#FFC000] text-[#0A2540]' : currentStep === (index + 1) ? 'border-2 border-[#FFC000] text-[#0A2540]' : 'border-2 border-slate-300 text-slate-400'">
                                        <i v-if="currentStep > (index + 1)" class="fa-solid fa-check text-[10px]"></i>
                                        <span v-else>{{ index + 1 }}</span>
                                    </div>
                                    <span v-if="isStepTitleVisible(index)" class="text-xs font-semibold whitespace-nowrap transition-colors"
                                          :class="currentStep >= (index + 1) ? 'text-[#0A2540]' : 'text-slate-400'">
                                        {{ step.title }}
                                    </span>
                                </div>
                                <div class="w-full h-0.5 transition-colors"
                                     :class="currentStep > index ? 'bg-[#FFC000]' : 'bg-slate-100'"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CONTENT AREA -->
                <div class="flex-1 min-w-0">

                    <!-- VALIDATION ALERT -->
                    <div v-if="showValidationAlert && Object.keys(validationErrors).length > 0"
                         class="mb-4 p-4 bg-rose-50 border border-rose-200 rounded-lg">
                        <p class="text-sm font-bold text-rose-700 mb-2 flex items-center gap-1.5">
                            Terdapat kesalahan pada formulir:
                        </p>
                        <ul class="list-disc list-inside text-sm text-rose-600 space-y-1">
                            <li v-for="(msg, key) in validationErrors" :key="key">{{ msg }}</li>
                        </ul>
                    </div>

                    <!-- LOADING TYPE DETAILS -->
                    <div v-if="isLoadingTypeDetails && currentStep === 1" class="mb-4 flex items-center gap-3 text-sm font-semibold text-slate-500 bg-white p-4 rounded-lg border border-slate-200/80 shadow-sm">
                        <svg class="animate-spin h-5 w-5 text-[#0A2540]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Memuat spesifikasi aset...
                    </div>

                    <!-- FORM CARD -->
            <form @submit.prevent="submitProperty" class="bg-white rounded-lg p-6 md:p-8 border border-slate-200/80 shadow-sm space-y-6 relative">

                <!-- LOADING SKELETON OVERLAY -->
                <div v-if="isSavingDraft" class="absolute inset-0 z-50 bg-white/70 backdrop-blur-[2px] flex flex-col p-6 md:p-8 space-y-6 pointer-events-none transition-all duration-300 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-bold text-slate-600">Menyimpan Draft...</span>
                    </div>
                    <div class="h-8 bg-slate-200/60 rounded w-1/3 animate-pulse"></div>
                    <div class="space-y-4">
                        <div class="h-12 bg-slate-200/60 rounded w-full animate-pulse"></div>
                        <div class="h-12 bg-slate-200/60 rounded w-5/6 animate-pulse"></div>
                        <div class="h-32 bg-slate-200/60 rounded w-full animate-pulse"></div>
                        <div class="h-12 bg-slate-200/60 rounded w-4/6 animate-pulse"></div>
                    </div>
                </div>

                <!-- STEP 1 -->
                <Step1
                    v-show="steps[currentStep - 1]?.component === 'Step1'"
                    :form="form"
                    :categories="categories"
                    :availableTypes="availableTypes"
                    :assetTypeDetails="assetTypeDetails"
                    :allowUnits="allowUnits"
                />

                <Step2 v-show="steps[currentStep - 1]?.component === 'Step2'" :form="form" :currentStep="currentStep" />

                <Step3
                    v-show="steps[currentStep - 1]?.component === 'Step3'"
                    :form="form"
                    :assetTypeDetails="assetTypeDetails"
                    @toggleFasilitas="toggleFasilitas"
                />

                <Step4
                    v-show="steps[currentStep - 1]?.component === 'Step4'"
                    :form="form"
                    :assetTypeDetails="assetTypeDetails"
                    @tambahUnit="tambahUnit"
                    @hapusUnit="hapusUnit"
                    @toggleUnitFasilitas="toggleUnitFasilitas"
                />

                <Step5
                    v-show="steps[currentStep - 1]?.component === 'Step5'"
                    :form="form"
                    :allowUnits="allowUnits"
                    :assetTypeDetails="assetTypeDetails"
                />

                <Step6
                    v-show="steps[currentStep - 1]?.component === 'Step6'"
                    :form="form"
                    :allowUnits="allowUnits"
                    :assetTypeDetails="assetTypeDetails"
                    @tambahKategoriFoto="tambahKategoriFoto"
                    @hapusKategoriFoto="hapusKategoriFoto"
                    @handleFileUpload="handleFileUpload"
                    @hapusFoto="hapusFoto"
                    @handleThumbnailUpload="handleThumbnailUpload"
                    @hapusThumbnail="hapusThumbnail"
                    @tambahUnitKategoriFoto="tambahUnitKategoriFoto"
                    @hapusUnitKategoriFoto="hapusUnitKategoriFoto"
                    @handleUnitFileUpload="handleUnitFileUpload"
                    @hapusUnitFoto="hapusUnitFoto"
                    @handleUnitThumbnailUpload="handleUnitThumbnailUpload"
                    @hapusUnitThumbnail="hapusUnitThumbnail"
                />

                <Step7
                    v-show="steps[currentStep - 1]?.component === 'Step7'"
                    :form="form"
                    @addFaq="addFaq"
                    @removeFaq="removeFaq"
                    @addPolicy="addPolicy"
                    @removePolicy="removePolicy"
                />

                    </form>
                </div>
            </div>
        </div>

        <!-- STICKY BOTTOM ACTION BAR (Mobile) -->
        <DetailBottomBar class="md:hidden"
            :buttonText="currentStep < steps.length ? 'Berikutnya' : 'Selesaikan'"
            @submit="currentStep < steps.length ? nextStep() : submitProperty()"
            :disabled="form.processing">

            <template #left-content>
                <button type="button" @click="prevStep" class="h-[40px] px-4 rounded-md border border-muted/30 text-secondary font-semibold text-[14px] hover:bg-background transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                    Sebelumnya
                </button>
            </template>
            <template #right-content>
                <button v-if="currentStep < steps.length" type="button" @click="nextStep" :disabled="form.processing || isSavingDraft" class="h-[40px] px-6 rounded-md bg-[#F2C94C] text-[#0A2540] font-bold text-[14px] shadow-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg v-if="isSavingDraft" class="animate-spin h-4 w-4 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ isSavingDraft ? 'Memproses' : 'Berikutnya' }}
                </button>
                <button v-else type="button" @click="submitProperty" :disabled="form.processing || isSavingDraft" class="h-[40px] px-6 rounded-md bg-[#FFC000] text-[#0A2540] font-bold text-[14px] shadow-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg v-if="isSavingDraft || form.processing" class="animate-spin h-4 w-4 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Selesaikan
                </button>
            </template>
        </DetailBottomBar>

        <!-- STICKY BOTTOM ACTION BAR (Desktop) -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] z-40 hidden md:block">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div>
                    <button type="button" @click="prevStep" :disabled="isSavingDraft" class="h-[40px] px-6 rounded-md border border-slate-300 text-[#0A2540] font-semibold text-[14px] hover:bg-slate-50 transition-colors bg-white shadow-sm flex items-center gap-2 disabled:opacity-50" :class="currentStep === 1 ? 'invisible' : ''">
                        Sebelumnya
                    </button>
                </div>

                <div>
                    <button v-if="currentStep < steps.length" type="button" @click="nextStep" :disabled="form.processing || isSavingDraft" class="h-[40px] px-8 rounded-md bg-[#F2C94C] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg v-if="isSavingDraft" class="animate-spin -ml-1 mr-1 h-4 w-4 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ isSavingDraft ? 'Memproses...' : 'Berikutnya' }}
                    </button>
                    <button v-else type="button" @click="submitProperty" :disabled="form.processing || isSavingDraft" class="h-[40px] px-8 rounded-md bg-[#FFC000] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-1 h-4 w-4 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
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
                    <div class="bg-white rounded-xl p-6 sm:p-8 max-w-md w-full text-center shadow-2xl border border-slate-100 space-y-5 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#0A2540] via-[#FFC000] to-[#0A2540]"></div>

                        <div class="w-16 h-16 rounded-xl bg-amber-50 text-[#FFC000] flex items-center justify-center mx-auto text-2xl relative shadow-inner">
                            <Send class="text-[#0A2540]" />
                            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
                            </span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight">Pengajuan Berhasil Dikirim!</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">Data aset Anda sudah masuk ke sistem dan saat ini sedang dalam proses review admin.</p>
                        </div>

                        <div class="bg-amber-50/60 border border-amber-200/80 rounded-lg p-4 flex items-center gap-3 text-left">
                            <div class="w-10 h-10 rounded-md bg-[#FFC000]/20 text-[#0A2540] flex items-center justify-center shrink-0 text-lg">
                                <History class="" />
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-amber-800 uppercase tracking-wider block">Status Pengajuan</span>
                                <span class="text-sm font-bold text-[#0A2540]">Menunggu Persetujuan Admin</span>
                            </div>
                        </div>

                        <button type="button" @click="closeModalAndRedirect" class="w-full bg-[#0A2540] text-white font-semibold text-sm py-3.5 rounded-md hover:bg-[#123e6b] transition cursor-pointer">
                            Kembali ke Halaman Properti
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </DashboardLayout>
</template>
