<script setup>
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Pages/admin/AdminSidebar.vue';
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-800 antialiased">
        <div class="mx-auto flex min-h-screen max-w-[1600px] gap-6 p-4 lg:p-6">
            <!-- Sidebar Component -->
            <AdminSidebar />

            <!-- Main Content Area -->
            <main class="flex-1 space-y-6 overflow-hidden">
                <!-- Top Navbar / Header -->
                <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <nav class="text-xs text-slate-400">
                            <span>Administrator</span> / <span class="font-medium text-slate-700">Dashboard Statistik</span>
                        </nav>
                        <h1 class="mt-1 text-xl font-bold text-slate-900">Sistem Pengelolaan Aset</h1>
                    </div>

                    <!-- Header Action Controls -->
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input
                                type="text"
                                placeholder="Cari aset, user, transaksi..."
                                class="w-56 rounded-xl border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-700 placeholder-slate-400 transition focus:border-[#FFC000] focus:outline-none focus:ring-1 focus:ring-[#FFC000]"
                            />
                        </div>
                        <button class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 transition hover:bg-slate-100">
                            <i class="fa-regular fa-bell text-sm"></i>
                            <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-[#FFC000]"></span>
                        </button>
                        <div class="flex items-center gap-2 border-l border-slate-200 pl-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-900 font-bold text-xs text-white">
                                AD
                            </div>
                            <span class="text-xs font-semibold text-slate-700">Admin</span>
                        </div>
                    </div>
                </header>

                <!-- Key Stat Cards (Sesuai Spesifikasi Dokumen) -->
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <!-- Total Pendapatan Biaya Layanan -->
                    <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Pendapatan Layanan</p>
                                <h3 class="mt-1 text-xl font-bold text-slate-900">Rp 48.500.000</h3>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-[#FFC000] shadow-sm">
                                <i class="fa-solid fa-wallet text-sm"></i>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-1 border-t border-slate-100 pt-2.5 text-[11px]">
                            <span class="font-bold text-emerald-600">+12%</span>
                            <span class="text-slate-400">dari bulan lalu</span>
                        </div>
                    </div>

                    <!-- Total Aset Tersedia (Properti & Baliho) -->
                    <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Aset Aktif / Tersedia</p>
                                <h3 class="mt-1 text-xl font-bold text-slate-900">1,248 Aset</h3>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                                <i class="fa-solid fa-building text-sm"></i>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-1 border-t border-slate-100 pt-2.5 text-[11px]">
                            <span class="text-slate-500 font-medium">850 Properti</span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-500 font-medium">398 Baliho</span>
                        </div>
                    </div>

                    <!-- Menunggu Validasi -->
                    <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Menunggu Validasi</p>
                                <h3 class="mt-1 text-xl font-bold text-slate-900">18 Pengajuan</h3>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFC000] text-slate-950 shadow-sm">
                                <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-1 border-t border-slate-100 pt-2.5 text-[11px]">
                            <span class="font-bold text-amber-600">Perlu tindakan admin</span>
                        </div>
                    </div>

                    <!-- Total User Terdaftar -->
                    <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Pengguna Terdaftar</p>
                                <h3 class="mt-1 text-xl font-bold text-slate-900">3,420 User</h3>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                                <i class="fa-solid fa-users text-sm"></i>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-1 border-t border-slate-100 pt-2.5 text-[11px]">
                            <span class="font-bold text-emerald-600">2,800 Penyewa</span>
                            <span class="text-slate-300">•</span>
                            <span class="font-bold text-slate-600">620 Pemilik</span>
                        </div>
                    </div>
                </section>

                <!-- Visual Charts Section (Grafik Transaksi & Pengguna) -->
                <section class="grid gap-6 xl:grid-cols-3">
                    <!-- Grafik Transaksi Bulanan -->
                    <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100 xl:col-span-2">
                        <div class="flex items-center justify-between pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">Statistik Transaksi Bulanan</h3>
                                <p class="text-xs text-slate-400">Ringkasan transaksi berhasil, pending, dan ditolak</p>
                            </div>
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">Tahun 2026</span>
                        </div>
                        
                        <!-- Visual representation of line chart -->
                        <div class="mt-2 flex h-48 w-full items-center justify-center rounded-xl bg-slate-50 p-2">
                            <svg viewBox="0 0 500 120" class="h-full w-full text-[#FFC000]" fill="none" stroke="currentColor" stroke-width="3">
                                <path d="M10 100 L70 80 L130 85 L190 40 L250 55 L310 20 L370 45 L430 15 L490 35" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        
                        <div class="mt-4 flex items-center justify-around border-t border-slate-100 pt-3 text-xs text-slate-500">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Transaksi Berhasil (88%)
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#FFC000]"></span> Menunggu Validasi (8%)
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Ditolak (4%)
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Aset Paling Banyak Disewa -->
                    <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800">Aset Populer & Disewa</h3>
                        <p class="text-xs text-slate-400 mb-4">Properti & Baliho dengan booking tertinggi</p>

                        <div class="space-y-3.5 text-xs">
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <div>
                                    <p class="font-bold text-slate-800">Baliho Sudirman Sp. 4</p>
                                    <p class="text-[10px] text-slate-400">Kategori: Baliho Digital</p>
                                </div>
                                <span class="rounded-md bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800">42 Kali Sewa</span>
                            </div>

                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <div>
                                    <p class="font-bold text-slate-800">Ruko Executive Block A</p>
                                    <p class="text-[10px] text-slate-400">Kategori: Properti Usaha</p>
                                </div>
                                <span class="rounded-md bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800">28 Kali Sewa</span>
                            </div>

                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <div>
                                    <p class="font-bold text-slate-800">Gedung Serbaguna Utama</p>
                                    <p class="text-[10px] text-slate-400">Kategori: Properti Acara</p>
                                </div>
                                <span class="rounded-md bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800">19 Kali Sewa</span>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>