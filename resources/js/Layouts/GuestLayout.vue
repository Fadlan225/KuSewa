<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: 'KitaSewa',
    },
});
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA]">
        <Head :title="title" />

        <div class="min-h-screen flex items-center justify-center px-4 py-10 sm:px-6">
            <div class="w-full max-w-6xl">
                <slot />
            </div>
        </div>
    </div>
</template>
