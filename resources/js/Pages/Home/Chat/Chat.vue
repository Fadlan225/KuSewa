<template>
  <Head title="Kotak Masuk" />
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
        :isTyping="isTyping"
        @sendMessage="sendMessage"
        @editMessage="editMessage"
        @deleteMessage="deleteMessage"
        @typing="handleTyping"
        @showInfo="showInfoModal"
        @closeMobile="closeMobileChat"
      />

      <!-- Message Info Modal -->
      <MessageInfoModal
          :show="messageInfo !== null"
          :message="messageInfo"
          @close="messageInfo = null"
      />

      <!-- 3. Detail Penyewaan (Hanya Desktop Besar / lg) -->
      <aside v-if="activeChatId" class="w-1/4 lg:w-[300px] bg-white border-l border-gray-200/70 hidden lg:flex flex-col shrink-0 overflow-y-auto">
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
                    <Image class="text-3xl" />
                </div>
                <h4 class="font-medium text-gray-800 text-[15px] leading-tight mb-1">{{ activeChat.assetName }}</h4>
                <p class="text-sm text-[#FFC000] font-bold mb-3">{{ activeChat.price }}</p>
                <a :href="route('assets.show', { asset: activeChat.assetSlug || 1 })" target="_blank" class="block text-center w-full border border-gray-200 text-gray-700 text-sm font-medium py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    Lihat Aset
                </a>
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
import { Image } from 'lucide-vue-next';
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Bottombar from '@/Components/Bottombar.vue';
import ChatList from './ChatList.vue';
import ChatRoom from './ChatRoom.vue';
import MessageInfoModal from '@/Components/ui/MessageInfoModal.vue';

const isMobileChatOpen = ref(false);
const activeChatId = ref(null);
const newMessage = ref('');

