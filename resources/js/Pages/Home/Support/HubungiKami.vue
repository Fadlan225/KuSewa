<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { MessageCircle, Instagram, Mail } from 'lucide-vue-next'; // Changed to MessageCircle to look like WhatsApp

const whatsappNumber = '6285151241588';
const whatsappMessage = encodeURIComponent('Halo CS KitaSewa, saya butuh bantuan terkait...');
const whatsappLink = `https://wa.me/${whatsappNumber}?text=${whatsappMessage}`;
</script>

<template>
    <Head title="Hubungi CS - KitaSewa" />
    <AppLayout>
        <div class="bg-gray-50/50 min-h-screen pt-12 pb-24">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">

                <h1 class="text-3xl md:text-[32px] font-extrabold text-[#0A2540] mb-2 tracking-tight">Hubungi CS KitaSewa</h1>
                <p class="text-gray-500 mb-10 text-sm md:text-base">Silakan pilih metode yang Anda inginkan.</p>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-16">
                    <!-- WhatsApp Card -->
                    <a :href="whatsappLink" target="_blank" rel="noopener noreferrer"
                       class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col hover:shadow-md hover:border-primary transition-all cursor-pointer group">
                        <div class="mb-5 text-gray-700 group-hover:text-primary transition-colors">
                            <MessageCircle class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-[#0A2540] mb-2 group-hover:text-primary transition-colors">WhatsApp</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Hubungi customer service lewat WhatsApp Anda.</p>
                    </a>

                    <!-- Email Card -->
                    <a href="mailto:kitasewa.web.id@gmail.com"
                       class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col hover:shadow-md hover:border-primary transition-all cursor-pointer group">
                        <div class="mb-5 text-gray-700 group-hover:text-primary transition-colors">
                            <Mail class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold text-[#0A2540] mb-2 group-hover:text-primary transition-colors">Email</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Kirimkan pertanyaan atau keluhan Anda melalui email resmi kami.</p>
                    </a>
                </div>

                <!-- Footer / Keterangan -->
                <div class="space-y-8 text-[13px] md:text-sm text-[#0A2540]">
                    <div class="space-y-8">
                        <!-- CS KitaSewa -->
                        <div>
                            <h4 class="font-bold text-[#0A2540] mb-3">Layanan Pengaduan Konsumen</h4>
                            <p class="text-gray-600 mb-2">CS KitaSewa</p>
                            <div class="flex flex-col gap-2">
                                <a :href="whatsappLink" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-600 font-medium hover:text-[#FFC000] transition-colors w-fit">
                                    <MessageCircle class="w-4 h-4" />
                                    <span>WhatsApp: 0851-5124-1588</span>
                                </a>
                                <a href="mailto:kitasewa.web.id@gmail.com" class="flex items-center gap-2 text-gray-600 font-medium hover:text-[#FFC000] transition-colors w-fit">
                                    <Mail class="w-4 h-4" />
                                    <span>Email: kitasewa.web.id@gmail.com</span>
                                </a>
                            </div>
                        </div>

                        <!-- Media Sosial -->
                        <div>
                            <h4 class="font-bold text-[#0A2540] mb-3">Media Sosial</h4>
                            <div class="flex flex-col gap-2">
                                <a href="https://www.instagram.com/kitasewa.web.id/" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-600 font-medium hover:text-[#FFC000] transition-colors w-fit">
                                    <Instagram class="w-4 h-4" />
                                    <span>Instagram: kitasewa.web.id</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
