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
          {{ totalUnreadCount }}
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
               md:inset-auto md:bottom-24 md:right-6 md:w-96 md:h-[520px] md:rounded-2xl md:shadow-2xl md:border md:border-black/10 md:overflow-hidden"
      >
        
        <!-- VIEW 1: DAFTAR KONTAK -->
        <template v-if="activeContactId === null">
          <div class="bg-[#ffc000] p-4 text-slate-950 flex items-center justify-between shadow-xs">
            <div>
              <h3 class="font-extrabold text-sm md:text-base leading-tight">Pesan & Support</h3>
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
                  <h4 class="font-bold text-xs md:text-sm text-slate-900 truncate">{{ contact.name }}</h4>
                  <span class="text-[10px] text-slate-400 font-mono">{{ getLastTime(contact.id) }}</span>
                </div>
                <p class="text-xs text-slate-500 truncate leading-tight">{{ getLastMessage(contact.id) }}</p>
              </div>
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
                <h3 class="font-bold text-xs md:text-sm leading-tight text-slate-900">{{ currentContact?.name }}</h3>
                <p class="text-[10px] font-medium text-slate-800/80">{{ currentContact?.isOnline ? 'Online' : 'Offline' }}</p>
              </div>
            </div>
            <button @click="closeChat" class="w-8 h-8 rounded-full bg-black/10 flex items-center justify-center text-slate-900">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div ref="chatContainer" class="flex-1 p-4 overflow-y-auto space-y-3 bg-[#f8f9fa]">
            <div v-for="msg in currentMessages" :key="msg.id" class="flex flex-col" :class="msg.isSender ? 'items-end' : 'items-start'">
              <div class="max-w-[85%] px-3.5 py-2.5 rounded-2xl text-xs md:text-sm leading-relaxed" :class="msg.isSender ? 'bg-[#ffc000] text-slate-950 font-medium' : 'bg-white text-slate-800 border border-slate-200'">
                {{ msg.text }}
              </div>
              <span class="text-[9px] text-slate-400 mt-0.5 px-1 font-mono">{{ msg.time }}</span>
            </div>
          </div>

          <form @submit.prevent="sendChatMessage" class="p-3 bg-white border-t border-slate-100 flex items-center gap-2">
            <input v-model="newChatMessage" type="text" placeholder="Tulis pesan..." class="flex-1 bg-slate-100 text-slate-800 text-xs md:text-sm px-4 py-2.5 rounded-full border-0 focus:ring-2 focus:ring-[#ffc000]" />
            <button type="submit" :disabled="!newChatMessage.trim()" class="w-9 h-9 rounded-full bg-[#ffc000] text-slate-950 flex items-center justify-center disabled:opacity-40">
              <svg class="w-4 h-4 translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </form>
        </template>

      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'

const emit = defineEmits(['update:isOpen'])

const isChatOpen = ref(false)
const searchQuery = ref('')
const activeContactId = ref(null)
const newChatMessage = ref('')
const chatContainer = ref(null)

const contacts = ref([
  { id: 1, name: 'Support Kusewa.id', avatarText: 'CS', isOnline: true },
  { id: 2, name: 'Owner Baliho Sudirman', avatarText: 'BS', isOnline: true },
])

const chats = ref({
  1: [{ id: 101, text: 'Halo! Ada yang bisa dibantu mengenai persewaan kamu?', isSender: false, time: '12:00' }],
  2: [{ id: 201, text: 'Halo, lokasi baliho ready untuk bulan depan ya.', isSender: false, time: '11:15' }]
})

const filteredContacts = computed(() => contacts.value.filter(c => c.name.toLowerCase().includes(searchQuery.value.toLowerCase())))
const currentContact = computed(() => contacts.value.find(c => c.id === activeContactId.value))
const currentMessages = computed(() => chats.value[activeContactId.value] || [])
const totalUnreadCount = ref(2)

const getLastMessage = (id) => chats.value[id]?.slice(-1)[0]?.text || ''
const getLastTime = (id) => chats.value[id]?.slice(-1)[0]?.time || ''

// FUNGSI UTAMA DIPANGGIL DARI BOTTOMBAR
const openChatFromBottombar = () => {
  isChatOpen.value = true
  activeContactId.value = null 
  emit('update:isOpen', true)
}

const closeChat = () => {
  isChatOpen.value = false
  emit('update:isOpen', false)
}

const toggleChat = () => {
  isChatOpen.value = !isChatOpen.value
  if (isChatOpen.value) activeContactId.value = null
  emit('update:isOpen', isChatOpen.value)
}

const openContactChat = (id) => {
  activeContactId.value = id
}

const sendChatMessage = () => {
  if (!newChatMessage.value.trim() || !activeContactId.value) return
  chats.value[activeContactId.value].push({
    id: Date.now(),
    text: newChatMessage.value.trim(),
    isSender: true,
    time: 'Baru saja'
  })
  newChatMessage.value = ''
  nextTick(() => { if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight })
}

defineExpose({ openChatFromBottombar, closeChat })
</script>