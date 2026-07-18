<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    transparent: {
        type: Boolean,
        default: false
    }
});

const page = usePage();

const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const isCurrentlyTransparent = computed(() => {
    return props.transparent && !isScrolled.value;
});
</script>

<template>
    <nav
        :class="[
            'fixed top-0 left-0 w-full z-50 transition-all duration-300',
            isCurrentlyTransparent
                ? 'bg-transparent shadow-none border-transparent'
                : 'bg-white shadow-sm border-b border-[#6C757D]/10'
        ]"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                <Link :href="route('Home')" class="flex items-center gap-2">
                    <img
                        src="/kusewa-logo.png"
                        alt="logo"
                        :class="[
                            'h-8 w-auto object-contain transition-all duration-300',
                            isCurrentlyTransparent ? 'brightness-0 invert' : 'brightness-100 invert-0'
                        ]"
                    />

                    <span
                        :class="[
                            'font-bold text-lg transition-colors duration-300',
                            isCurrentlyTransparent ? 'text-white' : 'text-[#0A2540]'
                        ]"
                    >
                        kusewa<span class="text-[#FFC000]">.id</span>
                    </span>
                </Link>

                <div class="hidden md:flex items-center space-x-7">
                    <Link
                        href="#"
                        :class="[
                            'text-sm font-semibold transition-colors duration-300',
                            isCurrentlyTransparent
                                ? 'text-white hover:text-[#FFC000]'
                                : 'text-[#0A2540] hover:text-[#FFC000]'
                        ]"
                    >
                        Jelajahi
                    </Link>

                    <Link
                        v-if="page.props.auth.user"
                        href="#"
                        :class="[
                            'text-sm font-semibold transition-colors duration-300',
                            isCurrentlyTransparent
                                ? 'text-white hover:text-[#FFC000]'
                                : 'text-[#0A2540] hover:text-[#FFC000]'
                        ]"
                    >
                        Favorit
                    </Link>

                    <Link
                        href="#"
                        :class="[
                            'text-sm font-semibold transition-colors duration-300',
                            isCurrentlyTransparent
                                ? 'text-white hover:text-[#FFC000]'
                                : 'text-[#0A2540] hover:text-[#FFC000]'
                        ]"
                    >
                        Bantuan
                    </Link>

                    <Link
                        href="#"
                        :class="[
                            'text-sm font-semibold transition-colors duration-300',
                            isCurrentlyTransparent
                                ? 'text-white hover:text-[#FFC000]'
                                : 'text-[#0A2540] hover:text-[#FFC000]'
                        ]"
                    >
                        Mulai Sewakan Aset
                    </Link>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    <!-- Language Selector Desktop -->
                    <div
                        class="flex items-center gap-2 cursor-pointer transition-all duration-300 px-3 py-1.5 rounded-lg border border-transparent"
                        :class="[
                            isCurrentlyTransparent
                                ? 'text-white hover:bg-white/10'
                                : 'text-[#0A2540] hover:bg-gray-100'
                        ]"
                    >
                        <img
                            src="https://flagcdn.com/id.svg"
                            alt="Indonesia Flag"
                            class="w-5 h-5 rounded-full object-cover border border-white/20"
                        />
                        <span class="font-semibold text-xs">ID</span>
                        <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i>
                    </div>

                        <Link
                            :href="route('login')"
                            :class="[
                                'relative px-6 py-2 rounded-full font-semibold transition-all duration-300 active:scale-95',
                                isCurrentlyTransparent
                                    ? 'bg-[#FFC000]/10 backdrop-blur-md border border-[#FFC000]/30 text-white shadow-lg hover:bg-[#FFC000] hover:border-[#FFC000] hover:text-[#0A2540]'
                                    : 'bg-[#FFC000] border border-[#FFC000] text-[#0A2540] shadow-md hover:opacity-90'
                            ]"
                        >
                            Login
                        </Link>
                    </div>

                <!-- Mobile Language Selector -->
                <div class="flex md:hidden items-center gap-2">
                    <div
                        class="flex items-center gap-1.5 cursor-pointer transition px-2 py-1 rounded-lg"
                        :class="[
                            isCurrentlyTransparent
                                ? 'text-white hover:bg-white/10'
                                : 'text-[#0A2540] hover:bg-gray-100'
                        ]"
                    >
                        <img
                            src="https://flagcdn.com/id.svg"
                            alt="Indonesia Flag"
                            class="w-4 h-4 rounded-full object-cover border border-white/20"
                        />
                        <span class="font-semibold text-xs">ID</span>
                        <i class="fa-solid fa-chevron-down text-[8px] ml-0.5"></i>
                    </div>
                </div>

            </div>
        </div>
    </nav>
</template>
