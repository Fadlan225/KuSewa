<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    properties: { type: Array, default: () => [] },
});

const rejectionTarget = ref(null);
const note = ref('');
const processing = ref(false);

const approve = (property) => {
    processing.value = true;
    router.patch(route('admin.properties.approve', property.id), {}, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; },
    });
};

const reject = () => {
    if (!rejectionTarget.value || !note.value.trim()) return;
    processing.value = true;
    router.patch(route('admin.properties.reject', rejectionTarget.value.id), { verification_note: note.value }, {
        preserveScroll: true,
        onSuccess: () => { rejectionTarget.value = null; note.value = ''; },
        onFinish: () => { processing.value = false; },
    });
};
</script>

<template>
    <Head title="Verifikasi Properti" />

    <main class="min-h-screen bg-slate-50 p-6 md:p-10 text-slate-700">
        <div class="mx-auto max-w-6xl">
            <header class="mb-8">
                <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Admin</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Antrean Verifikasi Properti</h1>
                <p class="mt-2 text-sm text-slate-500">Setujui properti yang memenuhi ketentuan atau kirim alasan penolakan ke owner.</p>
            </header>

            <div v-if="properties.length" class="space-y-4">
                <article v-for="property in properties" :key="property.id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 md:flex-row">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-slate-900">{{ property.title }}</h2>
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700"><i class="fa-solid fa-clock mr-1"></i>Menunggu Verifikasi</span>
                            </div>
                            <p class="text-sm text-slate-500">{{ property.category }} · {{ property.city }}</p>
                            <p class="text-sm text-slate-600">{{ property.address }}</p>
                            <p class="text-xs text-slate-400">Owner: {{ property.owner?.name }} ({{ property.owner?.email }}) · Diajukan {{ property.created_at }}</p>
                            <p class="font-bold text-[#0A2540]">Rp {{ property.price.toLocaleString('id-ID') }}/{{ property.rent_period }}</p>
                        </div>
                        <div class="flex shrink-0 items-start gap-2">
                            <button :disabled="processing" @click="approve(property)" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 disabled:opacity-50">Setujui</button>
                            <button :disabled="processing" @click="rejectionTarget = property" class="rounded-xl border border-rose-200 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 disabled:opacity-50">Tolak</button>
                        </div>
                    </div>
                </article>
            </div>

            <section v-else class="rounded-2xl border border-slate-200 bg-white p-12 text-center">
                <i class="fa-solid fa-circle-check text-3xl text-emerald-500"></i>
                <h2 class="mt-3 font-bold text-slate-900">Tidak ada pengajuan yang menunggu</h2>
            </section>
        </div>

        <div v-if="rejectionTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <form @submit.prevent="reject" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="font-black text-slate-900">Tolak pengajuan</h2>
                <p class="mt-1 text-sm text-slate-500">Berikan alasan yang akan dilihat owner.</p>
                <textarea v-model="note" required maxlength="1000" rows="4" class="mt-4 w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-[#0A2540] focus:outline-none" placeholder="Contoh: Foto dan alamat properti belum lengkap."></textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" @click="rejectionTarget = null; note = ''" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500">Batal</button>
                    <button :disabled="processing || !note.trim()" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white disabled:opacity-50">Kirim Penolakan</button>
                </div>
            </form>
        </div>
    </main>
</template>
