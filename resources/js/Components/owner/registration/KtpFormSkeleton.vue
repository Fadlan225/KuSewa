<script setup>
// KtpFormSkeleton.vue
// Ditampilkan HANYA saat ocrStatus === 'processing'
// Tidak ada artificial delay — state skeleton mengikuti state server secara real-time
</script>

<template>
  <div class="space-y-6">

    <!-- Skeleton Fields -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
      <!-- NIK - full width -->
      <div class="md:col-span-2 space-y-2">
        <div class="skeleton-label w-24"></div>
        <div class="skeleton-input w-full"></div>
      </div>

      <!-- Nama - full width -->
      <div class="md:col-span-2 space-y-2">
        <div class="skeleton-label w-28"></div>
        <div class="skeleton-input w-full"></div>
      </div>

      <!-- Tempat Lahir -->
      <div class="space-y-2">
        <div class="skeleton-label w-24"></div>
        <div class="skeleton-input w-full"></div>
      </div>

      <!-- Tanggal Lahir -->
      <div class="space-y-2">
        <div class="skeleton-label w-28"></div>
        <div class="skeleton-input w-full"></div>
      </div>

      <!-- Jenis Kelamin -->
      <div class="space-y-2">
        <div class="skeleton-label w-28"></div>
        <div class="skeleton-input w-3/4"></div>
      </div>

      <!-- Kewarganegaraan -->
      <div class="space-y-2">
        <div class="skeleton-label w-32"></div>
        <div class="skeleton-input w-1/2"></div>
      </div>

      <!-- Agama -->
      <div class="space-y-2">
        <div class="skeleton-label w-16"></div>
        <div class="skeleton-input w-2/3"></div>
      </div>

      <!-- Status Perkawinan -->
      <div class="space-y-2">
        <div class="skeleton-label w-36"></div>
        <div class="skeleton-input w-3/4"></div>
      </div>

      <!-- Pekerjaan -->
      <div class="space-y-2">
        <div class="skeleton-label w-20"></div>
        <div class="skeleton-input w-full"></div>
      </div>

      <!-- Separator -->
      <div class="md:col-span-2 pt-2 border-t border-slate-100">
        <div class="skeleton-label w-32 mb-4"></div>
      </div>

      <!-- Alamat -->
      <div class="md:col-span-2 space-y-2">
        <div class="skeleton-label w-20"></div>
        <div class="skeleton-input w-full h-20"></div>
      </div>

      <!-- Provinsi -->
      <div class="space-y-2">
        <div class="skeleton-label w-20"></div>
        <div class="skeleton-input w-full"></div>
      </div>

      <!-- Kota -->
      <div class="space-y-2">
        <div class="skeleton-label w-32"></div>
        <div class="skeleton-input w-full"></div>
      </div>

      <!-- Kecamatan -->
      <div class="space-y-2">
        <div class="skeleton-label w-24"></div>
        <div class="skeleton-input w-full"></div>
      </div>

      <!-- Kelurahan -->
      <div class="space-y-2">
        <div class="skeleton-label w-32"></div>
        <div class="skeleton-input w-full"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.skeleton-label {
  height: 12px;
  border-radius: 6px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-input {
  height: 48px;
  border-radius: 8px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
