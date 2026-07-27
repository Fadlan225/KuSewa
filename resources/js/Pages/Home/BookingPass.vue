<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import Toast from '@/Components/UI/Toast.vue';
import JsBarcode from 'jsbarcode';
import { toPng } from 'html-to-image';
import { jsPDF } from 'jspdf';

const props = defineProps({
    booking: {
        type: Object,
        required: true
    }
});

const barcodeRef = ref(null);
const ticketRef = ref(null);
const isDownloading = ref(false);

const isConfirmedAndPaid = computed(() => {
    // TIKET BOOKING ditunjukkan jika status booking dikonfirmasi/selesai DAN pembayaran sudah lunas
    // (Atau jika booking status completed)
    return (props.booking.booking_status === 'confirmed' || props.booking.booking_status === 'completed') &&
           (props.booking.payment?.payment_status === 'paid' || props.booking.payment?.payment_status === 'success');
});

const assetImage = computed(() => {
    const firstImgObj = props.booking.asset?.first_image || props.booking.asset?.firstImage;
    const img = firstImgObj?.image_url || firstImgObj?.image;
    if (img) {
        return img.startsWith('http') ? img : '/storage/' + img;
    }
    return '/no-image.svg'; // Local fallback
});

const isCancelled = computed(() => {
    return props.booking.booking_status === 'cancelled' || props.booking.booking_status === 'rejected';
});

const generateBarcode = () => {
    if (barcodeRef.value && props.booking.booking_code) {
        JsBarcode(barcodeRef.value, props.booking.booking_code, {
            format: "CODE128",
            lineColor: "#000",
            width: 2,
            height: 60,
            displayValue: false,
            margin: 0
        });
    }
};

onMounted(() => {
    if (isConfirmedAndPaid.value) {
        nextTick(() => {
            generateBarcode();
        });
    }
});

const goBack = () => {
    router.visit(route('aktivitas.index'));
};

const formatPrice = (price) => {
    if (!price) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    }).format(d);
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const dateRange = computed(() => {
    return formatDate(props.booking.start_date);
});

const timeRange = computed(() => {
    const start = formatTime(props.booking.start_date);
    const end = formatTime(props.booking.end_date);
    // Simple duration calc (in hours for simplicity, or just show start-end)
    const startDate = new Date(props.booking.start_date);
    const endDate = new Date(props.booking.end_date);
    let diff = Math.abs(endDate - startDate) / 36e5;
    diff = Math.round(diff * 10) / 10;

    // Check rental unit
    const unit = props.booking.asset?.type?.rental_unit;
    if (unit === 'day') return `${start} - ${end} (${diff/24} Hari)`;
    if (unit === 'month') return `${start} - ${end} (${Math.round(diff/24/30)} Bulan)`;
    return `${start} - ${end} (${diff} Jam)`;
});

const locationString = computed(() => {
    const city = props.booking.asset?.city || '';
    const address = props.booking.asset?.address || '';
    return [city, address].filter(Boolean).join(', ');
});

const showCopiedToast = ref(false);
const toastMessage = ref('Kode berhasil disalin!');
const toastType = ref('success');
let toastTimer = null;

const showToast = (msg = 'Kode berhasil disalin!', type = 'success') => {
    toastMessage.value = msg;
    toastType.value = type;
    if (toastTimer) clearTimeout(toastTimer);
    showCopiedToast.value = true;
    toastTimer = setTimeout(() => {
        showCopiedToast.value = false;
    }, 2500);
};

const downloadTicketPDF = async () => {
    if (!ticketRef.value) return;
    isDownloading.value = true;

    try {
        const imgData = await toPng(ticketRef.value, {
            pixelRatio: 2,
            backgroundColor: '#ffffff'
        });

        const width = ticketRef.value.offsetWidth;
        const height = ticketRef.value.offsetHeight;

        const pdf = new jsPDF({
            orientation: width > height ? 'l' : 'p',
            unit: 'px',
            format: [width, height]
        });
        
        pdf.addImage(imgData, 'PNG', 0, 0, width, height);
        pdf.save(`Tiket-Booking-${props.booking.booking_code}.pdf`);
        showToast('Tiket berhasil diunduh!', 'success');
    } catch (error) {
        console.error('Error generating PDF:', error);
        showToast('Gagal: ' + (error.message || String(error)), 'error');
    } finally {
        isDownloading.value = false;
    }
};

