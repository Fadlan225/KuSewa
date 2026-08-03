<template>
  <AppLayout hideNavbar hideBottombar>
    <!-- NAV DETIL dengan tombol back -->
    <DetailNavbar backUrl="/aktivitas" :showSections="false" :showShare="false" :showFavorite="false" />

    <div class="min-h-screen bg-[#f2f2f7] text-slate-900 font-sans antialiased pb-24 pt-4 selection:bg-[#ffc000]/30">
      <main class="max-w-3xl mx-auto px-4 sm:px-6 space-y-6">

        <!-- TOTAL PEMBAYARAN -->
        <div class="bg-white rounded-2xl p-6 sm:py-8 border border-slate-100 shadow-sm flex flex-col items-center justify-center relative">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Total Pembayaran</span>
          <span class="text-4xl font-black text-[#0A2540] tracking-tight">{{ formatCurrency(payment.booking.total) }}</span>
        </div>

        <!-- REAL COUNTDOWN TIMER CARD (SCOREBOARD STYLE) -->
        <div class="bg-white rounded-2xl p-6 sm:py-8 border border-slate-100 shadow-sm flex flex-col items-center justify-center relative">
          <p class="text-[10px] sm:text-xs font-bold text-slate-500 mb-6 tracking-widest uppercase text-center">Selesaikan Pembayaran Dalam Waktu</p>

          <div class="flex items-center justify-center gap-2 sm:gap-4">
             <!-- Days -->
             <template v-if="timerObj.days !== '00'">
                 <div class="flex flex-col items-center min-w-[50px] sm:min-w-[64px]">
                    <div class="relative h-[40px] sm:h-[48px] w-full flex justify-center items-center overflow-hidden">
                        <transition name="slide-fade">
                            <span :key="timerObj.days" class="absolute text-slate-800 text-4xl sm:text-5xl font-mono font-medium tracking-tight">{{ timerObj.days }}</span>
                        </transition>
                    </div>
                    <span class="text-slate-400 text-[10px] font-medium lowercase tracking-wider mt-2">days</span>
                 </div>
                 <span class="text-slate-300 text-3xl sm:text-4xl font-light mb-6">:</span>
             </template>

             <!-- Hours -->
             <div class="flex flex-col items-center min-w-[50px] sm:min-w-[64px]">
                <div class="relative h-[40px] sm:h-[48px] w-full flex justify-center items-center overflow-hidden">
                    <transition name="slide-fade">
                        <span :key="timerObj.hours" class="absolute text-slate-800 text-4xl sm:text-5xl font-mono font-medium tracking-tight">{{ timerObj.hours }}</span>
                    </transition>
                </div>
                <span class="text-slate-400 text-[10px] font-medium lowercase tracking-wider mt-2">hours</span>
             </div>
             <span class="text-slate-300 text-3xl sm:text-4xl font-light mb-6">:</span>

             <!-- Minutes -->
             <div class="flex flex-col items-center min-w-[50px] sm:min-w-[64px]">
                <div class="relative h-[40px] sm:h-[48px] w-full flex justify-center items-center overflow-hidden">
                    <transition name="slide-fade">
                        <span :key="timerObj.minutes" class="absolute text-slate-800 text-4xl sm:text-5xl font-mono font-medium tracking-tight">{{ timerObj.minutes }}</span>
                    </transition>
                </div>
                <span class="text-slate-400 text-[10px] font-medium lowercase tracking-wider mt-2">minutes</span>
             </div>
             <span class="text-slate-300 text-3xl sm:text-4xl font-light mb-6">:</span>

             <!-- Seconds -->
             <div class="flex flex-col items-center min-w-[50px] sm:min-w-[64px]">
                <div class="relative h-[40px] sm:h-[48px] w-full flex justify-center items-center overflow-hidden">
                    <transition name="slide-fade">
                        <span :key="timerObj.seconds" class="absolute text-slate-800 text-4xl sm:text-5xl font-mono font-medium tracking-tight">{{ timerObj.seconds }}</span>
                    </transition>
                </div>
                <span class="text-slate-400 text-[10px] font-medium lowercase tracking-wider mt-2">seconds</span>
             </div>
          </div>
        </div>

        <div class="flex flex-col space-y-6 items-center">

          <!-- KOLOM KIRI: INFO REKENING & PANDUAN -->
          <div class="w-full space-y-6">

            <!-- Rekening Tujuan Card -->
            <div class="bg-white rounded-3xl p-1 shadow-sm border border-slate-100/60 overflow-hidden relative group">
              <div class="absolute inset-0 bg-gradient-to-br from-slate-50/50 to-white -z-10"></div>
              <div class="p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between">
                  <h2 class="text-sm sm:text-base font-bold tracking-tight text-slate-900 uppercase">Rekening Tujuan</h2>
                </div>

                <div v-if="selectedBank" class="bg-white rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 border border-slate-200 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl border flex items-center justify-center border-slate-100 bg-slate-50 shadow-inner shrink-0">
                      <i class="fa-solid fa-building-columns text-slate-400 text-lg sm:text-xl"></i>
                    </div>
                    <div>
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{{ selectedBank.bank_name }}</p>
                      <p class="text-lg sm:text-xl font-black text-slate-900 tracking-wider font-mono">{{ selectedBank.account_number }}</p>
                      <p class="text-xs font-medium text-slate-500 mt-0.5">a.n {{ selectedBank.account_holder }}</p>
                    </div>
                  </div>
                  <button type="button" @click="copyToClipboard(selectedBank.account_number)" class="w-full sm:w-auto text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 py-2.5 sm:py-3 px-4 sm:px-5 rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm shrink-0 active:scale-95">
                    <i v-if="!isCopied" class="fa-regular fa-copy"></i>
                    <i v-else class="fa-solid fa-check text-green-600"></i>
                    <span v-if="!isCopied">Salin Rekening</span>
                    <span v-else class="text-green-600">Tersalin!</span>
                  </button>
                </div>
                <div v-else class="text-sm text-red-500 p-4 rounded-xl border border-red-200 bg-red-50">
                  Informasi rekening tidak ditemukan.
                </div>
              </div>
            </div>

            <!-- Upload Bukti Pembayaran -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-100/60 space-y-5 relative overflow-hidden">
              <div class="flex items-center justify-between">
                <h2 class="text-sm sm:text-base font-bold tracking-tight text-slate-900 uppercase">Konfirmasi Pembayaran</h2>
                <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 px-2 py-1 rounded-md border border-slate-100">Wajib</span>
              </div>

              <div class="border-2 border-dashed rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center transition-all cursor-pointer relative overflow-hidden group"
                   :class="paymentProof ? 'border-[#ffc000] bg-[#ffc000]/5' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50 hover:border-slate-300'">
                <input type="file" @change="handleFileUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*,.pdf" />

                <div v-if="!paymentProof" class="space-y-4 relative z-0 pointer-events-none">
                  <div class="w-14 h-14 sm:w-16 sm:h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm border border-slate-100 text-slate-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700">Unggah Bukti Transfer</p>
                    <p class="text-xs text-slate-500 mt-1">Format JPG, PNG, atau PDF (Maks. 5MB)</p>
                  </div>
                  <div class="inline-block px-5 py-2.5 bg-[#0A2540] text-white text-xs font-bold rounded-xl shadow-sm transition-all group-hover:bg-slate-800 group-hover:shadow-md group-hover:-translate-y-0.5">Pilih File</div>
                </div>

                <div v-else class="space-y-4 relative z-0 pointer-events-none">
                  <div class="w-14 h-14 sm:w-16 sm:h-16 bg-[#ffc000] rounded-full flex items-center justify-center mx-auto shadow-sm text-slate-900">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-900 truncate max-w-[200px] sm:max-w-xs mx-auto">{{ paymentProof.name }}</p>
                    <p class="text-xs text-slate-500 mt-1">Berhasil dipilih. Siap dikirim!</p>
                  </div>
                  <div class="inline-block px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl shadow-sm">Ganti File</div>
                </div>
              </div>
            </div>

            <!-- Panduan Pembayaran -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-100/60 space-y-6">
              <h2 class="text-sm sm:text-base font-bold tracking-tight text-slate-900 uppercase">Panduan Pembayaran</h2>

              <div class="space-y-5">
                <div class="flex gap-4 items-start">
                  <div class="w-7 h-7 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0 text-xs font-bold text-slate-500 mt-0.5">1</div>
                  <div>
                    <p class="text-sm font-bold text-slate-800">Buka Aplikasi / ATM</p>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Gunakan aplikasi Mobile Banking, Internet Banking, atau mesin ATM terdekat.</p>
                  </div>
                </div>
                <div class="flex gap-4 items-start">
                  <div class="w-7 h-7 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0 text-xs font-bold text-slate-500 mt-0.5">2</div>
                  <div>
                    <p class="text-sm font-bold text-slate-800">Pilih Transfer ke <span class="text-[#ffc000]">{{ selectedBank?.bank_name || 'Bank Tujuan' }}</span></p>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Masukkan nomor rekening <strong class="font-mono text-slate-700 bg-slate-50 px-1 rounded">{{ selectedBank?.account_number || '-' }}</strong> a.n {{ selectedBank?.account_holder || '-' }}.</p>
                  </div>
                </div>
                <div class="flex gap-4 items-start">
                  <div class="w-7 h-7 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0 text-xs font-bold text-slate-500 mt-0.5">3</div>
                  <div>
                    <p class="text-sm font-bold text-slate-800">Masukkan Nominal Persis</p>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Pastikan nominal transfer sama persis dengan <strong>Total Pembayaran</strong> di atas.</p>
                  </div>
                </div>
                <div class="flex gap-4 items-start">
                  <div class="w-7 h-7 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0 text-xs font-bold text-slate-500 mt-0.5">4</div>
                  <div>
                    <p class="text-sm font-bold text-slate-800">Simpan & Unggah Bukti</p>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Simpan resi atau <em>screenshot</em> bukti transfer, lalu unggah pada kolom di atas untuk diverifikasi.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Button Desktop -->
            <div class="hidden lg:block pt-4">
              <button
                type="button"
                @click="handlePayment"
                :disabled="isLoading || !selectedBank || timeLeft <= 0 || !paymentProof"
                class="w-full bg-[#ffc000] hover:bg-[#ebd000] active:scale-[0.98] text-slate-950 font-extrabold py-3.5 rounded-xl transition-all shadow-md shadow-[#ffc000]/20 text-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                <span v-if="!isLoading">Bayar Sekarang</span>
                <span v-else class="flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  Memproses...
                </span>
              </button>
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
      buttonText="Bayar Sekarang"
      @submit="handlePayment"
      :disabled="isLoading || !selectedBank || timeLeft <= 0 || !paymentProof"
    />

  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DetailNavbar from '@/Components/UI/DetailNavbar.vue'
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue'

