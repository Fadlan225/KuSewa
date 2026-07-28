<template>
  <div v-if="show && message" :class="[isAbsolute ? 'absolute z-[9999999]' : 'fixed z-[200]', 'inset-0 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm']" @click="close">
    <div class="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-xl flex flex-col" @click.stop>
      <!-- Header -->
      <div class="flex items-center px-4 py-4 bg-white border-b border-gray-100 shadow-sm">
        <button @click="close" class="mr-4 text-gray-500 hover:text-gray-800 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
        <h3 class="text-gray-800 font-bold text-[15px]">Info Pesan</h3>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto max-h-[70vh]">
        <!-- Message Preview -->
        <div class="flex justify-end mb-8">
            <div class="bg-[#FFC000] text-black p-2.5 rounded-xl rounded-tr-none max-w-[85%] text-sm relative shadow-sm">
                <!-- Attachments -->
                <template v-if="message.attachments && message.attachments.length > 0">
                    <div class="grid gap-1 mb-1 max-w-[200px]" :class="[
                        message.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
                    ]">
                        <div v-for="(att, idx) in message.attachments.slice(0, 4)" :key="att.id" class="relative" :class="{'col-span-2': message.attachments.length === 3 && idx === 0}">
                            <img
                                :src="att.file_url"
                                class="w-full object-cover rounded-md"
                                :class="[
                                    message.attachments.length === 3 && idx === 0 ? 'aspect-[2/1]' : 'aspect-square'
                                ]"
                            />
                        </div>
                    </div>
                    <div v-if="message.text" class="mt-1 whitespace-pre-wrap">{{ message.text }}</div>
                </template>
                <template v-else-if="message.type === 'image'">
                    <img :src="message.file_url" class="max-w-[200px] rounded-lg mb-1" />
                    <div v-if="message.text" class="mt-1 whitespace-pre-wrap">{{ message.text }}</div>
                </template>
                <template v-else>
                    <div class="whitespace-pre-wrap">{{ message.text }}</div>
                </template>
                <div class="flex justify-end mt-1 items-center gap-1 opacity-70">
                    <span class="text-[10px]">{{ message.time }}</span>
                    <i class="fa-solid fa-check-double text-[10px]" :class="message.isRead ? 'text-blue-600' : 'text-gray-600'"></i>
                </div>
            </div>
        </div>

        <!-- Read and Delivered Status -->
        <div class="space-y-6 text-gray-700 px-2">
            <!-- Read -->
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-check-double text-blue-500 text-lg w-6 text-center"></i>
                    <span class="font-bold text-gray-900 text-[15px]">Dibaca</span>
                </div>
                <div class="pl-9 text-sm text-gray-500 font-medium">
                    {{ message.isRead ? message.readTime : '-' }}
                </div>
            </div>

            <!-- Delivered -->
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-check-double text-gray-400 text-lg w-6 text-center"></i>
                    <span class="font-bold text-gray-900 text-[15px]">Terkirim</span>
                </div>
                <div class="pl-9 text-sm text-gray-500 font-medium">
                    {{ message.time }}
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    message: {
        type: Object,
        default: null
    },
    isAbsolute: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
</script>
