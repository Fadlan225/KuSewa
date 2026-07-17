<script setup>
import { ref } from 'vue';
import SiteFooter from '@/Components/SiteFooter.vue';
</script>

<template>
  <div class="min-h-screen bg-white pb-20 pt-6 font-sans antialiased text-slate-800">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h1 class="text-base font-bold text-slate-900">Samarinda</h1>
          <p class="text-xs font-medium text-slate-500 mt-0.5">230 properti ditemukan</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[240px_1fr]">
        <aside class="space-y-6">
          <!-- Price range (simple placeholder) -->
          <div class="rounded-xl border border-slate-300 bg-white p-4 shadow-md">
            <h3 class="text-xs font-bold text-slate-900">Rentang harga</h3>
            <p class="text-[10px] text-slate-500 mb-3">Per malam</p>
            <div class="flex items-center gap-2 mb-4">
              <input type="text" value="IDR 0" class="h-7 w-full rounded-md border border-slate-300 bg-slate-50 px-2 text-[10px] text-center">
              <span class="text-slate-400 text-xs">-</span>
              <input type="text" value="IDR 16.000.000" class="h-7 w-full rounded-md border border-slate-300 bg-slate-50 px-2 text-[10px] text-center">
            </div>
            <div class="relative h-1 w-full bg-slate-200 rounded-full">
              <div class="absolute left-0 right-0 h-full bg-[#fec107] rounded-full"></div>
            </div>
          </div>
        </aside>

        <section class="min-w-0 space-y-6">
          <div class="space-y-6">
            <!-- property cards (simplified) -->
            <article class="grid overflow-hidden rounded-md border border-slate-200 bg-white shadow md:grid-cols-[200px_1fr_180px]">
              <div class="p-4">
                <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=900&q=85" class="w-full h-40 object-cover rounded">
              </div>
              <div class="p-4">
                <h2 class="text-sm font-bold text-slate-900 mb-1">Villa Pondok Jabuk</h2>
                <p class="text-[10px] font-medium text-slate-600 mb-3">Batuaa, Samarinda</p>
                <div class="text-lg font-black text-gray-900">Rp. 2.100.000</div>
              </div>
              <div class="p-4 flex items-center justify-center">
                <a href="#" class="text-sm font-bold text-yellow-500">Pilih</a>
              </div>
            </article>
          </div>
        </section>
      </div>
    </div>

    <SiteFooter />
  </div>
</template>
