<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-[#FFC000] focus:ring-[#FFC000] focus:outline-none focus:ring-2 focus:ring-[#FFC000]"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        v-bind="$attrs"
    />
</template>
