<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    asset: Object,
});

// ========== FAQ ==========
const faqs = ref(props.asset.faqs ?? []);

const newFaq = reactive({ question: '', answer: '' });
const isAddingFaq = ref(false);
const isSavingFaq = ref(false);
const editingFaqId = ref(null);
const editingFaq = reactive({ question: '', answer: '' });

const submitFaq = () => {
    if (!newFaq.question.trim() || !newFaq.answer.trim()) return;
    isSavingFaq.value = true;
    router.post(route('owner.asset.faqs.store', props.asset.slug || props.asset.id), {
        question: newFaq.question,
        answer: newFaq.answer,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newFaq.question = '';
            newFaq.answer = '';
            isAddingFaq.value = false;
            isSavingFaq.value = false;
        },
        onError: () => { isSavingFaq.value = false; },
    });
};

const startEditFaq = (faq) => {
    editingFaqId.value = faq.id;
    editingFaq.question = faq.question;
    editingFaq.answer = faq.answer;
};

const submitEditFaq = (faqId) => {
    router.put(route('owner.asset.faqs.update', { asset: props.asset.slug || props.asset.id, faq: faqId }), {
        question: editingFaq.question,
        answer: editingFaq.answer,
    }, {
        preserveScroll: true,
        onSuccess: () => { editingFaqId.value = null; },
    });
};

const deleteFaq = (faqId) => {
    if (!confirm('Hapus FAQ ini?')) return;
    router.delete(route('owner.asset.faqs.destroy', { asset: props.asset.slug || props.asset.id, faq: faqId }), {
        preserveScroll: true,
    });
};

// ========== KEBIJAKAN ==========
const newPolicy = reactive({ title: '', description: '' });
const isAddingPolicy = ref(false);
const isSavingPolicy = ref(false);
const editingPolicyId = ref(null);
const editingPolicy = reactive({ title: '', description: '' });

const submitPolicy = () => {
    if (!newPolicy.title.trim()) return;
    isSavingPolicy.value = true;
    router.post(route('owner.asset.policies.store', props.asset.slug || props.asset.id), {
        title: newPolicy.title,
        description: newPolicy.description,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newPolicy.title = '';
            newPolicy.description = '';
            isAddingPolicy.value = false;
            isSavingPolicy.value = false;
        },
        onError: () => { isSavingPolicy.value = false; },
    });
};

const startEditPolicy = (policy) => {
    editingPolicyId.value = policy.id;
    editingPolicy.title = policy.title;
    editingPolicy.description = policy.description ?? '';
};

const submitEditPolicy = (policyId) => {
    router.put(route('owner.asset.policies.update', { asset: props.asset.slug || props.asset.id, policy: policyId }), {
        title: editingPolicy.title,
        description: editingPolicy.description,
    }, {
        preserveScroll: true,
        onSuccess: () => { editingPolicyId.value = null; },
    });
};

