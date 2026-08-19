<script setup>
import { Image, Pen, X, Trash2, Plus, Loader2 } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import BottomSheet from '@/Components/UI/BottomSheet.vue';

const props = defineProps({
    asset: Object,
    galleryCategories: {
        type: Array,
        default: () => []
    }
});

const formatRupiah = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

// Modal State
const showModal = ref(false);
const isEditing = ref(false);

const unitDetailFields = ref([]);
const unitFacilitiesFromDB = ref([]);

onMounted(async () => {
    if (props.asset?.type?.id) {
        try {
            const res = await fetch(`/api/asset-type/${props.asset.type.id}/details`);
            const json = await res.json();
            unitDetailFields.value = json.unit_detail_fields || [];
            unitFacilitiesFromDB.value = json.unit_facilities || [];
        } catch (e) {
            console.error('Gagal mengambil detail tipe aset', e);
        }
    }
});

const existingImages = ref([]);
const unitImageGroups = ref([]);

const isSubmitting = ref(false);
const formErrors = ref({});

const form = ref({
    id: null,
    name: '',
    quantity: 1,
    pricings: [{ _id: Date.now(), duration: 1, rental_unit: 'month', price: 0 }],
    detail: {},
    facilities: [],
    new_images: [],
    deleted_images: [],
    thumbnail: null,
    thumbnail_preview: null,
});

const openCreateModal = () => {
    isEditing.value = false;
    isSubmitting.value = false;
    formErrors.value = {};
    form.value = {
        id: null,
        name: '',
        quantity: 1,
        pricings: [{ _id: Date.now(), duration: 1, rental_unit: 'month', price: 0 }],
        detail: {},
        facilities: [],
        new_images: [],
        deleted_images: [],
        thumbnail: null,
        thumbnail_preview: null,
    };
    existingImages.value = [];
    unitImageGroups.value = [];
    showModal.value = true;
};

const openEditModal = (unit) => {
    isEditing.value = true;
    isSubmitting.value = false;
    formErrors.value = {};
    form.value = {
        id: unit.id,
        name: unit.name,
        quantity: unit.quantity || 1,
        pricings: (unit.pricings && unit.pricings.length > 0) ? unit.pricings.map(p => ({...p, _id: p.id || Date.now() + Math.random()})) : [{ _id: Date.now(), duration: 1, rental_unit: 'month', price: 0 }],
        detail: unit.detail || {},
        facilities: (unit.facilities || []).map(f => f.id),
        new_images: [],
        deleted_images: [],
        thumbnail: null,
        thumbnail_preview: null,
    };

    // Find thumbnail from thumbnail_image relation
    if (unit.thumbnail_image) {
        form.value.thumbnail_preview = unit.thumbnail_image.image_url;
    }
    existingImages.value = unit.images ? unit.images.filter(img => !img.is_thumbnail) : [];

    unitImageGroups.value = [];
    showModal.value = true;
};

// --- Thumbnail Logic ---
const handleUnitThumbnailUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.thumbnail = file;
        if (form.value.thumbnail_preview && form.value.thumbnail_preview.startsWith('blob:')) {
            URL.revokeObjectURL(form.value.thumbnail_preview);
        }
        form.value.thumbnail_preview = URL.createObjectURL(file);
    }
    e.target.value = '';
};

const removeUnitThumbnail = () => {
    if (form.value.thumbnail_preview && form.value.thumbnail_preview.startsWith('blob:')) {
        URL.revokeObjectURL(form.value.thumbnail_preview);
    }
    form.value.thumbnail = null;
    form.value.thumbnail_preview = null;
};

// --- Image Handling Logic ---
const addUnitImageGroup = () => {
    unitImageGroups.value.push({
        id: Date.now() + '_' + Math.random().toString(36).substring(2, 9),
        category_id: '',
        items: []
    });
};

const handleGroupImageChange = (e, groupIndex) => {
    const files = e.target.files;
    if (!files.length) return;
    
    for (let i=0; i<files.length; i++) {
        unitImageGroups.value[groupIndex].items.push({
            id: Date.now() + '_' + i,
            file: files[i],
            preview: URL.createObjectURL(files[i])
        });
    }
    e.target.value = ''; // reset
};

