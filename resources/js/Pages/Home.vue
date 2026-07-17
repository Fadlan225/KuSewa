<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Hero from '@/Components/Hero.vue';
import SearchForm from '@/Components/SearchForm.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
</script>

<template>
  <GuestLayout>
    <Hero />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 mt-8">
      <section>
        <div class="text-center max-w-2xl mx-auto mb-16">
          <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Pilihan Terpopuler</h2>
          <div class="h-1 w-20 bg-[#FFC107] mx-auto mt-4 rounded-full"></div>
          <p class="text-gray-500 mt-4 text-base md:text-lg">Temukan hunian terfavorit dengan pelayanan terbaik, keamanan terjamin, dan harga yang bersahabat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Static example cards (converted from Blade) -->
          <div class="bg-white rounded-3xl overflow-hidden shadow-md border border-gray-100 group">
            <div class="relative overflow-hidden h-64">
              <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80" alt="Apartemen" class="w-full h-full object-cover">
              <span class="absolute top-4 left-4 bg-yellow-400 text-white font-bold text-xs uppercase px-3 py-1.5 rounded-full">Apartemen</span>
            </div>
            <div class="p-6">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-xl font-bold text-gray-900 truncate">Central Park Residence</h3>
                <div class="flex items-center text-yellow-500"><span class="text-sm font-bold text-gray-800 ml-1">4.8</span></div>
              </div>
              <p class="text-gray-500 text-sm mb-4">Jakarta Barat, DKI Jakarta</p>
              <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                <div class="text-lg font-black text-gray-900">Rp 4.500.000<span class="text-xs font-normal text-gray-500">/bln</span></div>
                <a href="#" class="text-sm font-bold text-yellow-500">Lihat Detail →</a>
              </div>
            </div>
          </div>

          <!-- two more sample cards -->
          <div class="bg-white rounded-3xl overflow-hidden shadow-md border border-gray-100 group">
            <div class="relative overflow-hidden h-64">
              <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80" alt="Rumah" class="w-full h-full object-cover">
              <span class="absolute top-4 left-4 bg-yellow-400 text-white font-bold text-xs uppercase px-3 py-1.5 rounded-full">Rumah</span>
            </div>
            <div class="p-6">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-xl font-bold text-gray-900 truncate">Seminyak Green Oasis</h3>
                <div class="flex items-center text-yellow-500"><span class="text-sm font-bold text-gray-800 ml-1">4.9</span></div>
              </div>
              <p class="text-gray-500 text-sm mb-4">Badung, Bali</p>
              <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                <div class="text-lg font-black text-gray-900">Rp 12.000.000<span class="text-xs font-normal text-gray-500">/bln</span></div>
                <a href="#" class="text-sm font-bold text-yellow-500">Lihat Detail →</a>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-3xl overflow-hidden shadow-md border border-gray-100 group">
            <div class="relative overflow-hidden h-64">
              <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80" alt="Villa" class="w-full h-full object-cover">
              <span class="absolute top-4 left-4 bg-yellow-400 text-white font-bold text-xs uppercase px-3 py-1.5 rounded-full">Vila</span>
            </div>
            <div class="p-6">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-xl font-bold text-gray-900 truncate">Dago Hilltop Mansion</h3>
                <div class="flex items-center text-yellow-500"><span class="text-sm font-bold text-gray-800 ml-1">4.7</span></div>
              </div>
              <p class="text-gray-500 text-sm mb-4">Dago, Bandung</p>
              <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                <div class="text-lg font-black text-gray-900">Rp 18.000.000<span class="text-xs font-normal text-gray-500">/bln</span></div>
                <a href="#" class="text-sm font-bold text-yellow-500">Lihat Detail →</a>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-10 text-center">
          <a href="/properti" class="inline-flex items-center gap-2 rounded-xl border border-yellow-400 px-6 py-3 text-sm font-bold text-yellow-600">Lihat Selengkapnya →</a>
        </div>
      </section>

    </div>

    <SiteFooter />
  </GuestLayout>
</template>
