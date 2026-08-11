<script setup>
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
    <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between p-4 shrink-0">
        <div class="space-y-6">
            <!-- Brand Logo -->
            <div class="flex items-center justify-between px-2 py-1">
                <Link :href="route('Home') || '/'" class="flex items-center gap-2 transition-transform hover:scale-[1.02] duration-200">
                    <img src="/kusewa-logo.png" alt="KuSewa Logo" class="h-6 w-auto object-contain" />
                    <span class="font-black text-xl tracking-tight text-[#0A2540] mt-0.5">
                        kusewa<span class="text-[#FFC000]">.id</span>
                    </span>
                </Link>
            </div>

            <!-- Profile Switcher -->
            <div class="relative" ref="profileMenuRef">
                <button @click="toggleProfileMenu" type="button" class="w-full flex items-center justify-between p-2 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-100 transition focus:outline-none">
                    <div class="flex items-center gap-2.5 min-w-0">
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
                        <div class="min-w-0 text-left flex-1">
                            <h4 class="text-xs font-bold text-slate-800 truncate">{{ user.name }}</h4>
                            <p class="text-[10px] text-slate-400 truncate">{{ role }}</p>
                        </div>
                    </div>
                    <i :class="showProfileMenu ? 'rotate-180' : ''" class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 shrink-0"></i>
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
                    <div v-if="showProfileMenu" class="absolute z-50 top-full mt-2 w-full rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg text-xs origin-top">
                        <Link :href="route('profile.edit')" @click="showProfileMenu = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                            <i class="fa-solid fa-user w-4 text-center text-slate-400"></i> Profil Saya
                        </Link>
                        <Link v-if="role === 'Owner'" :href="route('owner.settings')" @click="showProfileMenu = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                            <i class="fa-solid fa-gear w-4 text-center text-slate-400"></i> Pengaturan Akun
                        </Link>
                        <Link v-if="role === 'Owner'" :href="route('owner.help')" @click="showProfileMenu = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                            <i class="fa-solid fa-headset w-4 text-center text-slate-400"></i> Bantuan
                        </Link>

                        <div class="h-px bg-slate-100 my-1"></div>

                        <button @click="handleLogout" type="button" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50 font-medium text-left transition-colors">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Keluar
                        </button>
                    </div>
                </Transition>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1 text-xs">
                <template v-for="(item, idx) in menu" :key="idx">
                    <!-- Jika ada item divider -->
                    <div v-if="item.divider" class="pt-2 pb-1">
                        <hr class="border-slate-100 border-dashed" />
                    </div>

                    <!-- Otomatis mendeteksi status aktif dari rute laravel menggunakan routeName -->
                    <template v-else>
                        <Link
                            :href="item.route"
                            :class="[route().current(item.routeName) ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center justify-between px-3 py-2 rounded-lg transition-colors']"
                        >
                            <div class="flex items-center gap-3">
                                <i :class="[item.icon, route().current(item.routeName) ? 'text-[#0A2540]' : 'text-slate-400', 'w-4 text-center']"></i>
                                <span>{{ item.label }}</span>
                            </div>

                            <template v-if="item.badge || item.badgeIcon">
                                <i v-if="item.badgeIcon && !item.badge" :class="item.badgeIcon"></i>
                                <span v-else-if="item.badge" :class="item.badgeClass || 'bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded text-[9px] font-bold'">
                                    {{ item.badge }}
                                </span>
                            </template>
                        </Link>

                        <!-- Sub-menu kontekstual (tampil jika parent aktif & ada subMenu) -->
                        <div
                            v-if="subMenu.length > 0 && subMenuParentRouteName && item.routeName === subMenuParentRouteName && route().current(item.routeName)"
                            class="ml-3 pl-3 border-l-2 border-slate-200 space-y-0.5 py-1"
                        >
                            <button
                                v-for="sub in subMenu"
                                :key="sub.key"
                                @click="sub.onClick && sub.onClick()"
                                :class="[
                                    sub.active
                                        ? 'bg-[#FFC000]/10 text-[#0A2540] font-bold'
                                        : 'text-slate-500 hover:bg-slate-50 font-medium',
                                    'w-full flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg transition-colors text-left'
                                ]"
                            >
                                <div class="flex items-center gap-2">
                                    <i :class="[sub.icon, sub.active ? 'text-[#0A2540]' : 'text-slate-400', 'w-3.5 text-center text-[11px]']"></i>
                                    <span class="text-[11px]">{{ sub.label }}</span>
                                </div>
                                <span v-if="sub.badge" class="text-[9px] font-black bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-full shrink-0">
                                    {{ sub.badge }}
                                </span>
                            </button>
                        </div>
                    </template>
                </template>
            </nav>
        </div>

        <!-- Bottom Navigation Links -->
        <div v-if="bottomMenu && bottomMenu.length">
            <hr class="border-slate-100 mb-4" />
            <nav class="space-y-1 text-xs">
                <template v-for="(item, idx) in bottomMenu" :key="idx">
                    <div v-if="item.divider" class="pt-2 pb-1">
                        <hr class="border-slate-100 border-dashed" />
                    </div>
                    <Link
                        v-else
                        :href="item.route"
                        :class="[route().current(item.routeName) ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center justify-between px-3 py-2 rounded-lg transition-colors']"
                    >
                        <div class="flex items-center gap-3">
                            <i :class="[item.icon, route().current(item.routeName) ? 'text-[#0A2540]' : 'text-slate-400', 'w-4 text-center']"></i>
                            <span>{{ item.label }}</span>
                        </div>
                        <template v-if="item.badge || item.badgeIcon">
                            <i v-if="item.badgeIcon && !item.badge" :class="item.badgeIcon"></i>
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
