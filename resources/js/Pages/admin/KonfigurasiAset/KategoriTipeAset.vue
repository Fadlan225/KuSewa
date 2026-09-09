<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { Search, X, Plus, Trash2, Edit2, CheckCircle2, XCircle, FolderOpen, ChevronLeft, ChevronRight, ChevronDown, RotateCcw } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import CustomSelect from '@/Components/ui/CustomSelect.vue';
import ResetIllustration from '@/Components/ui/Icons/ResetIllustration.vue';

const props = defineProps({
    allKategori:  { type: Array, default: () => [] },
    kategoriAset: { type: Object, default: () => ({ data: [] }) },
    jenisAset:    { type: Object, default: () => ({ data: [] }) },
});

const page = usePage();
const activeTab = ref('kategori');

// Unified Popover State
const activePopover = ref(null); // 'add_kategori', 'edit_kategori', 'add_jenis', 'edit_jenis'
const popoverTop = ref(0);
const popoverLeft = ref(0);

const kategoriForm = ref({ id: null, name: '', is_active: true });
const jenisForm = ref({ id: null, name: '', is_active: true, category_id: '', payment_countdown_minutes: 60, allow_units: false, default_rental_unit: 'month' });

const kategoriOptions = computed(() => {
    return props.allKategori.map(cat => ({
        label: cat.name,
        value: cat.id
    }));
});

// Confirm Modal
const showConfirmModal = ref(false);
const confirmData = ref({ type: '', id: null, title: '', message: '' });

// Popover Logic
const closeOnOutsideClick = (e) => {
    if (activePopover.value && !e.target.closest('.popover-container') && !e.target.closest('.trigger-btn')) {
        closePopover();
    }
};

onMounted(() => {
    document.addEventListener('click', closeOnOutsideClick);
});

onUnmounted(() => {
    document.removeEventListener('click', closeOnOutsideClick);
});

const closePopover = () => {
    activePopover.value = null;
};

const openPopover = (type, data = null, event) => {
    activePopover.value = type;

    if (type === 'add_kategori') {
        kategoriForm.value = { id: null, name: '', is_active: true };
    } else if (type === 'edit_kategori') {
        kategoriForm.value = { id: data.id, name: data.name, is_active: data.is_active };
    } else if (type === 'add_jenis') {
        jenisForm.value = { id: null, name: '', is_active: true, category_id: props.allKategori.length > 0 ? props.allKategori[0].id : '', payment_countdown_minutes: 60, allow_units: false, default_rental_unit: 'month' };
    } else if (type === 'edit_jenis') {
        jenisForm.value = { id: data.id, name: data.name, is_active: data.is_active, category_id: data.category_id, payment_countdown_minutes: data.payment_countdown_minutes || 60, allow_units: data.allow_units ? true : false, default_rental_unit: data.default_rental_unit || 'month' };
    }

    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        if (type.startsWith('add')) {
            const popoverHeight = type === 'add_jenis' ? 460 : 300;
            popoverTop.value = Math.max(16, Math.min(rect.bottom + 8, window.innerHeight - popoverHeight));
            popoverLeft.value = Math.max(16, rect.right - 280);
        } else {
            const popoverHeight = type === 'edit_jenis' ? 500 : 320;
            popoverTop.value = Math.max(16, Math.min(rect.top - 10, window.innerHeight - popoverHeight));
            popoverLeft.value = Math.max(16, rect.left - 290);
        }
    }
};

function submitKategori() {
    const isEdit = activePopover.value === 'edit_kategori';
    const routeName = isEdit ? 'admin.kategori-aset.update' : 'admin.kategori-aset.store';
    const routeArgs = isEdit ? route(routeName, kategoriForm.value.id) : route(routeName);
    const method = isEdit ? 'put' : 'post';

    router[method](routeArgs, kategoriForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            closePopover();
        }
    });
}

function deleteKategori(id, name) {
    closePopover();
    confirmData.value = {
        type: 'kategori',
        id: id,
        title: 'Hapus Kategori Aset',
        message: `Yakin ingin menghapus kategori "${name}"?`
    };
    showConfirmModal.value = true;
}

function submitJenis() {
    const isEdit = activePopover.value === 'edit_jenis';
    const routeName = isEdit ? 'admin.jenis-aset.update' : 'admin.jenis-aset.store';
    const routeArgs = isEdit ? route(routeName, jenisForm.value.id) : route(routeName);
    const method = isEdit ? 'put' : 'post';

    router[method](routeArgs, jenisForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            closePopover();
        }
    });
}

function deleteJenis(id, name) {
    closePopover();
    confirmData.value = {
        type: 'jenis',
        id: id,
        title: 'Hapus Tipe Aset',
        message: `Yakin ingin menghapus tipe aset "${name}"? Data yang sudah dihapus tidak dapat dikembalikan.`
    };
    showConfirmModal.value = true;
}

