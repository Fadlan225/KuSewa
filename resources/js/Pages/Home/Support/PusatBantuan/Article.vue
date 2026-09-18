<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import HelpCenterLayout from '@/Layouts/HelpCenterLayout.vue';
import { ChevronRight, ThumbsUp, ThumbsDown, MessageSquare, AlertTriangle, ArrowRight, BookOpen, Clock, Loader2, User, Building, Shield, Briefcase, HelpCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    tab: String,
    article: Object,
    sidebarCategories: Array
});

const activePlatform = ref('desktop');

const feedbackForm = useForm({
    is_helpful: null,
    reason: '',
    reason_custom: ''
});

const feedbackSubmitted = ref(false);

const submitFeedback = (isHelpful) => {
    feedbackForm.is_helpful = isHelpful;
    if (isHelpful) {
        feedbackForm.post(route('bantuan.feedback', props.article.id), {
            preserveScroll: true,
            onSuccess: () => feedbackSubmitted.value = true
        });
    }
};

const submitReason = () => {
    if (!feedbackForm.reason) return;

    let finalReason = feedbackForm.reason;
    if (finalReason === 'Lainnya') {
        if (!feedbackForm.reason_custom) return;
        finalReason = feedbackForm.reason_custom;
    }

    feedbackForm.transform((data) => ({
        ...data,
        reason: finalReason
    })).post(route('bantuan.feedback', props.article.id), {
        preserveScroll: true,
        onSuccess: () => feedbackSubmitted.value = true
    });
};

const predefinedReasons = [
    'Artikel tidak memberikan penjelasan yang lengkap.',
    'Artikel memberikan informasi yang tidak akurat.',
    'Artikel tidak dapat dibaca dengan jelas.',
    'Lainnya'
];


const breadcrumbs = computed(() => {
    const crumbs = [];
    if (props.article.category?.parent) {
        crumbs.push({
            label: props.article.category.parent.name,
            route: route('bantuan.category', props.article.category.parent.id)
        });
    }
    if (props.article.category) {
        crumbs.push({ label: props.article.category.name });
    }
    crumbs.push({ label: props.article.title });
    return crumbs;
});

const openCategory = ref(props.article.category?.parent_id || null);

const toggleCategory = (id) => {
    if (openCategory.value === id) {
        openCategory.value = null;
    } else {
        openCategory.value = id;
    }
};

const getIcon = (iconName) => {
    const icons = {
        User,
        Building,
        Shield,
        Briefcase,
        HelpCircle
    };
    return icons[iconName] || HelpCircle;
};
</script>

