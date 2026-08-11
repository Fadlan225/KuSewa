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
    // { label: 'Dashboard Admin', route: route('admin.dashboard'), routeName: 'admin.dashboard', icon: 'fa-solid fa-gauge' }
];

export const getAdminBottomMenu = () => [];
