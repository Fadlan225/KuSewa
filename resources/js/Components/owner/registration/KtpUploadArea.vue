<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import { useIntervalFn } from '@vueuse/core';
import DetailBottomBar from '@/Components/ui/DetailBottomBar.vue';
import KtpFormSkeleton from '@/Components/owner/registration/KtpFormSkeleton.vue';
import CameraOpenIcon from '@/Components/ui/Icons/CameraOpenIcon.vue';
import { Camera, Image as ImageIcon, Sparkles, RefreshCw } from 'lucide-vue-next';

const emit = defineEmits(['ocr-success', 'ocr-failed', 'manual-entry', 'status-change']);

// ============================================================
// State
// ============================================================
const activeTab    = ref('scan');   // default ke 'scan' agar langsung buka kamera
const ocrStatus    = ref('idle');     // idle | uploading | processing | failed
const currentJobId = ref(null);
const errorMessage = ref('');
const ktpPreview   = ref(null);
const uploadError  = ref('');

// Helper: set ocrStatus dan emit status-change ke parent
const setStatus = (status) => {
  ocrStatus.value = status;
  emit('status-change', status);
};

// Kamera
const cameraStream     = ref(null);
const videoEl          = ref(null);
const capturedImageBlob = ref(null);
const cameraError      = ref('');
const isCapturing      = ref(false);
const cameraStarted    = ref(false);
const browserSupportsCamera = ref(true);

// File upload
const fileInputRef = ref(null);
const selectedFile = ref(null);

// ============================================================
// Validasi file (client-side)
// ============================================================
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const ALLOWED_MIMES = ['image/jpeg', 'image/jpg', 'image/png'];

const validateFile = (file) => {
  if (!ALLOWED_MIMES.includes(file.type)) {
    return 'Format file harus JPG, JPEG, atau PNG.';
  }
  if (file.size > MAX_FILE_SIZE) {
    return 'Ukuran file tidak boleh lebih dari 5MB.';
  }
  return null;
};

// ============================================================
// File Upload Handler
// ============================================================
const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  uploadError.value = '';
  const error = validateFile(file);
  if (error) {
    uploadError.value = error;
    return;
  }

  selectedFile.value = file;
  ktpPreview.value   = URL.createObjectURL(file);
  activeTab.value    = 'upload'; // Alihkan ke upload agar diproses dari file
  stopCamera();
};

const triggerFileInput = () => {
  fileInputRef.value?.click();
};

const resetFile = () => {
  selectedFile.value = null;
  ktpPreview.value   = null;
  uploadError.value  = '';
  if (fileInputRef.value) fileInputRef.value.value = '';
};

// ============================================================
// Camera Handler
// ============================================================
const startCamera = async () => {
  cameraError.value = '';
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
    });
    cameraStream.value = stream;
    cameraStarted.value = true;
    await nextTick(); // Tunggu Vue me-render elemen <video> ke DOM
    if (videoEl.value) {
      videoEl.value.srcObject = stream;
    }
  } catch (e) {
    browserSupportsCamera.value = false;
    cameraError.value = 'Kamera tidak tersedia. Gunakan tab Upload.';
    activeTab.value = 'upload';
  }
};

const stopCamera = () => {
  if (cameraStream.value) {
    cameraStream.value.getTracks().forEach(t => t.stop());
    cameraStream.value = null;
  }
  cameraStarted.value = false;
};

