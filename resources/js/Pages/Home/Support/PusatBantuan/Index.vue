<script setup>
import { Head, Link } from '@inertiajs/vue3';
import HelpCenterLayout from '@/Layouts/HelpCenterLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';
import EmptyStateSupport from '@/Components/ui/Icons/EmptyStateSupport.vue';
import Hero from './Hero.vue';

const props = defineProps({
    tab: String,
    categories: Array
});
</script>

<template>
    <Head title="Pusat Bantuan" />
    <HelpCenterLayout :tab="tab">
        <Hero @search="(q) => { /* Optional: can integrate with layout search */ }" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h1 class="text-2xl font-bold text-gray-900 mb-8">Kategori Bantuan</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <!-- Main Category Box -->
                <div v-for="category in categories" :key="category.id" class="flex flex-col">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center shrink-0 text-gray-600">
                            <!-- Try rendering AppIcon if it's fa- or else we can just use Lucide icon if it's text. We assume icon is lucide class or icon name -->
                            <AppIcon v-if="category.icon" :iconClass="category.icon" />
                            <i v-else class="fas fa-folder-open"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">{{ category.name }}</h2>
                    </div>

                    <!-- Sub Categories List -->
                    <ul class="space-y-3 pl-[3.25rem]">
                        <li v-for="subCategory in category.children" :key="subCategory.id">
                            <a
                                :href="route('bantuan.category', category.id) + '#sub-' + subCategory.id"
                                class="text-gray-600 hover:text-[#FFC000] text-sm font-medium transition-colors"
                            >
                                {{ subCategory.name }}
                            </a>
                        </li>
                        <li v-if="!category.children || category.children.length === 0" class="text-sm text-gray-400 italic">
                            Belum ada sub-kategori.
                        </li>
                    </ul>
                </div>
            </div>

            <div v-if="categories.length === 0" class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <EmptyStateSupport class="w-64 h-auto md:w-96 mb-8 mt-10 opacity-60" />
                <h2 class="text-2xl font-bold text-gray-900 mb-3">Artikel Panduan Belum Tersedia</h2>
                <p class="text-gray-500 max-w-md mx-auto mb-8 leading-relaxed">
                    Halaman ini akan memuat panduan penggunaan platform. Artikel akan otomatis ditampilkan setelah pengelola sistem mempublikasikannya.
                </p>
                <Link :href="route('hubungi-kami')" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-bold rounded-md text-[#0A2540] bg-[#FFC000] hover:bg-yellow-400 transition-colors shadow-sm">
                    Hubungi CS
                </Link>
            </div>
        </div>
    </HelpCenterLayout>
</template>
