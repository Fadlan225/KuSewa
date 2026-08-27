<script setup>
import { AlertTriangle, RefreshCw, FileEdit } from 'lucide-vue-next';

defineProps({
  message: {
    type: String,
    default: 'KTP belum dapat dibaca',
  },
});

const emit = defineEmits(['retry', 'manual-entry']);
</script>

<template>
  <div class="flex flex-col items-center text-center gap-6 py-8 px-4">
    <!-- Icon -->
    <div class="w-16 h-16 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center">
      <AlertTriangle class="w-8 h-8 text-amber-500" />
    </div>

    <!-- Title -->
    <div>
      <h3 class="text-lg font-bold text-[#0A2540] mb-2">{{ message }}</h3>
      <p class="text-sm text-slate-500 max-w-xs mx-auto">
        Pastikan foto KTP Anda memenuhi syarat berikut:
      </p>
    </div>

    <!-- Tips -->
    <ul class="text-sm text-slate-600 space-y-2 text-left w-full max-w-xs">
      <li class="flex items-start gap-2">
        <span class="text-amber-500 mt-0.5 shrink-0">•</span>
        <span>Seluruh bagian KTP terlihat, tidak terpotong</span>
      </li>
      <li class="flex items-start gap-2">
        <span class="text-amber-500 mt-0.5 shrink-0">•</span>
        <span>Foto tidak buram atau goyang</span>
      </li>
      <li class="flex items-start gap-2">
        <span class="text-amber-500 mt-0.5 shrink-0">•</span>
        <span>Pencahayaan cukup, tidak terlalu gelap</span>
      </li>
      <li class="flex items-start gap-2">
        <span class="text-amber-500 mt-0.5 shrink-0">•</span>
        <span>Tidak ada pantulan cahaya (glare) di permukaan KTP</span>
      </li>
    </ul>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-3 w-full max-w-xs">
      <button
        type="button"
        @click="emit('retry')"
        class="flex-1 h-11 flex items-center justify-center gap-2 rounded-lg bg-[#FFC000] text-[#0A2540] font-bold text-sm hover:brightness-95 transition-all shadow-sm"
      >
        <RefreshCw class="w-4 h-4" />
        Coba Lagi
      </button>
      <button
        type="button"
        @click="emit('manual-entry')"
        class="flex-1 h-11 flex items-center justify-center gap-2 rounded-lg border border-slate-300 text-[#0A2540] font-semibold text-sm hover:bg-slate-50 transition-colors bg-white"
      >
        <FileEdit class="w-4 h-4" />
        Isi Manual
      </button>
    </div>
  </div>
</template>
