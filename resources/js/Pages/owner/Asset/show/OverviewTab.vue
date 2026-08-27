<script setup>
import { computed, ref, onMounted } from 'vue';
import { VisXYContainer, VisLine, VisArea, VisAxis, VisCrosshair, VisTooltip, VisStackedBar } from '@unovis/vue';
import { Home, Users, Eye, Star, HelpCircle, BarChart3, ChevronDown, Check, TrendingUp, TrendingDown, MessageSquare, Heart, Laptop, Globe } from 'lucide-vue-next';
import SpreadEmptyIllustration from '@/Components/ui/Icons/SpreadEmptyIllustration.vue';
import EmptyReviewsIcon from '@/Components/ui/Icons/EmptyReviewsIcon.vue';
import NoChatIcon from '@/Components/ui/Icons/NoChatIcon.vue';
import NotFoundIcon from '@/Components/ui/Icons/NotFoundIcon.vue';
import EmptyOsChartIcon from '@/Components/ui/Icons/EmptyOsChartIcon.vue';
import EmptyBrowserChartIcon from '@/Components/ui/Icons/EmptyBrowserChartIcon.vue';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/Components/ui/card';

const props = defineProps({
    asset: {
        type: Object,
        required: true
    },
    totalUnitsCount: {
        type: Number,
        default: 1
    },
    occupiedUnitsCount: {
        type: Number,
        default: 0
    },
    chartData: {
        type: Object,
        default: () => ({})
    },
    ratingDistribution: {
        type: Array,
        default: () => []
    },
    osDistribution: {
        type: Array,
        default: () => []
    },
    browserDistribution: {
        type: Array,
        default: () => []
    }
});

const periods = [
    { value: '7days', label: '7 Hari' },
    { value: '30days', label: '30 Hari' },
    { value: '90days', label: '90 Hari' },
    { value: '1year', label: '365 Hari' }
];

// Independent states for each chart
const selectedViewsPeriod = ref('7days');
const selectedFavPeriod = ref('7days');
const selectedChatPeriod = ref('7days');

const isViewsDropdownOpen = ref(false);
const isFavDropdownOpen = ref(false);
const isChatDropdownOpen = ref(false);

const viewsPeriodLabel = computed(() => periods.find(p => p.value === selectedViewsPeriod.value)?.label || '30 Hari');
const favPeriodLabel = computed(() => periods.find(p => p.value === selectedFavPeriod.value)?.label || '30 Hari');
const chatPeriodLabel = computed(() => periods.find(p => p.value === selectedChatPeriod.value)?.label || '30 Hari');

const activeViewsData = computed(() => props.chartData?.views?.[selectedViewsPeriod.value] || { series: [], current_total: 0, percentage: 0 });
const activeFavData = computed(() => props.chartData?.favorites?.[selectedFavPeriod.value] || { series: [], current_total: 0, percentage: 0 });
const activeChatData = computed(() => props.chartData?.chats?.[selectedChatPeriod.value] || { series: [], current_total: 0, percentage: 0 });

const viewsSeries = computed(() => activeViewsData.value.series);
const favSeries = computed(() => activeFavData.value.series);
const chatSeries = computed(() => activeChatData.value.series);

// Periksa apakah data trafik kosong (berdasarkan data views)
const isChartEmpty = computed(() => {
    if (!viewsSeries.value || viewsSeries.value.length === 0) return true;
    return viewsSeries.value.every(item => item.value === 0);
});

const x = (d, i) => i;
const y = [ (d) => d.value ];

const xTickFormat = (i) => {
    return viewsSeries.value[i]?.name || '';
};

const xTickFormatFav = (i) => {
    return favSeries.value[i]?.name || '';
};

const xTickFormatChat = (i) => {
    return chatSeries.value[i]?.name || '';
};

const occupancyPercentage = computed(() => {
    return Math.round((props.occupiedUnitsCount / props.totalUnitsCount) * 100) || 0;
});