const removeGroupItem = (groupIndex, itemIndex) => {
    const item = unitImageGroups.value[groupIndex].items[itemIndex];
    if (item.preview) URL.revokeObjectURL(item.preview);
    unitImageGroups.value[groupIndex].items.splice(itemIndex, 1);
};

const removeGroup = (groupIndex) => {
    unitImageGroups.value[groupIndex].items.forEach(item => {
        if (item.preview) URL.revokeObjectURL(item.preview);
    });
    unitImageGroups.value.splice(groupIndex, 1);
};

const removeExistingImage = (imageObj) => {
    form.value.deleted_images.push(imageObj.id);
    existingImages.value = existingImages.value.filter(img => img.id !== imageObj.id);
};

const triggerGroupFileInput = (groupId, isMobile = false) => {
    const refName = isMobile ? `fileInput_mobile_${groupId}` : `fileInput_desktop_${groupId}`;
    const el = document.getElementById(refName);
    if (el) el.click();
};

const submit = () => {
    // Build new_images from unitImageGroups
    const newImages = [];
    for (const group of unitImageGroups.value) {
        if (!group.category_id && group.items.length > 0) {
            alert('Silakan pilih kategori untuk semua foto yang Anda tambahkan.');
            return;
        }
        for (const item of group.items) {
            newImages.push({ file: item.file, category_id: group.category_id });
        }
    }

    // Build FormData manually agar file benar-benar terkirim
    const fd = new FormData();
    fd.append('name', form.value.name);
    fd.append('quantity', form.value.quantity);
    form.value.pricings.forEach((p, i) => {
        fd.append(`pricings[${i}][duration]`, p.duration);
        fd.append(`pricings[${i}][rental_unit]`, p.rental_unit);
        fd.append(`pricings[${i}][price]`, p.price);
    });
    fd.append('detail', JSON.stringify(form.value.detail || {}));
    (form.value.facilities || []).forEach(id => fd.append('facilities[]', id));
    (form.value.deleted_images || []).forEach(id => fd.append('deleted_images[]', id));
    newImages.forEach((img, i) => {
        fd.append(`new_images[${i}][file]`, img.file);
        fd.append(`new_images[${i}][category_id]`, img.category_id);
    });
    if (form.value.thumbnail instanceof File) {
        fd.append('thumbnail', form.value.thumbnail);
    }

    isSubmitting.value = true;
    formErrors.value = {};

    const url = isEditing.value
        ? route('owner.asset.units.update', [props.asset.slug || props.asset.id, form.value.id])
        : route('owner.asset.units.store', props.asset.slug || props.asset.id);

    if (isEditing.value) fd.append('_method', 'PUT');

    router.post(url, fd, {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            isSubmitting.value = false;
        },
        onError: (errors) => {
            formErrors.value = errors;
            isSubmitting.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <div class="animate-in fade-in duration-300">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-white px-5 py-4 flex items-center justify-between">
                <h2 class="font-bold text-slate-800">Daftar Unit</h2>
                <button @click="openCreateModal" class="text-xs font-bold text-secondary bg-primary hover:opacity-90 px-3 py-1.5 rounded-lg transition shadow-sm">
                    + Tambah Unit
                </button>
            </div>

            <!-- List / Table View -->
            <div class="flex flex-col divide-y divide-slate-100">
                <!-- Header (Desktop Only) -->
                <div class="hidden md:grid grid-cols-[2fr_1fr_1fr_auto] gap-4 px-5 py-3 bg-slate-50 text-xs text-slate-500 uppercase font-semibold border-y border-slate-100">
                    <div>Nama Unit</div>
                    <div>Jumlah</div>
                    <div>Harga Sewa</div>
                    <div class="text-right w-10">Aksi</div>
                </div>

                <!-- Empty State -->
                <div v-if="!asset.units || asset.units.length === 0" class="px-5 py-12 text-center text-slate-400 text-sm">
                    Belum ada unit yang ditambahkan.
                </div>

                <!-- List Items -->
                <div v-for="unit in asset.units" :key="unit.id" class="group relative hover:bg-slate-50/80 transition p-4 md:px-5 md:py-3 grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_auto] items-start md:items-center gap-3 md:gap-4">

                    <!-- Mobile View: Card Style -->
                    <div class="flex items-center gap-3 md:col-span-1 pr-10 md:pr-0">
                        <div class="w-16 h-12 md:w-12 md:h-10 rounded-md bg-slate-100 overflow-hidden border border-slate-200 shrink-0">
                            <template v-if="unit.thumbnail_image && unit.thumbnail_image.image_url">
                                <img :src="unit.thumbnail_image.image_url" class="w-full h-full object-cover" />
                            </template>
                            <template v-else-if="unit.images && unit.images.length > 0 && (unit.images[0].image_url || unit.images[0].url)">
                                <img :src="unit.images[0].image_url || unit.images[0].url" class="w-full h-full object-cover" />
                            </template>
                            <template v-else>
                                <div class="w-full h-full flex items-center justify-center">
                                    <Image class="text-slate-300" />
                                </div>
                            </template>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-bold text-slate-800 text-sm truncate">{{ unit.name }}</span>
                            <span class="font-medium text-slate-500 text-xs md:hidden mt-0.5">
                                {{ unit.quantity }} Unit <span class="mx-1">&bull;</span> <span class="font-semibold text-[#F97316]">{{ unit.pricings && unit.pricings.length > 0 ? formatRupiah(unit.pricings[0].price) : '-' }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Desktop View: Kuantitas Column -->
                    <div class="hidden md:block font-medium text-slate-700 text-sm">
                        {{ unit.quantity }} Unit
                    </div>

                    <!-- Desktop View: Price Column -->
                    <div class="hidden md:block font-semibold text-[#F97316] text-sm">
                        {{ unit.pricings && unit.pricings.length > 0 ? formatRupiah(unit.pricings[0].price) : '-' }}
                    </div>

                    <!-- Action Button -->
                    <div class="absolute top-4 right-4 md:static md:text-right">
                        <button @click="openEditModal(unit)" class="text-slate-400 hover:text-primary hover:bg-yellow-50 transition w-8 h-8 rounded-lg flex items-center justify-center border border-slate-200 md:border-transparent bg-slate-50 md:bg-transparent">
                            <Pen class="text-xs" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah/Edit Unit (Desktop Only) -->
        <div v-if="showModal" class="hidden md:flex fixed inset-0 z-[100] items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
                <div class="p-6 border-b border-slate-100 shrink-0 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">{{ isEditing ? 'Edit Unit' : 'Tambah Unit Baru' }}</h3>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <X class="" />
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="flex flex-col overflow-hidden min-h-0">
                    <div class="p-6 space-y-5 overflow-y-auto min-h-0">
                        <!-- NAMA UNIT -->
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Nama Unit</label>
                            <input v-model="form.name" type="text" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full transition" placeholder="Contoh: Kamar Deluxe" required />
                            <div v-if="formErrors.name" class="text-xs text-rose-500 mt-1">{{ formErrors.name }}</div>
                        </div>

                        <!-- KUANTITAS -->
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Kuantitas (Jumlah) <span class="text-rose-500">*</span></label>
                            <input v-model="form.quantity" type="number" min="1" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full transition" required />
                            <div v-if="formErrors.quantity" class="text-xs text-rose-500 mt-1">{{ formErrors.quantity }}</div>
                        </div>

                        <!-- DAFTAR HARGA -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                                <label class="text-xs font-bold text-slate-700">Daftar Harga Sewa <span class="text-rose-500">*</span></label>
                                <button type="button" @click="form.pricings.push({ _id: Date.now(), duration: 1, rental_unit: 'month', price: '' })" class="text-[10px] font-bold bg-white border border-slate-200 text-[#0A2540] hover:text-[#FFC000] px-2.5 py-1.5 rounded-lg transition shadow-sm">
                                    + Tambah Harga
                                </button>
                            </div>
                            <div class="p-4 space-y-3 bg-white">
                                <div v-for="(pricing, pIdx) in form.pricings" :key="pricing._id || pIdx" class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-lg p-3">
                                    <div class="w-1/4">
                                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Durasi</label>
                                        <input v-model="pricing.duration" type="number" min="1" class="w-full text-xs px-2.5 py-2 rounded-md border border-slate-300 focus:border-[#0A2540] focus:ring-0" required />
                                    </div>
                                    <div class="w-1/4">
                                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Satuan</label>
                                        <select v-model="pricing.rental_unit" class="w-full text-xs px-2.5 py-2 rounded-md border border-slate-300 focus:border-[#0A2540] focus:ring-0" required>
                                            <option value="hour">Jam</option>
                                            <option value="day">Hari</option>
                                            <option value="night">Malam</option>
                                            <option value="week">Minggu</option>
                                            <option value="month">Bulan</option>
                                        </select>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Harga (Rp)</label>
                                        <input v-model="pricing.price" type="number" min="0" placeholder="100000" class="w-full text-xs px-2.5 py-2 rounded-md border border-slate-300 focus:border-[#0A2540] focus:ring-0" required />
                                    </div>
                                    <button v-if="form.pricings.length > 1" type="button" @click="form.pricings.splice(pIdx, 1)" class="w-8 h-8 rounded-md bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center shrink-0 mt-4" title="Hapus">
                                        <Trash2 class="text-xs" />
                                    </button>
                                </div>
                                <div v-if="formErrors.pricings" class="text-xs text-rose-500">{{ formErrors.pricings }}</div>
                            </div>
                        </div>

                        <!-- THUMBNAIL UNIT (Upload 1 foto) -->
                        <div class="border-t border-slate-100 pt-5">
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Thumbnail Unit</h4>
                            <div class="flex gap-4 items-start">
                                <div v-if="!form.thumbnail_preview" class="flex-1 border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:bg-slate-50 transition cursor-pointer relative">
                                    <input
                                        type="file"
                                        accept="image/png, image/jpeg, image/webp"
                                        @change="handleUnitThumbnailUpload"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                    <Image class="text-2xl text-slate-300 mb-2" />
                                    <p class="text-xs font-bold text-slate-500">Pilih Thumbnail Unit</p>
                                </div>
                                <div v-else class="relative w-32 h-24 group/thumb">
                                    <img :src="form.thumbnail_preview" class="w-full h-full object-cover rounded-xl border border-slate-200" />
                                    <button
                                        type="button"
                                        @click="removeUnitThumbnail"
                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow opacity-0 group-hover/thumb:opacity-100 transition cursor-pointer"
                                        title="Hapus Thumbnail"
                                    >
                                        <X class="" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Detail Fields -->
                        <div v-if="unitDetailFields.length > 0" class="border-t border-slate-100 pt-5">
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Spesifikasi Unit</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="field in unitDetailFields" :key="field.key">
                                    <label class="text-xs font-bold text-slate-500 block mb-1">
                                        {{ field.label }} <span v-if="field.required" class="text-rose-500">*</span>
                                    </label>

                                    <template v-if="field.type === 'select'">
                                        <select v-model="form.detail[field.key]" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full transition" :required="field.required">
                                            <option value="" disabled>Pilih {{ field.label }}</option>
                                            <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                        </select>
                                    </template>
                                    <template v-else-if="field.type === 'number'">
                                        <input v-model="form.detail[field.key]" type="number" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full transition" :placeholder="field.label" :required="field.required" min="0" />
                                    </template>
                                    <template v-else>
                                        <input v-model="form.detail[field.key]" :type="field.type === 'time' ? 'time' : 'text'" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full transition" :placeholder="field.label" :required="field.required" />
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Unit Facilities -->
                        <div v-if="unitFacilitiesFromDB.length > 0" class="border-t border-slate-100 pt-5">
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Fasilitas Unit</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <label v-for="fac in unitFacilitiesFromDB" :key="fac.id" class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" :value="fac.id" v-model="form.facilities" class="w-4 h-4 text-[#FFC000] border-slate-300 rounded focus:ring-[#FFC000]" />
                                    <span class="text-xs text-slate-600 group-hover:text-slate-800 transition line-clamp-1">{{ fac.name }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Foto Unit (Desktop) -->
                        <div class="border-t border-slate-100 pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-slate-800">Foto Unit</h4>
                                <button type="button" @click="addUnitImageGroup" class="text-xs font-bold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition shadow-sm">
                                    + Tambah Kategori Foto
                                </button>
                            </div>

                            <div class="space-y-4">
                                <!-- Existing Images (Edit Mode) -->
                                <div v-if="existingImages.length > 0" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-6">
                                    <div v-for="img in existingImages" :key="img.id" class="relative group rounded-lg overflow-hidden border border-slate-200 aspect-square">
                                        <img :src="img.image_url" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center p-2">
                                            <span class="text-white text-[10px] font-bold text-center bg-black/50 px-2 py-1 rounded-full mb-2 truncate max-w-full">
                                                {{ img.gallery_category?.name || 'Umum' }}
                                            </span>
                                            <button type="button" @click="removeExistingImage(img)" class="w-8 h-8 rounded-full bg-white text-rose-500 hover:text-rose-600 flex items-center justify-center shadow-sm">
                                                <Trash2 class="text-xs" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grouped New Images -->
                                <div v-for="(group, gIdx) in unitImageGroups" :key="group.id" class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="flex items-center justify-between mb-3">
                                        <select v-model="group.category_id" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full max-w-[250px] transition" required>
                                            <option value="" disabled>Pilih Kategori Foto</option>
                                            <option v-for="cat in galleryCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                        <button type="button" @click="removeGroup(gIdx)" class="text-xs font-bold text-rose-500 hover:underline px-2">Hapus Kategori</button>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-3">
                                        <div v-for="(item, iIdx) in group.items" :key="item.id" class="w-20 h-20 rounded-lg overflow-hidden border border-slate-300 relative group/item">
                                            <img :src="item.preview" class="w-full h-full object-cover" />
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/item:opacity-100 transition flex items-center justify-center">
                                                <button type="button" @click="removeGroupItem(gIdx, iIdx)" class="text-white text-xs bg-rose-500 rounded-full w-6 h-6 flex items-center justify-center shadow-sm">
                                                    <X class="" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="w-20 h-20 shrink-0 bg-white rounded-lg border-2 border-dashed border-slate-300 flex flex-col items-center justify-center cursor-pointer hover:border-slate-400 hover:bg-slate-100 transition" @click="triggerGroupFileInput(group.id, false)">
                                            <Plus class="text-slate-400 text-xl" />
                                            <input :id="'fileInput_desktop_' + group.id" type="file" class="hidden" multiple accept="image/*" @change="e => handleGroupImageChange(e, gIdx)" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-slate-100 shrink-0 flex items-center gap-3">
                        <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition text-sm">
                            Batal
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="flex-1 px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg transition text-sm flex items-center justify-center gap-2">
                            <Loader2 v-if="isSubmitting" class="animate-spin" />
                            {{ isEditing ? 'Simpan Perubahan' : 'Simpan Unit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bottom Sheet Tambah/Edit Unit (Mobile Only) -->
        <BottomSheet v-model="showModal" :title="isEditing ? 'Edit Unit' : 'Tambah Unit Baru'" heightClass="h-[90vh]">
            <form @submit.prevent="submit" class="flex flex-col h-full overflow-hidden">
                <div class="p-5 space-y-5 overflow-y-auto flex-1 pb-10">
                    <!-- NAMA UNIT -->
                    <div>
                        <label class="text-xs font-bold text-slate-500 block mb-1">Nama Unit</label>
                        <input v-model="form.name" type="text" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-3 w-full transition" placeholder="Contoh: Kamar Deluxe" required />
                        <div v-if="formErrors.name" class="text-xs text-rose-500 mt-1">{{ formErrors.name }}</div>
                    </div>

                    <!-- KUANTITAS & HARGA -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Kuantitas</label>
                            <input v-model="form.quantity" type="number" min="1" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-3 w-full transition" required />
                            <div v-if="formErrors.quantity" class="text-xs text-rose-500 mt-1">{{ formErrors.quantity }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 block mb-1">Harga Sewa (Rp)</label>
                            <input v-model="form.price" type="number" min="0" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-3 w-full transition" required />
                            <div v-if="formErrors.price" class="text-xs text-rose-500 mt-1">{{ formErrors.price }}</div>
                        </div>
                    </div>

                    <!-- Dynamic Detail Fields -->
                    <div v-if="unitDetailFields.length > 0" class="border-t border-slate-100 pt-5">
                        <h4 class="text-sm font-bold text-slate-800 mb-3">Spesifikasi Unit</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div v-for="field in unitDetailFields" :key="field.key">
                                <label class="text-xs font-bold text-slate-500 block mb-1">
                                    {{ field.label }} <span v-if="field.required" class="text-rose-500">*</span>
                                </label>

                                <template v-if="field.type === 'select'">
                                    <select v-model="form.detail[field.key]" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-3 w-full transition" :required="field.required">
                                        <option value="" disabled>Pilih {{ field.label }}</option>
                                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>
                                </template>
                                <template v-else-if="field.type === 'number'">
                                    <input v-model="form.detail[field.key]" type="number" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-3 w-full transition" :placeholder="field.label" :required="field.required" min="0" />
                                </template>
                                <template v-else>
                                    <input v-model="form.detail[field.key]" :type="field.type === 'time' ? 'time' : 'text'" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-3 w-full transition" :placeholder="field.label" :required="field.required" />
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Unit Facilities -->
                    <div v-if="unitFacilitiesFromDB.length > 0" class="border-t border-slate-100 pt-5">
                        <h4 class="text-sm font-bold text-slate-800 mb-3">Fasilitas Unit</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <label v-for="fac in unitFacilitiesFromDB" :key="fac.id" class="flex items-center gap-2 cursor-pointer group py-1">
                                <input type="checkbox" :value="fac.id" v-model="form.facilities" class="w-5 h-5 text-[#FFC000] border-slate-300 rounded focus:ring-[#FFC000]" />
                                <span class="text-xs text-slate-600 transition">{{ fac.name }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Foto Unit (Mobile) -->
                    <div class="border-t border-slate-100 pt-5">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-slate-800">Foto Unit</h4>
                            <button type="button" @click="addUnitImageGroup" class="text-xs font-bold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition shadow-sm">
                                + Kategori Foto
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Existing Images -->
                            <div v-if="existingImages.length > 0" class="grid grid-cols-2 gap-3 mb-4">
                                <div v-for="img in existingImages" :key="img.id" class="relative group rounded-lg overflow-hidden border border-slate-200 aspect-square">
                                    <img :src="img.image_url" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-black/40 transition flex flex-col items-center justify-center p-2">
                                        <span class="text-white text-[10px] font-bold text-center bg-black/60 px-2 py-1 rounded-full mb-2 truncate max-w-full">
                                            {{ img.gallery_category?.name || 'Umum' }}
                                        </span>
                                        <button type="button" @click="removeExistingImage(img)" class="w-8 h-8 rounded-full bg-white text-rose-500 hover:text-rose-600 flex items-center justify-center shadow-sm">
                                            <Trash2 class="text-xs" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Grouped New Images (Mobile) -->
                            <div v-for="(group, gIdx) in unitImageGroups" :key="group.id" class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                                <div class="flex items-center gap-2">
                                    <select v-model="group.category_id" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2.5 flex-1 transition" required>
                                        <option value="" disabled>Pilih Kategori</option>
                                        <option v-for="cat in galleryCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                    <button type="button" @click="removeGroup(gIdx)" class="w-10 h-10 shrink-0 bg-white border border-slate-200 rounded-lg flex items-center justify-center text-rose-500 shadow-sm">
                                        <Trash2 class="" />
                                    </button>
                                </div>
                                
                                <div class="flex gap-3 overflow-x-auto pb-1 snap-x">
                                    <div v-for="(item, iIdx) in group.items" :key="item.id" class="w-20 h-20 shrink-0 rounded-lg overflow-hidden border border-slate-300 relative snap-start">
                                        <img :src="item.preview" class="w-full h-full object-cover" />
                                        <div class="absolute top-1 right-1">
                                            <button type="button" @click="removeGroupItem(gIdx, iIdx)" class="text-white text-xs bg-rose-500 rounded-full w-5 h-5 flex items-center justify-center shadow-sm">
                                                <X class="" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="w-20 h-20 shrink-0 bg-white rounded-lg border-2 border-dashed border-slate-300 flex flex-col items-center justify-center cursor-pointer snap-start" @click="triggerGroupFileInput(group.id, true)">
                                        <Plus class="text-slate-400 text-lg mb-1" />
                                        <span class="text-[9px] font-semibold text-slate-500">Tambah</span>
                                        <input :id="'fileInput_mobile_' + group.id" type="file" class="hidden" multiple accept="image/*" @change="e => handleGroupImageChange(e, gIdx)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer (dalam form) -->
                <div class="p-5 border-t border-[#6C757D]/10 bg-white shrink-0">
                    <button type="submit" :disabled="isSubmitting" class="w-full py-3 bg-primary hover:bg-primary/90 text-slate-800 font-bold rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-sm">
                        <Loader2 v-if="isSubmitting" class="animate-spin" />
                        {{ isEditing ? 'Simpan Perubahan' : 'Simpan Unit' }}
                    </button>
                </div>
            </form>
        </BottomSheet>
    </div>
</template>
