<template>
  <Head title="Pesan & Obrolan" />
  <!-- Main Layout -->
  <div class="fixed inset-0 flex flex-col bg-[#F0F2F5] font-sans overflow-hidden">
    <!-- Navbar Component for Top (Hidden on mobile if chat is open) -->
    <div :class="{'hidden md:block': isMobileChatOpen}">
      <Navbar class="z-20 shrink-0" />
    </div>

    <!-- Main Content wrapper, adjusting padding for Navbar/Bottombar depending on state -->
    <div 
        class="flex flex-1 overflow-hidden relative max-w-[1600px] mx-auto w-full transition-all"
        :class="isMobileChatOpen ? 'pt-0 pb-0' : 'pt-16 md:pt-20 pb-20 md:pb-0'"
    >
      <!-- 1. Sidebar Chat (Daftar Chat) -->
      <ChatList 
        :chatList="chatList"
        :activeChatId="activeChatId"
        :isMobileChatOpen="isMobileChatOpen"
        @selectChat="selectChat"
      />

      <!-- 2. Area Percakapan -->
      <ChatRoom 
        :isMobileChatOpen="isMobileChatOpen"
        :activeChatId="activeChatId"
        :activeChat="activeChat"
        :messages="messages"
        v-model:newMessage="newMessage"
        @closeMobile="isMobileChatOpen = false"
        @sendMessage="sendMessage"
      />

      <!-- 3. Detail Penyewaan (Hanya Desktop Besar / lg) -->
      <aside v-if="activeChatId" class="w-1/4 lg:w-[300px] bg-white border-l hidden lg:flex flex-col shrink-0 overflow-y-auto">
        <div class="bg-[#F0F2F5] h-16 border-b flex items-center px-6 shrink-0">
            <h3 class="font-medium text-gray-800 text-[15px]">Info Kontak</h3>
        </div>
        <div class="p-6 flex flex-col items-center border-b border-gray-100">
          <img v-if="activeChat.avatar" :src="activeChat.avatar" :alt="activeChat.name" class="w-48 h-48 object-cover rounded-full mb-4 shadow-sm">
          <div v-else class="w-48 h-48 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-4xl shadow-sm mb-4">
                {{ activeChat.avatarText }}
          </div>
          <h2 class="font-semibold text-xl text-gray-900 mb-1 text-center">{{ activeChat.name }}</h2>
        </div>

        <div class="p-6">
            <h4 class="text-sm font-semibold text-gray-500 mb-3">Terkait Aset</h4>
            <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                <img v-if="activeChat.assetImage" :src="activeChat.assetImage" :alt="activeChat.assetName" class="w-full h-32 object-cover rounded-lg mb-3">
                <div v-else class="w-full h-32 bg-gray-50 flex items-center justify-center text-gray-400 rounded-lg mb-3">
                    <i class="fa-solid fa-image text-3xl"></i>
                </div>
                <h4 class="font-medium text-gray-800 text-[15px] leading-tight mb-1">{{ activeChat.assetName }}</h4>
                <p class="text-sm text-[#FFC000] font-bold mb-3">{{ activeChat.price }}</p>
                <Link :href="route('assets.show', { asset: activeChat.assetId || 1 })" class="block text-center w-full border border-gray-200 text-gray-700 text-sm font-medium py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    Lihat Aset
                </Link>
            </div>
        </div>
      </aside>

    </div>
    
    <!-- Bottombar Component for Mobile Bottom Navigation -->
    <div :class="{'hidden md:block': isMobileChatOpen}">
      <Bottombar />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Bottombar from '@/Components/Bottombar.vue';
import ChatList from './ChatList.vue';
import ChatRoom from './ChatRoom.vue';

const isMobileChatOpen = ref(false);
const activeChatId = ref(null);
const newMessage = ref('');

const chatList = ref([]);
const messages = ref([]);

const fetchChats = async () => {
  try {
    const response = await axios.get('/api/chats');
    
    // Pertahankan properti lokal (seperti price dan assetId) agar tidak hilang saat polling
    const updatedChats = response.data.map(newChat => {
        const existingChat = chatList.value.find(c => c.id === newChat.id);
        if (existingChat) {
            newChat.price = existingChat.price;
            newChat.assetId = existingChat.assetId;
            if (activeChatId.value === newChat.id) {
                newChat.unread = 0;
            }
        }
        return newChat;
    });
    
    chatList.value = updatedChats;
    
    // Auto select first chat if activeChatId is null and not mobile
    if (!activeChatId.value && chatList.value.length > 0 && window.innerWidth >= 768) {
      const urlParams = new URLSearchParams(window.location.search);
      const roomId = urlParams.get('room_id');
      if (roomId) {
        selectChat(parseInt(roomId));
      } else {
        selectChat(chatList.value[0].id);
      }
    }
  } catch (error) {
    console.error('Error fetching chats:', error);
  }
};

const fetchMessages = async (id) => {
  try {
    const response = await axios.get(`/api/chats/${id}/messages`);
    messages.value = response.data.messages;
    
    const chat = chatList.value.find(c => c.id === id);
    if(chat) {
        chat.unread = 0;
        chat.price = response.data.priceLabel;
        // Simpan asset ID di object chat untuk rute tombol
        if (response.data.room?.asset_id) {
            chat.assetId = response.data.room.asset_id;
        }
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
  }
};

const selectChat = async (id) => {
  activeChatId.value = id;
  isMobileChatOpen.value = true;
  await fetchMessages(id);
  scrollToBottom();
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !activeChatId.value) return;

  const text = newMessage.value.trim();
  newMessage.value = '';

  messages.value.push({
    id: Date.now(),
    text: text,
    isSelf: true,
    time: 'Baru saja',
    dateLabel: 'Hari Ini'
  });
  scrollToBottom();

  try {
    const response = await axios.post(`/api/chats/${activeChatId.value}/messages`, { message: text });
    
    const lastMsg = messages.value[messages.value.length - 1];
    if (lastMsg) {
      lastMsg.id = response.data.message.id;
      lastMsg.time = response.data.message.time;
    }
    await fetchChats();
  } catch (error) {
    console.error('Error sending message:', error);
  }
};

const scrollToBottom = () => {
  nextTick(() => {
    const container = document.getElementById('chat-container');
    if (container) {
      container.scrollTop = container.scrollHeight;
    }
  });
};

const activeChat = computed(() => {
  return chatList.value.find(c => c.id === activeChatId.value) || {};
});

onMounted(() => {
  fetchChats();
  
  // Set up interval for polling (simple realtime)
  setInterval(() => {
    if(activeChatId.value) {
        fetchMessages(activeChatId.value);
    }
    fetchChats();
  }, 5000);
});
</script>