const totalReviewsCount = computed(() => {
    if (!props.ratingDistribution) return 0;
    return props.ratingDistribution.reduce((acc, item) => acc + item.count, 0);
});

const averageRating = computed(() => {
    if (!props.ratingDistribution || totalReviewsCount.value === 0) return 0;
    const totalStars = props.ratingDistribution.reduce((acc, item) => acc + (item.star * item.count), 0);
    return (totalStars / totalReviewsCount.value).toFixed(1);
});

const tooltipTemplate = (d) => `
    <div class="px-3 py-2 bg-white rounded-lg shadow-xl border border-slate-100 min-w-[120px]">
        <div class="text-xs text-slate-500 font-semibold mb-1">${d.full_name}</div>
        <div class="font-black text-slate-900 text-sm flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#FFC000]"></span>
            ${d.value} Kunjungan
        </div>
    </div>
`;

const tooltipTemplateFav = (d) => `
    <div class="px-3 py-2 bg-white rounded-lg shadow-xl border border-slate-100 min-w-[120px]">
        <div class="text-xs text-slate-500 font-semibold mb-1">${d.full_name}</div>
        <div class="font-black text-slate-900 text-sm flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#FFC000]"></span>
            ${d.value} Favorit Baru
        </div>
    </div>
`;

const tooltipTemplateChat = (d) => `
    <div class="px-3 py-2 bg-white rounded-lg shadow-xl border border-slate-100 min-w-[120px]">
        <div class="text-xs text-slate-500 font-semibold mb-1">${d.full_name}</div>
        <div class="font-black text-slate-900 text-sm flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#FFC000]"></span>
            ${d.value} Percakapan
        </div>
    </div>
`;
// Calculate total for percentages
const totalOsCount = computed(() => {
    return props.osDistribution.reduce((sum, item) => sum + item.count, 0);
});

const totalBrowserCount = computed(() => {
    return props.browserDistribution.reduce((sum, item) => sum + item.count, 0);
});

const chartsLoaded = ref(false);
onMounted(() => {
    setTimeout(() => {
        chartsLoaded.value = true;
    }, 200);
});
</script>

