<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useNotifications } from '@/Composables/useNotifications';
import { Loader2, RefreshCw } from 'lucide-vue-next';
import EmptyStateIcon from '@/Components/ui/Icons/EmptyStateIcon.vue';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';

const { notifications, unreadCount, isLoading, fetchNotifications, markAsRead, markAllAsRead } = useNotifications();

// Filter Options
const filters = ['Semua', 'Belum Dibaca', 'Booking', 'Pembayaran', 'Chat', 'Sistem'];
const activeFilter = ref('Semua');

onMounted(async () => {
    await fetchNotifications();
});

const switchFilter = async (filter) => {
    activeFilter.value = filter;
    await fetchNotifications();
};

const getNotifMeta = (notif) => {
    let meta = { type: 'Sistem', status: 'info' };
    if (!notif.type) return meta;

    if (notif.type.includes('Booking')) {
        meta.type = 'Booking';
        meta.status = notif.type.includes('Cancelled') ? 'error' : 'success';
    }
    if (notif.type.includes('Payment')) {
        meta.type = 'Pembayaran';
        meta.status = 'warning';
    }
    if (notif.type.includes('Chat')) {
        meta.type = 'Chat';
        meta.status = 'info';
    }
    return meta;
};

const filteredNotifications = computed(() => {
    return notifications.value.filter(notif => {
        if (activeFilter.value === 'Belum Dibaca') return !notif.read_at;

        if (activeFilter.value !== 'Semua') {
            const meta = getNotifMeta(notif);
            return meta.type === activeFilter.value;
        }

        return true;
    });
});

const getDateGroup = (dateString) => {
    if (!dateString) return 'Lainnya';
    const now = new Date();
    const date = new Date(dateString);

    // Normalize to midnight for accurate day difference
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const target = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    const diffMs = today - target;
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return '(Hari Ini)';
    if (diffDays === 1) return '(Kemarin)';
    if (diffDays < 7) return '(Minggu Lalu)';
    if (diffDays < 30) return '(Bulan Ini)';
    return '(Bulan Lalu)';
};

const groupedNotifications = computed(() => {
    const groups = {};
    filteredNotifications.value.forEach(notif => {
        const group = getDateGroup(notif.created_at);
        if (!groups[group]) groups[group] = [];
        groups[group].push(notif);
    });
    return groups;
});

const handleClick = async (notification) => {
    if (!notification.read_at) {
        await markAsRead(notification.id);
    }
};

const formatTimeOnly = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

// Styling Helpers
const getStatusColor = (status) => {
  switch (status) {
    case 'success': return 'bg-green-100 text-green-600';
    case 'warning': return 'bg-orange-100 text-orange-500';
    case 'error': return 'bg-red-100 text-red-600';
    case 'info': return 'bg-blue-100 text-blue-600';
    default: return 'bg-gray-100 text-gray-600';
  }
};
</script>

