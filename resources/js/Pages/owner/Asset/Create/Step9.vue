<script setup>
import { ChevronDown, ChevronUp, Plus } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    form: Object,
    policyTemplates: { type: Array, default: () => [] },
    assetTypeName: { type: String, default: '' },
});

const emit = defineEmits(['addFaq', 'removeFaq']);

// ─── State utama (Peraturan ...) ─────────────────────────────────────────────
const mainOpen = ref(false);

// ─── State sub-section (key dinamis dari template) ───────────────────────────
const openSections = ref({});
const toggleSub = (key) => {
    openSections.value[key] = !openSections.value[key];
};

// ─── Pastikan policies adalah object ─────────────────────────────────────────
if (!props.form.policies || Array.isArray(props.form.policies)) {
    props.form.policies = {};
}
if (!Array.isArray(props.form.custom_policies)) {
    props.form.custom_policies = [];
}

const setPolicy = (key, value) => { props.form.policies[key] = value; };
const getPolicy = (key, defaultVal = false) => props.form.policies?.[key] ?? defaultVal;

// ─── Apakah item visible (cek parent_key) ────────────────────────────────────
const isVisible = (item) => {
    if (!item.parent_key) return true;
    const parentVal = getPolicy(item.parent_key);
    // parent toggle harus true, atau parent radio harus memilih nilai tertentu
    return parentVal === true || parentVal === item.parent_key;
};

// ─── Helper: nilai radio group aktif ─────────────────────────────────────────
const getRadioValue = (radioGroup) => getPolicy(radioGroup, null);
const setRadioValue = (radioGroup, value) => setPolicy(radioGroup, value);

// ─── Judul header "Peraturan ..." ─────────────────────────────────────────────
const policyTitle = computed(() =>
    props.assetTypeName ? `Peraturan ${props.assetTypeName}` : 'Peraturan'
);

// ─── Ada template atau tidak? ─────────────────────────────────────────────────
const hasTemplates = computed(() => props.policyTemplates.length > 0);

// ─── Custom (Lainnya) ─────────────────────────────────────────────────────────
const addCustomPolicy = () => { props.form.custom_policies.push({ title: '' }); };
const removeCustomPolicy = (idx) => { props.form.custom_policies.splice(idx, 1); };
</script>

