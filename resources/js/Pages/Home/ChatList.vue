<template>
  <aside
    :class="[
      'bg-white border-r flex-col w-full md:w-[350px] lg:w-[400px] shrink-0 transition-all shadow-sm z-10',
      isMobileChatOpen ? 'hidden md:flex' : 'flex'
    ]"
  >
    <!-- Search Bar -->
    <div class="p-3 border-b border-gray-200 shrink-0 bg-white sticky top-0 z-10">
      <div class="relative flex items-center">
        <span class="absolute left-4 text-gray-400">
          <i class="fa-solid fa-magnifying-glass text-sm"></i>
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
      <div
        v-for="chat in chatList"
        :key="chat.id"
        @click="$emit('selectChat', chat.id)"
        class="flex items-center gap-3 p-3 cursor-pointer transition-colors hover:bg-gray-50 border-b border-gray-100/50"
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
            <span class="font-semibold text-[15px] text-gray-900 truncate">{{ chat.name }}</span>
            <span class="text-[12px] text-gray-500">{{ chat.time }}</span>
          </div>
          <div class="flex justify-between items-center">
            <p class="text-[13px] text-gray-600 truncate pr-2 flex-1">{{ chat.lastMessage || 'Mulai percakapan baru' }}</p>
            <!-- Unread Badge -->
            <span v-if="chat.unread" class="bg-red-500 text-white text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0">
              {{ chat.unread }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
defineProps({
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
defineEmits(['selectChat']);
</script>
