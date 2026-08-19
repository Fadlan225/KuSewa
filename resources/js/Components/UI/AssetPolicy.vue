<script setup>
import { FileSignature } from 'lucide-vue-next';
defineProps({
    policies: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <div class="py-10 border-b border-gray-200">
        <h3 class="text-2xl font-extrabold text-[#0A2540] mb-6">Kebijakan & Informasi Lainnya</h3>
        
        <div v-if="policies && policies.length > 0" class="flex flex-col w-full border border-gray-100 rounded-xl overflow-hidden">
            <div
                v-for="(policy, index) in policies"
                :key="policy.id"
                class="flex flex-col sm:flex-row py-4 px-4 sm:px-6 hover:bg-gray-100 transition-colors"
                :class="index % 2 !== 0 ? 'bg-gray-50/50' : 'bg-white'"
            >
                <div class="sm:w-[35%] shrink-0 text-gray-500 font-medium text-[15px]">
                    {{ policy.title }}
                </div>
                <div class="sm:w-[65%] text-[#222222] font-semibold text-[15px] mt-1 sm:mt-0 leading-relaxed">
                    {{ policy.description }}
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm border border-gray-100">
                <FileSignature class="text-xl text-gray-300" />
            </div>
            <p class="text-sm font-bold text-gray-500">Belum ada kebijakan khusus</p>
            <p class="text-xs text-gray-400 mt-1">Pemilik belum menambahkan kebijakan untuk aset ini</p>
        </div>
    </div>
</template>
