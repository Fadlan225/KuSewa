<script setup>
import { ref, onMounted, computed, useSlots } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import NotificationDropdown from '@/Components/ui/NotificationDropdown.vue';
import { useNotifications } from '@/Composables/useNotifications';
import AssetSwitcher from '@/Components/owner/AssetSwitcher.vue';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    breadcrumbs: { type: Array, default: () => [] }
});

const slots = useSlots();
const hasLeftContent = computed(() => !!slots.leftAction || route().current()?.startsWith('owner.'));

const page = usePage();
const { unreadCount, init: initNotifications } = useNotifications();
const isNotifDropdownOpen = ref(false);

const currentAssetSlug = computed(() => {
    return page.props.active_asset_slug;
});

onMounted(() => {
    if (page.props.auth?.user) {
        initNotifications();
    }
});
</script>

<template>
    <div class="bg-white border-b border-slate-200/80 px-4 md:px-6 lg:px-8 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-6 sticky top-[60px] lg:top-0 z-30">
        <div class="flex items-center gap-3 flex-1 w-full min-w-0">
            <slot name="leftAction" />
            
            <AssetSwitcher v-if="route().current()?.startsWith('owner.')" :current-asset-slug="currentAssetSlug" class="flex-1 min-w-0" />
            
            <div v-if="!hasLeftContent" class="flex-1 w-full min-w-0 hidden md:block">
                <!-- BREADCRUMBS -->
                <nav v-if="breadcrumbs && breadcrumbs.length" class="flex text-[10px] text-slate-400 font-medium mb-1 space-x-1.5">
                    <template v-for="(bc, idx) in breadcrumbs" :key="idx">
                        <Link v-if="bc.route" :href="bc.route" class="hover:text-[#0A2540] transition-colors">{{ bc.label }}</Link>
                        <span v-else class="text-slate-600">{{ bc.label }}</span>
                        <span v-if="idx < breadcrumbs.length - 1" class="text-slate-300">/</span>
                    </template>
                </nav>
                <h1 v-if="title" class="text-xl md:text-2xl font-black tracking-tight text-[#0A2540] truncate">{{ title }}</h1>
                <p v-if="description" class="text-xs md:text-sm text-slate-500 font-medium truncate mt-0.5">{{ description }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3 self-end sm:self-auto">
            <!-- Action Button Slot (e.g. Daftarkan Aset) -->
            <slot />

            <!-- Notification Button -->
            <div v-if="page.props.auth?.user" class="relative">
                <button
                    @click="isNotifDropdownOpen = !isNotifDropdownOpen"
                    class="relative w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-105 focus:outline-none text-[#0A2540] hover:bg-gray-100"
                    title="Notifikasi"
                >
                    <Bell class="w-5 h-5" />
                    <!-- Badge Unread Count -->
                    <span
                        v-if="unreadCount > 0"
                        class="absolute -top-1 -right-1 flex items-center justify-center bg-red-500 text-white text-[10px] font-black min-w-[16px] h-[16px] px-1 rounded-full shadow-sm leading-none"
                    >
                        {{ unreadCount > 99 ? '99+' : unreadCount }}
                    </span>
                </button>

                <!-- Backdrop untuk tutup dropdown -->
                <div v-if="isNotifDropdownOpen" @click="isNotifDropdownOpen = false" class="fixed inset-0 z-40"></div>

                <!-- Dropdown Notifikasi -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform scale-100 opacity-100 translate-y-0"
                    leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                >
                    <div
                        v-if="isNotifDropdownOpen"
                        class="absolute top-[130%] right-0 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 origin-top-right overflow-hidden"
                    >
                        <NotificationDropdown @close="isNotifDropdownOpen = false" />
                    </div>
                </Transition>
            </div>
        </div>
    </div>
</template>
