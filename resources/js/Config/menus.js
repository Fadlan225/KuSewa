import {
    Home, Building, Receipt, CreditCard, Wallet,
    Gauge, List, Flag, Shield, Users, UserPlus,
    CheckSquare, Layers, Scale, Tags, Bell, Laptop, Database
} from 'lucide-vue-next';

export const getOwnerMenu = (sidebarCounts) => [
    { label: 'Dashboard', route: route('owner.dashboard'), routeName: 'owner.dashboard', icon: Home },
    {
        label: 'Aset Saya',
        route: route('owner.asset.index'),
        routeName: 'owner.asset.*',
        icon: Building,
        badge: sidebarCounts?.pendingPropertyCount,
        badgeClass: 'bg-[#FFC000]/20 text-[#0A2540] text-[10px] font-black px-1.5 py-0.5 rounded'
    },
    {
        label: 'Pemesanan',
        route: route('owner.bookings'),
        routeName: 'owner.bookings*',
        icon: Receipt,
        badge: sidebarCounts?.pendingBookingCount,
        badgeClass: 'bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full'
    },
    { label: 'Biaya Bulanan', route: route('owner.monthly-payment'), routeName: 'owner.monthly-payment', icon: CreditCard },
    { label: 'Keuangan', route: route('owner.income'), routeName: 'owner.income', icon: Wallet },
    { divider: true },
];

export const getAdminMenu = (sidebarCounts) => [
    { label: 'Dashboard', route: route('admin.dashboard'), routeName: 'admin.dashboard', icon: Gauge },
    { label: 'Log Aktivitas', route: route('admin.activity-log'), routeName: 'admin.activity-log', icon: List },
    { label: 'Laporan Pengguna', route: route('admin.user-reports'), routeName: 'admin.user-reports', icon: Flag },
    { divider: true },
    { label: 'Akun Administrator', route: route('admin.account-management'), routeName: 'admin.account-management', icon: Shield },
    { label: 'Manajemen Pengguna', route: route('admin.user-management'), routeName: 'admin.user-management', icon: Users },
    { label: 'Pengajuan Akun', route: route('admin.pengajuan-akun'), routeName: 'admin.pengajuan-akun', icon: UserPlus },
    { divider: true },
    { label: 'Aset & Properti', route: route('admin.aset-properti'), routeName: 'admin.aset-properti', icon: Building },
    { label: 'Validasi Aset', route: route('admin.validasi-aset'), routeName: 'admin.validasi-aset', icon: CheckSquare },
    { label: 'Kategori Fasilitas', route: route('admin.kategori-fasilitas'), routeName: 'admin.kategori-fasilitas', icon: Layers },
    { divider: true },
    { label: 'Sistem Pembayaran', route: route('admin.payment-system'), routeName: 'admin.payment-system', icon: CreditCard },
    { label: 'Biaya & Sanksi', route: route('admin.service-fee'), routeName: 'admin.service-fee', icon: Scale },
    { label: 'Promo & Diskon', route: route('admin.promo-diskon'), routeName: 'admin.promo-diskon', icon: Tags },
    { divider: true },
    { label: 'Notifikasi Sistem', route: route('admin.system-notifications'), routeName: 'admin.system-notifications', icon: Bell },
    { label: 'CMS Manager', route: route('admin.cms-manager'), routeName: 'admin.cms-manager', icon: Laptop },
    { label: 'Backup & Restore', route: route('admin.backup-restore'), routeName: 'admin.backup-restore', icon: Database },
];

export const getAdminBottomMenu = () => [];
