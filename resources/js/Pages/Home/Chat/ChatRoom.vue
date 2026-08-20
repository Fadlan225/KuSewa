<template>
  <main
    :class="[
      'flex-1 flex-col relative min-w-0 bg-[#EFEAE2] overflow-hidden',
      isMobileChatOpen ? 'flex' : 'hidden md:flex'
    ]"
  >
    <!-- Empty State -->
    <div v-if="!activeChatId" class="flex-1 flex flex-col items-center justify-center bg-[#F0F2F5] text-center p-6">
        <NoChatIcon class="w-64 h-64 mb-6 opacity-80" />
        <h2 class="text-2xl font-bold text-gray-700 mb-2">KitaSewa Web</h2>
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

          <div class="flex-1 min-w-0 cursor-pointer overflow-hidden">
              <h2 v-marquee class="font-semibold text-gray-900 text-[15px] leading-tight flex items-center h-[22px] whitespace-nowrap w-fit">
                {{ chatTitle }}
              </h2>
              <p v-if="activeChat.assetName && !activeChat.isContactOwner" class="text-[12px] text-gray-500 flex items-center gap-1 mt-0.5 truncate h-[18px]">
              {{ activeChat.assetName }}
            </p>
          </div>
        </div>
        <button class="text-gray-500 hover:text-gray-700 p-2">
          <MoreVertical class="" />
        </button>
      </div>

      <!-- Chat Header Mobile (DetailNavbar) -->
      <DetailNavbar class="md:hidden shrink-0 z-50">
          <template #content>
            <div class="flex items-center gap-3 w-full pr-4 py-2">
              <button @click="$emit('closeMobile')" class="p-2 -ml-2 text-[#0A2540] hover:text-gray-800 transition-colors">
                <ArrowLeft class="" />
              </button>
              <img v-if="activeChat.avatar" :src="activeChat.avatar" alt="Avatar" class="w-9 h-9 rounded-full object-cover shrink-0">
              <div v-else class="w-9 h-9 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs shadow-sm shrink-0">
                  {{ activeChat.avatarText }}
              </div>
              <div class="flex-1 min-w-0 flex flex-col justify-center overflow-hidden">
                <h2 v-marquee class="font-semibold text-gray-900 text-[14px] leading-tight flex items-center h-[20px] whitespace-nowrap w-fit">
                  {{ chatTitle }}
                </h2>
                <p v-if="activeChat.assetName && !activeChat.isContactOwner" class="text-[11px] text-gray-500 truncate mt-0.5 h-[16px]">
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
                    <Image class="text-xl" />
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
            <!-- Warning Banner -->
            <div class="bg-[#FFF8E6] border-b border-[#FFC000]/20 px-4 py-2.5 flex items-start gap-2.5 z-20">
                <div class="mt-0.5 shrink-0 bg-[#FFC000]/20 p-1 rounded-full text-[#B28600]">
                   <Lock class="w-3.5 h-3.5" />
                </div>
                <div class="flex-1 flex flex-col">
                    <span class="text-[11px] font-bold text-[#8C6900] leading-tight">Keamanan Chat Diawasi Admin</span>
                    <span class="text-[10.5px] text-[#A67C00] leading-snug mt-0.5">Demi keamanan transaksi, dilarang bertukar kontak pribadi (No. HP/Email) atau link eksternal.</span>
                </div>
            </div>

            <!-- Messages Iteration -->
            <template v-for="(msg, index) in messages" :key="index">
              <!-- Date Separator -->
              <div v-if="index === 0 || messages[index - 1].dateLabel !== msg.dateLabel" class="flex justify-center my-4">
                <span class="bg-gray-100 text-gray-600 text-[12px] font-medium px-4 py-1.5 rounded-lg">
                  {{ msg.dateLabel || 'Hari Ini' }}
                </span>
              </div>
              <!-- Bubble Lawan -->
              <div v-if="!msg.isSelf" class="flex flex-col self-start max-w-[85%] md:max-w-[65%] mb-1 select-none" style="-webkit-touch-callout: none;"
                   @contextmenu.prevent="openContextMenu($event, msg)"
                   @touchstart="startLongPress($event, msg)"
                   @touchend="cancelLongPress"
                   @touchmove="cancelLongPress">
                <div class="bg-gray-100 text-gray-800 px-3 py-2 rounded-xl rounded-tl-none text-[14.5px] leading-snug relative">

                  <div v-if="msg.replyTo" class="bg-black/5 border-l-4 border-gray-400 p-2 rounded-r-lg mb-2 text-sm cursor-pointer opacity-80" @click="scrollToMsg(msg.replyTo.id)">
                      <div class="font-semibold text-gray-700 text-xs">{{ msg.replyTo.sender_name }}</div>
                      <div class="text-gray-600 truncate max-w-[200px] md:max-w-[300px] text-xs">{{ msg.replyTo.text }}</div>
                  </div>

                  <template v-if="msg.isDeleted">
                      <div class="italic text-gray-500 text-sm"><Ban class="mr-1" /> Pesan ini telah dihapus</div>
                  </template>
                  <template v-else-if="msg.attachments && msg.attachments.length > 0">
                      <div class="grid gap-1 mb-1 max-w-[250px]" :class="[
                          msg.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
                      ]">
                          <div v-for="(att, idx) in msg.attachments.slice(0, 4)" :key="att.id" class="relative" :class="{'col-span-2': msg.attachments.length === 3 && idx === 0}">
                              <img
                                  :src="att.file_url"
                                  class="w-full object-cover rounded-md cursor-pointer"
                                  :class="msg.attachments.length === 3 && idx === 0 ? 'aspect-[2/1]' : 'aspect-square'"
                                  @click="openViewer(att.file_url)"
                              />
                              <div v-if="idx === 3 && msg.attachments.length > 4" class="absolute inset-0 bg-black/60 flex items-center justify-center rounded-md cursor-pointer text-white font-bold text-lg" @click="openViewer(att.file_url)">
                                  +{{ msg.attachments.length - 4 }}
                              </div>
                          </div>
                      </div>
                      <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                  </template>
                  <template v-else-if="msg.type === 'image'">
                      <img :src="msg.file_url" class="max-w-[200px] md:max-w-[250px] rounded-lg mb-1 cursor-pointer" @click="openViewer(msg.file_url)" />
                      <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                  </template>
                  <template v-else-if="msg.type === 'file'">
                      <a :href="msg.file_url" target="_blank" class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 mb-1 hover:bg-gray-50">
                          <FileText class="text-2xl text-red-500" />
                          <span class="text-sm truncate max-w-[150px]">{{ msg.file_name }}</span>
                          <Download class="text-gray-400 ml-auto" />
                      </a>
                      <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                  </template>
                  <template v-else>
                      <div class="whitespace-pre-wrap">{{ msg.text }}</div>
                  </template>
                  <span class="text-[10px] text-gray-400 ml-3 float-right mt-2"><span v-if="msg.isEdited" class="mr-1 italic">diedit</span>{{ msg.time }}</span>
                </div>
              </div>

              <!-- Bubble Sendiri -->
              <div v-else class="flex flex-col self-end max-w-[85%] md:max-w-[65%] mb-1 select-none" style="-webkit-touch-callout: none;"
                   @contextmenu.prevent="openContextMenu($event, msg)"
                   @touchstart="startLongPress($event, msg)"
                   @touchend="cancelLongPress"
                   @touchmove="cancelLongPress">
                <div :class="msg.status === 'policy_error' ? 'bg-red-50 border border-red-200 text-gray-800' : 'bg-[#FFC000] text-black'" class="px-3 py-2 rounded-xl rounded-tr-none text-[14.5px] leading-snug relative shadow-sm">

                  <div v-if="msg.replyTo" class="bg-black/10 border-l-4 border-yellow-700 p-2 rounded-r-lg mb-2 text-sm cursor-pointer opacity-80" @click="scrollToMsg(msg.replyTo.id)">
                      <div class="font-semibold text-yellow-900 text-xs">{{ msg.replyTo.sender_name }}</div>
                      <div class="text-yellow-800 truncate max-w-[200px] md:max-w-[300px] text-xs">{{ msg.replyTo.text }}</div>
                  </div>

                  <div v-if="msg.status === 'policy_error'" class="text-[11px] md:text-xs text-red-600 font-medium mb-1 pb-1 border-b border-red-100 flex items-start gap-1.5">
                    <AlertCircle class="mt-0.5" />
                    <span>{{ msg.error_text }}</span>
                  </div>

                  <div :class="{'opacity-60': msg.status === 'policy_error'}">
                      <template v-if="msg.isDeleted">
                          <div class="italic text-gray-800 text-sm"><Ban class="mr-1" /> Pesan ini telah dihapus</div>
                      </template>
                      <template v-else-if="msg.attachments && msg.attachments.length > 0">
                          <div class="grid gap-1 mb-1 max-w-[250px]" :class="[
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
                                  <div v-if="idx === 3 && msg.attachments.length > 4" class="absolute inset-0 bg-black/60 flex items-center justify-center rounded-md text-white font-bold text-lg" :class="msg.status === 'policy_error' ? 'cursor-default' : 'cursor-pointer'" @click="msg.status !== 'policy_error' && openViewer(att.file_url)">
                                      +{{ msg.attachments.length - 4 }}
                                  </div>
                              </div>
                          </div>
                          <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                      </template>
                      <template v-else-if="msg.type === 'image'">
                          <img :src="msg.file_url" class="max-w-[200px] md:max-w-[250px] rounded-lg mb-1" :class="msg.status === 'policy_error' ? 'cursor-default' : 'cursor-pointer'" @click="msg.status !== 'policy_error' && openViewer(msg.file_url)" />
                          <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                      </template>
                      <template v-else-if="msg.type === 'file'">
                          <a :href="msg.file_url" target="_blank" class="flex items-center gap-2 p-2 rounded-lg mb-1" :class="msg.status === 'policy_error' ? 'bg-red-100 border border-red-200 pointer-events-none' : 'bg-yellow-400 border border-yellow-500 hover:bg-yellow-500'">
                              <FileText class="text-2xl text-black" />
                              <span class="text-sm truncate max-w-[150px]">{{ msg.file_name }}</span>
                              <Download class="text-black ml-auto" />
                          </a>
                          <div v-if="msg.text" class="mt-1 whitespace-pre-wrap">{{ msg.text }}</div>
                      </template>
                      <template v-else>
                          <div class="whitespace-pre-wrap">{{ msg.text }}</div>
                      </template>
                  </div>

                  <div class="text-[10px] ml-3 float-right mt-1.5 flex items-center gap-1" :class="msg.status === 'policy_error' ? 'text-red-500' : 'text-gray-700'">
                      <span v-if="msg.isEdited" class="mr-1 italic">diedit</span>
                      {{ msg.time }}
                      <AlertCircle v-if="msg.status === 'policy_error'" class="" />
                      <i v-else :class="[
                        'fa-solid transition-colors duration-500 ease-in-out',
                        (msg.status === 'failed' || msg.status === 'sending') ? 'fa-check text-gray-500' :
                        (msg.isRead ? 'fa-check-double text-blue-600 read-bounce' : 'fa-check-double text-gray-500')
                      ]"></i>
                  </div>
                </div>
              </div>
            </template>

            <!-- Typing Indicator -->
            <div v-if="isTyping" class="flex flex-col self-start max-w-[85%] md:max-w-[65%] mb-2 animate-fade-in-up">
              <div class="bg-gray-100 px-4 py-3.5 rounded-xl rounded-tl-none relative w-fit">
                <div class="flex gap-1.5 items-center">
                  <div class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                  <div class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                  <div class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Quick Replies (Ice Breakers) - Sticky above keyboard -->
        <div v-if="messages && messages.length === 0" class="shrink-0 w-full z-10 px-4 md:px-16 pt-2 pb-2 bg-white animate-fade-in-up">
            <div class="bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden flex flex-col md:max-w-4xl md:mx-auto">
                <button v-for="(q, index) in suggestedQuestions" :key="index" @click="sendSuggestion(q)"
                        class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors text-left group"
                        :class="{'border-b border-gray-100': index !== suggestedQuestions.length - 1}">
                    <span class="text-[14px] text-gray-700 font-medium pr-4 group-hover:text-gray-900 transition-colors">{{ q }}</span>
                    <Send class="w-[18px] h-[18px] text-[#FFC000] shrink-0" />
                </button>
            </div>
        </div>

        <!-- Reply / Edit Preview -->
      <div v-if="replyingToMessage || editingMessageId" class="shrink-0 bg-[#F0F2F5] px-4 pt-3 pb-1 flex items-center justify-between w-full z-10 md:px-16" style="border-radius: 12px 12px 0 0; margin-top: -12px;">
         <div class="flex flex-col flex-1 min-w-0 pr-4 border-l-4 border-[#FFC000] pl-3 bg-black/5 py-1.5 rounded-r-lg">
             <span class="text-[12px] font-bold text-[#FFC000]">{{ editingMessageId ? 'Mengedit Pesan' : ('Membalas ' + replyingToMessage.sender_name) }}</span>
             <span class="text-[12px] text-gray-600 truncate">{{ editingMessageId ? editingMessageOriginal : replyingToMessage.text }}</span>
         </div>
         <button @click="cancelReplyOrEdit" class="text-gray-500 hover:text-gray-700 w-8 h-8 flex items-center justify-center shrink-0 ml-2 rounded-full hover:bg-black/5 transition-colors">
            <X class="text-lg" />
         </button>
      </div>

      <!-- Input Chat Desktop -->
      <div class="hidden md:flex bg-[#F0F2F5] px-4 py-3 pt-2 items-end gap-2 shrink-0 z-10 w-full relative">

        <div class="relative">
          <button @click="isAttachmentMenuOpen = !isAttachmentMenuOpen" class="p-2 text-gray-500 hover:text-gray-700 text-xl transition-colors" :class="{'text-[#FFC000]': isAttachmentMenuOpen}">
             <Paperclip class="" />
          </button>

          <!-- Attachment Menu Desktop -->
          <div v-if="isAttachmentMenuOpen" class="absolute bottom-full mb-2 left-0 bg-white rounded-2xl shadow-lg border border-gray-100 p-2 flex flex-col gap-2 z-50 min-w-[160px] animate-fade-in-up">
              <button @click="triggerFile('gallery')" class="flex items-center gap-3 w-full p-2 hover:bg-gray-50 rounded-xl transition-colors text-left">
                  <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                      <Image class="text-lg" />
                  </div>
                  <span class="font-medium text-gray-700 text-sm">Galeri</span>
              </button>
              <button @click="triggerFile('camera')" class="flex items-center gap-3 w-full p-2 hover:bg-gray-50 rounded-xl transition-colors text-left">
                  <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center shrink-0">
                      <Camera class="text-lg" />
                  </div>
                  <span class="font-medium text-gray-700 text-sm">Kamera</span>
              </button>
          </div>
        </div>

        <div class="flex-1 bg-white rounded-lg flex items-center transition-all overflow-hidden shadow-sm">
          <textarea
            v-model="localNewMessage"
            @input="$emit('typing')"
            @keyup.enter.prevent="handleSend"
            rows="1"
            placeholder="Ketik pesan..."
            class="w-full bg-transparent px-4 py-2.5 text-[15px] outline-none resize-none max-h-24 md:max-h-32 text-gray-700 border-none focus:ring-0"
          ></textarea>
        </div>

        <button @click="handleSend" :disabled="!localNewMessage.trim()" class="bg-[#FFC000] hover:bg-yellow-500 disabled:opacity-50 text-black w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors">
          <Send class="text-[15px] -ml-0.5" />
        </button>
      </div>

      <!-- Input Chat Mobile -->
      <div class="md:hidden shrink-0 p-2 z-50 flex items-center gap-2 pb-safe w-full transition-colors" :class="(replyingToMessage || editingMessageId) ? 'bg-[#F0F2F5]' : 'bg-white border-t border-gray-200 shadow-[0_-4px_10px_-2px_rgba(0,0,0,0.05)]'">
        <button @click="isAttachmentMenuOpen = true" class="p-2 text-gray-500 hover:text-gray-700 text-lg transition-colors" :class="{'text-[#FFC000]': isAttachmentMenuOpen}">
            <Paperclip class="" />
        </button>
        <div class="flex-1 bg-gray-50 rounded-full flex items-center transition-all overflow-hidden border border-gray-200">
          <textarea
            v-model="localNewMessage"
            @input="$emit('typing')"
            @keyup.enter.prevent="handleSend"
            rows="1"
            placeholder="Ketik pesan..."
            class="w-full bg-transparent px-4 py-2 text-[14px] outline-none resize-none max-h-20 text-gray-700 border-none focus:ring-0 leading-normal"
          ></textarea>
        </div>
        <button @click="handleSend" :disabled="!localNewMessage.trim()" class="bg-[#FFC000] hover:bg-yellow-500 disabled:opacity-50 text-black w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors mr-1">
          <Send class="text-[14px] -ml-0.5" />
        </button>
      </div>
    </template>

    <!-- Context Menu Overlay & Menu -->
    <div v-if="contextMenu.show"
         class="fixed inset-0 z-[100]"
         @click="closeContextMenu"
         @touchstart="closeContextMenu">
        <div class="fixed bg-white border border-gray-200 shadow-xl rounded-lg py-1 w-48 text-sm text-gray-700 overflow-hidden"
             :style="{ top: Math.min(contextMenu.y, windowHeight - 200) + 'px', left: Math.min(contextMenu.x, windowWidth - 192) + 'px' }"
             @click.stop
             @touchstart.stop>

            <button @click="handleAction('info')" class="w-full text-left px-4 py-2 text-xs font-semibold text-gray-500 border-b flex items-center gap-2 hover:bg-gray-100 transition-colors">
              <Info class="" /> Info Pesan
            </button>
            <button @click="handleAction('reply')" class="w-full text-left px-4 py-2.5 hover:bg-gray-100 flex items-center gap-3 transition-colors">
              <Reply class="w-4 text-gray-400" /> Balas
            </button>
            <button @click="handleAction('copy')" class="w-full text-left px-4 py-2.5 hover:bg-gray-100 flex items-center gap-3 transition-colors">
              <Copy class="w-4 text-gray-400" /> Salin
            </button>
            <template v-if="contextMenu.message.isSelf">
                <button v-if="isEditable(contextMenu.message)" @click="handleAction('edit')" class="w-full text-left px-4 py-2.5 hover:bg-gray-100 flex items-center gap-3 transition-colors">
                  <Pen class="w-4 text-gray-400" /> Edit Pesan
                </button>
                <button @click="handleAction('delete')" class="w-full text-left px-4 py-2.5 hover:bg-gray-100 text-red-600 flex items-center gap-3 transition-colors">
                  <Trash2 class="w-4" /> Hapus
                </button>
            </template>
        </div>
    </div>

    <!-- Hidden File Inputs -->
    <input type="file" ref="galleryInput" accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="handleFileChange" multiple>
    <input type="file" ref="cameraInput" accept=".jpg,.jpeg,.png,.webp" capture="environment" class="hidden" @change="handleFileChange">

    <!-- Attachment Menu Mobile (Bottom Sheet) -->
    <div v-if="isAttachmentMenuOpen" class="md:hidden fixed inset-0 z-[60] flex items-end bg-black/20" @click.self="closeAttachmentMenu">
        <div
            class="bg-[#F0F2F5] w-full rounded-t-3xl p-8 pb-16 shadow-2xl"
            :style="{ transform: sheetTransform, transition: touchStartY === 0 ? 'transform 0.2s ease-out' : 'none' }"
        >
            <div
                class="w-full flex justify-center pb-8 pt-2 -mt-4 cursor-grab touch-none"
                @touchstart="onTouchStart"
                @touchmove.prevent="onTouchMove"
                @touchend="onTouchEnd"
            >
                <div class="w-16 h-1.5 bg-gray-300 rounded-full"></div>
            </div>
            <div class="grid grid-cols-2 gap-6 max-w-[250px] mx-auto">
                <button @click="triggerFile('gallery')" class="flex flex-col items-center gap-2">
                    <div class="w-14 h-14 rounded-full bg-white shadow-sm text-blue-600 flex items-center justify-center border border-gray-100 active:scale-95 transition-transform">
                        <Image class="text-2xl" />
                    </div>
                    <span class="text-xs font-medium text-gray-700">Galeri</span>
                </button>
                <button @click="triggerFile('camera')" class="flex flex-col items-center gap-2">
                    <div class="w-14 h-14 rounded-full bg-white shadow-sm text-pink-600 flex items-center justify-center border border-gray-100 active:scale-95 transition-transform">
                        <Camera class="text-2xl" />
                    </div>
                    <span class="text-xs font-medium text-gray-700">Kamera</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <FilePreviewModal
        :show="previewFiles.length > 0"
        :files="previewFiles"
        @close="cancelPreview"
        @send="sendAttachment"
        @remove="removePreviewFile"
        @addMore="triggerFile('gallery')"
    />

    <!-- Desktop Webcam Modal -->
    <WebcamCaptureModal
        :show="showWebcamModal"
        @close="showWebcamModal = false"
        @capture="handleWebcamCapture"
    />

    <!-- Image Viewer Modal -->
    <ImageViewerModal
        :show="showViewer"
        :images="viewerImages"
        :initial-index="viewerIndex"
        @close="showViewer = false"
    />

    <!-- Toast Component -->
    <Toast
        :show="showToast"
        :message="toastMessage"
        :type="toastType"
    />
  </main>
