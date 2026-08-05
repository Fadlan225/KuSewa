import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthModalStore = defineStore('authModal', () => {
    const isOpen = ref(false);
    const initialStep = ref(null);
    const initialData = ref({});

    const open = (step = null, data = {}) => {
        initialStep.value = step;
        initialData.value = data;
        isOpen.value = true;
    };

    const close = () => {
        isOpen.value = false;
        setTimeout(() => {
            initialStep.value = null;
            initialData.value = {};
        }, 300);
    };

    return { isOpen, initialStep, initialData, open, close };
});
