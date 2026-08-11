<script setup>
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
    <div v-if="faqs.length > 0" class="py-10 border-b border-gray-200">
        <h2 class="text-2xl font-extrabold text-[#0A2540] mb-6">Pertanyaan yang Sering Diajukan (FAQ)</h2>
        
        <div class="space-y-4">
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
                    <i 
                        class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-300"
                        :class="{'rotate-180': openIndex === index}"
                    ></i>
                </button>
                
                <div 
                    v-show="openIndex === index"
                    class="p-5 pt-0 bg-white text-gray-600 leading-relaxed text-[15px]"
                >
                    {{ faq.answer }}
                </div>
            </div>
        </div>
    </div>
</template>
