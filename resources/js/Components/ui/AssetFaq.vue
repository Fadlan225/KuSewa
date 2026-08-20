<script setup>
import { ChevronDown, HelpCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    faqs: {
        type: Array,
        default: () => []
    },
    assetType: {
        type: String,
        default: 'Aset'
    }
});

const openIndex = ref(null);

const toggleFaq = (index) => {
    openIndex.value = openIndex.value === index ? null : index;
};
</script>

<template>
    <div class="py-10 border-b border-gray-200">
        <h2 class="text-2xl font-extrabold text-[#0A2540] mb-6">Pertanyaan yang Sering Diajukan (FAQ)</h2>
        
        <div v-if="faqs.length > 0" class="space-y-4">
            <div 
                v-for="(faq, index) in faqs" 
                :key="index"
                class="border border-gray-200 rounded-xl overflow-hidden transition-all duration-300"
                :class="{'ring-1 ring-[#FFC000] border-[#FFC000]': openIndex === index}"
            >
                <button 
                    @click="toggleFaq(index)"
                    class="w-full flex items-center justify-between p-5 text-left bg-white hover:bg-gray-50 transition-colors"
                >
                    <span class="font-bold text-[#0A2540] pr-4 text-[15px]">{{ faq.question }}</span>
                    <ChevronDown class="text-gray-400 transition-transform duration-300"
                        :class="{'rotate-180': openIndex === index}" />
                </button>
                
                <div 
                    v-show="openIndex === index"
                    class="p-5 pt-0 bg-white text-gray-600 leading-relaxed text-[15px]"
                >
                    {{ faq.answer }}
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm border border-gray-100">
                <HelpCircle class="text-xl text-gray-300" />
            </div>
            <p class="text-sm font-bold text-gray-500">Belum ada FAQ</p>
            <p class="text-xs text-gray-400 mt-1">Pemilik belum menambahkan pertanyaan umum untuk aset ini</p>
        </div>
    </div>
</template>
