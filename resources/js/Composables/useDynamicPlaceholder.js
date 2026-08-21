import { ref, onMounted, onUnmounted } from 'vue';

export function useDynamicPlaceholder(placeholders = [], intervalMs = 3000) {
    const defaultPlaceholders = [
        "Mau sewa apa hari ini?"
    ];

    const actualPlaceholders = placeholders && placeholders.length > 0 
        ? placeholders 
        : defaultPlaceholders;

    const currentIndex = ref(0);
    const currentPlaceholder = ref(actualPlaceholders[0]);
    let intervalId = null;

    const startInterval = () => {
        if (actualPlaceholders.length <= 1) return;
        
        intervalId = setInterval(() => {
            currentIndex.value = (currentIndex.value + 1) % actualPlaceholders.length;
            currentPlaceholder.value = actualPlaceholders[currentIndex.value];
        }, intervalMs);
    };

    const stopInterval = () => {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };

    onMounted(() => {
        startInterval();
    });

    onUnmounted(() => {
        stopInterval();
    });

    return {
        currentPlaceholder,
        currentIndex,
        placeholders: actualPlaceholders,
        startInterval,
        stopInterval
    };
}
