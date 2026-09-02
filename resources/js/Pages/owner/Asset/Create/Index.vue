<script setup>
import { Send, History, ArrowLeft } from 'lucide-vue-next';
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount, onUnmounted } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Toast from '@/Components/ui/Toast.vue';
import DetailBottomBar from '@/Components/ui/DetailBottomBar.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import Step1 from './Step1.vue';
import Step2 from './Step2.vue';
import Step3 from './Step3.vue';
import Step4 from './Step4.vue';
import Step5 from './Step5.vue';
import Step6 from './Step6.vue';
import Step7 from './Step7.vue';
import Step8 from './Step8.vue';
import Step8Unit from './Step8Unit.vue';
import Step9 from './Step9.vue';
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';

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

const page = usePage();

// --- DATA DINAMIS dari API ---
const assetTypeDetails = ref(null);
const isLoadingTypeDetails = ref(false);

const fetchAssetTypeDetails = async (typeId, isReset = false) => {
    if (!typeId) {
        assetTypeDetails.value = null;
        return;
    }
    isLoadingTypeDetails.value = true;
    try {
        const res = await fetch(`/api/asset-type/${typeId}/details`);
        const data = await res.json();
        assetTypeDetails.value = data;

        if (isReset) {
            form.detail = {};
            form.facility_ids = [];
            form.units = [makeEmptyUnit()];

            if (data.mandatory_categories && data.mandatory_categories.length > 0) {
                form.photos = data.mandatory_categories.map(cat => ({
                    _id: Date.now() + Math.random(),
                    gallery_category_id: cat.id,
                    gallery_category_name: cat.name,
                    is_mandatory: true,
                    files: [],
                    previews: [],
                }));
            } else {
                form.photos = [{
                    _id: Date.now(),
                    gallery_category_id: null,
                    is_mandatory: false,
                    files: [],
                    previews: [],
                }];
            }
        }
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
    empty_rooms: 1,
    pricings: [{ _id: Date.now(), duration: 1, rental_unit: 'month', price: '' }],
    detail: {},
    facility_ids: [],
    thumbnail: null,
    thumbnail_preview: null,
    photos: [{ _id: Date.now(), gallery_category_id: null, files: [], previews: [] }],
    is_expanded: true,
});

let safeDraftData = null;
if (props.draftData) {
    try {
        safeDraftData = typeof props.draftData === 'string' ? JSON.parse(props.draftData) : JSON.parse(JSON.stringify(props.draftData));
    } catch(e) { safeDraftData = props.draftData; }

    // Bersihkan field bawaan Inertia yang ikut tersimpan di draft sebelumnya
    const inertiaFields = ['errors', 'isDirty', 'progress', 'hasErrors', 'processing', 'wasSuccessful', '__rememberable', 'recentlySuccessful'];
    inertiaFields.forEach(field => {
        if (field in safeDraftData) delete safeDraftData[field];
    });

    if (Array.isArray(safeDraftData.detail)) safeDraftData.detail = {};
    if (Array.isArray(safeDraftData.units)) {
        safeDraftData.units.forEach(u => {
            if (u.name && u.name.toLowerCase().startsWith('tipe ')) {
                u.name = u.name.substring(5).trim();
            }
            if (Array.isArray(u.detail)) u.detail = {};
            if (u.thumbnail && typeof u.thumbnail === 'string') {
                u.thumbnail_preview = u.thumbnail.startsWith('http') ? u.thumbnail : '/storage/' + u.thumbnail;
            }
            if (Array.isArray(u.photos)) {
                u.photos.forEach(p => {
                    if (Array.isArray(p.files)) {
                        p.previews = p.files.map(f => typeof f === 'string' && f.startsWith('http') ? f : '/storage/' + f);
                    } else {
                        p.previews = [];
                    }
                });
            }
        });
    }

    if (safeDraftData.thumbnail && typeof safeDraftData.thumbnail === 'string') {
        safeDraftData.thumbnail_preview = safeDraftData.thumbnail.startsWith('http') ? safeDraftData.thumbnail : '/storage/' + safeDraftData.thumbnail;
    }
    if (Array.isArray(safeDraftData.photos)) {
        safeDraftData.photos.forEach(p => {
            if (Array.isArray(p.files)) {
                p.previews = p.files.map(f => typeof f === 'string' && f.startsWith('http') ? f : '/storage/' + f);
            } else {
                p.previews = [];
            }
        });
    }
}

const defaultForm = {
    draft_id: props.draftId ?? null,
    title: '',
    description: '',
    category_id: null,
    asset_type_id: null,
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
    location_name: '',
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
    policies: {},
    custom_policies: [],
};

const form = useForm(safeDraftData ? { ...defaultForm, ...safeDraftData, draft_id: props.draftId } : defaultForm);

watch(() => form.asset_type_id, (newTypeId, oldTypeId) => {
    // Sinkronisasi category_id
    for (const cat of (props.categories || [])) {
        if (cat.types?.some(t => t.id === newTypeId)) {
            if (form.category_id !== cat.id) {
                form.category_id = cat.id;
            }
            break;
        }
    }

    const isReset = !(props.draftData && !oldTypeId);

    if (isReset) {
        form.detail = {};
        form.facility_ids = [];
        form.units = [makeEmptyUnit()];
    }

    fetchAssetTypeDetails(newTypeId, isReset);
});