</template>

<script setup>
import { MoreVertical, ArrowLeft, Image, Lock, Ban, FileText, Download, AlertCircle, X, Paperclip, Camera, Send, Info, Reply, Copy, Pen, Trash2 } from 'lucide-vue-next';
import { ref, watch, nextTick, computed, onMounted, onUnmounted } from 'vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import FilePreviewModal from '@/Components/UI/FilePreviewModal.vue';
import WebcamCaptureModal from '@/Components/UI/WebcamCaptureModal.vue';
import ImageViewerModal from '@/Components/UI/ImageViewerModal.vue';
import Toast from '@/Components/UI/Toast.vue';
import NoChatIcon from '@/Components/UI/Icons/NoChatIcon.vue';

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
  isTyping: {
      type: Boolean,
      default: false
  }
});

const emit = defineEmits(['closeMobile', 'update:newMessage', 'sendMessage', 'typing', 'editMessage', 'deleteMessage']);

const chatTitle = computed(() => {
    if (!props.activeChat) return '';
    let title = props.activeChat.name;
    if (props.activeChat.assetName && props.activeChat.isContactOwner) {
        title += ` - ${props.activeChat.assetName}`;
    }
    return title;
});

const vMarquee = {
    mounted(el) {
        const setupMarquee = () => {
            el.style.animation = 'none';
            el.style.transform = 'translateX(0)';
            
            requestAnimationFrame(() => {
                const scrollWidth = el.scrollWidth;
                const clientWidth = el.parentElement.clientWidth;
                
                if (scrollWidth > clientWidth) {
                    const dist = scrollWidth - clientWidth;
                    el.style.setProperty('--scroll-dist', `${dist}px`);
                    el.style.animation = 'custom-marquee 6s linear infinite alternate';
                }
            });
        };
        
        const observer = new ResizeObserver(setupMarquee);
        observer.observe(el.parentElement);
        el._marqueeObserver = observer;
    },
    updated(el) {
        requestAnimationFrame(() => {
            el.style.animation = 'none';
            requestAnimationFrame(() => {
                const scrollWidth = el.scrollWidth;
                const clientWidth = el.parentElement.clientWidth;
                if (scrollWidth > clientWidth) {
                    const dist = scrollWidth - clientWidth;
                    el.style.setProperty('--scroll-dist', `${dist}px`);
                    el.style.animation = 'custom-marquee 6s linear infinite alternate';
                }
            });
        });
    },
    unmounted(el) {
        if (el._marqueeObserver) {
            el._marqueeObserver.disconnect();
        }
    }
};

