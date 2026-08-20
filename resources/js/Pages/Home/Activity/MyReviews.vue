<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Star, ChevronLeft, Calendar } from 'lucide-vue-next';
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';

const props = defineProps({
    reviews: {
        type: Object,
        required: true
    }
});

// Calculate time ago
const timeAgo = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Hari ini';
    if (diffDays === 1) return 'Kemarin';
    if (diffDays < 7) return `${diffDays} hari lalu`;

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    }).format(date);
};

const getImageUrl = (imgObj) => {
    const imgStr = imgObj?.image_url || imgObj?.image;
    if (!imgStr) return 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=300&q=80';
    if (imgStr.startsWith('http')) return imgStr;
    if (imgStr.startsWith('/assets/') || imgStr.startsWith('/storage/')) return imgStr;
    if (imgStr.startsWith('assets/')) return '/' + imgStr;
    if (imgStr.startsWith('/')) return '/storage' + imgStr;
    return '/storage/' + imgStr;
};
</script>

<template>
    <AppLayout :hideNavbar="true">
        <Head title="Ulasan Saya" />

        <div class="bg-[#F8F9FA] min-h-screen pb-24 sm:pb-16">
            <!-- Custom Top Navbar -->
            <div class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">
                <button @click="router.get(route('aktivitas.hub'))" class="p-2 -ml-2 rounded-full hover:bg-slate-50 transition-colors">
                    <ChevronLeft class="w-6 h-6 text-[#1D1D1F]" />
                </button>
                <h1 class="text-base font-bold text-[#1D1D1F]">Ulasan Saya</h1>
                <div class="w-10"></div>
            </div>

            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 text-[#1D1D1F] min-h-[70vh]">

            <!-- Empty State -->
            <div v-if="!reviews.data || reviews.data.length === 0" class="bg-white rounded-[1.5rem] border border-slate-200/60 py-16 px-4 text-center shadow-xs flex flex-col items-center justify-center mt-6">
                <EmptyStateIcon class="w-48 h-48 object-contain mb-6 opacity-80" />
                <h2 class="text-xl font-bold text-[#0A2540] mb-2">Belum ada ulasan</h2>
                <p class="text-sm text-[#6C757D] mb-6">Anda belum pernah memberikan ulasan untuk penyewaan apa pun.</p>
                <button @click="router.get(route('aktivitas.transaksi'))" class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors">
                    Lihat Transaksi
                </button>
            </div>

            <!-- List -->
            <div v-else class="space-y-4">
                <div
                    v-for="review in reviews.data"
                    :key="review.id"
                    class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 p-4 sm:p-5 hover:shadow-md transition-shadow"
                >
                    <!-- Asset Info -->
                    <div
                        class="flex items-center gap-4 mb-4 pb-4 border-b border-slate-100 cursor-pointer group"
                        @click="router.get(route('assets.show', review.booking?.asset?.slug || review.booking?.asset?.id))"
                    >
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl overflow-hidden shrink-0 bg-slate-100">
                            <img :src="getImageUrl(review.booking?.asset?.first_image || review.booking?.asset?.firstImage)" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 uppercase tracking-wide">
                                    {{ review.booking?.asset?.type?.category?.name || 'Aset' }}
                                </span>
                            </div>
                            <h3 class="font-bold text-sm sm:text-base text-[#1D1D1F] truncate group-hover:text-[#FFC000] transition-colors">
                                {{ review.booking?.asset?.title || 'Aset tidak diketahui' }}
                            </h3>
                            <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                <Calendar class="w-3 h-3" /> Booking ID: {{ review.booking?.booking_code || review.booking_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Review Content -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1">
                                <Star
                                    v-for="i in 5"
                                    :key="i"
                                    class="w-4 h-4 sm:w-5 sm:h-5"
                                    :class="i <= review.rating ? 'fill-[#FFC000] text-[#FFC000]' : 'fill-slate-100 text-slate-200'"
                                />
                            </div>
                            <span class="text-[10px] sm:text-xs text-slate-400">{{ timeAgo(review.created_at) }}</span>
                        </div>

                        <p class="text-sm sm:text-base text-[#1D1D1F] mt-3" v-if="review.review">
                            "{{ review.review }}"
                        </p>
                        <p class="text-sm text-slate-400 italic mt-3" v-else>
                            Tidak ada pesan ulasan.
                        </p>

                        <!-- Tags -->
                        <div class="flex flex-wrap gap-2 mt-4" v-if="review.items && review.items.length > 0">
                            <span
                                v-for="item in review.items"
                                :key="item.id"
                                class="px-2 py-1 bg-amber-50 text-amber-600 border border-amber-200/50 rounded-lg text-[10px] sm:text-xs font-semibold"
                            >
                                {{ item.review_tag?.name || 'Tag' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination (if more than 1 page) -->
            <div v-if="reviews.last_page > 1" class="mt-8 flex justify-center gap-2">
                <button
                    v-for="link in reviews.links"
                    :key="link.label"
                    @click="link.url ? router.get(link.url) : null"
                    v-html="link.label"
                    :disabled="!link.url"
                    :class="[
                        'px-3 py-1.5 text-sm rounded-lg border transition-colors',
                        link.active ? 'bg-[#FFC000] border-[#FFC000] text-[#0A2540] font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50',
                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                ></button>
            </div>

        </div>
        </div>
    </AppLayout>
</template>
