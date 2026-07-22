<template>
  <div class="min-h-screen bg-[#f2f2f7] text-slate-900 font-sans antialiased pb-24 selection:bg-[#ffc000]/30 relative">
    
    <!-- NAVIGATION BAR (iOS Style Glassmorphism) -->
    <header class="sticky top-0 z-40 bg-white/70 backdrop-blur-xl border-b border-black/5 transition-all">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
        <Link href="/booking-page" class="flex items-center gap-1 text-sm font-medium text-slate-600 hover:text-black transition-colors">
          <svg class="w-5 h-5 -ml-1 text-[#ffc000]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          Kembali
        </Link>

        <span class="text-base font-bold tracking-tight text-slate-900">
          kusewa<span class="text-[#ffc000]">.id</span>
        </span>

        <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-black/5 px-2.5 py-1 rounded-full">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
          Encrypted
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 mt-6 space-y-6">
      
      <!-- iOS FLOATING TIMER CARD -->
      <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-4 border border-black/5 shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-[#ffc000]/20 flex items-center justify-center text-[#d9a300] font-black text-sm">
            ⚡
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-400">Selesaikan Pembayaran</p>
            <p class="text-xs font-bold text-slate-800">Kamar Di-hold Sementara</p>
          </div>
        </div>
        <div class="bg-black/5 border border-black/5 px-3 py-1.5 rounded-full font-mono text-xs font-extrabold text-slate-800 tracking-wider">
          {{ formattedTimer }}
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- KOLOM KIRI: METODE PEMBAYARAN (7 COLS) -->
        <div class="lg:col-span-7 space-y-5">
          
          <h1 class="text-xl font-bold tracking-tight text-slate-900 px-1">Metode Pembayaran</h1>

          <!-- iOS GROUPED LIST -->
          <div class="bg-white rounded-2xl border border-black/5 shadow-xs overflow-hidden divide-y divide-black/5">
            
            <!-- CATEGORY 1: VIRTUAL ACCOUNT -->
            <div>
              <button 
                type="button" 
                @click="selectedCategory = 'va'" 
                class="w-full p-4 flex items-center justify-between text-left transition-colors cursor-pointer"
                :class="selectedCategory === 'va' ? 'bg-[#ffc000]/5' : 'hover:bg-slate-50/80'">
                <div class="flex items-center gap-3.5">
                  <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                       :class="selectedCategory === 'va' ? 'border-[#ffc000] bg-[#ffc000]' : 'border-slate-300'">
                    <svg v-if="selectedCategory === 'va'" class="w-3 h-3 text-slate-950 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                  </div>
                  <span class="text-sm font-semibold text-slate-900">Virtual Account</span>
                </div>
                <div class="flex gap-1">
                  <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">BCA</span>
                  <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">MANDIRI</span>
                  <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">BRI</span>
                </div>
              </button>

              <!-- Sub-items Bank -->
              <div v-if="selectedCategory === 'va'" class="px-4 pb-4 pt-1 space-y-2 bg-slate-50/50">
                <div v-for="bank in vaBanks" :key="bank.id"
                     @click="selectedBank = bank.id"
                     :class="selectedBank === bank.id ? 'bg-white ring-2 ring-[#ffc000] shadow-xs' : 'bg-white/60 hover:bg-white border border-black/5'"
                     class="p-3 rounded-xl flex items-center justify-between cursor-pointer transition-all">
                  <div class="flex items-center gap-3">
                    <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                         :class="selectedBank === bank.id ? 'border-[#ffc000] bg-[#ffc000]' : 'border-slate-300'">
                      <div v-if="selectedBank === bank.id" class="w-1.5 h-1.5 rounded-full bg-slate-950"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-800">{{ bank.name }}</span>
                  </div>
                  <span class="text-[11px] font-bold text-slate-400 font-mono">{{ bank.code }}</span>
                </div>
              </div>
            </div>

            <!-- CATEGORY 2: QRIS -->
            <button 
              type="button" 
              @click="selectCategory('qris', 'qris')" 
              class="w-full p-4 flex items-center justify-between text-left transition-colors cursor-pointer"
              :class="selectedCategory === 'qris' ? 'bg-[#ffc000]/5' : 'hover:bg-slate-50/80'">
              <div class="flex items-center gap-3.5">
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                     :class="selectedCategory === 'qris' ? 'border-[#ffc000] bg-[#ffc000]' : 'border-slate-300'">
                  <svg v-if="selectedCategory === 'qris'" class="w-3 h-3 text-slate-950 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-sm font-semibold text-slate-900">QRIS / Instant Transfer</span>
              </div>
              <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Bebas Biaya</span>
            </button>

            <!-- CATEGORY 3: ATM / BANK TRANSFER -->
            <button 
              type="button" 
              @click="selectCategory('atm', 'atm')" 
              class="w-full p-4 flex items-center justify-between text-left transition-colors cursor-pointer"
              :class="selectedCategory === 'atm' ? 'bg-[#ffc000]/5' : 'hover:bg-slate-50/80'">
              <div class="flex items-center gap-3.5">
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                     :class="selectedCategory === 'atm' ? 'border-[#ffc000] bg-[#ffc000]' : 'border-slate-300'">
                  <svg v-if="selectedCategory === 'atm'" class="w-3 h-3 text-slate-950 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-sm font-semibold text-slate-900">Transfer ATM (Manual)</span>
              </div>
              <span class="text-[10px] font-bold text-slate-400">Semua Bank</span>
            </button>

            <!-- CATEGORY 4: KARTU KREDIT/DEBIT -->
            <button 
              type="button" 
              @click="selectCategory('cc', 'cc')" 
              class="w-full p-4 flex items-center justify-between text-left transition-colors cursor-pointer"
              :class="selectedCategory === 'cc' ? 'bg-[#ffc000]/5' : 'hover:bg-slate-50/80'">
              <div class="flex items-center gap-3.5">
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                     :class="selectedCategory === 'cc' ? 'border-[#ffc000] bg-[#ffc000]' : 'border-slate-300'">
                  <svg v-if="selectedCategory === 'cc'" class="w-3 h-3 text-slate-950 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-sm font-semibold text-slate-900">Kartu Kredit / Debit</span>
              </div>
              <div class="flex gap-1">
                <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">VISA</span>
                <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">MC</span>
              </div>
            </button>

          </div>

          <!-- PROMO / KUPON INSET CARD -->
          <div class="bg-white rounded-2xl p-3.5 border border-black/5 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-[#ffc000]/15 flex items-center justify-center text-sm">
                🏷️
              </div>
              <span class="text-xs font-semibold text-slate-800">Punya Kode Promo?</span>
            </div>
            <button type="button" class="text-xs font-bold text-[#b88a00] hover:underline px-3 py-1 cursor-pointer">
              Masukkan
            </button>
          </div>

          <!-- BOTTOM TOTAL & SUBMIT BUTTON -->
          <div class="bg-white rounded-2xl p-5 border border-black/5 shadow-xs space-y-4">
            <div class="flex items-baseline justify-between">
              <span class="text-xs font-medium text-slate-500">Total Pembayaran</span>
              <span class="text-2xl font-black text-slate-900 tracking-tight">Rp 396.082</span>
            </div>

            <button 
              type="button"
              @click="handlePayment"
              :disabled="isLoading"
              class="w-full bg-[#ffc000] hover:bg-[#ebd000] active:scale-[0.98] text-slate-950 font-extrabold py-3.5 rounded-xl transition-all shadow-md shadow-[#ffc000]/20 text-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
              <span v-if="!isLoading">Bayar Sekarang</span>
              <span v-else class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Memproses...
              </span>
            </button>
          </div>

        </div>

        <!-- KOLOM KANAN: RINCIAN PESANAN (5 COLS) -->
        <div class="lg:col-span-5 space-y-4">
          <h2 class="text-xl font-bold tracking-tight text-slate-900 px-1">Ringkasan</h2>

          <div class="bg-white rounded-2xl border border-black/5 shadow-xs overflow-hidden sticky top-20">
            
            <!-- Header Banner -->
            <div class="bg-[#ffc000] p-4 text-slate-950 flex items-center justify-between">
              <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-800/80 block">ID Pesanan</span>
                <p class="text-xs font-mono font-black text-slate-950 leading-tight">#1377346270</p>
              </div>
              <span class="text-[10px] font-extrabold bg-slate-950 text-white px-3 py-1 rounded-full shadow-xs">
                Terverifikasi
              </span>
            </div>

            <!-- Content Detail -->
            <div class="p-4 space-y-4 text-xs">
              <div>
                <h3 class="font-bold text-slate-900 text-sm">Hotel Botanic Belatuk</h3>
                <p class="text-slate-400 text-[11px] mt-0.5">Samarinda, Kalimantan Timur</p>
              </div>

              <div class="bg-[#f2f2f7] p-3 rounded-xl grid grid-cols-2 gap-2 text-slate-700">
                <div>
                  <span class="text-[10px] font-semibold text-slate-400 uppercase block">Check-in</span>
                  <strong class="font-bold text-slate-900 block mt-0.5">22 Jul 2026</strong>
                </div>
                <div class="border-l border-slate-300/60 pl-3">
                  <span class="text-[10px] font-semibold text-slate-400 uppercase block">Check-out</span>
                  <strong class="font-bold text-slate-900 block mt-0.5">23 Jul 2026</strong>
                </div>
              </div>

              <div class="space-y-1 pt-1 border-t border-black/5">
                <p class="font-bold text-slate-800">(1x) Superior Double Room</p>
                <p class="text-[11px] text-slate-400">2 Tamu • 1 Double Bed • Tanpa Sarapan</p>
              </div>

              <div class="bg-[#f2f2f7] p-3 rounded-xl space-y-0.5">
                <span class="text-[10px] font-semibold text-slate-400 uppercase block">Tamu</span>
                <p class="font-bold text-slate-800">muhammad dzakwan</p>
                <p class="text-[11px] text-slate-500">+62 858-2272-2058</p>
              </div>
            </div>

          </div>
        </div>

      </div>
    </main>

    <!-- ==================== iOS POP-UP ALERT MODAL ==================== -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 scale-90"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-90"
    >
      <div 
        v-if="isModalOpen" 
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-md"
      >
        <div class="bg-white/95 rounded-3xl p-6 max-w-sm w-full text-center shadow-2xl relative border border-white/20">
          
          <!-- Dynamic Status Icon -->
          <div 
            class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 shadow-inner"
            :class="{
              'bg-[#ffc000]/20 text-[#b88a00]': modalStatus === 'success',
              'bg-red-100 text-red-500': modalStatus === 'error',
              'bg-amber-100 text-amber-600': modalStatus === 'warning'
            }"
          >
            <svg v-if="modalStatus === 'success'" class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <svg v-else-if="modalStatus === 'error'" class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>

          <!-- Title & Message -->
          <h3 class="text-lg font-bold text-slate-900 mb-1.5 tracking-tight">
            {{ modalTitle }}
          </h3>
          <p class="text-xs text-slate-500 mb-6 leading-relaxed font-medium">
            {{ modalMessage }}
          </p>

          <!-- TOMBOL LANJUTKAN BERHASIL DIPERBAIKI (HURUF KECIL & PANGGIL closeModal) -->
          <button 
            type="button"
            @click="closeModal"
            class="w-full py-3.5 px-4 rounded-xl text-xs font-extrabold text-slate-950 bg-[#ffc000] hover:bg-[#ebd000] active:scale-[0.98] transition-all shadow-md shadow-[#ffc000]/20 cursor-pointer block text-center"
          >
            Lanjutkan
          </button>

        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const selectedCategory = ref('va')