const selectedAssetTypeName = computed(() => {
    for (const cat of (props.categories || [])) {
        const type = cat.types?.find(t => t.id === form.asset_type_id);
        if (type) return type.name;
    }
    return '';
});

const unitLabel = computed(() => {
    const name = selectedAssetTypeName.value?.toLowerCase() || '';
    if (name.includes('hotel')) return 'Kamar';
    if (name.includes('apartemen')) return 'Unit';
    if (name.includes('homestay')) return 'Kamar';
    if (name.includes('guest house')) return 'Kamar';
    if (name.includes('kos')) return 'Kamar';
    if (name.includes('resort')) return 'Kamar';
    if (name.includes('studio')) return 'Ruang';
    return 'Unit';
});


const currentStep = ref(1);
const showSuccessModal = ref(false);

// --- TOAST NOTIFICATION ---
const showDraftToast = ref(false);
let toastTimer = null;
const displayToast = () => {
    if (toastTimer) clearTimeout(toastTimer);
    showDraftToast.value = true;
    toastTimer = setTimeout(() => {
        showDraftToast.value = false;
    }, 3000);
};

// --- LEAVE CONFIRMATION ---
const isSubmittingFinal = ref(false);
const showLeaveModal = ref(false);
const pendingVisitUrl = ref('');
const isConfirmedLeave = ref(false);
let unbindBefore = null;

const handleBeforeUnload = (e) => {
    if (!isSubmittingFinal.value && !showSuccessModal.value && form.draft_id) {
        e.preventDefault();
        e.returnValue = '';
    }
};

