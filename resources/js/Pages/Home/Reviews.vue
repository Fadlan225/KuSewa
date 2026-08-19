<script setup>
import { ArrowLeft, Star } from 'lucide-vue-next';
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';

const props = defineProps({
    booking_id: {
        type: [String, Number],
        required: true
    },
    booking_code: {
        type: String,
        required: true
    },
    asset: {
        type: Object,
        required: true
    },
    availableTags: {
        type: Array,
        required: true
    }
});

const form = useForm({
    booking_id: props.booking_id,
    rating: 0,
    tags: [],
    review: ''
});

const hoverRating = ref(0);

const setRating = (star) => {
    form.rating = star;
};

const toggleTag = (tagId) => {
    const index = form.tags.indexOf(tagId);
    if (index > -1) {
        form.tags.splice(index, 1);
    } else {
        form.tags.push(tagId);
    }
};

const submitReview = () => {
    form.post(route('ulasan.store', props.booking_id));
};
</script>

<template>
    <Head title="Beri Ulasan" />

    <AppLayout :hideNavbar="true" :hideBottombar="true">
        <div class="min-h-screen bg-white flex flex-col relative pb-24 md:pb-0">
            <!-- Topbar -->
            <DetailNavbar
                :showSections="false"
                :showShare="false"
                :showFavorite="false"
                :backUrl="route('aktivitas.transaksi')"
                class="!sticky top-0"
            >
                <template #content>
                    <div class="flex items-center justify-between w-full">
                        <Link :href="route('aktivitas.transaksi')" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition-colors">
                            <ArrowLeft class="text-[#0A2540]" />
                        </Link>
                        <div class="text-center">
                            <h1 class="font-bold text-gray-900 leading-tight">Penyewaan Selesai</h1>
                            <p class="text-xs text-gray-500">kode Booking : {{ booking_code }}</p>
                        </div>
                        <div class="w-10"></div>
                    </div>
                </template>
            </DetailNavbar>

            <!-- Main Content Container -->
            <div class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-12 flex flex-col md:flex-row gap-8 lg:gap-12">

                <!-- Kiri: Info Aset -->
                <div class="flex flex-col items-center md:w-1/3 md:border md:border-gray-200 md:rounded-2xl md:p-6 md:h-fit">
                    <!-- Foto -->
                    <div class="w-20 h-20 md:w-full md:h-40 md:rounded-xl rounded-full overflow-hidden mb-4 border border-gray-100 shadow-sm">
                        <img :src="asset.image" alt="Asset" class="w-full h-full object-cover" />
                    </div>
                    <!-- Teks -->
                    <div class="text-center md:text-left w-full">
                        <p class="text-gray-800 font-bold md:text-lg mb-1 md:text-center">{{ asset.name }}</p>
                        <p class="text-sm text-gray-500 md:text-center">Host: {{ asset.host }}</p>
                        <div class="flex items-center justify-center gap-1.5 mt-2">
                            <Star class="text-[#FFC000] text-lg" />
                            <span class="font-bold text-gray-800 text-lg">{{ asset.rating }}</span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Form Ulasan -->
                <div class="flex flex-col flex-1">

                    <!-- Main Title -->
                    <h2 class="text-2xl font-extrabold text-center md:text-left text-[#0A2540] mb-6 mt-2 md:mt-0">
                        Bagaimana pengalaman sewa kamu?
                    </h2>

                    <!-- Star Rating -->
                    <div class="flex justify-center md:justify-start gap-2 mb-8">
                        <button
                            v-for="star in 5" :key="star"
                            type="button"
                            @click="setRating(star)"
                            @mouseenter="hoverRating = star"
                            @mouseleave="hoverRating = 0"
                            class="text-4xl transition-transform hover:scale-110 focus:outline-none"
                        >
                            <Star class=""
                               :class="(hoverRating || form.rating) >= star ? 'text-[#FFC000]' : 'text-gray-200'" />
                        </button>
                    </div>

                    <!-- Tags Section -->
                    <div v-if="form.rating === 5" class="mb-8 animate-fade-in-up">
                        <h3 class="text-center md:text-left font-bold text-gray-800 mb-4 text-lg">Apa yang membuat kamu terkesan?</h3>
                        <div class="flex md:flex-wrap overflow-x-auto md:overflow-visible gap-2.5 pb-2 hide-scrollbar snap-x">
                            <button
                                v-for="tag in availableTags" :key="tag.id"
                                type="button"
                                @click="toggleTag(tag.id)"
                                class="whitespace-nowrap shrink-0 snap-start px-4 py-2 rounded-full border text-sm font-medium transition-colors"
                                :class="form.tags.includes(tag.id)
                                    ? 'bg-[#FFC000]/10 border-[#FFC000] text-[#B38600]'
                                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                            >
                                {{ tag.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Review Textarea -->
                    <div>
                        <textarea
                            v-model="form.review"
                            placeholder="Ceritakan pengalaman sewamu (Opsional)"
                            class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm text-gray-700 focus:ring-2 focus:ring-gray-200 focus:border-gray-200 resize-none h-28"
                        ></textarea>
                    </div>

                <!-- Desktop Bottom Button (DetailBottomBar is md:hidden) -->
                <button
                    @click="submitReview"
                    :disabled="form.rating === 0 || form.processing"
                    class="hidden md:flex mt-6 w-full py-3.5 rounded-xl font-bold transition-all shadow-md justify-center items-center gap-2"
                    :class="form.rating > 0 ? 'bg-[#FFC000] text-[#0A2540] hover:bg-[#FFC000]/90 cursor-pointer' : 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none'"
                >
                    <span v-if="form.processing">Mengirim...</span>
                    <span v-else>Kirim</span>
                </button>
            </div>
            </div>

            <!-- Mobile Detail Bottom Bar -->
            <DetailBottomBar
                :hideLeftContent="true"
                buttonText="Kirim"
                :disabled="form.rating === 0 || form.processing"
                @submit="submitReview"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