<template>
    <Head :title="`${article.title} - Pusat Bantuan - KuSewa`" />
    <HelpCenterLayout :tab="tab" :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col md:flex-row gap-10 relative items-start">

                <!-- Sidebar -->
                <div class="w-full md:w-1/4 shrink-0 md:sticky md:top-24 hidden md:block">
                    <div class="space-y-3">
                        <div v-for="cat in sidebarCategories" :key="cat.id" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                            <!-- Main Category Header -->
                            <button 
                                @click="toggleCategory(cat.id)"
                                class="flex items-center justify-between p-4 w-full text-left transition-colors relative outline-none"
                                :class="[
                                    openCategory === cat.id ? 'text-[#FFC000]' : 'text-[#0A2540] hover:bg-gray-50'
                                ]"
                            >
                                <!-- Yellow Left Border for active -->
                                <div 
                                    v-if="openCategory === cat.id" 
                                    class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#FFC000] rounded-r-md"
                                ></div>

                                <div class="flex items-center gap-4 px-2">
                                    <!-- Icon -->
                                    <component 
                                        :is="getIcon(cat.icon)" 
                                        class="w-[22px] h-[22px] shrink-0" 
                                        :class="openCategory === cat.id ? 'text-[#FFC000]' : 'text-gray-400'" 
                                    />
                                    <span class="font-bold text-[16px]">{{ cat.name }}</span>
                                </div>

                                <!-- Chevron -->
                                <ChevronRight 
                                    class="w-[18px] h-[18px] shrink-0 transition-transform duration-200" 
                                    :class="[
                                        openCategory === cat.id ? 'rotate-90 text-[#FFC000]' : 'text-gray-400'
                                    ]" 
                                />
                            </button>

                            <!-- Sub Categories -->
                            <div 
                                v-show="openCategory === cat.id && cat.children && cat.children.length > 0" 
                                class="pl-[4.5rem] pr-4 pb-4"
                            >
                                <ul class="space-y-3.5">
                                    <li v-for="sub in cat.children" :key="sub.id" class="relative">
                                        <Link 
                                            :href="route('bantuan.category', sub.id)" 
                                            class="text-[14.5px] block transition-colors"
                                            :class="[
                                                sub.id === article.category_id 
                                                    ? 'font-bold text-[#FFC000]' 
                                                    : 'text-[#6C757D] hover:text-gray-900'
                                            ]"
                                        >
                                            {{ sub.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="w-full md:w-3/4 bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-10">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-6">{{ article.title }}</h1>

                    <div v-if="article.summary_answer" class="text-[15px] text-gray-700 leading-relaxed mb-8 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        {{ article.summary_answer }}
                    </div>

                    <!-- Platform Tabs -->
                    <div v-if="article.has_platform_tabs" class="mb-8 border-b border-gray-200 flex space-x-6">
                        <button
                            @click="activePlatform = 'desktop'"
                            class="pb-3 text-sm font-bold transition-colors"
                            :class="activePlatform === 'desktop' ? 'text-[#FFC000] border-b-2 border-[#FFC000]' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Desktop
                        </button>
                        <button
                            @click="activePlatform = 'mobile'"
                            class="pb-3 text-sm font-bold transition-colors"
                            :class="activePlatform === 'mobile' ? 'text-[#FFC000] border-b-2 border-[#FFC000]' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Aplikasi
                        </button>
                    </div>

                    <!-- Content Area -->
                    <div class="prose prose-blue max-w-none text-gray-700">
                        <!-- We use v-html because content is stored as Rich Text from admin -->
                        <div v-if="!article.has_platform_tabs || activePlatform === 'desktop'" v-html="article.content_desktop"></div>
                        <div v-else-if="article.has_platform_tabs && activePlatform === 'mobile'" v-html="article.content_mobile"></div>
                    </div>

                    <!-- Additional Notes -->
                    <div v-if="article.additional_notes" class="mt-12 p-5 bg-blue-50/50 rounded-xl border border-blue-100 text-sm text-gray-700">
                        <div class="font-bold text-[#0A2540] mb-2 flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-500"></i> Catatan Tambahan
                        </div>
                        <div v-html="article.additional_notes"></div>
                    </div>

                    <!-- Feedback Survey -->
                    <div class="mt-16 pt-8 border-t border-gray-200">
                        <div v-if="feedbackSubmitted" class="text-center py-6 bg-green-50 rounded-xl border border-green-100">
                            <CheckCircle class="mx-auto h-10 w-10 text-green-500 mb-3" />
                            <p class="font-bold text-gray-900">Terima kasih atas masukan Anda!</p>
                            <p class="text-sm text-gray-500 mt-1">Saran Anda membantu kami meningkatkan kualitas panduan ini.</p>
                        </div>

                        <div v-else>
                            <h3 class="font-bold text-gray-900 text-center mb-6">Apakah artikel ini membantu Anda?</h3>

                            <div v-if="feedbackForm.is_helpful === null || feedbackForm.is_helpful === true" class="flex items-center justify-center gap-4">
                                <button
                                    @click="submitFeedback(true)"
                                    class="flex items-center gap-2 px-6 py-2.5 rounded-full border border-gray-200 font-medium text-gray-700 hover:bg-green-50 hover:text-green-600 hover:border-green-200 transition-all"
                                    :disabled="feedbackForm.processing"
                                >
                                    <ThumbsUp class="w-4 h-4" /> Ya
                                </button>
                                <button
                                    @click="feedbackForm.is_helpful = false"
                                    class="flex items-center gap-2 px-6 py-2.5 rounded-full border border-gray-200 font-medium text-gray-700 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all"
                                >
                                    <ThumbsDown class="w-4 h-4" /> Tidak
                                </button>
                            </div>

                            <!-- Reason form if NO -->
                            <div v-if="feedbackForm.is_helpful === false" class="max-w-md mx-auto animate-fade-in-up">
                                <p class="text-sm text-gray-600 mb-4 font-medium text-center">Mohon beri tahu kami apa yang bisa ditingkatkan:</p>

                                <div class="space-y-3 mb-4">
                                    <label v-for="(reason, idx) in predefinedReasons" :key="idx" class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors" :class="{'border-[#FFC000] bg-yellow-50/30': feedbackForm.reason === reason}">
                                        <input type="radio" :value="reason" v-model="feedbackForm.reason" class="mt-0.5 text-[#FFC000] focus:ring-[#FFC000]">
                                        <span class="text-sm text-gray-700 leading-tight">{{ reason }}</span>
                                    </label>
                                </div>

                                <textarea
                                    v-if="feedbackForm.reason === 'Lainnya'"
                                    v-model="feedbackForm.reason_custom"
                                    rows="3"
                                    class="w-full text-sm rounded-lg border-gray-200 focus:ring-[#FFC000] focus:border-[#FFC000] mb-4"
                                    placeholder="Tuliskan alasan spesifik Anda..."
                                ></textarea>

                                <div class="flex gap-3">
                                    <button
                                        @click="feedbackForm.is_helpful = null"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        @click="submitReason"
                                        :disabled="!feedbackForm.reason || (feedbackForm.reason === 'Lainnya' && !feedbackForm.reason_custom) || feedbackForm.processing"
                                        class="flex-1 px-4 py-2 text-sm font-bold text-[#0A2540] bg-[#FFC000] hover:bg-yellow-400 rounded-lg transition-colors disabled:opacity-50"
                                    >
                                        Kirim Masukan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </HelpCenterLayout>
</template>
