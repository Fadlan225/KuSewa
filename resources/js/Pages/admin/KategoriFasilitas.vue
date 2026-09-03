<script setup>
import { Search, X, Pen, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

/* ================= INERTIA PROPS ================= */
const props = defineProps({
    kategoriAset:      { type: Array, default: () => [] },
    jenisAset:         { type: Array, default: () => [] },
    kategoriFasilitas: { type: Array, default: () => [] },
    tipeAsetMandatory: { type: Array, default: () => [] }, // { id, name, description, mandatory_ids[], mandatory_names[] }
});

const page = usePage();

/* ================= STATE DASAR ================= */
const searchQuery = ref('');

// Grup konten: 'Aset' atau 'Fasilitas'
const contentGroup = ref('Aset');
const groups = ['Aset', 'Fasilitas'];

// Level: 'Kategori' (level atas) atau 'Jenis' (turunan dari kategori)
const levelTab = ref('Kategori');
const levels = ['Kategori', 'Jenis'];

/* ================= NORMALISASI DATA ================= */
// Backend pakai `is_active`, frontend pakai `active` — kita buat computed yang normalize
const normalizeKategoriAset = computed(() =>
    props.kategoriAset.map(k => ({ ...k, active: k.is_active, kategoriId: null }))
);
const normalizeJenisAset = computed(() =>
    props.jenisAset.map(j => ({ ...j, active: j.is_active, kategoriId: j.category_id }))
);
const normalizeKategoriFasilitas = computed(() =>
    props.kategoriFasilitas.map(k => ({ ...k, active: k.is_active, description: k.description ?? '', kategoriId: null }))
);
// Mode Fasilitas > Jenis = tipe aset + kategori wajib, bukan list facility
const normalizeTipeAsetMandatory = computed(() =>
    props.tipeAsetMandatory.map(t => ({ ...t, active: true, description: t.description ?? '', kategoriId: null }))
);

/* ================= HELPERS ================= */
// List kategori & jenis aktif sesuai grup yang sedang dipilih
const currentKategoriList = computed(() =>
    contentGroup.value === 'Aset' ? normalizeKategoriAset.value : normalizeKategoriFasilitas.value
);
const currentJenisList = computed(() => {
    if (contentGroup.value === 'Aset') return normalizeJenisAset.value;
    // Fasilitas > Jenis = tipe aset (untuk set kategori wajib)
    return normalizeTipeAsetMandatory.value;
});

// Apakah sedang di mode Kategori Wajib (Fasilitas > Jenis)
const isMandatoryMode = computed(() => contentGroup.value === 'Fasilitas' && levelTab.value === 'Jenis');

// List yang sedang ditampilkan di tabel utama, sesuai level (Kategori/Jenis)
const currentList = computed(() =>
    levelTab.value === 'Kategori' ? currentKategoriList.value : currentJenisList.value
);

function parentName(kategoriId) {
    const found = currentKategoriList.value.find((k) => k.id === kategoriId);
    return found ? found.name : '—';
}

const kategoriLabel = computed(() => `Kategori ${contentGroup.value}`);
const jenisLabel = computed(() => isMandatoryMode.value ? 'Kategori Wajib per Tipe Aset' : `Jenis ${contentGroup.value}`);
const levelLabel = computed(() => (levelTab.value === 'Kategori' ? kategoriLabel.value : jenisLabel.value));

/* ================= FILTER PENCARIAN ================= */
const filteredItems = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return currentList.value.filter((item) => {
        const parts = [item.name, item.description ?? ''];
        if (levelTab.value === 'Jenis' && !isMandatoryMode.value) parts.push(parentName(item.kategoriId));
        return parts.join(' ').toLowerCase().includes(query);
    });
});

/* ================= STAT CARDS ================= */
const statCards = computed(() => {
    const kCount = currentKategoriList.value.length;
    const jCount = currentJenisList.value.length;
    return [
        {
            label: `Total ${kategoriLabel.value}`,
            value: kCount,
            desc: `Kategori ${contentGroup.value.toLowerCase()} yang tersedia.`,
        },
        {
            label: isMandatoryMode.value ? 'Total Tipe Aset' : `Total ${jenisLabel.value}`,
            value: jCount,
            desc: isMandatoryMode.value ? 'Tipe aset yang dapat diatur kategori wajibnya.' : `Jenis ${contentGroup.value.toLowerCase()} yang dapat dipilih.`,
        },
        {
            label: 'Sesuai Pencarian',
            value: filteredItems.value.length,
            desc: `${levelLabel.value} sesuai pencarian saat ini.`,
        },
    ];
});

