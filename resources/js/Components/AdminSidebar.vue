<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const isActive = (pattern) => route().current(pattern);

const openMenus = ref({
    master: true,
    validasi: false,
    transaksi: false,
    konten: false,
    sistem: false
});

const toggleMenu = (key) => {
    openMenus.value[key] = !openMenus.value[key];
};
</script>

<template>
    <!-- 
      - Menggunakan h-full dan flex flex-col agar pas di dalam kontainer utama layout.
      - overflow-hidden pada pembungkus luar agar container induk tidak ikut memanjang.
      - max-h-screen untuk membatasi tinggi maksimal sesuai layar.
    -->
    <aside class="no-scrollbar hidden h-full w-[275px] flex-shrink-0 flex-col rounded-2xl bg-white p-4 text-slate-700 shadow-sm xl:flex border border-slate-100 max-h-screen overflow-hidden">
        
        <!-- Logo / Brand Header (Bersifat tetap / tidak ikut ter-scroll) -->
        <div class="flex flex-shrink-0 items-center gap-3 px-3 py-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFC000] text-slate-950 font-bold shadow-sm">
                <i class="fa-solid fa-shield-halved text-lg"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold tracking-tight text-slate-800">Admin Panel</h2>
                <p class="text-[10px] text-slate-400">Sistem Pengelolaan Aset</p>
            </div>
        </div>

        <hr class="my-3 flex-shrink-0 border-slate-100" />

        <!-- 
          - Navigation Menu Container
          - Diberikan kelas "flex-1 overflow-y-auto" dan "no-scrollbar" 
            agar area menu ini saja yang dapat di-scroll secara mandiri sampai bawah.
        -->
        <div class="flex-1 overflow-y-auto no-scrollbar space-y-1 text-xs pr-1">
            <!-- Main Dashboard -->
            <Link
                :href="route('admin.dashboard')"
                class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 font-semibold transition-all duration-200"
                :class="isActive('admin.dashboard') 
                    ? 'bg-gradient-to-r from-slate-900 to-slate-800 text-white shadow-md shadow-slate-900/10' 
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
            >
                <i class="fa-solid fa-gauge-high w-5 text-center text-sm" :class="isActive('admin.dashboard') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                <span>Dashboard</span>
            </Link>

            <!-- Section: Pengelolaan Akun & Pengguna -->
            <div class="pt-2">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pengguna & Akun</p>
                
                <Link
                    :href="route('admin.pengajuan-akun')"
                    class="mt-1 flex items-center gap-3 rounded-xl px-3.5 py-2 font-semibold transition-all duration-200"
                    :class="isActive('admin.pengajuan-akun') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                >
                    <i class="fa-solid fa-file-lines w-5 text-center" :class="isActive('admin.pengajuan-akun') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                    <span>Kelola Pengajuan Akun</span>
                </Link>

                <Link
                    :href="route('admin.user-accounts')"
                    class="flex items-center gap-3 rounded-xl px-3.5 py-2 font-semibold transition-all duration-200"
                    :class="isActive('admin.user-accounts') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                >
                    <i class="fa-solid fa-users w-5 text-center" :class="isActive('admin.user-accounts') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                    <span>Akun Penyewa & Pemilik</span>
                </Link>

                <Link
                    :href="route('admin.admin-accounts')"
                    class="flex items-center gap-3 rounded-xl px-3.5 py-2 font-semibold transition-all duration-200"
                    :class="isActive('admin.admin-accounts') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                >
                    <i class="fa-solid fa-user-shield w-5 text-center" :class="isActive('admin.admin-accounts') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                    <span>Akun Administrator</span>
                </Link>
            </div>

            <!-- Section: Validasi & Master Aset -->
            <div class="pt-2">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Validasi & Aset</p>
                
                <Link
                    :href="route('admin.asset-validation')"
                    class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                    :class="isActive('admin.asset-validation') ? 'bg-slate-100 text-slate-900' : ''"
                >
                    <i class="fa-solid fa-clipboard-check w-5 text-center" :class="isActive('admin.asset-validation') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                    <span>Validasi Aset & Pengajuan</span>
                </Link>

                <Link
                    :href="route('admin.property-assets')"
                    class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                    :class="isActive('admin.property-assets') ? 'bg-slate-100 text-slate-900' : ''"
                >
                    <i class="fa-solid fa-building w-5 text-center" :class="isActive('admin.property-assets') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                    <span>Aset Properti</span>
                </Link>

                <Link
                    :href="route('admin.category-facility')"
                    class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                    :class="isActive('admin.category-facility') ? 'bg-slate-100 text-slate-900' : ''"
                >
                    <i class="fa-solid fa-layer-group w-5 text-center" :class="isActive('admin.category-facility') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                    <span>Kategori & Fasilitas</span>
                </Link>
            </div>

            <!-- Section: Transaksi & Keuangan -->
            <div class="pt-2">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Keuangan & Pembayaran</p>
                
                    <Link
                        :href="route('admin.payment-system')"
                        class="mt-1 flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        :class="isActive('admin.payment-system') ? 'bg-slate-100 text-slate-900' : ''"
                    >
                        <i class="fa-solid fa-credit-card w-5 text-center" :class="isActive('admin.payment-system') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                        <span>Sistem Pembayaran</span>
                    </Link>

                    <Link
                        :href="route('admin.service-fee')"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        :class="isActive('admin.service-fee') ? 'bg-slate-100 text-slate-900' : ''"
                    >
                        <i class="fa-solid fa-hand-holding-dollar w-5 text-center" :class="isActive('admin.service-fee') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                        <span>Biaya Layanan & Sanksi</span>
                    </Link>

                    <Link
                        :href="route('admin.promo-discount')"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        :class="isActive('admin.promo-discount') ? 'bg-slate-100 text-slate-900' : ''"
                    >
                        <i class="fa-solid fa-ticket w-5 text-center" :class="isActive('admin.promo-discount') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                        <span>Promo & Diskon</span>
                    </Link>
                </div>

                <!-- Section: Konten & Komunikasi -->
                <div class="pt-2">
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Konten & Informasi</p>
                    
                    <Link
                        :href="route('admin.cms-manager')"
                        class="mt-1 flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        :class="isActive('admin.cms-manager') ? 'bg-slate-100 text-slate-900' : ''"
                    >
                        <i class="fa-solid fa-globe w-5 text-center" :class="isActive('admin.cms-manager') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                        <span>Kelola CMS Website</span>
                    </Link>

                    <Link
                        :href="route('admin.system-notifications')"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        :class="isActive('admin.system-notifications') ? 'bg-slate-100 text-slate-900' : ''"
                    >
                        <i class="fa-regular fa-bell w-5 text-center" :class="isActive('admin.system-notifications') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                        <span>Notifikasi Sistem</span>
                    </Link>

                    <Link
                        :href="route('admin.user-reports')"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        :class="isActive('admin.user-reports') ? 'bg-slate-100 text-slate-900' : ''"
                    >
                        <i class="fa-solid fa-flag w-5 text-center" :class="isActive('admin.user-reports') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                        <span>Laporan Pengguna & Rating</span>
                    </Link>
                </div>

                <!-- Section: Pengaturan & Sistem -->
                <div class="pt-2 pb-2">
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Sistem & Keamanan</p>

                    <Link
                        :href="route('admin.activity-log')"
                        class="mt-1 flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        :class="isActive('admin.activity-log') ? 'bg-slate-100 text-slate-900' : ''"
                    >
                        <i class="fa-solid fa-list-check w-5 text-center" :class="isActive('admin.activity-log') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                        <span>Log Aktivitas Admin</span>
                    </Link>

                    <Link
                        :href="route('admin.backup-restore')"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        :class="isActive('admin.backup-restore') ? 'bg-slate-100 text-slate-900' : ''"
                    >
                        <i class="fa-solid fa-database w-5 text-center" :class="isActive('admin.backup-restore') ? 'text-[#FFC000]' : 'text-slate-400'"></i>
                        <span>Backup & Restore Data</span>
                    </Link>
                </div>
            </div>
        </aside>
    </template>

<style scoped>
/* Hilangkan scrollbar untuk Chrome, Safari, dan Edge modern */
.no-scrollbar::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
    background: transparent !important;
}

/* Hilangkan scrollbar untuk Firefox & IE lama */
.no-scrollbar {
    -ms-overflow-style: none !important;  /* IE and Edge */
    scrollbar-width: none !important;  /* Firefox */
}
</style>
