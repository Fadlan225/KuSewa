<script setup>
import UpdateProfileInformationForm from './UpdateProfileInformationForm.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
});
</script>

<template>
    <div class="space-y-8">
        <!-- Data Pribadi Form -->
        <div class="p-5 sm:p-6 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <h3 class="text-lg font-bold text-[#1D1D1F] mb-6">Data Pribadi</h3>
            <UpdateProfileInformationForm
                :must-verify-email="mustVerifyEmail"
                :status="status"
            />
        </div>
    </div>
</template>
