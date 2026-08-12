<script setup>
import { computed, watch, ref } from 'vue';

const props = defineProps({
    form: Object,
    categories: Array,
    availableTypes: Array,
    assetTypeDetails: Object,  // null | { rental_unit, allow_units, facilities, unit_facilities, detail_fields, unit_detail_fields }
    allowUnits: Boolean,
});

const emit = defineEmits([
    'tambahUnit', 'hapusUnit', 'toggleUnitFasilitas', 'toggleFasilitas', 
    'tambahUnitKategoriFoto', 'hapusUnitKategoriFoto', 'handleUnitFileUpload', 'hapusUnitFoto',
    'handleUnitThumbnailUpload', 'hapusUnitThumbnail'
]);
// Computed: label periode sewa
const rentalUnitLabel = computed(() => {
    const map = {
        hour: 'Per Jam',
        night: 'Per Malam',
        day: 'Per Hari',
        month: 'Per Bulan',
    };
    return map[props.assetTypeDetails?.rental_unit] ?? '-';
});

// Computed: detail fields dari API
const detailFields = computed(() => props.assetTypeDetails?.detail_fields ?? []);
const unitDetailFields = computed(() => props.assetTypeDetails?.unit_detail_fields ?? []);

// Computed: fasilitas dari API
const facilitiesFromDB = computed(() => props.assetTypeDetails?.facilities ?? []);
const unitFacilitiesFromDB = computed(() => props.assetTypeDetails?.unit_facilities ?? []);

// Computed: kategori galeri (global)
const galleryCategoriesFromDB = computed(() => props.assetTypeDetails?.gallery_categories ?? []);

// Fasilitas aset dropdown
const fasilitasDropdownOpen = ref(false);
const fasilitasDropdownRef = ref(null);

const toggleFasilitasDropdown = () => {
    fasilitasDropdownOpen.value = !fasilitasDropdownOpen.value;
};

// Unit fasilitas dropdown state (per unit index)
const unitFasilitasDropdownOpen = ref(null);

const toggleUnitFasilitasDropdown = (index) => {
    unitFasilitasDropdownOpen.value = unitFasilitasDropdownOpen.value === index ? null : index;
};
</script>

