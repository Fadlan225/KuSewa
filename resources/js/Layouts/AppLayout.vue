<script setup>
import { Head} from '@inertiajs/vue3';
import { ref, provide } from 'vue';

import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import Bottombar from '@/Components/Bottombar.vue';
import GlobalLoading from "@/Components/GlobalLoading.vue";
import FloatingChat from '@/Components/UI/FloatingChat.vue';
import AuthModal from '@/Components/Auth/AuthModal.vue';
import AuthFeedbackModal from '@/Components/Auth/AuthFeedbackModal.vue';
import { useAuthModalStore } from '@/Stores/AuthModalStore';
import { useAuthFeedbackStore } from '@/Stores/AuthFeedbackStore';
import { storeToRefs } from 'pinia';

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

// Global Auth Modal State via Pinia
const authModalStore = useAuthModalStore();
const { isOpen: isAuthModalOpen, initialStep: initialAuthStep, initialData: initialAuthData } = storeToRefs(authModalStore);

// Keep provide for backward compatibility with components using inject
provide('openAuthModal', (step = null, data = {}) => {
    authModalStore.open(step, data);
});
provide('initialAuthStep', initialAuthStep);
provide('initialAuthData', initialAuthData);

import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const authFeedbackStore = useAuthFeedbackStore();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        authFeedbackStore.showSuccess({
            title: 'Berhasil',
            message: flash.success
        });
    } else if (flash?.error) {
        authFeedbackStore.showError({
            title: 'Gagal',
            message: flash.error
        });
    }
}, { deep: true, immediate: true });

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
                authModalStore.open('register_password', initialAuthData.value);
            } else if (pPurpose === 'forgot_password' || pPurpose === 'create_password') {
                authModalStore.open('reset_password', initialAuthData.value);
            } else {
                authModalStore.open(null, initialAuthData.value);
            }
            
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

        <AuthModal v-model="isAuthModalOpen" @update:modelValue="(val) => !val && authModalStore.close()" />
        <AuthFeedbackModal />

        <!-- <Footer /> -->
    </div>
</template>
