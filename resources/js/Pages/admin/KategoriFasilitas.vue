<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

/* ================= STATE DASAR ================= */
const searchQuery = ref('');

// Grup konten: 'Aset' atau 'Fasilitas'
const contentGroup = ref('Aset');
const groups = ['Aset', 'Fasilitas'];

// Level: 'Kategori' (level atas) atau 'Jenis' (turunan dari kategori)
const levelTab = ref('Kategori');
const levels = ['Kategori', 'Jenis'];

/* ================= DATA: ASET ================= */
// Kategori Aset = pengelompokan besar (misal: Hunian Jangka Pendek, Komersial, dst)
const kategoriAset = ref([
    { id: 1, name: 'Hunian Jangka Pendek', description: 'Properti untuk sewa harian atau bulanan.', active: true },
    { id: 2, name: 'Hunian Jangka Panjang', description: 'Properti untuk sewa tahunan atau milik pribadi.', active: true },
    { id: 3, name: 'Komersial', description: 'Properti untuk kegiatan usaha.', active: false },
]);

// Jenis Aset = turunan dari Kategori Aset (misal: Kost, Apartemen, Ruko, Villa)
const jenisAset = ref([
    { id: 1, kategoriId: 1, name: 'Kost', description: 'Ruangan untuk kos harian atau bulanan.', active: true },
    { id: 2, kategoriId: 2, name: 'Apartemen', description: 'Unit apartemen dengan fasilitas lengkap.', active: true },
    { id: 3, kategoriId: 3, name: 'Ruko', description: 'Ruko untuk usaha dan hunian.', active: false },
    { id: 4, kategoriId: 2, name: 'Villa', description: 'Hunian villa untuk liburan.', active: true },
]);

/* ================= DATA: FASILITAS ================= */
// Kategori Fasilitas = pengelompokan besar fasilitas (misal: Gratis, Kamar Tidur, Umum, dst)
const kategoriFasilitas = ref([
    { id: 1, name: 'Gratis', description: 'Fasilitas yang tidak dikenakan biaya tambahan.', active: true },
    { id: 2, name: 'Kamar Tidur', description: 'Fasilitas yang tersedia di dalam kamar.', active: true },
    { id: 3, name: 'Umum', description: 'Fasilitas yang dapat digunakan bersama penghuni.', active: false },
    { id: 4, name: 'Terbuka', description: 'Fasilitas area luar ruangan.', active: true },
    { id: 5, name: 'Komunitas', description: 'Fasilitas yang digunakan bersama penghuni lain.', active: true },
]);

// Jenis Fasilitas = turunan dari Kategori Fasilitas (misal: WiFi, AC, Kolam Renang, dst)
const jenisFasilitas = ref([
    { id: 1, kategoriId: 1, name: 'WiFi', description: 'Akses internet nirkabel.', active: true },
    { id: 2, kategoriId: 2, name: 'AC', description: 'Pendingin udara dalam kamar.', active: true },
    { id: 3, kategoriId: 3, name: 'Kolam Renang', description: 'Kolam renang untuk penghuni.', active: false },
    { id: 4, kategoriId: 4, name: 'Parkir', description: 'Area parkir kendaraan.', active: true },
    { id: 5, kategoriId: 5, name: 'Dapur Bersama', description: 'Dapur yang dapat digunakan bersama.', active: true },
]);

/* ================= HELPERS ================= */
function nextId(list) {
    return list.length ? Math.max(...list.map((i) => i.id)) + 1 : 1;
}

// List kategori & jenis aktif sesuai grup yang sedang dipilih
const currentKategoriList = computed(() =>
    contentGroup.value === 'Aset' ? kategoriAset.value : kategoriFasilitas.value
);
const currentJenisList = computed(() =>
    contentGroup.value === 'Aset' ? jenisAset.value : jenisFasilitas.value
);

// List yang sedang ditampilkan di tabel utama, sesuai level (Kategori/Jenis)
const currentList = computed(() =>
    levelTab.value === 'Kategori' ? currentKategoriList.value : currentJenisList.value
);

function parentName(kategoriId) {
    const found = currentKategoriList.value.find((k) => k.id === kategoriId);
    return found ? found.name : '—';
}

const kategoriLabel = computed(() => `Kategori ${contentGroup.value}`);
const jenisLabel = computed(() => `Jenis ${contentGroup.value}`);
const levelLabel = computed(() => (levelTab.value === 'Kategori' ? kategoriLabel.value : jenisLabel.value));

