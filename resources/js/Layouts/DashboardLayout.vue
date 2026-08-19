<script setup>
import { ref, watch, computed } from 'vue';
import Sidebar from '@/Components/sidebar.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { X, Menu } from 'lucide-vue-next';

import { getOwnerMenu, getAdminMenu } from '@/Config/menus';

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    role: { type: String, required: true },
    breadcrumbs: { type: Array, default: () => [] },
    // Sub-menu kontekstual untuk detail page (misal: tab aset)
    subMenu: { type: Array, default: () => [] },
    subMenuParentRouteName: { type: String, default: null },
});

const page = usePage();

// Dapatkan sidebar counts dari global props (di-inject via HandleInertiaRequests)
const sidebarCounts = computed(() => page.props.sidebarCounts || {});

// Menu yang dipakai bergantung pada role yang diberikan dari props
const menu = computed(() => props.role === 'Admin' ? getAdminMenu(sidebarCounts.value) : getOwnerMenu(sidebarCounts.value));

// ==========================================
// MOBILE SIDEBAR BEHAVIOR
// ==========================================
const showMobileMenu = ref(false);

watch(() => page.url, () => {
    showMobileMenu.value = false;
});
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans antialiased flex flex-col lg:flex-row">

        <!-- ==============================
             MOBILE HEADER
        ============================== -->
        <header class="lg:hidden sticky top-0 z-40 w-full bg-white border-b border-slate-200/80 px-4 h-[60px] flex items-center justify-between shadow-sm">
            <Link :href="route('Home') || '/'" class="flex items-center gap-2 transition-transform hover:scale-[1.02] duration-200">
                <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-6 w-auto object-contain" />
                <span class="font-black text-xl tracking-tight text-[#0A2540] mt-0.5">
                    kusewa<span class="text-[#FFC000]">.id</span>
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
            :subMenu="subMenu"
            :subMenuParentRouteName="subMenuParentRouteName"
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
            <div class="p-4 md:p-6 lg:p-6 xl:p-8 w-full max-w-[1400px] mx-auto flex-1 flex flex-col">

                <!-- DASHBOARD HEADER -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <!-- BREADCRUMBS -->
                        <nav v-if="breadcrumbs && breadcrumbs.length" class="flex text-[10px] text-slate-400 font-medium mb-1.5 space-x-1.5">
                            <template v-for="(bc, idx) in breadcrumbs" :key="idx">
                                <Link v-if="bc.route" :href="bc.route" class="hover:text-[#0A2540] transition-colors">{{ bc.label }}</Link>
                                <span v-else class="text-slate-600">{{ bc.label }}</span>
                                <span v-if="idx < breadcrumbs.length - 1" class="text-slate-300">/</span>
                            </template>
                        </nav>

                        <h1 class="text-2xl font-black text-slate-900">{{ title }}</h1>
                        <p v-if="description" class="text-xs text-slate-400 mt-1">{{ description }}</p>
                    </div>

                    <!-- Action Button Slot -->
                    <div>
                        <slot name="action" />
                    </div>
                </div>

                <!-- PAGE CONTENT SLOT -->
                <div class="flex-1">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
