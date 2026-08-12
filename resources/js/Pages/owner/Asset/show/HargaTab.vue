<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    asset: Object,
    lowestPrice: Object,
});

const form = useForm({
    pricings: []
});

onMounted(() => {
    if (props.asset.pricings && props.asset.pricings.length > 0) {
        form.pricings = props.asset.pricings.map(p => ({
            _id: p.id,
            duration: p.duration,
            rental_unit: p.rental_unit,
            price: p.price
        }));
    } else {
        // Default empty pricing if none exists
        form.pricings.push({
            _id: Date.now(),
            duration: 1,
            rental_unit: props.asset.type?.rental_unit || 'month',
            price: ''
        });
    }
});

const formatRupiah = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const submitPricings = () => {
    // Client side validation
    const errors = {};
    const seen = new Set();
    let hasError = false;

    if (!form.pricings || form.pricings.length === 0) {
        form.setError('pricings', 'Paket harga sewa wajib diisi minimal 1.');
        return;
    }

    form.pricings.forEach((p, i) => {
        if (!p.duration || p.duration <= 0) {
            errors[`pricings.${i}.duration`] = 'Durasi tidak valid.';
            hasError = true;
        }
        if (!p.price || Number(p.price) <= 0) {
            errors[`pricings.${i}.price`] = 'Harga tidak valid.';
            hasError = true;
        }
        const key = `${p.duration}-${p.rental_unit}`;
        if (seen.has(key)) {
            errors[`pricings.${i}.duration`] = 'Paket durasi ini sudah ada (Duplikat).';
            hasError = true;
        }
        seen.add(key);
    });

    if (hasError) {
        form.clearErrors();
        form.setError(errors);
        return;
    }

    form.put(route('owner.asset.pricings.update', props.asset.slug || props.asset.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Update the _ids so they don't get messed up
            if (props.asset.pricings) {
                form.pricings = props.asset.pricings.map(p => ({
                    _id: p.id,
                    duration: p.duration,
                    rental_unit: p.rental_unit,
                    price: p.price
                }));
            }
        }
    });
};
</script>

<template>
    <div class="animate-in fade-in duration-300">
        <!-- Harga / Pricings Form -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6 max-w-4xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-slate-800 text-base">Atur Paket Harga Sewa</h2>
                <button
                    type="button"
                    @click="form.pricings.push({ _id: Date.now(), duration: 1, rental_unit: asset.type?.rental_unit || 'month', price: '' })"
                    class="text-xs font-bold text-[#0A2540] hover:text-white bg-slate-100 hover:bg-[#0A2540] px-3 py-1.5 rounded-lg transition"
                >
                    + Tambah Harga
                </button>
            </div>

            <div v-if="form.errors.pricings" class="text-xs text-rose-500 mb-3 bg-rose-50 p-2 rounded-lg border border-rose-100">
                {{ form.errors.pricings }}
            </div>

            <form @submit.prevent="submitPricings" class="space-y-4">
                <div class="space-y-3">
                    <div v-for="(pricing, pIdx) in form.pricings" :key="pricing._id || pIdx" class="flex flex-col sm:flex-row gap-3 sm:items-start bg-slate-50 p-3 rounded-xl border border-slate-200 relative group">

                        <!-- Delete Button (absolute on mobile, relative on desktop) -->
                        <button
                            v-if="form.pricings.length > 1"
                            type="button"
                            @click="form.pricings.splice(pIdx, 1)"
                            class="absolute top-2 right-2 sm:relative sm:top-0 sm:right-0 sm:mt-6 w-7 h-7 shrink-0 rounded-lg bg-rose-100 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center text-xs"
                            title="Hapus Harga"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                        <div class="w-full sm:w-1/4">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Durasi</label>
                            <input v-model="pricing.duration" type="number" min="1" class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#0A2540] bg-white transition" required />
                            <span v-if="form.errors[`pricings.${pIdx}.duration`]" class="text-[10px] text-rose-500 mt-1 block">{{ form.errors[`pricings.${pIdx}.duration`] }}</span>
                        </div>
                        <div class="w-full sm:w-1/4">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Satuan Waktu</label>
                            <select v-model="pricing.rental_unit" class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#0A2540] bg-white transition" required>
                                <option value="hour">Jam</option>
                                <option value="night">Malam</option>
                                <option value="day">Hari</option>
                                <option value="week">Minggu</option>
                                <option value="month">Bulan</option>
                            </select>
                        </div>
                        <div class="w-full sm:flex-1">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Harga (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">Rp</span>
                                <input v-model="pricing.price" type="number" min="0" class="w-full text-sm pl-9 pr-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#0A2540] bg-white transition" placeholder="Contoh: 1000000" required />
                            </div>
                            <span v-if="form.errors[`pricings.${pIdx}.price`]" class="text-[10px] text-rose-500 mt-1 block">{{ form.errors[`pricings.${pIdx}.price`] }}</span>
                            <div v-if="pricing.price && Number(pricing.price) > 0" class="text-[10px] text-emerald-600 font-semibold mt-1">
                                {{ formatRupiah(pricing.price) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" :disabled="form.processing" class="bg-[#0A2540] hover:bg-slate-800 text-white text-sm font-bold px-6 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                        <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-save"></i>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan Harga' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
