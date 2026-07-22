import { ref } from 'vue';

const isOpen = ref(false);
const view = ref('login'); // 'login', 'register', 'forgot-password'

export function useMobileAuth() {
    const openAuth = (targetView = 'login') => {
        view.value = targetView;
        isOpen.value = true;
    };

    const closeAuth = () => {
        isOpen.value = false;
    };

    const switchView = (targetView) => {
        view.value = targetView;
    };

    return {
        isOpen,
        view,
        openAuth,
        closeAuth,
        switchView,
    };
}
