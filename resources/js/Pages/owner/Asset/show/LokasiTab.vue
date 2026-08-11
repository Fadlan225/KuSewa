<script setup>
const props = defineProps({
    asset: Object,
    form: Object,
});
</script>

<template>
    <div class="animate-in fade-in duration-300">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6">
            <h2 class="font-bold text-slate-800 text-base mb-4">Lokasi & Wilayah</h2>

            <!-- ADDRESS (Editable) -->
            <div class="mb-4">
                <label class="text-xs font-bold text-slate-500 block mb-1">Alamat Lengkap</label>
                <input v-model="form.address" type="text" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full bg-white transition shadow-sm" placeholder="Jalan, No, RT/RW" />
                <div v-if="form.errors.address" class="text-xs text-rose-500 mt-1">{{ form.errors.address }}</div>

                <label class="text-xs font-bold text-slate-500 block mt-3 mb-1">Kode Pos</label>
                <input v-model="form.postal_code" type="text" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 rounded-lg px-3 py-2 w-full bg-white transition shadow-sm" placeholder="Kode Pos" />
                <div v-if="form.errors.postal_code" class="text-xs text-rose-500 mt-1">{{ form.errors.postal_code }}</div>
            </div>

            <div class="grid grid-cols-2 gap-y-4 gap-x-4 text-sm mb-4">
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

            <!-- MAP PLACEHOLDER -->
            <div class="w-full h-48 bg-slate-100 rounded-lg overflow-hidden relative flex items-center justify-center border border-slate-200 mt-4">
                <div class="absolute inset-0 bg-cover bg-center opacity-50" style="background-image: url('https://map.viamichelin.com/map/carte?map=viamichelin&z=10&lat=-0.502&lon=117.153&width=800&height=400&format=png&version=latest&layer=background')"></div>
                <div class="z-10 flex flex-col items-center bg-white/90 p-3 rounded-lg shadow-sm">
                    <i class="fa-solid fa-location-dot text-rose-500 text-xl mb-1"></i>
                    <span class="font-bold text-xs text-slate-700">Peta belum diintegrasikan</span>
                </div>
            </div>
        </div>
    </div>
</template>
