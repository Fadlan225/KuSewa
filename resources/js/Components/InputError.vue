<script setup>
import { AlertCircle } from 'lucide-vue-next';
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <p v-if="message" class="flex items-center gap-1.5 text-xs text-red-500 font-medium">
        <AlertCircle class="text-[10px]" />
        {{ message }}
    </p>
</template>