<template>
    <div class="flex flex-col gap-6 animate-in fade-in duration-500">

        <!-- STATS OVERVIEW - Elegant Panel Design like Dashboard -->
        <Card class="bg-white border border-slate-200/80 rounded-xl shadow-sm mb-2 relative overflow-hidden">
            <CardContent class="p-0 grid grid-cols-2 xl:grid-cols-4 border-slate-100">


                <!-- Total Unit -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-b xl:border-b-0 border-slate-100 hover:bg-slate-50/50 transition duration-300">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Home class="text-slate-400 w-3.5 h-3.5" /> <span>Total Unit</span>
                        </p>
                        <div class="group/help relative cursor-help">
                            <HelpCircle class="w-3.5 h-3.5 text-slate-300 hover:text-[#FFC000] transition-colors" />
                            <div class="absolute bottom-full left-[-10px] sm:left-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] leading-relaxed rounded-lg opacity-0 group-hover/help:opacity-100 transition-opacity pointer-events-none z-50 shadow-lg text-left font-medium">
                                Keseluruhan jumlah unit / pintu yang tersedia pada aset ini.
                                <div class="absolute top-full left-[14px] sm:left-3 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-slate-900">{{ props.totalUnitsCount }}</p>
                </div>

                <!-- Rating Ulasan -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-b xl:border-b-0 xl:border-r border-slate-100 hover:bg-slate-50/50 transition duration-300">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Star class="text-slate-400 w-3.5 h-3.5" /> <span>Rating Ulasan</span>
                        </p>
                        <div class="group/help relative cursor-help">
                            <HelpCircle class="w-3.5 h-3.5 text-slate-300 hover:text-[#FFC000] transition-colors" />
                            <div class="absolute bottom-full right-[-10px] sm:right-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] leading-relaxed rounded-lg opacity-0 group-hover/help:opacity-100 transition-opacity pointer-events-none z-50 shadow-lg text-left font-medium">
                                Rata-rata penilaian yang diberikan oleh penyewa berdasarkan keseluruhan ulasan.
                                <div class="absolute top-full right-[14px] sm:right-3 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-2xl lg:text-3xl font-black text-slate-900">{{ averageRating }}</p>
                        <p class="text-sm font-semibold text-slate-500 mb-1 lg:mb-1.5 hidden sm:block">
                            ({{ totalReviewsCount }} Ulasan)
                        </p>
                    </div>
                    <div class="flex gap-0.5 text-[#FFC000] mt-1 lg:mt-2 hidden sm:flex">
                        <Star v-for="i in 5" :key="'r' + i" class="w-3.5 h-3.5 lg:w-4 lg:h-4" :class="i <= Math.round(averageRating) ? 'fill-[#FFC000]' : 'text-slate-200 fill-slate-200'" />
                    </div>
                </div>

                <!-- Total Views -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center border-r border-slate-100 hover:bg-slate-50/50 transition duration-300">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Eye class="text-slate-400 w-3.5 h-3.5" /> <span>Kunjungan Profil</span>
                        </p>
                        <div class="group/help relative cursor-help">
                            <HelpCircle class="w-3.5 h-3.5 text-slate-300 hover:text-[#FFC000] transition-colors" />
                            <div class="absolute bottom-full left-[-10px] sm:left-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] leading-relaxed rounded-lg opacity-0 group-hover/help:opacity-100 transition-opacity pointer-events-none z-50 shadow-lg text-left font-medium">
                                Total seberapa sering halaman aset ini dilihat oleh pengguna.
                                <div class="absolute top-full left-[14px] sm:left-3 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-slate-900">{{ activeViewsData.current_total ?? 0 }}</p>
                </div>

                <!-- Favorit -->
                <div class="p-4 lg:p-5 xl:p-6 flex flex-col justify-center hover:bg-slate-50/50 transition duration-300">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-xs text-slate-500 font-medium tracking-wide flex items-center gap-2">
                            <Heart class="text-slate-400 w-3.5 h-3.5" /> <span>Favorit</span>
                        </p>
                        <div class="group/help relative cursor-help">
                            <HelpCircle class="w-3.5 h-3.5 text-slate-300 hover:text-[#FFC000] transition-colors" />
                            <div class="absolute bottom-full right-[-10px] sm:right-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] leading-relaxed rounded-lg opacity-0 group-hover/help:opacity-100 transition-opacity pointer-events-none z-50 shadow-lg text-left font-medium">
                                Jumlah pengguna yang menyimpan (bookmark/favorit) aset Anda untuk dilihat nanti.
                                <div class="absolute top-full right-[14px] sm:right-3 border-4 border-transparent border-t-slate-800"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-2xl lg:text-3xl font-black text-slate-900">{{ props.asset?.favorites_count ?? 0 }}</p>
                </div>
            </CardContent>
        </Card>

        <!-- Main Sections: Chart & Ratings -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6">

            <!-- Chart Section with Elegant Empty State -->
            <Card class="xl:col-span-2 bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl overflow-hidden flex flex-col group relative">
                <CardHeader class="relative z-20 flex flex-col items-stretch p-4 md:p-5 sm:flex-row pb-2">
                    <div class="flex flex-1 flex-col justify-center gap-1 text-left">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-base font-black text-slate-900 tracking-tight">Trafik Pengunjung (Views)</CardTitle>
                                <p class="text-xs text-slate-500 mt-0.5">Statistik pengunjung yang melihat halaman aset Anda.</p>
                            </div>
                            <!-- Custom Period Dropdown for Views -->
                            <div class="relative">
                                <button
                                    @click="isViewsDropdownOpen = !isViewsDropdownOpen"
                                    class="flex items-center justify-between w-[110px] h-9 px-3 text-xs font-semibold bg-white border border-slate-200 rounded-xl shadow-sm hover:border-[#FFC000] focus:outline-none transition-colors"
                                >
                                    <span class="text-slate-700">{{ viewsPeriodLabel }}</span>
                                    <ChevronDown class="w-4 h-4 text-slate-400" />
                                </button>

                                <!-- Backdrop to close dropdown -->
                                <div v-if="isViewsDropdownOpen" @click="isViewsDropdownOpen = false" class="fixed inset-0 z-40"></div>

                                <!-- Dropdown Menu -->
                                <Transition
                                    enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100 translate-y-0"
                                    leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                                >
                                    <div v-if="isViewsDropdownOpen" class="absolute right-0 mt-2 w-[140px] bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 overflow-hidden">
                                        <button
                                            v-for="period in periods" :key="period.value"
                                            @click="selectedViewsPeriod = period.value; isViewsDropdownOpen = false"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium transition-colors hover:bg-[#FFC000]/10"
                                            :class="selectedViewsPeriod === period.value ? 'bg-[#FFC000]/10 text-slate-900 font-bold' : 'text-slate-600'"
                                        >
                                            <div class="w-4 h-4 flex items-center justify-center">
                                                <Check v-if="selectedViewsPeriod === period.value" class="w-3.5 h-3.5 text-slate-900" />
                                            </div>
                                            {{ period.label }}
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </CardHeader>

                <!-- Jika ada data, tampilkan chart -->
                <CardContent v-if="!isChartEmpty" class="p-4 pt-0 flex-1 min-h-[320px] relative z-0">
                    <div class="w-full h-full relative text-xs z-0 transition-opacity duration-300">
                        <VisXYContainer :data="viewsSeries" :height="300" :duration="800" :padding="{ top: 20, right: 10, left: 10, bottom: 0 }">
                            <!-- Area Fill -->
                            <VisArea :x="x" :y="y" color="#FFC000" :opacity="0.2" curveType="monotoneX" />

                            <!-- Line on top -->
                            <VisLine :x="x" :y="y" color="#FFC000" :lineWidth="3" curveType="monotoneX" />

                            <!-- Axes -->
                            <VisAxis type="x" :tickValues="viewsSeries.map((_, i) => i)" :tickFormat="xTickFormat" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-400" />
                            <VisAxis type="y" :tickFormat="(d) => d" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-400" />

                            <!-- Interaction -->
                            <VisTooltip />
                            <VisCrosshair color="#FFC000" :template="tooltipTemplate" />
                        </VisXYContainer>
                    </div>
                </CardContent>

                <!-- EMPTY STATE ELEGANT (Menjual & Mahal) -->
                <CardContent v-else class="flex-1 flex flex-col items-center justify-center py-12 px-4 text-center min-h-[320px] relative z-10 bg-slate-50/30">
                    <div class="w-32 mb-4 pointer-events-none drop-shadow-sm opacity-60 hover:opacity-80 transition-all duration-500 hover:scale-105 transform">
                        <SpreadEmptyIllustration class="w-full h-auto text-slate-300 drop-shadow-xl" />
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-2">Belum Ada Data Trafik</h3>
                    <p class="text-sm text-slate-500 max-w-sm mb-6 leading-relaxed font-medium">
                        Aset Anda belum mendapatkan kunjungan baru-baru ini. Tingkatkan daya tarik aset dengan melengkapi foto dan detail fasilitas!
                    </p>
                </CardContent>
            </Card>

            <!-- Rating Distribution Section -->
            <Card class="bg-white border border-slate-200/60 shadow-sm hover:shadow-md transition-all rounded-xl flex flex-col group">
                <CardHeader class="p-4 md:p-5 pb-0 mb-6">
                    <div>
                        <CardTitle class="text-base font-black text-slate-900 tracking-tight">Distribusi Ulasan</CardTitle>
                        <p class="text-xs text-slate-500 mt-0.5">Rincian tingkat kepuasan penyewa.</p>
                    </div>
                </CardHeader>

                <CardContent v-if="totalReviewsCount > 0" class="p-4 md:p-5 pt-0 flex-1 flex flex-col">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="text-4xl lg:text-5xl font-black text-slate-900 tracking-tighter">{{ averageRating }}</div>
                        <div class="flex flex-col">
                            <div class="flex gap-0.5 text-[#FFC000] mb-1">
                                <Star v-for="i in 5" :key="i" class="w-4 h-4 lg:w-5 lg:h-5" :class="i <= Math.round(averageRating) ? 'fill-[#FFC000]' : 'text-slate-200 fill-slate-200'" />
                            </div>
                            <div class="text-xs text-slate-500 font-semibold">{{ totalReviewsCount }} Total Ulasan</div>
                        </div>
                    </div>

                    <div class="space-y-3.5 mt-auto">
                        <div v-for="rating in props.ratingDistribution" :key="rating.star" class="flex items-center gap-3 text-sm group/bar cursor-default">
                            <div class="flex items-center gap-1.5 w-9 text-slate-600 font-bold">
                                {{ rating.star }} <Star class="w-3.5 h-3.5 text-slate-400 group-hover/bar:text-[#FFC000] transition-colors" :class="{'fill-[#FFC000] text-[#FFC000]': rating.count > 0}" />
                            </div>
                            <div class="flex-1 h-3 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                <div class="h-full bg-[#FFC000] rounded-full transition-all duration-1000 ease-out" :style="{ width: rating.percentage + '%' }"></div>
                            </div>
                            <div class="w-10 text-right text-xs font-bold text-slate-600 group-hover/bar:text-slate-900 transition-colors">{{ rating.percentage }}%</div>
                        </div>
                    </div>
                </CardContent>

                <CardContent v-else class="p-4 md:p-5 pt-0 flex-1 flex flex-col items-center justify-center text-center py-6">
                    <div class="w-32 mb-4 pointer-events-none drop-shadow-sm opacity-60 hover:opacity-80 transition-all duration-500 hover:scale-105 transform">
                        <EmptyReviewsIcon class="w-full h-auto text-slate-300 drop-shadow-xl" />
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-1">Belum Ada Ulasan</h3>
                    <p class="text-xs text-slate-500 max-w-[200px] leading-relaxed font-medium">
                        Aset ini belum menerima ulasan dari penyewa manapun.
                    </p>
                </CardContent>
            </Card>

            <!-- Custom Metric Cards: Favorites & OS & Chats & Browser -->
            <div class="xl:col-span-3 grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6 mb-6">
                <!-- Favorit Chart -->
                <Card class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col animate-in fade-in slide-in-from-bottom-4 duration-700 delay-150 fill-mode-both">
                    <!-- Header -->
                    <CardHeader class="relative">
                        <CardTitle class="text-base font-semibold leading-none tracking-tight text-slate-900">Kinerja Favorit</CardTitle>
                        <p class="text-sm text-slate-500 mt-1">Total pengguna yang memfavoritkan aset.</p>
                        <!-- Filter -->
                        <div class="absolute right-6 top-6">
                            <div class="relative z-20">
                                <button
                                    @click="isFavDropdownOpen = !isFavDropdownOpen"
                                    class="flex items-center justify-between w-[95px] h-8 px-2 text-[10px] font-semibold bg-white border border-slate-200 rounded-md shadow-sm hover:border-[#FFC000] focus:outline-none transition-colors"
                                >
                                    <span class="text-slate-700">{{ favPeriodLabel }}</span>
                                    <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
                                </button>
                                <div v-if="isFavDropdownOpen" @click="isFavDropdownOpen = false" class="fixed inset-0 z-40"></div>
                                <Transition
                                    enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100 translate-y-0"
                                    leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                                >
                                    <div v-if="isFavDropdownOpen" class="absolute right-0 mt-1 w-[120px] bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50 overflow-hidden">
                                        <button
                                            v-for="period in periods" :key="period.value"
                                            @click="selectedFavPeriod = period.value; isFavDropdownOpen = false"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-[11px] font-medium transition-colors hover:bg-[#FFC000]/10"
                                            :class="selectedFavPeriod === period.value ? 'bg-[#FFC000]/10 text-slate-900 font-bold' : 'text-slate-600'"
                                        >
                                            <div class="w-3 h-3 flex items-center justify-center">
                                                <Check v-if="selectedFavPeriod === period.value" class="w-3 h-3 text-slate-900" />
                                            </div>
                                            {{ period.label }}
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </CardHeader>

                    <!-- Content -->
                    <CardContent v-if="activeFavData.current_total > 0" class="flex-1">
                        <div class="w-full h-[200px] relative text-xs z-0">
                            <VisXYContainer :data="favSeries" :height="200" :duration="1000" :padding="{ top: 10, right: 10, left: 10, bottom: 0 }">
                                <VisArea :x="x" :y="y" color="#FFC000" :opacity="0.2" curveType="monotoneX" />
                                <VisLine :x="x" :y="y" color="#FFC000" :lineWidth="2" curveType="monotoneX" />
                                <VisAxis type="x" :tickValues="favSeries.map((_, i) => i)" :tickFormat="xTickFormatFav" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-500 text-[10px]" />
                                <VisAxis type="y" :tickFormat="(d) => d" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-500 text-[10px]" />
                                <VisCrosshair color="#FFC000" :template="tooltipTemplateFav" />
                                <VisTooltip />
                            </VisXYContainer>
                        </div>
                    </CardContent>
                    <CardContent v-else class="flex-1 flex flex-col items-center justify-center py-8 px-4 text-center min-h-[280px] relative z-10 bg-slate-50/30">
                        <div class="w-24 mb-4 pointer-events-none drop-shadow-sm opacity-60 hover:opacity-80 transition-all duration-500 hover:scale-105 transform">
                            <NotFoundIcon class="w-full h-auto text-slate-300 drop-shadow-xl" />
                        </div>
                        <h3 class="text-base font-black text-slate-900 mb-2">Belum Ada Favorit</h3>
                        <p class="text-xs text-slate-500 max-w-[280px] mb-5 leading-relaxed font-medium">
                            Buat penawaran menarik atau perbarui foto aset untuk memikat penyewa!
                        </p>
                    </CardContent>

                    <!-- Footer -->
                    <CardFooter class="mt-auto">
                        <div class="flex w-full items-start gap-2 text-sm mt-2">
                            <div class="grid gap-1.5">
                                <div class="flex items-center gap-2 leading-none text-slate-500">
                                    Total {{ activeFavData.current_total }} untuk periode {{ favPeriodLabel }}
                                </div>
                            </div>
                        </div>
                    </CardFooter>
                </Card>

                <!-- OS Chart -->
                <Card class="xl:col-span-1 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col animate-in fade-in slide-in-from-bottom-4 duration-700 delay-300 fill-mode-both">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold leading-none tracking-tight text-slate-900">Sistem Operasi (OS)</CardTitle>
                        <p class="text-sm text-slate-500 mt-1">Perangkat yang digunakan pengunjung.</p>
                    </CardHeader>
                    <CardContent class="flex-1 flex flex-col justify-center">
                        <div v-if="props.osDistribution.length > 0" class="flex flex-col gap-4">
                            <div v-for="(item, index) in props.osDistribution" :key="index" class="w-full flex items-center h-[32px] gap-2 group cursor-default">
                                <div class="flex-1 h-full flex items-center bg-transparent">
                                    <div class="bg-[#FFC000] h-full rounded flex items-center px-2.5 transition-all duration-1000 ease-out min-w-max max-w-full relative" 
                                         :style="{ width: chartsLoaded ? `${(item.count / totalOsCount) * 100}%` : '0%' }">
                                        <span class="text-[11px] font-semibold text-white drop-shadow-sm whitespace-nowrap overflow-hidden text-ellipsis">{{ item.name }}</span>
                                    </div>
                                </div>
                                <div class="min-w-[20px] text-right">
                                    <span class="text-xs font-semibold text-slate-700">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex-1 flex flex-col items-center justify-center py-8 text-center bg-slate-50/30">
                            <div class="w-32 mb-4 pointer-events-none drop-shadow-sm opacity-60 hover:opacity-80 transition-all duration-500 hover:scale-105 transform">
                                <EmptyOsChartIcon class="w-full h-auto text-slate-300 drop-shadow-xl" />
                            </div>
                            <h3 class="text-base font-black text-slate-900 mb-2">Belum Ada Data OS</h3>
                            <p class="text-xs text-slate-500 max-w-[280px] mb-5 leading-relaxed font-medium">
                                Data sistem operasi akan muncul setelah ada pengunjung baru.
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter class="mt-auto">
                        <div class="flex items-center gap-2 text-xs font-medium text-slate-500 w-full mt-4">
                            Total {{ totalOsCount }} rekaman pengunjung (All time)
                        </div>
                    </CardFooter>
                </Card>

                <!-- Chat Chart -->
                <Card class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col animate-in fade-in slide-in-from-bottom-4 duration-700 delay-500 fill-mode-both">
                    <!-- Header -->
                    <CardHeader class="relative">
                        <CardTitle class="text-base font-semibold leading-none tracking-tight text-slate-900">Interaksi Chat</CardTitle>
                        <p class="text-sm text-slate-500 mt-1">Total percakapan baru dengan pengguna.</p>
                        <!-- Filter -->
                        <div class="absolute right-6 top-6">
                            <div class="relative z-20">
                                <button
                                    @click="isChatDropdownOpen = !isChatDropdownOpen"
                                    class="flex items-center justify-between w-[95px] h-8 px-2 text-[10px] font-semibold bg-white border border-slate-200 rounded-md shadow-sm hover:border-[#FFC000] focus:outline-none transition-colors"
                                >
                                    <span class="text-slate-700">{{ chatPeriodLabel }}</span>
                                    <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
                                </button>
                                <div v-if="isChatDropdownOpen" @click="isChatDropdownOpen = false" class="fixed inset-0 z-40"></div>
                                <Transition
                                    enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100 translate-y-0"
                                    leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                                >
                                    <div v-if="isChatDropdownOpen" class="absolute right-0 mt-1 w-[120px] bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50 overflow-hidden">
                                        <button
                                            v-for="period in periods" :key="period.value"
                                            @click="selectedChatPeriod = period.value; isChatDropdownOpen = false"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-[11px] font-medium transition-colors hover:bg-[#FFC000]/10"
                                            :class="selectedChatPeriod === period.value ? 'bg-[#FFC000]/10 text-slate-900 font-bold' : 'text-slate-600'"
                                        >
                                            <div class="w-3 h-3 flex items-center justify-center">
                                                <Check v-if="selectedChatPeriod === period.value" class="w-3 h-3 text-slate-900" />
                                            </div>
                                            {{ period.label }}
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </CardHeader>

                    <!-- Content -->
                    <CardContent v-if="activeChatData.current_total > 0" class="flex-1">
                        <div class="w-full h-[200px] relative text-xs z-0">
                            <VisXYContainer :data="chatSeries" :height="200" :duration="1000" :padding="{ top: 10, right: 10, left: 10, bottom: 0 }">
                                <VisStackedBar :x="x" :y="y" color="#FFC000" :roundedCorners="4" :barPadding="0.3" />
                                <VisAxis type="x" :tickValues="chatSeries.map((_, i) => i)" :tickFormat="xTickFormatChat" :gridLine="false" :tickLine="false" :domainLine="false" class="text-slate-500 text-[10px]" />
                                <VisAxis type="y" :tickFormat="(d) => d" :gridLine="true" :tickLine="false" :domainLine="false" class="text-slate-500 text-[10px]" />
                                <VisCrosshair color="#FFC000" :template="tooltipTemplateChat" />
                                <VisTooltip />
                            </VisXYContainer>
                        </div>
                    </CardContent>
                    <CardContent v-else class="flex-1 flex flex-col items-center justify-center py-8 px-4 text-center min-h-[280px] relative z-10 bg-slate-50/30">
                        <div class="w-32 mb-4 pointer-events-none drop-shadow-sm opacity-60 hover:opacity-80 transition-all duration-500 hover:scale-105 transform">
                            <NoChatIcon class="w-full h-auto text-slate-300 drop-shadow-xl" />
                        </div>
                        <h3 class="text-base font-black text-slate-900 mb-2">Belum Ada Percakapan</h3>
                        <p class="text-xs text-slate-500 max-w-[280px] mb-5 leading-relaxed font-medium">
                            Berikan promo khusus atau deskripsi yang jelas agar penyewa tertarik bertanya!
                        </p>
                    </CardContent>

                    <!-- Footer -->
                    <CardFooter class="mt-auto">
                        <div class="flex w-full items-start gap-2 text-sm mt-2">
                            <div class="grid gap-1.5">
                                <div class="flex items-center gap-2 leading-none text-slate-500">
                                    Total {{ activeChatData.current_total }} untuk periode {{ chatPeriodLabel }}
                                </div>
                            </div>
                        </div>
                    </CardFooter>
                </Card>

                <!-- Browser Chart -->
                <Card class="xl:col-span-1 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col animate-in fade-in slide-in-from-bottom-4 duration-700 delay-700 fill-mode-both">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold leading-none tracking-tight text-slate-900">Browser Pengunjung</CardTitle>
                        <p class="text-sm text-slate-500 mt-1">Browser web yang digunakan pengunjung.</p>
                    </CardHeader>
                    <CardContent class="flex-1 flex flex-col justify-center">
                        <div v-if="props.browserDistribution.length > 0" class="flex flex-col gap-4">
                            <div v-for="(item, index) in props.browserDistribution" :key="index" class="w-full flex items-center h-[32px] gap-2 group cursor-default">
                                <div class="flex-1 h-full flex items-center bg-transparent">
                                    <div class="bg-[#FFC000] h-full rounded flex items-center px-2.5 transition-all duration-1000 ease-out min-w-max max-w-full relative" 
                                         :style="{ width: chartsLoaded ? `${(item.count / totalBrowserCount) * 100}%` : '0%' }">
                                        <span class="text-[11px] font-semibold text-white drop-shadow-sm whitespace-nowrap overflow-hidden text-ellipsis">{{ item.name }}</span>
                                    </div>
                                </div>
                                <div class="min-w-[20px] text-right">
                                    <span class="text-xs font-semibold text-slate-700">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex-1 flex flex-col items-center justify-center py-8 text-center bg-slate-50/30">
                            <div class="w-32 mb-4 pointer-events-none drop-shadow-sm opacity-60 hover:opacity-80 transition-all duration-500 hover:scale-105 transform">
                                <EmptyBrowserChartIcon class="w-full h-auto text-slate-300 drop-shadow-xl" />
                            </div>
                            <h3 class="text-base font-black text-slate-900 mb-2">Belum Ada Data Browser</h3>
                            <p class="text-xs text-slate-500 max-w-[280px] mb-5 leading-relaxed font-medium">
                                Data browser web akan muncul setelah ada pengunjung baru.
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter class="mt-auto">
                        <div class="flex items-center gap-2 text-xs font-medium text-slate-500 w-full mt-4">
                            Total {{ totalBrowserCount }} rekaman pengunjung (All time)
                        </div>
                    </CardFooter>
                </Card>
            </div>

        </div>
    </div>
</template>

<style>
/* Unovis styling override for elegance */
:root {
  --vis-primary-color: #FFC000;
  --vis-text-color: #94a3b8;
  --vis-axis-grid-color: #f1f5f9;
}
.unovis-chart-container path {
    filter: drop-shadow(0px 4px 6px rgba(255, 192, 0, 0.3));
    transition: all 0.5s ease;
}
</style>
