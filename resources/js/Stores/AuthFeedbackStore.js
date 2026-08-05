import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthFeedbackStore = defineStore('authFeedback', () => {
    const isOpen = ref(false);
    const type = ref('success'); // success, error, info, warning
    const title = ref('');
    const message = ref('');
    const illustration = ref('/secure.svg'); // Always use secure.svg
    const autoClose = ref(true);
    const duration = ref(1800);

    let resolvePromise = null;
    let timer = null;

    const show = (config = {}) => {
        return new Promise((resolve) => {
            type.value = config.type || 'success';
            title.value = config.title || '';
            message.value = config.message || '';
            illustration.value = '/secure.svg'; // Hardcoded per user request
            autoClose.value = config.autoClose !== undefined ? config.autoClose : (type.value === 'success' ? true : false);
            duration.value = config.duration || 1800;

            isOpen.value = true;
            resolvePromise = resolve;

            if (timer) clearTimeout(timer);

            if (autoClose.value) {
                timer = setTimeout(() => {
                    close();
                }, duration.value);
            }
        });
    };

    const close = () => {
        isOpen.value = false;
        if (timer) clearTimeout(timer);
        
        // Wait for transition animation to finish (250ms)
        setTimeout(() => {
            if (resolvePromise) {
                resolvePromise();
                resolvePromise = null;
            }
        }, 300);
    };

    const showSuccess = (config) => show({ ...config, type: 'success' });
    const showError = (config) => show({ ...config, type: 'error', autoClose: config.autoClose !== undefined ? config.autoClose : false });
    const showInfo = (config) => show({ ...config, type: 'info' });
    const showWarning = (config) => show({ ...config, type: 'warning' });

    return { 
        isOpen, type, title, message, illustration, autoClose, duration, 
        show, showSuccess, showError, showInfo, showWarning, close 
    };
});
