<template>
  <div class="min-h-screen bg-[var(--color-background)] font-sans text-[var(--color-text)] py-8 px-4 md:px-8">
    <div class="max-w-3xl mx-auto">
      
      <!-- HEADER -->
      <header class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-[var(--color-secondary)]">Notifikasi</h1>
          </div>
          <button 
            @click="markAllAsRead" 
            class="text-sm font-medium text-[var(--color-primary-light)] hover:text-[var(--color-secondary)] transition-colors text-left md:text-right"
          >
            Tandai semua sudah dibaca
          </button>
        </div>

        <!-- FILTERS -->
        <div class="flex overflow-x-auto gap-2 pb-2 -mx-4 px-4 md:mx-0 md:px-0 hide-scrollbar">
          <button 
            v-for="filter in filters" 
            :key="filter"
            @click="activeFilter = filter"
            :class="[
              'whitespace-nowrap px-4 py-2 rounded-full text-sm transition-all border flex items-center gap-2',
              activeFilter === filter 
                ? 'bg-[#FFC000] text-[#0A2540] border-[#FFC000] font-bold shadow-md' 
                : 'bg-white text-[var(--color-muted)] border-gray-200 hover:bg-gray-50 font-medium'
            ]"
          >
            <span>{{ filter }}</span>
            
            <!-- Badge angka bulat (Background Putih) -->
            <span 
              v-if="filter !== 'Semua' && getUnreadCount(filter) > 0"
              :class="[
                'w-5 h-5 rounded-full text-[11px] font-bold transition-colors flex items-center justify-center bg-white shadow-sm',
                activeFilter === filter
                  ? 'text-[#0A2540]'
                  : 'text-gray-600 border border-gray-200'
              ]"
            >
              {{ getUnreadCount(filter) }}
            </span>
          </button>
        </div>
      </header>

<!-- EMPTY STATE -->
<div v-if="Object.keys(groupedNotifications).length === 0" class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl shadow-sm border border-gray-100">
  <div class="text-5xl text-gray-300 mb-4">
    <i class="fa-regular fa-bell"></i>
  </div>
  <h3 class="text-lg font-bold text-[var(--color-secondary)] mb-2">Belum ada notifikasi</h3>
  <p class="text-[var(--color-muted)] max-w-sm text-sm">Semua aktivitas penyewaan Anda, mulai dari booking hingga pembayaran akan muncul di sini.</p>
</div>

      <!-- TIMELINE LIST -->
      <div v-else class="space-y-8">
        <div v-for="(notifs, dateGroup) in groupedNotifications" :key="dateGroup">
          
          <!-- Date Header -->
          <div class="flex items-center gap-4 mb-4">
            <h2 :class="[
              'text-sm font-bold uppercase tracking-wider',
              dateGroup === 'Belum Dibaca' ? 'text-[#E87E04]' : 'text-[var(--color-muted)]'
            ]">
              {{ dateGroup }}
            </h2>
            <div class="flex-1 h-px bg-gray-200"></div>
          </div>

          <!-- Notification Cards -->
          <div class="flex flex-col gap-3">
            <div 
              v-for="notif in notifs" 
              :key="notif.id"
              :class="[
                'relative flex flex-col md:flex-row gap-4 p-4 md:p-5 rounded-2xl border transition-all duration-300',
                notif.read ? 'bg-gray-50/50 border-transparent opacity-75' : 'bg-white border-gray-100 shadow-sm hover:shadow-md'
              ]"
            >
              <!-- Unread Indicator Dot -->
              <div v-if="!notif.read" class="absolute top-4 right-4 w-2 h-2 rounded-full bg-[#FFC000]"></div>

              <!-- Icon -->
              <div class="shrink-0 flex items-start">
                <div :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-inner',
                  notif.read ? 'bg-gray-200 grayscale' : getStatusColor(notif.status)
                ]">
                  {{ getIcon(notif.type) }}
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 flex flex-col justify-center">
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="font-semibold" :class="notif.read ? 'text-[var(--color-muted)]' : 'text-[var(--color-secondary)]'">
                    {{ notif.title }}
                  </h3>
                </div>
                <p class="text-sm mb-2" :class="notif.read ? 'text-gray-400' : 'text-[var(--color-text)]'">
                  {{ notif.description }}
                </p>
                <span class="text-xs font-medium text-[var(--color-muted)] flex items-center gap-1">
                  🕒 {{ notif.time }}
                </span>
              </div>

              <!-- Action Button -->
              <div v-if="notif.actionLabel && notif.actionLabel !== 'Tutup'" class="mt-3 md:mt-0 flex shrink-0 items-center">
                <button 
                  @click="handleAction(notif)"
                  class="w-full md:w-auto px-5 py-2.5 rounded-lg text-sm font-bold transition-transform active:scale-95"
                  :class="notif.type === 'Pembayaran' && notif.status === 'warning' 
                    ? 'bg-[#FFC000] text-[#0A2540] shadow-sm hover:brightness-105'
                    : 'bg-[var(--color-background)] border border-gray-200 text-[var(--color-secondary)] hover:bg-gray-100'"
                >
                  {{ notif.actionLabel }}
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Filter Options (Belum Dibaca dihapus)
const filters = ['Semua', 'Booking', 'Pembayaran', 'Chat', 'Sistem'];
const activeFilter = ref('Semua');

// Mock Data
const notifications = ref([]);

// Methods & Computed Properties
const getUnreadCount = (filterName) => {
  return notifications.value.filter(n => n.type === filterName && !n.read).length;
};

const filteredNotifications = computed(() => {
  return notifications.value.filter(notif => {
    if (activeFilter.value !== 'Semua') return notif.type === activeFilter.value;
    return true;
  });
});

// Memisahkan notifikasi dan menaruh yang "Belum Dibaca" paling atas
const groupedNotifications = computed(() => {
  const groups = {};
  const unreadList = [];
  const readList = [];

  // Pisahkan array berdasarkan status baca
  filteredNotifications.value.forEach(notif => {
    if (!notif.read) {
      unreadList.push(notif);
    } else {
      readList.push(notif);
    }
  });

  // Masukkan notifikasi belum dibaca ke dalam grup khusus di paling atas
  if (unreadList.length > 0) {
    groups['Belum Dibaca'] = unreadList;
  }

  // Kelompokkan sisanya berdasarkan tanggal aslinya (Hari Ini, Kemarin, dll)
  readList.forEach(notif => {
    if (!groups[notif.dateGroup]) {
      groups[notif.dateGroup] = [];
    }
    groups[notif.dateGroup].push(notif);
  });

  return groups;
});

const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true);
};

const handleAction = (notif) => {
  alert(`Aksi: ${notif.actionLabel} - ID: ${notif.id}`);
  notif.read = true;
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

const getIcon = (type) => {
  switch (type) {
    case 'Booking': return '📅';
    case 'Pembayaran': return '💳';
    case 'Chat': return '💬';
    case 'Review': return '⭐';
    case 'Sistem': return '🔔';
    default: return '📌';
  }
};
</script>

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