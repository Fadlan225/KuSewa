<script setup>
defineProps({
    layout: {
        type: String,
        default: 'vertical'
    }
});
</script>

<template>
    <div
        v-if="layout === 'vertical'"
        class="flex-none w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px] snap-start flex flex-col animate-pulse rounded-2xl shadow-md bg-white overflow-hidden"
    >
        <div class="aspect-[3/2] w-full bg-gray-200 relative overflow-hidden">
            <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
        </div>
        <div class="flex flex-col gap-2 p-3 sm:p-4">
            <div class="h-4 rounded bg-gray-200 w-full"></div>
            <div class="h-3 rounded bg-gray-200 w-2/3"></div>
            <div class="h-4 rounded bg-gray-200 w-1/2 mt-2"></div>
        </div>
    </div>

    <div
        v-else
        class="w-full snap-start flex flex-row animate-pulse rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden"
    >
        <div class="w-[120px] sm:w-[200px] lg:w-[240px] flex-shrink-0 aspect-[4/3] sm:aspect-[3/2] bg-gray-200 relative overflow-hidden">
            <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
        </div>
        <div class="flex flex-col flex-grow p-3 sm:p-4 gap-3 bg-white sm:flex-row">
            <div class="flex flex-col flex-grow justify-between gap-1.5">
                <div class="h-5 rounded bg-gray-200 w-3/4"></div>
                <div class="h-4 rounded bg-gray-200 w-1/3 mt-1"></div>
                <div class="h-3 rounded bg-gray-200 w-1/2 mt-2"></div>
            </div>
            <div class="sm:w-[200px] flex flex-col justify-end sm:justify-between sm:border-l border-gray-100 sm:pl-4 mt-2 sm:mt-0 pt-2 sm:pt-0 border-t sm:border-t-0">
                <div class="h-5 rounded bg-gray-200 w-1/2 ml-auto sm:ml-0"></div>
                <div class="h-8 rounded bg-gray-200 w-full sm:w-24 ml-auto mt-2 sm:mt-0"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    100% { transform: translateX(100%); }
}
.animate-shimmer {
    animation: shimmer 1.5s infinite;
}
</style>