/* ================= FORM TAMBAH / EDIT (hanya untuk Aset & Kategori Fasilitas) ================= */
const showFormModal = ref(false);
const formMode = ref('add'); // 'add' | 'edit'
const formData = ref({ id: null, name: '', description: '', kategoriId: null, active: true });

// Hanya tampilkan tombol Tambah jika bukan mode mandatory
const addButtonLabel = computed(() => isMandatoryMode.value ? '' : `Tambah ${levelLabel.value}`);
const canAddJenis = computed(() => {
    if (isMandatoryMode.value) return false; // mode mandatory tidak ada tambah item
    return levelTab.value !== 'Jenis' || currentKategoriList.value.length > 0;
});

function openAddForm() {
    if (!canAddJenis.value) return;
    formMode.value = 'add';
    formData.value = {
        id: null,
        name: '',
        description: '',
        kategoriId: currentKategoriList.value[0]?.id ?? null,
        active: true,
    };
    showFormModal.value = true;
}

function openEditForm(item) {
    formMode.value = 'edit';
    formData.value = { ...item };
    showFormModal.value = true;
}

function closeForm() {
    showFormModal.value = false;
}

const isFormValid = computed(() => {
    if (!formData.value.name.trim()) return false;
    if (levelTab.value === 'Jenis' && !formData.value.kategoriId) return false;
    return true;
});

function submitForm() {
    if (!isFormValid.value) return;

    // Siapkan payload sesuai grup & level
    const isAset = contentGroup.value === 'Aset';
    const isKategori = levelTab.value === 'Kategori';
    const isAdd = formMode.value === 'add';

    let routeName, payload;

    if (isAset && isKategori) {
        routeName = isAdd ? 'admin.kategori-aset.store' : 'admin.kategori-aset.update';
        payload   = { name: formData.value.name.trim(), description: formData.value.description?.trim() ?? '', is_active: formData.value.active };
    } else if (isAset && !isKategori) {
        routeName = isAdd ? 'admin.jenis-aset.store' : 'admin.jenis-aset.update';
        payload   = { name: formData.value.name.trim(), description: formData.value.description?.trim() ?? '', is_active: formData.value.active, category_id: formData.value.kategoriId };
    } else if (!isAset && isKategori) {
        routeName = isAdd ? 'admin.kategori-fas.store' : 'admin.kategori-fas.update';
        payload   = { name: formData.value.name.trim(), is_active: formData.value.active };
    } else {
        routeName = isAdd ? 'admin.jenis-fas.store' : 'admin.jenis-fas.update';
        payload   = { name: formData.value.name.trim(), description: formData.value.description?.trim() ?? '', is_active: formData.value.active, facility_category_id: formData.value.kategoriId };
    }

    const routeArgs = isAdd ? route(routeName) : route(routeName, formData.value.id);
    const method    = isAdd ? 'post' : 'put';

    router[method](routeArgs, payload, {
        preserveScroll: true,
        onSuccess: () => closeForm(),
    });
}

/* ================= KELOLA SEMUA ================= */
const showManageModal = ref(false);
const manageSearch = ref('');

const manageList = computed(() => {
    const query = manageSearch.value.toLowerCase();
    if (!query) return currentList.value;
    return currentList.value.filter((item) => {
        const parts = [item.name, item.description];
        if (levelTab.value === 'Jenis') parts.push(parentName(item.kategoriId));
        return parts.join(' ').toLowerCase().includes(query);
    });
});

function openManage() {
    manageSearch.value = '';
    showManageModal.value = true;
}

function closeManage() {
    showManageModal.value = false;
}

function toggleActive(item) {
    const isAset    = contentGroup.value === 'Aset';
    const isKategori = levelTab.value === 'Kategori';

    let routeName;
    if (isAset && isKategori)       routeName = 'admin.kategori-aset.toggle';
    else if (isAset && !isKategori) routeName = 'admin.jenis-aset.toggle';
    else if (!isAset && isKategori) routeName = 'admin.kategori-fas.toggle';
    else return; // mandatory mode: tidak ada toggle

    router.patch(route(routeName, item.id), {}, { preserveScroll: true });
}

