<template>
  <!-- Main Layout -->
  <div class="flex flex-col h-screen bg-gray-50 font-sans">
    
    <!-- Top Header App -->
    <header class="h-14 md:h-16 bg-white shadow-sm border-b px-4 md:px-6 flex items-center justify-between z-10 shrink-0">
      <h1 class="text-lg md:text-xl font-bold text-gray-800">KuSewa Chat</h1>
    </header>

    <!-- Main Content -->
    <div class="flex flex-1 overflow-hidden relative">
      
      <!-- 1. Sidebar Chat (Daftar Chat) -->
      <aside 
        :class="[
          'bg-white border-r flex-col w-full md:w-80 lg:w-1/4 shrink-0 transition-all',
          isMobileChatOpen ? 'hidden md:flex' : 'flex'
        ]"
      >
        <!-- Search Bar -->
        <div class="p-3 md:p-4 border-b border-gray-100 shrink-0">
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">🔍</span>
            <input 
              type="text" 
              placeholder="Cari Chat..." 
              class="w-full pl-10 pr-4 py-2 bg-gray-100 border-transparent rounded-lg focus:bg-white focus:border-[#FFC000] focus:ring-2 focus:ring-[#FFC000]/20 outline-none transition-all text-sm"
            >
          </div>
        </div>

        <!-- Chat List -->
        <div class="flex-1 overflow-y-auto">
          <div 
            v-for="chat in chatList" 
            :key="chat.id"
            @click="selectChat(chat.id)"
            :class="[
              'p-4 flex gap-3 cursor-pointer transition-colors border-b border-gray-50',
              activeChatId === chat.id 
                ? 'bg-[#FFF8E1] md:border-l-4 md:border-l-[#FFC000]' 
                : 'hover:bg-[#FFF8E1] md:border-l-4 md:border-l-transparent'
            ]"
          >
            <!-- Avatar -->
            <img :src="chat.avatar" alt="Avatar" class="w-12 h-12 rounded-full object-cover shrink-0">
            
            <!-- Chat Info -->
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-center mb-1">
                <span class="font-semibold text-sm text-gray-900 truncate">{{ chat.assetName }}</span>
                <span class="text-xs text-gray-400">{{ chat.time }}</span>
              </div>
              <div class="text-sm font-medium text-gray-700 truncate">{{ chat.name }}</div>
              <div class="flex justify-between items-center mt-0.5">
                <p class="text-xs text-gray-500 truncate pr-2">"{{ chat.lastMessage }}"</p>
                <!-- Unread Badge -->
                <span v-if="chat.unread" class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0">
                  {{ chat.unread }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- 2. Area Percakapan -->
      <main 
        :class="[
          'flex-1 flex-col bg-[#F9FAFB] relative min-w-0',
          isMobileChatOpen ? 'flex' : 'hidden md:flex'
        ]"
      >
        
        <!-- Chat Header -->
        <div class="h-14 md:h-16 bg-white border-b px-4 md:px-6 flex items-center justify-between shrink-0 shadow-sm z-10">
          <div class="flex items-center gap-2 md:gap-3">
            
            <!-- Tombol Back (Mobile) -->
            <button 
              @click="isMobileChatOpen = false"
              class="md:hidden p-2 -ml-2 text-gray-500 hover:text-gray-800 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <img :src="activeChat.avatar" alt="Avatar" class="w-9 h-9 md:w-10 md:h-10 rounded-full object-cover">
            <div>
              <h2 class="font-semibold text-gray-800 text-sm md:text-base leading-tight">{{ activeChat.name }}</h2>
              <p class="text-[10px] md:text-xs text-green-500 flex items-center gap-1 mt-0.5">
                <span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-green-500 inline-block"></span> Online
              </p>
            </div>
          </div>
          <button class="text-gray-400 hover:text-gray-600 p-2">⋮</button>
        </div>

        <!-- Chat Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6 flex flex-col gap-3 md:gap-4">
          
          <!-- ========================================== -->
          <!-- DETAIL PENYEWAAN (KHUSUS MOBILE & TABLET)  -->
          <!-- ========================================== -->
          <div class="lg:hidden bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center gap-3 shrink-0 mb-2">
            <img :src="activeChat.assetImage" :alt="activeChat.assetName" class="w-14 h-14 md:w-16 md:h-16 object-cover rounded-lg shrink-0">
            <div class="flex-1 min-w-0">
              <h4 class="font-semibold text-gray-800 text-sm truncate">{{ activeChat.assetName }}</h4>
              <p class="text-xs text-gray-500 mt-0.5">{{ activeChat.price }}</p>
            </div>
            <button class="bg-[#FFC000] text-black text-xs font-semibold px-3 md:px-4 py-2 rounded-lg hover:bg-yellow-500 whitespace-nowrap shadow-sm shrink-0">
              Lihat
            </button>
          </div>

          <!-- Date Separator -->
          <div class="flex justify-center my-2 md:my-4">
            <span class="bg-gray-200 text-gray-600 text-[10px] md:text-xs font-medium px-3 py-1 rounded-full">
              Hari Ini
            </span>
          </div>

          <!-- Messages Iteration -->
          <template v-for="(msg, index) in messages" :key="index">
            <!-- Bubble Lawan -->
            <div v-if="!msg.isSelf" class="flex flex-col self-start max-w-[85%] md:max-w-[70%]">
              <span class="text-[10px] md:text-xs text-gray-500 mb-1 ml-1">{{ activeChat.name }}</span>
              <div class="bg-white border border-gray-200 text-gray-800 p-2.5 md:p-3 rounded-2xl rounded-tl-sm shadow-sm text-sm md:text-base whitespace-pre-wrap">
                {{ msg.text }}
              </div>
              <span class="text-[10px] text-gray-400 mt-1 ml-1">{{ msg.time }}</span>
            </div>

            <!-- Bubble Sendiri -->
            <div v-else class="flex flex-col self-end max-w-[85%] md:max-w-[70%]">
              <div class="bg-[#FFC000] text-black p-2.5 md:p-3 rounded-2xl rounded-tr-sm shadow-sm text-sm md:text-base whitespace-pre-wrap">
                {{ msg.text }}
              </div>
              <span class="text-[10px] text-gray-400 mt-1 text-right mr-1 flex items-center justify-end gap-1">
                {{ msg.time }} <span class="text-blue-600 font-bold">✓✓</span>
              </span>
            </div>
          </template>
        </div>

        <!-- Typing Indicator -->
        <div class="px-4 md:px-6 pb-2 text-[10px] md:text-xs text-gray-400 italic">
          {{ activeChat.name }} sedang mengetik...
        </div>

        <!-- Input Chat -->
        <div class="bg-white border-t p-3 md:p-4 flex items-end gap-2 shrink-0">
          <button class="p-2 text-gray-400 hover:text-gray-600 text-xl hidden sm:block">
            😊
          </button>
          
          <div class="flex-1 bg-gray-100 rounded-xl flex items-center border border-transparent focus-within:border-[#FFC000] focus-within:bg-white transition-all overflow-hidden">
            <textarea 
              rows="1"
              placeholder="Ketik pesan..." 
              class="w-full bg-transparent p-2.5 md:p-3 text-sm md:text-base outline-none resize-none max-h-24 md:max-h-32 text-gray-700"
            ></textarea>
            <button class="p-2 md:p-3 text-gray-500 hover:text-gray-700 text-base md:text-lg">
              📎
            </button>
          </div>

          <button class="bg-[#FFC000] hover:bg-yellow-500 text-black p-2.5 md:p-3 rounded-xl flex items-center justify-center shadow-sm shrink-0">
            <span class="transform rotate-90 text-base md:text-lg font-bold">➜</span>
          </button>
        </div>

      </main>

      <!-- 3. Detail Penyewaan (Hanya Desktop Besar / lg) -->
      <aside class="w-1/4 lg:w-72 bg-white border-l hidden lg:flex flex-col p-6 shrink-0">
        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Detail Penyewaan</h3>
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 shadow-sm">
          <img :src="activeChat.assetImage" :alt="activeChat.assetName" class="w-full h-36 object-cover rounded-lg mb-3">
          <h4 class="font-semibold text-gray-800 text-sm">{{ activeChat.assetName }}</h4>
          <p class="text-xs text-gray-500 mb-4">{{ activeChat.price }}</p>
          <button class="w-full bg-[#FFC000] text-black text-sm font-semibold py-2 rounded-lg hover:bg-yellow-500 transition-colors shadow-sm">
            Lihat Transaksi
          </button>
        </div>
      </aside>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const isMobileChatOpen = ref(false);
