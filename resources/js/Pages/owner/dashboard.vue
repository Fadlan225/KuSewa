    <script setup>
    import { ref, computed } from 'vue';
    import { Head, Link, usePage } from '@inertiajs/vue3';
    // 1. Import komponen Sidebar reusable yang baru dibuat
    import Sidebar from '@/Components/sidebar.vue';

    const page = usePage();

    const props = defineProps({
        owner: {
            type: Object,
            default: () => ({
                nik: '6471000000001001',
                ttl: 'Samarinda, 22 Maret 1995',
                alamat: 'Jl. Pemilik Aset No. 3, Samarinda',
                status: 'Terverifikasi'
            })
        },
        stats: {
            type: Object,
            default: () => ({
                totalProperti: 8,
                pesananAktif: 14,
                konfirmasiPending: 3,
                totalPendapatan: 'Rp 18.500.000'
            })
        }
    });

    // Tab Aktif Kategori Aset Kusewa
    const activeTab = ref('Kos & Rumah');
    const tabs = ['Kos & Rumah', 'Apartemen', 'Kendaraan'];

    const user = computed(() => page.props.auth?.user || {
        name: 'Budi Santoso',
        email: 'owner@kusewa.id'
    });

    // Mock Data Penyewa Aktif di kusewa.id
    const activeTenants = ref([
        { name: 'Ahmad Rizky', property: 'Kos Samarinda #03', rentEnd: '12 Aug 2026', status: 'Lunas', avatar: 'https://i.pravatar.cc/100?img=11' },
        { name: 'Siti Rahma', property: 'Apt Orchard B12', rentEnd: '01 Sep 2026', status: 'Pending', avatar: 'https://i.pravatar.cc/100?img=5' },
        { name: 'Budi Kurniawan', property: 'Innova Reborn (2 Hri)', rentEnd: '25 Jul 2026', status: 'Lunas', avatar: 'https://i.pravatar.cc/100?img=9' },
        { name: 'Wilona Hamda', property: 'Kos Melati #01', rentEnd: '10 Aug 2026', status: 'Lunas', avatar: 'https://i.pravatar.cc/100?img=20' },
        { name: 'Rava Nanda', property: 'Rumah Kontrakan A2', rentEnd: '15 Dec 2026', status: 'Lunas', avatar: 'https://i.pravatar.cc/100?img=13' },
    ]);
    </script>

    <template>
        <Head title="Owner Dashboard - kusewa.id" />

        <div class="min-h-screen bg-[#F3F5F8] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30">

            <!-- ==================== SIDEBAR COMPONENT ==================== -->
            <!-- Menggantikan tag <aside> panjang yang lama -->
            <Sidebar />

            <!-- ==================== MAIN CONTENT ==================== -->
            <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

                <!-- TOPBAR HEADER -->
                <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                    <div class="flex items-center gap-3 w-1/3">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                        <input type="text" placeholder="Cari penyewa, ID pesanan, atau unit..." class="w-full text-xs bg-transparent focus:outline-none placeholder-slate-400" />
                    </div>

                    <div class="flex items-center gap-4">
                        <button class="relative p-2 text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-bell text-sm"></i>
                            <span class="w-1.5 h-1.5 bg-rose-500 rounded-full absolute top-2 right-2"></span>
                        </button>

                        <div class="h-6 w-[1px] bg-slate-200"></div>

                        <!-- Owner Status Indicator Badge -->
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-[#FFC000] text-[#0A2540] flex items-center justify-center font-black text-xs">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div class="text-left leading-tight hidden sm:block">
                                <p class="text-xs font-bold text-slate-800">Pemilik Terverifikasi</p>
                                <p class="text-[10px] text-emerald-600 font-semibold">● NIK Cocok</p>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- BODY CONTENT AREA -->
                <div class="p-6 space-y-5 max-w-[1400px] w-full mx-auto">

                    <!-- TITLE ROW & QUICK ACTION -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-[#0A2540] text-[#FFC000] flex items-center justify-center font-black text-base shadow-sm">
                                <i class="fa-solid fa-house-user"></i>
                            </div>
                            <div>
                                <h1 class="text-base font-bold text-slate-900 tracking-tight">Ringkasan Aset & Sewa</h1>
                                <p class="text-[11px] text-slate-400">Kelola operasional penyewaan properti kamu di kusewa.id</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-xs">
                            <Link href="/owner/property/create" class="bg-[#FFC000] hover:bg-[#e6ad00] active:scale-95 text-[#0A2540] font-black px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                                <i class="fa-solid fa-plus text-xs"></i>
                                <span>Tambah Properti Baru</span>
                            </Link>
                        </div>
                    </div>

                    <!-- TABS CATEGORY (Kos, Apartemen, Kendaraan) -->
                    <div class="flex items-center gap-6 border-b border-slate-200/80 pb-1 text-xs font-medium">
                        <button 
                            v-for="tab in tabs" 
                            :key="tab"
                            @click="activeTab = tab"
                            :class="[
                                'pb-2 transition relative',
                                activeTab === tab ? 'text-[#0A2540] font-bold border-b-2 border-[#0A2540]' : 'text-slate-400 hover:text-slate-600'
                            ]"
                        >
                            {{ tab }}
                        </button>
                    </div>

                    <!-- TOP ANALYTICS SECTION: 4 MINI METRICS + MAP LOKASI ASET -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                        
                        <!-- 4 Metric Cards Grid (5 Cols) -->
                        <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                            
                            <!-- Card 1 -->
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                                <div class="w-8 h-8 rounded-full bg-amber-50 text-[#0A2540] flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-building text-[#FFC000]"></i>
                                </div>
                                <div class="mt-4">
                                    <span class="text-[11px] font-medium text-slate-400 block">Total Unit Aset</span>
                                    <span class="text-xl font-extrabold text-slate-900">{{ stats.totalProperti }} Unit</span>
                                </div>
                            </div>

                            <!-- Card 2 -->
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-key"></i>
                                </div>
                                <div class="mt-4">
                                    <span class="text-[11px] font-medium text-slate-400 block">Sedang Tersewa</span>
                                    <span class="text-xl font-extrabold text-slate-900">{{ stats.pesananAktif }} Penyewa</span>
                                </div>
                            </div>

                            <!-- Card 3 -->
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                                <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div class="mt-4">
                                    <span class="text-[11px] font-medium text-slate-400 block">Perlu Konfirmasi</span>
                                    <span class="text-xl font-extrabold text-slate-900">{{ stats.konfirmasiPending }} Pengajuan</span>
                                </div>
                            </div>

                            <!-- Card 4 -->
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <div class="mt-4">
                                    <span class="text-[11px] font-medium text-slate-400 block">Pendapatan Bulan Ini</span>
                                    <span class="text-lg font-extrabold text-slate-900">{{ stats.totalPendapatan }}</span>
                                </div>
                            </div>

                        </div>

                        <!-- Map Sebaran Properti kusewa.id (7 Cols) -->
                        <div class="lg:col-span-7 bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm flex flex-col justify-between relative overflow-hidden min-h-[220px]">
                            <div class="grid grid-cols-3 gap-4 z-10">
                                <div>
                                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider block">Cakupan Wilayah</span>
                                    <span class="text-sm font-extrabold text-slate-800">2 Kota (Kaltim)</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider block">Tingkat Okupansi</span>
                                    <span class="text-sm font-extrabold text-emerald-600">87.5% Terisi</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider block">Rata-rata Sewa</span>
                                    <span class="text-sm font-extrabold text-slate-800">6 Bulan</span>
                                </div>
                            </div>

                            <!-- Map Pin Area kusewa.id (Samarinda/Balikpapan Visual) -->
                            <div class="my-4 relative h-32 bg-slate-50/50 rounded-xl border border-dashed border-slate-200 flex items-center justify-center overflow-hidden">
                                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#0A2540_1px,transparent_1px)] [background-size:12px_12px]"></div>

                                <!-- Pins Lokasi Aset -->
                                <div class="absolute top-4 left-1/4 bg-[#0A2540] text-white text-[9px] px-2.5 py-1 rounded-md shadow-md flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-[#FFC000]"></i> Samarinda Kota <strong class="text-[#FFC000]">5 Unit</strong>
                                </div>
                                <div class="absolute bottom-5 right-1/4 bg-[#0A2540] text-white text-[9px] px-2.5 py-1 rounded-md shadow-md flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-[#FFC000]"></i> Balikpapan <strong class="text-[#FFC000]">3 Unit</strong>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-100 z-10">
                                <span>Diperbarui otomatis dari sistem kusewa.id</span>
                                <button class="text-[#0A2540] font-bold flex items-center gap-1 hover:underline">
                                    <i class="fa-solid fa-rotate text-[10px]"></i>
                                    <span>Refresh Data</span>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- BOTTOM 3-COLUMN DATA GRID -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                        
                        <!-- Col 1: List Penyewa Aktif (4 Cols) -->
                        <div class="lg:col-span-4 bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-xs font-bold text-slate-800">Penyewa Aktif Saat Ini</h3>
                                    <button class="text-[11px] font-bold text-[#0A2540] hover:underline">Lihat Semua</button>
                                </div>

                                <div class="divide-y divide-slate-100">
                                    <div v-for="(tenant, index) in activeTenants" :key="index" class="py-2.5 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <img :src="tenant.avatar" class="w-7 h-7 rounded-full object-cover" />
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-800 truncate">{{ tenant.name }}</p>
                                                <p class="text-[10px] text-slate-400 truncate">{{ tenant.property }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 border border-emerald-200">
                                                {{ tenant.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Col 2: Demografi Penyewa (4 Cols) -->
                        <div class="lg:col-span-4 bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm">
                            <h3 class="text-xs font-bold text-slate-800 mb-1">Kategori Usia Penyewa</h3>
                            <p class="text-[10px] text-slate-400 mb-3">Profil demografi penghuni aset Anda</p>
                            
                            <div class="flex items-center justify-center gap-6 text-[11px] font-semibold mb-4">
                                <span class="text-blue-600 flex items-center gap-1"><i class="fa-solid fa-mars"></i> Pria (60%)</span>
                                <span class="text-emerald-500 flex items-center gap-1"><i class="fa-solid fa-venus"></i> Wanita (40%)</span>
                            </div>

                            <!-- Demografi Bar Visual -->
                            <div class="space-y-2 text-[10px] text-slate-400">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="w-1/2 flex justify-end"><div class="h-3 bg-blue-600 rounded-l w-[30%]"></div></div>
                                    <span class="w-12 text-center font-bold text-slate-600">Mahasiswa</span>
                                    <div class="w-1/2"><div class="h-3 bg-emerald-400 rounded-r w-[50%]"></div></div>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <div class="w-1/2 flex justify-end"><div class="h-3 bg-blue-600 rounded-l w-[85%]"></div></div>
                                    <span class="w-12 text-center font-bold text-slate-600">Karyawan</span>
                                    <div class="w-1/2"><div class="h-3 bg-emerald-400 rounded-r w-[40%]"></div></div>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <div class="w-1/2 flex justify-end"><div class="h-3 bg-blue-600 rounded-l w-[20%]"></div></div>
                                    <span class="w-12 text-center font-bold text-slate-600">Keluarga</span>
                                    <div class="w-1/2"><div class="h-3 bg-emerald-400 rounded-r w-[15%]"></div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Col 3: Status Berkas Identitas Owner (4 Cols) -->
                        <div class="lg:col-span-4 bg-white rounded-2xl p-4 border border-slate-200/70 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Berkas Identitas Owner</h3>
                                    <span class="text-[9px] font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        VERIFIED
                                    </span>
                                </div>

                                <div class="space-y-2.5 text-xs">
                                    <div>
                                        <span class="text-slate-400 text-[10px] font-medium block">NIK Terdaftar</span>
                                        <span class="font-bold text-slate-800 mt-0.5 block tracking-wider">{{ owner.nik }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[10px] font-medium block">Tempat, Tanggal Lahir</span>
                                        <span class="font-semibold text-slate-700 mt-0.5 block">{{ owner.ttl }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[10px] font-medium block">Alamat Domisili</span>
                                        <span class="font-semibold text-slate-700 mt-0.5 block leading-tight">{{ owner.alamat }}</span>
                                    </div>
                                </div>
                            </div>

                            <button class="w-full mt-4 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold py-2 rounded-xl transition">
                                Perbarui Data Identitas
                            </button>
                        </div>

                    </div>

                </div>
            </main>

        </div>
    </template>