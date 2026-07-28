<template>
  <main
    :class="[
      'flex-1 flex-col relative min-w-0 bg-[#EFEAE2] overflow-hidden',
      isMobileChatOpen ? 'flex' : 'hidden md:flex'
    ]"
  >
    <!-- Empty State -->
    <div v-if="!activeChatId" class="flex-1 flex flex-col items-center justify-center bg-[#F0F2F5] text-center p-6">
        <!-- Using a placeholder if no-chat.svg is not available, but user requested no-chat.svg -->
        <img src="/no-chat.svg" alt="No Chat" class="w-64 h-64 mb-6 opacity-80" onerror="this.src='/images/dummy-map.png'" />
        <h2 class="text-2xl font-bold text-gray-700 mb-2">KuSewa Web</h2>
        <p class="text-gray-500 max-w-sm">Pilih pesan di samping untuk mulai berdiskusi atau hubungi pemilik aset.</p>
    </div>

    <!-- Active Chat Room -->
    <template v-else>
      <!-- Chat Header Desktop -->
      <div class="hidden md:flex h-16 bg-white border-b px-4 items-center justify-between shrink-0 shadow-sm z-10 w-full">
        <div class="flex items-center gap-3 w-full">
          <!-- Avatar -->
          <img v-if="activeChat.avatar" :src="activeChat.avatar" alt="Avatar" class="w-10 h-10 rounded-full object-cover">
          <div v-else class="w-10 h-10 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs shadow-sm">
              {{ activeChat.avatarText }}
          </div>

          <div class="flex-1 min-w-0 cursor-pointer">
            <h2 class="font-semibold text-gray-900 text-[15px] leading-tight truncate">
              {{ activeChat.name }} <template v-if="activeChat.assetName && activeChat.isContactOwner">- {{ activeChat.assetName }}</template>
            </h2>
            <p v-if="activeChat.assetName && !activeChat.isContactOwner" class="text-[12px] text-gray-500 flex items-center gap-1 mt-0.5 truncate">
              {{ activeChat.assetName }}
            </p>
          </div>
        </div>
        <button class="text-gray-500 hover:text-gray-700 p-2">
          <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
      </div>

      <!-- Chat Header Mobile (DetailNavbar) -->
      <DetailNavbar class="md:hidden shrink-0 z-50">
          <template #content>
            <div class="flex items-center gap-3 w-full pr-4 py-2">
              <button @click="$emit('closeMobile')" class="p-2 -ml-2 text-[#0A2540] hover:text-gray-800 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
              </button>
              <img v-if="activeChat.avatar" :src="activeChat.avatar" alt="Avatar" class="w-9 h-9 rounded-full object-cover shrink-0">
              <div v-else class="w-9 h-9 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs shadow-sm shrink-0">
                  {{ activeChat.avatarText }}
              </div>
              <div class="flex-1 min-w-0 flex flex-col justify-center">
                <h2 class="font-semibold text-gray-900 text-[14px] leading-tight truncate">
                  {{ activeChat.name }} <template v-if="activeChat.assetName && activeChat.isContactOwner">- {{ activeChat.assetName }}</template>
                </h2>
                <p v-if="activeChat.assetName && !activeChat.isContactOwner" class="text-[11px] text-gray-500 truncate mt-0.5">
                  {{ activeChat.assetName }}
                </p>
              </div>
            </div>
          </template>
      </DetailNavbar>

      <!-- Chat Messages Container -->
      <div class="flex-1 overflow-y-auto overscroll-contain p-4 md:p-6 flex flex-col gap-2 relative bg-white pb-4 md:pb-6 chat-container" id="chat-container">

        <!-- Info Aset Terkait (Hanya Mobile, di paling atas chat) -->
        <div class="md:hidden w-full bg-white rounded-xl border border-gray-200 p-3 mb-4 shadow-sm">
            <div class="flex gap-3">
                <img v-if="activeChat.assetImage" :src="activeChat.assetImage" class="w-20 h-20 object-cover rounded-lg shrink-0">
                <div v-else class="w-20 h-20 bg-gray-50 flex items-center justify-center text-gray-400 rounded-lg shrink-0">
                    <i class="fa-solid fa-image text-xl"></i>
                </div>
                <div class="flex-1 min-w-0 flex flex-col justify-center">
                    <h4 class="font-bold text-gray-800 text-[14px] leading-tight mb-1 truncate">{{ activeChat.assetName }}</h4>
                    <p class="text-[13px] text-[#FFC000] font-bold mb-2">{{ activeChat.price }}</p>
                    <button class="w-fit px-4 border border-gray-200 text-gray-700 text-[11px] font-bold py-1.5 rounded-md hover:bg-gray-50 transition-colors">
                        Lihat Aset
                    </button>
                </div>
            </div>
        </div>

        <div class="z-10 flex flex-col gap-1 w-full">

            <!-- Messages Iteration -->
            <template v-for="(msg, index) in messages" :key="index">
              <!-- Date Separator -->
              <div v-if="index === 0 || messages[index - 1].dateLabel !== msg.dateLabel" class="flex justify-center my-4">
                <span class="bg-gray-100 text-gray-600 text-[12px] font-medium px-4 py-1.5 rounded-lg">
                  {{ msg.dateLabel || 'Hari Ini' }}
                </span>
              </div>
              <!-- Bubble Lawan -->
              <div v-if="!msg.isSelf" class="flex flex-col self-start max-w-[85%] md:max-w-[65%] mb-1">
                <div class="bg-gray-100 text-gray-800 px-3 py-2 rounded-xl rounded-tl-none text-[14.5px] leading-snug relative">
                  {{ msg.text }}
                  <span class="text-[10px] text-gray-400 ml-3 float-right mt-2">{{ msg.time }}</span>
                </div>
              </div>

              <!-- Bubble Sendiri -->
              <div v-else class="flex flex-col self-end max-w-[85%] md:max-w-[65%] mb-1">
                <div class="bg-[#FFC000] text-black px-3 py-2 rounded-xl rounded-tr-none text-[14.5px] leading-snug relative shadow-sm">
                  {{ msg.text }}
                  <div class="text-[10px] text-gray-700 ml-3 float-right mt-2 flex items-center gap-1">
                      {{ msg.time }}
                      <i :class="[
                        'fa-solid transition-colors duration-500 ease-in-out',
                        (msg.status === 'failed' || msg.status === 'sending') ? 'fa-check text-gray-500' :
                        (msg.isRead ? 'fa-check-double text-blue-600 read-bounce' : 'fa-check-double text-gray-500')
                      ]"></i>
                  </div>
                </div>
              </div>
            </template>
        </div>
      </div>

      <!-- Input Chat Desktop -->
      <div class="hidden md:flex bg-[#F0F2F5] px-4 py-3 items-end gap-2 shrink-0 z-10 w-full">
        <button class="p-2 text-gray-500 hover:text-gray-700 text-xl hidden sm:block">
           <i class="fa-regular fa-face-smile"></i>
        </button>

        <button class="p-2 text-gray-500 hover:text-gray-700 text-xl">
           <i class="fa-solid fa-paperclip"></i>
        </button>

        <div class="flex-1 bg-white rounded-lg flex items-center transition-all overflow-hidden shadow-sm">
          <textarea
            v-model="localNewMessage"
            @keyup.enter.prevent="handleSend"
            rows="1"
            placeholder="Ketik pesan..."
            class="w-full bg-transparent px-4 py-2.5 text-[15px] outline-none resize-none max-h-24 md:max-h-32 text-gray-700 border-none focus:ring-0"
          ></textarea>
        </div>

        <button @click="handleSend" :disabled="!localNewMessage.trim()" class="bg-[#FFC000] hover:bg-yellow-500 disabled:opacity-50 text-black w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors">
          <i class="fa-solid fa-paper-plane text-[15px] -ml-0.5"></i>
        </button>
      </div>

      <!-- Input Chat Mobile -->
      <div class="md:hidden shrink-0 bg-white border-t border-gray-200 p-2 z-50 flex items-center gap-2 pb-safe shadow-[0_-4px_10px_-2px_rgba(0,0,0,0.05)] w-full">
        <button class="p-2 text-gray-500 hover:text-gray-700 text-lg">
            <i class="fa-solid fa-paperclip"></i>
        </button>
        <div class="flex-1 bg-gray-50 rounded-full flex items-center transition-all overflow-hidden border border-gray-200">
          <textarea
            v-model="localNewMessage"
            @keyup.enter.prevent="handleSend"
            rows="1"
            placeholder="Ketik pesan..."
            class="w-full bg-transparent px-4 py-2 text-[14px] outline-none resize-none max-h-20 text-gray-700 border-none focus:ring-0 leading-normal"
          ></textarea>
        </div>
        <button @click="handleSend" :disabled="!localNewMessage.trim()" class="bg-[#FFC000] hover:bg-yellow-500 disabled:opacity-50 text-black w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors mr-1">
          <i class="fa-solid fa-paper-plane text-[14px] -ml-0.5"></i>
        </button>
      </div>

    </template>
  </main>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';

