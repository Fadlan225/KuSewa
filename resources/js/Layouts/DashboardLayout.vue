<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import Sidebar from '@/Components/sidebar.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { X, Menu } from 'lucide-vue-next';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import LogoutIllustrationIcon from '@/Components/ui/Icons/LogoutIllustrationIcon.vue';
import Topbar from '@/Components/Topbar.vue';
import ProfileIncompleteModal from '@/Components/ui/ProfileIncompleteModal.vue';

import { getOwnerMenu, getAdminMenu } from '@/Config/menus';
import NotificationToast from '@/Components/ui/NotificationToast.vue';
import { useNotifications } from '@/Composables/useNotifications';

const { addNewNotification } = useNotifications();
const toastRef = ref(null);

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    role: { type: String, required: true },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();

// Dapatkan sidebar counts dari global props (di-inject via HandleInertiaRequests)
const sidebarCounts = computed(() => page.props.sidebarCounts || {});

// Menu yang dipakai bergantung pada role yang diberikan dari props
const isProfileComplete = computed(() => page.props.isProfileComplete);
const menu = computed(() => props.role === 'Admin' ? getAdminMenu(sidebarCounts.value) : getOwnerMenu(sidebarCounts.value, isProfileComplete.value));

// ==========================================
// MOBILE SIDEBAR BEHAVIOR
// ==========================================
const showMobileMenu = ref(false);

watch(() => page.url, () => {
    showMobileMenu.value = false;
});

const showLogoutModal = ref(false);

const openLogoutModal = () => {
    showLogoutModal.value = true;
};

const handleLogoutConfirm = () => {
    router.post(route('logout'));
};

onMounted(() => {
    if (page.props.auth?.user) {
        window.Echo?.private(`App.Models.User.${page.props.auth.user.id}`)
            .notification((notification) => {
                addNewNotification(notification);
                toastRef.value?.addToast(notification);
            });
    }
    window.addEventListener('open-logout-modal', openLogoutModal);
});

onUnmounted(() => {
    window.removeEventListener('open-logout-modal', openLogoutModal);
});
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-700 font-sans antialiased flex flex-col lg:flex-row">

        <ProfileIncompleteModal />

        <!-- ==============================
             MOBILE HEADER
        ============================== -->
        <header class="lg:hidden sticky top-0 z-40 w-full bg-white border-b border-slate-200/80 px-4 h-[60px] flex items-center justify-between shadow-sm">
            <Link :href="route('Home') || '/'" class="flex items-center gap-2 transition-transform hover:scale-[1.02] duration-200">
                <img src="/kitasewa-logo.png" alt="KitaSewa Logo" class="h-6 w-auto object-contain" />
                <span class="font-black text-xl tracking-tight text-[#0A2540] mt-0.5">
                    kitasewa<span class="text-[#FFC000]">.id</span>
                </span>
            </Link>
            <button
                @click="showMobileMenu = !showMobileMenu"
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 transition-colors"
            >
                <component :is="showMobileMenu ? X : Menu" class="text-sm transition-transform w-5 h-5" />
            </button>
        </header>

        <!-- ==============================
             SIDEBAR COMPONENT
        ============================== -->
        <!-- Desktop Sidebar (Fixed) -->
        <Sidebar
            class="hidden lg:flex sticky top-0 h-screen"
            :role="role"
            :menu="menu"
        />

        <!-- Mobile Sidebar Overlay -->
        <div
            v-show="showMobileMenu"
            class="lg:hidden fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm transition-opacity"
            @click="showMobileMenu = false"
        ></div>

        <!-- Mobile Sidebar Panel -->
        <div
            class="lg:hidden fixed top-[60px] left-0 bottom-0 z-50 w-[260px] bg-white transform transition-transform duration-300 ease-in-out border-r border-slate-200 overflow-y-auto"
            :class="showMobileMenu ? 'translate-x-0' : '-translate-x-full'"
        >
            <Sidebar
                class="!flex !border-0 !w-full min-h-full"
                :role="role"
                :menu="menu"
            />
        </div>

        <!-- ==============================
             MAIN CONTENT
        ============================== -->
        <main class="flex-1 min-w-0 flex flex-col min-h-[calc(100vh-60px)] lg:min-h-screen">
            <!-- TOPBAR COMPONENT -->
            <Topbar :title="title" :description="description" :breadcrumbs="breadcrumbs">
                <template #leftAction v-if="$slots.leftAction">
                    <slot name="leftAction" />
                </template>
                <template #default>
                    <slot name="action" />
                </template>
            </Topbar>

            <slot name="afterTopbar" />

            <div class="p-4 md:p-6 lg:p-6 xl:p-8 w-full max-w-[1400px] mx-auto flex-1 flex flex-col">


                <!-- PAGE CONTENT SLOT -->
                <div class="flex-1">
                    <slot />
                </div>
            </div>
        </main>
        
        <!-- Toast Notifikasi Real-time -->
        <NotificationToast ref="toastRef" />

        <!-- Logout Confirmation Modal -->
        <ConfirmModal
            :show="showLogoutModal"
            type="primary"
            title="Keluar dari Akun?"
            message="Anda akan keluar dari sesi ini. Anda dapat masuk kembali kapan saja."
            confirmText="Ya, Keluar"
            cancelText="Batal"
            @confirm="handleLogoutConfirm"
            @cancel="showLogoutModal = false"
        >
            <template #icon>
                <div class="w-28 mx-auto mb-2">
                    <LogoutIllustrationIcon class="w-full h-auto" />
                </div>
            </template>
        </ConfirmModal>
    </div>
</template>
