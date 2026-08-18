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
        <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-bold text-slate-800 mb-2">
                Kebijakan & FAQ
            </h2>
            <p class="text-sm text-slate-500">Langkah ini opsional — Anda bisa melewatinya dan mengisinya nanti di dashboard.</p>
        </div>

        <!-- ========== KEBIJAKAN ========== -->
        <div class="space-y-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-700">
                        Kebijakan Properti
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Aturan yang harus dipatuhi calon penyewa.</p>
                </div>
                <button type="button" @click="emit('addPolicy')"
                    class="text-sm font-seqmibold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-md transition cursor-pointer">
                    + Tambah Kebijakan
                </button>
            </div>

            <div v-if="form.policies.length === 0" class="border-2 border-dashed border-slate-300 rounded-lg p-6 text-center bg-slate-50">
                <i class="fa-solid fa-file-contract text-3xl text-slate-300 mb-3 block"></i>
                <p class="text-sm text-slate-500">Belum ada kebijakan. Klik "Tambah Kebijakan" untuk mulai.</p>
            </div>

            <div v-for="(policy, idx) in form.policies" :key="idx" class="border border-slate-300 rounded-lg p-5 space-y-4 relative bg-white shadow-sm">
                <button type="button" @click="emit('removePolicy', idx)"
                    class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-md bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition text-sm cursor-pointer">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <div class="pr-10">
                    <label class="text-sm font-semibold text-slate-700 block mb-1.5">Judul Kebijakan <span class="text-rose-500">*</span></label>
                    <input v-model="policy.title" type="text" maxlength="200" placeholder="Contoh: Tidak Boleh Membawa Hewan Peliharaan"
                        class="w-full text-sm border border-slate-300 focus:border-transparent focus:ring-2 focus:ring-[#0A2540] focus:outline-none rounded-md px-4 py-2.5 transition" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 block mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <textarea v-model="policy.description" rows="2" maxlength="2000" placeholder="Penjelasan lebih detail tentang aturan ini..."
                        class="w-full text-sm border border-slate-300 focus:border-transparent focus:ring-2 focus:ring-[#0A2540] focus:outline-none rounded-md px-4 py-2.5 transition resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- ========== FAQ ========== -->
        <div class="space-y-4 pt-6 border-t border-slate-200">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-700">
                        FAQ (Pertanyaan Umum)
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Jawab pertanyaan yang paling sering ditanyakan calon penyewa.</p>
                </div>
                <button type="button" @click="emit('addFaq')"
                    class="text-sm font-semibold text-[#0A2540] bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-md transition cursor-pointer">
                    + Tambah FAQ
                </button>
            </div>

            <div v-if="form.faqs.length === 0" class="border-2 border-dashed border-slate-300 rounded-lg p-6 text-center bg-slate-50">
                <i class="fa-solid fa-comments text-3xl text-slate-300 mb-3 block"></i>
                <p class="text-sm text-slate-500">Belum ada FAQ. Klik "Tambah FAQ" untuk mulai.</p>
            </div>

            <div v-for="(faq, idx) in form.faqs" :key="idx" class="border border-slate-300 rounded-lg p-5 space-y-4 relative bg-white shadow-sm">
                <button type="button" @click="emit('removeFaq', idx)"
                    class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-md bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition text-sm cursor-pointer">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <div class="pr-10">
                    <label class="text-sm font-semibold text-slate-700 block mb-1.5">Pertanyaan <span class="text-rose-500">*</span></label>
                    <input v-model="faq.question" type="text" maxlength="300" placeholder="Contoh: Apakah biaya sewa sudah termasuk listrik?"
                        class="w-full text-sm border border-slate-300 focus:border-transparent focus:ring-2 focus:ring-[#0A2540] focus:outline-none rounded-md px-4 py-2.5 transition" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 block mb-1.5">Jawaban <span class="text-rose-500">*</span></label>
                    <textarea v-model="faq.answer" rows="2" maxlength="2000" placeholder="Contoh: Belum, biaya listrik menggunakan token masing-masing kamar..."
                        class="w-full text-sm border border-slate-300 focus:border-transparent focus:ring-2 focus:ring-[#0A2540] focus:outline-none rounded-md px-4 py-2.5 transition resize-none"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