const props = defineProps({
  isMobileChatOpen: {
      type: Boolean,
      default: false
  },
  activeChatId: {
      type: Number,
      default: null
  },
  activeChat: {
      type: Object,
      default: () => ({})
  },
  messages: {
      type: Array,
      default: () => []
  },
  newMessage: {
      type: String,
      default: ''
  },
});

const emit = defineEmits(['closeMobile', 'update:newMessage', 'sendMessage']);

const localNewMessage = ref(props.newMessage);

watch(() => props.newMessage, (val) => {
    localNewMessage.value = val;
});
watch(localNewMessage, (val) => {
    emit('update:newMessage', val);
});

const handleSend = () => {
    emit('sendMessage');
    nextTick(() => {
        const container = document.getElementById('chat-container');
        if (container) container.scrollTop = container.scrollHeight;
    });
};
</script>

<style scoped>
/* Custom Scrollbar */
::-webkit-scrollbar { width: 4px; }
@media (min-width: 768px) { ::-webkit-scrollbar { width: 6px; } }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
/* Safe area for mobile bottoms */
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 0.5rem);
}
@keyframes pop-bounce {
  0% { transform: scale(1); }
  50% { transform: scale(1.5); }
  100% { transform: scale(1); }
}
.read-bounce {
  animation: pop-bounce 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
</style>
