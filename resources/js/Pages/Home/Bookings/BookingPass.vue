<script setup>
import { Loader2, Download, X, Clock, CalendarCheck, Copy, Calendar, MessageCircle } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import Toast from '@/Components/UI/Toast.vue';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';
import { toPng } from 'html-to-image';
import { jsPDF } from 'jspdf';
import NoImageIcon from '@/Components/UI/Icons/NoImageIcon.vue';

const props = defineProps({
    booking: {
        type: Object,
        required: true
    }
});

const barcodeRef = ref(null);
const qrcodeRef = ref(null);
const ticketRef = ref(null);
const isDownloading = ref(false);
const activeCodeTab = ref('barcode');
const imageError = ref(false);

const isConfirmedAndPaid = computed(() => {
    // Tiket ditampilkan jika booking confirmed/active/completed DAN payment paid
    return (['confirmed', 'active', 'completed'].includes(props.booking.booking_status)) &&
           (props.booking.payment?.payment_status === 'paid');
});

// Sudah kirim bukti, menunggu konfirmasi owner
const isVerifying = computed(() => {
    return props.booking.payment?.payment_status === 'verifying';
});

// Belum ada bukti pembayaran, perlu bayar
const isNeedPayment = computed(() => {
    return !isCancelled.value &&
           !isConfirmedAndPaid.value &&
           !isVerifying.value &&
           props.booking.payment?.payment_status === 'pending';
});

// Ditolak owner
const isRejected = computed(() => {
    return props.booking.payment?.payment_status === 'rejected';
});

// Expired
const isExpired = computed(() => {
    return props.booking.payment?.payment_status === 'expired';
});

const isCancelled = computed(() => {
    return props.booking.booking_status === 'cancelled';
});

const assetImage = computed(() => {
    const firstImgObj = props.booking.asset?.first_image || props.booking.asset?.firstImage;
    const img = firstImgObj?.image_url || firstImgObj?.image;
    if (img) {
        if (img.startsWith('http')) return img;
        if (img.startsWith('/assets/') || img.startsWith('/storage/')) return img;
        if (img.startsWith('assets/')) return '/' + img;
        return img.startsWith('/') ? '/storage' + img : '/storage/' + img;
    }
    return null;
});

const generateCodes = async () => {
    if (barcodeRef.value && props.booking.booking_code) {
        JsBarcode(barcodeRef.value, props.booking.booking_code, {
            format: "CODE128",
            lineColor: "#000",
            width: 1.5,
            height: 60,
            displayValue: false,
            margin: 0
        });
    }

    if (qrcodeRef.value && props.booking.booking_code) {
        try {
            await QRCode.toCanvas(qrcodeRef.value, props.booking.booking_code, {
                width: 200,
                margin: 1,
                color: {
                    dark: '#0A2540',
                    light: '#F9FAFB'
                }
            });
        } catch (err) {
            console.error('Error generating QR code', err);
        }
    }
};

