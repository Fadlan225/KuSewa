import { watch, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useFaviconBadge() {
    const page = usePage();
    const originalFavicon = ref(null);
    let canvas = null;
    let ctx = null;
    let img = null;

    const updateFavicon = (unreadCount) => {
        if (typeof window === 'undefined') return;

        const link = document.querySelector("link[rel~='icon']");
        if (!link) return;

        if (!originalFavicon.value) {
            originalFavicon.value = link.href;
        }

        if (unreadCount <= 0) {
            link.href = originalFavicon.value;
            return;
        }

        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.width = 32;
            canvas.height = 32;
            ctx = canvas.getContext('2d');
        }

        if (!img) {
            img = new Image();
            img.crossOrigin = "Anonymous";
            img.src = originalFavicon.value;
            img.onload = () => {
                drawBadge(unreadCount, link);
            };
        } else {
            drawBadge(unreadCount, link);
        }
    };

    const drawBadge = (unreadCount, link) => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, 32, 32);

        // Draw red dot
        ctx.beginPath();
        ctx.arc(26, 6, 6, 0, 2 * Math.PI);
        ctx.fillStyle = '#EF144A';
        ctx.fill();

        // Update favicon
        link.href = canvas.toDataURL('image/png');
    };

    onMounted(() => {
        watch(
            () => page.props.auth?.unreadCount,
            (newCount) => {
                updateFavicon(newCount || 0);
            },
            { immediate: true }
        );
    });
}
