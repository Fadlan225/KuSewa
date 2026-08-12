<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const pages = ref([
    { id: 1, title: 'Tentang Kami', status: 'Publik', updated: '30 Jul 2026' },
    { id: 2, title: 'Syarat & Ketentuan', status: 'Privat', updated: '28 Jul 2026' },
    { id: 3, title: 'Panduan Pemilik', status: 'Publik', updated: '20 Jul 2026' },
]);

const posts = ref([
    { id: 1, title: 'Panduan Verifikasi Aset', published: '01 Agu 2026', status: 'Draft' },
    { id: 2, title: 'Promo Musim Liburan', published: '15 Jul 2026', status: 'Publik' },
]);
</script>

<template>
    <Head title="Kelola CMS Website - Admin Panel" />

    <div class="h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased overflow-hidden">
        <div class="h-full flex-shrink-0">
            <AdminSidebar />
        </div>

        <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
            <header class="h-16 bg-white border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 text-[#FFC000] flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-globe"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">Kelola CMS Website</h1>
                        <p class="text-xs text-slate-400">Kelola konten statis dan artikel pengumuman platform.</p>
                    </div>
                </div>
                <button class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Buat Halaman Baru</button>
            </header>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Halaman Publik</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ pages.filter(page => page.status === 'Publik').length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Konten website yang sudah dipublikasikan.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Halaman Draft</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ pages.filter(page => page.status !== 'Publik').length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Konten yang belum dipublikasikan.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Artikel</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ posts.length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah posting informasi yang tersedia.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Halaman</h2>
                                <p class="text-[11px] text-slate-400">Mengelola konten statis website.</p>
                            </div>
                            <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Kelola Halaman</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                        <th class="py-4 px-5">Judul</th>
                                        <th class="py-4 px-4">Status</th>
                                        <th class="py-4 px-4">Terakhir diubah</th>
                                        <th class="py-4 px-5">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="page in pages" :key="page.id" class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-4 px-5 font-semibold text-slate-900">{{ page.title }}</td>
                                        <td class="py-4 px-4 text-slate-600">{{ page.status }}</td>
                                        <td class="py-4 px-4 text-slate-500">{{ page.updated }}</td>
                                        <td class="py-4 px-5 text-right whitespace-nowrap">
                                            <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Edit</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Artikel & Pengumuman</h2>
                                <p class="text-[11px] text-slate-400">Kelola posting informasi kepada pengguna.</p>
                            </div>
                            <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Tambah Artikel</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                        <th class="py-4 px-5">Judul</th>
                                        <th class="py-4 px-4">Tanggal</th>
                                        <th class="py-4 px-4">Status</th>
                                        <th class="py-4 px-5">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="post in posts" :key="post.id" class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-4 px-5 font-semibold text-slate-900">{{ post.title }}</td>
                                        <td class="py-4 px-4 text-slate-500">{{ post.published }}</td>
                                        <td class="py-4 px-4 text-slate-600">{{ post.status }}</td>
                                        <td class="py-4 px-5 text-right whitespace-nowrap">
                                            <button class="rounded-full bg-slate-900 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 transition">Edit</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</template>
