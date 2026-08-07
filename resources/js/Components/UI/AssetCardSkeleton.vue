<script setup>
defineProps({
    layout: {
        type: String,
        default: 'vertical'
    },
    isOwner: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div
        v-if="layout === 'vertical'"
        :class="[
            'flex-none snap-start flex flex-col animate-pulse rounded-xl shadow-sm border border-gray-100 bg-white overflow-hidden',
            isOwner ? 'w-full' : 'w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px]'
        ]"
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
        class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-row p-2.5 md:p-3 items-center gap-3 md:gap-4 w-full animate-pulse"
    >
        <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-200 rounded-lg shrink-0 relative overflow-hidden">
            <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
        </div>
        <div class="flex-1 space-y-2 py-1">
            <div class="h-3 bg-slate-200 rounded w-16"></div>
            <div class="h-4 bg-slate-200 rounded w-3/4"></div>
            <div class="h-3 bg-slate-200 rounded w-1/2"></div>
        </div>
        <div class="shrink-0 flex flex-col items-end gap-2 py-1">
            <div class="h-4 bg-slate-200 rounded w-16"></div>
            <div class="h-6 w-6 bg-slate-200 rounded-full mt-2"></div>
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