onMounted(() => {
    if (form.asset_type_id) {
        fetchAssetTypeDetails(form.asset_type_id);
    }
    window.addEventListener('click', handleClickOutsideFasilitas);

    // Intercept Inertia routing
    unbindBefore = router.on('before', (event) => {
        if (!isSubmittingFinal.value && !isConfirmedLeave.value && !showSuccessModal.value && form.draft_id) {
            event.preventDefault();
            pendingVisitUrl.value = event.detail.visit.url;
            showLeaveModal.value = true;
        }
    });
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutsideFasilitas);
    if (unbindBefore) unbindBefore();
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

const confirmLeave = () => {
    isConfirmedLeave.value = true;
    showLeaveModal.value = false;
    if (pendingVisitUrl.value) {
        router.visit(pendingVisitUrl.value);
    }
};

const cancelLeave = () => {
    showLeaveModal.value = false;
    pendingVisitUrl.value = '';
};

const steps = computed(() => {
    if (allowUnits.value) {
        let baseSteps = [
            { id: 1, title: 'Informasi & Lokasi', component: 'Step1' },
            { id: 2, title: 'Detail & Tipe ' + unitLabel.value, component: 'Step3' },
            { id: 3, title: 'Harga Sewa', component: 'Step5' },
            { id: 4, title: 'Galeri Foto Aset', component: 'Step6' },
        ];

        let nextId = 5;

        form.units.forEach((unit, index) => {
            baseSteps.push({
                id: nextId++,
                title: 'Foto ' + (unit.name ? 'Tipe ' + unit.name : (unitLabel.value + ' ' + (index + 1))),
                component: 'Step7',
                unitIndex: index
            });
        });

        baseSteps.push({
            id: nextId++,
            title: 'Fasilitas Aset',
            component: 'Step8'
        });

        form.units.forEach((unit, index) => {
            baseSteps.push({
                id: nextId++,
                title: 'Fasilitas ' + (unit.name ? 'Tipe ' + unit.name : (unitLabel.value + ' ' + (index + 1))),
                component: 'Step8Unit',
                unitIndex: index
            });
        });

        baseSteps.push({
            id: nextId++,
            title: 'Kebijakan & FAQ',
            component: 'Step9'
        });

        return baseSteps;
    } else {
        return [
            { id: 1, title: 'Informasi & Lokasi', component: 'Step1' },
            { id: 2, title: 'Detail Aset', component: 'Step3' },
            { id: 3, title: 'Harga Sewa', component: 'Step5' },
            { id: 4, title: 'Galeri Foto', component: 'Step6' },
            { id: 5, title: 'Fasilitas Aset', component: 'Step8' },
            { id: 6, title: 'Kebijakan & FAQ', component: 'Step9' },
        ];
    }
});

const isCurrentStepValid = computed(() => {
    const currentStepConfig = steps.value[currentStep.value - 1];
    if (!currentStepConfig) return true;
    const component = currentStepConfig.component;
    if (!component) return true;

    switch (component) {
        case 'Step1':
            return !!(form.title && form.asset_type_id && form.province_code && form.city_code && form.district_code && form.village_code && form.address && form.latitude && form.longitude);
        case 'Step3':
            if (assetTypeDetails.value?.specifications) {
                const requiredSpecs = assetTypeDetails.value.specifications.filter(s => s.is_required);
                for (const spec of requiredSpecs) {
                    if (!form.detail[spec.key]) return false;
                }
            }
            if (allowUnits.value) {
                return form.units.every(u => u.name?.trim() && Number(u.quantity) > 0);
            }
            return true;
        case 'Step5': // Harga Sewa (Kos: Step 5, Non-Kos: Step 4)
            if (allowUnits.value) {
                return form.units.every(u => u.pricings && u.pricings[0] && Number(u.pricings[0].price) > 0 && Number(u.pricings[0].duration) > 0 && u.pricings[0].rental_unit);
            } else {
                return form.pricings && form.pricings[0] && Number(form.pricings[0].price) > 0 && Number(form.pricings[0].duration) > 0 && form.pricings[0].rental_unit;
            }
        case 'Step6': // Galeri
            if (!form.thumbnail) return false;
            if (form.photos) {
                for (const group of form.photos) {
                    if (group.is_mandatory && (!group.files || group.files.length === 0)) return false;
                }
            }
            return true;
        case 'Step7': // Galeri Unit
            if (allowUnits.value) {
                return Object.keys(validateStep7(currentStepConfig.unitIndex)).length === 0;
            }
            return true;
        case 'Step8': // Fasilitas Aset
            return Object.keys(validateStep8()).length === 0;
        case 'Step8Unit': // Fasilitas Unit
            if (allowUnits.value) {
                return Object.keys(validateStep8Unit(currentStepConfig.unitIndex)).length === 0;
            }
            return true;
        case 'Step9': // Kebijakan & FAQ
            return true;
        default:
            return true;
    }
});

const stepTwoTitle = computed(() => {
    if (selectedAssetTypeName.value) {
        return `${unitLabel.value} ${selectedAssetTypeName.value}`;
    }
    return 'Harga Sewa';
});

const mainSteps = computed(() => {
    const lastMainStepIds = steps.value.filter(s => s.id >= 4).map(s => s.id);

    if (allowUnits.value) {
        return [
            { id: 1, title: 'Data Aset', internalSteps: [1, 2] },
            { id: 2, title: stepTwoTitle.value, internalSteps: [3] },
            { id: 3, title: 'Foto & Fasilitas', internalSteps: lastMainStepIds }
        ];
    } else {
        return [
            { id: 1, title: 'Data Aset', internalSteps: [1, 2] },
            { id: 2, title: stepTwoTitle.value, internalSteps: [3] },
            { id: 3, title: 'Foto & Fasilitas', internalSteps: lastMainStepIds }
        ];
    }
});

const currentMainStep = computed(() => {
    return mainSteps.value.find(ms => ms.internalSteps.includes(currentStep.value)) || mainSteps.value[0];
});

const getProgressWidth = (mStep) => {
    if (currentMainStep.value.id > mStep.id) return '100%';
    if (currentMainStep.value.id < mStep.id) return '0%';

    const currentIndex = mStep.internalSteps.indexOf(currentStep.value) + 1;
    const total = mStep.internalSteps.length;
    return `${(currentIndex / total) * 100}%`;
};

const fasilitasDropdownOpen = ref(false);
const fasilitasDropdownRef = ref(null);

const toggleFasilitasDropdown = () => { fasilitasDropdownOpen.value = !fasilitasDropdownOpen.value; };
const handleClickOutsideFasilitas = (e) => { if (fasilitasDropdownRef.value && !fasilitasDropdownRef.value.contains(e.target)) fasilitasDropdownOpen.value = false; };
const toggleFasilitas = (facilityId) => {
    const index = form.facility_ids.indexOf(facilityId);
    if (index === -1) form.facility_ids.push(facilityId);
    else form.facility_ids.splice(index, 1);
};

const tambahUnit = () => {
    form.units.forEach(u => u.is_expanded = false);
    form.units.push(makeEmptyUnit());
};
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

    // Pastikan array terinisialisasi
    if (!form.units[unitIndex].photos[photoIndex].previews) form.units[unitIndex].photos[photoIndex].previews = [];
    if (!form.units[unitIndex].photos[photoIndex].files) form.units[unitIndex].photos[photoIndex].files = [];

    for (const file of files) {
        const previewUrl = URL.createObjectURL(file);
        form.units[unitIndex].photos[photoIndex].previews.push(previewUrl);

        // Asynchronous Upload for Draft
        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await axios.post(route('owner.asset.upload-temp'), formData);
            form.units[unitIndex].photos[photoIndex].files.push(res.data.path);
        } catch (e) { console.error(e); }
    }
    event.target.value = null;
    await saveDraft();
};
const hapusUnitFoto = async (unitIndex, photoIndex, fileIndex) => {
    URL.revokeObjectURL(form.units[unitIndex].photos[photoIndex].previews[fileIndex]);
    form.units[unitIndex].photos[photoIndex].files.splice(fileIndex, 1);
    form.units[unitIndex].photos[photoIndex].previews.splice(fileIndex, 1);
    await saveDraft();
};

