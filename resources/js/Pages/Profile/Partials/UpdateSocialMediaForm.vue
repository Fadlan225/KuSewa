<script setup>
import { ref, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { Plus, Trash2, GripVertical, ChevronRight, X, Loader2, Save } from 'lucide-vue-next';
import draggable from 'vuedraggable';
import axios from 'axios';

const page = usePage();
const originalLinks = Object.values(page.props.socialLinks || {});
const localLinks = ref([...originalLinks].sort((a, b) => a.order - b.order));

watch(() => page.props.socialLinks, (newLinks) => {
    localLinks.value = Object.values(newLinks || {}).sort((a, b) => a.order - b.order);
}, { deep: true });

// ── Drag & Drop ─────────────────────────────────────────────────────────────
let isDragging = false;
const onDragStart = () => { isDragging = true; };
const onDragEnd = () => {
    localLinks.value.forEach((link, index) => { link.order = index + 1; });
    axios.post(route('profile.social-media.reorder'), {
        links: localLinks.value.map(link => ({ id: link.id, order: link.order }))
    }).catch(err => console.error('Reorder failed:', err));
    setTimeout(() => { isDragging = false; }, 100);
};

// ── Tambah Form ───────────────────────────────────────────────────────────────
const form = useForm({ platform_name: '', url: '' });

// ── Edit State ────────────────────────────────────────────────────────────────
const openPopoverId = ref(null);
const editingLink   = ref(null);

// Field edit (plain refs, bukan useForm, agar watch bisa trigger debounce)
const editName      = ref('');
const editUrl       = ref('');
const editIsActive  = ref(true);

// Status simpan otomatis
const saveStatus    = ref('idle'); // 'idle' | 'saving' | 'saved' | 'error'
let debounceTimer   = null;

// ── Auto-save logika ─────────────────────────────────────────────────────────
const doSave = () => {
    if (!editingLink.value) return;
    saveStatus.value = 'saving';
    router.put(route('profile.social-media.update', editingLink.value.id), {
        platform_name : editName.value,
        url           : editUrl.value,
        is_active     : editIsActive.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            saveStatus.value = 'saved';
            setTimeout(() => { saveStatus.value = 'idle'; }, 2000);
        },
        onError: () => {
            saveStatus.value = 'error';
        }
    });
};

// ── Deteksi Platform ────────────────────────────────────────────────────────
const getPlatformName = (url) => {
    if (!url) return null;
    url = url.toLowerCase();
    if (url.includes('instagram.com')) return 'Instagram';
    if (url.includes('wa.me') || url.includes('whatsapp.com')) return 'WhatsApp';
    if (url.includes('facebook.com') || url.includes('fb.com')) return 'Facebook';
    if (url.includes('twitter.com') || url.includes('x.com')) return 'Twitter';
    if (url.includes('youtube.com')) return 'Youtube';
    if (url.includes('linkedin.com')) return 'LinkedIn';
    if (url.includes('github.com')) return 'Github';
    if (url.includes('mailto:') || (url.includes('@') && !url.includes('/'))) return 'Email';
    return null;
};

// Teks & URL — debounce 800ms
const scheduleAutoSave = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(doSave, 800);
};

// Toggle aktif — simpan langsung tanpa debounce
watch(editIsActive, () => {
    clearTimeout(debounceTimer);
    doSave();
});

// ── Popover ───────────────────────────────────────────────────────────────────
const openEditPopover = (element) => {
    if (isDragging) return;
    clearTimeout(debounceTimer);
    saveStatus.value = 'idle';
    openPopoverId.value = element.id;
    editingLink.value   = element;
    editName.value      = element.platform_name;
    editUrl.value       = element.url;
    editIsActive.value  = Boolean(element.is_active);
};

const openAddPopover = () => {
    openPopoverId.value = 'add';
    form.reset();
    form.clearErrors();
};

const closePopover = () => {
    clearTimeout(debounceTimer);
    openPopoverId.value = null;
    editingLink.value   = null;
    saveStatus.value    = 'idle';
};