function removeItem(item) {
    const isAset    = contentGroup.value === 'Aset';
    const isKategori = levelTab.value === 'Kategori';

    if (!isKategori && !isAset) return; // mandatory mode: tidak ada hapus langsung

    let routeName;
    if (isAset && isKategori)       routeName = 'admin.kategori-aset.destroy';
    else if (isAset && !isKategori) routeName = 'admin.jenis-aset.destroy';
    else if (!isAset && isKategori) routeName = 'admin.kategori-fas.destroy';

    router.delete(route(routeName, item.id), { preserveScroll: true });
}

/* ================= MODAL KATEGORI WAJIB ================= */
const showMandatoryModal = ref(false);
const mandatoryTarget = ref(null);        // tipe aset yang sedang diedit
const mandatorySelected = ref([]);        // array of facility_category_id yang dipilih

function openMandatoryModal(tipeAset) {
    mandatoryTarget.value = tipeAset;
    // Ambil current mandatory_ids dari props
    const found = props.tipeAsetMandatory.find(t => t.id === tipeAset.id);
    mandatorySelected.value = found ? [...found.mandatory_ids] : [];
    showMandatoryModal.value = true;
}

function closeMandatoryModal() {
    showMandatoryModal.value = false;
    mandatoryTarget.value = null;
}

function toggleMandatoryCategory(catId) {
    const idx = mandatorySelected.value.indexOf(catId);
    if (idx === -1) mandatorySelected.value.push(catId);
    else mandatorySelected.value.splice(idx, 1);
}

function saveMandatoryCategories() {
    if (!mandatoryTarget.value) return;
    router.put(
        route('admin.mandatory-fas.sync', mandatoryTarget.value.id),
        { facility_category_ids: mandatorySelected.value },
        { preserveScroll: true, onSuccess: () => closeMandatoryModal() }
    );
}
</script>