const gantiUnitFoto = async (event, unitIndex, photoIndex, fileIndex) => {
    const file = event.target.files[0];
    if (!file) return;

    URL.revokeObjectURL(form.units[unitIndex].photos[photoIndex].previews[fileIndex]);

    const previewUrl = URL.createObjectURL(file);
    form.units[unitIndex].photos[photoIndex].previews.splice(fileIndex, 1, previewUrl);

    const formData = new FormData();
    formData.append('file', file);
    try {
        const res = await axios.post(route('owner.asset.upload-temp'), formData);
        form.units[unitIndex].photos[photoIndex].files.splice(fileIndex, 1, res.data.path);
        await saveDraft();
    } catch (e) {
        console.error(e);
    }
};

const pindahkanUnitFoto = async (unitIndex, sourcePhotoIndex, fileIndex, targetUnitIndex, targetPhotoIndex) => {
    const preview = form.units[unitIndex].photos[sourcePhotoIndex].previews.splice(fileIndex, 1)[0];

    if (!form.units[targetUnitIndex].photos[targetPhotoIndex].previews) form.units[targetUnitIndex].photos[targetPhotoIndex].previews = [];
    if (!form.units[targetUnitIndex].photos[targetPhotoIndex].files) form.units[targetUnitIndex].photos[targetPhotoIndex].files = [];

    form.units[targetUnitIndex].photos[targetPhotoIndex].previews.push(preview);

    const file = form.units[unitIndex].photos[sourcePhotoIndex].files.splice(fileIndex, 1)[0];
    form.units[targetUnitIndex].photos[targetPhotoIndex].files.push(file);

    await saveDraft();
};

const handleUnitThumbnailUpload = async (event, unitIndex) => {
    const file = event.target.files[0];
    if (file) {
        if (form.units[unitIndex].thumbnail_preview) URL.revokeObjectURL(form.units[unitIndex].thumbnail_preview);
        form.units[unitIndex].thumbnail_preview = URL.createObjectURL(file);

        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await axios.post(route('owner.asset.upload-temp'), formData);
            form.units[unitIndex].thumbnail = res.data.path;
        } catch (e) { console.error(e); }
    }
    event.target.value = null;
    await saveDraft();
};
const hapusUnitThumbnail = async (unitIndex) => {
    if (form.units[unitIndex].thumbnail_preview) URL.revokeObjectURL(form.units[unitIndex].thumbnail_preview);
    form.units[unitIndex].thumbnail = null;
    form.units[unitIndex].thumbnail_preview = null;
    await saveDraft();
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

    // Pastikan array terinisialisasi
    if (!form.photos[index].previews) form.photos[index].previews = [];
    if (!form.photos[index].files) form.photos[index].files = [];

    for (const file of files) {
        const previewUrl = URL.createObjectURL(file);
        form.photos[index].previews.push(previewUrl);

        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await axios.post(route('owner.asset.upload-temp'), formData);
            form.photos[index].files.push(res.data.path);
        } catch (e) { console.error(e); }
    }
    event.target.value = null;
    await saveDraft();
};
const hapusFoto = async (catIndex, fileIndex) => {
    URL.revokeObjectURL(form.photos[catIndex].previews[fileIndex]);
    form.photos[catIndex].files.splice(fileIndex, 1);
    form.photos[catIndex].previews.splice(fileIndex, 1);
    await saveDraft();
};

const gantiFoto = async (event, index, fileIndex) => {
    const file = event.target.files[0];
    if (!file) return;

    URL.revokeObjectURL(form.photos[index].previews[fileIndex]);

    const previewUrl = URL.createObjectURL(file);
    form.photos[index].previews.splice(fileIndex, 1, previewUrl);

    const formData = new FormData();
    formData.append('file', file);
    try {
        const res = await axios.post(route('owner.asset.upload-temp'), formData);
        form.photos[index].files.splice(fileIndex, 1, res.data.path);
        await saveDraft();
    } catch (e) {
        console.error(e);
    }
};

const pindahkanFoto = async (sourceCatIndex, fileIndex, targetCatIndex) => {
    const preview = form.photos[sourceCatIndex].previews.splice(fileIndex, 1)[0];

    if (!form.photos[targetCatIndex].previews) form.photos[targetCatIndex].previews = [];
    if (!form.photos[targetCatIndex].files) form.photos[targetCatIndex].files = [];

    form.photos[targetCatIndex].previews.push(preview);

    const file = form.photos[sourceCatIndex].files.splice(fileIndex, 1)[0];
    form.photos[targetCatIndex].files.push(file);

    await saveDraft();
};

const handleThumbnailUpload = async (event) => {
    const file = event.target.files[0];
    if (file) {
        if (form.thumbnail_preview) URL.revokeObjectURL(form.thumbnail_preview);
        form.thumbnail_preview = URL.createObjectURL(file);

        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await axios.post(route('owner.asset.upload-temp'), formData);
            form.thumbnail = res.data.path;
        } catch (e) { console.error('Upload thumbnail gagal:', e); }
    }
    event.target.value = null;
    await saveDraft();
};
const hapusThumbnail = async () => {
    if (form.thumbnail_preview) URL.revokeObjectURL(form.thumbnail_preview);
    form.thumbnail = null;
    form.thumbnail_preview = null;
    await saveDraft();
};

