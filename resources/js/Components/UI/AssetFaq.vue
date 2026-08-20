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
    <div class="py-10 md:py-12 border-b border-gray-100">
        <h3 class="text-[22px] font-bold text-[#222222] mb-5">Pertanyaan yang Sering Diajukan</h3>
        
        <div v-if="faqs.length > 0" class="space-y-4">
            <div 
                v-for="(faq, index) in faqs" 
                :key="index"
                class="border border-gray-200 rounded-xl overflow-hidden transition-all duration-300"
            >
                <button 
                    @click="toggleFaq(index)"
                    class="w-full flex items-center justify-between p-5 text-left bg-white hover:bg-gray-50 transition-colors"
                >
                    <span class="font-semibold text-[#222222] pr-4 text-[15px]">{{ faq.question }}</span>
                    <ChevronDown class="text-gray-400 transition-transform duration-300 shrink-0 w-5 h-5"
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
