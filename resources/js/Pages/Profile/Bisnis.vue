<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import BisnisForms from './Partials/BisnisForms.vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
    owner_profile: { type: Object, default: null },
    bank_account: { type: Object, default: null },
});
</script>

<template>
    <Head title="Profil Bisnis" />

    <div class="md:hidden min-h-screen bg-[#F8F9FA] pb-24 text-[#333333] font-sans">
        <DetailNavbar title="Profil Bisnis" backUrl="/profile" :forceBackUrl="true" :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" />

        <div class="px-4 py-6">
            <BisnisForms 
                :must-verify-email="mustVerifyEmail"
                :status="status"
                    :user="user"
                    :owner_profile="owner_profile"
                    :bank_account="bank_account"
                />
            </div>
        </div>
</template>
