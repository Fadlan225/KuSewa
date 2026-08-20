<script setup>
import { ref, provide } from 'vue';

import Navbar from '@/Components/Navbar.vue';
import Bottombar from '@/Components/Bottombar.vue';
import Footer from '@/Components/Footer.vue';
import GlobalLoading from "@/Components/GlobalLoading.vue";
import FloatingChat from '@/Components/ui/FloatingChat.vue';
import AuthModal from '@/Components/Auth/AuthModal.vue';
import AuthFeedbackModal from '@/Components/Auth/AuthFeedbackModal.vue';
import LocationPermissionModal from '@/Components/ui/LocationPermissionModal.vue';
import NotificationToast from '@/Components/ui/NotificationToast.vue';
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
    },
    hideFooter: {
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

import { onMounted, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useNotifications } from '@/Composables/useNotifications';
import { usePushNotifications } from '@/Composables/usePushNotifications';

const page = usePage();
const isHome = computed(() => route().current('Home'));
const authFeedbackStore = useAuthFeedbackStore();

// Ref untuk komponen Toast (dipanggil dari sini saat ada broadcast)
const toastRef = ref(null);

const { addNewNotification } = useNotifications();
const { init: initPush, subscribe: subscribePush, isSubscribed, permission } = usePushNotifications();

let lastProcessedFlashId = null;

watch(() => page.props.flash, (flash) => {
    // Prevent re-processing the same flash message instance (e.g., when navigating back using history)
    if (flash?.uuid && flash.uuid === lastProcessedFlashId) return;
    if (flash?.uuid) lastProcessedFlashId = flash.uuid;

    if (flash?.success) {
        authFeedbackStore.showSuccess({
            title: 'Berhasil',
            message: flash.success
        });
        page.props.flash.success = null;
    } else if (flash?.error) {
        authFeedbackStore.showError({
            title: 'Gagal',
            message: flash.error
        });
        page.props.flash.error = null;
    }
}, { deep: true, immediate: true });

onMounted(async () => {
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

    // Inisialisasi Web Push dan subscribe jika user sudah login
    if (page.props.auth?.user) {
        await initPush();
        // Auto-subscribe jika izin sudah diberikan sebelumnya
        if (permission.value === 'granted' && !isSubscribed.value) {
            await subscribePush();
        }

        // Listen notifikasi real-time via Laravel Echo (Reverb/Pusher)
        // Uncomment baris di bawah setelah Reverb dikonfigurasi di server
        // window.Echo?.private(`App.Models.User.${page.props.auth.user.id}`)
        //     .notification((notification) => {
        //         addNewNotification(notification);
        //         toastRef.value?.addToast(notification);
        //     });
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA] font-sans text-[#000000]">

        <GlobalLoading />

        <Navbar v-if="!hideNavbar" :transparent="transparentNavbar" />

        <main :class="{ 'pt-16 lg:pt-[96px]': !transparentNavbar && !hideNavbar }">
            <slot />
        </main>

        <Footer v-if="!hideFooter" />

        <Bottombar v-if="!hideBottombar" />

        <FloatingChat v-if="isHome" />

        <AuthModal v-model="isAuthModalOpen" @update:modelValue="(val) => !val && authModalStore.close()" />
        <AuthFeedbackModal />
        
        <LocationPermissionModal />

        <!-- Toast Notifikasi Real-time -->
        <NotificationToast ref="toastRef" />
    </div>
</template>
