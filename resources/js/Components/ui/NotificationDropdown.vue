<script setup>
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bell, Check, CheckCheck, Loader2, ChevronRight } from 'lucide-vue-next';
import { useNotifications } from '@/Composables/useNotifications';

const { notifications, unreadCount, isLoading, isDropdownOpen, fetchNotifications, markAsRead, markAllAsRead } = useNotifications();

const emit = defineEmits(['close']);

onMounted(async () => {
    await fetchNotifications();
});

const handleNotificationClick = async (notification) => {
    if (!notification.read_at) {
        await markAsRead(notification.id);
    }
    emit('close');
};

// Format waktu relatif
const formatTime = (dateString) => {
    if (!dateString) return '';
    const now = new Date();
    const date = new Date(dateString);
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays < 7) return `${diffDays} hari lalu`;
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};
</script>

<template>
    <div class="flex flex-col max-h-[480px] w-[340px] sm:w-[380px]">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3.5 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-[#0A2540] text-sm">Notifikasi</h3>
                <span v-if="unreadCount > 0" class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </div>
            <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="flex items-center gap-1 text-[11px] text-[#466080] hover:text-[#0A2540] font-semibold transition-colors"
                title="Tandai semua sudah dibaca"
            >
                <CheckCheck class="w-3.5 h-3.5" />
                Tandai semua dibaca
            </button>
        </div>

        <!-- List Notifikasi -->
        <div class="overflow-y-auto flex-1 hide-scrollbar">
            <!-- Loading -->
            <div v-if="isLoading" class="flex items-center justify-center py-10">
                <Loader2 class="w-5 h-5 animate-spin text-[#6C757D]" />
            </div>

            <!-- Empty State -->
            <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-10 px-4 text-center">
                <Bell class="w-10 h-10 text-gray-200 mb-3" />
                <p class="text-sm font-semibold text-gray-400">Belum ada notifikasi</p>
                <p class="text-xs text-gray-300 mt-1">Kami akan memberitahu kamu aktivitas penting di sini.</p>
            </div>

            <!-- List -->
            <template v-else>
                <component
                    :is="n.data?.action_url ? Link : 'div'"
                    v-for="n in notifications.slice(0, 8)"
                    :key="n.id"
                    :href="n.data?.action_url || undefined"
                    @click="handleNotificationClick(n)"
                    class="flex gap-3 px-4 py-3.5 cursor-pointer transition-colors hover:bg-gray-50 border-b border-gray-50 last:border-0"
                    :class="!n.read_at ? 'bg-blue-50/40 hover:bg-blue-50/60' : ''"
                >
                    <!-- Logo / Ikon -->
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-9 h-9 rounded-full bg-[#FFC000]/10 flex items-center justify-center">
                            <img src="/kitasewa-logo.png" alt="KitaSewa" class="w-6 h-6 object-contain" />
                        </div>
                    </div>

                    <!-- Konten -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-[#0A2540] leading-snug line-clamp-1">
                            {{ n.data?.title }}
                        </p>
                        <p class="text-xs text-[#6C757D] mt-0.5 leading-relaxed line-clamp-2">
                            {{ n.data?.message }}
                        </p>
                        <p class="text-[10px] text-gray-400 mt-1 font-medium">
                            {{ formatTime(n.created_at) }}
                        </p>
                    </div>

                    <!-- Indikator belum dibaca -->
                    <div class="flex-shrink-0 flex items-start pt-1.5">
                        <div v-if="!n.read_at" class="w-2 h-2 rounded-full bg-[#FFC000]"></div>
                        <div v-else class="w-2 h-2"></div>
                    </div>
                </component>
            </template>
        </div>

        <!-- Footer: Lihat Semua -->
        <div class="border-t border-gray-100 flex-shrink-0">
            <Link
                :href="route('notifications.page')"
                @click="$emit('close')"
                class="flex items-center justify-center gap-1.5 py-3 text-xs font-bold text-[#0A2540] hover:text-[#FFC000] hover:bg-gray-50 transition-colors w-full"
            >
                Lihat Semua Notifikasi
                <ChevronRight class="w-3.5 h-3.5" />
            </Link>
        </div>
    </div>
</template>
