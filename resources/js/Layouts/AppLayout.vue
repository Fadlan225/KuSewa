<script setup>
import { Head} from '@inertiajs/vue3';
import { ref, provide } from 'vue';

import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import Bottombar from '@/Components/Bottombar.vue';
import GlobalLoading from "@/Components/GlobalLoading.vue";
import FloatingChat from '@/Components/UI/FloatingChat.vue';
import AuthModal from '@/Components/Auth/AuthModal.vue';

defineProps({
    transparentNavbar: {
        type: Boolean,
        default: false
    },
    hideNavbar: {
        type: Boolean,
        default: false
    },
    hideBottombar: {
        type: Boolean,
        default: false
    }
});

// Global Auth Modal State
const isAuthModalOpen = ref(false);
const initialAuthStep = ref(null);
const initialAuthData = ref({});

provide('openAuthModal', () => {
    isAuthModalOpen.value = true;
});

provide('initialAuthStep', initialAuthStep);
provide('initialAuthData', initialAuthData);

import { onMounted, watch } from 'vue';

watch(isAuthModalOpen, (newVal) => {
    if (!newVal) {
        // Modal closed, reset the initial state so next time it opens it starts fresh
        setTimeout(() => {
            initialAuthStep.value = null;
            initialAuthData.value = {};
        }, 300); // Wait for transition to finish
    }
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('auth_action') === 'magic_link_success') {
        const pEmail = params.get('email');
        const pPurpose = params.get('purpose');
        const pProof = params.get('proof');
        
        if (pEmail && pPurpose && pProof) {
            initialAuthData.value = {
                email: pEmail,
                purpose: pPurpose,
                proof: pProof
            };
            
            if (pPurpose === 'register') {
                initialAuthStep.value = 'register_password';
            } else if (pPurpose === 'forgot_password' || pPurpose === 'create_password') {
                initialAuthStep.value = 'reset_password';
            }
            
            isAuthModalOpen.value = true;
            
            // Hapus parameter URL agar tidak terus memicu modal jika di-refresh
            window.history.replaceState({}, '', window.location.pathname);
        }
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA] font-sans text-[#000000]">

        <GlobalLoading />

        <Navbar v-if="!hideNavbar" :transparent="transparentNavbar" />

        <main :class="{ 'pt-16': !transparentNavbar && !hideNavbar }">
            <slot />
        </main>

        <Bottombar v-if="!hideBottombar" />

        <FloatingChat />

        <AuthModal v-model="isAuthModalOpen" />

        <!-- <Footer /> -->
    </div>
</template>
