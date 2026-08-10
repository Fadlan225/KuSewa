<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    asset: Object,
});

const formatRupiah = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <div class="animate-in fade-in duration-300">
        <div v-if="asset.type?.allow_units" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-white px-5 py-4 flex items-center justify-between">
                <h2 class="font-bold text-slate-800">Daftar Unit</h2>
                <Link :href="`/owner/asset/${asset.slug || asset.id}/units/create`" class="text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg transition shadow-sm">
                    + Tambah Unit
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase border-y border-slate-100">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Nama Unit</th>
                            <th class="px-5 py-3 font-semibold">Harga Sewa</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="!asset.units || asset.units.length === 0">
                            <td colspan="4" class="px-5 py-12 text-center text-slate-400">Belum ada unit yang ditambahkan.</td>
                        </tr>
                        <tr v-for="unit in asset.units" :key="unit.id" class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3 font-bold text-slate-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-10 rounded-md bg-slate-100 overflow-hidden border border-slate-200 shrink-0">
                                        <img v-if="unit.images && unit.images.length > 0" :src="unit.images[0].image_url" class="w-full h-full object-cover" />
                                        <i v-else class="fa-solid fa-image text-slate-300 w-full h-full flex items-center justify-center"></i>
                                    </div>
                                    {{ unit.name }}
                                </div>
                            </td>
                            <td class="px-5 py-3 font-semibold text-[#F97316]">
                                {{ unit.pricings && unit.pricings.length > 0 ? formatRupiah(unit.pricings[0].price) : '-' }}
                            </td>
                            <td class="px-5 py-3">
                                <span :class="unit.bookings?.length ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'" class="px-2 py-1 rounded text-[10px] font-bold">
                                    {{ unit.bookings?.length ? 'Terisi' : 'Kosong' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Link :href="`/owner/asset/${asset.slug || asset.id}/units/${unit.id}/edit`" class="text-slate-400 hover:text-blue-600 transition p-2">
                                    <i class="fa-solid fa-pen"></i>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else class="text-center py-12 bg-white rounded-xl border border-slate-200">
            <i class="fa-solid fa-door-closed text-4xl text-slate-300 mb-3"></i>
            <h3 class="text-slate-600 font-bold mb-1">Tipe Properti Tidak Mendukung Unit</h3>
            <p class="text-sm text-slate-400">Properti ini disewakan sebagai satu kesatuan utuh.</p>
        </div>
    </div>
</template>