/* ================= FILTER PENCARIAN ================= */
const filteredItems = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return currentList.value.filter((item) => {
        const parts = [item.name, item.description];
        if (levelTab.value === 'Jenis') parts.push(parentName(item.kategoriId));
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
            label: `Total ${jenisLabel.value}`,
            value: jCount,
            desc: `Jenis ${contentGroup.value.toLowerCase()} yang dapat dipilih.`,
        },
        {
            label: 'Sesuai Pencarian',
            value: filteredItems.value.length,
            desc: `${levelLabel.value} sesuai pencarian saat ini.`,
        },
    ];
});

/* ================= FORM TAMBAH / EDIT ================= */
const showFormModal = ref(false);
const formMode = ref('add'); // 'add' | 'edit'
const formData = ref({ id: null, name: '', description: '', kategoriId: null, active: true });

const addButtonLabel = computed(() => `Tambah ${levelLabel.value}`);
const canAddJenis = computed(() => levelTab.value !== 'Jenis' || currentKategoriList.value.length > 0);

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

    const targetList = levelTab.value === 'Kategori' ? currentKategoriList.value : currentJenisList.value;

    if (formMode.value === 'add') {
        const newItem = {
            id: nextId(targetList),
            name: formData.value.name.trim(),
            description: formData.value.description.trim(),
            active: formData.value.active,
        };
        if (levelTab.value === 'Jenis') newItem.kategoriId = formData.value.kategoriId;
        targetList.push(newItem);
    } else {
        const idx = targetList.findIndex((i) => i.id === formData.value.id);
        if (idx !== -1) {
            targetList[idx] = {
                ...targetList[idx],
                name: formData.value.name.trim(),
                description: formData.value.description.trim(),
                active: formData.value.active,
                ...(levelTab.value === 'Jenis' ? { kategoriId: formData.value.kategoriId } : {}),
            };
        }
    }
    closeForm();
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
    item.active = !item.active;
}

function removeItem(item) {
    if (levelTab.value === 'Kategori') {
        const children = currentJenisList.value.filter((j) => j.kategoriId === item.id);
        if (children.length > 0) {
            alert(
                `Tidak dapat menghapus "${item.name}" karena masih memiliki ${children.length} ${jenisLabel.value.toLowerCase()} terkait. Hapus atau pindahkan item tersebut terlebih dahulu.`
            );
            return;
        }
    }
    const list = levelTab.value === 'Kategori' ? currentKategoriList.value : currentJenisList.value;
    const idx = list.findIndex((i) => i.id === item.id);
    if (idx !== -1) list.splice(idx, 1);
}
</script>

<template>
    <Head title="Kategori & Fasilitas - Admin Panel" />

    <DashboardLayout role="Admin" title="Kategori & Fasilitas" description="Kelola master data kategori & jenis aset, serta kategori & jenis fasilitas.">
        <template #header-actions>
            <div class="flex items-center gap-3 w-64 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                <input
                    type="text"
                    v-model="searchQuery"
                    :placeholder="`Cari ${levelLabel.toLowerCase()}...`"
                    class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                />
            </div>
            <button
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

                <p v-if="levelTab === 'Jenis' && currentKategoriList.length === 0" class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3">
                    Belum ada {{ kategoriLabel.toLowerCase() }}. Tambahkan {{ kategoriLabel.toLowerCase() }} terlebih dahulu sebelum menambahkan {{ jenisLabel.toLowerCase() }}.
                </p>

                <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar {{ levelLabel }}</h2>
                            <p class="text-[11px] text-slate-400">Atur nama, deskripsi, dan status aktif untuk {{ levelLabel.toLowerCase() }}.</p>
                        </div>
                        <button
                            type="button"
                            @click="openManage"
                            class="text-[11px] font-semibold text-[#0A2540] hover:underline"
                        >
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
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ item.active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button
                                            @click="openEditForm(item)"
                                            class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition"
                                        >
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
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="text-[11px] font-semibold text-slate-500">Nama {{ levelLabel }}</label>
                            <input
                                type="text"
                                v-model="formData.name"
                                :placeholder="`Contoh: ${levelTab === 'Kategori' ? 'Hunian Jangka Pendek' : 'Kost'}`"
                                class="mt-1.5 w-full text-xs bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                            />
                        </div>

                        <div v-if="levelTab === 'Jenis'">
                            <label class="text-[11px] font-semibold text-slate-500">{{ kategoriLabel }}</label>
                            <select
                                v-model="formData.kategoriId"
                                class="mt-1.5 w-full text-xs bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
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
                                class="mt-1.5 w-full text-xs bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20 resize-none"
                            ></textarea>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="formData.active" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#0A2540]/30" />
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
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <div class="px-6 pt-4 shrink-0">
                        <div class="flex items-center gap-3 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
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
                                    <i class="fa-solid fa-pen text-[11px]"></i>
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
                                    <i class="fa-solid fa-trash text-[11px]"></i>
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
    </DashboardLayout>
</template>
