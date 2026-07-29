<template>
  <AppLayout hideNavbar hideBottombar>
    <!-- NAV DETIL dengan tombol back -->
    <DetailNavbar backUrl="/booking-page" :showSections="false" :showShare="false" :showFavorite="false" />

    <div class="min-h-screen bg-[#f2f2f7] text-slate-900 font-sans antialiased pb-24 pt-4 selection:bg-[#ffc000]/30">
      <main class="max-w-4xl mx-auto px-4 sm:px-6 space-y-6">

        <!-- REAL COUNTDOWN TIMER CARD -->
        <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-4 border border-black/5 shadow-xs flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center text-red-500 font-black text-sm">
              <i class="fa-regular fa-clock"></i>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-400">Selesaikan Pembayaran Sebelum</p>
              <p class="text-xs font-bold text-slate-800">Batas Waktu Habis</p>
            </div>
          </div>
          <div class="bg-red-500 text-white px-3 py-1.5 rounded-full font-mono text-xs font-extrabold tracking-wider" :class="{ 'bg-gray-400': timeLeft <= 0 }">
            {{ formattedTimer }}
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

          <!-- KOLOM KIRI: METODE PEMBAYARAN (7 COLS) -->
          <div class="lg:col-span-7 space-y-5">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 px-1">Metode Pembayaran</h1>

            <!-- iOS GROUPED LIST -->
            <div class="bg-white rounded-2xl border border-black/5 shadow-xs overflow-hidden divide-y divide-black/5">

              <!-- TRANSFER BANK (Dynamic from DB) -->
              <div>
                <div class="w-full p-4 flex items-center justify-between bg-slate-50/80">
                  <div class="flex items-center gap-3.5">
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center border-[#ffc000] bg-[#ffc000]">
                      <svg class="w-3 h-3 text-slate-950 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-slate-900">Transfer Bank</span>
                  </div>
                </div>

                <!-- Sub-items Bank (iOS Nested Inset List) -->
                <div class="px-4 pb-4 pt-1 space-y-2 bg-slate-50/50">
                  <div v-for="bank in bankAccounts" :key="bank.id"
                       @click="selectedBank = bank.id"
                       :class="selectedBank === bank.id ? 'bg-white ring-2 ring-[#ffc000] shadow-xs' : 'bg-white/60 hover:bg-white border border-black/5'"
                       class="p-3 rounded-xl flex items-center justify-between cursor-pointer transition-all">
                    <div class="flex items-center gap-3">
                      <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                           :class="selectedBank === bank.id ? 'border-[#ffc000] bg-[#ffc000]' : 'border-slate-300'">
                        <div v-if="selectedBank === bank.id" class="w-1.5 h-1.5 rounded-full bg-slate-950"></div>
                      </div>
                      <div class="flex flex-col">
                        <span class="text-xs font-semibold text-slate-800">{{ bank.bank_name }}</span>
                        <span class="text-[10px] text-slate-500">{{ bank.account_holder }}</span>
                      </div>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400 font-mono">{{ bank.account_number }}</span>
                  </div>

                  <!-- Empty State -->
                  <div v-if="bankAccounts.length === 0" class="text-center text-xs text-slate-500 py-4">
                    Belum ada metode pembayaran yang tersedia.
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- KOLOM KANAN: RINCIAN PESANAN (5 COLS) -->
          <div class="lg:col-span-5 space-y-4">
            <h2 class="text-xl font-bold tracking-tight text-slate-900 px-1">Ringkasan</h2>

            <!-- CARD SESUAI GAMBAR -->
            <div class="bg-white rounded-2xl p-5 border border-black/5 shadow-xs space-y-6 sticky top-24">

              <!-- Title / Harga -->
              <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                  {{ formatCurrency(payment.booking.subtotal) }}
                </h2>
              </div>

              <!-- Rent Period Card -->
              <div class="rounded-xl border border-gray-200 overflow-hidden text-sm">
                <div class="grid grid-cols-2 divide-x divide-gray-200 border-b border-gray-200">
                  <div class="p-3">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Mulai Sewa</span>
                    <div class="flex items-center justify-between">
                      <span class="font-bold text-slate-900">{{ formatDate(payment.booking.start_date) }}</span>
                      <i class="fa-regular fa-calendar text-slate-400"></i>
                    </div>
                  </div>
                  <div class="p-3">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Selesai Sewa</span>
                    <div class="flex items-center justify-between">
                      <span class="font-bold text-slate-900">{{ formatDate(payment.booking.end_date) }}</span>
                      <i class="fa-regular fa-calendar text-slate-400"></i>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Button Desktop -->
              <div class="hidden md:block">
                <button
                  type="button"
                  @click="handlePayment"
                  :disabled="isLoading || bankAccounts.length === 0 || timeLeft <= 0"
                  class="w-full bg-[#ffc000] hover:bg-[#ebd000] active:scale-[0.98] text-slate-950 font-extrabold py-3.5 rounded-xl transition-all shadow-md shadow-[#ffc000]/20 text-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                  <span v-if="!isLoading">Pesan Sekarang</span>
                  <span v-else class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Memproses...
                  </span>
                </button>
              </div>

              <div class="h-px bg-gray-100"></div>

              <!-- Breakdown -->
              <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-500">Subtotal</span>
                  <span class="font-bold text-slate-900">{{ formatCurrency(payment.booking.subtotal) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-500">Biaya Layanan</span>
                  <span class="font-bold text-slate-900">{{ formatCurrency(payment.booking.service_fee) }}</span>
                </div>
              </div>

              <!-- Total -->
              <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                <span class="text-lg font-black text-slate-900">Total</span>
                <span class="text-lg font-black text-slate-900">{{ formatCurrency(payment.booking.total) }}</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- BOTTOM DETAIL (Mobile Only) -->
    <DetailBottomBar
      :price="Number(payment.booking.total)"
      :durationCount="0"
      durationLabel=""
      buttonText="Pesan Sekarang"
      @submit="handlePayment"
      :disabled="isLoading || bankAccounts.length === 0 || timeLeft <= 0"
    />

  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DetailNavbar from '@/Components/UI/DetailNavbar.vue'
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue'

const props = defineProps({
  payment: {
    type: Object,
    required: true
  },
  bankAccounts: {
    type: Array,
    default: () => []
  }
})

const selectedBank = ref(props.bankAccounts.length > 0 ? props.bankAccounts[0].id : null)
const isLoading = ref(false)

// Real Countdown Timer Logic
const timeLeft = ref(0)
let timerInterval = null

const calculateTimeLeft = () => {
  if (!props.payment || !props.payment.expires_at) return 0;
  // Parse expiry date from database (assuming UTC or server timezone, you might need to adjust if timezone differs, but JS Date parsing usually handles ISO strings)
  const expiryDate = new Date(props.payment.expires_at.replace(' ', 'T')).getTime();
  const now = new Date().getTime();
  const diff = Math.floor((expiryDate - now) / 1000);
  return diff > 0 ? diff : 0;
}

const formattedTimer = computed(() => {
  if (timeLeft.value <= 0) return '00:00:00';
  const h = Math.floor(timeLeft.value / 3600).toString().padStart(2, '0');
  const m = Math.floor((timeLeft.value % 3600) / 60).toString().padStart(2, '0');
  const s = (timeLeft.value % 60).toString().padStart(2, '0');
  return `${h}:${m}:${s}`;
})

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '/');
}

onMounted(() => {
  timeLeft.value = calculateTimeLeft();
  timerInterval = setInterval(() => {
    timeLeft.value = calculateTimeLeft();
    if (timeLeft.value <= 0) {
      clearInterval(timerInterval);
    }
  }, 1000)
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

const handlePayment = () => {
  if (timeLeft.value <= 0) {
    alert('Waktu pembayaran telah habis.');
    return;
  }

  isLoading.value = true
  setTimeout(() => {
    isLoading.value = false
    alert('Mengarahkan ke halaman konfirmasi...')
  }, 1200)
}
</script>
