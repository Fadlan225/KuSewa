<script setup>
import { Head, Link } from '@inertiajs/vue3';
import HelpCenterLayout from '@/Layouts/HelpCenterLayout.vue';
import { ChevronRight, User, Building, Shield, Briefcase, HelpCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    tab: String,
    category: Object,
    sidebarCategories: Array
});

// Find the active subcategory based on current URL or just assume category is the main one if we clicked it.
// Wait, the controller passes $category which is the root category. We need to display its subcategories and articles.


const breadcrumbs = computed(() => {
    return [
        { label: props.category.name }
    ];
});

const openCategory = ref(props.category.parent_id || props.category.id || null);

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
    <Head :title="`${category.name} - Pusat Bantuan - KuSewa`" />
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
                                            class="text-[14.5px] block transition-colors text-[#6C757D] hover:text-gray-900"
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
                <div class="w-full md:w-3/4 bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">{{ category.name }}</h1>
                    
                    <!-- If this is a root category (has children) -->
                    <template v-if="!category.parent_id">
                        <div v-if="!category.children || category.children.length === 0" class="text-gray-500 italic">
                            Kategori ini belum memiliki artikel.
                        </div>
                        
                        <div class="space-y-12">
                            <div v-for="sub in category.children" :key="sub.id" :id="`sub-${sub.id}`" class="scroll-mt-24">
                                <h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">{{ sub.name }}</h2>
                                
                                <ul v-if="sub.articles && sub.articles.length > 0" class="space-y-4">
                                    <li v-for="article in sub.articles" :key="article.id">
                                        <Link 
                                            :href="route('bantuan.article', article.slug)"
                                            class="text-[15px] text-gray-700 hover:text-[#FFC000] transition-colors block"
                                        >
                                            {{ article.title }}
                                        </Link>
                                    </li>
                                </ul>
                                <div v-else class="text-sm text-gray-400 italic">
                                    Belum ada artikel di sub-kategori ini.
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- If this is a subcategory (has parent_id) -->
                    <template v-else>
                        <div v-if="!category.articles || category.articles.length === 0" class="text-gray-500 italic">
                            Belum ada artikel di sub-kategori ini.
                        </div>
                        <ul v-else class="space-y-4">
                            <li v-for="article in category.articles" :key="article.id">
                                <Link 
                                    :href="route('bantuan.article', article.slug)"
                                    class="text-[15px] text-gray-700 hover:text-[#FFC000] transition-colors block"
                                >
                                    {{ article.title }}
                                </Link>
                            </li>
                        </ul>
                    </template>
                </div>
                
            </div>
        </div>
    </HelpCenterLayout>
</template>
