<script setup>
import { ref } from 'vue';
</script>

<template>
  <form class="mb-8 flex flex-wrap items-center gap-2 rounded-full border border-slate-300 bg-white p-1.5 shadow-xs md:flex-nowrap">
    <div class="w-full px-4 border-r border-slate-200 py-1 md:w-1/4">
      <select class="w-full text-xs font-medium text-slate-700 bg-transparent outline-none cursor-pointer">
        <option>Jenis Properti</option>
        <option>Villa</option>
        <option>Homestay</option>
      </select>
    </div>
    <div class="w-full px-4 border-r border-slate-200 py-1 md:w-1/4">
      <select class="w-full text-xs font-medium text-slate-700 bg-transparent outline-none cursor-pointer">
        <option>Lokasi</option>
        <option>Samarinda</option>
      </select>
    </div>
    <div class="w-full px-4 border-r border-slate-200 py-1 md:w-1/4">
      <select class="w-full text-xs font-medium text-slate-700 bg-transparent outline-none cursor-pointer">
        <option>Jadwal</option>
      </select>
    </div>
    <div class="w-full px-4 py-1 md:w-1/4">
      <select class="w-full text-xs font-medium text-slate-700 bg-transparent outline-none cursor-pointer">
        <option>Harga</option>
      </select>
    </div>
    <button type="submit" class="flex h-10 w-16 shrink-0 items-center justify-center rounded-full bg-[#fec107] text-white transition hover:bg-amber-500">
      <svg class="h-4 w-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
      </svg>
    </button>
  </form>
</template>
