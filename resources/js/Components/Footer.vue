<script setup>
import { computed } from 'vue';
import { Instagram, Mail, MessageCircle, Facebook, Twitter, Youtube, Linkedin, Github, Globe } from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const socialLinks = computed(() => Object.values(page.props.socialLinks || {}).filter(l => l.is_active));

const getIconComponent = (url) => {
    if (!url) return Globe;
    url = url.toLowerCase();
    if (url.includes('instagram.com')) return Instagram;
    if (url.includes('wa.me') || url.includes('whatsapp.com')) return MessageCircle;
    if (url.includes('facebook.com') || url.includes('fb.com')) return Facebook;
    if (url.includes('twitter.com') || url.includes('x.com')) return Twitter;
    if (url.includes('youtube.com')) return Youtube;
    if (url.includes('linkedin.com')) return Linkedin;
    if (url.includes('github.com')) return Github;
    if (url.includes('mailto:') || (url.includes('@') && !url.includes('/'))) return Mail;
    return Globe;
};
</script>

<template>
    <footer class="w-full bg-white border-t border-gray-200 pt-12 pb-28 md:pb-12 font-sans">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-10">
                <!-- Bagian Paling Kiri: Logo & Tagline -->
                <div class="lg:col-span-2 pr-0 lg:pr-8">
                    <Link :href="route('Home')" class="flex items-center gap-2 mb-4">
                        <img src="/kitasewa-logo.png" alt="KitaSewa Logo" width="32" height="32" class="h-8 w-auto object-contain" />
                        <span class="font-bold text-lg text-[#0A2540]">
                            kitasewa<span class="text-[#FFC000]">.id</span>
                        </span>
                    </Link>
                    <p class="text-sm text-gray-500 leading-relaxed max-w-sm">
                        Butuh tempat untuk mewujudkan rencana? Temukan berbagai aset sewaan di KitaSewa, dari tempat tinggal, usaha, hingga kebutuhan event dan promosi.
                    </p>
                </div>

                <!-- Kolom 2: KITASEWA.ID -->
                <div>
                    <h3 class="font-bold text-[#0A2540] text-sm uppercase tracking-wider mb-5">Kitasewa.id</h3>
                    <ul class="flex flex-col space-y-4">
                        <li>
                            <Link href="#" class="text-sm text-gray-500 hover:text-[#FFC000] transition-colors">Tentang Kami</Link>
                        </li>
                        <li>
                            <Link href="#" class="text-sm text-gray-500 hover:text-[#FFC000] transition-colors">Cara Kerja</Link>
                        </li>
                    </ul>
                </div>

                <!-- Kolom 3: MENU BANTUAN -->
                <div>
                    <h3 class="font-bold text-[#0A2540] text-sm uppercase tracking-wider mb-5">Menu Bantuan</h3>
                    <ul class="flex flex-col space-y-4">
                        <li>
                            <Link :href="route('bantuan.index')" class="text-sm text-gray-500 hover:text-[#FFC000] transition-colors">Pusat Bantuan</Link>
                        </li>
                        <li>
                            <Link :href="route('hubungi-kami')" class="text-sm text-gray-500 hover:text-[#FFC000] transition-colors">Hubungi Kami</Link>
                        </li>
                    </ul>
                </div>

                <!-- Kolom 4: KEBIJAKAN -->
                <div>
                    <h3 class="font-bold text-[#0A2540] text-sm uppercase tracking-wider mb-5">Kebijakan</h3>
                    <ul class="flex flex-col space-y-4">
                        <li>
                            <Link href="#" class="text-sm text-gray-500 hover:text-[#FFC000] transition-colors">Syarat dan Ketentuan</Link>
                        </li>
                        <li>
                            <Link href="#" class="text-sm text-gray-500 hover:text-[#FFC000] transition-colors">Kebijakan Privasi</Link>
                        </li>
                    </ul>
                </div>

                <!-- Kolom 5: IKUTI KAMI -->
                <div>
                    <h3 class="font-bold text-[#0A2540] text-sm uppercase tracking-wider mb-5">Ikuti Kami</h3>
                    <ul class="flex flex-col space-y-4">
                        <li v-for="link in socialLinks" :key="link.id">
                            <a :href="link.url" target="_blank" rel="noopener noreferrer" class="flex items-start gap-3 text-sm text-gray-500 hover:text-[#FFC000] transition-colors">
                                <component :is="getIconComponent(link.url)" class="w-4 h-4 shrink-0 mt-0.5" />
                                <span class="break-words">{{ link.platform_name }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="mt-12 pt-8 border-t border-gray-200 flex flex-col items-center justify-center gap-4 text-center">
                <p class="text-sm text-gray-500">
                    &copy; {{ new Date().getFullYear() }} Kitasewa.id. Hak cipta dilindungi undang-undang
                </p>
            </div>
        </div>
    </footer>
</template>
