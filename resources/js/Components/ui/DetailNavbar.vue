<script setup>
import { ArrowLeft, Upload, Heart, X, Link as LinkIcon, Share2 } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import BottomSheet from './BottomSheet.vue';
import Toast from './Toast.vue';

const props = defineProps({
    backUrl: {
        type: String,
        default: '/'
    },
    sections: {
        type: Array,
        default: () => [
            { id: 'foto', label: 'Foto Aset' },
            { id: 'informasi', label: 'Informasi Umum' },
            { id: 'fasilitas', label: 'Fasilitas Aset' },
            { id: 'lokasi', label: 'Lokasi' },
            { id: 'kebijakan', label: 'Kebijakan' },
            { id: 'ulasan', label: 'Ulasan' },
            { id: 'pemilik', label: 'Pemilik Aset' },
        ]
    },
    isFavorited: {
        type: Boolean,
        default: false
    },
    showSections: {
        type: Boolean,
        default: true
    },
    showShare: {
        type: Boolean,
        default: true
    },
    showFavorite: {
        type: Boolean,
        default: true
    },
    forceBackUrl: {
        type: Boolean,
        default: false
    },
    showBackButton: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    mobileBackOnly: {
        type: Boolean,
        default: false
    }
});

defineEmits(['favorite']);

const goBack = () => {
    if (props.forceBackUrl) {
        router.visit(props.backUrl);
        return;
    }

    if (window.history.length > 2) {
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

// Scroll Spy logic for active section
const activeSection = ref('');

const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) {
        // Offset for sticky headers: Navbar (~64px) + StickySubNavSearch (~60px) + some padding
        const yOffset = -140;
        const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
        window.scrollTo({ top: y, behavior: 'smooth' });
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    supportsNativeShare.value = !!navigator.share;

    // Intersection Observer for scroll spy
    if (props.showSections) {
        const observerOptions = {
            root: null,
            rootMargin: '-80px 0px -60% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    activeSection.value = entry.target.id;
                }
            });
        }, observerOptions);

        setTimeout(() => {
            props.sections.forEach(section => {
                const el = document.getElementById(section.id);
                if (el) observer.observe(el);
            });
        }, 100);

        onUnmounted(() => observer.disconnect());
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    if (toastTimer) clearTimeout(toastTimer);
});
</script>

<template>
    <nav class="sticky top-0 z-[60] bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-12 md:h-16 flex items-center justify-between w-full">
            <slot name="content">
                <div class="flex items-center gap-8 h-full">
                    <!-- Tombol Kembali -->
                    <button v-if="showBackButton || mobileBackOnly"
                            @click="goBack"
                            class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors"
                            :class="mobileBackOnly ? 'md:hidden' : ''">
                        <ArrowLeft class="text-[#0A2540]" />
                    </button>

                    <h1 v-if="title" class="text-lg font-bold text-[#0A2540]">{{ title }}</h1>

                    <!-- Desktop Scroll Menu -->
                    <div v-if="showSections" class="hidden md:flex gap-8 transition-all duration-300 h-full">
                        <a v-for="section in sections" :key="section.id" :href="`#${section.id}`"
                           @click.prevent="scrollToSection(section.id)"
                           class="text-[15px] transition-all duration-200 h-full flex items-center border-b-[4px]"
                           :class="activeSection === section.id ? 'font-bold text-[#d4a000] border-[#FFC000]' : 'font-medium text-gray-600 hover:text-[#0A2540] border-transparent'">
                            {{ section.label }}
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button v-if="showShare" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors" @click="showShareUI = true">
                        <Upload class="text-[#0A2540]" />
                    </button>
                    <button v-if="showFavorite" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors" @click="$emit('favorite')">
                        <Heart class="transition-colors" :class="isFavorited ? 'text-pink-500 fill-pink-500' : 'text-[#0A2540]'" />
                    </button>
                    <slot name="actions"></slot>
                </div>
            </slot>
        </div>
    </nav>

    <!-- Desktop Share Modal -->
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="showShareUI" class="fixed inset-0 z-[9999] bg-black/40 backdrop-blur-sm hidden md:flex items-center justify-center" @click.self="showShareUI = false">
                <div class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-sm" @click.stop>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-[#0A2540]">Bagikan Aset</h3>
                        <button @click="showShareUI = false" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center hover:bg-gray-100 text-gray-500 transition-colors">
                            <X class="" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-3">
                        <button @click="copyLink" class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition font-bold text-[#0A2540]">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-700"><LinkIcon class="" /></div>
                            Salin Tautan
                        </button>
                        <button v-if="supportsNativeShare" @click="nativeShare" class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition font-bold text-[#0A2540]">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"><Share2 class="" /></div>
                            Opsi Lainnya
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Mobile Share Bottom Sheet -->
    <BottomSheet v-model="showShareUI" title="Bagikan" heightClass="h-auto pb-10">
        <div class="px-5 pt-4 flex flex-col gap-3">
            <button @click="copyLink" class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition font-bold text-[#0A2540] active:scale-95">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-700"><LinkIcon class="" /></div>
                Salin Tautan
            </button>
            <button v-if="supportsNativeShare" @click="nativeShare" class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition font-bold text-[#0A2540] active:scale-95">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"><Share2 class="" /></div>
                Opsi Lainnya
            </button>
        </div>
    </BottomSheet>

    <!-- Copied Toast Notification -->
    <Toast :show="showCopiedToast" message="Tautan berhasil disalin!" />
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
</style>
