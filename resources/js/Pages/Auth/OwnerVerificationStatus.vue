<script setup>
import { History, Check, AlertTriangle } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';

const props = defineProps({
    status: {
        type: String,
        default: 'pending' // pending, verified, rejected
    }
});
</script>

<template>
    <Head title="Status Verifikasi Owner - kitasewa.id" />

    <AppLayout hideNavbar hideBottombar>
        <DetailNavbar :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" forceBackUrl backUrl="/" />

        <div class="min-h-screen bg-background font-sans text-text flex items-center justify-center -mt-16">
            <div class="max-w-md w-full px-6 py-12">

                <div class="bg-white rounded-3xl shadow-[0_4px_24px_rgba(0,0,0,0.04)] border border-muted/10 p-8 text-center">

                    <template v-if="status === 'pending'">
                        <div class="w-20 h-20 mx-auto bg-amber-50 rounded-full flex items-center justify-center mb-6">
                            <History class="text-3xl text-amber-500" />
                        </div>
                        <h2 class="text-2xl font-bold text-secondary mb-3">Verifikasi Diproses</h2>
                        <p class="text-[15px] text-muted leading-relaxed mb-8">
                            Data pendaftaran Anda sedang ditinjau oleh tim kami. Proses ini biasanya memakan waktu 1x24 jam kerja. Kami akan mengirimkan pemberitahuan setelah verifikasi selesai.
                        </p>
                        <Link href="/" class="block w-full h-[48px] rounded-[12px] bg-primary text-secondary font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center justify-center">
                            Kembali ke Beranda
                        </Link>
                    </template>

                    <template v-else-if="status === 'verified'">
                        <div class="w-20 h-20 mx-auto bg-emerald-50 rounded-full flex items-center justify-center mb-6">
                            <Check class="text-3xl text-emerald-500" />
                        </div>
                        <h2 class="text-2xl font-bold text-secondary mb-3">Verifikasi Berhasil!</h2>
                        <p class="text-[15px] text-muted leading-relaxed mb-8">
                            Selamat! Akun Anda telah terverifikasi sebagai Owner. Sekarang Anda sudah bisa mulai menyewakan aset Anda.
                        </p>
                        <Link href="/" class="block w-full h-[48px] rounded-[12px] bg-primary text-secondary font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center justify-center">
                            Mulai Menyewakan
                        </Link>
                    </template>

                    <template v-else-if="status === 'rejected'">
                        <div class="w-20 h-20 mx-auto bg-red-50 rounded-full flex items-center justify-center mb-6">
                            <AlertTriangle class="text-3xl text-red-500" />
                        </div>
                        <h2 class="text-2xl font-bold text-secondary mb-3">Verifikasi Ditolak</h2>
                        <p class="text-[15px] text-muted leading-relaxed mb-8">
                            Maaf, dokumen atau data yang Anda berikan tidak memenuhi syarat verifikasi kami. Silakan cek kembali dan ajukan ulang pendaftaran Anda.
                        </p>
                        <Link href="/owner/register" class="block w-full h-[48px] rounded-[12px] bg-primary text-secondary font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center justify-center mb-3">
                            Ajukan Ulang
                        </Link>
                        <Link href="/" class="block w-full h-[48px] rounded-[12px] border border-muted/20 text-secondary font-bold text-[14px] hover:bg-background transition-all shadow-sm flex items-center justify-center">
                            Kembali ke Beranda
                        </Link>
                    </template>

                </div>

            </div>
        </div>
    </AppLayout>
</template>