const capturePhoto = () => {
  if (!videoEl.value || !cameraStarted.value) return;

  const video = videoEl.value;
  const Vw = video.videoWidth;
  const Vh = video.videoHeight;

  if (Vw === 0 || Vh === 0) return;

  // Rasio aspek kontainer video di layar (4:3)
  const Rc = 4 / 3;
  const Rv = Vw / Vh;

  let sourceX = 0;
  let sourceY = 0;
  let sourceW = Vw;
  let sourceH = Vh;

  // Inset panduan e-KTP di layar (8% dari setiap sisi)
  const xFrac = 0.08;
  const yFrac = 0.08;
  const wFrac = 0.84;
  const hFrac = 0.84;

  if (Rv > Rc) {
    // Video source lebih lebar dibandingkan rasio kontainer (misal: 16:9 vs 4:3)
    const visibleWidthInSource = Vh * Rc;
    const croppedWidthInSource = (Vw - visibleWidthInSource) / 2;

    sourceX = croppedWidthInSource + (xFrac * visibleWidthInSource);
    sourceY = yFrac * Vh;
    sourceW = wFrac * visibleWidthInSource;
    sourceH = hFrac * Vh;
  } else {
    // Video source lebih tinggi dibandingkan kontainer
    const visibleHeightInSource = Vw / Rc;
    const croppedHeightInSource = (Vh - visibleHeightInSource) / 2;

    sourceX = xFrac * Vw;
    sourceY = croppedHeightInSource + (yFrac * visibleHeightInSource);
    sourceW = wFrac * Vw;
    sourceH = hFrac * visibleHeightInSource;
  }

  const canvas = document.createElement('canvas');
  canvas.width  = Math.round(sourceW);
  canvas.height = Math.round(sourceH);

  const ctx = canvas.getContext('2d');
  ctx.drawImage(
    video,
    Math.round(sourceX),
    Math.round(sourceY),
    Math.round(sourceW),
    Math.round(sourceH),
    0,
    0,
    Math.round(sourceW),
    Math.round(sourceH)
  );

  canvas.toBlob((blob) => {
    capturedImageBlob.value = blob;
    ktpPreview.value = URL.createObjectURL(blob);
    stopCamera();
    isCapturing.value = false;
  }, 'image/jpeg', 0.92);
};

const retakePhoto = () => {
  capturedImageBlob.value = null;
  selectedFile.value      = null;
  ktpPreview.value        = null;
  uploadError.value       = '';
  activeTab.value         = 'scan';
  if (fileInputRef.value) fileInputRef.value.value = '';
  startCamera();
};

const switchToScan = () => {
  activeTab.value = 'scan';
  if (!cameraStarted.value && !ktpPreview.value) {
    startCamera();
  }
};

const switchToUpload = () => {
  stopCamera();
  activeTab.value = 'upload';
};

// Auto start/stop camera on mount/unmount
onMounted(() => {
  activeTab.value = 'scan';
  startCamera();
});

onUnmounted(() => {
  stopCamera();
});

