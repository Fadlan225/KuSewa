<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { User, Settings, Headset, LogOut, ChevronRight, ChevronLeft, ChevronDown, Home } from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    role: { type: String, default: 'User' },
    menu: { type: Array, default: () => [] },
    bottomMenu: { type: Array, default: () => [] },
    // Sub-menu kontekstual (tampil di bawah parent aktif saat di detail page)
    subMenu: { type: Array, default: () => [] },
    subMenuParentRouteName: { type: String, default: null },
});

const roleBadgeClass = computed(() => props.role === 'Admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-[#FFC000]/20 text-[#0A2540]');

const page = usePage();

// Hanya mengambil data nyata dari session. Jika gagal, akan error sesuai standar SaaS (no dummy data)
const user = computed(() => page.props.auth.user);

const showProfileMenu = ref(false);
const profileMenuRef = ref(null);

const isCollapsed = ref(false);

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('sidebar_collapsed', isCollapsed.value);
};

const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
};

// Handle klik di luar untuk menutup dropdown
const closeProfileMenu = (e) => {
    if (showProfileMenu.value && profileMenuRef.value && !profileMenuRef.value.contains(e.target)) {
        showProfileMenu.value = false;
    }
};

// Handle tekan ESC untuk menutup dropdown
const handleEscape = (e) => {
    if (e.key === 'Escape' && showProfileMenu.value) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeProfileMenu);
    document.addEventListener('keydown', handleEscape);

    const stored = localStorage.getItem('sidebar_collapsed');
    if (stored === 'true') {
        isCollapsed.value = true;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', closeProfileMenu);
    document.removeEventListener('keydown', handleEscape);
});

const handleLogout = () => {
    showProfileMenu.value = false;
    // Panggil modal global logout yang sudah ada di sistem
    window.dispatchEvent(new CustomEvent('open-logout-modal'));
};
</script>

