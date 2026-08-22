<script setup>
import AvatarFemale from './AvatarFemale.vue';
import AvatarMale from './AvatarMale.vue';
import AvatarDefault from './AvatarDefault.vue';

const props = defineProps({
    user: Object,
});
</script>

<template>
    <AvatarFemale v-if="user?.gender === 'female'" class="w-full h-full" />
    <AvatarMale v-else-if="user?.gender === 'male'" class="w-full h-full" />
    <AvatarDefault v-else class="w-full h-full" />
</template>