// ============================================================
// Upload ke server & dispatch OCR
// ============================================================
const uploadAndProcess = async () => {
  const file = activeTab.value === 'scan' ? capturedImageBlob.value : selectedFile.value;
  if (!file) return;

  setStatus('uploading');

  const formData = new FormData();
  const filename = activeTab.value === 'scan' ? 'ktp_scan.jpg' : selectedFile.value.name;
  formData.append('ktp_photo', file, filename);

  try {
    const res = await axios.post('/owner/register/ktp-upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    currentJobId.value = res.data.job_id;

    // Sync driver (dev): OCR sudah selesai, langsung emit hasil
    if (res.data.sync === true) {
      if (res.data.status === 'completed') {
        emit('ocr-success', {
          ...res.data.data,
          ktp_temp_path: res.data.ktp_photo_path,
        });
      } else {
        const errMsg = res.data.errors?.general ?? 'KTP tidak dapat dibaca.';
        errorMessage.value = errMsg;
        setStatus('failed');
        emit('ocr-failed', errMsg);
      }
      return; // skip polling
    }

    // Async driver (production): set status processing dan mulai polling
    setStatus('processing');
    startPolling();

  } catch (e) {
    let serverError = 'Upload gagal. Silakan coba lagi.';

    if (e.response) {
      const status = e.response.status;
      const data   = e.response.data;

      if (status === 422) {
        // Laravel validation error
        serverError = data?.errors?.ktp_photo?.[0]
          ?? data?.message
          ?? 'File tidak valid. Pastikan format JPG/PNG dan ukuran < 5MB.';
      } else if (status === 419) {
        // CSRF token expired — reload halaman
        serverError = 'Sesi Anda habis. Halaman akan dimuat ulang...';
        setTimeout(() => window.location.reload(), 2000);
      } else if (status === 413) {
        serverError = 'Ukuran file terlalu besar. Maksimal 5MB.';
      } else if (status === 500) {
        serverError = 'Terjadi kesalahan di server. Silakan coba lagi dalam beberapa saat.';
      } else if (status === 401 || status === 403) {
        serverError = 'Sesi login Anda telah berakhir. Silakan login ulang.';
      } else {
        serverError = data?.message ?? `Gagal upload (${status}). Silakan coba lagi.`;
      }
    }
    // e.response === undefined → genuine network error
    errorMessage.value = serverError;
    setStatus('failed');
    emit('ocr-failed', errorMessage.value);
  }
};

// ============================================================
// Polling OCR status (via @vueuse/core useIntervalFn)
// ============================================================
const MAX_POLL_ATTEMPTS = 40; // 40 × 2 detik = 80 detik max
let pollAttempts = 0;

const { pause: stopPolling, resume: startPollingFn } = useIntervalFn(async () => {
  if (!currentJobId.value) return;

  pollAttempts++;
  if (pollAttempts > MAX_POLL_ATTEMPTS) {
    stopPolling();
    errorMessage.value = 'Proses OCR timeout. Silakan coba lagi.';
    setStatus('failed');
    emit('ocr-failed', errorMessage.value);
    return;
  }

  try {
    const res = await axios.get(`/owner/register/ocr-status/${currentJobId.value}`);

    if (res.data.status === 'completed') {
      stopPolling();
      // Emit ke parent SEBELUM set status — parent menangani 'success'
      emit('ocr-success', {
        ...res.data.data,
        ktp_temp_path: res.data.ktp_photo_path,
      });
      // Tidak perlu setStatus('success') di sini — parent yang handle

    } else if (res.data.status === 'failed') {
      stopPolling();
      errorMessage.value = res.data.errors?.general ?? 'KTP tidak dapat dibaca.';
      setStatus('failed');
      emit('ocr-failed', errorMessage.value);
    }
    // status === 'processing' → lanjut polling

  } catch (e) {
    // Network error — lanjut polling beberapa kali dulu
    if (pollAttempts > 5) {
      stopPolling();
      errorMessage.value = 'Terjadi kesalahan koneksi.';
      setStatus('failed');
      emit('ocr-failed', errorMessage.value);
    }
  }
}, 2000, { immediate: false });

const startPolling = () => {
  pollAttempts = 0;
  startPollingFn();
};

// ============================================================
// Reset ke idle
// ============================================================
const reset = () => {
  setStatus('idle');
  currentJobId.value      = null;
  errorMessage.value      = '';
  ktpPreview.value        = null;
  capturedImageBlob.value = null;
  selectedFile.value      = null;
  uploadError.value       = '';
  pollAttempts            = 0;
  stopPolling();
  if (fileInputRef.value) fileInputRef.value.value = '';
};

const handleRetry = () => {
  reset();
};

// ============================================================
// Computed
// ============================================================
const hasPreview  = computed(() => !!ktpPreview.value);
const canProcess  = computed(() => hasPreview.value && ocrStatus.value === 'idle');
const isUploading = computed(() => ocrStatus.value === 'uploading');
const isProcessing = computed(() => ocrStatus.value === 'processing');
</script>

<template>
  <div>

    <!-- ============ IDLE: Scanner / Preview Area ============ -->
    <div v-if="ocrStatus === 'idle'" class="space-y-6">

      <!-- Hidden Native File Input -->
      <input
        ref="fileInputRef"
        type="file"
        accept="image/jpg,image/jpeg,image/png"
        class="hidden"
        @change="handleFileChange"
      />

      <!-- ================= CAMERA VIEW (Idle, no preview yet) ================= -->
      <div v-if="!ktpPreview" class="space-y-5">

        <!-- Live Video Element & Guideline Overlay -->
        <div v-if="cameraStarted" class="relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700/80 shadow-md aspect-[4/3] w-full max-w-md mx-auto">
          <video ref="videoEl" autoplay playsinline class="w-full h-full object-cover"/>

          <!-- Translucent scanner overlay backdrop -->
          <div class="absolute inset-0 bg-slate-950/40 pointer-events-none"></div>

          <!-- KTP High-Fidelity Guide Card Overlay (Outer frame and photo area only) -->
          <div class="absolute inset-[8%] pointer-events-none flex items-center justify-center drop-shadow-md">
            <svg viewBox="0 0 400 250" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
              <!-- Card Outer Outline (Yellow stroke, thin, rx=8 for square look) -->
              <rect x="5" y="5" width="390" height="240" rx="8" fill="none" stroke="#FFC000" stroke-width="1.8" />
              
              <!-- Photo Area Placeholder (Right side, thinner, rx=4) -->
              <rect x="270" y="36" width="105" height="142" rx="4" fill="none" stroke="#FFC000" stroke-width="1.2" />
            </svg>
          </div>

          <p class="absolute bottom-4 left-0 right-0 text-center text-xs text-white/90 font-semibold tracking-wide bg-slate-950/65 py-1 px-4 max-w-[280px] mx-auto rounded-full">
            Pastikan KTP masuk dalam bingkai
          </p>
        </div>

        <!-- Camera Loading or Error Fallback if permission/hardware fails -->
        <div v-else class="border border-slate-200 bg-slate-50/50 rounded-2xl p-8 flex flex-col items-center justify-center text-center min-h-[240px]">
          <CameraOpenIcon class="w-32 h-auto mb-4 drop-shadow-sm" />
          <p class="text-sm font-semibold text-slate-600 mb-1">Membuka Kamera...</p>
          <p class="text-xs text-slate-400 max-w-[240px]">Izinkan akses kamera jika diminta browser atau gunakan tombol Pilih File di bawah.</p>
        </div>

        <p v-if="cameraError" class="text-red-500 text-xs text-center font-medium">{{ cameraError }}</p>
        <p v-if="uploadError" class="text-red-500 text-xs text-center font-medium">{{ uploadError }}</p>

        <!-- Two Buttons: Ambil Foto & Pilih File (Desktop Only) -->
        <div class="hidden md:grid grid-cols-2 gap-3 w-full max-w-md mx-auto pt-2">
          <!-- Ambil Foto -->
          <button
            type="button"
            @click="capturePhoto"
            :disabled="!cameraStarted"
            class="h-12 rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-sm flex items-center justify-center gap-2 hover:brightness-95 transition-all shadow-md active:scale-[0.98] disabled:opacity-50"
          >
            <Camera class="w-5 h-5" />
            Ambil Foto
          </button>

          <!-- Pilih File -->
          <button
            type="button"
            @click="triggerFileInput"
            class="h-12 rounded-xl border-2 border-slate-200 bg-white text-slate-700 font-bold text-sm flex items-center justify-center gap-2 hover:bg-slate-50 transition-colors shadow-sm active:scale-[0.98]"
          >
            <ImageIcon class="w-5 h-5 text-slate-500" />
            Pilih File
          </button>
        </div>

        <!-- Mobile Bottom Bar Action (Camera screen: Ambil Foto & Pilih File) -->
        <DetailBottomBar class="md:hidden" :hideLeftContent="true">
          <template #left-content>
            <button
              type="button"
              @click="triggerFileInput"
              class="w-full h-11 px-4 rounded-xl border-2 border-slate-200 bg-white text-slate-700 font-bold text-[13px] shadow-sm transition-colors flex items-center justify-center gap-2"
            >
              <ImageIcon class="w-4 h-4 text-slate-500" />
              Pilih File
            </button>
          </template>
          
          <template #right-content>
            <button
              type="button"
              @click="capturePhoto"
              :disabled="!cameraStarted"
              class="w-full h-11 px-4 rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-[13px] shadow-sm transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <Camera class="w-4 h-4" />
              Ambil Foto
            </button>
          </template>
        </DetailBottomBar>

      </div>

      <!-- ================= PREVIEW VIEW (File selected or Photo taken) ================= -->
      <div v-else class="space-y-5 flex flex-col items-center">

        <div class="relative rounded-2xl overflow-hidden bg-slate-50 border border-slate-200/80 shadow-sm p-4 w-full max-w-md mx-auto flex justify-center">
          <img :src="ktpPreview" alt="Foto KTP Preview" class="max-h-56 object-contain rounded-xl shadow-sm border border-slate-100"/>
        </div>

        <p v-if="uploadError" class="text-red-500 text-xs text-center font-medium">{{ uploadError }}</p>

        <!-- Two Buttons: Baca KTP Otomatis & Foto Ulang/Ganti File (Desktop Only) -->
        <div class="hidden md:grid grid-cols-2 gap-3 w-full max-w-md mx-auto pt-2">
          <!-- Baca KTP Otomatis -->
          <button
            type="button"
            @click="uploadAndProcess"
            class="h-12 rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-sm flex items-center justify-center gap-2 hover:brightness-95 transition-all shadow-md active:scale-[0.98]"
          >
            <Sparkles class="w-4 h-4" />
            Konfirmasi
          </button>

          <!-- Ulangi / Retake -->
          <button
            type="button"
            @click="retakePhoto"
            class="h-12 rounded-xl border-2 border-slate-200 bg-white text-slate-700 font-bold text-sm flex items-center justify-center gap-2 hover:bg-slate-50 transition-colors shadow-sm active:scale-[0.98]"
          >
            <RefreshCw class="w-4 h-4 text-slate-500" />
            Ulangi
          </button>
        </div>

        <!-- Mobile Bottom Bar Action (Preview screen: Konfirmasi & Ulangi) -->
        <DetailBottomBar class="md:hidden" :hideLeftContent="true">
          <template #left-content>
            <button
              type="button"
              @click="retakePhoto"
              class="w-full h-11 px-4 rounded-xl border-2 border-slate-200 bg-white text-slate-700 font-bold text-[13px] shadow-sm transition-colors flex items-center justify-center gap-2"
            >
              <RefreshCw class="w-4 h-4 text-slate-500" />
              Ulangi
            </button>
          </template>
          
          <template #right-content>
            <button
              type="button"
              @click="uploadAndProcess"
              class="w-full h-11 px-4 rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-[13px] shadow-sm transition-colors flex items-center justify-center gap-2"
            >
              <Sparkles class="w-4 h-4" />
              Konfirmasi
            </button>
          </template>
        </DetailBottomBar>       </div>

      </div>

    <!-- ============ UPLOADING & PROCESSING (Skeleton Loader) ============ -->
    <div v-else-if="ocrStatus === 'uploading' || ocrStatus === 'processing'" class="bg-white rounded-xl p-6 md:p-8 border border-slate-200/80 shadow-sm animate-fade-in py-6">
      <KtpFormSkeleton />
    </div>

    <!-- ============ FAILED ============ -->
    <div v-else-if="ocrStatus === 'failed'">
      <slot name="error" :message="errorMessage" :retry="handleRetry" :manual="() => emit('manual-entry')">
        <div class="text-center py-8">
          <p class="text-sm text-slate-600 mb-4">{{ errorMessage }}</p>
          <div class="flex gap-3 justify-center">
            <button type="button" @click="handleRetry" class="px-4 py-2 rounded-lg bg-[#FFC000] text-[#0A2540] font-bold text-sm">Coba Lagi</button>
            <button type="button" @click="emit('manual-entry')" class="px-4 py-2 rounded-lg border border-slate-300 text-[#0A2540] font-semibold text-sm">Isi Manual</button>
          </div>
        </div>
      </slot>
    </div>

  </div>
</template>