// --- VALIDATION AND AUTO-SAVE ---
const validationErrors = ref({});
const showValidationAlert = ref(false);
const isSavingDraft = ref(false);

const preparePayload = (data, isKos) => {
    const payload = JSON.parse(JSON.stringify(data));

    // Bersihkan data yang tidak perlu sebelum dikirim
    if (payload.units) {
        payload.units = payload.units.map(unit => {
            const u = { ...unit };
            delete u._id;
            delete u.thumbnail_preview;
            if (u.pricings) {
                u.pricings = u.pricings.filter(p => p.price && Number(p.price) > 0);
                u.pricings.forEach(p => delete p._id);
            }
            if (u.photos) {
                // Hanya simpan grup foto yang memiliki file terpilih
                u.photos = u.photos.filter(p => p.files && p.files.length > 0);
                u.photos.forEach(p => {
                    delete p._id;
                    delete p.previews;
                });
            }
            if (isKos && u.name && !u.name.toLowerCase().startsWith('tipe ')) {
                u.name = 'Tipe ' + u.name.trim();
            }

            return u;
        });
    }

    if (payload.photos) {
        // Hanya simpan grup foto utama yang memiliki file terpilih
        payload.photos = payload.photos.filter(p => p.files && p.files.length > 0);
        payload.photos.forEach(p => {
            delete p._id;
            delete p.previews;
        });
    }

    if (payload.pricings) {
        payload.pricings = payload.pricings.filter(p => p.price && Number(p.price) > 0);
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

    if (isKos) {
        delete payload.price;
    } else {
        delete payload.units;
    }

    return payload;
};

const saveDraft = async () => {
    try {
        isSavingDraft.value = true;
        const payload = preparePayload(form.data(), allowUnits.value);
        const res = await axios.post(route('owner.asset.auto-save'), payload);
        const data = res.data;
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

    const currentStepConfig = steps.value[currentStep.value - 1];
    const currentComponent = currentStepConfig.component;
    if (currentComponent === 'Step1') err = validateStep1();
    else if (currentComponent === 'Step3') err = validateStep3();
    else if (currentComponent === 'Step5') err = validateStep5();
    else if (currentComponent === 'Step6') err = validateStep6();
    else if (currentComponent === 'Step7') err = validateStep7(currentStepConfig.unitIndex);
    else if (currentComponent === 'Step8') err = validateStep8();
    else if (currentComponent === 'Step8Unit') err = validateStep8Unit(currentStepConfig.unitIndex);
    else if (currentComponent === 'Step9') err = validateStep9();

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

const validateStep1 = () => {
    const errors = {};
    if (!form.asset_type_id) errors.asset_type_id = 'Tipe aset wajib dipilih.';
    if (!form.title?.trim()) errors.title = 'Nama aset wajib diisi.';

    // Validasi lokasi juga
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
    if (assetTypeDetails.value?.specifications) {
        const requiredSpecs = assetTypeDetails.value.specifications.filter(s => s.is_required);
        for (const spec of requiredSpecs) {
            if (!form.detail[spec.key]) errors[`detail.${spec.key}`] = `${spec.label} wajib diisi.`;
        }
    }
    if (allowUnits.value) {
        form.units.forEach((unit, i) => {
            if (!unit.name?.trim()) errors[`units.${i}.name`] = 'Nama unit wajib diisi.';
            if (!unit.quantity || Number(unit.quantity) < 1) errors[`units.${i}.quantity`] = 'Jumlah unit wajib diisi.';
        });
    }
    return errors;
};

const validateStep5 = () => {
    const errors = {};
    if (allowUnits.value) {
        form.units.forEach((unit, i) => {
            if (!unit.pricings || unit.pricings.length === 0) {
                errors[`units.${i}.pricings`] = 'Paket harga unit wajib diisi.';
            } else {
                if (!unit.pricings[0].price || Number(unit.pricings[0].price) <= 0) {
                    errors[`units.${i}.pricings.0.price`] = 'Harga unit wajib diisi.';
                }
            }
        });
    } else {
        if (!form.pricings || form.pricings.length === 0) {
            errors.pricings = 'Paket harga sewa wajib diisi.';
        } else {
            if (!form.pricings[0].price || Number(form.pricings[0].price) <= 0) {
                errors[`pricings.0.price`] = 'Harga sewa dasar wajib diisi.';
            }
        }
    }
    return errors;
};

const validateStep6 = () => {
    const errors = {};
    if (!form.thumbnail) errors.thumbnail = 'Foto sampul utama wajib diunggah.';
    if (form.photos) {
        form.photos.forEach((group, i) => {
            if (group.is_mandatory && (!group.files || group.files.length === 0)) {
                errors[`foto_${i}`] = `Foto untuk kategori ${group.gallery_category_name} wajib diunggah.`;
            }
        });
    }
    return errors;
};

const validateStep7 = (unitIdx) => {
    const errors = {};
    if (allowUnits.value) {
        const unit = form.units[unitIdx];
        if (!unit) return errors;
        if (!unit.thumbnail) {
            errors[`unit_${unitIdx}_thumbnail`] = `Foto sampul untuk unit ${unit.name || 'ini'} wajib diunggah.`;
        }
        if (unit.photos) {
            unit.photos.forEach((group, photoIdx) => {
                if (group.is_mandatory && (!group.files || group.files.length === 0)) {
                    errors[`unit_${unitIdx}_photo_${photoIdx}`] = `Foto untuk kategori ${group.gallery_category_name} wajib diunggah.`;
                }
            });
        }
    }
    return errors;
};

const validateStep8 = () => { // Fasilitas Aset
    const errors = {};
    if (assetTypeDetails.value?.mandatory_facility_categories?.length > 0) {
        let availableAssetFacilities = [...(assetTypeDetails.value.facilities || [])];
        
        const isLuarOrBersamaSelected = availableAssetFacilities.some(f => 
            (f.name === 'Kamar Mandi Luar' || f.name === 'Kamar Mandi Bersama') && 
            form.facility_ids.includes(f.id)
        );
        if (isLuarOrBersamaSelected) {
            const perabotFacilities = (assetTypeDetails.value.unit_facilities || [])
                .filter(f => f.category?.name === 'Perabot Kamar Mandi');
            availableAssetFacilities = availableAssetFacilities.concat(perabotFacilities);
        }

        for (const cat of assetTypeDetails.value.mandatory_facility_categories) {
            const facilitiesInCat = availableAssetFacilities.filter(f => f.category?.name === cat.name || f.facility_category_id === cat.id);
            if (facilitiesInCat.length === 0) continue;
            
            const hasSelected = facilitiesInCat.some(f => form.facility_ids.includes(f.id));
            if (!hasSelected) {
                errors[`facility_dasar_${cat.id}`] = `Pilih minimal 1 fasilitas dari kategori ${cat.name}.`;
            }
        }
    }
    return errors;
};

const validateStep8Unit = (unitIdx) => { // Fasilitas Unit
    const errors = {};
    if (allowUnits.value) {
        const unit = form.units[unitIdx];
        if (!unit) return errors;
        if (assetTypeDetails.value?.mandatory_unit_facility_categories?.length > 0) {
            const assetFacilities = assetTypeDetails.value.facilities || [];
            const isDalamSelected = assetFacilities.some(f => 
                f.name === 'Kamar Mandi Dalam' && form.facility_ids.includes(f.id)
            );
            
            let availableUnitFacilities = [...(assetTypeDetails.value.unit_facilities || [])];
            if (!isDalamSelected) {
                availableUnitFacilities = availableUnitFacilities.filter(f => f.category?.name !== 'Perabot Kamar Mandi');
            }

            for (const cat of assetTypeDetails.value.mandatory_unit_facility_categories) {
                const facilitiesInCat = availableUnitFacilities.filter(f => f.category?.name === cat.name || f.facility_category_id === cat.id);
                if (facilitiesInCat.length === 0) continue;
                
                const hasSelected = facilitiesInCat.some(f => unit.facility_ids?.includes(f.id));
                if (!hasSelected) {
                    errors[`unit_${unitIdx}_facility_dasar_${cat.id}`] = `Pilih minimal 1 fasilitas dari kategori ${cat.name}.`;
                }
            }
        }
    }
    return errors;
};

const validateStep9 = () => { // Kebijakan & FAQ (Optional), tapi Deskripsi sekarang disini
    const errors = {};
    if (!form.description?.trim()) errors.description = 'Deskripsi aset wajib diisi.';
    else if (form.description.trim().length < 100) errors.description = 'Deskripsi minimal 100 karakter.';
    return errors;
};

const submitProperty = async () => {
    showValidationAlert.value = false;
    validationErrors.value = {};

    // Hanya validasi step terakhir saat submit
    const currentComponent = steps.value[steps.value.length - 1].component;
    let err = {};
    if (currentComponent === 'Step9') err = validateStep9();

    if (Object.keys(err).length > 0) {
        validationErrors.value = err;
        showValidationAlert.value = true;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    if (page.props.isProfileComplete === false) {
        window.dispatchEvent(new CustomEvent('show-profile-incomplete-bubble'));
        return;
    }

    // Auto save draft for the last time before submitting (optional, but good for safety)
    await saveDraft();

    isSubmittingFinal.value = true;

    form.transform((data) => {
        return preparePayload(data, allowUnits.value);
    }).post(route('owner.asset.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
        },
        onError: (errors) => {
            isSubmittingFinal.value = false;
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
        title="Daftarkan Aset"
        role="Owner"
        subMenuParentRouteName="owner.asset.*"
    >
        <template #leftAction>
            <Link
                href="/owner/asset"
                class="w-9 h-9 flex items-center justify-center rounded-full text-slate-600 hover:bg-slate-100 hover:text-[#0A2540] transition-all shrink-0"
                title="Kembali"
            >
                <ArrowLeft class="w-5 h-5" />
            </Link>
        </template>
        <Head title="Ajukan Aset Baru" />

        <div class="pb-32 font-sans text-[#0A2540]">
            <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col gap-6 lg:gap-8">

                <!-- MAIN PROGRESS BAR -->
                <div class="w-full flex items-start justify-between relative">
                    <template v-for="(mStep, index) in mainSteps" :key="mStep.id">
                        <div class="flex-1 flex flex-col relative z-10 px-1 sm:px-2 text-center items-center group">

                            <!-- Icon & Title -->
                            <div class="flex items-center justify-center gap-1.5 sm:gap-2 mb-2 sm:mb-3">
                                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-[11px] sm:text-[13px] font-bold transition-colors shrink-0 border-[1.5px]"
                                    :class="[
                                        currentStep > mStep.internalSteps[mStep.internalSteps.length-1]
                                            ? 'border-[#FFC000] bg-[#FFC000] text-white'
                                            : currentMainStep.id === mStep.id
                                            ? 'border-[#FFC000] text-[#FFC000] bg-transparent'
                                            : 'border-slate-300 text-slate-400 bg-transparent'
                                    ]">
                                    <span v-if="currentStep > mStep.internalSteps[mStep.internalSteps.length-1]" class="font-black">✓</span>
                                    <span v-else>{{ mStep.id }}</span>
                                </div>
                                <span class="text-[11px] sm:text-[14px] whitespace-nowrap transition-colors tracking-tight"
                                      :class="currentMainStep.id >= mStep.id ? 'text-[#0A2540] font-bold' : 'text-slate-400 font-medium'">
                                    {{ mStep.title }}
                                </span>
                            </div>

                            <!-- Continuous Progress Line -->
                            <div class="w-full h-1.5 mt-auto relative px-1">
                                <div class="w-full h-full bg-slate-200 relative rounded-full overflow-hidden">
                                    <div class="h-full bg-[#FFC000] transition-all duration-500 ease-out"
                                         :style="{ width: getProgressWidth(mStep) }">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
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

                    <!-- FORM SKELETON LOADER -->
                    <div v-if="isLoadingTypeDetails && currentStep !== 1" class="bg-white rounded-lg p-6 md:p-8 border border-slate-200/80 shadow-sm space-y-6">
                        <div class="h-6 bg-slate-200 rounded animate-pulse w-1/3 mb-4"></div>
                        <div class="space-y-4">
                            <div class="h-4 bg-slate-200 rounded animate-pulse w-1/4"></div>
                            <div class="h-12 bg-slate-200 rounded animate-pulse w-full"></div>
                        </div>
                        <div class="space-y-4">
                            <div class="h-4 bg-slate-200 rounded animate-pulse w-1/4"></div>
                            <div class="h-12 bg-slate-200 rounded animate-pulse w-full"></div>
                        </div>
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <div class="h-4 bg-slate-200 rounded animate-pulse w-1/4"></div>
                            <div class="h-40 bg-slate-200 rounded animate-pulse w-full"></div>
                        </div>
                    </div>

                    <!-- FORM CARD -->
            <form @submit.prevent="submitProperty" class="bg-white rounded-lg p-6 md:p-8 border border-slate-200/80 shadow-sm space-y-6 relative">

                <!-- STEP 1 -->
                <Step1
                    v-show="steps[currentStep - 1]?.component === 'Step1'"
                    :form="form"
                    :categories="categories"
                    :availableTypes="availableTypes"
                    :assetTypeDetails="assetTypeDetails"
                    :allowUnits="allowUnits"
                    :currentStep="currentStep"
                    :assetTypeName="selectedAssetTypeName"
                />

                <Step3
                    v-show="steps[currentStep - 1]?.component === 'Step3'"
                    :form="form"
                    :assetTypeDetails="assetTypeDetails"
                    :unitLabel="unitLabel"
                    @toggleFasilitas="toggleFasilitas"
                    @tambahUnit="tambahUnit"
                    @hapusUnit="hapusUnit"
                />

                <Step4
                    v-show="steps[currentStep - 1]?.component === 'Step4'"
                    :form="form"
                    :assetTypeDetails="assetTypeDetails"
                    :unitLabel="unitLabel"
                    @tambahUnit="tambahUnit"
                    @hapusUnit="hapusUnit"
                    @toggleUnitFasilitas="toggleUnitFasilitas"
                />

                <Step5
                    v-show="steps[currentStep - 1]?.component === 'Step5'"
                    :form="form"
                    :allowUnits="allowUnits"
                    :assetTypeDetails="assetTypeDetails"
                    :unitLabel="unitLabel"
                />

                <Step6
                    v-show="steps[currentStep - 1]?.component === 'Step6'"
                    :isActive="steps[currentStep - 1]?.component === 'Step6'"
                    :form="form"
                    :assetTypeDetails="assetTypeDetails"
                    @tambahKategoriFoto="tambahKategoriFoto"
                    @hapusKategoriFoto="hapusKategoriFoto"
                    @handleFileUpload="handleFileUpload"
                    @hapusFoto="hapusFoto"
                    @gantiFoto="gantiFoto"
                    @pindahkanFoto="pindahkanFoto"
                    @handleThumbnailUpload="handleThumbnailUpload"
                    @hapusThumbnail="hapusThumbnail"
                />

                <Step7
                    v-if="steps[currentStep - 1]?.component === 'Step7'"
                    :form="form"
                    :allowUnits="allowUnits"
                    :assetTypeDetails="assetTypeDetails"
                    :unitLabel="unitLabel"
                    :currentUnitIndex="steps[currentStep - 1]?.unitIndex"
                    @tambahUnitKategoriFoto="tambahUnitKategoriFoto"
                    @hapusUnitKategoriFoto="hapusUnitKategoriFoto"
                    @handleUnitFileUpload="handleUnitFileUpload"
                    @hapusUnitFoto="hapusUnitFoto"
                    @gantiUnitFoto="gantiUnitFoto"
                    @pindahkanUnitFoto="pindahkanUnitFoto"
                    @handleUnitThumbnailUpload="handleUnitThumbnailUpload"
                    @hapusUnitThumbnail="hapusUnitThumbnail"
                />

                <Step8
                    v-show="steps[currentStep - 1]?.component === 'Step8'"
                    :form="form"
                    :assetTypeDetails="assetTypeDetails"
                />

                <Step8Unit
                    v-if="steps[currentStep - 1]?.component === 'Step8Unit'"
                    :form="form"
                    :assetTypeDetails="assetTypeDetails"
                    :unitLabel="unitLabel"
                    :unitIndex="steps[currentStep - 1]?.unitIndex"
                />

                <Step9
                    v-show="steps[currentStep - 1]?.component === 'Step9'"
                    :form="form"
                    @addFaq="addFaq"
                    @removeFaq="removeFaq"
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
                <button v-if="currentStep < steps.length" type="button" @click="nextStep" :disabled="form.processing || isSavingDraft || !isCurrentStepValid" class="h-[40px] px-6 rounded-md bg-[#F2C94C] text-[#0A2540] font-bold text-[14px] shadow-md transition-colors disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg v-if="isSavingDraft" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ isSavingDraft ? 'Memproses' : 'Berikutnya' }}
                </button>
                <button v-else type="button" @click="submitProperty" :disabled="form.processing || isSavingDraft || !isCurrentStepValid" class="h-[40px] px-6 rounded-md bg-[#FFC000] text-[#0A2540] font-bold text-[14px] shadow-md transition-colors disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg v-if="isSavingDraft || form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Selesaikan
                </button>
            </template>
        </DetailBottomBar>

        <!-- STICKY BOTTOM ACTION BAR (Desktop) -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] z-40 hidden md:block">
            <div class="w-full px-6 h-16 flex items-center justify-between">
                <div>
                    <button type="button" @click="prevStep" :disabled="isSavingDraft" class="h-[40px] px-6 rounded-md border border-slate-300 text-[#0A2540] font-semibold text-[14px] hover:bg-slate-50 transition-colors bg-white shadow-sm flex items-center gap-2 disabled:opacity-50" :class="currentStep === 1 ? 'invisible' : ''">
                        Sebelumnya
                    </button>
                </div>

                <div>
                    <button v-if="currentStep < steps.length" type="button" @click="nextStep" :disabled="form.processing || isSavingDraft || !isCurrentStepValid" class="h-[40px] px-8 rounded-md bg-[#F2C94C] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed">
                        <svg v-if="isSavingDraft" class="animate-spin -ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ isSavingDraft ? 'Memproses...' : 'Berikutnya' }}
                    </button>
                    <button v-else type="button" @click="submitProperty" :disabled="form.processing || isSavingDraft || !isCurrentStepValid" class="h-[40px] px-8 rounded-md bg-[#FFC000] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
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
                        <div class="w-48 h-auto mx-auto flex items-center justify-center">
                            <EmptyStateIcon class="w-full h-auto" />
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

                        <button type="button" @click="closeModalAndRedirect" class="w-full bg-[#FFC000] text-[#0A2540] font-bold text-sm py-3.5 rounded-md hover:brightness-95 transition shadow-sm cursor-pointer">
                            Kembali ke Halaman Aset & Unit
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
        <!-- LEAVE CONFIRMATION MODAL -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showLeaveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                    <div class="bg-white rounded-xl p-6 sm:p-8 max-w-sm w-full text-center shadow-2xl border border-slate-100 space-y-4 relative overflow-hidden">

                        <!-- Illustration Logo -->
                        <div class="w-24 h-auto mx-auto mb-2 flex items-center justify-center">
                            <img src="/kitasewa-logo.png" alt="KiraSewa Logo" class="w-full h-auto object-contain" />
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-lg font-bold text-[#0A2540] tracking-tight">Mohon Perhatiannya Sebentar</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                Jika keluar, maka data kos akan tersimpan dengan status <span class="font-bold">"Draft"</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-3 w-full pt-2">
                            <button @click="confirmLeave" type="button" class="flex-1 py-3 px-4 rounded-md border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                                Keluar Sekarang
                            </button>
                            <button @click="cancelLeave" type="button" class="flex-1 py-3 px-4 rounded-md bg-[#FFC000] text-[#0A2540] font-bold text-sm hover:brightness-95 transition shadow-sm shadow-amber-500/20">
                                Lanjut Isi
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- TOAST NOTIFICATION -->
        <Toast :show="showDraftToast" message="Progress tersimpan sebagai draft" type="success" />

    </DashboardLayout>
</template>
