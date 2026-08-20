import { ref } from 'vue';

const isLocationModalOpen = ref(false);
let locationModalResolve = null;

export function useLocationPermission() {
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

        // Jika user sebelumnya sudah klik "Nanti Saja" di sesi ini, jangan ganggu lagi kecuali dia memaksa (klik tombol lokasi)
        if (!force && sessionStorage.getItem('locationPromptDismissed') === 'true') {
            return false;
        }

        isLocationModalOpen.value = true;
        
        return new Promise((resolve) => {
            locationModalResolve = resolve;
        });
    };

    const handleLocationAllow = () => {
        isLocationModalOpen.value = false;
        if (locationModalResolve) {
            locationModalResolve(true);
            locationModalResolve = null;
        }
    };

    const handleLocationDeny = () => {
        sessionStorage.setItem('locationPromptDismissed', 'true');
        isLocationModalOpen.value = false;
        if (locationModalResolve) {
            locationModalResolve(false);
            locationModalResolve = null;
        }
    };

    return {
        isLocationModalOpen,
        requestLocationPermission,
        handleLocationAllow,
        handleLocationDeny
    };
}
