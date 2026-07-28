<template>
  <div>
    <!-- 1. BUBBLE DESKTOP SAJA (Pojok Kanan Bawah, Hanya di Desktop/Laptop) -->
    <div class="hidden md:block fixed bottom-6 right-6 z-[99999] font-sans antialiased">
      <button
        @click="toggleChat"
        class="w-14 h-14 rounded-full bg-[#ffc000] hover:bg-[#ebd000] text-slate-950 shadow-2xl flex items-center justify-center transition-all active:scale-90 relative cursor-pointer group"
      >
        <span
          v-if="totalUnreadCount > 0 && !isChatOpen"
          class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-white shadow-xs"
        >
          {{ totalUnreadCount > 99 ? '99+' : totalUnreadCount }}
        </span>

        <svg v-if="!isChatOpen" class="w-7 h-7 text-slate-950 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>

        <svg v-else class="w-6 h-6 text-slate-950 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- 2. LAYAR CHAT (FULLSCREEN PADA MOBILE / POPUP PADA DESKTOP) -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0 translate-y-4"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 translate-y-4"
    >
      <div
        v-if="isChatOpen"
        class="fixed z-[999999] font-sans antialiased
               /* FULLSCREEN MOBILE */
               inset-0 w-full h-full bg-white flex flex-col
               /* POPUP DESKTOP */
               md:inset-auto md:bottom-24 md:right-6 md:w-96 md:h-[520px] md:max-h-[calc(100vh-120px)] md:rounded-2xl md:shadow-2xl md:border md:border-black/10 md:overflow-hidden"
      >

        <!-- VIEW 1: DAFTAR KONTAK -->
        <template v-if="activeContactId === null">
          <div class="bg-[#ffc000] p-4 text-slate-950 flex items-center justify-between shadow-xs">
            <div>
              <h3 class="font-extrabold text-sm md:text-base leading-tight">Kotak Masuk</h3>
              <p class="text-[10px] md:text-xs font-medium text-slate-800/80">Pilih kontak untuk memulai chat</p>
            </div>
            <button
              @click="closeChat"
              class="w-8 h-8 rounded-full bg-black/10 hover:bg-black/20 flex items-center justify-center text-slate-900 transition-colors cursor-pointer"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="p-3 bg-slate-50 border-b border-slate-100">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari kontak..."
              class="w-full bg-white text-slate-800 text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#ffc000]"
            />
          </div>

          <div class="flex-1 overflow-y-auto divide-y divide-slate-100 bg-white">
            <template v-if="filteredContacts && filteredContacts.length > 0">
              <div
                v-for="contact in filteredContacts"
                :key="contact.id"
                @click="openContactChat(contact.id)"
                class="p-4 flex items-center gap-3 hover:bg-slate-50 active:bg-slate-100 transition-colors cursor-pointer"
              >
              <div class="relative">
                <div class="w-11 h-11 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                  {{ contact.avatarText }}
                </div>
                <span v-if="contact.isOnline" class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-0.5">
                  <h4 class="font-bold text-xs md:text-sm text-slate-900 truncate">
                    {{ contact.name }} <template v-if="contact.assetName && contact.isContactOwner">- {{ contact.assetName }}</template>
                  </h4>
                  <span class="text-[10px] text-slate-400 font-mono">{{ contact.time }}</span>
                </div>
                <p class="text-xs text-slate-500 truncate leading-tight flex items-center gap-1">
                  <span v-if="contact.lastMessage && contact.isLastMessageSelf" class="shrink-0 text-[10px]">
                    <i :class="['fa-solid fa-check-double transition-colors duration-500 ease-in-out', contact.isLastMessageRead ? 'text-blue-600 read-bounce' : 'text-slate-400']"></i>
                  </span>
                  <span class="truncate">{{ contact.lastMessage }}</span>
                </p>
              </div>
              <div v-if="contact.unread" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0">
                {{ contact.unread > 99 ? '99+' : contact.unread }}
              </div>
            </div>
            </template>
            <!-- Empty State for Contacts -->
            <div v-else class="flex flex-col items-center justify-center h-full px-6 text-center py-10">
              <img src="/no-chat.svg" alt="No Chat" class="w-24 h-24 mb-4 opacity-80" onerror="this.src='/images/dummy-map.png'" />
              <h3 class="text-slate-800 font-semibold text-sm mb-1">Belum Ada Percakapan</h3>
              <p class="text-slate-500 text-xs">Anda belum memiliki percakapan aktif.</p>
            </div>
          </div>
        </template>

        <!-- VIEW 2: DETAIL ROOM CHAT -->
        <template v-else>
          <div class="bg-[#ffc000] p-3 flex items-center justify-between text-slate-950 shadow-xs">
            <div class="flex items-center gap-2.5">
              <button @click="activeContactId = null" class="w-8 h-8 rounded-full bg-black/10 flex items-center justify-center text-slate-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
              </button>
              <div>
                <h3 class="font-bold text-xs md:text-sm leading-tight text-slate-900">
                  {{ currentContact?.name }} <template v-if="currentContact?.assetName && currentContact?.isContactOwner">- {{ currentContact?.assetName }}</template>
                </h3>
                <p class="text-[10px] font-medium text-slate-800/80">{{ currentContact?.isOnline ? 'Online' : 'Offline' }}</p>
              </div>
            </div>
            <button @click="closeChat" class="w-8 h-8 rounded-full bg-black/10 flex items-center justify-center text-slate-900">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div ref="chatContainer" class="flex-1 overflow-y-auto p-3 space-y-4 bg-[#F5F7FB] scroll-smooth">
            <div v-for="msg in currentMessages" :key="msg.id" class="flex flex-col" :class="msg.isSender ? 'items-end' : 'items-start'">
              <div :class="[msg.status === 'policy_error' ? 'bg-red-50 border border-red-200 text-gray-800' : (msg.isSender ? 'bg-[#FFC000] text-black' : 'bg-white text-gray-800 border border-gray-100'), 'px-3 py-2 rounded-xl text-sm leading-snug relative shadow-sm max-w-[85%] select-none']" style="-webkit-touch-callout: none;"
                   @contextmenu.prevent="openContextMenu($event, msg)"
                   @touchstart="startLongPress($event, msg)"
                   @touchend="cancelLongPress"
                   @touchmove="cancelLongPress">
                <div v-if="msg.status === 'policy_error'" class="text-[10px] md:text-xs text-red-600 font-medium mb-1 pb-1 border-b border-red-100 flex items-start gap-1.5">
                  <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                  <span>{{ msg.error_text }}</span>
                </div>
                
                <div :class="{'opacity-60': msg.status === 'policy_error'}">
                    <div v-if="msg.replyTo" class="bg-black/5 border-l-4 p-2 rounded-r-lg mb-2 text-xs cursor-pointer opacity-80" :class="msg.isSender ? 'border-yellow-700' : 'border-gray-400'">
                        <div class="font-semibold mb-0.5" :class="msg.isSender ? 'text-yellow-900' : 'text-gray-700'">{{ msg.replyTo.sender_name }}</div>
                        <div class="truncate max-w-[150px]" :class="msg.isSender ? 'text-yellow-800' : 'text-gray-600'">{{ msg.replyTo.text }}</div>
                    </div>
                    <template v-if="msg.isDeleted">
                        <div class="italic text-gray-800 text-xs"><i class="fa-solid fa-ban mr-1"></i> Pesan ini telah dihapus</div>
                    </template>
                    <template v-else-if="msg.attachments && msg.attachments.length > 0">
                        <div class="grid gap-1 mb-1 max-w-[200px]" :class="[
                            msg.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
                        ]">
                            <div v-for="(att, idx) in msg.attachments.slice(0, 4)" :key="att.id" class="relative" :class="{'col-span-2': msg.attachments.length === 3 && idx === 0}">
                                <img 
                                    :src="att.file_url" 
                                    class="w-full object-cover rounded-md" 
                                    :class="[
                                        msg.status === 'policy_error' ? 'cursor-default' : 'cursor-pointer',
                                        msg.attachments.length === 3 && idx === 0 ? 'aspect-[2/1]' : 'aspect-square'
                                    ]"
                                    @click="msg.status !== 'policy_error' && openViewer(att.file_url)" 
                                />
                                <div v-if="idx === 3 && msg.attachments.length > 4" class="absolute inset-0 bg-black/60 flex items-center justify-center rounded-md text-white font-bold text-sm" :class="msg.status === 'policy_error' ? 'cursor-default' : 'cursor-pointer'" @click="msg.status !== 'policy_error' && openViewer(att.file_url)">
                                    +{{ msg.attachments.length - 4 }}
                                </div>
                            </div>
                        </div>
                        <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                    </template>
                    <template v-else-if="msg.type === 'image'">
                        <img :src="msg.file_url" class="max-w-[150px] rounded-lg mb-1" :class="msg.status === 'policy_error' ? 'cursor-default' : 'cursor-pointer'" @click="msg.status !== 'policy_error' && openViewer(msg.file_url)" />
                        <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                    </template>
                    <template v-else-if="msg.type === 'file'">
                        <a :href="msg.file_url" target="_blank" class="flex items-center gap-2 p-1.5 rounded-lg border border-black/10" :class="msg.status === 'policy_error' ? 'bg-red-100 pointer-events-none' : (msg.isSender ? 'bg-yellow-400 hover:bg-black/5' : 'bg-slate-50 hover:bg-black/5')">
                            <i class="fa-solid fa-file-lines text-lg" :class="msg.isSender ? 'text-black' : 'text-red-500'"></i>
                            <span class="text-xs truncate max-w-[100px]">{{ msg.file_name }}</span>
                        </a>
                        <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                    </template>
                    <template v-else>
                        <div class="whitespace-pre-wrap">{{ msg.text }}</div>
                    </template>
                </div>
              </div>
              <div class="flex items-center gap-1 mt-0.5 px-1 justify-end" :class="msg.status === 'policy_error' ? 'text-red-500' : 'text-slate-400'">
                <span v-if="msg.isEdited" class="text-[9px] font-mono italic mr-0.5">diedit</span>
                <span class="text-[9px] font-mono">{{ msg.time }}</span>
                <template v-if="msg.isSender || msg.isSelf">
                  <i v-if="msg.status === 'policy_error'" class="fa-solid fa-circle-exclamation text-[9px]"></i>
                  <i v-else :class="[
                    'fa-solid text-[9px] transition-colors duration-500 ease-in-out',
                    (msg.status === 'failed' || msg.status === 'sending') ? 'fa-check text-slate-400' :
                    (msg.isRead ? 'fa-check-double text-blue-600 read-bounce' : 'fa-check-double text-slate-400')
                  ]"></i>
                </template>
              </div>
            </div>
            
            <!-- Typing Indicator -->
            <div v-if="isTyping" class="flex flex-col items-start mb-2 animate-fade-in-up">
              <div class="bg-white border border-slate-200 px-3 py-2.5 rounded-2xl relative w-fit">
                <div class="flex gap-1.5 items-center">
                  <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                  <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                  <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                </div>
              </div>
            </div>

          </div>

          <!-- Reply / Edit Preview -->
          <div v-if="replyingToMessage || editingMessageId" class="bg-white px-3 pt-2 pb-0 flex items-center justify-between w-full z-10 shrink-0 border-t border-slate-100">
             <div class="flex flex-col flex-1 min-w-0 pr-4 border-l-4 border-[#FFC000] pl-2 bg-slate-50 py-1 rounded-r-md">
                 <span class="text-[10px] font-bold text-[#FFC000]">{{ editingMessageId ? 'Mengedit Pesan' : ('Membalas ' + replyingToMessage.sender_name) }}</span>
                 <span class="text-[10px] text-gray-500 truncate">{{ editingMessageId ? editingMessageOriginal : replyingToMessage.text }}</span>
             </div>
             <button @click="cancelReplyOrEdit" class="text-gray-400 hover:text-gray-600 w-6 h-6 flex items-center justify-center shrink-0 ml-1 rounded-full hover:bg-slate-100 transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
             </button>
          </div>

          <form @submit.prevent="sendChatMessage" class="p-3 bg-white flex items-center gap-2 shrink-0 transition-all" :class="{'pt-1': replyingToMessage || editingMessageId}">
            <input v-model="newChatMessage" @input="handleTyping" type="text" placeholder="Tulis pesan..." class="flex-1 bg-slate-100 text-slate-800 text-xs md:text-sm px-4 py-2.5 rounded-full border-0 focus:ring-2 focus:ring-[#ffc000]" />
            <button type="submit" :disabled="!newChatMessage.trim()" class="w-9 h-9 rounded-full bg-[#ffc000] text-slate-950 flex items-center justify-center disabled:opacity-40">
              <svg class="w-4 h-4 translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </form>
        </template>

      </div>
    </Transition>

    <!-- Context Menu Overlay & Menu -->
    <div v-if="contextMenu.show" 
         class="fixed inset-0 z-[100]" 
         @click="closeContextMenu"
         @touchstart="closeContextMenu">
        <div class="fixed bg-white border border-gray-200 shadow-xl rounded-lg py-1 w-44 text-xs text-gray-700 overflow-hidden"
             :style="{ top: Math.min(contextMenu.y, windowHeight - 160) + 'px', left: Math.min(contextMenu.x, windowWidth - 176) + 'px' }"
             @click.stop
             @touchstart.stop>
            <button @click="handleAction('info')" class="w-full text-left px-3 py-1.5 font-semibold text-gray-500 border-b flex items-center gap-2 hover:bg-gray-100 transition-colors">
              <i class="fa-solid fa-circle-info"></i> Info Pesan
            </button>
            <button @click="handleAction('reply')" class="w-full text-left px-3 py-2 hover:bg-gray-100 flex items-center gap-2 transition-colors">
              <i class="fa-solid fa-reply w-3 text-gray-400"></i> Balas
            </button>
            <button @click="handleAction('copy')" class="w-full text-left px-3 py-2 hover:bg-gray-100 flex items-center gap-2 transition-colors">
              <i class="fa-regular fa-copy w-3 text-gray-400"></i> Salin
            </button>
            <template v-if="contextMenu.message.isSender || contextMenu.message.isSelf">
                <button v-if="isEditable(contextMenu.message)" @click="handleAction('edit')" class="w-full text-left px-3 py-2 hover:bg-gray-100 flex items-center gap-2 transition-colors">
                  <i class="fa-solid fa-pen w-3 text-gray-400"></i> Edit Pesan
                </button>
                <button @click="handleAction('delete')" class="w-full text-left px-3 py-2 hover:bg-gray-100 text-red-600 flex items-center gap-2 transition-colors">
                  <i class="fa-regular fa-trash-can w-3"></i> Hapus
                </button>
            </template>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <ImageViewerModal
        :show="showViewer"
        :images="viewerImages"
        :initial-index="viewerIndex"
        @close="showViewer = false"
    />

    <!-- Message Info Modal -->
    <MessageInfoModal 
        :show="messageInfo !== null"
        :message="messageInfo"
        @close="messageInfo = null"
    />
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import ImageViewerModal from '@/Components/UI/ImageViewerModal.vue'
import MessageInfoModal from '@/Components/UI/MessageInfoModal.vue'

