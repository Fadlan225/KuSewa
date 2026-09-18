<script setup>
defineProps({
    checked: Boolean,
    modelValue: [Boolean, String, Number, Array],
    value: [Boolean, String, Number],
});

defineEmits(['update:checked', 'update:modelValue']);
</script>

<template>
    <input
        type="checkbox"
        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-[#FFC000] focus:outline-none focus:ring-2 focus:ring-[#FFC000]"
        :checked="checked ?? modelValue"
        :value="value"
        @change="$emit('update:checked', $event.target.checked); $emit('update:modelValue', $event.target.checked)"
        v-bind="$attrs"
    />
</template>