// ── Hapus ─────────────────────────────────────────────────────────────────────
const deleteLink = () => {
    axios.delete(route('profile.social-media.destroy', editingLink.value.id))
        .then(() => {
            localLinks.value = localLinks.value.filter(l => l.id !== editingLink.value.id);
            closePopover();
        }).catch(err => console.error('Delete failed:', err));
};

// ── Simpan Tambah ─────────────────────────────────────────────────────────────
const submitAdd = () => {
    form.post(route('profile.social-media.store'), {
        preserveScroll: true,
        onSuccess: () => { closePopover(); form.reset(); }
    });
};
</script>

<template>
    <section>
        <header class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Media Sosial Platform</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Kelola tautan media sosial. Tarik untuk mengubah urutan, klik untuk mengedit.
                </p>
            </div>

            <!-- Tombol Tambah (teks saja) -->
            <div class="relative shrink-0 pt-0.5">
                <button type="button" @click.stop="openAddPopover"
                    class="flex items-center gap-1.5 text-sm font-semibold text-[#FFC000] hover:text-[#e5ac00] transition-colors focus:outline-none">
                    <Plus class="w-4 h-4" /> Tambah
                </button>

                <!-- Popover Tambah (tetap di sini) -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 translate-x-2 scale-95"
                    enter-to-class="opacity-100 translate-x-0 scale-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 translate-x-0 scale-100"
                    leave-to-class="opacity-0 translate-x-2 scale-95"
                >
                    <div v-if="openPopoverId === 'add'"
                        class="absolute z-[100] top-full right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden origin-top-right"
                        @click.stop>

                        <div class="flex items-center justify-between px-4 pt-4 pb-2">
                            <span class="text-sm font-bold text-[#0A2540]">Tambah Tautan</span>
                            <button @click="closePopover" class="text-gray-400 hover:text-gray-600">
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="px-4 pb-4 space-y-3.5">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Nama Label</label>
                                <input v-model="form.platform_name" type="text"
                                    class="block w-full border border-gray-200 outline-none focus:border-[#FFC000] focus:ring-[#FFC000]/20 focus:ring-2 rounded-md text-sm px-3 py-2 text-gray-900 transition-colors">
                                <p v-if="form.errors.platform_name" class="mt-1 text-xs text-red-600">{{ form.errors.platform_name }}</p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Tautan</label>
                                <input v-model="form.url" type="text"
                                    class="block w-full border border-gray-200 outline-none focus:border-[#FFC000] focus:ring-[#FFC000]/20 focus:ring-2 rounded-md text-sm px-3 py-2 text-gray-900 transition-colors">
                                <p v-if="form.errors.url" class="mt-1 text-xs text-red-600">{{ form.errors.url }}</p>
                            </div>

                            <div class="pt-2 border-t border-gray-100">
                                <button type="button" @click="submitAdd" :disabled="form.processing"
                                    class="w-full flex items-center justify-center gap-2 bg-[#FFC000] text-[#0A2540] text-sm font-semibold py-2 rounded-md hover:bg-[#e5ac00] disabled:opacity-50 transition-colors">
                                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </header>

        <!-- Overlay menutup popover -->
        <div v-if="openPopoverId" class="fixed inset-0 z-[90]" @click="closePopover" />

        <div class="space-y-3 w-full">

            <!-- ── Daftar Draggable ── -->
            <draggable
                v-model="localLinks"
                item-key="id"
                handle=".drag-handle"
                @start="onDragStart"
                @end="onDragEnd"
                class="space-y-2"
                :animation="200"
            >
                <template #item="{ element }">
                    <div class="relative">
                        <!-- Card -->
                        <div
                            class="flex items-center justify-between px-3 py-3.5 bg-white border rounded-lg transition-all"
                            :class="[
                                openPopoverId === element.id ? 'border-gray-200 shadow-sm' : 'border-gray-200 hover:border-gray-300',
                                !element.is_active ? 'opacity-55' : ''
                            ]"
                        >
                            <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 p-1 -ml-1 shrink-0">
                                <GripVertical class="w-4 h-4" />
                            </div>

                            <button type="button" class="flex-1 text-left px-3 text-sm font-semibold text-[#0A2540] focus:outline-none"
                                @click.stop="openEditPopover(element)">
                                {{ getPlatformName(element.url) || element.platform_name }}
                                <span v-if="!element.is_active" class="ml-1.5 text-[10px] bg-red-100 text-red-500 px-1.5 py-0.5 rounded-full font-normal align-middle">Nonaktif</span>
                            </button>

                            <button type="button" class="p-1 text-gray-300 hover:text-[#0A2540] transition-colors shrink-0 focus:outline-none"
                                @click.stop="openEditPopover(element)">
                                <ChevronRight class="w-4 h-4 transition-transform" :class="openPopoverId === element.id ? 'rotate-90' : ''" />
                            </button>
                        </div>

                        <!-- Popover Edit (di kiri) -->
                        <transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 translate-y-[-8px] scale-95"
                            enter-to-class="opacity-100 translate-y-0 scale-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100 translate-y-0 scale-100"
                            leave-to-class="opacity-0 translate-y-[-8px] scale-95"
                        >
                            <div v-if="openPopoverId === element.id"
                                class="absolute z-[100] top-full mt-2 right-0 w-72 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden origin-top-right"
                                @click.stop>

                                <!-- Header -->
                                <div class="flex items-center justify-between px-4 pt-4 pb-2">
                                    <span class="text-sm font-bold text-[#0A2540]">Edit Tautan</span>
                                    <button @click="closePopover" class="text-gray-400 hover:text-gray-600 transition-colors">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>

                                <div class="px-4 pb-4 space-y-3.5">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Nama Label</label>
                                        <input v-model="editName" type="text" @input="scheduleAutoSave"
                                            class="block w-full border border-gray-200 outline-none focus:border-[#FFC000] focus:ring-[#FFC000]/20 focus:ring-2 rounded-md text-sm px-3 py-2 text-gray-900 transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Tautan</label>
                                        <input v-model="editUrl" type="text" @input="scheduleAutoSave"
                                            class="block w-full border border-gray-200 outline-none focus:border-[#FFC000] focus:ring-[#FFC000]/20 focus:ring-2 rounded-md text-sm px-3 py-2 text-gray-900 transition-colors">
                                    </div>

                                    <label class="flex items-center gap-2.5 cursor-pointer py-0.5">
                                        <input type="checkbox" v-model="editIsActive"
                                            class="rounded border-gray-300 text-[#0A2540] focus:ring-0 w-4 h-4 cursor-pointer">
                                        <span class="text-sm text-[#0A2540]">Tampilkan di footer</span>
                                    </label>

                                    <div class="pt-2 border-t border-gray-100 space-y-2">
                                        <!-- Tombol Simpan (manual trigger & indikator auto-save) -->
                                        <button type="button" @click="doSave" :disabled="saveStatus === 'saving'"
                                            class="w-full flex items-center justify-center gap-2 bg-[#FFC000] text-[#0A2540] text-sm font-semibold py-2 rounded-md hover:bg-[#e5ac00] disabled:opacity-70 transition-colors">
                                            <Loader2 v-if="saveStatus === 'saving'" class="w-4 h-4 animate-spin" />
                                            <Save v-else class="w-4 h-4" />
                                            <span v-if="saveStatus === 'saving'">Menyimpan...</span>
                                            <span v-else-if="saveStatus === 'saved'">Tersimpan ✓</span>
                                            <span v-else-if="saveStatus === 'error'">Gagal, Coba Lagi</span>
                                            <span v-else>Simpan Perubahan</span>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <button type="button" @click="deleteLink"
                                            class="w-full flex items-center justify-center gap-2 px-2 py-1.5 text-red-500 text-xs font-semibold hover:bg-red-50 rounded-md transition-colors">
                                            <Trash2 class="w-3.5 h-3.5" /> Hapus Tautan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </template>
            </draggable>

            <div v-if="localLinks.length === 0"
                class="text-center py-8 text-gray-400 text-sm border border-dashed border-gray-300 rounded-lg">
                Belum ada tautan media sosial.
            </div>
        </div>
    </section>
</template>