const emit = defineEmits(['update:isOpen'])

const isChatOpen = ref(false)
const searchQuery = ref('')
const activeContactId = ref(null)
const newChatMessage = ref('')
const chatContainer = ref(null)

const messageInfo = ref(null)

const replyingToMessage = ref(null)
const editingMessageId = ref(null)
const editingMessageOriginal = ref('')

const contextMenu = ref({
    show: false,
    x: 0,
    y: 0,
    message: null
});

const windowHeight = ref(window.innerHeight);
const windowWidth = ref(window.innerWidth);

const updateWindowDimensions = () => {
    windowHeight.value = window.innerHeight;
    windowWidth.value = window.innerWidth;
};

const showViewer = ref(false);
const viewerImages = ref([]);
const viewerIndex = ref(0);

const contacts = ref([])
const currentMessages = ref([])
const currentContact = computed(() => contacts.value.find(c => c.id === activeContactId.value))
const isTyping = ref(false)
let typingTimer = null

let chatChannel = null

const totalUnreadCount = computed(() => {
  return contacts.value.reduce((total, contact) => total + (contact.unread || 0), 0);
})

const filteredContacts = computed(() => {
  if (!searchQuery.value) return contacts.value;
  return contacts.value.filter(c => c.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
})

let longPressTimer = null;

const startLongPress = (e, msg) => {
    if (msg.isDeleted || msg.status === 'policy_error') return;
    longPressTimer = setTimeout(() => {
        openContextMenu(e, msg, true);
    }, 500);
};

const cancelLongPress = () => {
    if (longPressTimer) clearTimeout(longPressTimer);
};

const openContextMenu = (e, msg, isTouch = false) => {
    if (msg.isDeleted || msg.status === 'policy_error') return;
    if (longPressTimer) clearTimeout(longPressTimer);
    
    let clientX = e.clientX;
    let clientY = e.clientY;
    
    if (isTouch && e.touches) {
        clientX = e.touches[0].clientX;
        clientY = e.touches[0].clientY;
    }
    
    contextMenu.value = {
        show: true,
        x: clientX,
        y: clientY,
        message: msg
    };
};

const closeContextMenu = () => {
    contextMenu.value.show = false;
};

const hasBeenRepliedTo = (msgId) => {
    return currentMessages.value.some(m => m.replyTo && m.replyTo.id === msgId);
};

const isEditable = (msg) => {
    if (hasBeenRepliedTo(msg.id)) return false;
    if (!msg.timestamp) return true;
    const msgTime = new Date(msg.timestamp).getTime();
    const now = new Date().getTime();
    return (now - msgTime) <= 5 * 60 * 1000;
};

const cancelReplyOrEdit = () => {
    replyingToMessage.value = null;
    editingMessageId.value = null;
    newChatMessage.value = '';
};

const handleAction = async (action) => {
    const msg = contextMenu.value.message;
    closeContextMenu();
    
    if (action === 'info') {
        messageInfo.value = msg;
    } else if (action === 'reply') {
        replyingToMessage.value = {
            id: msg.id,
            text: msg.type === 'image' ? 'Foto' : (msg.type === 'file' ? 'File' : msg.text),
            sender_name: msg.isSender ? 'Anda' : (currentContact.value.name || 'Lawan bicara')
        };
        editingMessageId.value = null;
    } else if (action === 'edit') {
        editingMessageId.value = msg.id;
        editingMessageOriginal.value = msg.text || '';
        newChatMessage.value = msg.text || '';
        replyingToMessage.value = null;
    } else if (action === 'delete') {
        try {
            await axios.delete(`/api/chats/${activeContactId.value}/messages/${msg.id}`);
            msg.isDeleted = true;
            msg.text = null;
            msg.attachments = [];
            msg.file_url = null;
        } catch (error) {
            console.error('Error deleting message:', error);
        }
    } else if (action === 'copy') {
        if (msg.text) {
            navigator.clipboard.writeText(msg.text);
        }
    }
};

const scrollToBottom = () => {
    nextTick(() => { if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight })
}

const getAllImages = () => {
    const allImages = [];
    currentMessages.value.forEach(msg => {
        if (msg.attachments && msg.attachments.length > 0) {
            msg.attachments.forEach(att => {
                allImages.push({ file_url: att.file_url });
            });
        } else if (msg.type === 'image') {
            allImages.push({ file_url: msg.file_url });
        }
    });
    return allImages;
};

const handleTyping = () => {
    if (chatChannel) {
        chatChannel.whisper('typing', {
            userId: usePage().props.auth.user.id
        });
    }
};

const openViewer = (clickedUrl) => {
    const allImages = getAllImages();
    const index = allImages.findIndex(img => img.file_url === clickedUrl);
    viewerImages.value = allImages;
    viewerIndex.value = index !== -1 ? index : 0;
    showViewer.value = true;
};

const fetchContacts = async () => {
  try {
    const response = await axios.get('/api/chats');
    contacts.value = response.data;
  } catch (error) {
    console.error('Error fetching chats:', error);
  }
}

const fetchMessages = async (roomId) => {
  try {
    const response = await axios.get(`/api/chats/${roomId}/messages`);
    currentMessages.value = response.data.messages;

    // Update unread count di kontak
    const contact = contacts.value.find(c => c.id === roomId);
    if (contact) {
      contact.unread = 0;
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
  }
}

const openContactChat = async (id) => {
  activeContactId.value = id;
  await fetchMessages(id);
  scrollToBottom()
}

const sendChatMessage = async () => {
  if (!newChatMessage.value.trim() && !editingMessageId.value) return
  if (!activeContactId.value) return

  const text = newChatMessage.value.trim();
  newChatMessage.value = '';

  if (editingMessageId.value) {
      const editId = editingMessageId.value;
      cancelReplyOrEdit();
      try {
          const response = await axios.put(`/api/chats/${activeContactId.value}/messages/${editId}`, {
              message: text
          });
          const msg = currentMessages.value.find(m => m.id === editId);
          if (msg) {
              msg.text = response.data.message;
              msg.isEdited = true;
          }
      } catch (error) {
          console.error('Error editing message:', error);
      }
      return;
  }

  const replyToId = replyingToMessage.value ? replyingToMessage.value.id : null;
  cancelReplyOrEdit();

  const tempId = Date.now();
  // Optimistic UI update
  currentMessages.value.push({
    id: tempId,
    text: text,
    isSender: true,
    isSelf: true,
    time: 'Baru saja',
    timestamp: new Date().toISOString(),
    status: 'sending',
    isRead: false
  });
  scrollToBottom()

  try {
    const response = await axios.post(`/api/chats/${activeContactId.value}/messages`, { 
        message: text,
        reply_to_id: replyToId
    });
    // Replace with real message data
    const sentMsg = currentMessages.value.find(m => m.id === tempId);
    if (sentMsg) {
      sentMsg.id = response.data.message.id;
      sentMsg.time = response.data.message.time;
      if (response.data.message.timestamp) sentMsg.timestamp = response.data.message.timestamp;
      sentMsg.isRead = response.data.message.isRead;
      sentMsg.status = 'sent';
      sentMsg.replyTo = response.data.message.replyTo;
    }
    await fetchContacts(); // Refresh contact list to show last message
  } catch (error) {
    console.error('Error sending message:', error);
    const failedMsg = currentMessages.value.find(m => m.id === tempId);
    if (failedMsg) {
      if (error.response && error.response.status === 422) {
          failedMsg.status = 'policy_error';
          failedMsg.error_text = error.response.data.error || 'Pesan melanggar kebijakan sistem.';
      } else {
          failedMsg.status = 'failed';
      }
    }
  }
}

const openChatFromBottombar = () => {
  isChatOpen.value = true
  activeContactId.value = null
  fetchContacts()
  emit('update:isOpen', true)
}

const closeChat = () => {
  isChatOpen.value = false
  emit('update:isOpen', false)
}

const toggleChat = () => {
  isChatOpen.value = !isChatOpen.value
  if (isChatOpen.value) {
    activeContactId.value = null
    fetchContacts()
  }
  emit('update:isOpen', isChatOpen.value)
}

watch(activeContactId, (newId, oldId) => {
  if (oldId && chatChannel) {
    window.Echo.leave('chat.' + oldId);
  }
  
  isTyping.value = false;
  
  if (newId) {
      chatChannel = window.Echo.private('chat.' + newId)
        .listen('MessageSent', (e) => {
            if (e.sender_id !== usePage().props.auth.user.id) {
                isTyping.value = false;
                if (!currentMessages.value.some(m => m.id === e.id)) {
                    currentMessages.value.push({
                        id: e.id,
                        type: e.type || 'text',
                        text: e.message,
                        attachments: e.attachments || [],
                        file_url: e.file_url,
                        file_name: e.file_name,
                        isSender: false,
                        time: e.created_at,
                        status: 'sent',
                        isRead: true,
                        replyTo: e.replyTo
                    })
                    scrollToBottom()
                    axios.put(`/api/chats/${newId}/messages/read`)
                    fetchContacts()
                }
            }
        })
        .listen('MessageUpdated', (e) => {
            const msg = currentMessages.value.find(m => m.id === e.message.id);
            if (msg) {
                msg.text = e.message.message;
                msg.isEdited = true;
            }
        })
        .listen('MessageDeleted', (e) => {
            const msg = currentMessages.value.find(m => m.id === e.messageId);
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

onMounted(() => {
  window.addEventListener('resize', updateWindowDimensions);
  // If user is authenticated, we could fetch contacts on load to get the unread count
  if (document.cookie.includes('XSRF-TOKEN')) {
      fetchContacts()
      setInterval(fetchContacts, 10000); // Polling lambat untuk update badge kontak lain
  }
})

onUnmounted(() => {
    window.removeEventListener('resize', updateWindowDimensions);
    if (longPressTimer) clearTimeout(longPressTimer);
});

defineExpose({ openChatFromBottombar, closeChat })
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