const activeChatId = ref(1);

// Menambahkan `assetImage` dan `price` untuk menampilkan kartu dengan baik
const chatList = ref([
  {
    id: 1,
    name: 'Ahmad',
    assetName: '🏠 Villa Bromo',
    assetImage: 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=300&h=200&fit=crop',
    price: 'Rp 1.500.000 / malam',
    avatar: 'https://i.pravatar.cc/150?img=11',
    lastMessage: 'Baik kak, besok...',
    time: '09:30',
    unread: 2
  },
  {
    id: 2,
    name: 'Rina',
    assetName: '🚗 Avanza Putih 2022',
    assetImage: 'https://images.unsplash.com/photo-1469285994282-454ceb49e63c?w=300&h=200&fit=crop',
    price: 'Rp 350.000 / hari',
    avatar: 'https://i.pravatar.cc/150?img=5',
    lastMessage: 'Masih tersedia?',
    time: 'Kemarin',
    unread: 0
  },
  {
    id: 3,
    name: 'Andi',
    assetName: '📷 Kamera Sony A7III',
    assetImage: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=300&h=200&fit=crop',
    price: 'Rp 200.000 / hari',
    avatar: 'https://i.pravatar.cc/150?img=8',
    lastMessage: 'Terima kasih',
    time: 'Senin',
    unread: 0
  }
]);

const messages = ref([
  { text: 'Halo kak, villa masih tersedia?', isSelf: false, time: '09:28' },
  { text: 'Ya masih kak\nuntuk tanggal berapa?', isSelf: true, time: '09:29' },
  { text: 'Tanggal 21.', isSelf: false, time: '09:30' }
]);

const selectChat = (id) => {
  activeChatId.value = id;
  isMobileChatOpen.value = true;
};

const activeChat = computed(() => {
  return chatList.value.find(c => c.id === activeChatId.value) || chatList.value[0];
});
</script>

<style scoped>
/* Custom Scrollbar */
::-webkit-scrollbar { width: 4px; }
@media (min-width: 768px) { ::-webkit-scrollbar { width: 6px; } }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>