<template>
    <Head title="Kategori & Fasilitas - Admin Panel" />

    <DashboardLayout role="Admin" title="Kategori & Fasilitas" description="Kelola master data kategori & jenis aset, serta kategori & jenis fasilitas.">
        <template #header-actions>
            <div class="flex items-center gap-3 w-64 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                <Search class="text-slate-400 text-xs" />
                <input
                    type="text"
                    v-model="searchQuery"
                    :placeholder="`Cari ${isMandatoryMode ? 'tipe aset...' : levelLabel.toLowerCase() + '...'}`"
                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                />
            </div>
            <button
                v-if="!isMandatoryMode"
                @click="openAddForm"
                :disabled="!canAddJenis"
                :class="[
                    'rounded-2xl px-4 py-2.5 text-xs font-bold transition',
                    canAddJenis ? 'bg-[#0A2540] text-white hover:bg-slate-900' : 'bg-slate-200 text-slate-400 cursor-not-allowed'
                ]"
            >
                {{ addButtonLabel }}
            </button>
        </template>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <!-- Flash messages -->
                <div v-if="page.props.flash?.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ page.props.flash.error }}
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-4">

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <!-- Pilih grup: Aset / Fasilitas -->
                        <div class="flex items-center gap-2 bg-white p-1 rounded-xl border border-slate-200/80 text-xs shadow-xs">
                            <button
                                v-for="group in groups"
                                :key="group"
                                @click="contentGroup = group"
                                :class="[
                                    'px-3.5 py-1.5 rounded-lg font-semibold transition',
                                    contentGroup === group ? 'bg-slate-900 text-[#FFC000] shadow-xs' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'
                                ]"
                            >
                                {{ group }}
                            </button>
                        </div>

                        <!-- Pilih level: Kategori / Jenis -->
                        <div class="flex items-center gap-2 bg-white p-1 rounded-xl border border-slate-200/80 text-xs shadow-xs">
                            <button
                                v-for="level in levels"
                                :key="level"
                                @click="levelTab = level"
                                :class="[
                                    'px-3.5 py-1.5 rounded-lg font-semibold transition',
                                    levelTab === level ? 'bg-[#0A2540] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'
                                ]"
                            >
                                {{ level }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div v-for="card in statCards" :key="card.label" class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">{{ card.label }}</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ card.value }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">{{ card.desc }}</p>
                    </div>
                </div>

                <p v-if="levelTab === 'Jenis' && !isMandatoryMode && currentKategoriList.length === 0" class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3">
                    Belum ada {{ kategoriLabel.toLowerCase() }}. Tambahkan {{ kategoriLabel.toLowerCase() }} terlebih dahulu sebelum menambahkan {{ jenisLabel.toLowerCase() }}.
                </p>

                <!-- ────── MODE NORMAL: Kategori / Jenis Aset & Kategori Fasilitas ────── -->
                <section v-if="!isMandatoryMode" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar {{ levelLabel }}</h2>
                            <p class="text-[11px] text-slate-400">Atur nama, deskripsi, dan status aktif untuk {{ levelLabel.toLowerCase() }}.</p>
                        </div>
                        <button type="button" @click="openManage" class="text-[11px] font-semibold text-[#0A2540] hover:underline">
                            Kelola Semua
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Nama</th>
                                    <th class="py-4 px-4" v-if="levelTab === 'Jenis'">{{ kategoriLabel }}</th>
                                    <th class="py-4 px-4">Deskripsi</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.name }}</td>
                                    <td v-if="levelTab === 'Jenis'" class="py-4 px-4 text-slate-600">{{ parentName(item.kategoriId) }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.description }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold', item.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200']">
                                            {{ item.active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button @click="openEditForm(item)" class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredItems.length === 0">
                                    <td :colspan="levelTab === 'Jenis' ? 5 : 4" class="py-12 text-center text-slate-400">
                                        Tidak ada {{ levelLabel.toLowerCase() }} sesuai pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ────── MODE MANDATORY: Kategori Wajib per Tipe Aset ────── -->
                <section v-if="isMandatoryMode" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h2 class="text-sm font-bold text-slate-900">Kategori Fasilitas Wajib per Tipe Aset</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik "Atur" untuk memilih kategori fasilitas yang wajib diisi saat owner mendaftarkan aset tipe tersebut.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Tipe Aset</th>
                                    <th class="py-4 px-4">Kategori Wajib</th>
                                    <th class="py-4 px-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="tipe in filteredItems" :key="tipe.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900 w-40">{{ tipe.name }}</td>
                                    <td class="py-4 px-4">
                                        <template v-if="tipe.mandatory_names && tipe.mandatory_names.length">
                                            <span
                                                v-for="cat in tipe.mandatory_names"
                                                :key="cat"
                                                class="inline-flex mr-1.5 mb-1 items-center rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[10px] font-semibold text-blue-700"
                                            >{{ cat }}</span>
                                        </template>
                                        <span v-else class="text-slate-400 italic">Belum diatur</span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button
                                            @click="openMandatoryModal(tipe)"
                                            class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition"
                                        >
                                            Atur
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredItems.length === 0">
                                    <td colspan="3" class="py-12 text-center text-slate-400">Tidak ada tipe aset sesuai pencarian.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>


        <!-- Modal Tambah / Edit -->
        <Teleport to="body">
            <div
                v-if="showFormModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeForm"
            >
                <div class="w-full max-w-md rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">
                            {{ formMode === 'add' ? 'Tambah' : 'Edit' }} {{ levelLabel }}
                        </h3>
                        <button
                            type="button"
                            @click="closeForm"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <X class="text-sm" />
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="text-[11px] font-semibold text-slate-500">Nama {{ levelLabel }}</label>
                            <input
                                type="text"
                                v-model="formData.name"
                                :placeholder="`Contoh: ${levelTab === 'Kategori' ? 'Hunian Jangka Pendek' : 'Kost'}`"
                                class="mt-1.5 w-full text-xs bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20"
                            />
                        </div>

                        <div v-if="levelTab === 'Jenis'">
                            <label class="text-[11px] font-semibold text-slate-500">{{ kategoriLabel }}</label>
                            <select
                                v-model="formData.kategoriId"
                                class="mt-1.5 w-full text-xs bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20"
                            >
                                <option v-for="k in currentKategoriList" :key="k.id" :value="k.id">{{ k.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-[11px] font-semibold text-slate-500">Deskripsi</label>
                            <textarea
                                v-model="formData.description"
                                rows="3"
                                placeholder="Deskripsi singkat..."
                                class="mt-1.5 w-full text-xs bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FFC000]/20 resize-none"
                            ></textarea>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="formData.active" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]/30" />
                            <span class="text-xs font-medium text-slate-600">Aktifkan item ini</span>
                        </label>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="closeForm"
                            class="rounded-2xl px-4 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitForm"
                            :disabled="!isFormValid"
                            :class="[
                                'rounded-2xl px-4 py-2 text-xs font-bold text-white transition',
                                isFormValid ? 'bg-[#0A2540] hover:bg-slate-900' : 'bg-slate-300 cursor-not-allowed'
                            ]"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Modal Kelola Semua -->
        <Teleport to="body">
            <div
                v-if="showManageModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeManage"
            >
                <div class="w-full max-w-xl rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden flex flex-col max-h-[80vh]">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Kelola Semua {{ levelLabel }}</h3>
                            <p class="text-[11px] text-slate-400">Ubah status aktif atau hapus item dari daftar {{ levelLabel.toLowerCase() }}.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeManage"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <X class="text-sm" />
                        </button>
                    </div>

                    <div class="px-6 pt-4 shrink-0">
                        <div class="flex items-center gap-3 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                            <Search class="text-slate-400 text-xs" />
                            <input
                                type="text"
                                v-model="manageSearch"
                                :placeholder="`Cari ${levelLabel.toLowerCase()}...`"
                                class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                            />
                        </div>
                    </div>

                    <div class="p-6 space-y-2 overflow-y-auto">
                        <div
                            v-for="item in manageList"
                            :key="item.id"
                            class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 px-4 py-3"
                        >
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-900 truncate">{{ item.name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">
                                    <span v-if="levelTab === 'Jenis'">{{ parentName(item.kategoriId) }} · </span>{{ item.description }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    type="button"
                                    @click="openEditForm(item)"
                                    class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition"
                                    aria-label="Edit"
                                >
                                    <Pen class="text-[11px]" />
                                </button>
                                <button
                                    type="button"
                                    @click="toggleActive(item)"
                                    :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold transition',
                                        item.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'
                                    ]"
                                >
                                    {{ item.active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                                <button
                                    type="button"
                                    @click="removeItem(item)"
                                    class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition"
                                    aria-label="Hapus"
                                >
                                    <Trash2 class="text-[11px]" />
                                </button>
                            </div>
                        </div>

                        <p v-if="manageList.length === 0" class="text-center text-xs text-slate-400 py-8">
                            Tidak ada {{ levelLabel.toLowerCase() }} sesuai pencarian.
                        </p>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end shrink-0">
                        <button
                            type="button"
                            @click="closeManage"
                            class="rounded-2xl bg-[#0A2540] px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition"
                        >
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Modal Set Kategori Wajib per Tipe Aset -->
        <Teleport to="body">
            <div
                v-if="showMandatoryModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeMandatoryModal"
            >
                <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden flex flex-col max-h-[80vh]">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Kategori Wajib — {{ mandatoryTarget?.name }}</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Centang kategori fasilitas yang wajib dipilih untuk tipe aset ini.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeMandatoryModal"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <X class="text-sm" />
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto">
                        <p v-if="!kategoriFasilitas.length" class="text-xs text-slate-400 text-center py-6">
                            Belum ada kategori fasilitas. Tambahkan dulu di tab Kategori Fasilitas.
                        </p>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label
                                v-for="cat in normalizeKategoriFasilitas"
                                :key="cat.id"
                                class="flex items-center gap-3 px-3.5 py-3 rounded-xl border border-slate-200/80 cursor-pointer hover:bg-slate-50 transition"
                                :class="mandatorySelected.includes(cat.id) ? 'border-[#0A2540] bg-blue-50/40' : ''"
                            >
                                <input
                                    type="checkbox"
                                    :value="cat.id"
                                    :checked="mandatorySelected.includes(cat.id)"
                                    @change="toggleMandatoryCategory(cat.id)"
                                    class="rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]/30"
                                />
                                <span class="text-xs font-semibold text-slate-700">{{ cat.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between shrink-0">
                        <span class="text-[11px] text-slate-400">{{ mandatorySelected.length }} kategori dipilih</span>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="closeMandatoryModal"
                                class="rounded-2xl px-4 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 transition"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="saveMandatoryCategories"
                                class="rounded-2xl bg-[#0A2540] px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition"
                            >
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </DashboardLayout>
</template>
