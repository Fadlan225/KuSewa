<script setup>
import { ref, computed, reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const notifications = ref([
    { id: 1, title: 'Server Maintenance', type: 'Sistem', created: '04 Agu 2026', status: 'Dikirim' },
    { id: 2, title: 'Update Kebijakan', type: 'CMS', created: '02 Agu 2026', status: 'Dijadwalkan' },
    { id: 3, title: 'Promo Baru', type: 'Marketing', created: '30 Jul 2026', status: 'Dikirim' },
]);

const notificationTypes = ['Sistem', 'CMS', 'Marketing', 'Keuangan', 'Keamanan'];
const notificationStatuses = ['Dikirim', 'Dijadwalkan'];

let nextId = 4;

// ==== Statistik ringkas (di kartu atas) ====
const sentCount = computed(() => notifications.value.filter(item => item.status === 'Dikirim').length);
const scheduledCount = computed(() => notifications.value.filter(item => item.status === 'Dijadwalkan').length);
const typeCount = computed(() => new Set(notifications.value.map(item => item.type)).size);

// ==== Form Buat / Edit Notifikasi ====
const showFormModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const formError = ref('');

const form = reactive({
    title: '',
    type: notificationTypes[0],
    created: '',
    status: 'Dijadwalkan',
});

function resetForm() {
    form.title = '';
    form.type = notificationTypes[0];
    form.created = '';
    form.status = 'Dijadwalkan';
    formError = '';
}

function openCreateForm() {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    formError.value = '';
    showFormModal.value = true;
}

function openEditForm(item) {
    isEditing.value = true;
    editingId.value = item.id;
    form.title = item.title;
    form.type = item.type;
    form.created = item.created;
    form.status = item.status;
    formError.value = '';
    showFormModal.value = true;
}

function closeForm() {
    showFormModal.value = false;
}

function submitForm() {
    if (!form.title.trim()) {
        formError.value = 'Judul notifikasi wajib diisi.';
        return;
    }
    if (!form.created.trim()) {
        formError.value = 'Tanggal wajib diisi.';
        return;
    }

    if (isEditing.value) {
        const target = notifications.value.find(item => item.id === editingId.value);
        if (target) {
            target.title = form.title.trim();
            target.type = form.type;
            target.created = form.created.trim();
            target.status = form.status;
        }
    } else {
        notifications.value.unshift({
            id: nextId++,
            title: form.title.trim(),
            type: form.type,
            created: form.created.trim(),
            status: form.status,
        });
    }

    showFormModal.value = false;
}

// ==== Hapus notifikasi ====
const deleteTarget = ref(null);

function askDelete(item) {
    deleteTarget.value = item;
}

function cancelDelete() {
    deleteTarget.value = null;
}

function confirmDelete() {
    notifications.value = notifications.value.filter(item => item.id !== deleteTarget.value.id);
    deleteTarget.value = null;
}

// ==== Kirim sekarang (ubah status Dijadwalkan -> Dikirim) ====
function sendNow(item) {
    item.status = 'Dikirim';
}

// ==== Lihat Semua: modal dengan pencarian & filter ====
const showAllModal = ref(false);
const allSearch = ref('');
const allTypeFilter = ref('Semua');
const allStatusFilter = ref('Semua');

const filteredAll = computed(() => {
    const query = allSearch.value.toLowerCase();
    return notifications.value.filter(item => {
        const matchesQuery = [item.title, item.type, item.created].join(' ').toLowerCase().includes(query);
        const matchesType = allTypeFilter.value === 'Semua' || item.type === allTypeFilter.value;
        const matchesStatus = allStatusFilter.value === 'Semua' || item.status === allStatusFilter.value;
        return matchesQuery && matchesType && matchesStatus;
    });
});

function openAll() {
    allSearch.value = '';
    allTypeFilter.value = 'Semua';
    allStatusFilter.value = 'Semua';
    showAllModal.value = true;
}

function closeAll() {
    showAllModal.value = false;
}
</script>

<template>
    <Head title="Notifikasi Sistem - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-regular fa-bell"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Notifikasi Sistem</h1>
                        <p class="text-xs text-slate-400">Kelola pesan sistem dan jadwal notifikasi ke pengguna.</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="openCreateForm"
                    class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition"
                >
                    Buat Notifikasi
                </button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Terkirim</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ sentCount }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Notifikasi yang sudah dikirim.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Dijadwalkan</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ scheduledCount }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Notifikasi yang akan dikirim.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Jenis Notifikasi</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ typeCount }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Kategori pesan sistem yang tersedia.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Daftar Notifikasi</h2>
                            <p class="text-[11px] text-slate-400">Tampilkan jadwal, tipe, dan status pengiriman.</p>
                        </div>
                        <button
                            type="button"
                            @click="openAll"
                            class="text-[11px] font-semibold text-[#0A2540] hover:underline"
                        >
                            Lihat Semua
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Judul</th>
                                    <th class="py-4 px-4">Tipe</th>
                                    <th class="py-4 px-4">Tanggal</th>
                                    <th class="py-4 px-5">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in notifications" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.title }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.type }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.created }}</td>
                                    <td class="py-4 px-5">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'Dikirim' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'
                                        ]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button
                                                v-if="item.status === 'Dijadwalkan'"
                                                type="button"
                                                @click="sendNow(item)"
                                                class="rounded-full bg-white border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition"
                                            >
                                                Kirim Sekarang
                                            </button>
                                            <button
                                                type="button"
                                                @click="openEditForm(item)"
                                                class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                @click="askDelete(item)"
                                                class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition"
                                                aria-label="Hapus"
                                            >
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="notifications.length === 0">
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        Belum ada notifikasi.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal Buat / Edit Notifikasi -->
        <Teleport to="body">
            <div
                v-if="showFormModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeForm"
            >
                <div class="w-full max-w-md rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ isEditing ? 'Edit Notifikasi' : 'Buat Notifikasi' }}</h3>
                            <p class="text-[11px] text-slate-400">Isi detail pesan yang akan dikirim ke pengguna.</p>
                        </div>
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
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Judul</label>
                            <input
                                type="text"
                                v-model="form.title"
                                placeholder="Contoh: Server Maintenance"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Tipe</label>
                                <select
                                    v-model="form.type"
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                                >
                                    <option v-for="type in notificationTypes" :key="type">{{ type }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Status</label>
                                <select
                                    v-model="form.status"
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                                >
                                    <option v-for="status in notificationStatuses" :key="status">{{ status }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1.5">Tanggal</label>
                            <input
                                type="text"
                                v-model="form.created"
                                placeholder="Contoh: 12 Agu 2026"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                            />
                        </div>
                        <p v-if="formError" class="text-[11px] font-semibold text-rose-600">{{ formError }}</p>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="closeForm"
                            class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitForm"
                            class="rounded-2xl bg-[#0A2540] px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition"
                        >
                            {{ isEditing ? 'Simpan Perubahan' : 'Buat Notifikasi' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Modal Konfirmasi Hapus -->
        <Teleport to="body">
            <div
                v-if="deleteTarget"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="cancelDelete"
            >
                <div class="w-full max-w-sm rounded-3xl bg-white shadow-xl border border-slate-100 p-6">
                    <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg mb-4">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Hapus notifikasi ini?</h3>
                    <p class="text-xs text-slate-500 mt-1.5">
                        "{{ deleteTarget.title }}" akan dihapus permanen dan tidak bisa dikembalikan.
                    </p>
                    <div class="flex justify-end gap-2 mt-6">
                        <button
                            type="button"
                            @click="cancelDelete"
                            class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="confirmDelete"
                            class="rounded-2xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Modal Lihat Semua -->
        <Teleport to="body">
            <div
                v-if="showAllModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
                @click.self="closeAll"
            >
                <div class="w-full max-w-2xl rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden flex flex-col max-h-[80vh]">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Semua Notifikasi</h3>
                            <p class="text-[11px] text-slate-400">Cari dan saring seluruh riwayat notifikasi.</p>
                        </div>
                        <button
                            type="button"
                            @click="closeAll"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            aria-label="Tutup"
                        >
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <div class="px-6 pt-4 shrink-0 flex flex-col sm:flex-row gap-2">
                        <div class="flex items-center gap-3 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60 flex-1">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                            <input
                                type="text"
                                v-model="allSearch"
                                placeholder="Cari judul, tipe, atau tanggal..."
                                class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400 text-slate-700"
                            />
                        </div>
                        <select
                            v-model="allTypeFilter"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                        >
                            <option>Semua</option>
                            <option v-for="type in notificationTypes" :key="type">{{ type }}</option>
                        </select>
                        <select
                            v-model="allStatusFilter"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0A2540]/20"
                        >
                            <option>Semua</option>
                            <option v-for="status in notificationStatuses" :key="status">{{ status }}</option>
                        </select>
                    </div>

                    <div class="p-6 space-y-2 overflow-y-auto">
                        <div
                            v-for="item in filteredAll"
                            :key="item.id"
                            class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 px-4 py-3"
                        >
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-900 truncate">{{ item.title }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ item.type }} · {{ item.created }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span :class="[
                                    'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                    item.status === 'Dikirim' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'
                                ]">
                                    {{ item.status }}
                                </span>
                                <button
                                    type="button"
                                    @click="openEditForm(item)"
                                    class="rounded-full bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition"
                                >
                                    Edit
                                </button>
                            </div>
                        </div>

                        <p v-if="filteredAll.length === 0" class="text-center text-xs text-slate-400 py-8">
                            Tidak ada notifikasi sesuai pencarian atau filter.
                        </p>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end shrink-0">
                        <button
                            type="button"
                            @click="closeAll"
                            class="rounded-2xl bg-[#0A2540] px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>