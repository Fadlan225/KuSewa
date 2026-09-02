<script setup>
import { ChevronDown, ChevronUp, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    form: Object,
});

const emit = defineEmits(['addFaq', 'removeFaq']);

// ─── State utama (Peraturan Kos) ─────────────────────────────────────────────
const mainOpen = ref(false);

// ─── State sub-section ───────────────────────────────────────────────────────
const openSections = ref({
    persyaratan: false,
    akses: false,
    larangan: false,
    tamu: false,
    lainnya: false,
});

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

const setPolicy = (key, value) => {
    props.form.policies[key] = value;
};

const getPolicy = (key, defaultVal = false) => {
    return props.form.policies?.[key] ?? defaultVal;
};

const addCustomPolicy = () => { props.form.custom_policies.push({ title: '' }); };
const removeCustomPolicy = (idx) => { props.form.custom_policies.splice(idx, 1); };
</script>

<template>
    <div class="divide-y divide-slate-200 border border-slate-200 rounded-lg overflow-hidden">

        <!-- ============================================================ -->
        <!-- PERATURAN KOS                                                 -->
        <!-- ============================================================ -->
        <div>
            <!-- Header utama -->
            <button type="button" @click="mainOpen = !mainOpen"
                class="w-full flex items-start justify-between px-4 py-4 text-left bg-white hover:bg-slate-50 transition-colors">
                <div>
                    <p class="text-sm font-bold text-slate-800">
                        Peraturan Kos
                        <span class="font-normal text-slate-400">(opsional)</span>
                    </p>
                    <p class="text-sm text-slate-500 mt-0.5">Dianjurkan diisi agar jelas bagi penyewa</p>
                </div>
                <ChevronDown v-if="!mainOpen" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
            </button>

            <!-- Isi sub-section -->
            <div v-if="mainOpen" class="border-t border-slate-200 divide-y divide-slate-200">

                <!-- ── Persyaratan dan Dokumen (selalu terbuka) ──────── -->
                <div>
                    <div class="px-4 py-3.5 bg-white border-b border-slate-100">
                        <span class="text-sm font-bold text-slate-800">Persyaratan dan Dokumen</span>
                    </div>

                    <div class="divide-y divide-slate-100">

                        <!-- Boleh bawa anak -->
                        <div class="flex items-center justify-between px-4 py-3 bg-white">
                            <span class="text-sm text-slate-700">Boleh bawa anak</span>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-sm" :class="getPolicy('allow_children') ? 'text-[#FFC000] font-semibold' : 'text-slate-400'">
                                    {{ getPolicy('allow_children') ? 'Ya' : 'Tidak' }}
                                </span>
                                <div @click="setPolicy('allow_children', !getPolicy('allow_children'))"
                                    class="relative w-10 h-[22px] rounded-full transition-colors duration-200 cursor-pointer"
                                    :class="getPolicy('allow_children') ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                    <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200"
                                        :class="getPolicy('allow_children') ? 'translate-x-[18px]' : 'translate-x-0'"></span>
                                </div>
                            </label>
                        </div>

                        <!-- Wajib KK — hanya jika boleh anak -->
                        <div v-if="getPolicy('allow_children')"
                            class="flex items-center justify-between px-4 py-3 bg-slate-50">
                            <div>
                                <span class="text-sm text-slate-700">Wajib sertakan kartu keluarga saat pengajuan sewa</span>
                                <p class="text-xs text-slate-400 mt-0.5">Dokumen untuk membawa anak</p>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none ml-4 shrink-0">
                                <span class="text-sm" :class="getPolicy('require_family_card') ? 'text-[#FFC000] font-semibold' : 'text-slate-400'">
                                    {{ getPolicy('require_family_card') ? 'Ya' : 'Tidak' }}
                                </span>
                                <div @click="setPolicy('require_family_card', !getPolicy('require_family_card'))"
                                    class="relative w-10 h-[22px] rounded-full transition-colors duration-200 cursor-pointer"
                                    :class="getPolicy('require_family_card') ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                    <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200"
                                        :class="getPolicy('require_family_card') ? 'translate-x-[18px]' : 'translate-x-0'"></span>
                                </div>
                            </label>
                        </div>

                        <!-- Boleh untuk pasutri -->
                        <div class="flex items-center justify-between px-4 py-3 bg-white">
                            <span class="text-sm text-slate-700">Boleh untuk pasutri</span>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-sm" :class="getPolicy('allow_couple') ? 'text-[#FFC000] font-semibold' : 'text-slate-400'">
                                    {{ getPolicy('allow_couple') ? 'Ya' : 'Tidak' }}
                                </span>
                                <div @click="setPolicy('allow_couple', !getPolicy('allow_couple'))"
                                    class="relative w-10 h-[22px] rounded-full transition-colors duration-200 cursor-pointer"
                                    :class="getPolicy('allow_couple') ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                    <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200"
                                        :class="getPolicy('allow_couple') ? 'translate-x-[18px]' : 'translate-x-0'"></span>
                                </div>
                            </label>
                        </div>

                        <!-- Wajib Buku Nikah — hanya jika boleh pasutri -->
                        <div v-if="getPolicy('allow_couple')"
                            class="flex items-center justify-between px-4 py-3 bg-slate-50">
                            <div>
                                <span class="text-sm text-slate-700">Wajib sertakan buku nikah saat pengajuan sewa</span>
                                <p class="text-xs text-slate-400 mt-0.5">Dokumen untuk pasutri</p>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer select-none ml-4 shrink-0">
                                <span class="text-sm" :class="getPolicy('require_marriage_book') ? 'text-[#FFC000] font-semibold' : 'text-slate-400'">
                                    {{ getPolicy('require_marriage_book') ? 'Ya' : 'Tidak' }}
                                </span>
                                <div @click="setPolicy('require_marriage_book', !getPolicy('require_marriage_book'))"
                                    class="relative w-10 h-[22px] rounded-full transition-colors duration-200 cursor-pointer"
                                    :class="getPolicy('require_marriage_book') ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                    <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200"
                                        :class="getPolicy('require_marriage_book') ? 'translate-x-[18px]' : 'translate-x-0'"></span>
                                </div>
                            </label>
                        </div>

                        <!-- Wajib KTP -->
                        <div class="flex items-center justify-between px-4 py-3 bg-white">
                            <span class="text-sm text-slate-700">Wajib sertakan KTP saat pengajuan sewa</span>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-sm" :class="getPolicy('require_ktp') ? 'text-[#FFC000] font-semibold' : 'text-slate-400'">
                                    {{ getPolicy('require_ktp') ? 'Ya' : 'Tidak' }}
                                </span>
                                <div @click="setPolicy('require_ktp', !getPolicy('require_ktp'))"
                                    class="relative w-10 h-[22px] rounded-full transition-colors duration-200 cursor-pointer"
                                    :class="getPolicy('require_ktp') ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                    <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200"
                                        :class="getPolicy('require_ktp') ? 'translate-x-[18px]' : 'translate-x-0'"></span>
                                </div>
                            </label>
                        </div>

                        <!-- Boleh bawa hewan -->
                        <div class="flex items-center justify-between px-4 py-3 bg-white">
                            <span class="text-sm text-slate-700">Boleh bawa hewan peliharaan</span>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-sm" :class="getPolicy('allow_pets') ? 'text-[#FFC000] font-semibold' : 'text-slate-400'">
                                    {{ getPolicy('allow_pets') ? 'Ya' : 'Tidak' }}
                                </span>
                                <div @click="setPolicy('allow_pets', !getPolicy('allow_pets'))"
                                    class="relative w-10 h-[22px] rounded-full transition-colors duration-200 cursor-pointer"
                                    :class="getPolicy('allow_pets') ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                    <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200"
                                        :class="getPolicy('allow_pets') ? 'translate-x-[18px]' : 'translate-x-0'"></span>
                                </div>
                            </label>
                        </div>

                    </div>
                </div>

                <!-- ── Akses Jam Malam ─────────────────────────────────── -->
                <div>
                    <button type="button" @click="toggleSub('akses')"
                        class="w-full flex items-center justify-between px-4 py-3.5 text-left bg-white hover:bg-slate-50 transition-colors">
                        <span class="text-sm font-bold text-slate-800">Akses Jam Malam</span>
                        <ChevronDown v-if="!openSections.akses" class="w-4 h-4 text-slate-400 shrink-0" />
                        <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0" />
                    </button>

                    <div v-if="openSections.akses" class="divide-y divide-slate-100 border-t border-slate-100">
                        <label @click="setPolicy('night_access', '24jam')"
                            class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 bg-white transition-colors">
                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                :class="getPolicy('night_access', null) === '24jam' ? 'border-[#FFC000]' : 'border-slate-300'">
                                <span v-if="getPolicy('night_access', null) === '24jam'" class="w-2 h-2 rounded-full bg-[#FFC000]"></span>
                            </div>
                            <span class="text-sm text-slate-700">Akses 24 jam</span>
                        </label>
                        <label @click="setPolicy('night_access', 'ada_jam_malam')"
                            class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 bg-white transition-colors">
                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                :class="getPolicy('night_access', null) === 'ada_jam_malam' ? 'border-[#FFC000]' : 'border-slate-300'">
                                <span v-if="getPolicy('night_access', null) === 'ada_jam_malam'" class="w-2 h-2 rounded-full bg-[#FFC000]"></span>
                            </div>
                            <span class="text-sm text-slate-700">Ada jam malam</span>
                        </label>
                        <div v-if="getPolicy('night_access', null) === 'ada_jam_malam'"
                            class="px-4 py-3 grid grid-cols-2 gap-3 bg-slate-50">
                            <div>
                                <label class="text-xs text-slate-500 block mb-1">Jam masuk terakhir</label>
                                <input type="time" v-model="form.policies.night_access_in"
                                    class="w-full text-sm border border-slate-300 focus:ring-2 focus:ring-[#FFC000] focus:border-transparent outline-none rounded-md px-3 py-2 transition" />
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 block mb-1">Jam keluar terbatas mulai</label>
                                <input type="time" v-model="form.policies.night_access_out"
                                    class="w-full text-sm border border-slate-300 focus:ring-2 focus:ring-[#FFC000] focus:border-transparent outline-none rounded-md px-3 py-2 transition" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Larangan Penyewa ────────────────────────────────── -->
                <div>
                    <button type="button" @click="toggleSub('larangan')"
                        class="w-full flex items-center justify-between px-4 py-3.5 text-left bg-white hover:bg-slate-50 transition-colors">
                        <span class="text-sm font-bold text-slate-800">Larangan Penyewa</span>
                        <ChevronDown v-if="!openSections.larangan" class="w-4 h-4 text-slate-400 shrink-0" />
                        <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0" />
                    </button>

                    <div v-if="openSections.larangan" class="divide-y divide-slate-100 border-t border-slate-100">
                        <template v-for="item in [
                            { key: 'no_smoking', label: 'Dilarang merokok di dalam kamar/unit' },
                            { key: 'no_alcohol', label: 'Dilarang membawa/mengonsumsi alkohol' },
                            { key: 'no_drugs', label: 'Dilarang membawa narkoba atau barang terlarang' },
                            { key: 'no_loud_music', label: 'Dilarang memainkan musik keras malam hari' },
                            { key: 'no_cooking', label: 'Dilarang memasak di dalam kamar' },
                        ]" :key="item.key">
                            <label @click="setPolicy(item.key, !getPolicy(item.key))"
                                class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 bg-white transition-colors">
                                <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-colors"
                                    :class="getPolicy(item.key) ? 'border-[#FFC000] bg-[#FFC000]' : 'border-slate-300'">
                                    <svg v-if="getPolicy(item.key)" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-sm text-slate-700">{{ item.label }}</span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- ── Peraturan Tamu ──────────────────────────────────── -->
                <div>
                    <button type="button" @click="toggleSub('tamu')"
                        class="w-full flex items-center justify-between px-4 py-3.5 text-left bg-white hover:bg-slate-50 transition-colors">
                        <span class="text-sm font-bold text-slate-800">Peraturan Tamu</span>
                        <ChevronDown v-if="!openSections.tamu" class="w-4 h-4 text-slate-400 shrink-0" />
                        <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0" />
                    </button>

                    <div v-if="openSections.tamu" class="divide-y divide-slate-100 border-t border-slate-100">
                        <div class="flex items-center justify-between px-4 py-3 bg-white">
                            <span class="text-sm text-slate-700">Boleh menerima tamu?</span>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <span class="text-sm" :class="getPolicy('allow_guests') ? 'text-[#FFC000] font-semibold' : 'text-slate-400'">
                                    {{ getPolicy('allow_guests') ? 'Ya' : 'Tidak' }}
                                </span>
                                <div @click="setPolicy('allow_guests', !getPolicy('allow_guests'))"
                                    class="relative w-10 h-[22px] rounded-full transition-colors duration-200 cursor-pointer"
                                    :class="getPolicy('allow_guests') ? 'bg-[#FFC000]' : 'bg-slate-200'">
                                    <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200"
                                        :class="getPolicy('allow_guests') ? 'translate-x-[18px]' : 'translate-x-0'"></span>
                                </div>
                            </label>
                        </div>
                        <template v-if="getPolicy('allow_guests')" v-for="item in [
                            { key: 'no_opposite_gender_guest', label: 'Dilarang membawa tamu lawan jenis' },
                            { key: 'guest_overnight_fee', label: 'Tamu menginap dikenakan biaya' },
                            { key: 'guest_can_overnight', label: 'Tamu boleh menginap' },
                            { key: 'guest_night_curfew', label: 'Ada jam malam untuk tamu' },
                        ]" :key="item.key">
                            <label @click="setPolicy(item.key, !getPolicy(item.key))"
                                class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 bg-white transition-colors">
                                <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-colors"
                                    :class="getPolicy(item.key) ? 'border-[#FFC000] bg-[#FFC000]' : 'border-slate-300'">
                                    <svg v-if="getPolicy(item.key)" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-sm text-slate-700">{{ item.label }}</span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- ── Lainnya ─────────────────────────────────────────── -->
                <div>
                    <button type="button" @click="toggleSub('lainnya')"
                        class="w-full flex items-center justify-between px-4 py-3.5 text-left bg-white hover:bg-slate-50 transition-colors">
                        <span class="text-sm font-bold text-slate-800">Lainnya</span>
                        <ChevronDown v-if="!openSections.lainnya" class="w-4 h-4 text-slate-400 shrink-0" />
                        <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0" />
                    </button>

                    <div v-if="openSections.lainnya" class="px-4 py-3 space-y-2 bg-white border-t border-slate-100">
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
        </div>

        <!-- ============================================================ -->
        <!-- FAQ                                                           -->
        <!-- ============================================================ -->
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

        <!-- ============================================================ -->
        <!-- DESKRIPSI ASET                                                -->
        <!-- ============================================================ -->
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