const selectedBank = ref('bca')
const isLoading = ref(false)

// State Modal Pop-up Alert
const isModalOpen = ref(false)
const modalStatus = ref('success') 
const modalTitle = ref('')
const modalMessage = ref('')

const vaBanks = [
  { id: 'bca', name: 'BCA Virtual Account', code: 'BCA' },
  { id: 'mandiri', name: 'Mandiri Virtual Account', code: 'MANDIRI' },
  { id: 'bri', name: 'BRI Virtual Account', code: 'BRIVA' }
]

const selectCategory = (cat, defaultBank) => {
  selectedCategory.value = cat
  selectedBank.value = defaultBank
}

// Countdown Timer 30 Menit
const timeLeft = ref(30 * 60)
let timerInterval = null

const formattedTimer = computed(() => {
  const m = Math.floor(timeLeft.value / 60).toString().padStart(2, '0')
  const s = (timeLeft.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

onMounted(() => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) timeLeft.value--
  }, 1000)
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

// Fungsi Tampil Modal
const showModalAlert = (status, title, message) => {
  modalStatus.value = status
  modalTitle.value = title
  modalMessage.value = message
  isModalOpen.value = true
}

// FUNGSI UNTUK MENUTUP MODAL & DIARAHKAN KE KONFIRMASI PEMBAYARAN
const closeModal = () => {
  isModalOpen.value = false
  
  // Mengarahkan ke URL /konfirmasi-pembayaran
  router.visit('/konfirmasi-pembayaran')
}

const handlePayment = () => {
  isLoading.value = true
  setTimeout(() => {
    isLoading.value = false
    
    // Tampilkan Modal Pop-up
    showModalAlert(
      'success',
      'Pesanan Berhasil dibuat!',
      'Klik Lanjutkan untuk masuk ke halaman konfirmasi pembayaran.'
    )
  }, 1000)
}
</script>