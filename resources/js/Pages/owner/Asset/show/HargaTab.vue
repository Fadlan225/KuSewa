<script setup>
import { Trash2, Loader2, Save } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/Components/ui/card';

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
            price: Math.floor(Number(p.price))
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

const formatInputRupiah = (val) => {
    if (!val) return '';
    const num = parseInt(val.toString().replace(/[^0-9]/g, ''), 10);
    return isNaN(num) ? '' : num.toLocaleString('id-ID');
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
        <Card class="bg-white rounded-xl border border-slate-200 shadow-sm w-full">
            <CardContent class="p-5 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-slate-800 text-base">Atur Paket Harga Sewa</h2>
                <button
                    type="button"
                    @click="form.pricings.push({ _id: Date.now(), duration: 1, rental_unit: asset.type?.rental_unit || 'month', price: '' })"
                    class="text-xs font-bold text-slate-900 hover:text-slate-900 bg-slate-100 hover:bg-[#FFC000] px-3 py-1.5 rounded-lg transition"
                >
                    + Tambah Harga
                </button>
            </div>

            <div v-if="form.errors.pricings" class="text-xs text-rose-500 mb-3 bg-rose-50 p-2 rounded-lg border border-rose-100">
                {{ form.errors.pricings }}
            </div>

            <form @submit.prevent="submitPricings" class="space-y-4">
                <div class="space-y-3">
                    <div v-for="(pricing, pIdx) in form.pricings" :key="pricing._id || pIdx" class="flex flex-col sm:flex-row gap-4 sm:items-start bg-white p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors relative group shadow-sm">

                        <!-- Delete Button (absolute on mobile, relative on desktop) -->
                        <button
                            v-if="form.pricings.length > 1"
                            type="button"
                            @click="form.pricings.splice(pIdx, 1)"
                            class="absolute top-2 right-2 sm:relative sm:top-0 sm:right-0 sm:mt-7 w-8 h-8 shrink-0 rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-colors flex items-center justify-center focus:outline-none"
                            title="Hapus Harga"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>

                        <div class="w-full sm:w-1/4">
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">Durasi</label>
                            <input v-model="pricing.duration" type="number" min="1" class="w-full border border-slate-200 bg-slate-50 hover:bg-white focus:bg-white hover:border-slate-300 rounded-lg focus:ring-4 focus:ring-[#FFC000]/10 focus:border-[#FFC000] focus:outline-none transition-all px-4 py-2 text-slate-800 font-semibold shadow-sm" required />
                            <span v-if="form.errors[`pricings.${pIdx}.duration`]" class="text-[10px] text-rose-500 mt-1 block">{{ form.errors[`pricings.${pIdx}.duration`] }}</span>
                        </div>
                        <div class="w-full sm:w-1/4">
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">Satuan Waktu</label>
                            <select v-model="pricing.rental_unit" class="w-full border border-slate-200 bg-slate-50 hover:bg-white focus:bg-white hover:border-slate-300 rounded-lg focus:ring-4 focus:ring-[#FFC000]/10 focus:border-[#FFC000] focus:outline-none transition-all px-4 py-2 text-slate-800 font-semibold shadow-sm" required>
                                <option value="hour">Jam</option>
                                <option value="night">Malam</option>
                                <option value="day">Hari</option>
                                <option value="week">Minggu</option>
                                <option value="month">Bulan</option>
                            </select>
                        </div>
                        <div class="w-full sm:flex-1">
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">Harga (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">Rp</span>
                                <input :value="formatInputRupiah(pricing.price)" @input="pricing.price = $event.target.value.replace(/[^0-9]/g, '')" type="text" class="w-full pl-10 pr-4 py-2 border border-slate-200 bg-slate-50 hover:bg-white focus:bg-white hover:border-slate-300 rounded-lg focus:ring-4 focus:ring-[#FFC000]/10 focus:border-[#FFC000] focus:outline-none transition-all text-slate-800 font-semibold shadow-sm" placeholder="Contoh: 1.000.000" required />
                            </div>
                            <span v-if="form.errors[`pricings.${pIdx}.price`]" class="text-[10px] text-rose-500 mt-1 block">{{ form.errors[`pricings.${pIdx}.price`] }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" :disabled="form.processing" class="bg-[#FFC000] hover:bg-[#e5ac00] text-slate-900 text-sm font-bold px-6 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                        <Loader2 v-if="form.processing" class="animate-spin" />
                        <Save v-else class="" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan Harga' }}
                    </button>
                </div>
            </form>
            </CardContent>
        </Card>
    </div>
</template>
