<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import BottomSheet from './BottomSheet.vue';

const props = defineProps({
    backUrl: {
        type: String,
        default: '/'
    },
    sections: {
        type: Array,
        default: () => [
            { id: 'foto', label: 'Foto' },
            { id: 'fasilitas', label: 'Fasilitas' },
            { id: 'lokasi', label: 'Lokasi' },
            { id: 'ulasan', label: 'Ulasan' },
        ]
    },
    isFavorited: {
        type: Boolean,
        default: false
    }
});

defineEmits(['favorite']);

const goBack = () => {
    if (window.history.length > 2) { // length 1 means new tab, length 2 usually means initial page load on some browsers. Better to just check > 1, or use router.visit as fallback
        window.history.back();
    } else {
        router.visit(props.backUrl);
    }
};

const showDesktopNavMenu = ref(false);
const showShareUI = ref(false);
const showCopiedToast = ref(false);
const supportsNativeShare = ref(false);

let toastTimer = null;

const handleScroll = () => {
    showDesktopNavMenu.value = window.scrollY > 400;
};

const shareUrl = computed(() => typeof window !== 'undefined' ? window.location.href : '');
const shareTitle = computed(() => typeof document !== 'undefined' ? document.title : '');

const showToast = () => {
    if (toastTimer) clearTimeout(toastTimer);
    showCopiedToast.value = true;
    toastTimer = setTimeout(() => {
        showCopiedToast.value = false;
    }, 2500);
};

// Fallback for mobile / non-HTTPS where navigator.clipboard may be blocked
const fallbackCopy = (text) => {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.top = '-9999px';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
        document.execCommand('copy');
    } catch (e) {
        console.error('Fallback clipboard gagal:', e);
    }
    document.body.removeChild(textarea);
};

const copyLink = async () => {
    showShareUI.value = false;
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(shareUrl.value);
        } else {
            fallbackCopy(shareUrl.value);
        }
    } catch (e) {
        // If clipboard API fails, use fallback
        fallbackCopy(shareUrl.value);
    }
    showToast();
};

const nativeShare = async () => {
    showShareUI.value = false;
    if (navigator.share) {
        try {
            await navigator.share({
                title: shareTitle.value,
                url: shareUrl.value
            });
        } catch (e) {
            // User cancelled, or error — silently ignore
        }
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    supportsNativeShare.value = !!navigator.share;
});
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    if (toastTimer) clearTimeout(toastTimer);
});
</script>

<template>
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <button @click="goBack" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-arrow-left text-[#0A2540]"></i>
            </button>

            <!-- Desktop Scroll Menu -->
            <div class="hidden md:flex gap-6 transition-all duration-300" :class="showDesktopNavMenu ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2 pointer-events-none'">
                <a v-for="section in sections" :key="section.id" :href="`#${section.id}`" class="text-sm font-bold text-gray-500 hover:text-[#0A2540] transition">
                    {{ section.label }}
                </a>
            </div>

            <div class="flex items-center gap-2">
                <button class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors" @click="showShareUI = true">
                    <i class="fa-solid fa-arrow-up-from-bracket text-[#0A2540]"></i>
                </button>
                <button class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors" @click="$emit('favorite')">
                    <i class="fa-heart" :class="isFavorited ? 'fa-solid text-red-500' : 'fa-regular text-[#0A2540]'"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Desktop Share Modal -->
    <Transition name="fade">
        <div v-if="showShareUI" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden md:flex items-center justify-center" @click.self="showShareUI = false">
            <div class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-sm" @click.stop>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-[#0A2540]">Bagikan Aset</h3>
                    <button @click="showShareUI = false" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center hover:bg-gray-100 text-gray-500 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="flex flex-col gap-3">
                    <button @click="copyLink" class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition font-bold text-[#0A2540]">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-700"><i class="fa-solid fa-link"></i></div>
                        Salin Tautan
                    </button>
                    <button v-if="supportsNativeShare" @click="nativeShare" class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition font-bold text-[#0A2540]">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-share-nodes"></i></div>
                        Opsi Lainnya
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Mobile Share Bottom Sheet -->
    <BottomSheet v-model="showShareUI" title="Bagikan" heightClass="h-auto pb-10">
        <div class="px-5 pt-4 flex flex-col gap-3">
            <button @click="copyLink" class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition font-bold text-[#0A2540] active:scale-95">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-700"><i class="fa-solid fa-link"></i></div>
                Salin Tautan
            </button>
            <button v-if="supportsNativeShare" @click="nativeShare" class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition font-bold text-[#0A2540] active:scale-95">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-share-nodes"></i></div>
                Opsi Lainnya
            </button>
        </div>
    </BottomSheet>

    <!-- Copied Toast Notification -->
    <Transition name="toast">
        <div v-if="showCopiedToast" class="fixed bottom-24 md:bottom-8 left-1/2 -translate-x-1/2 z-[200] flex items-center gap-3 bg-[#0A2540] text-white text-sm font-semibold px-5 py-3 rounded-2xl shadow-xl pointer-events-none whitespace-nowrap">
            <div class="w-6 h-6 rounded-full bg-green-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-check text-white text-[10px]"></i>
            </div>
            Tautan berhasil disalin!
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Toast pop-up from below */
.toast-enter-active,
.toast-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(-50%) translateY(12px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(12px);
}
</style>
