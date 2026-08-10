<script setup>
import { ref } from 'vue';

const props = defineProps({
    asset: Object,
    isEditing: Boolean,
    form: Object,
    specItems: Array,
    assetFacilities: Array,
});

const isExpanded = ref(false);
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-in fade-in duration-300">
        <!-- Deskripsi & Info Singkat -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6">
            <h2 class="font-bold text-slate-800 mb-4 text-base">Tentang {{ asset.title }}</h2>

            <div v-if="!isEditing" class="text-sm text-slate-600 mb-6">
                <div class="leading-relaxed whitespace-pre-line transition-all duration-300" :class="{ 'line-clamp-4': !isExpanded }">
                    {{ asset.description }}
                </div>
                <button v-if="asset.description && asset.description.length > 150" @click="isExpanded = !isExpanded" class="text-[#0A2540] font-bold mt-2 hover:underline focus:outline-none transition">
                    {{ isExpanded ? 'Tampilkan Lebih Sedikit' : 'Baca Selengkapnya' }}
                </button>
            </div>
            <div v-else class="mb-6">
                <textarea v-model="form.description" rows="4" class="text-sm text-slate-700 border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-lg p-3 w-full bg-slate-50 transition shadow-inner" placeholder="Tuliskan deskripsi menarik tentang properti Anda..."></textarea>
                <div v-if="form.errors.description" class="text-xs text-rose-500 mt-1">{{ form.errors.description }}</div>
            </div>

        </div>

        <!-- Informasi Umum -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6">
            <h2 class="font-bold text-slate-800 text-base mb-4">Informasi Umum</h2>
            
            <!-- Brief Specs (Luas dll) -->
            <div class="space-y-3 mb-6 text-sm">
                <div v-for="spec in specItems.slice(0, 4)" :key="spec.label" class="flex items-center justify-between">
                    <span class="text-slate-500">{{ spec.label }}</span>
                    <span class="font-semibold text-slate-800">{{ spec.value }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4 pt-4 border-t border-slate-100">
                <h2 class="font-bold text-slate-800 text-base">Fasilitas Utama</h2>
                <button v-if="isEditing" class="text-xs font-bold text-blue-600 hover:text-blue-700">Ubah Fasilitas</button>
            </div>

            <div v-if="assetFacilities.length > 0" class="grid grid-cols-4 sm:grid-cols-4 gap-y-6 gap-x-2 text-center">
                <div v-for="fac in assetFacilities.slice(0, 8)" :key="fac.id" class="flex flex-col items-center gap-2 group">
                    <div class="w-10 h-10 rounded-full bg-slate-50 group-hover:bg-blue-50 text-slate-400 group-hover:text-blue-600 flex items-center justify-center transition-colors border border-slate-100">
                        <i class="fa-solid" :class="fac.icon || 'fa-check'"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-600 line-clamp-2 leading-tight">{{ fac.name }}</span>
                </div>
            </div>
            <div v-else class="text-sm text-slate-400 text-center py-6">
                Belum ada fasilitas yang ditambahkan.
            </div>
        </div>
    </div>
</template>