<template>
    <div class="divide-y divide-slate-200 border border-slate-200 rounded-lg overflow-hidden">

        <!-- ================================================================ -->
        <!-- PERATURAN ... (dinamis dari assetTypeName)                        -->
        <!-- ================================================================ -->
        <div>
            <!-- Header utama -->
            <button type="button" @click="mainOpen = !mainOpen"
                class="w-full flex items-start justify-between px-4 py-4 text-left bg-white hover:bg-slate-50 transition-colors">
                <div>
                    <p class="text-sm font-bold text-slate-800">
                        {{ policyTitle }}
                        <span class="font-normal text-slate-400">(opsional)</span>
                    </p>
                    <p class="text-sm text-slate-500 mt-0.5">Dianjurkan diisi agar jelas bagi penyewa</p>
                </div>
                <ChevronDown v-if="!mainOpen" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
            </button>

            <!-- Isi: render dari policyTemplates -->
            <div v-if="mainOpen && hasTemplates" class="border-t border-slate-200 divide-y divide-slate-200">

                <!-- Loop tiap group dari template -->
                <div v-for="group in policyTemplates" :key="group.group_key">

                    <!-- Group header: always_open → tidak ada toggle -->
                    <div v-if="group.always_open" class="px-4 py-3.5 bg-white border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-800">{{ group.group_label }}</span>
                    </div>
                    <button v-else type="button" @click="toggleSub(group.group_key)"
                        class="w-full flex items-center justify-between px-4 py-3.5 text-left bg-white hover:bg-slate-50 transition-colors">
                        <span class="text-sm font-bold text-slate-800">{{ group.group_label }}</span>
                        <ChevronDown v-if="!openSections[group.group_key]" class="w-4 h-4 text-slate-400 shrink-0" />
                        <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0" />
                    </button>

                    <!-- Group content -->
                    <div v-if="group.always_open || openSections[group.group_key]"
                        class="divide-y divide-slate-100 border-t border-slate-100">

                        <template v-for="item in group.items" :key="item.key + '_' + (item.radio_value ?? '')">
                            <!-- Sembunyikan jika parent_key belum aktif -->
                            <template v-if="isVisible(item)">

                                <!-- ── TOGGLE ─────────────────────────────── -->
                                <div v-if="item.input_type === 'toggle'"
                                    class="flex items-center justify-between px-4 py-3 bg-white"
                                    :class="item.parent_key ? 'bg-slate-50' : 'bg-white'">
                                    <span class="text-sm text-slate-700">{{ item.label }}</span>
                                    <label class="flex items-center gap-2 cursor-pointer select-none ml-4 shrink-0">
                                        <span class="text-sm"
                                            :class="getPolicy(item.key) ? 'text-[#FFC000] font-semibold' : 'text-slate-400'">
                                            {{ getPolicy(item.key) ? 'Ya' : 'Tidak' }}
                                        </span>
                                        <div @click="setPolicy(item.key, !getPolicy(item.key))"
                                            class="relative w-10 h-[22px] rounded-full transition-colors duration-200 cursor-pointer"
                                            :class="getPolicy(item.key) ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                            <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200"
                                                :class="getPolicy(item.key) ? 'translate-x-[18px]' : 'translate-x-0'"></span>
                                        </div>
                                    </label>
                                </div>

                                <!-- ── CHECKBOX ────────────────────────────── -->
                                <label v-else-if="item.input_type === 'checkbox'"
                                    @click="setPolicy(item.key, !getPolicy(item.key))"
                                    class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 bg-white transition-colors">
                                    <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-colors"
                                        :class="getPolicy(item.key) ? 'border-[#FFC000] bg-[#FFC000]' : 'border-slate-300'">
                                        <svg v-if="getPolicy(item.key)" class="w-2.5 h-2.5 text-white" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-slate-700">{{ item.label }}</span>
                                </label>

                                <!-- ── RADIO ───────────────────────────────── -->
                                <div v-else-if="item.input_type === 'radio'">
                                    <label @click="setRadioValue(item.radio_group, item.radio_value)"
                                        class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 bg-white transition-colors">
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                            :class="getRadioValue(item.radio_group) === item.radio_value
                                                ? 'border-[#FFC000]' : 'border-slate-300'">
                                            <span v-if="getRadioValue(item.radio_group) === item.radio_value"
                                                class="w-2 h-2 rounded-full bg-[#FFC000]"></span>
                                        </div>
                                        <span class="text-sm text-slate-700">{{ item.label }}</span>
                                    </label>

                                    <!-- Extra input khusus night_access = ada_jam_malam (hardcode) -->
                                    <div v-if="item.radio_group === 'night_access'
                                            && item.radio_value === 'ada_jam_malam'
                                            && getRadioValue('night_access') === 'ada_jam_malam'"
                                        class="px-4 py-3 grid grid-cols-2 gap-3 bg-slate-50 border-t border-slate-100">
                                        <div>
                                            <label class="text-xs text-slate-500 block mb-1">Jam masuk terakhir</label>
                                            <input type="time" v-model="form.policies.night_access_in"
                                                min="20:00" max="23:59"
                                                class="w-full text-sm border border-slate-300 focus:ring-2 focus:ring-[#FFC000] focus:border-transparent outline-none rounded-md px-3 py-2 transition" />
                                            <p class="text-xs text-slate-400 mt-1">Rentang jam malam: 20.00 – 24.00</p>
                                        </div>
                                        <div>
                                            <label class="text-xs text-slate-500 block mb-1">Jam keluar terbatas mulai</label>
                                            <input type="time" v-model="form.policies.night_access_out"
                                                min="20:00" max="23:59"
                                                class="w-full text-sm border border-slate-300 focus:ring-2 focus:ring-[#FFC000] focus:border-transparent outline-none rounded-md px-3 py-2 transition" />
                                            <p class="text-xs text-slate-400 mt-1">Rentang jam malam: 20.00 – 24.00</p>
                                        </div>
                                    </div>
                                </div>

                            </template>
                        </template>

                    </div>
                </div>

                <!-- ── Lainnya (selalu ada di bawah) ─────────────────────── -->
                <div>
                    <button type="button" @click="toggleSub('lainnya')"
                        class="w-full flex items-center justify-between px-4 py-3.5 text-left bg-white hover:bg-slate-50 transition-colors">
                        <span class="text-sm font-bold text-slate-800">Lainnya</span>
                        <ChevronDown v-if="!openSections['lainnya']" class="w-4 h-4 text-slate-400 shrink-0" />
                        <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0" />
                    </button>
                    <div v-if="openSections['lainnya']" class="px-4 py-3 space-y-2 bg-white border-t border-slate-100">
                        <div v-for="(cp, idx) in form.custom_policies" :key="idx" class="flex items-center gap-2">
                            <input v-model="cp.title" type="text" maxlength="200"
                                placeholder="Contoh: Tidak boleh membawa kendaraan roda empat"
                                class="flex-1 text-sm border border-slate-300 focus:ring-2 focus:ring-[#FFC000] focus:border-transparent outline-none rounded-md px-3 py-2 transition" />
                            <button type="button" @click="removeCustomPolicy(idx)"
                                class="text-slate-400 hover:text-rose-500 transition cursor-pointer p-1 shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <button type="button" @click="addCustomPolicy"
                            class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1.5 transition cursor-pointer pt-1">
                            <Plus class="w-3.5 h-3.5" />
                            Tambah aturan
                        </button>
                    </div>
                </div>

            </div>

            <!-- Jika tidak ada template untuk tipe ini -->
            <div v-else-if="mainOpen && !hasTemplates"
                class="border-t border-slate-200 px-4 py-4 text-sm text-slate-400 bg-white">
                Belum ada template peraturan untuk jenis aset ini.
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- FAQ                                                               -->
        <!-- ================================================================ -->
        <div>
            <div class="flex items-start justify-between px-4 py-4 bg-white">
                <div>
                    <p class="text-sm font-bold text-slate-800">
                        FAQ
                        <span class="font-normal text-slate-400">(opsional)</span>
                    </p>
                    <p class="text-sm text-slate-500 mt-0.5">Jawab pertanyaan yang sering ditanyakan calon penyewa</p>
                </div>
                <button type="button" @click="emit('addFaq')"
                    class="text-sm text-slate-600 border border-slate-300 hover:border-slate-400 px-3 py-1.5 rounded-md transition cursor-pointer shrink-0 ml-4">
                    + Tambah
                </button>
            </div>

            <div v-if="form.faqs.length > 0" class="divide-y divide-slate-100 border-t border-slate-200">
                <div v-for="(faq, idx) in form.faqs" :key="idx" class="px-4 py-3 bg-white">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 space-y-2.5">
                            <div>
                                <label class="text-xs text-slate-500 block mb-1">Pertanyaan <span class="text-rose-400">*</span></label>
                                <input v-model="faq.question" type="text" maxlength="300"
                                    placeholder="Contoh: Apakah biaya sewa sudah termasuk listrik?"
                                    class="w-full text-sm border border-slate-300 focus:ring-2 focus:ring-[#FFC000] focus:border-transparent outline-none rounded-md px-3 py-2 transition" />
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 block mb-1">Jawaban <span class="text-rose-400">*</span></label>
                                <textarea v-model="faq.answer" rows="2" maxlength="2000"
                                    placeholder="Tulis jawaban di sini..."
                                    class="w-full text-sm border border-slate-300 focus:ring-2 focus:ring-[#FFC000] focus:border-transparent outline-none rounded-md px-3 py-2 transition resize-none"></textarea>
                            </div>
                        </div>
                        <button type="button" @click="emit('removeFaq', idx)"
                            class="text-slate-400 hover:text-rose-500 transition cursor-pointer p-1 mt-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- DESKRIPSI ASET                                                    -->
        <!-- ================================================================ -->
        <div class="px-4 py-4 bg-white">
            <label class="block text-sm font-bold text-slate-800 mb-0.5">
                Deskripsi Aset <span class="text-rose-400">*</span>
            </label>
            <p class="text-sm text-slate-500 mb-2">Ceritakan keunggulan dan kondisi aset Anda</p>
            <textarea
                v-model="form.description"
                rows="4"
                minlength="100"
                placeholder="Deskripsikan secara lengkap keunggulan, kondisi, dan informasi penting lainnya..."
                class="w-full text-sm px-3 py-2 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-transparent transition resize-none"
                required
            ></textarea>
            <p class="text-xs text-slate-400 mt-1">
                Minimal 100 karakter —
                <span :class="(form.description?.length ?? 0) >= 100 ? 'text-slate-600 font-medium' : ''">
                    {{ form.description?.length ?? 0 }} karakter
                </span>
            </p>
        </div>

    </div>
</template>
