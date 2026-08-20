<template>
  <aside
    :class="[
      'bg-white border-r border-gray-200/70 flex-col w-full md:w-[350px] lg:w-[400px] shrink-0 transition-all shadow-sm z-10',
      isMobileChatOpen ? 'hidden md:flex' : 'flex'
    ]"
  >
    <!-- Search Bar -->
    <div class="p-3 border-b border-gray-200 shrink-0 bg-white sticky top-0 z-10">
      <div class="relative flex items-center">
        <span class="absolute left-4 text-gray-400">
          <Search class="text-sm" />
        </span>
        <input
          type="text"
          placeholder="Cari atau mulai chat baru"
          class="w-full pl-10 pr-4 py-2 bg-gray-100 border-none rounded-lg focus:bg-white focus:ring-1 focus:ring-gray-300 outline-none transition-all text-sm h-10"
        >
      </div>
    </div>

    <!-- Chat List -->
    <div class="flex-1 overflow-y-auto">
      <template v-if="displayChats && displayChats.length > 0">
        <div
          v-for="chat in displayChats"
        :key="chat.id"
        @click="$emit('selectChat', chat.id)"
        class="flex items-center gap-3 p-3 cursor-pointer transition-colors hover:bg-gray-50 border-b border-gray-200/70"
        :class="{'bg-gray-100': activeChatId === chat.id}"
      >
        <!-- Avatar -->
        <div class="relative shrink-0">
            <img v-if="chat.avatar" :src="chat.avatar" alt="Avatar" class="w-12 h-12 rounded-full object-cover shrink-0">
            <div v-else class="w-12 h-12 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-sm shrink-0">
                {{ chat.avatarText }}
            </div>
        </div>

        <!-- Chat Info -->
        <div class="flex-1 min-w-0 flex flex-col justify-center border-b border-transparent h-full pb-1">
          <div class="flex justify-between items-center mb-0.5">
            <span class="font-semibold text-[15px] text-gray-900 truncate">
              {{ chat.name }} <template v-if="chat.assetName && chat.isContactOwner">- {{ chat.assetName }}</template>
            </span>
            <span class="text-[12px] text-gray-500">{{ chat.time }}</span>
          </div>
          <div class="flex justify-between items-center">
            <p class="text-[13px] text-gray-600 truncate pr-2 flex-1 flex items-center gap-1">
              <span v-if="chat.lastMessage && chat.isLastMessageSelf" class="shrink-0 text-[10px]">
                <CheckCheck :class="['', chat.isLastMessageRead ? 'text-blue-600 read-bounce' : 'text-gray-400']" />
              </span>
              <span v-if="chat.lastMessageType === 'image'" class="shrink-0"><Image class="" /></span>
              <span v-else-if="chat.lastMessageType === 'file'" class="shrink-0"><FileText class="" /></span>
              <span class="truncate">{{ chat.lastMessage || 'Mulai percakapan baru' }}</span>
            </p>
            <!-- Unread Badge -->
            <span v-if="chat.unread" class="bg-red-500 text-white text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0">
              {{ chat.unread > 99 ? '99+' : chat.unread }}
            </span>
          </div>
        </div>
        </div>
      </template>

      <!-- Empty State for Chat List -->
      <div v-else class="flex flex-col items-center justify-center h-full px-6 text-center pt-24 pb-8">
        <NoChatIcon class="w-48 h-48 mb-6 opacity-80" />
        <h3 class="text-gray-800 font-semibold text-lg mb-1">Belum Ada Percakapan</h3>
        <p class="text-gray-500 text-sm">Mulai diskusi dengan penyewa atau pemilik aset sekarang.</p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Search, CheckCheck, Image, FileText } from 'lucide-vue-next';
import NoChatIcon from '@/Components/ui/Icons/NoChatIcon.vue';

const props = defineProps({
  chatList: {
      type: Array,
      default: () => []
  },
  activeChatId: {
      type: Number,
      default: null
  },
  isMobileChatOpen: {
      type: Boolean,
      default: false
  },
});

const displayChats = computed(() => {
  return props.chatList.filter(chat => chat.lastMessage || chat.id === props.activeChatId);
});

defineEmits(['selectChat']);
</script>

<style scoped>
@keyframes pop-bounce {
  0% { transform: scale(1); }
  50% { transform: scale(1.5); }
  100% { transform: scale(1); }
}
.read-bounce {
  animation: pop-bounce 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
</style>