const showResetModal = ref(false);

function resetToDefault() {
    showResetModal.value = true;
}

function executeReset() {
    router.post(route('admin.kategori-aset.reset'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showResetModal.value = false;
        }
    });
}

function getKategoriName(id) {
    const k = props.allKategori.find(cat => cat.id === id);
    return k ? k.name : '-';
}

function executeDelete() {
    if (confirmData.value.type === 'kategori') {
        router.delete(route('admin.kategori-aset.destroy', confirmData.value.id), {
            preserveScroll: true,
            onSuccess: () => showConfirmModal.value = false
        });
    } else if (confirmData.value.type === 'jenis') {
        router.delete(route('admin.jenis-aset.destroy', confirmData.value.id), {
            preserveScroll: true,
            onSuccess: () => showConfirmModal.value = false
        });
    }
}
</script>

<template>
    <Head title="Kategori & Tipe Aset" />

    <DashboardLayout role="Admin" title="Kategori & Tipe Aset" description="Kelola master data Kategori Aset dan jenis tipe di dalamnya.">
        <div class="flex flex-col gap-6">
            <!-- Tabs & Actions -->
            <div class="flex items-center justify-between border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <button
                        @click="activeTab = 'kategori'"
                        class="px-4 py-3 text-sm font-bold transition-colors border-b-2"
                        :class="activeTab === 'kategori' ? 'border-[#FFC000] text-[#0A2540]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        Kategori Aset
                    </button>
                    <button
                        @click="activeTab = 'tipe'"
                        class="px-4 py-3 text-sm font-bold transition-colors border-b-2"
                        :class="activeTab === 'tipe' ? 'border-[#FFC000] text-[#0A2540]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        Tipe Aset
                    </button>
                </div>

                <div class="pb-2 flex items-center gap-2">
                    <button @click="resetToDefault" class="flex items-center justify-center w-10 h-10 shrink-0 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors shadow-sm" title="Kembalikan ke Default">
                        <RotateCcw :size="18" :stroke-width="2.5" />
                    </button>
                    <button v-if="activeTab === 'kategori'" @click="openPopover('add_kategori', null, $event)" class="trigger-btn flex items-center gap-2 px-4 py-2 h-10 bg-[#FFC000] text-[#0A2540] font-bold text-sm rounded-lg hover:bg-amber-400 transition-colors">
                        <Plus :size="16" /> Tambah Kategori
                    </button>
                    <button v-else @click="openPopover('add_jenis', null, $event)" class="trigger-btn flex items-center gap-2 px-4 py-2 h-10 bg-[#FFC000] text-[#0A2540] font-bold text-sm rounded-lg hover:bg-amber-400 transition-colors">
                        <Plus :size="16" /> Tambah Tipe
                    </button>
                </div>
            </div>

            <!-- Tab Content: Kategori -->
            <div v-if="activeTab === 'kategori'" class="flex flex-col gap-4">
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="py-3 px-6">Nama Kategori</th>
                                    <th class="py-3 px-6">Status</th>
                                    <th class="py-3 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="cat in kategoriAset.data" :key="cat.id" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-800">{{ cat.name }}</td>
                                    <td class="py-4 px-6">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold"
                                            :class="cat.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500'">
                                            <CheckCircle2 v-if="cat.is_active" class="w-3 h-3" />
                                            <XCircle v-else class="w-3 h-3" />
                                            {{ cat.is_active ? 'Aktif' : 'Disembunyikan' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <button @click="openPopover('edit_kategori', cat, $event)" class="trigger-btn inline-flex p-1.5 text-slate-400 hover:text-[#0A2540] hover:bg-slate-100 rounded-md transition border border-transparent hover:border-slate-200" title="Aksi">
                                            <ChevronRight class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': activePopover === 'edit_kategori' && kategoriForm.id === cat.id}" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="kategoriAset.data.length === 0">
                                    <td colspan="3" class="py-12 text-center text-slate-400">
                                        <FolderOpen class="w-8 h-8 mb-3 mx-auto text-slate-300" />
                                        Belum ada kategori aset.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination for Kategori -->
                <div v-if="kategoriAset.last_page > 1" class="flex items-center justify-between text-xs text-slate-500 mt-2">
                    <span>Menampilkan {{ kategoriAset.from }}–{{ kategoriAset.to }} dari {{ kategoriAset.total }}</span>
                    <div class="flex items-center gap-1">
                        <Link v-if="kategoriAset.prev_page_url" :href="kategoriAset.prev_page_url" preserve-state class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1">
                            <ChevronLeft class="w-3.5 h-3.5" /> Prev
                        </Link>
                        <span class="px-3 py-1.5 font-semibold text-slate-700">{{ kategoriAset.current_page }} / {{ kategoriAset.last_page }}</span>
                        <Link v-if="kategoriAset.next_page_url" :href="kategoriAset.next_page_url" preserve-state class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1">
                            Next <ChevronRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Tipe -->
            <div v-if="activeTab === 'tipe'" class="flex flex-col gap-4">
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="py-3 px-6">Nama Tipe</th>
                                    <th class="py-3 px-6">Kategori</th>
                                    <th class="py-3 px-6">Status</th>
                                    <th class="py-3 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="jenis in jenisAset.data" :key="jenis.id" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-800">{{ jenis.name }}</td>
                                    <td class="py-4 px-6">
                                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[11px] font-semibold border border-slate-200">
                                            {{ getKategoriName(jenis.category_id) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold"
                                            :class="jenis.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500'">
                                            <CheckCircle2 v-if="jenis.is_active" class="w-3 h-3" />
                                            <XCircle v-else class="w-3 h-3" />
                                            {{ jenis.is_active ? 'Aktif' : 'Disembunyikan' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <button @click="openPopover('edit_jenis', jenis, $event)" class="trigger-btn inline-flex p-1.5 text-slate-400 hover:text-[#0A2540] hover:bg-slate-100 rounded-md transition border border-transparent hover:border-slate-200" title="Aksi">
                                            <ChevronRight class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': activePopover === 'edit_jenis' && jenisForm.id === jenis.id}" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="jenisAset.data.length === 0">
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        <FolderOpen class="w-8 h-8 mb-3 mx-auto text-slate-300" />
                                        Belum ada tipe aset.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination for Tipe -->
                <div v-if="jenisAset.last_page > 1" class="flex items-center justify-between text-xs text-slate-500 mt-2">
                    <span>Menampilkan {{ jenisAset.from }}–{{ jenisAset.to }} dari {{ jenisAset.total }}</span>
                    <div class="flex items-center gap-1">
                        <Link v-if="jenisAset.prev_page_url" :href="jenisAset.prev_page_url" preserve-state class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1">
                            <ChevronLeft class="w-3.5 h-3.5" /> Prev
                        </Link>
                        <span class="px-3 py-1.5 font-semibold text-slate-700">{{ jenisAset.current_page }} / {{ jenisAset.last_page }}</span>
                        <Link v-if="jenisAset.next_page_url" :href="jenisAset.next_page_url" preserve-state class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 transition flex items-center gap-1">
                            Next <ChevronRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>

    <!-- Unified Popover Form -->
    <Transition
        mode="out-in"
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95 -translate-y-2 lg:-translate-x-2 lg:translate-y-0"
        enter-to-class="opacity-100 scale-100 translate-y-0 lg:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100 scale-100 translate-y-0 lg:translate-x-0"
        leave-to-class="opacity-0 scale-95 -translate-y-2 lg:-translate-x-2 lg:translate-y-0"
    >
        <div v-if="activePopover" :key="activePopover" class="fixed inset-0 lg:inset-auto z-[200] flex items-center justify-center lg:block p-4 lg:p-0 pointer-events-none popover-desktop" :style="{ '--popover-top': popoverTop + 'px', '--popover-left': popoverLeft + 'px' }">
            <!-- Mobile backdrop -->
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm lg:hidden pointer-events-auto" @click="closePopover"></div>

            <!-- Popover Content -->
            <div class="relative bg-white rounded-xl shadow-2xl border border-slate-100 w-full max-w-sm lg:w-[280px] flex flex-col pointer-events-auto popover-container" @click.stop>

                <div class="px-4 py-3 flex items-center justify-between border-b border-slate-100 bg-slate-50/80 rounded-t-xl">
                    <h3 class="font-bold text-[#0A2540] text-[13px]">
                        {{ activePopover.includes('kategori') ? (activePopover.startsWith('edit') ? 'Edit Kategori Aset' : 'Tambah Kategori Aset') : (activePopover.startsWith('edit') ? 'Edit Tipe Aset' : 'Tambah Tipe Aset') }}
                    </h3>
                    <button @click="closePopover" class="text-slate-400 hover:text-slate-600 transition bg-white border border-slate-200 rounded-md w-6 h-6 flex items-center justify-center hover:bg-slate-100"><X :size="14" /></button>
                </div>

                <div class="p-4 space-y-3.5">
                    <template v-if="activePopover.includes('kategori')">
                        <div>
                            <label class="text-[11px] uppercase font-bold text-slate-500 mb-1 block">Nama Kategori</label>
                            <input v-model="kategoriForm.name" type="text" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50/50 border border-slate-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000]" placeholder="Contoh : Industri, Rekreasi" />
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer pt-1 group w-fit">
                            <input type="checkbox" :checked="!kategoriForm.is_active" @change="kategoriForm.is_active = !$event.target.checked" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]/30 transition-shadow" />
                            <span class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition-colors">Sembunyikan Kategori</span>
                        </label>
                    </template>
                    <template v-else>
                        <div>
                            <label class="text-[11px] uppercase font-bold text-slate-500 mb-1 block">Kategori Induk</label>
                            <CustomSelect v-model="jenisForm.category_id" :options="kategoriOptions" placeholder="Pilih Kategori..." fullWidth />
                        </div>
                        <div>
                            <label class="text-[11px] uppercase font-bold text-slate-500 mb-1 block">Nama Tipe</label>
                            <input v-model="jenisForm.name" type="text" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50/50 border border-slate-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000]" placeholder="Contoh : Gedung, Homestay" />
                        </div>
                        <div>
                            <label class="text-[11px] uppercase font-bold text-slate-500 mb-1 block">Batas Pembayaran (Menit)</label>
                            <input v-model.number="jenisForm.payment_countdown_minutes" type="number" min="1" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50/50 border border-slate-200 rounded-lg focus:ring-[#FFC000] focus:border-[#FFC000]" placeholder="Contoh : 60" />
                        </div>
                        <div>
                            <label class="text-[11px] uppercase font-bold text-slate-500 mb-1 block">Satuan Harga Dasar</label>
                            <CustomSelect
                                v-model="jenisForm.default_rental_unit"
                                :options="[
                                    { code: 'hour', name: 'Per Jam' },
                                    { code: 'night', name: 'Per Malam' },
                                    { code: 'day', name: 'Per Hari' },
                                    { code: 'week', name: 'Per Minggu' },
                                    { code: 'month', name: 'Per Bulan' },
                                ]"
                                placeholder="Pilih Satuan..."
                                fullWidth
                            />
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer pt-1 group w-fit">
                            <input type="checkbox" v-model="jenisForm.allow_units" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]/30 transition-shadow" />
                            <span class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition-colors">Izinkan Multi Unit</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer pt-1 group w-fit">
                            <input type="checkbox" :checked="!jenisForm.is_active" @change="jenisForm.is_active = !$event.target.checked" class="rounded border-slate-300 text-[#0A2540] focus:ring-[#FFC000]/30 transition-shadow" />
                            <span class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition-colors">Sembunyikan Tipe</span>
                        </label>
                    </template>
                </div>

                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/80 rounded-b-xl flex flex-col gap-2">
                    <button v-if="activePopover === 'edit_kategori'" @click="deleteKategori(kategoriForm.id, kategoriForm.name)" class="w-full flex items-center justify-center gap-2 px-3 py-2 border border-rose-200 bg-white text-rose-600 font-bold text-xs rounded-lg hover:bg-rose-50 hover:border-rose-300 transition-colors">
                        <Trash2 :size="14" /> Hapus Kategori
                    </button>
                    <button v-if="activePopover === 'edit_jenis'" @click="deleteJenis(jenisForm.id, jenisForm.name)" class="w-full flex items-center justify-center gap-2 px-3 py-2 border border-rose-200 bg-white text-rose-600 font-bold text-xs rounded-lg hover:bg-rose-50 hover:border-rose-300 transition-colors">
                        <Trash2 :size="14" /> Hapus Tipe
                    </button>
                    <button @click="activePopover.includes('kategori') ? submitKategori() : submitJenis()" :disabled="activePopover.includes('kategori') ? !kategoriForm.name : (!jenisForm.name || !jenisForm.category_id)" class="w-full px-3 py-2.5 text-xs font-bold text-[#0A2540] bg-[#FFC000] hover:bg-amber-400 rounded-lg disabled:opacity-50 transition-colors text-center shadow-sm">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Confirm Modal -->
    <ConfirmModal
        :show="showConfirmModal"
        :title="confirmData.title"
        :message="confirmData.message"
        @confirm="executeDelete"
        @cancel="showConfirmModal = false"
    />

    <!-- Reset Confirmation Modal -->
    <ConfirmModal
        :show="showResetModal"
        type="primary"
        title="Reset ke Default"
        message="Yakin ingin mereset Kategori & Tipe Aset ke pengaturan default (bawaan)? Data bawaan yang terhapus akan dikembalikan."
        confirmText="Ya, Reset"
        cancelText="Batal"
        @confirm="executeReset"
        @cancel="showResetModal = false"
    >
        <template #icon>
            <div class="w-32 h-32 mx-auto mb-2 flex items-center justify-center">
                <ResetIllustration />
            </div>
        </template>
    </ConfirmModal>
</template>

<style scoped>
@media (min-width: 1024px) {
  .popover-desktop {
    top: var(--popover-top) !important;
    left: var(--popover-left) !important;
    bottom: auto !important;
    right: auto !important;
  }
}
</style>