<template>
<div class="space-y-5">
<!-- STEP 1: INFORMASI ASET -->
    <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
        <i class="fa-solid fa-building text-[#0A2540]"></i>
        <span>Informasi Dasar Aset</span>
    </h2>

    <!-- Nama Aset -->
    <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Aset <span class="text-rose-500">*</span></label>
        <input
            v-model="form.title"
            type="text"
            placeholder="cth: Kost Pak Budi, Hotel Bintang Lima, Gudang Jl. Sudirman"
            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
            required
        />
    </div>

    <!-- Deskripsi -->
    <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Aset <span class="text-rose-500">*</span></label>
        <textarea
            v-model="form.description"
            rows="4"
            placeholder="Deskripsikan aset Anda secara lengkap. Sebutkan keunggulan, kondisi, dan informasi penting lainnya..."
            class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition resize-none"
            required
        ></textarea>
        <p class="text-[10px] text-slate-400 mt-1">Minimal 20 karakter. {{ form.description?.length ?? 0 }} karakter.</p>
    </div>

    <!-- Kategori & Jenis Aset -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Kategori -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Aset <span class="text-rose-500">*</span></label>
            <select
                v-model="form.category_id"
                class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                required
            >
                <option :value="null" disabled>Pilih Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
        </div>

        <!-- Jenis / Tipe Aset -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Aset <span class="text-rose-500">*</span></label>
            <select
                v-model="form.asset_type_id"
                :disabled="!availableTypes || availableTypes.length === 0"
                class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition disabled:bg-slate-50 disabled:text-slate-400"
                required
            >
                <option :value="null" disabled>Pilih Jenis</option>
                <option v-for="type in availableTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
            </select>
        </div>
    </div>

    <!-- Badge Periode Sewa (readonly, dari DB) -->
    <div v-if="assetTypeDetails">
        <label class="block text-xs font-bold text-slate-700 mb-1">Periode Sewa</label>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 bg-[#0A2540]/5 border border-[#0A2540]/10 text-[#0A2540] font-bold text-xs px-3 py-2 rounded-xl">
                <i class="fa-solid fa-clock text-[10px]"></i>
                {{ rentalUnitLabel }}
            </span>
            <p class="text-[10px] text-slate-400">Ditentukan berdasarkan jenis aset yang dipilih.</p>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- DETAIL SPESIFIKASI ASET (dinamis dari DB) -->
    <!-- ============================================ -->
    <template v-if="detailFields.length > 0">
        <div class="border-t border-slate-100 pt-4">
            <h3 class="text-xs font-bold text-slate-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-[#0A2540] text-[10px]"></i>
                Spesifikasi Aset
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div v-for="field in detailFields" :key="field.key">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        {{ field.label }}
                        <span v-if="field.required" class="text-rose-500">*</span>
                    </label>

                    <!-- Select -->
                    <select
                        v-if="field.type === 'select'"
                        v-model="form.detail[field.key]"
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        :required="field.required"
                    >
                        <option value="" disabled>Pilih...</option>
                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                    </select>

                    <!-- Checkbox -->
                    <div v-else-if="field.type === 'checkbox'" class="flex items-center gap-2 mt-1">
                        <input
                            type="checkbox"
                            :id="`detail_${field.key}`"
                            v-model="form.detail[field.key]"
                            class="w-4 h-4 rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540]"
                        />
                        <label :for="`detail_${field.key}`" class="text-xs text-slate-600">Ya</label>
                    </div>

                    <!-- Radio -->
                    <div v-else-if="field.type === 'radio'" class="flex items-center gap-4 mt-1">
                        <label v-for="opt in field.options" :key="opt" class="flex items-center gap-1.5 text-xs text-slate-600 cursor-pointer">
                            <input
                                type="radio"
                                :name="`detail_${field.key}`"
                                :value="opt"
                                v-model="form.detail[field.key]"
                                class="border-slate-300 text-[#0A2540]"
                            />
                            {{ opt }}
                        </label>
                    </div>

                    <!-- Time -->
                    <input
                        v-else-if="field.type === 'time'"
                        type="time"
                        v-model="form.detail[field.key]"
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        :required="field.required"
                    />

                    <!-- Number / Text fallback -->
                    <input
                        v-else
                        :type="field.type"
                        v-model="form.detail[field.key]"
                        :placeholder="field.label"
                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        :required="field.required"
                    />
                </div>
            </div>
        </div>
    </template>

    <!-- ============================================ -->
    <!-- FASILITAS ASET (dari DB, scope = asset) -->
    <!-- ============================================ -->
    <div v-if="facilitiesFromDB.length > 0" class="border-t border-slate-100 pt-4">
        <h3 class="text-xs font-bold text-slate-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-star text-[#0A2540] text-[10px]"></i>
            Fasilitas Aset
        </h3>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="fac in facilitiesFromDB"
                :key="fac.id"
                type="button"
                @click="emit('toggleFasilitas', fac.id)"
                class="text-[11px] px-3 py-1.5 rounded-xl border transition cursor-pointer"
                :class="form.facility_ids.includes(fac.id)
                    ? 'bg-[#0A2540] text-white border-[#0A2540] font-bold'
                    : 'bg-white text-slate-600 border-slate-200 hover:border-[#0A2540]/40'"
            >
                {{ fac.name }}
            </button>
        </div>

        <p v-if="form.facility_ids.length > 0" class="text-[10px] text-slate-400 mt-2">
            {{ form.facility_ids.length }} fasilitas dipilih
        </p>
    </div>

    <!-- ============================================ -->
    <!-- UNIT ASET (jika allow_units = true) -->
    <!-- ============================================ -->
    <div v-if="allowUnits" class="border-t border-slate-100 pt-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-door-open text-[#0A2540] text-[10px]"></i>
                Tipe / Unit Aset
            </h3>
            <button
                type="button"
                @click="emit('tambahUnit')"
                class="text-[11px] font-bold text-[#0A2540] hover:text-[#FFC000] bg-slate-100 px-3 py-1.5 rounded-lg transition cursor-pointer shrink-0"
            >
                + Tambah Tipe Unit
            </button>
        </div>

        <div class="space-y-4">
            <div
                v-for="(unit, unitIndex) in form.units"
                :key="unit._id"
                class="border border-slate-200 rounded-2xl p-4 relative bg-white shadow-xs"
            >
                <!-- Tombol Hapus Unit -->
                <button
                    v-if="form.units.length > 1"
                    type="button"
                    @click="emit('hapusUnit', unitIndex)"
                    class="absolute top-3 right-3 w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-xs cursor-pointer"
                    title="Hapus Unit"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>

                <p class="text-[11px] font-bold text-slate-500 mb-3">Tipe Unit {{ unitIndex + 1 }}</p>

                <!-- Nama Unit, Jumlah & Harga -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Tipe Unit <span class="text-rose-500">*</span></label>
                        <input
                            v-model="unit.name"
                            type="text"
                            placeholder="cth: Standard, Deluxe, VIP"
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Jumlah Unit <span class="text-rose-500">*</span></label>
                        <input
                            v-model="unit.quantity"
                            type="number"
                            min="1"
                            placeholder="1"
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                            required
                        />
                    </div>
                </div>

                <!-- Daftar Harga Sewa -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[11px] font-bold text-slate-600">
                            <i class="fa-solid fa-tags text-[#0A2540] mr-1 text-[10px]"></i>
                            Daftar Harga Sewa <span class="text-rose-500">*</span>
                        </label>
                        <button
                            type="button"
                            @click="unit.pricings.push({ _id: Date.now(), duration: 1, rental_unit: assetTypeDetails?.rental_unit || 'month', price: '' })"
                            class="text-[10px] font-bold text-[#0A2540] hover:text-[#FFC000] bg-slate-100 px-2.5 py-1 rounded-lg transition cursor-pointer"
                        >
                            + Tambah Harga
                        </button>
                    </div>
                    
                    <div class="space-y-2">
                        <div v-for="(pricing, pIdx) in unit.pricings" :key="pricing._id || pIdx" class="flex gap-2 items-center bg-slate-50 p-2 rounded-xl border border-slate-100 relative">
                            <div class="w-1/4">
                                <label class="block text-[9px] font-bold text-slate-500 mb-0.5">Durasi</label>
                                <input v-model="pricing.duration" type="number" min="1" class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:border-[#0A2540]" required />
                            </div>
                            <div class="w-1/4">
                                <label class="block text-[9px] font-bold text-slate-500 mb-0.5">Satuan</label>
                                <select v-model="pricing.rental_unit" class="w-full text-xs px-2 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:border-[#0A2540]" required>
                                    <option value="hour">Jam</option>
                                    <option value="day">Hari</option>
                                    <option value="night">Malam</option>
                                    <option value="week">Minggu</option>
                                    <option value="month">Bulan</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-[9px] font-bold text-slate-500 mb-0.5">Harga</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400">Rp</span>
                                    <input v-model="pricing.price" type="number" min="0" placeholder="100000" class="w-full text-xs pl-7 pr-2 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:border-[#0A2540]" required />
                                </div>
                            </div>
                            <button
                                v-if="unit.pricings.length > 1"
                                type="button"
                                @click="unit.pricings.splice(pIdx, 1)"
                                class="w-6 h-6 rounded-md bg-rose-100 text-rose-500 flex items-center justify-center shrink-0 mt-3 hover:bg-rose-500 hover:text-white transition"
                                title="Hapus Harga"
                            >
                                <i class="fa-solid fa-xmark text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Detail Fields Unit (dinamis dari DB) -->
                <div v-if="unitDetailFields.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div v-for="field in unitDetailFields" :key="field.key">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">{{ field.label }}</label>

                        <select
                            v-if="field.type === 'select'"
                            v-model="unit.detail[field.key]"
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        >
                            <option value="" disabled>Pilih...</option>
                            <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                        </select>

                        <input
                            v-else
                            :type="field.type"
                            v-model="unit.detail[field.key]"
                            :placeholder="field.label"
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-[#0A2540] transition"
                        />
                    </div>
                </div>

                <!-- Fasilitas Unit (dari DB, scope = unit) -->
                <div v-if="unitFacilitiesFromDB.length > 0">
                    <label class="block text-[11px] font-bold text-slate-600 mb-2">Fasilitas Unit</label>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="fac in unitFacilitiesFromDB"
                            :key="fac.id"
                            type="button"
                            @click="emit('toggleUnitFasilitas', unitIndex, fac.id)"
                            class="text-[11px] px-2.5 py-1 rounded-lg border transition cursor-pointer"
                            :class="unit.facility_ids.includes(fac.id)
                                ? 'bg-[#0A2540] text-white border-[#0A2540] font-bold'
                                : 'bg-white text-slate-600 border-slate-200 hover:border-[#0A2540]/40'"
                        >
                            {{ fac.name }}
                        </button>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- THUMBNAIL UNIT (Upload 1 foto) -->
                <!-- ===================================== -->
                <div class="border-t border-slate-100 pt-3 mt-1">
                    <label class="block text-[11px] font-bold text-slate-600 mb-2">
                        <i class="fa-solid fa-image text-[#0A2540] mr-1 text-[10px]"></i>
                        Thumbnail Unit <span class="text-[10px] text-slate-400 font-normal">(Maks. 1 foto sampul)</span>
                    </label>

                    <div v-if="!unit.thumbnail_preview" class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:bg-slate-50 transition cursor-pointer relative">
                        <input
                            type="file"
                            accept="image/png, image/jpeg, image/webp"
                            @change="emit('handleUnitThumbnailUpload', $event, unitIndex)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        />
                        <i class="fa-solid fa-cloud-arrow-up text-xl text-slate-300 mb-2"></i>
                        <p class="text-[10px] font-bold text-slate-500">Klik untuk upload thumbnail</p>
                    </div>
                    <div v-else class="relative w-32 h-24 group/thumb">
                        <img :src="unit.thumbnail_preview" class="w-full h-full object-cover rounded-xl border border-slate-200" />
                        <button
                            type="button"
                            @click="emit('hapusUnitThumbnail', unitIndex)"
                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow opacity-0 group-hover/thumb:opacity-100 transition cursor-pointer"
                            title="Hapus Thumbnail"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- FOTO UNIT (upload per kategori) -->
                <!-- ===================================== -->
                <div v-if="galleryCategoriesFromDB.length > 0" class="border-t border-slate-100 pt-3 mt-1">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[11px] font-bold text-slate-600">
                            <i class="fa-solid fa-images text-[#0A2540] mr-1 text-[10px]"></i>
                            Foto Unit
                        </label>
                        <button
                            type="button"
                            @click="emit('tambahUnitKategoriFoto', unitIndex)"
                            class="text-[10px] font-bold text-[#0A2540] hover:text-[#FFC000] bg-slate-100 px-2.5 py-1 rounded-lg transition cursor-pointer"
                        >
                            + Tambah Kategori
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(photoGroup, photoIdx) in unit.photos"
                            :key="photoGroup._id"
                            class="border border-slate-200 rounded-xl p-3 relative bg-slate-50"
                        >
                            <!-- Hapus grup foto unit -->
                            <button
                                v-if="unit.photos.length > 1"
                                type="button"
                                @click="emit('hapusUnitKategoriFoto', unitIndex, photoIdx)"
                                class="absolute top-2 right-2 w-5 h-5 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-[10px] cursor-pointer"
                                title="Hapus Kategori Foto"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
                                <!-- Pilih Kategori Foto -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Kategori / Area Foto <span class="text-rose-500">*</span></label>
                                    <select
                                        v-model="photoGroup.gallery_category_id"
                                        class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 focus:border-[#0A2540] transition"
                                    >
                                        <option :value="null" disabled>Pilih kategori</option>
                                        <option v-for="gc in galleryCategoriesFromDB" :key="gc.id" :value="gc.id">
                                            {{ gc.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Upload File Foto Unit -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Pilih File Foto</label>
                                    <input
                                        type="file"
                                        multiple
                                        accept="image/png, image/jpeg, image/webp"
                                        @change="emit('handleUnitFileUpload', $event, unitIndex, photoIdx)"
                                        class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-[#0A2540] file:text-white hover:file:bg-[#123e6b] cursor-pointer"
                                    />
                                </div>
                            </div>

                            <!-- Preview foto unit -->
                            <div v-if="photoGroup.previews && photoGroup.previews.length > 0" class="flex flex-wrap gap-1.5 pt-2 border-t border-slate-100">
                                <div
                                    v-for="(preview, fileIdx) in photoGroup.previews"
                                    :key="fileIdx"
                                    class="relative group/photo"
                                >
                                    <img :src="preview" class="w-14 h-14 object-cover rounded-lg border border-slate-200" />
                                    <button
                                        type="button"
                                        @click="emit('hapusUnitFoto', unitIndex, photoIdx, fileIdx)"
                                        class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white rounded-full flex items-center justify-center text-[9px] shadow opacity-0 group-hover/photo:opacity-100 transition cursor-pointer"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-[10px] text-slate-400 mt-1">Belum ada foto diunggah.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