<template>
    <aside :class="[isCollapsed ? 'w-20' : 'w-60', 'h-full max-h-screen bg-white border-r border-slate-200/80 flex flex-col p-3 md:p-4 shrink-0 transition-all duration-300 relative z-40']">
        <!-- Brand Logo -->
        <div class="flex items-center px-2 py-1 mb-6 shrink-0 transition-all duration-300" :class="isCollapsed ? 'justify-center' : 'justify-start'">
            <div class="flex items-center gap-2 overflow-hidden">
                <!-- Logo acts as collapse toggle on desktop -->
                <img @click="toggleCollapse" src="/kitasewa-logo.png" alt="KitaSewa Logo" class="h-6 w-auto object-contain shrink-0 cursor-pointer transition-transform hover:scale-110 hidden lg:block" title="Sembunyikan/Tampilkan Menu" />

                <!-- Logo acts as home link on mobile -->
                <Link :href="route('Home') || '/'" class="lg:hidden shrink-0">
                    <img src="/kitasewa-logo.png" alt="KitaSewa Logo" class="h-6 w-auto object-contain" />
                </Link>

                <!-- Brand Name acts as home link -->
                <Link v-if="!isCollapsed" :href="route('Home') || '/'" class="transition-transform hover:scale-[1.02] duration-200">
                    <span class="font-black text-lg tracking-tight text-[#0A2540] mt-0.5 whitespace-nowrap transition-opacity duration-300">
                        kitasewa<span class="text-[#FFC000]">.id</span>
                    </span>
                </Link>
            </div>
        </div>

            <!-- Profile Switcher -->
            <div class="relative mb-6 shrink-0" ref="profileMenuRef">
                <button @click="toggleProfileMenu" type="button" class="w-full flex items-center p-2 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-100 transition focus:outline-none" :class="isCollapsed ? 'justify-center' : 'justify-between'">
                    <div class="flex items-center gap-2.5 min-w-0" :class="isCollapsed ? 'justify-center' : ''">
                        <!-- Cek foto profil atau avatar (sesuai struktur standar Laravel/SaaS) -->
                        <img
                            v-if="user.profile_photo_url || user.avatar"
                            :src="user.profile_photo_url || user.avatar"
                            :alt="user.name"
                            class="w-8 h-8 rounded-lg object-cover bg-slate-200 shrink-0 border border-slate-200"
                        />
                        <!-- Fallback ke inisial huruf pertama -->
                        <div v-else class="w-8 h-8 rounded-lg bg-[#0A2540] text-[#FFC000] flex items-center justify-center font-black text-xs uppercase shrink-0">
                            {{ user.name.charAt(0) }}
                        </div>
                        <div v-if="!isCollapsed" class="min-w-0 text-left flex-1 whitespace-nowrap">
                            <h4 class="text-xs font-bold text-slate-800 truncate">{{ user.name }}</h4>
                            <p class="text-[10px] text-slate-400 truncate">{{ role }}</p>
                        </div>
                    </div>
                    <ChevronDown v-if="!isCollapsed" :class="showProfileMenu ? 'rotate-180' : ''" class="text-[10px] w-3 h-3 text-slate-400 transition-transform duration-200 shrink-0" />
                </button>

                <!-- Dropdown Animasi Vue Transition -->
                <Transition
                    enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95"
                >
                    <div v-if="showProfileMenu" :class="[
                        'absolute z-50 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg text-xs',
                        isCollapsed ? 'left-full top-0 ml-3 w-48 origin-top-left' : 'top-full mt-2 w-full origin-top'
                    ]">
                        <a :href="route('Home') || '/'" target="_blank" rel="noopener noreferrer" @click="showProfileMenu = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                            <Home class="w-4 text-center text-slate-400" /> Halaman Utama
                        </a>
                        <Link :href="route('profile.edit')" @click="showProfileMenu = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                            <User class="w-4 text-center text-slate-400" /> Profil Saya
                        </Link>

                        <div class="h-px bg-slate-100 my-1"></div>

                        <button @click="handleLogout" type="button" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50 font-medium text-left transition-colors">
                            <LogOut class="w-4 text-center" /> Keluar
                        </button>
                    </div>
                </Transition>
            </div>

            <!-- Navigation Links Wrapper with Fade Effect -->
            <div class="relative flex-1 min-h-0 -mx-2">
                <div class="h-full overflow-y-auto no-scrollbar px-2 pb-6">
                    <nav class="space-y-2 text-xs">
                <template v-for="(item, idx) in menu" :key="idx">
                    <!-- Jika ada item divider -->
                    <div v-if="item.divider" class="pt-2 pb-1">
                        <hr class="border-slate-100 border-dashed" />
                    </div>

                    <!-- Otomatis mendeteksi status aktif dari rute laravel menggunakan routeName -->
                    <template v-else>
                        <Link
                            :href="item.route"
                            :title="isCollapsed ? item.label : ''"
                            :class="[route().current(item.routeName) ? 'text-[#0A2540] font-bold border-l-[4px] border-[#FFC000] bg-slate-50/50 rounded-r-lg' : 'text-slate-600 hover:bg-slate-50 font-medium border-l-[4px] border-transparent rounded-r-lg', 'flex items-center px-3 py-2.5 transition-all duration-200', isCollapsed ? 'justify-center' : 'justify-between']"
                        >
                            <div class="flex items-center gap-3">
                                <AppIcon v-if="typeof item.icon === 'string'" :iconClass="item.icon" :class="route().current(item.routeName) ? 'text-[#FFC000]' : 'text-slate-400'" class="w-4 text-center" />
                                <component v-else :is="item.icon" :class="[route().current(item.routeName) ? 'text-[#FFC000]' : 'text-slate-400', 'w-4 text-center']" />
                                <span v-if="!isCollapsed" class="whitespace-nowrap">{{ item.label }}</span>
                            </div>

                            <template v-if="!isCollapsed && (item.badge || item.badgeIcon)">
                                <AppIcon :iconClass="item.badgeIcon" v-if="item.badgeIcon && !item.badge" />
                                <span v-else-if="item.badge" :class="item.badgeClass || 'bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded text-[9px] font-bold'">
                                    {{ item.badge }}
                                </span>
                            </template>
                        </Link>

                        <!-- Sub-menu kontekstual (tampil jika parent aktif & ada subMenu) -->
                        <div
                            v-if="subMenu.length > 0 && subMenuParentRouteName && item.routeName === subMenuParentRouteName && route().current(item.routeName)"
                            :class="isCollapsed ? 'mt-1 space-y-1' : 'ml-3 pl-3 border-l-2 border-slate-200 space-y-0.5 py-1'"
                        >
                            <button
                                v-for="sub in subMenu"
                                :key="sub.key"
                                @click="sub.onClick && sub.onClick()"
                                :title="isCollapsed ? sub.label : ''"
                                :class="[
                                    sub.active
                                        ? 'bg-[#FFC000]/10 text-[#0A2540] font-bold'
                                        : 'text-slate-500 hover:bg-slate-50 font-medium',
                                    'w-full flex items-center rounded-lg transition-colors',
                                    isCollapsed ? 'justify-center px-2 py-2' : 'justify-between gap-2 px-2.5 py-1.5 text-left'
                                ]"
                            >
                                <div class="flex items-center" :class="isCollapsed ? 'justify-center' : 'gap-2'">
                                    <AppIcon v-if="typeof sub.icon === 'string'" :iconClass="sub.icon" :class="isCollapsed ? 'w-4 text-sm' : 'w-3.5 text-center text-[11px]'" />
                                    <component v-else :is="sub.icon" :class="[sub.active ? 'text-[#0A2540]' : 'text-slate-400', isCollapsed ? 'w-4 text-sm' : 'w-3.5 text-center text-[11px]']" />
                                    <span v-if="!isCollapsed" class="text-[11px] whitespace-nowrap">{{ sub.label }}</span>
                                </div>
                                <span v-if="!isCollapsed && sub.badge" class="text-[9px] font-black bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-full shrink-0">
                                    {{ sub.badge }}
                                </span>
                            </button>
                        </div>
                    </template>
                </template>
                </nav>
                </div>
                <!-- Fade Shadow Bottom -->
                <div class="pointer-events-none absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-white to-transparent"></div>
            </div>

        <!-- Bottom Navigation Links -->
        <div v-if="bottomMenu && bottomMenu.length" class="shrink-0 pt-4 mt-auto">
            <hr class="border-slate-100 mb-4" />
            <nav class="space-y-2 text-xs">
                <template v-for="(item, idx) in bottomMenu" :key="idx">
                    <div v-if="item.divider" class="pt-2 pb-1">
                        <hr class="border-slate-100 border-dashed" />
                    </div>
                    <Link
                        v-else
                        :href="item.route"
                        :title="isCollapsed ? item.label : ''"
                        :class="[route().current(item.routeName) ? 'text-[#0A2540] font-bold border-l-[4px] border-[#FFC000] bg-slate-50/50 rounded-r-lg' : 'text-slate-600 hover:bg-slate-50 font-medium border-l-[4px] border-transparent rounded-r-lg', 'flex items-center px-3 py-2.5 transition-all duration-200', isCollapsed ? 'justify-center' : 'justify-between']"
                    >
                        <div class="flex items-center gap-3">
                            <AppIcon v-if="typeof item.icon === 'string'" :iconClass="item.icon" :class="route().current(item.routeName) ? 'text-[#FFC000]' : 'text-slate-400'" class="w-4 text-center" />
                            <component v-else :is="item.icon" :class="[route().current(item.routeName) ? 'text-[#FFC000]' : 'text-slate-400', 'w-4 text-center']" />
                            <span v-if="!isCollapsed" class="whitespace-nowrap">{{ item.label }}</span>
                        </div>
                        <template v-if="!isCollapsed && (item.badge || item.badgeIcon)">
                            <AppIcon :iconClass="item.badgeIcon" v-if="item.badgeIcon && !item.badge" />
                            <span v-else-if="item.badge" :class="item.badgeClass || 'bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded text-[9px] font-bold'">
                                {{ item.badge }}
                            </span>
                        </template>
                    </Link>
                </template>
            </nav>
        </div>
    </aside>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
