<script setup>
import { ref } from 'vue';

const props = defineProps({
    form: Object,
});

const emit = defineEmits(['addFaq', 'removeFaq', 'addPolicy', 'removePolicy']);
</script>

<template>
    <div class="space-y-8">
        <!-- Header -->
        <div>
            <h2 class="text-xl font-black text-[#0A2540] mb-1">Kebijakan & FAQ</h2>
            <p class="text-sm text-slate-500">Langkah ini opsional — Anda bisa melewatinya dan mengisinya nanti di dashboard.</p>
        </div>

        <!-- ========== FAQ ========== -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#0A2540]">FAQ (Pertanyaan Umum)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Jawab pertanyaan yang sering ditanyakan calon penyewa</p>
                </div>
                <button type="button" @click="emit('addFaq')"
                    class="text-xs font-bold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Tambah FAQ
                </button>
            </div>

            <div v-if="form.faqs.length === 0" class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center">
                <i class="fa-solid fa-circle-question text-3xl text-slate-300 mb-2"></i>
                <p class="text-sm text-slate-400">Belum ada FAQ. Klik "Tambah FAQ" untuk mulai.</p>
            </div>

            <div v-for="(faq, idx) in form.faqs" :key="idx" class="border border-slate-200 rounded-xl p-4 space-y-3 relative">
                <button type="button" @click="emit('removeFaq', idx)"
                    class="absolute top-3 right-3 w-6 h-6 flex items-center justify-center rounded-full bg-rose-100 text-rose-500 hover:bg-rose-200 transition text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div>
                    <label class="text-xs font-bold text-slate-500 block mb-1">Pertanyaan <span class="text-rose-500">*</span></label>
                    <input v-model="faq.question" type="text" maxlength="300" placeholder="Contoh: Apakah tersedia parkir?"
                        class="w-full text-sm border border-slate-300 focus:border-indigo-500 focus:ring-0 rounded-lg px-3 py-2 transition" />
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 block mb-1">Jawaban <span class="text-rose-500">*</span></label>
                    <textarea v-model="faq.answer" rows="2" maxlength="2000" placeholder="Tulis jawaban yang jelas..."
                        class="w-full text-sm border border-slate-300 focus:border-indigo-500 focus:ring-0 rounded-lg px-3 py-2 transition resize-none"></textarea>
                </div>
            </div>
        </div>

        <hr class="border-slate-100" />

        <!-- ========== KEBIJAKAN ========== -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#0A2540]">Kebijakan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Aturan yang harus dipatuhi penyewa</p>
                </div>
                <button type="button" @click="emit('addPolicy')"
                    class="text-xs font-bold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Tambah Kebijakan
                </button>
            </div>

            <div v-if="form.policies.length === 0" class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center">
                <i class="fa-solid fa-file-contract text-3xl text-slate-300 mb-2"></i>
                <p class="text-sm text-slate-400">Belum ada kebijakan. Klik "Tambah Kebijakan" untuk mulai.</p>
            </div>

            <div v-for="(policy, idx) in form.policies" :key="idx" class="border border-slate-200 rounded-xl p-4 space-y-3 relative">
                <button type="button" @click="emit('removePolicy', idx)"
                    class="absolute top-3 right-3 w-6 h-6 flex items-center justify-center rounded-full bg-rose-100 text-rose-500 hover:bg-rose-200 transition text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div>
                    <label class="text-xs font-bold text-slate-500 block mb-1">Judul Kebijakan <span class="text-rose-500">*</span></label>
                    <input v-model="policy.title" type="text" maxlength="200" placeholder="Contoh: Tidak Merokok"
                        class="w-full text-sm border border-slate-300 focus:border-amber-500 focus:ring-0 rounded-lg px-3 py-2 transition" />
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 block mb-1">Deskripsi (Opsional)</label>
                    <textarea v-model="policy.description" rows="2" maxlength="2000" placeholder="Penjelasan lebih lanjut..."
                        class="w-full text-sm border border-slate-300 focus:border-amber-500 focus:ring-0 rounded-lg px-3 py-2 transition resize-none"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
