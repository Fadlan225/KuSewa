<template>
  <div class="min-h-screen bg-slate-50/60 text-slate-800 font-sans antialiased pb-16">
    <!-- TOP NAVIGATION BAR -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100 mb-8">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <span class="text-2xl font-black tracking-tight text-slate-900">
            kusewa<span class="text-[#ffc000]">.id</span>
          </span>
          <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>
          <div class="hidden sm:block">
            <h1 class="text-base font-bold text-slate-900 leading-tight">Hotel Botanic Belatuk</h1>
            <p class="text-xs text-slate-500 font-medium">
              <span class="text-blue-600 font-bold">5.2/10</span> • 420 Ulasan
            </p>
          </div>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center gap-2 text-xs font-semibold">
          <span class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-full flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Review
          </span>
          <span class="text-slate-300">/</span>
          <span class="px-3.5 py-1.5 bg-[#ffc000] text-slate-950 font-bold rounded-full shadow-sm shadow-[#ffc000]/30">
            2. Pembayaran
          </span>
        </div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- MAIN CONTENT (KOLOM KIRI - 7 COLS) -->
        <div class="lg:col-span-7 space-y-6">
          
          <!-- Google Auth Info Card -->
          <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-xs text-slate-600">
                G
              </div>
              <div>
                <p class="text-xs text-slate-400 font-medium">Terhubung sebagai</p>
                <p class="text-sm font-semibold text-slate-800">24_muhammaddzakwan dzakwan</p>
              </div>
            </div>
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Aktif</span>
          </div>

          <!-- Section: Data Pemesan -->
          <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
            <div class="border-b border-slate-100 pb-4">
              <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="w-2 h-5 bg-[#ffc000] rounded-full inline-block"></span>
                Data Pemesan
              </h2>
              <p class="text-xs text-slate-400 mt-1">Pastikan data sesuai dengan kartu identitas resmi Anda.</p>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap*</label>
                <input type="text" v-model="form.namaPemesan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#ffc000] focus:ring-4 focus:ring-[#ffc000]/20 transition-all text-sm outline-none bg-slate-50/50 focus:bg-white" />
                <span class="text-[11px] text-slate-400 mt-1 block">Gunakan nama yang tertera di KTP/Paspor.</span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. Handphone*</label>
                  <div class="flex">
                    <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-200 rounded-l-xl text-xs font-semibold text-slate-600">+62</span>
                    <input type="text" v-model="form.phone" class="w-full px-3.5 py-2.5 rounded-r-xl border border-slate-200 focus:border-[#ffc000] focus:ring-4 focus:ring-[#ffc000]/20 transition-all text-sm outline-none bg-slate-50/50 focus:bg-white" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email*</label>
                  <input type="email" v-model="form.email" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#ffc000] focus:ring-4 focus:ring-[#ffc000]/20 transition-all text-sm outline-none bg-slate-50/50 focus:bg-white" />
                </div>
              </div>

              <label class="flex items-center gap-2.5 pt-2 cursor-pointer select-none">
                <input type="checkbox" v-model="form.untukSaya" class="w-4 h-4 rounded text-[#ffc000] focus:ring-[#ffc000] border-slate-300" />
                <span class="text-xs font-medium text-slate-700">Saya menginap untuk diri sendiri</span>
              </label>
            </div>
          </section>

          <!-- Section: Informasi Tamu -->
          <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
              <span class="w-2 h-5 bg-slate-800 rounded-full inline-block"></span>
              Nama Tamu Menginap
            </h2>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap Tamu*</label>
              <input type="text" v-model="form.namaTamu" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#ffc000] focus:ring-4 focus:ring-[#ffc000]/20 transition-all text-sm outline-none bg-slate-50/50 focus:bg-white" />
            </div>
          </section>

          <!-- Section: Permintaan Khusus -->
          <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <div>
              <h2 class="text-base font-bold text-slate-900">Permintaan Khusus</h2>
              <p class="text-xs text-slate-400 mt-0.5">Terlepas dari konfirmasi, pemenuhan permintaan tergantung ketersediaan saat check-in.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <label v-for="req in specialRequests" :key="req.id" 
                     :class="[form.requests.includes(req.id) ? 'border-[#ffc000] bg-[#ffc000]/5 ring-1 ring-[#ffc000]' : 'border-slate-200 hover:bg-slate-50']"
                     class="flex items-center gap-2.5 p-3.5 border rounded-xl cursor-pointer transition-all">
                <input type="checkbox" :value="req.id" v-model="form.requests" class="rounded text-[#ffc000] focus:ring-[#ffc000] border-slate-300" />
                <span class="text-xs font-medium text-slate-700 leading-snug">{{ req.label }}</span>
              </label>
            </div>
          </section>

          <!-- Section: Fasilitas Utama -->
          <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-slate-900">Fasilitas Utama</h2>
            <div class="divide-y divide-slate-100">
              <div v-for="facility in facilities" :key="facility.name" class="py-3 flex items-center justify-between text-sm">
                <div class="flex items-center gap-3">
                  <span class="text-base">{{ facility.icon }}</span>
                  <span class="text-xs font-medium text-slate-700">{{ facility.name }}</span>
                </div>
                <span class="text-xs font-bold text-slate-800 bg-slate-100 px-2 py-1 rounded-lg">★ {{ facility.rating }}</span>
              </div>
            </div>
            <button type="button" class="text-xs font-bold text-[#b38600] hover:text-amber-700 transition-colors pt-2 cursor-pointer">
              Lihat Semua Fasilitas →
            </button>
          </section>

          <!-- Section: Asuransi Hotel Card -->
          <section class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-xl space-y-4 relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-[#ffc000]/10 rounded-full blur-2xl"></div>
            
            <div class="flex items-start justify-between">
              <div>
                <span class="bg-[#ffc000] text-slate-950 text-[10px] font-black px-2 py-0.5 rounded tracking-wide uppercase">Rekomendasi</span>
                <h3 class="text-base font-bold text-white mt-1.5">Perlindungan Ekstra Menginap</h3>
              </div>
              <span class="text-lg font-black text-[#ffc000]">Rp 13.000</span>
            </div>

            <label class="flex items-start gap-3 bg-white/5 p-3.5 rounded-xl border border-white/10 cursor-pointer hover:bg-white/10 transition-colors">
              <input type="checkbox" v-model="form.asuransi" class="mt-0.5 rounded text-[#ffc000] focus:ring-[#ffc000] bg-slate-800 border-slate-700" />
              <div class="text-xs space-y-1 text-slate-300">
                <p class="font-semibold text-white">Proteksi Pembatalan & Kehilangan</p>
                <p class="text-[11px] text-slate-400">Menutup hingga Rp 2.000.000 jika terjadi insiden tidak terduga atau pembatalan sepihak.</p>
              </div>
            </label>
          </section>

        </div>

        <!-- SIDEBAR (KOLOM KANAN - 5 COLS) -->
        <div class="lg:col-span-5 space-y-6 sticky top-24">
          
          <!-- Order Summary Card -->
          <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xl shadow-slate-200/50 space-y-5">
            
            <!-- Room Badge -->
            <div class="bg-amber-50 border border-[#ffc000]/30 rounded-xl p-3.5 flex items-center justify-between">
              <div>
                <p class="text-xs font-bold text-amber-900">Sisa 3 Kamar Terakhir!</p>
                <p class="text-[11px] text-amber-700">Dapatkan tarif terbaik sebelum habis.</p>
              </div>
              <span class="text-xl">🔥</span>
            </div>

            <!-- Room Info -->
            <div class="border-b border-slate-100 pb-4">
              <h3 class="font-bold text-slate-900 text-base">(1x) Superior Double Room</h3>
              <p class="text-xs text-slate-500 mt-1">2 Tamu • 1 Double Bed • Tanpa Sarapan</p>
            </div>

            <!-- Stay Schedule Grid -->
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-100 text-xs">
              <div>
                <span class="text-slate-400 block font-medium">Check-in</span>
                <strong class="text-slate-800 font-bold block mt-0.5">Rabu, 22 Jul 2026</strong>
                <span class="text-[10px] text-slate-400">Dari 14:00</span>
              </div>
              <div class="border-l border-slate-200 pl-3">
                <span class="text-slate-400 block font-medium">Check-out</span>
                <strong class="text-slate-800 font-bold block mt-0.5">Kamis, 23 Jul 2026</strong>
                <span class="text-[10px] text-slate-400">Sebelum 12:00</span>
              </div>
            </div>

            <!-- Price Breakdown -->
            <div class="space-y-2.5 text-xs pt-1">
              <div class="flex justify-between text-slate-600">
                <span>Harga Kamar (1 malam)</span>
                <span class="font-semibold text-slate-800">Rp 224.221</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Pajak & Biaya Pelayanan</span>
                <span class="font-semibold text-slate-800">Rp 71.221</span>
              </div>
              
              <div class="border-t border-slate-100 pt-3 flex justify-between items-baseline">
                <div>
                  <span class="font-bold text-slate-900 text-sm block">Total Pembayaran</span>
                  <span class="text-[10px] text-slate-400">Termasuk seluruh pajak</span>
                </div>
                <span class="text-2xl font-black text-slate-900 tracking-tight">
                  Rp 396.002
                </span>
              </div>
            </div>

            <!-- Action Button (Menggunakan Inertia Link) -->
            <Link 
              href="/payment-page" 
              :data="{ booking_id: '1377346270' }"
              class="block text-center w-full bg-[#ffc000] hover:bg-[#ebd000] active:scale-[0.98] text-slate-950 font-extrabold py-3.5 rounded-xl transition-all shadow-lg shadow-[#ffc000]/25 text-sm cursor-pointer select-none">
              Lanjut ke Pembayaran
            </Link>

            <p class="text-[10px] text-slate-400 text-center leading-relaxed">
              Dengan melanjutkan, Anda menyetujui <a href="#" class="underline font-medium text-slate-600">Syarat & Ketentuan</a> pemesanan kusewa.id.
            </p>
          </div>

          <!-- Section: Ulasan Singkat -->
          <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="font-bold text-sm text-slate-800">Ulasan Pengunjung</h4>
              <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">★ 8.0/10</span>
            </div>

            <div class="space-y-2.5">
              <div v-for="(review, index) in reviews" :key="index" class="bg-slate-50 p-3 rounded-xl space-y-1">
                <div class="flex justify-between items-center text-[11px]">
                  <span class="font-bold text-slate-700">{{ review.user }}</span>
                  <span class="font-bold text-amber-600">★ {{ review.score }}</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-snug">{{ review.comment }}</p>
              </div>
            </div>
          </div>

        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { Link } from '@inertiajs/vue3' // Import Link dari Inertia.js

const form = reactive({
  namaPemesan: '2k_muhammaddzakwan dzakwan',
  phone: '8123456789',
  email: '2k_muhammaddzakwan@gmail.com',
  untukSaya: true,
  namaTamu: '2k_muhammaddzakwan dzakwan',
  requests: ['smokefree'],
  asuransi: true
})

const specialRequests = [
  { id: 'smokefree', label: 'Bebas Asap Rokok' },
  { id: 'connecting', label: 'Pintu Penghubung' },
  { id: 'highfloor', label: 'Lantai Atas' }
]

const facilities = [
  { name: '4 Kamar Tidur (1 Single & 2 Double)', icon: '🛏️', rating: '9.2' },
  { name: '2 Kamar Mandi dengan Bathtub', icon: '🛁', rating: '8.5' },
  { name: 'Kolam Renang Outdoor', icon: '🏊', rating: '7.9' },
  { name: 'Pemandangan Sungai Mahakam', icon: '🏞️', rating: '8.2' }
]

const reviews = [
  { user: 'Pengguna kusewa.id', score: '10/10', comment: 'Sangat direkomendasikan! Tempatnya bersih dan fasilitas lengkap.' },
  { user: 'Pengguna kusewa.id', score: '9/10', comment: 'Kamar bersih dan toilet rapi. Pelayanan staf sangat ramah.' }
]
</script>