const copyCode = async () => {
    const code = props.booking.booking_code;
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(code);
            showToast();
        } else {
            throw new Error('No clipboard API');
        }
    } catch (e) {
        const textarea = document.createElement('textarea');
        textarea.value = code;
        textarea.style.position = 'fixed';
        textarea.style.top = '-9999px';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
            showToast();
        } catch (err) {
            console.error('Fallback clipboard gagal:', err);
            alert('Gagal menyalin kode. Silakan salin manual.');
        }
        document.body.removeChild(textarea);
    }
};
</script>

<template>
    <Head :title="isConfirmedAndPaid ? 'Tiket Booking' : 'Status Booking'" />

    <AppLayout :hideNavbar="true" hideBottombar>
        <div class="min-h-screen bg-[#F8F9FA] pb-24 md:pb-8 text-[#1D1D1F] font-sans relative flex flex-col">
            <!-- NAVBAR -->
            <DetailNavbar backUrl="/aktivitas" :showSections="false" :showShare="false" :showFavorite="false" />

        <!-- CONTAINER (Responsive Width Constraint) -->
        <div class="w-full max-w-3xl mx-auto p-4 flex-1">

            <!-- ============================================== -->
            <!-- TIKET BOOKING CARD -->
            <!-- ============================================== -->
            <template v-if="isConfirmedAndPaid">
                <!-- Ticket Wrapper for Drop Shadow -->
                <div class="px-4 py-2 drop-shadow-xl">
                    <!-- Ticket Container with CSS Mask -->
                    <div ref="ticketRef" class="w-full max-w-sm mx-auto bg-white ticket-shape flex flex-col relative">
                        
                        <!-- Image Header (h-56 = 224px) -->
                        <div class="relative h-56 w-full bg-gray-200 shrink-0">
                            <img :src="assetImage" 
                                 :class="assetImage === '/no-image.svg' ? 'w-full h-full object-contain p-8 opacity-60' : 'w-full h-full object-cover'" 
                                 alt="Asset Image" crossorigin="anonymous" 
                                 onerror="this.src='/no-image.svg'; this.className='w-full h-full object-contain p-8 opacity-60'" />
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0A2540]/90 via-[#0A2540]/30 to-transparent"></div>
    
                            <div class="absolute bottom-6 left-6 right-6 flex justify-between items-end">
                                <div>
                                    <p class="text-white/80 text-[10px] uppercase font-bold tracking-[0.2em] mb-1">KODE BOOKING</p>
                                    <p class="text-white text-3xl font-black tracking-widest drop-shadow-sm">{{ booking.booking_code }}</p>
                                </div>
                            </div>
    
                            <!-- Logo Badge -->
                            <div class="absolute top-5 right-5 flex items-center gap-1.5 bg-black/20 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/20 shadow-sm">
                                <span class="font-extrabold text-white text-xs tracking-tighter">kusewa</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor" class="w-3.5 h-3.5 text-[#FFC000]"><path d="M0 144C0 117.5 21.5 96 48 96h416c26.5 0 48 21.5 48 48v64c0 35.3-28.7 64-64 64s-64 28.7-64 64 28.7 64 64 64 64 28.7 64 64v64c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48v-64c0-35.3 28.7-64 64-64s64-28.7 64-64-28.7-64-64-64-64-28.7-64-64v-64z"/></svg>
                            </div>
                        </div>
    
                        <!-- Dashed Separator placed exactly at 224px (cutouts) -->
                        <div class="w-full relative z-10 flex justify-center -my-[1px]">
                            <div class="w-full mx-6 border-t-[2.5px] border-dashed border-gray-300"></div>
                        </div>
    
                        <!-- Details Body -->
                        <div class="p-6 pt-8 pb-8 bg-white relative">
                        <div class="space-y-5">
                            <div class="grid grid-cols-2 gap-y-5 gap-x-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Asset / Unit</p>
                                    <p class="font-bold text-[13px] text-[#0A2540] truncate pr-2">{{ booking.asset?.title }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Kapasitas</p>
                                    <p class="font-bold text-[13px] text-[#0A2540]">{{ booking.asset?.detail?.capacity ? booking.asset.detail.capacity + ' Orang' : 'Sesuai Ketentuan' }}</p>
                                </div>

                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Tanggal</p>
                                    <p class="font-bold text-[13px] text-[#0A2540] pr-2">{{ dateRange }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Waktu</p>
                                    <p class="font-bold text-[13px] text-[#0A2540]">{{ timeRange }}</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-5">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Lokasi</p>
                                <p class="font-bold text-[13px] text-[#0A2540] leading-relaxed">{{ locationString || 'Lokasi tidak diketahui' }}</p>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Penyewa</p>
                                    <p class="font-bold text-[13px] text-[#0A2540]">{{ booking.user?.name || 'User' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Status</p>
                                    <span class="px-2.5 py-1 bg-green-100 text-green-700 text-[10px] font-black tracking-wide rounded-md uppercase">Terverifikasi</span>
                                </div>
                            </div>
                        </div>

                        <!-- Barcode Area -->
                        <div class="mt-8 flex flex-col items-center justify-center p-5 bg-gray-50 rounded-2xl border border-gray-100/80">
                            <svg ref="barcodeRef" class="w-full max-w-[240px] h-auto"></svg>
                            <p class="text-[10px] text-gray-400 mt-2 font-medium">Tunjukkan tiket ini kepada pihak penyedia</p>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Buttons -->
                <div class="mt-5 space-y-3">
                    <button @click="downloadTicketPDF" :disabled="isDownloading" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-[0.98] transition-all text-[#0A2540] font-bold py-3.5 rounded-xl shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i v-if="isDownloading" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-download"></i>
                        {{ isDownloading ? 'Mengunduh...' : 'Download Tiket' }}
                    </button>
                </div>
            </template>


            <!-- ============================================== -->
            <!-- STATE 2: STATUS BOOKING (MENUNGGU / DIBATALKAN)  -->
            <!-- ============================================== -->
            <template v-else>
                <div class="flex flex-col items-center mt-6 text-center px-2">
                    <!-- Illustration -->
                    <div class="relative w-32 h-32 mb-6">
                        <div class="absolute inset-0 rounded-full animate-pulse" :class="isCancelled ? 'bg-red-500/20' : 'bg-[#FFC000]/20'"></div>
                        <div class="absolute inset-4 bg-white rounded-full shadow-sm flex items-center justify-center border-4" :class="isCancelled ? 'border-red-500' : 'border-[#FFC000]'">
                            <i v-if="isCancelled" class="fa-solid fa-xmark text-5xl text-red-500"></i>
                            <i v-else class="fa-regular fa-clock text-4xl text-[#0A2540]"></i>
                        </div>
                        <div v-if="!isCancelled" class="absolute -bottom-2 -right-2 bg-white rounded-lg shadow-md p-2 border border-gray-100">
                            <i class="fa-regular fa-calendar-check text-[#FFC000] text-xl"></i>
                        </div>
                    </div>

                    <h2 class="text-xl font-black text-[#0A2540] mb-3">
                        {{ isCancelled ? 'Booking Dibatalkan' : 'Menunggu Verifikasi Pembayaran' }}
                    </h2>
                    <p class="text-sm text-gray-500 mb-8 max-w-xs">
                        <template v-if="isCancelled">
                            Mohon maaf, booking Anda telah dibatalkan atau ditolak.
                        </template>
                        <template v-else>
                            Terima kasih! Booking Anda berhasil dibuat.<br/>
                            {{ booking.payment?.payment_status === 'pending' ? 'Silakan selesaikan pembayaran Anda.' : 'Kami sedang memverifikasi pembayaran Anda.' }}
                        </template>
                    </p>

                    <!-- Details Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 w-full text-left mb-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide mb-1">Kode Booking</p>
                                <h3 class="text-xl font-black text-[#0A2540]">{{ booking.booking_code }}</h3>
                            </div>
                            <button @click="copyCode" class="bg-amber-50 text-[#0A2540] hover:bg-amber-100 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 border border-amber-200">
                                <i class="fa-regular fa-copy text-amber-600"></i> Salin
                            </button>
                        </div>

                        <div class="mb-4">
                            <p class="font-bold text-sm text-[#0A2540]">{{ booking.asset?.title }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5 truncate">{{ locationString || 'Lokasi tidak diketahui' }}</p>
                            <p class="text-[11px] text-gray-500 mt-1"><i class="fa-regular fa-calendar mr-1"></i> {{ dateRange }} &bull; {{ timeRange }}</p>
                        </div>

                        <div class="border-t border-dashed border-gray-200 my-4"></div>

                        <div class="flex justify-between items-center mb-4">
                            <p class="text-xs text-gray-500">Total Pembayaran</p>
                            <p class="text-lg font-black text-red-500">{{ formatPrice(booking.total) }}</p>
                        </div>

                        <div class="flex justify-between items-center">
                            <p class="text-xs text-gray-500">Status</p>
                            <span v-if="isCancelled" class="px-3 py-1 bg-red-50 text-red-600 text-xs font-bold rounded-full border border-red-100">
                                Dibatalkan
                            </span>
                            <span v-else-if="booking.payment?.payment_status === 'pending'" class="px-3 py-1 bg-red-50 text-red-600 text-xs font-bold rounded-full border border-red-100">
                                Menunggu Pembayaran
                            </span>
                            <span v-else class="px-3 py-1 bg-amber-50 text-amber-600 text-xs font-bold rounded-full border border-amber-100">
                                Menunggu Verifikasi
                            </span>
                        </div>
                    </div>

                    <p v-if="!isCancelled" class="text-xs text-gray-500 mb-8 max-w-xs leading-relaxed">
                        Setelah pembayaran diverifikasi, tiket booking Anda akan tersedia dan dapat digunakan.
                    </p>

                    <!-- Help Card (Desktop Only) -->
                    <div v-if="!isCancelled" class="hidden md:block bg-amber-50/50 rounded-2xl border border-amber-100 p-5 w-full text-left">
                        <h4 class="text-sm font-bold text-[#0A2540] mb-1">Butuh Bantuan?</h4>
                        <p class="text-[11px] text-gray-500 mb-4">Hubungi admin jika ada kendala pembayaran.</p>

                        <div class="space-y-2">
                            <a href="https://wa.me/6281234567890" target="_blank" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 text-[#0A2540] font-bold py-2.5 rounded-xl text-xs transition-colors shadow-sm">
                                <i class="fa-brands fa-whatsapp text-green-500 text-sm"></i> WhatsApp Admin
                            </a>
                            <button @click="router.get(`/payment/${booking.payment?.id}`)" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 text-[#0A2540] font-bold py-2.5 rounded-xl text-xs transition-colors shadow-sm">
                                Lihat Cara Pembayaran
                            </button>
                        </div>
                    </div>

                    <button v-if="isCancelled" @click="router.get('/aktivitas')" class="mt-2 bg-white border border-gray-300 hover:bg-gray-50 text-[#0A2540] font-bold py-3.5 px-8 rounded-xl shadow-sm transition-colors text-sm tracking-wide w-full">
                        Kembali ke Aktivitas
                    </button>
                </div>
            </template>
        </div>

        <!-- BOTTOM DETAIL (Mobile Only for Pending Status) -->
        <DetailBottomBar v-if="!isConfirmedAndPaid && !isCancelled" :price="Number(booking.total)" buttonText="Bayar" @submit="router.get(`/payment/${booking.payment?.id}`)">
            <template #left-content>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Total Harga</span>
                    <span class="text-lg font-extrabold text-[#0A2540]">{{ formatPrice(booking.total) }}</span>
                    <a href="https://wa.me/6281234567890" target="_blank" class="mt-1 text-xs text-green-600 font-bold hover:underline flex items-center gap-1">
                        <i class="fa-brands fa-whatsapp"></i> Hubungi Admin
                    </a>
                </div>
            </template>
            <template #right-content>
                <button @click="router.get(`/payment/${booking.payment?.id}`)" class="bg-[#FFC000] hover:bg-[#ebd000] text-[#0A2540] font-bold py-3 px-8 rounded-xl shadow-md transition-colors text-sm tracking-wide">
                    Bayar
                </button>
            </template>
        </DetailBottomBar>

        <!-- Copied Toast Notification -->
        <Toast :show="showCopiedToast" :message="toastMessage" :type="toastType" />

    </div>
    </AppLayout>
</template>

<style scoped>
.ticket-shape {
    /* Create perfectly transparent cutouts exactly 224px from top */
    mask-image: radial-gradient(circle at 0% 224px, transparent 16px, black 16.5px),
                radial-gradient(circle at 100% 224px, transparent 16px, black 16.5px);
    mask-size: 51% 100%;
    mask-position: left, right;
    mask-repeat: no-repeat;
    -webkit-mask-image: radial-gradient(circle at 0% 224px, transparent 16px, black 16.5px),
                        radial-gradient(circle at 100% 224px, transparent 16px, black 16.5px);
    -webkit-mask-size: 51% 100%;
    -webkit-mask-position: left, right;
    -webkit-mask-repeat: no-repeat;
}
</style>
