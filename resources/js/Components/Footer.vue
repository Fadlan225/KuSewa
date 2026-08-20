<script setup>
import { Facebook, Instagram, Mail, MessageCircle } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="w-full bg-white border-t border-gray-200 py-12 font-sans">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-10">
                <!-- Bagian Paling Kiri: Logo & Tagline -->
                <div class="lg:col-span-2 pr-0 lg:pr-8">
                    <Link :href="route('Home')" class="flex items-center gap-2 mb-4">
                        <img src="/kitasewa-logo.png" alt="KitaSewa Logo" class="h-8 w-auto object-contain" />
                        <span class="font-bold text-lg text-[#0A2540]">
                            kitasewa<span class="text-[#FFC000]">.id</span>
                        </span>
                    </Link>
                    <p class="text-sm text-gray-500 leading-relaxed max-w-sm">
                        Platform penyewaan aset terpercaya. Temukan berbagai aset kantor, hunian, dan lahan terbaik sesuai kebutuhan Anda dengan mudah dan aman.
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
                            <Link href="#" class="text-sm text-gray-500 hover:text-[#FFC000] transition-colors">Pusat Bantuan</Link>
                        </li>
                        <li>
                            <Link href="#" class="text-sm text-gray-500 hover:text-[#FFC000] transition-colors">Hubungi Kami</Link>
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
                        <li>
                            <a href="#" class="flex items-center gap-3 text-sm text-gray-500 hover:text-[#FFC000] transition-colors">
                                <Facebook class="w-4 text-center text-lg" />
                                Facebook
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center gap-3 text-sm text-gray-500 hover:text-[#FFC000] transition-colors">
                                <Instagram class="w-4 text-center text-lg" />
                                Instagram
                            </a>
                        </li>
                        <li>
                            <a href="mailto:cs@kitasewa.id" class="flex items-center gap-3 text-sm text-gray-500 hover:text-[#FFC000] transition-colors">
                                <Mail class="w-4 text-center text-lg" />
                                Email
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center gap-3 text-sm text-gray-500 hover:text-[#FFC000] transition-colors">
                                <MessageCircle class="w-4 text-center text-lg" />
                                No Whatsapp
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="mt-12 pt-8 border-t border-gray-200 flex flex-col items-center justify-center gap-4 text-center">
                <p class="text-sm text-gray-500">
                    &copy; {{ new Date().getFullYear() }} Kitasewa.id. All rights reserved
                </p>
            </div>
        </div>
    </footer>
</template>
