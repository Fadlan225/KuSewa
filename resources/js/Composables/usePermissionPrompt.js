import { ref } from 'vue';

const isModalOpen = ref(false);
const promptType = ref('location'); // 'location' or 'notification'
let modalResolve = null;

export function usePermissionPrompt() {
    const requestLocationPermission = async (force = false) => {
        try {
            if (navigator.permissions && navigator.permissions.query) {
                const status = await navigator.permissions.query({ name: 'geolocation' });
                // Jika sudah diberi izin dari browser, langsung return true tanpa memunculkan modal kita
                if (status.state === 'granted') return true;
                // Jika sudah diblokir dari browser, langsung return false
                if (status.state === 'denied') return false;
            }
        } catch (e) {
            // Abaikan jika browser tidak support API navigator.permissions
        }

        // Jika user sebelumnya sudah klik "Nanti Saja" di sesi ini, jangan ganggu lagi kecuali dia memaksa
        if (!force && sessionStorage.getItem('locationPromptDismissed') === 'true') {
            return false;
        }

        promptType.value = 'location';
        isModalOpen.value = true;
        
        return new Promise((resolve) => {
            modalResolve = resolve;
        });
    };

    const requestNotificationPermission = async (force = false) => {
        if (!('Notification' in window)) return false;
        
        if (Notification.permission === 'granted') return true;
        if (Notification.permission === 'denied') return false;

        if (!force && sessionStorage.getItem('notificationPromptDismissed') === 'true') {
            return false;
        }

        promptType.value = 'notification';
        isModalOpen.value = true;

        return new Promise((resolve) => {
            modalResolve = resolve;
        });
    };

    const handleAllow = () => {
        isModalOpen.value = false;

        if (promptType.value === 'notification') {
            // Trigger native notification prompt directly in the click event
            Notification.requestPermission().then((permission) => {
                if (modalResolve) {
                    modalResolve(permission === 'granted');
                    modalResolve = null;
                }
            });
        } else {
            // Trigger native location prompt directly in the click event
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    () => {
                        if (modalResolve) {
                            modalResolve(true);
                            modalResolve = null;
                        }
                    },
                    () => {
                        if (modalResolve) {
                            modalResolve(false);
                            modalResolve = null;
                        }
                    },
                    { timeout: 10000 } // Add timeout to not hang forever
                );
            } else {
                if (modalResolve) {
                    modalResolve(false);
                    modalResolve = null;
                }
            }
        }
    };

    const handleDeny = () => {
        if (promptType.value === 'location') {
            sessionStorage.setItem('locationPromptDismissed', 'true');
        } else {
            sessionStorage.setItem('notificationPromptDismissed', 'true');
        }
        
        isModalOpen.value = false;
        if (modalResolve) {
            modalResolve(false);
            modalResolve = null;
        }
    };

    return {
        isModalOpen,
        promptType,
        requestLocationPermission,
        requestNotificationPermission,
        handleAllow,
        handleDeny
    };
}
