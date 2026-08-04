<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    reviews: {
        type: Array,
        default: () => []
    },
    reviewsCount: {
        type: Number,
        default: 0
    },
    averageRating: {
        type: [Number, String],
        default: 0
    }
});

// Menghitung distribusi rating (5 bintang sampai 1 bintang)
const reviewDistribution = computed(() => {
    const counts = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
    if (props.reviews) {
        props.reviews.forEach(r => {
            const rating = Math.round(r.rating);
            if (counts[rating] !== undefined) {
                counts[rating]++;
            }
        });
    }
    const total = props.reviews?.length || 0;

    return [5, 4, 3, 2, 1].map(star => {
        const count = counts[star];
        const percentage = total > 0 ? (count / total) * 100 : 0;
        return { star, count, percentage };
    });
});

const reviewTagsDistribution = computed(() => {
    if (!props.reviews || props.reviews.length === 0) return [];
    
    const tagCounts = {};
    const totalReviews = props.reviews.length;
    
    props.reviews.forEach(review => {
        if (review.review_tag_items && review.review_tag_items.length > 0) {
            review.review_tag_items.forEach(item => {
                const tag = item.review_tag;
                if (tag) {
                    if (!tagCounts[tag.name]) {
                        tagCounts[tag.name] = { count: 0, name: tag.name };
                    }
                    tagCounts[tag.name].count++;
                }
            });
        }
    });
    
    const sortedTags = Object.values(tagCounts)
        .sort((a, b) => b.count - a.count)
        .slice(0, 6);
    
    return sortedTags.map(tag => {
        const percentage = Math.round((tag.count / totalReviews) * 100);
        return { ...tag, percentage };
    });
});

const showAllReviews = ref(false);

const visibleReviews = computed(() => {
    if (!props.reviews) return [];
    if (showAllReviews.value) return props.reviews;
    return props.reviews.slice(0, 6);
});

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <div id="ulasan" class="pt-6 border-t border-gray-200">
        <h3 class="text-[22px] font-semibold text-[#222222] mb-6">Ulasan Pengguna</h3>

        <div v-if="reviewsCount > 0" class="mb-10">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 sm:gap-10">
                <!-- Sisi Kiri: Nilai Rata-rata -->
                <div class="flex flex-col items-center justify-center shrink-0 w-full sm:w-auto">
                    <div class="text-6xl font-black text-[#0A2540] tracking-tighter">
                        {{ parseFloat(averageRating || 0).toFixed(1) }}
                    </div>

                    <div class="flex gap-1 text-[#FFC000] my-2 text-lg">
                        <i v-for="i in 5" :key="i" class="fa-solid fa-star"
                            :class="{
                                'fa-star-half-stroke': i - 0.5 <= averageRating && i > averageRating,
                                'text-gray-200': i > Math.ceil(averageRating)
                            }">
                        </i>
                    </div>

                    <span class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-wider">
                        {{ reviewsCount || 0 }} Penilaian
                    </span>
                </div>

                <!-- Sisi Kanan: Progress Bar Breakdown -->
                <div class="flex-grow sm:pl-8 flex flex-col gap-3 w-full">
                    <div v-for="item in reviewTagsDistribution" :key="item.name" class="flex items-center text-sm">
                        <div class="flex flex-col w-full gap-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600 font-medium">{{ item.name }}</span>
                                <span class="font-bold text-[#0A2540]">{{ item.percentage }}%</span>
                            </div>
                            <!-- Progress Bar dinamis -->
                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#FFC000] rounded-full transition-all duration-500" :style="{ width: item.percentage + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="reviews && reviews.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                    <!-- Card Individual Ulasan -->
                    <div v-for="review in visibleReviews" :key="review.id" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                        <!-- Header Card Ulasan: Profil & Bintang -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#0A2540] relative flex items-center justify-center text-white font-bold overflow-hidden shrink-0">
                                    <!-- Inisial selalu ada di belakang -->
                                    <span class="absolute inset-0 flex items-center justify-center">{{ review.user?.name?.charAt(0) || 'U' }}</span>
                                    <!-- Jika ada foto, letakkan di atas. Jika gagal dimuat, sembunyikan img-nya menggunakan native onerror -->
                                    <img v-if="review.user?.profile_photo" :src="review.user.profile_photo" onerror="this.style.display='none'" class="w-full h-full object-cover relative z-10" />
                                </div>
                                <div>
                                    <p class="font-bold text-[#0A2540] text-sm">{{ review.user?.name || 'Anonim' }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(review.created_at) }}</p>
                                </div>
                            </div>
                            <!-- Bintang Ulasan User -->
                            <div class="flex gap-0.5">
                                <i v-for="i in 5" :key="i" class="fa-solid fa-star text-[11px]" :class="i <= review.rating ? 'text-[#FFC000]' : 'text-gray-200'"></i>
                            </div>
                        </div>

                        <!-- Teks Ulasan -->
                        <p class="text-sm text-gray-600 leading-relaxed">
                            "{{ review.review }}"
                        </p>

                        <!-- Tags yang dipilih -->
                        <div v-if="review.review_tag_items && review.review_tag_items.length > 0" class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-gray-50">
                            <span v-for="tagItem in review.review_tag_items" :key="tagItem.id" class="px-2.5 py-1 bg-[#FFC000]/10 text-[#B38600] font-semibold border border-[#FFC000]/20 rounded-md text-[11px]">
                                {{ tagItem.review_tag?.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="reviews.length > 6 && !showAllReviews" class="mt-8">
                    <button @click="showAllReviews = true" class="px-6 py-3 rounded-lg border border-black bg-white hover:bg-gray-50 text-[#222222] font-semibold text-[15px] transition-colors inline-block">
                        Tampilkan ke-{{ reviews.length }} ulasan
                    </button>
                </div>
            </div>
        </div>

        <!-- EMPTY STATE (Jika belum ada ulasan) -->
        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                <i class="fa-solid fa-star text-2xl text-gray-200"></i>
            </div>
            <h3 class="text-[#0A2540] font-bold text-lg mb-1">Belum Ada Ulasan</h3>
            <p class="text-sm text-gray-500">Jadilah yang pertama memberikan ulasan!</p>
        </div>
    </div>
</template>