const props = defineProps({
  payment: {
    type: Object,
    required: true
  },
  selectedBank: {
    type: Object,
    default: null
  }
})

const isLoading = ref(false)
const paymentProof = ref(null)
const isCopied = ref(false)

const copyToClipboard = async (text) => {
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text)
    } else {
      const textArea = document.createElement("textarea")
      textArea.value = text
      textArea.style.position = "fixed"
      textArea.style.left = "-999999px"
      textArea.style.top = "-999999px"
      document.body.appendChild(textArea)
      textArea.focus()
      textArea.select()
      document.execCommand('copy')
      textArea.remove()
    }
    isCopied.value = true
    setTimeout(() => {
      isCopied.value = false
    }, 2000)
  } catch (err) {
    console.error('Gagal menyalin:', err)
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      alert('Ukuran file maksimal 5MB')
      event.target.value = ''
      return
    }
    paymentProof.value = file
  }
}

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

const timerObj = computed(() => {
  let diff = timeLeft.value;
  if (diff <= 0) return { days: '00', hours: '00', minutes: '00', seconds: '00' };

  const d = Math.floor(diff / 86400).toString().padStart(2, '0');
  const h = Math.floor((diff % 86400) / 3600).toString().padStart(2, '0');
  const m = Math.floor((diff % 3600) / 60).toString().padStart(2, '0');
  const s = (diff % 60).toString().padStart(2, '0');

  return { days: d, hours: h, minutes: m, seconds: s };
});

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

  if (!paymentProof.value) {
    alert('Mohon unggah bukti pembayaran terlebih dahulu.');
    return;
  }

  isLoading.value = true

  const formData = new FormData()
  formData.append('payment_id', props.payment.id)
  formData.append('proof_of_payment', paymentProof.value)

  router.post(route('payment.store'), formData, {
    forceFormData: true,
    onSuccess: () => {
      isLoading.value = false
    },
    onError: (errors) => {
      isLoading.value = false
      console.error('Payment error:', errors)
      alert('Gagal mengirim bukti pembayaran. Silakan coba lagi.')
    }
  })
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-fade-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}
.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>
