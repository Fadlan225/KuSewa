export const getOwnerMenu = (sidebarCounts) => [
    { label: 'Dashboard', route: route('owner.dashboard'), routeName: 'owner.dashboard', icon: 'fa-solid fa-house-chimney' },
    {
        label: 'Aset & Unit',
        route: route('owner.asset.index'),
        routeName: 'owner.asset.*',
        icon: 'fa-solid fa-building',
        badge: sidebarCounts?.pendingPropertyCount,
        badgeClass: 'bg-[#FFC000]/20 text-[#0A2540] text-[10px] font-black px-1.5 py-0.5 rounded'
    },
    {
        label: 'Pemesanan',
        route: route('owner.bookings'),
        routeName: 'owner.bookings*',
        icon: 'fa-solid fa-receipt',
        badge: sidebarCounts?.pendingBookingCount,
        badgeClass: 'bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full'
    },
    { label: 'Biaya Bulanan', route: route('owner.monthly-payment'), routeName: 'owner.monthly-payment', icon: 'fa-solid fa-credit-card' },
    { label: 'Keuangan', route: route('owner.income'), routeName: 'owner.income', icon: 'fa-solid fa-wallet' },
    { divider: true },
];

export const getOwnerBottomMenu = () => [
    { label: 'Pengaturan Akun', route: route('owner.settings'), routeName: 'owner.settings', icon: 'fa-solid fa-gear' },
    { label: 'Bantuan kusewa', route: route('owner.help'), routeName: 'owner.help', icon: 'fa-solid fa-headset' }
];

export const getAdminMenu = (sidebarCounts) => [
    { label: 'Dashboard', route: route('admin.dashboard'), routeName: 'admin.dashboard', icon: 'fa-solid fa-gauge' },
    { label: 'Log Aktivitas', route: route('admin.activity-log'), routeName: 'admin.activity-log', icon: 'fa-solid fa-list-ul' },
    { label: 'Laporan Pengguna', route: route('admin.user-reports'), routeName: 'admin.user-reports', icon: 'fa-solid fa-flag' },
    { divider: true },
    { label: 'Akun Administrator', route: route('admin.account-management'), routeName: 'admin.account-management', icon: 'fa-solid fa-user-shield' },
    { label: 'Manajemen Pengguna', route: route('admin.user-management'), routeName: 'admin.user-management', icon: 'fa-solid fa-users' },
    { label: 'Pengajuan Akun', route: route('admin.pengajuan-akun'), routeName: 'admin.pengajuan-akun', icon: 'fa-solid fa-user-clock' },
    { divider: true },
    { label: 'Aset & Properti', route: route('admin.aset-properti'), routeName: 'admin.aset-properti', icon: 'fa-solid fa-building' },
    { label: 'Validasi Aset', route: route('admin.validasi-aset'), routeName: 'admin.validasi-aset', icon: 'fa-solid fa-check-to-slot' },
    { label: 'Kategori Fasilitas', route: route('admin.kategori-fasilitas'), routeName: 'admin.kategori-fasilitas', icon: 'fa-solid fa-layer-group' },
    { divider: true },
    { label: 'Sistem Pembayaran', route: route('admin.payment-system'), routeName: 'admin.payment-system', icon: 'fa-solid fa-credit-card' },
    { label: 'Biaya & Sanksi', route: route('admin.service-fee'), routeName: 'admin.service-fee', icon: 'fa-solid fa-scale-balanced' },
    { label: 'Promo & Diskon', route: route('admin.promo-diskon'), routeName: 'admin.promo-diskon', icon: 'fa-solid fa-tags' },
    { divider: true },
    { label: 'Notifikasi Sistem', route: route('admin.system-notifications'), routeName: 'admin.system-notifications', icon: 'fa-solid fa-bell' },
    { label: 'CMS Manager', route: route('admin.cms-manager'), routeName: 'admin.cms-manager', icon: 'fa-solid fa-laptop-code' },
    { label: 'Backup & Restore', route: route('admin.backup-restore'), routeName: 'admin.backup-restore', icon: 'fa-solid fa-database' },
];

export const getAdminBottomMenu = () => [];
