<script setup>
import { ref } from 'vue';
import UpdateBisnisInformationForm from './UpdateBisnisInformationForm.vue';
import UpdateBankAccountForm from './UpdateBankAccountForm.vue';
import { Briefcase, Landmark } from 'lucide-vue-next';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
    owner_profile: { type: Object, default: null },
    bank_account: { type: Object, default: null },
});

const activeTab = ref('bisnis');
</script>

<template>
    <div class="space-y-6">
        <!-- Tabs Nav -->
        <div class="flex space-x-1 bg-gray-100 p-1 rounded-xl">
            <button
                @click="activeTab = 'bisnis'"
                :class="[
                    'flex-1 flex items-center justify-center py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-200',
                    activeTab === 'bisnis' ? 'bg-white text-[#0A2540] shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'
                ]"
            >
                <Briefcase class="w-4 h-4 mr-2" />
                Informasi Bisnis
            </button>
            <button
                @click="activeTab = 'rekening'"
                :class="[
                    'flex-1 flex items-center justify-center py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-200',
                    activeTab === 'rekening' ? 'bg-white text-[#0A2540] shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'
                ]"
            >
                <Landmark class="w-4 h-4 mr-2" />
                Informasi Rekening
            </button>
        </div>

        <div class="p-5 sm:p-6 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <template v-if="activeTab === 'bisnis'">
                <h3 class="text-lg font-bold text-[#1D1D1F] mb-6">Informasi Bisnis</h3>
                <UpdateBisnisInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    :owner_profile="owner_profile"
                />
            </template>
            <template v-else-if="activeTab === 'rekening'">
                <h3 class="text-lg font-bold text-[#1D1D1F] mb-6">Informasi Rekening Bank</h3>
                <UpdateBankAccountForm
                    :bank_account="bank_account"
                />
            </template>
        </div>
    </div>
</template>
