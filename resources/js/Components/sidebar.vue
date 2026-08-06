<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const ownerActivityCounts = computed(() => page.props.ownerActivityCounts || {
    propertyReview: 0,
    bookingReview: 0,
    verificationReview: 0,
});
const user = computed(() => page.props.auth?.user || {
    name: 'Budi Santoso',
    email: 'owner@kusewa.id'
});
const showProfileMenu = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between p-4 shrink-0 hidden lg:flex">
        <div class="space-y-6">
            <!-- Brand Logo -->
            <div class="flex items-center justify-between px-2 py-1">
                <Link :href="route('Home')" class="flex items-center gap-1.5">
                    <span class="font-black text-2xl tracking-tight text-[#0A2540]">
                        kusewa<span class="text-[#FFC000]">.id</span>
                    </span>
                </Link>
                <span class="text-[9px] font-black text-[#0A2540] bg-[#FFC000]/20 px-2 py-0.5 rounded-md uppercase">Owner</span>
            </div>

            <!-- Profile Switcher -->
            <div class="relative">
            <button @click="showProfileMenu = !showProfileMenu" type="button" class="w-full flex items-center justify-between p-2 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-100 transition">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-[#0A2540] text-[#FFC000] flex items-center justify-center font-black text-xs">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-slate-800 truncate">{{ user.name }}</h4>
                        <p class="text-[10px] text-slate-400 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <i :class="showProfileMenu ? 'rotate-180' : ''" class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform"></i>
            </button>
            <div v-if="showProfileMenu" class="absolute z-50 top-full mt-2 w-full rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg text-xs">
                <Link :href="route('profile.edit')" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium">
                    <i class="fa-solid fa-user w-3 text-slate-400"></i> Profil Saya
                </Link>
                <Link :href="route('owner.settings')" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 font-medium">
                    <i class="fa-solid fa-gear w-3 text-slate-400"></i> Pengaturan Akun
                </Link>
                <button @click="logout" type="button" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50 font-medium">
                    <i class="fa-solid fa-right-from-bracket w-3"></i> Keluar
                </button>
            </div>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input 
                    type="text" 
                    placeholder="Cari menu / aset..." 
                    class="w-full bg-slate-50 text-xs pl-8 pr-3 py-1.5 rounded-lg border border-slate-200/80 focus:outline-none focus:bg-white focus:border-slate-300 transition"
                />
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1 text-xs">
                <Link :href="route('owner.dashboard')" :class="[route().current('owner.dashboard') ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center gap-3 px-3 py-2 rounded-lg transition']">
                    <i class="fa-solid fa-house-chimney text-[#0A2540]"></i>
                    <span>Dashboard</span>
                </Link>

                <Link :href="route('owner.property.index')" :class="[route().current('owner.property.*') ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center justify-between px-3 py-2 rounded-lg transition']">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-building text-slate-400"></i>
                        <span>Properti & Aset</span>
                    </div>
                    <span v-if="ownerActivityCounts.propertyReview > 0" class="bg-[#FFC000]/20 text-[#0A2540] text-[10px] font-black px-1.5 py-0.5 rounded">{{ ownerActivityCounts.propertyReview }}</span>
                </Link>

                <Link :href="route('owner.bookings')" :class="[route().current('owner.bookings') ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center justify-between px-3 py-2 rounded-lg transition']">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-receipt text-slate-400"></i>
                        <span>Pemesanan</span>
                    </div>
                    <span v-if="ownerActivityCounts.bookingReview > 0" class="bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full">{{ ownerActivityCounts.bookingReview }}</span>
                </Link>

                <!-- BIAYA BULANAN -->
                <Link :href="route('owner.monthly-payment')" :class="[route().current('owner.monthly-payment') ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center gap-3 px-3 py-2 rounded-lg transition']">
                    <i class="fa-solid fa-credit-card text-slate-400"></i>
                    <span>Biaya Bulanan</span>
                </Link>

                <Link :href="route('owner.finance')" :class="[route().current('owner.finance') ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center gap-3 px-3 py-2 rounded-lg transition']">
                    <i class="fa-solid fa-wallet text-slate-400"></i>
                    <span>Keuangan</span>
                </Link>

                <div class="pt-2">
                    <Link :href="route('owner.verification')" :class="[route().current('owner.verification') ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center justify-between px-3 py-2 rounded-lg transition']">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-id-card text-slate-400"></i>
                            <span>Verifikasi Berkas</span>
                        </div>
                        <span v-if="ownerActivityCounts.verificationReview > 0" class="bg-amber-100 text-amber-700 text-[9px] font-bold px-1.5 py-0.5 rounded-full">{{ ownerActivityCounts.verificationReview }}</span>
                        <i v-else class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                    </Link>
                </div>
            </nav>

            <hr class="border-slate-100" />

            <nav class="space-y-1 text-xs">
                <Link :href="route('owner.settings')" :class="[route().current('owner.settings') ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center gap-3 px-3 py-2 rounded-lg transition']">
                    <i class="fa-solid fa-gear text-slate-400"></i>
                    <span>Pengaturan Akun</span>
                </Link>
                <Link :href="route('owner.help')" :class="[route().current('owner.help') ? 'bg-slate-100 text-[#0A2540] font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium', 'flex items-center gap-3 px-3 py-2 rounded-lg transition']">
                    <i class="fa-solid fa-headset text-slate-400"></i>
                    <span>Bantuan kusewa</span>
                </Link>
            </nav>
        </div>

    </aside>
</template>
