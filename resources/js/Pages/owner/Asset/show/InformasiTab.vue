<script setup>
const props = defineProps({
    asset: Object,
    isEditing: Boolean,
    form: Object,
    specItems: Array,
});
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-in fade-in duration-300">
        <div class="space-y-6">
            <!-- Wilayah -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6">
                <h2 class="font-bold text-slate-800 text-base mb-4">Lokasi & Wilayah</h2>

                <!-- ADDRESS (Edit Mode) -->
                <div v-if="isEditing" class="mb-4">
                    <label class="text-xs font-bold text-slate-500 block mb-1">Alamat Lengkap</label>
                    <input v-model="form.address" type="text" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full bg-white transition shadow-sm" placeholder="Jalan, No, RT/RW" />
                    <div v-if="form.errors.address" class="text-xs text-rose-500 mt-1">{{ form.errors.address }}</div>
                </div>

                <div class="grid grid-cols-2 gap-y-4 gap-x-4 text-sm">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Provinsi</span>
                        <span class="font-semibold text-slate-700">{{ asset.province?.name || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Kota/Kabupaten</span>
                        <span class="font-semibold text-slate-700">{{ asset.city?.name || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Kecamatan</span>
                        <span class="font-semibold text-slate-700">{{ asset.district?.name || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Kelurahan/Desa</span>
                        <span class="font-semibold text-slate-700">{{ asset.village?.name || '-' }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span>Ingin mengubah data wilayah?</span>
                    <button class="font-bold text-blue-600 hover:underline">Hubungi CS</button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Spesifikasi Detail -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-slate-800 text-base">Spesifikasi Detail</h2>
                    <button v-if="isEditing" class="text-xs font-bold text-blue-600 hover:text-blue-700">Ubah Data</button>
                </div>

                <ul v-if="specItems.length > 0" class="divide-y divide-slate-100">
                    <li v-for="(spec, i) in specItems" :key="i" class="flex justify-between py-2.5 text-sm">
                        <span class="text-slate-500">{{ spec.label }}</span>
                        <span class="font-semibold text-slate-800">{{ spec.value }}</span>
                    </li>
                </ul>
                <div v-else class="text-sm text-slate-400 py-4 text-center">
                    Tidak ada data spesifikasi terperinci.
                </div>
            </div>
        </div>
    </div>
</template>
