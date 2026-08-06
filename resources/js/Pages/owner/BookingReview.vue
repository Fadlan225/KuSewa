<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Sidebar from '@/Components/sidebar.vue';

const props = defineProps({
    booking: Object,
    messages: { type: Array, default: () => [] }, // tambahan
});

const formatCurrency = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;

const confirming = ref(false);
const rejecting = ref(false);

const confirmBooking = () => {
    confirming.value = true;
    router.patch(`/owner/bookings/${props.booking.id}/confirm`, {}, {
        onFinish: () => { confirming.value = false; },
    });
};

const rejectBooking = () => {
    rejecting.value = true;
    router.patch(`/owner/bookings/${props.booking.id}/reject`, {}, {
        onFinish: () => { rejecting.value = false; },
    });
};

// ========== CHAT ==========
const newMessage = ref('');
const sending = ref(false);

const sendMessage = () => {
    if (!newMessage.value.trim() || sending.value) return;

    sending.value = true;
    router.post(`/owner/bookings/${props.booking.id}/messages`, {
        message: newMessage.value.trim(),
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            newMessage.value = '';
        },
        onFinish: () => {
            sending.value = false;
        },
    });
};

// Mengurutkan pesan dari yang terlama ke terbaru
const sortedMessages = computed(() => {
    return [...props.messages].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

// Cek apakah pesan dikirim oleh owner (pemilik properti)
const isOwnerMessage = (message) => {
    return message.sender_type === 'owner' || message.sender_id === props.booking?.owner_id;
};
</script>

<template>
    <Head title="Tinjau Pengajuan Booking - kusewa.id" />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-700 font-sans flex">
        <Sidebar />
        <main class="flex-1 min-w-0 p-6 md:p-8">
            <div class="max-w-3xl mx-auto space-y-6">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Link :href="route('owner.dashboard')" class="hover:text-slate-800">Dashboard</Link>
                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                    <Link :href="route('owner.bookings')" class="hover:text-slate-800">Pemesanan</Link>
                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                    <span class="font-bold text-slate-800">Tinjau Pengajuan</span>
                </div>

                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Tinjau Pengajuan Booking</h1>
                    <p class="text-sm text-slate-500 mt-1">Periksa detail penyewa sebelum mengonfirmasi atau menolak.</p>
                </div>

                <!-- Card: Detail Booking -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-slate-800">Detail Pesanan</h2>
                        <span class="bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-clock mr-1"></i> Menunggu
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Booking</span>
                            <p class="font-extrabold text-[#0A2540] text-lg">{{ booking.code }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aset</span>
                            <p class="font-bold text-slate-800">{{ booking.asset }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Periode Sewa</span>
                            <p class="font-bold text-slate-800">{{ booking.start_date }} - {{ booking.end_date }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pembayaran</span>
                            <p class="font-extrabold text-emerald-600 text-lg">{{ formatCurrency(booking.total) }}</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-4 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Subtotal Sewa</span>
                            <span class="font-semibold">{{ formatCurrency(booking.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Biaya Layanan</span>
                            <span class="font-semibold">{{ formatCurrency(booking.service_fee) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-2">
                            <span class="font-bold text-slate-800">Total</span>
                            <span class="font-black text-[#0A2540]">{{ formatCurrency(booking.total) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card: Info Penyewa -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-bold text-slate-800">Informasi Penyewa</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama</span>
                            <p class="font-bold text-slate-800">{{ booking.tenant }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</span>
                            <p class="font-bold text-slate-800">{{ booking.tenant_email }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Telepon</span>
                            <p class="font-bold text-slate-800">{{ booking.tenant_phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- ========== CARD CHAT ========== -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-regular fa-comment-dots text-[#FFC000]"></i> Pesan dengan Penyewa
                    </h2>

                    <!-- Daftar pesan -->
                    <div class="h-72 overflow-y-auto space-y-3 p-2 bg-slate-50/50 rounded-xl border border-slate-100">
                        <div v-if="sortedMessages.length === 0" class="text-center text-slate-400 text-sm py-10">
                            <i class="fa-regular fa-comment text-2xl block mb-2"></i>
                            Belum ada pesan. Kirim pesan pertama Anda!
                        </div>

                        <div
                            v-for="msg in sortedMessages"
                            :key="msg.id"
                            :class="[
                                'flex',
                                isOwnerMessage(msg) ? 'justify-end' : 'justify-start'
                            ]"
                        >
                            <div
                                :class="[
                                    'max-w-[80%] px-4 py-2 rounded-2xl text-sm',
                                    isOwnerMessage(msg)
                                        ? 'bg-[#0A2540] text-white rounded-br-none'
                                        : 'bg-white border border-slate-200 text-slate-700 rounded-bl-none'
                                ]"
                            >
                                <p class="whitespace-pre-wrap break-words">{{ msg.message }}</p>
                                <span class="text-[10px] opacity-70 block mt-1">
                                    {{ new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Form kirim pesan -->
                    <div class="flex items-center gap-2">
                        <input
                            v-model="newMessage"
                            @keyup.enter="sendMessage"
                            type="text"
                            placeholder="Ketik pesan..."
                            class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#0A2540]"
                            :disabled="sending"
                        />
                        <button
                            @click="sendMessage"
                            :disabled="!newMessage.trim() || sending"
                            class="bg-[#0A2540] hover:bg-[#081d33] text-white px-4 py-2.5 rounded-xl transition disabled:opacity-50 flex items-center gap-1"
                        >
                            <i v-if="sending" class="fa-solid fa-spinner animate-spin"></i>
                            <i v-else class="fa-regular fa-paper-plane"></i>
                            <span class="text-xs font-bold hidden sm:inline">Kirim</span>
                        </button>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-3 justify-end">
                    <button
                        @click="rejectBooking"
                        :disabled="rejecting || confirming"
                        class="px-5 py-2.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <i v-if="rejecting" class="fa-solid fa-spinner animate-spin"></i>
                        <i v-else class="fa-solid fa-xmark"></i>
                        {{ rejecting ? 'Menolak...' : 'Tolak Pengajuan' }}
                    </button>
                    <button
                        @click="confirmBooking"
                        :disabled="confirming || rejecting"
                        class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <i v-if="confirming" class="fa-solid fa-spinner animate-spin"></i>
                        <i v-else class="fa-solid fa-circle-check"></i>
                        {{ confirming ? 'Mengonfirmasi...' : 'Konfirmasi Booking' }}
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>