onMounted(() => {
    if (isConfirmedAndPaid.value) {
        nextTick(() => {
            generateCodes();
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

const durationString = computed(() => {
    const startDate = new Date(props.booking.start_date);
    const endDate = new Date(props.booking.end_date);
    let diff = Math.abs(endDate - startDate) / 36e5; // dalam jam

    if (diff >= 8760) return `${Math.round(diff / 8760)} Tahun`;
    if (diff >= 720) return `${Math.round(diff / 720)} Bulan`;
    if (diff >= 168) return `${Math.round(diff / 168)} Minggu`;
    if (diff >= 24) return `${Math.round(diff / 24)} Hari`;

    return `${Math.round(diff * 10) / 10} Jam`;
});

const assetTitle = computed(() => {
    let title = props.booking.asset_name || '';
    let unitName = props.booking.asset_unit_name;
    if (unitName) {
        title += ' - ' + unitName;
    }
    return title;
});

const locationString = computed(() => {
    const city = props.booking.asset?.city || '';
    const address = props.booking.asset?.address || '';
    return [city, address].filter(Boolean).join(', ');
});

const formatCapacity = (cap) => {
    if (!cap) return 'Sesuai Ketentuan';
    const capStr = String(cap).trim();
    if (capStr.toLowerCase().includes('orang')) return capStr;
    return `${capStr} Orang`;
};

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
            backgroundColor: '#ffffff',
            filter: (node) => {
                if (node.classList && node.classList.contains('exclude-from-pdf')) {
                    return false;
                }
                return true;
            }
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
            <DetailNavbar backUrl="/aktivitas" :showBackButton="true" :forceBackUrl="true" :showSections="false" :showShare="false" :showFavorite="false" />

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
                            <template v-if="assetImage && !imageError">
                                <img :src="assetImage"
                                     class="w-full h-full object-cover"
                                     alt="Asset Image" crossorigin="anonymous"
                                     @error="imageError = true" />
                            </template>
                            <template v-else>
                                <NoImageIcon class="w-full h-full object-contain p-8 opacity-60" />
                            </template>
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
                                <img src="/kusewa-logo.png" alt="Logo" class="h-4 w-auto object-contain drop-shadow-md">
                            </div>
                        </div>

                        <!-- Dashed Separator placed exactly at 224px (cutouts) -->
                        <div class="w-full relative z-10 flex justify-center -my-[1px]">
                            <div class="w-full mx-6 border-t-[2.5px] border-dashed border-gray-300"></div>
                        </div>

                        <!-- Details Body -->
                        <div class="p-6 pt-8 pb-8 bg-white relative">
                        <div class="space-y-0">
                            <div class="grid grid-cols-2 gap-y-5 gap-x-4 mb-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Asset / Unit</p>
                                    <p class="font-bold text-[13px] text-[#0A2540] truncate pr-2">{{ assetTitle }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Kapasitas</p>
                                    <p class="font-bold text-[13px] text-[#0A2540]">{{ formatCapacity(booking.asset?.detail?.capacity) }}</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 py-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Waktu Mulai</p>
                                        <p class="font-bold text-[12px] text-[#0A2540]">{{ formatDate(booking.start_date) }}</p>
                                        <p class="text-[11px] text-gray-500 font-medium">Pukul {{ formatTime(booking.start_date) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Waktu Selesai</p>
                                        <p class="font-bold text-[12px] text-[#0A2540]">{{ formatDate(booking.end_date) }}</p>
                                        <p class="text-[11px] text-gray-500 font-medium">Pukul {{ formatTime(booking.end_date) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-gray-100 py-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Durasi Sewa</p>
                                    <p class="font-bold text-[13px] text-[#0A2540]">{{ durationString }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Status</p>
                                    <span class="px-2.5 py-1 bg-green-100 text-green-700 text-[10px] font-black tracking-wide rounded-md uppercase">Terverifikasi</span>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 py-4">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Penyewa</p>
                                        <p class="font-bold text-[13px] text-[#0A2540]">{{ booking.user?.name || 'User' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Penyedia / Owner</p>
                                        <p class="font-bold text-[13px] text-[#0A2540]">{{ booking.asset?.owner_profile?.user?.name || 'Owner' }}</p>
                                        <a v-if="booking.asset?.owner_profile?.user?.phone" :href="'https://wa.me/' + booking.asset?.owner_profile?.user?.phone" target="_blank" class="text-[10px] font-bold text-green-500 hover:underline mt-0.5 inline-flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-3 h-3 fill-current"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg> {{ booking.asset.owner_profile.user.phone }}</a>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 font-semibold">Lokasi Aset</p>
                                <p class="font-bold text-[13px] text-[#0A2540] leading-relaxed">{{ locationString || 'Lokasi tidak diketahui' }}</p>
                            </div>
                        </div>

                        <!-- Code Area (Tabs) -->
                        <div class="mt-8 flex flex-col items-center justify-center p-5 bg-gray-50 rounded-2xl border border-gray-100/80">
                            <!-- Tabs -->
                            <div class="flex items-center gap-1 mb-4 bg-gray-200/80 p-1 rounded-lg w-full max-w-[220px] exclude-from-pdf">
                                <button @click="activeCodeTab = 'barcode'" :class="activeCodeTab === 'barcode' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700'" class="flex-1 px-3 py-1.5 rounded-md text-xs font-bold transition-all">Barcode</button>
                                <button @click="activeCodeTab = 'qrcode'" :class="activeCodeTab === 'qrcode' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700'" class="flex-1 px-3 py-1.5 rounded-md text-xs font-bold transition-all">QR Code</button>
                            </div>

                            <div v-show="activeCodeTab === 'barcode'" class="w-full flex justify-center">
                                <svg ref="barcodeRef" class="h-16"></svg>
                            </div>
                            <div v-show="activeCodeTab === 'qrcode'" class="w-full flex justify-center">
                                <canvas ref="qrcodeRef" class="w-40 h-40 rounded-xl mix-blend-multiply"></canvas>
                            </div>

                            <p class="text-[10px] text-gray-400 mt-3 font-medium text-center">Tunjukkan {{ activeCodeTab === 'barcode' ? 'barcode' : 'QR code' }} ini kepada pihak penyedia</p>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Buttons (Desktop) -->
                <div class="mt-5 space-y-3 max-w-sm mx-auto hidden md:block">
                    <button @click="downloadTicketPDF" :disabled="isDownloading" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-[0.98] transition-all text-[#0A2540] font-bold py-3.5 rounded-xl shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <Loader2 v-if="isDownloading" class="animate-spin" />
                        <Download v-else class="" />
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
                        <div class="absolute inset-0 rounded-full" :class="isCancelled ? 'bg-red-500/20' : 'bg-[#FFC000]/20'"></div>
                        <div class="absolute inset-4 bg-white rounded-full shadow-sm flex items-center justify-center border-4" :class="isCancelled ? 'border-red-500' : 'border-[#FFC000]'">
                            <X v-if="isCancelled" class="text-5xl text-red-500" />
                            <Clock v-else class="text-4xl text-[#0A2540]" />
                        </div>
                        <div v-if="!isCancelled" class="absolute -bottom-2 -right-2 bg-white rounded-lg shadow-md p-2 border border-gray-100">
                            <CalendarCheck class="text-[#FFC000] text-xl" />
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
                                <Copy class="text-amber-600" /> Salin
                            </button>
                        </div>

                        <div class="mb-4">
                            <p class="font-bold text-sm text-[#0A2540]">{{ assetTitle }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5 truncate">{{ locationString || 'Lokasi tidak diketahui' }}</p>
                            <p class="text-[11px] text-gray-500 mt-1"><Calendar class="mr-1" /> {{ formatDate(booking.start_date) }} &bull; {{ formatTime(booking.start_date) }} - {{ formatTime(booking.end_date) }}</p>
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
                                <MessageCircle class="text-green-500 text-sm" /> WhatsApp Admin
                            </a>
                            <button @click="router.get(`/payment/${booking.payment?.id}`)" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 text-[#0A2540] font-bold py-2.5 rounded-xl text-xs transition-colors shadow-sm">
                                Lihat Cara Pembayaran
                            </button>
                        </div>
                    </div>

                    <button v-if="isCancelled" @click="router.get('/aktivitas')" class="mt-2 hidden md:block bg-[#FFC000] hover:bg-[#ebd000] text-[#0A2540] font-bold py-3.5 px-8 rounded-xl shadow-sm transition-colors text-sm tracking-wide w-full">
                        Kembali ke Aktivitas
                    </button>
                </div>
            </template>
        </div>

        <!-- BOTTOM BAR: Dibatalkan / Ditolak / Expired -->
        <DetailBottomBar v-if="isCancelled || isRejected || isExpired" hideLeftContent>
            <template #right-content>
                <button @click="router.get('/aktivitas')" class="w-full bg-[#FFC000] hover:bg-[#ebd000] text-[#0A2540] font-bold py-3 px-6 rounded-xl shadow-md transition-colors text-sm tracking-wide flex items-center justify-center gap-2">
                    Kembali ke Aktivitas
                </button>
            </template>
        </DetailBottomBar>

        <!-- BOTTOM BAR: Belum Bayar (payment pending) -->
        <DetailBottomBar v-if="isNeedPayment" :price="Number(booking.total)" buttonText="Bayar Sekarang" @submit="router.get(`/payment/${booking.payment?.id}`)">
            <template #left-content>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Total Harga</span>
                    <span class="text-lg font-extrabold text-[#0A2540]">{{ formatPrice(booking.total) }}</span>
                </div>
            </template>
            <template #right-content>
                <button @click="router.get(`/payment/${booking.payment?.id}`)" class="bg-[#FFC000] hover:bg-[#ebd000] text-[#0A2540] font-bold py-3 px-8 rounded-xl shadow-md transition-colors text-sm tracking-wide">
                    Bayar Sekarang
                </button>
            </template>
        </DetailBottomBar>

        <!-- BOTTOM BAR: Bukti Dikirim, Menunggu Verifikasi Owner -->
        <DetailBottomBar v-if="isVerifying" hideLeftContent>
            <template #right-content>
                <div class="w-full flex items-center justify-center gap-2.5 py-3 px-6 bg-amber-50 border border-amber-200 rounded-xl">
                    <Clock class="text-amber-500 animate-pulse" />
                    <span class="text-sm font-bold text-amber-700">Menunggu Konfirmasi Owner</span>
                </div>
            </template>
        </DetailBottomBar>

        <!-- BOTTOM BAR: Tiket Aktif - Download -->
        <DetailBottomBar v-if="isConfirmedAndPaid" hideLeftContent>
            <template #right-content>
                <button @click="downloadTicketPDF" :disabled="isDownloading" class="w-full bg-[#FFC000] hover:bg-[#ebd000] text-[#0A2540] font-bold py-3 px-6 rounded-xl shadow-md transition-colors text-sm tracking-wide flex items-center justify-center gap-2 disabled:opacity-50">
                    <Loader2 v-if="isDownloading" class="animate-spin" />
                    <Download v-else class="" />
                    {{ isDownloading ? 'Mengunduh...' : 'Download Tiket' }}
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