const localNewMessage = ref(props.newMessage);
const replyingToMessage = ref(null);
const editingMessageId = ref(null);
const editingMessageOriginal = ref('');

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

onMounted(() => {
    window.addEventListener('resize', updateWindowDimensions);
});
onUnmounted(() => {
    window.removeEventListener('resize', updateWindowDimensions);
    if (longPressTimer) clearTimeout(longPressTimer);
});

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
    return props.messages.some(m => m.replyTo && m.replyTo.id === msgId);
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
    localNewMessage.value = '';
};

const handleAction = (action) => {
    const msg = contextMenu.value.message;
    closeContextMenu();

    if (action === 'info') {
        emit('showInfo', msg);
    } else if (action === 'reply') {
        replyingToMessage.value = {
            id: msg.id,
            text: msg.type === 'image' ? 'Foto' : (msg.type === 'file' ? 'File' : msg.text),
            sender_name: msg.isSelf ? 'Anda' : (props.activeChat.name || 'Lawan bicara')
        };
        editingMessageId.value = null;
    } else if (action === 'edit') {
        editingMessageId.value = msg.id;
        editingMessageOriginal.value = msg.text || '';
        localNewMessage.value = msg.text || '';
        replyingToMessage.value = null;
    } else if (action === 'delete') {
        emit('deleteMessage', msg.id);
    } else if (action === 'copy') {
        if (msg.text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(msg.text)
                    .then(() => displayToast('Pesan berhasil disalin', 'success'))
                    .catch(() => fallbackCopy(msg.text));
            } else {
                fallbackCopy(msg.text);
            }
        } else {
            displayToast('Tidak ada teks untuk disalin', 'error');
        }
    }
};
const isAttachmentMenuOpen = ref(false);
const previewFiles = ref([]);
const showWebcamModal = ref(false);