const chatList = ref([]);
const messages = ref([]);
const messageInfo = ref(null);

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

    // Handle URL params for direct room opening
    const urlParams = new URLSearchParams(window.location.search);
    const roomId = urlParams.get('room_id') || urlParams.get('room');
    
    if (roomId && !activeChatId.value) {
      selectChat(parseInt(roomId));
    } else if (!activeChatId.value && chatList.value.length > 0 && window.innerWidth >= 768) {
      // Desktop default auto-open behavior (optional)
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

const closeMobileChat = () => {
  isMobileChatOpen.value = false;
  activeChatId.value = null;
};

const sendMessage = async (payload = null) => {
  let text = '';
  let files = [];

  if (payload && payload.files) {
    files = payload.files;
    text = payload.text || '';
  } else if (payload && payload.file) { // backward compatibility for FloatingChat or single files
    files = [payload.file];
    text = payload.text || '';
  } else {
    if (!newMessage.value.trim() && !activeChatId.value) return;
    text = newMessage.value.trim();
    newMessage.value = '';
  }

  const tempId = Date.now();
  let type = 'text';
  let attachments = [];

  if (files.length > 0) {
      type = files.length > 1 ? 'album' : (files[0].type.startsWith('image/') ? 'image' : 'file');
      attachments = files.map((f, idx) => ({
          id: `temp-${idx}`,
          file_url: URL.createObjectURL(f),
          file_name: f.name
      }));
  }

  messages.value.push({
    id: tempId,
    type: type,
    text: text,
    attachments: attachments,
    isSelf: true,
    time: 'Baru saja',
    timestamp: new Date().toISOString(),
    dateLabel: 'Hari Ini',
    status: 'sending',
    isRead: false
  });
  scrollToBottom();

  try {
    let response;
    if (files.length > 0) {
        const formData = new FormData();
        if (text) formData.append('message', text);
        files.forEach(f => formData.append('files[]', f));
        if (payload && payload.replyToId) {
            formData.append('reply_to_id', payload.replyToId);
        }

        response = await axios.post(`/api/chats/${activeChatId.value}/messages`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    } else {
        response = await axios.post(`/api/chats/${activeChatId.value}/messages`, {
            message: text,
            reply_to_id: payload ? payload.replyToId : null
        });
    }

    const sentMsg = messages.value.find(m => m.id === tempId);
    if (sentMsg) {
      sentMsg.id = response.data.message.id;
      sentMsg.time = response.data.message.time;
      if (response.data.message.timestamp) sentMsg.timestamp = response.data.message.timestamp;
      sentMsg.isRead = response.data.message.isRead;
      sentMsg.status = 'sent';
      sentMsg.replyTo = response.data.message.replyTo;

      if (response.data.message.attachments) {
          sentMsg.attachments = response.data.message.attachments;
      }
      if (response.data.message.file_url) {
          sentMsg.file_url = response.data.message.file_url;
      }
    }
    await fetchChats();
  } catch (error) {
    console.error('Error sending message:', error);
    const failedMsg = messages.value.find(m => m.id === tempId);
    if (failedMsg) {
      if (error.response && error.response.status === 422) {
          failedMsg.status = 'policy_error';
          failedMsg.error_text = error.response.data.error || 'Pesan melanggar kebijakan sistem.';
      } else {
          failedMsg.status = 'failed';
      }
    }
  }
};

const editMessage = async (payload) => {
    try {
        const response = await axios.put(`/api/chats/${activeChatId.value}/messages/${payload.id}`, {
            message: payload.text
        });
        const msg = messages.value.find(m => m.id === payload.id);
        if (msg) {
            msg.text = response.data.message;
            msg.isEdited = true;
        }
    } catch (error) {
        console.error('Error editing message:', error);
    }
};

const deleteMessage = async (id) => {
    try {
        await axios.delete(`/api/chats/${activeChatId.value}/messages/${id}`);
        const msg = messages.value.find(m => m.id === id);
        if (msg) {
            msg.isDeleted = true;
            msg.text = null;
            msg.attachments = [];
            msg.file_url = null;
        }
    } catch (error) {
        console.error('Error deleting message:', error);
    }
};

const showInfoModal = (msg) => {
    messageInfo.value = msg;
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

let chatChannel = null;
let typingTimer = null;
const isTyping = ref(false);

watch(activeChatId, (newId, oldId) => {
  if (oldId && chatChannel) {
    window.Echo.leave('chat.' + oldId);
  }

  isTyping.value = false;

  if (newId) {
    chatChannel = window.Echo.private('chat.' + newId)
      .listen('MessageSent', (e) => {
        // Cek apakah pesan ini dari user lain (bukan milik sendiri)
        if (e.sender_id !== usePage().props.auth.user.id) {
          isTyping.value = false;
          if (!messages.value.some(m => m.id === e.id)) {
            messages.value.push({
              id: e.id,
              type: e.type || 'text',
              text: e.message,
              attachments: e.attachments || [],
              file_url: e.file_url, // For backward compatibility
              file_name: e.file_name,
              isSelf: false,
              time: e.created_at,
              dateLabel: 'Hari Ini',
              status: 'sent',
              isRead: true,
              replyTo: e.replyTo
            });
            scrollToBottom();

            // Tandai sudah dibaca di server (jika perlu) karena chat room sedang terbuka
            axios.put(`/api/chats/${newId}/messages/read`);

            // Refresh list chat untuk update last message
            fetchChats();
          }
        }
      })
      .listen('MessageUpdated', (e) => {
          const msg = messages.value.find(m => m.id === e.message.id);
          if (msg) {
              msg.text = e.message.message;
              msg.isEdited = true;
          }
      })
      .listen('MessageDeleted', (e) => {
          const msg = messages.value.find(m => m.id === e.messageId);
          if (msg) {
              msg.isDeleted = true;
              msg.text = null;
              msg.attachments = [];
              msg.file_url = null;
          }
      })
      .listenForWhisper('typing', (e) => {
        if (e.userId !== usePage().props.auth.user.id) {
          isTyping.value = true;
          clearTimeout(typingTimer);
          typingTimer = setTimeout(() => {
            isTyping.value = false;
          }, 2000);
          scrollToBottom();
        }
      });
  }
});

let lastWhisper = 0;
const handleTyping = () => {
  const now = Date.now();
  if (now - lastWhisper > 1000) {
    if (chatChannel) {
      chatChannel.whisper('typing', {
        userId: usePage().props.auth.user.id
      });
      lastWhisper = now;
    }
  }
};

onMounted(() => {
  fetchChats();

  // Polling lambat hanya untuk fallback/update badge kalau ada pesan di chat lain
  setInterval(() => {
    fetchChats();
  }, 10000);
});
</script>