const deletePolicy = (policyId) => {
    if (!confirm('Hapus kebijakan ini?')) return;
    router.delete(route('owner.asset.policies.destroy', { asset: props.asset.slug || props.asset.id, policy: policyId }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="animate-in fade-in duration-300 grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- ========== FAQ ========== -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6 flex flex-col gap-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="font-bold text-slate-800 text-base">FAQ</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pertanyaan yang sering ditanyakan penyewa</p>
                </div>
                <button
                    v-if="!isAddingFaq"
                    @click="isAddingFaq = true"
                    class="text-xs font-bold text-white bg-[#0A2540] hover:bg-[#0A2540]/90 px-3 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5"
                >
                    <i class="fa-solid fa-plus"></i> Tambah FAQ
                </button>
            </div>

            <!-- Add Form -->
            <div v-if="isAddingFaq" class="border border-indigo-200 bg-indigo-50/30 rounded-xl p-4 space-y-3">
                <div>
                    <label class="text-xs font-bold text-slate-600 block mb-1">Pertanyaan <span class="text-rose-500">*</span></label>
                    <input v-model="newFaq.question" type="text" maxlength="300" placeholder="Contoh: Apakah ada biaya tambahan?"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-indigo-500 focus:ring-0 transition" />
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-600 block mb-1">Jawaban <span class="text-rose-500">*</span></label>
                    <textarea v-model="newFaq.answer" rows="3" maxlength="2000" placeholder="Tulis jawaban yang jelas..."
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-indigo-500 focus:ring-0 transition resize-none"></textarea>
                </div>
                <div class="flex gap-2">
                    <button @click="submitFaq" :disabled="isSavingFaq || !newFaq.question.trim() || !newFaq.answer.trim()"
                        class="flex-1 px-4 py-2 bg-[#0A2540] text-white text-xs font-bold rounded-lg transition disabled:opacity-50">
                        <i v-if="isSavingFaq" class="fa-solid fa-spinner fa-spin mr-1"></i>
                        Simpan
                    </button>
                    <button @click="isAddingFaq = false"
                        class="px-4 py-2 text-slate-600 bg-white border border-slate-200 text-xs font-bold rounded-lg hover:bg-slate-50 transition">
                        Batal
                    </button>
                </div>
            </div>

            <!-- FAQ List -->
            <div v-if="asset.faqs && asset.faqs.length > 0" class="space-y-3">
                <div v-for="faq in asset.faqs" :key="faq.id"
                    class="border border-slate-200 rounded-xl overflow-hidden">
                    <!-- View Mode -->
                    <div v-if="editingFaqId !== faq.id" class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 text-sm leading-snug">{{ faq.question }}</p>
                                <p class="text-slate-500 text-sm mt-1.5 leading-relaxed">{{ faq.answer }}</p>
                            </div>
                            <div class="flex gap-1.5 shrink-0">
                                <button @click="startEditFaq(faq)" title="Edit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition text-xs">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button @click="deleteFaq(faq.id)" title="Hapus"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition text-xs">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Edit Mode -->
                    <div v-else class="p-4 bg-indigo-50/30 space-y-3">
                        <input v-model="editingFaq.question" type="text" maxlength="300"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-indigo-500 focus:ring-0 transition" />
                        <textarea v-model="editingFaq.answer" rows="3" maxlength="2000"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-indigo-500 focus:ring-0 transition resize-none"></textarea>
                        <div class="flex gap-2">
                            <button @click="submitEditFaq(faq.id)"
                                class="flex-1 px-3 py-1.5 bg-[#0A2540] text-white text-xs font-bold rounded-lg transition">
                                Simpan
                            </button>
                            <button @click="editingFaqId = null"
                                class="px-3 py-1.5 text-slate-600 bg-white border border-slate-200 text-xs font-bold rounded-lg hover:bg-slate-50 transition">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="!isAddingFaq" class="flex flex-col items-center justify-center py-8 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                    <i class="fa-solid fa-circle-question text-xl text-slate-300"></i>
                </div>
                <p class="text-sm font-bold text-slate-500">Belum ada FAQ</p>
                <p class="text-xs text-slate-400 mt-1">Tambahkan pertanyaan umum untuk membantu penyewa</p>
            </div>
        </div>

        <!-- ========== KEBIJAKAN ========== -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6 flex flex-col gap-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="font-bold text-slate-800 text-base">Kebijakan</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Aturan dan kebijakan aset Anda</p>
                </div>
                <button
                    v-if="!isAddingPolicy"
                    @click="isAddingPolicy = true"
                    class="text-xs font-bold text-white bg-[#0A2540] hover:bg-[#0A2540]/90 px-3 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5"
                >
                    <i class="fa-solid fa-plus"></i> Tambah Kebijakan
                </button>
            </div>

            <!-- Add Form -->
            <div v-if="isAddingPolicy" class="border border-amber-200 bg-amber-50/30 rounded-xl p-4 space-y-3">
                <div>
                    <label class="text-xs font-bold text-slate-600 block mb-1">Judul Kebijakan <span class="text-rose-500">*</span></label>
                    <input v-model="newPolicy.title" type="text" maxlength="200" placeholder="Contoh: Tidak Merokok di Dalam Ruangan"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-amber-500 focus:ring-0 transition" />
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-600 block mb-1">Deskripsi (Opsional)</label>
                    <textarea v-model="newPolicy.description" rows="2" maxlength="2000" placeholder="Penjelasan lebih detail tentang kebijakan ini..."
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-amber-500 focus:ring-0 transition resize-none"></textarea>
                </div>
                <div class="flex gap-2">
                    <button @click="submitPolicy" :disabled="isSavingPolicy || !newPolicy.title.trim()"
                        class="flex-1 px-4 py-2 bg-[#FFC000] text-[#0A2540] text-xs font-bold rounded-lg transition disabled:opacity-50">
                        <i v-if="isSavingPolicy" class="fa-solid fa-spinner fa-spin mr-1"></i>
                        Simpan
                    </button>
                    <button @click="isAddingPolicy = false"
                        class="px-4 py-2 text-slate-600 bg-white border border-slate-200 text-xs font-bold rounded-lg hover:bg-slate-50 transition">
                        Batal
                    </button>
                </div>
            </div>

            <!-- Policy List -->
            <div v-if="asset.policies && asset.policies.length > 0" class="space-y-3">
                <div v-for="policy in asset.policies" :key="policy.id"
                    class="border border-slate-200 rounded-xl overflow-hidden">
                    <!-- View Mode -->
                    <div v-if="editingPolicyId !== policy.id" class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-circle-check text-amber-500 text-[10px]"></i>
                                    </div>
                                    <p class="font-bold text-slate-800 text-sm">{{ policy.title }}</p>
                                </div>
                                <p v-if="policy.description" class="text-slate-500 text-sm mt-1.5 leading-relaxed ml-8">{{ policy.description }}</p>
                            </div>
                            <div class="flex gap-1.5 shrink-0">
                                <button @click="startEditPolicy(policy)" title="Edit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition text-xs">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button @click="deletePolicy(policy.id)" title="Hapus"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition text-xs">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Edit Mode -->
                    <div v-else class="p-4 bg-amber-50/30 space-y-3">
                        <input v-model="editingPolicy.title" type="text" maxlength="200"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-amber-500 focus:ring-0 transition" />
                        <textarea v-model="editingPolicy.description" rows="2" maxlength="2000"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-amber-500 focus:ring-0 transition resize-none"></textarea>
                        <div class="flex gap-2">
                            <button @click="submitEditPolicy(policy.id)"
                                class="flex-1 px-3 py-1.5 bg-[#FFC000] text-[#0A2540] text-xs font-bold rounded-lg transition">
                                Simpan
                            </button>
                            <button @click="editingPolicyId = null"
                                class="px-3 py-1.5 text-slate-600 bg-white border border-slate-200 text-xs font-bold rounded-lg hover:bg-slate-50 transition">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="!isAddingPolicy" class="flex flex-col items-center justify-center py-8 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                    <i class="fa-solid fa-file-contract text-xl text-slate-300"></i>
                </div>
                <p class="text-sm font-bold text-slate-500">Belum ada kebijakan</p>
                <p class="text-xs text-slate-400 mt-1">Tambahkan aturan agar penyewa tahu ketentuan Anda</p>
            </div>
        </div>

    </div>
</template>