const showViewer = ref(false);
const viewerImages = ref([]);
const viewerIndex = ref(0);

const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('error');

const fallbackCopy = (text) => {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        const successful = document.execCommand('copy');
        if(successful) {
            displayToast('Pesan berhasil disalin', 'success');
        } else {
            displayToast('Gagal menyalin pesan', 'error');
        }
    } catch (err) {
        displayToast('Gagal menyalin pesan', 'error');
    }
    document.body.removeChild(textArea);
};

const displayToast = (msg, type = 'error') => {
    toastMessage.value = msg;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

const galleryInput = ref(null);
const cameraInput = ref(null);

const touchStartY = ref(0);
const touchCurrentY = ref(0);

const onTouchStart = (e) => {
    touchStartY.value = e.touches[0].clientY;
    touchCurrentY.value = e.touches[0].clientY;
};
const onTouchMove = (e) => {
    touchCurrentY.value = e.touches[0].clientY;
};
const onTouchEnd = () => {
    const deltaY = touchCurrentY.value - touchStartY.value;
    if (deltaY > 90) {
        closeAttachmentMenu();
    }
    touchStartY.value = 0;
    touchCurrentY.value = 0;
};

const sheetTransform = computed(() => {
    if (touchCurrentY.value > touchStartY.value && touchStartY.value !== 0) {
        return `translateY(${touchCurrentY.value - touchStartY.value}px)`;
    }
    return 'translateY(0)';
});

const closeAttachmentMenu = () => {
    isAttachmentMenuOpen.value = false;
};

watch(() => props.newMessage, (val) => {
    localNewMessage.value = val;
});
watch(localNewMessage, (val) => {
    emit('update:newMessage', val);
});

const triggerFile = (type) => {
    isAttachmentMenuOpen.value = false;
    if (type === 'gallery') galleryInput.value.click();
    if (type === 'camera') cameraInput.value.click();
};

const handleFileChange = (e) => {
    const files = Array.from(e.target.files);
    if (!files.length) return;

    if (previewFiles.value.length > 0) {
        let newTotal = previewFiles.value.length + files.length;
        if (newTotal > 30) {
            displayToast('Maksimal 30 gambar dalam satu kali kirim.', 'error');
            files.splice(30 - previewFiles.value.length);
        }
        previewFiles.value = [...previewFiles.value, ...files];
    } else {
        if (files.length > 30) {
            displayToast('Maksimal 30 gambar dalam satu kali kirim.', 'error');
            files.splice(30);
        }
        previewFiles.value = files;
    }
    e.target.value = '';
};

const removePreviewFile = (index) => {
    previewFiles.value.splice(index, 1);
    if (previewFiles.value.length === 0) {
        cancelPreview();
    }
};

const cancelPreview = () => {
    previewFiles.value = [];
};

const sendAttachment = (payload) => {
    emit('sendMessage', { files: payload.files, text: payload.caption });
    cancelPreview();
    nextTick(() => {
        const container = document.getElementById('chat-container');
        if (container) container.scrollTop = container.scrollHeight;
    });
};

const getFileName = (url) => {
    if (!url) return 'file';
    return url.split('/').pop();
};

const getAllImages = () => {
    const allImages = [];
    props.messages.forEach(msg => {
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

const openViewer = (clickedUrl) => {
    const allImages = getAllImages();
    const index = allImages.findIndex(img => img.file_url === clickedUrl);
    viewerImages.value = allImages;
    viewerIndex.value = index !== -1 ? index : 0;
    showViewer.value = true;
};

const scrollToMsg = (id) => {
    // optional logic to scroll to specific message later
};

const suggestedQuestions = [
    "Halo, apakah aset ini masih tersedia?",
    "Apakah ada syarat khusus untuk menyewa ini?",
    "Apakah bisa survei aset terlebih dahulu?"
];

const sendSuggestion = (text) => {
    localNewMessage.value = text;
    handleSend();
};

const handleSend = () => {
    if (!localNewMessage.value.trim()) return;

    if (editingMessageId.value) {
        emit('editMessage', { id: editingMessageId.value, text: localNewMessage.value.trim() });
        cancelReplyOrEdit();
    } else {
        emit('sendMessage', {
            text: localNewMessage.value.trim(),
            replyToId: replyingToMessage.value ? replyingToMessage.value.id : null
        });
        cancelReplyOrEdit();
    }

    nextTick(() => {
        const container = document.getElementById('chat-container');
        if (container) container.scrollTop = container.scrollHeight;
    });
};
</script>

<style scoped>
/* Custom Marquee Animation */
@keyframes custom-marquee {
  0%, 15% { transform: translateX(0); }
  85%, 100% { transform: translateX(calc(-1 * var(--scroll-dist, 0px))); }
}

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
@keyframes slide-up {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up {
  animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
  animation: fade-in-up 0.2s ease-out forwards;
}
</style>
