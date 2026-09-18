import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Singleton state — kondisi notifikasi dishare seluruh komponen
const notifications = ref([]);
const unreadCount = ref(0);
const isLoading = ref(false);
const isDropdownOpen = ref(false);
let initialized = false;

export function useNotifications() {
    /**
     * Ambil notifikasi dari server
     */
    const fetchNotifications = async (onlyUnread = false) => {
        isLoading.value = true;
        try {
            const res = await axios.get('/api/notifications', {
                params: { unread: onlyUnread ? 1 : 0, per_page: 15 }
            });
            notifications.value = res.data.data;
            unreadCount.value = res.data.unread_count;
        } catch (e) {
            console.error('Gagal mengambil notifikasi', e);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Tandai satu notifikasi sudah dibaca
     */
    const markAsRead = async (notificationId) => {
        try {
            await axios.post(`/api/notifications/${notificationId}/mark-as-read`);
            const item = notifications.value.find(n => n.id === notificationId);
            if (item && !item.read_at) {
                item.read_at = new Date().toISOString();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        } catch (e) {
            console.error('Gagal menandai notifikasi', e);
        }
    };

    /**
     * Tandai semua notifikasi sudah dibaca
     */
    const markAllAsRead = async () => {
        try {
            await axios.post('/api/notifications/mark-all-as-read');
            notifications.value.forEach(n => {
                if (!n.read_at) n.read_at = new Date().toISOString();
            });
            unreadCount.value = 0;
        } catch (e) {
            console.error('Gagal menandai semua notifikasi', e);
        }
    };

    /**
     * Tambahkan notifikasi baru (dari WebSocket/Broadcast) ke list
     */
    const addNewNotification = (notification) => {
        notifications.value.unshift(notification);
        unreadCount.value++;
    };

    /**
     * Inisialisasi awal: ambil unread count dari Inertia Shared Data
     */
    const init = () => {
        if (initialized) return;
        initialized = true;
        
        try {
            const page = usePage();
            if (page.props.auth?.badges) {
                unreadCount.value = page.props.auth.badges.total || 0;
            }

            // Sync dengan Inertia jika navigasi terjadi
            document.addEventListener('inertia:success', () => {
                if (page.props.auth?.badges) {
                    // Update hanya jika kita tidak sedang membuka dropdown
                    if (!isDropdownOpen.value) {
                        unreadCount.value = page.props.auth.badges.total || 0;
                    }
                }
            });
        } catch (e) {
            console.error('Gagal membaca initial badge data', e);
        }
    };

    const hasUnread = computed(() => unreadCount.value > 0);

    return {
        notifications,
        unreadCount,
        isLoading,
        isDropdownOpen,
        hasUnread,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        addNewNotification,
        init,
    };
}
