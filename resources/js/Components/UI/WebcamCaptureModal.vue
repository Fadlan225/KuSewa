<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-[70] bg-black/95 flex flex-col backdrop-blur-sm">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 pt-safe text-white bg-gradient-to-b from-black/60 to-transparent absolute top-0 left-0 right-0 z-10">
                <button @click="stopCamera" class="p-2 hover:bg-white/10 rounded-full transition-colors active:bg-white/20">
                    <X class="text-xl" />
                </button>
                <h3 class="font-medium text-[15px] drop-shadow-md">Kamera</h3>
                <div class="w-10"></div>
            </div>

            <!-- Video Stream -->
            <div class="flex-1 flex items-center justify-center p-4 w-full h-full relative overflow-hidden bg-black">
                <video 
                    v-show="!errorMessage"
                    ref="videoElement" 
                    autoplay 
                    playsinline 
                    class="w-full h-full object-contain transform scale-x-[-1]"
                ></video>

                <!-- Error Message -->
                <div v-if="errorMessage" class="text-center text-red-400 p-8 flex flex-col items-center gap-4">
                    <AlertTriangle class="text-5xl" />
                    <p class="font-medium">{{ errorMessage }}</p>
                </div>
            </div>

            <!-- Capture Button -->
            <div class="absolute bottom-0 left-0 right-0 p-8 pb-safe bg-gradient-to-t from-black/80 via-black/40 to-transparent flex justify-center z-10">
                <button 
                    v-if="!errorMessage"
                    @click="capturePhoto" 
                    class="w-16 h-16 rounded-full border-4 border-white flex items-center justify-center bg-white/20 hover:bg-white/40 active:scale-95 transition-all"
                >
                    <div class="w-12 h-12 rounded-full bg-white"></div>
                </button>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { X, AlertTriangle } from 'lucide-vue-next';
import { ref, watch, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'capture']);

const videoElement = ref(null);
const stream = ref(null);
const errorMessage = ref('');

const startCamera = async () => {
    errorMessage.value = '';
    
    // Periksa apakah browser mendukung mediaDevices (membutuhkan HTTPS atau localhost)
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        errorMessage.value = 'Browser Anda memblokir akses Kamera karena koneksi tidak aman (HTTPS diperlukan) atau fitur tidak didukung.';
        return;
    }

    try {
        stream.value = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'user' },
            audio: false 
        });
        await nextTick();
        if (videoElement.value) {
            videoElement.value.srcObject = stream.value;
        }
    } catch (err) {
        console.error('Error accessing webcam:', err);
        errorMessage.value = 'Gagal mengakses kamera. Pastikan Anda telah memberikan izin.';
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
    }
    emit('close');
};

const capturePhoto = () => {
    if (!videoElement.value) return;

    const canvas = document.createElement('canvas');
    canvas.width = videoElement.value.videoWidth;
    canvas.height = videoElement.value.videoHeight;
    const ctx = canvas.getContext('2d');
    
    // Flip horizontal to match mirrored video
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);

    canvas.toBlob((blob) => {
        if (blob) {
            const file = new File([blob], `capture_${Date.now()}.jpeg`, { type: 'image/jpeg' });
            emit('capture', file);
            stopCamera();
        }
    }, 'image/jpeg', 0.9);
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        startCamera();
    } else {
        stopCamera();
    }
});

onUnmounted(() => {
    stopCamera();
});
</script>
