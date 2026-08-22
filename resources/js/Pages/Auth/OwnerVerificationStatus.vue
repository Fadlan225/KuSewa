<script setup>
import { History, Check, AlertTriangle, Hourglass, Mail, Home } from 'lucide-vue-next';
import WaitingVerificationIcon from '@/Components/ui/Icons/WaitingVerificationIcon.vue';
import RejectedVerificationIcon from '@/Components/ui/Icons/RejectedVerificationIcon.vue';
import VerifiedVerificationIcon from '@/Components/ui/Icons/VerifiedVerificationIcon.vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';

const props = defineProps({
    status: {
        type: String,
        default: 'pending' // pending, verified, rejected
    },
    createdAt: {
        type: String,
        default: null
    },
    rejectionReason: {
        type: String,
        default: null
    }
});
</script>

<template>
    <Head title="Status Verifikasi Owner - kitasewa.id" />

    <AppLayout hideNavbar hideBottombar>
        <DetailNavbar title="Pendaftaran pemilik Aset" :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" forceBackUrl backUrl="/" />

        <div class="min-h-screen bg-slate-50 font-sans text-slate-800 flex items-start justify-center pt-8 md:pt-16 pb-20 px-4">
            <div class="max-w-md md:max-w-4xl w-full">

                    <template v-if="status === 'pending'">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between md:gap-12">
                            <!-- Left: Illustration -->
                            <div class="w-full max-w-[320px] md:max-w-none md:flex-1 mx-auto mb-8 md:mb-0 animate-fade-in">
                                <div class="w-full aspect-[4/3] flex items-center justify-center">
                                    <WaitingVerificationIcon />
                                </div>
                            </div>

                            <!-- Right: Content -->
                            <div class="md:flex-1 w-full max-w-[320px] md:max-w-md mx-auto text-center md:text-left">
                                <div class="animate-fade-in" style="animation-delay: 50ms;">
                                    <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-[#0A2540] mb-3">Verifikasi sedang diproses.</h2>
                                    <p class="text-sm md:text-base text-slate-500 leading-relaxed mb-8">
                                        Data pendaftaran Anda sudah kami terima dan sedang ditinjau oleh tim KitaSewa.<br class="hidden md:block"><br class="hidden md:block">
                                        <span class="inline-block mt-2 md:mt-0">Proses verifikasi biasanya memerlukan waktu hingga 1x24 jam kerja.</span>
                                    </p>
                                </div>

                                <!-- Status Cards -->
                                <div class="space-y-3 mb-8 animate-fade-in" style="animation-delay: 100ms;">
                                    <!-- Card 1: Menunggu verifikasi -->
                                    <div class="bg-[#FFFDF5] border border-[#FFC000]/20 rounded-xl p-4 flex gap-4 items-start shadow-sm text-left">
                                        <div class="w-10 h-10 rounded-full bg-[#FFC000]/10 flex items-center justify-center shrink-0 border border-[#FFC000]/20">
                                            <Hourglass class="w-5 h-5 text-[#FFC000]" />
                                        </div>
                                        <div class="flex-1 min-w-0 pt-0.5">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <div class="w-2 h-2 rounded-full bg-[#FFC000]"></div>
                                                <h3 class="font-bold text-[#0A2540] text-sm">Menunggu verifikasi</h3>
                                            </div>
                                            <p class="text-xs text-slate-500">Dikirim pada {{ createdAt || 'hari ini' }} • Dalam peninjauan tim</p>
                                        </div>
                                    </div>

                                    <!-- Card 2: Info Email -->
                                    <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-4 flex gap-4 items-center text-left">
                                        <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-sm">
                                            <Mail class="w-5 h-5 text-slate-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 leading-relaxed">
                                                Kami akan mengirimkan notifikasi melalui email setelah proses verifikasi selesai.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <Link href="/" class="w-full h-[48px] rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-[0_2px_10px_rgba(255,192,0,0.2)] flex items-center justify-center gap-2 animate-fade-in" style="animation-delay: 200ms;">
                                    <Home class="w-4 h-4" />
                                    Kembali ke Beranda
                                </Link>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="status === 'verified'">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between md:gap-12">
                            <!-- Left: Illustration -->
                            <div class="w-full max-w-[320px] md:max-w-none md:flex-1 mx-auto mb-8 md:mb-0 animate-fade-in">
                                <div class="w-full aspect-[4/3] flex items-center justify-center">
                                    <VerifiedVerificationIcon />
                                </div>
                            </div>

                            <!-- Right: Content -->
                            <div class="md:flex-1 w-full max-w-[320px] md:max-w-md mx-auto text-center md:text-left">
                                <div class="animate-fade-in" style="animation-delay: 50ms;">
                                    <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-[#0A2540] mb-3">Verifikasi Berhasil</h2>
                                    <p class="text-sm md:text-base text-slate-500 leading-relaxed mb-6">
                                        Verifikasi akun Anda telah disetujui. Sekarang Anda dapat mulai mendaftarkan dan mengelola aset di KitaSewa.
                                    </p>
                                </div>

                                <div class="space-y-3 animate-fade-in" style="animation-delay: 150ms;">
                                    <Link :href="route('owner.dashboard')" class="w-full h-[48px] rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-[0_2px_10px_rgba(255,192,0,0.2)] flex items-center justify-center">
                                        Mulai Daftarkan Aset
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="status === 'rejected'">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between md:gap-12">
                            <!-- Left: Illustration -->
                            <div class="w-full max-w-[320px] md:max-w-none md:flex-1 mx-auto mb-8 md:mb-0 animate-fade-in">
                                <div class="w-full aspect-[4/3] flex items-center justify-center">
                                    <RejectedVerificationIcon />
                                </div>
                            </div>

                            <!-- Right: Content -->
                            <div class="md:flex-1 w-full max-w-[320px] md:max-w-md mx-auto text-center md:text-left">
                                <div class="animate-fade-in" style="animation-delay: 50ms;">
                                    <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-[#0A2540] mb-3">Verifikasi ditolak</h2>
                                    <p class="text-sm md:text-base text-slate-500 leading-relaxed mb-6">
                                        Data pendaftaran Anda tidak memenuhi persyaratan verifikasi KitaSewa. Silakan periksa alasan penolakan dan lakukan perbaikan sebelum mengajukan verifikasi kembali.
                                    </p>
                                </div>

                                <!-- Box Alasan Penolakan -->
                                <div class="mb-8 animate-fade-in" style="animation-delay: 100ms;">
                                    <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-left shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <AlertTriangle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                                            <div>
                                                <h3 class="font-bold text-red-800 text-sm mb-1">Alasan Penolakan:</h3>
                                                <p class="text-sm text-red-700 leading-relaxed">
                                                    {{ rejectionReason || 'Tidak ada keterangan spesifik yang diberikan oleh admin.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3 animate-fade-in" style="animation-delay: 200ms;">
                                    <Link href="/owner/register" class="w-full h-[48px] rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-[0_2px_10px_rgba(255,192,0,0.2)] flex items-center justify-center">
                                        Perbaiki Data
                                    </Link>
                                    <Link href="/" class="w-full h-[48px] rounded-xl border border-slate-200 text-slate-600 font-bold text-[14px] hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center">
                                        Kembali ke Beranda
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </template>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
