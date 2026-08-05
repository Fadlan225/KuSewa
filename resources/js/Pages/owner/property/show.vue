<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue';

const props = defineProps({
    property: {
        type: Object,
        required: true,
    },
});

const verificationLabel = (status) => ({
    pending: 'Menunggu Verifikasi',
    approved: 'Terverifikasi',
    rejected: 'Ditolak',
}[status] || 'Menunggu Verifikasi');

const verificationClass = (status) => ({
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected: 'bg-rose-50 text-rose-700 border-rose-200',
}[status] || 'bg-amber-50 text-amber-700 border-amber-200');

const verificationIcon = (status) => ({
    pending: 'fa-clock',
    approved: 'fa-circle-check',
    rejected: 'fa-circle-xmark',
}[status] || 'fa-clock');

const statusClass = (status) => status === 'Tersewa'
    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
    : 'bg-blue-50 text-blue-700 border-blue-200';

const placeholderImage = 'https://placehold.co/800x500?text=Belum+Ada+Foto';

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="`${property.title} - kusewa.id`" />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans flex antialiased selection:bg-[#FFC000]/30">

        <Sidebar />

        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- TOPBAR HEADER -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                    <Link href="/owner/dashboard" class="hover:text-[#0A2540] transition">Dashboard</Link>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                    <Link href="/owner/property" class="hover:text-[#0A2540] transition">Daftar Properti & Aset</Link>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                    <span class="text-slate-800 font-bold line-clamp-1 max-w-[220px]">{{ property.title }}</span>
                </div>

                <Link
                    :href="`/owner/property/${property.id}/edit`"
                    class="bg-[#0A2540] hover:bg-[#14385f] active:scale-95 text-white font-bold px-4 py-2 rounded-xl shadow-md transition flex items-center gap-2 text-xs"
                >
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                    <span>Edit Unit</span>
                </Link>
            </header>

            <!-- PAGE CONTENT -->
            <div class="p-6 md:p-8 space-y-6 max-w-[1100px] w-full mx-auto">

                <Link href="/owner/property" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-[#0A2540] transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Kembali ke Daftar Properti
                </Link>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                    <!-- LEFT: Image & Description -->
                    <div class="lg:col-span-3 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <div class="relative h-72 bg-slate-100">
                                <img :src="property.image || placeholderImage" :alt="property.title" class="w-full h-full object-cover" />
                                <div class="absolute top-3 left-3">
                                    <span class="bg-[#0A2540]/90 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                                        {{ property.category }}
                                    </span>
                                </div>
                                <div class="absolute top-3 right-3">
                                    <span :class="['text-[10px] font-black px-2.5 py-1 rounded-lg shadow-xs border backdrop-blur-md', verificationClass(property.verification_status)]">
                                        <i :class="['fa-solid mr-1', verificationIcon(property.verification_status)]"></i>
                                        {{ verificationLabel(property.verification_status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h1 class="text-lg font-black text-slate-900 leading-snug">{{ property.title }}</h1>
                                    <span :class="['text-[10px] font-bold px-2.5 py-1 rounded-full border shrink-0', statusClass(property.status)]">
                                        {{ property.status }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-[#FFC000]"></i>
                                    {{ property.address }}, {{ property.city }}
                                </p>

                                <p v-if="property.verification_status === 'rejected' && property.verification_note"
                                   class="text-xs text-rose-600 bg-rose-50 rounded-lg p-3">
                                    <span class="font-bold block mb-1">Catatan admin:</span>
                                    {{ property.verification_note }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Info Cards -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-1">
                            <span class="text-[11px] font-medium text-slate-400 block">Harga Sewa</span>
                            <span class="text-2xl font-black text-[#0A2540]">
                                Rp {{ Number(property.price).toLocaleString('id-ID') }}
                                <span class="text-xs font-normal text-slate-400">/{{ property.rent_period }}</span>
                            </span>
                        </div>

                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Detail Unit</h3>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Tipe</span>
                                <span class="font-bold text-slate-700">{{ property.type }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Keterisian</span>
                                <span class="font-bold text-slate-700">{{ property.occupancy || '-' }}</span>
                            </div>
                            <div v-if="property.tenant" class="flex items-center justify-between text-xs pt-3 border-t border-slate-100">
                                <span class="text-slate-400">Penyewa</span>
                                <span class="font-bold text-slate-700">{{ property.tenant }}</span>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Status Verifikasi</h3>
                            <span :class="['inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border', verificationClass(property.verification_status)]">
                                <i :class="['fa-solid mr-1.5', verificationIcon(property.verification_status)]"></i>
                                {{ verificationLabel(property.verification_status) }}
                            </span>
                            <div v-if="property.verified_by" class="text-xs text-slate-400 pt-2 border-t border-slate-100">
                                Diverifikasi oleh <span class="font-semibold text-slate-600">{{ property.verified_by }}</span>
                                pada {{ formatDate(property.verified_at) }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                :href="`/owner/property/${property.id}/edit`"
                                class="flex-1 bg-[#0A2540] hover:bg-[#14385f] text-white font-bold px-4 py-2.5 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-xs"
                            >
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                Edit Unit
                            </Link>
                            <button class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 rounded-xl transition" title="Hapus Unit">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>