<template>
  <AppLayout hide-navbar>
    <DetailNavbar
      :showSections="false"
      :showShare="false"
      :showFavorite="false"
      :showBackButton="true"
      title="Notifikasi"
      backUrl="/"
    >
      <template #actions>
        <button
          @click="fetchNotifications()"
          class="p-2 rounded-full text-gray-500 hover:text-[#0A2540] hover:bg-gray-100 transition"
          title="Refresh"
        >
          <RefreshCw class="w-5 h-5" :class="isLoading ? 'animate-spin' : ''" />
        </button>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="hidden md:block text-sm font-medium text-blue-600 hover:text-[#0A2540] transition-colors ml-2"
        >
          Tandai semua sudah dibaca
        </button>
      </template>
    </DetailNavbar>
    <div class="min-h-screen bg-[#F8F9FA] font-sans text-gray-700 py-6 px-4 md:px-8">
      <div class="max-w-3xl mx-auto">

        <!-- FILTERS & ACTIONS (MOBILE) -->
        <header class="mb-6">
          <div class="mb-4 md:hidden" v-if="unreadCount > 0">
              <button
                @click="markAllAsRead"
                class="text-sm font-medium text-blue-600 hover:text-[#0A2540] transition-colors"
              >
                Tandai semua sudah dibaca
              </button>
          </div>

          <!-- FILTERS -->
          <div class="flex overflow-x-auto gap-2 pb-2 hide-scrollbar">
            <button
              v-for="filter in filters"
              :key="filter"
              @click="switchFilter(filter)"
              class="relative whitespace-nowrap px-4 py-1.5 rounded-md text-sm font-medium transition-all"
              :class="activeFilter === filter
                ? 'bg-[#FFC000] text-[#0A2540] shadow-sm font-bold'
                : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50'"
            >
              {{ filter }}
              <!-- Notification Badge Count for 'Belum Dibaca' -->
              <span
                v-if="filter === 'Belum Dibaca' && unreadCount > 0"
                class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full min-w-[16px] text-center"
              >
                {{ unreadCount }}
              </span>
            </button>
          </div>
        </header>

        <!-- LOADING STATE -->
        <div v-if="isLoading" class="flex justify-center py-20">
            <Loader2 class="w-8 h-8 animate-spin text-gray-300" />
        </div>

        <!-- EMPTY STATE -->
        <div v-else-if="Object.keys(groupedNotifications).length === 0" class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl shadow-sm border border-gray-100">
          <div class="w-48 max-w-[50%] mb-4 opacity-80">
              <EmptyStateIcon />
          </div>
          <h3 class="text-lg font-bold text-[#0A2540] mb-2">Kotak notifikasi masih kosong</h3>
          <p class="text-gray-500 max-w-sm">Dapatkan kabar terbaru tentang booking dan pembayaran Anda.</p>
        </div>

        <!-- TIMELINE LIST -->
        <div v-else class="space-y-8">
          <div v-for="(notifs, dateGroup) in groupedNotifications" :key="dateGroup">

            <!-- Date Header -->
            <div class="flex items-center gap-4 mb-4">
              <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">{{ dateGroup }}</h2>
              <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <!-- Notification Cards -->
            <div class="flex flex-col gap-3">
              <component
                :is="notif.data?.action_url ? Link : 'div'"
                :href="notif.data?.action_url || undefined"
                @click="handleClick(notif)"
                v-for="notif in notifs"
                :key="notif.id"
                :class="[
                  'relative flex flex-col md:flex-row gap-4 p-4 md:p-5 rounded-2xl border transition-all duration-300',
                  notif.data?.action_url ? 'cursor-pointer' : '',
                  notif.read_at ? 'bg-gray-50/50 border-transparent opacity-80' : 'bg-white border-gray-100 shadow-sm hover:shadow-md'
                ]"
              >
                <!-- Unread Indicator Dot -->
                <div v-if="!notif.read_at" class="absolute top-4 right-4 w-2 h-2 rounded-full bg-[#FFC000]"></div>

                <!-- Icon / Logo -->
                <div class="shrink-0 flex items-start">
                  <div :class="[
                    'w-12 h-12 rounded-full flex items-center justify-center shadow-inner',
                    notif.read_at ? 'bg-gray-200' : getStatusColor(getNotifMeta(notif).status)
                  ]">
                    <img src="/kitasewa-logo.png" alt="KitaSewa" class="w-7 h-7 object-contain" :class="notif.read_at ? 'grayscale opacity-60' : ''" />
                  </div>
                </div>

                <!-- Content -->
                <div class="flex-1 flex flex-col justify-center">
                  <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold" :class="notif.read_at ? 'text-gray-500' : 'text-[#0A2540]'">
                      {{ notif.data?.title }}
                    </h3>
                  </div>
                  <p class="text-sm mb-2" :class="notif.read_at ? 'text-gray-400' : 'text-gray-700'">
                    {{ notif.data?.message }}
                  </p>
                  <span class="text-xs font-medium text-gray-500 flex items-center gap-1">
                    🕒 {{ formatTimeOnly(notif.created_at) }}
                  </span>
                </div>

                <!-- Action Button -->
                <div v-if="notif.data?.action_url && !notif.read_at" class="mt-3 md:mt-0 flex shrink-0 items-center">
                  <button
                    class="w-full md:w-auto px-5 py-2.5 rounded-lg text-sm font-bold transition-transform active:scale-95 border border-gray-200 text-[#0A2540] hover:bg-gray-100"
                  >
                    Lihat Detail
                  </button>
                </div>
              </component>
            </div>

          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Menyembunyikan scrollbar pada filter tabs di mobile namun tetap bisa digeser */
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
