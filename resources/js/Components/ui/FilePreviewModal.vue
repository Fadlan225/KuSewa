<template>
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="absolute inset-0 z-[50] bg-white flex flex-col md:rounded-b-none shadow-sm border border-gray-200">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 pt-safe text-gray-800 bg-white border-b border-gray-200 shadow-sm z-10 w-full shrink-0">
                <button @click="$emit('close')" class="p-2 hover:bg-gray-100 rounded-full transition-colors active:bg-gray-200">
                    <X class="text-xl" />
                </button>
                <h3 class="font-semibold text-[15px]">Kirim Berkas</h3>
                <div class="w-10"></div>
            </div>

            <!-- Main Preview Area -->
            <div class="flex-1 flex items-center justify-center p-4 md:p-10 w-full h-full overflow-hidden relative">
                <Transition
                    enter-active-class="transition-all duration-300 ease-out transform"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    mode="out-in"
                >
                    <div :key="currentIndex" class="w-full h-full flex justify-center items-center">
                        <img
                            v-if="currentType === 'image'"
                            :src="currentUrl"
                            class="max-w-full max-h-full object-contain shadow-sm rounded-lg bg-black/5"
                        >
                        <div v-else class="flex flex-col items-center gap-4 text-gray-700 bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
                            <FileText class="text-6xl text-gray-400" />
                            <p class="font-medium text-center break-all text-sm md:text-base max-w-[200px]">{{ currentFile?.name }}</p>
                            <p class="text-xs text-gray-500 font-mono">{{ (currentFile?.size / 1024 / 1024).toFixed(2) }} MB</p>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Bottom Controls (Thumbnails, Caption, Send Button) -->
            <div class="bg-white pb-safe z-10 flex flex-col gap-2 shrink-0 border-t border-gray-200">

                <!-- Thumbnails Bar -->
                <div v-if="files.length > 0" class="flex overflow-x-auto px-4 py-3 gap-3 snap-x hide-scrollbar bg-gray-50 border-b border-gray-200">
                    <div
                        v-for="(file, index) in files"
                        :key="index"
                        class="relative flex-shrink-0 w-14 h-14 snap-center cursor-pointer"
                        @click="currentIndex = index"
                    >
                        <div
                            class="w-full h-full rounded-lg overflow-hidden border-2 transition-all"
                            :class="currentIndex === index ? 'border-[#FFC000] scale-105' : 'border-transparent opacity-60 hover:opacity-100'"
                        >
                            <img v-if="isImage(file)" :src="getFileUrl(file)" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-500">
                                <File class="" />
                            </div>
                        </div>
                        <button @click.stop="handleRemove(index)" class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-gray-800 hover:bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm transition-colors z-10">
                            <X class="" />
                        </button>
                    </div>

                    <!-- Add More Button -->
                    <button v-if="files.length < 30" @click="$emit('addMore')" class="flex-shrink-0 w-14 h-14 rounded-lg border-1 border-gray-400 flex items-center justify-center text-gray-500 hover:bg-black/5 transition-colors snap-center">
                        <Plus class="text-xl" />
                    </button>
                </div>

                <!-- Input & Send Button Container -->
                <div class="flex items-end gap-3 px-4 py-3 bg-white">
                    <div class="flex-1 bg-gray-100 rounded-2xl p-2 px-4 focus-within:ring-1 focus-within:ring-[#FFC000] transition-shadow border-0">
                        <textarea
                            v-model="caption"
                            placeholder="Tambahkan keterangan..."
                            class="w-full bg-transparent text-gray-800 placeholder-gray-500 outline-none resize-none max-h-32 text-sm md:text-base py-1 border-0 focus:ring-0 p-0"
                            rows="1"
                            @input="autoResize"
                            ref="captionInput"
                        ></textarea>
                    </div>

                    <button @click="handleSend" class="bg-[#FFC000] hover:bg-yellow-500 text-black w-12 h-12 flex-shrink-0 rounded-full flex items-center justify-center shadow-sm transition-transform active:scale-90 mb-0.5">
                        <Send class="text-xl -ml-1" />
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { X, FileText, File, Plus, Send } from 'lucide-vue-next';
import { ref, watch, computed, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    files: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'send', 'remove', 'addMore']);

const currentIndex = ref(0);
const caption = ref('');
const captionInput = ref(null);

const currentFile = computed(() => props.files[currentIndex.value]);
const currentType = computed(() => {
    if (!currentFile.value) return 'unknown';
    return currentFile.value.type.startsWith('image/') ? 'image' : 'file';
});
const currentUrl = computed(() => getFileUrl(currentFile.value));

const isImage = (file) => file && file.type.startsWith('image/');

const urlMap = new Map();

const getFileUrl = (file) => {
    if (!file || !isImage(file)) return '';
    if (!urlMap.has(file)) {
        urlMap.set(file, URL.createObjectURL(file));
    }
    return urlMap.get(file);
};

const autoResize = () => {
    if (captionInput.value) {
        captionInput.value.style.height = 'auto';
        captionInput.value.style.height = Math.min(captionInput.value.scrollHeight, 120) + 'px';
    }
};

const handleRemove = (index) => {
    emit('remove', index);
};

watch(() => props.files.length, (newLen) => {
    if (currentIndex.value >= newLen && newLen > 0) {
        currentIndex.value = newLen - 1;
    }
});

const revokeUrls = () => {
    urlMap.forEach(url => URL.revokeObjectURL(url));
    urlMap.clear();
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        currentIndex.value = 0;
        caption.value = '';
        revokeUrls();

        nextTick(() => {
            autoResize();
            if (captionInput.value) {
                captionInput.value.focus();
            }
        });
    } else {
        revokeUrls();
    }
});

onUnmounted(() => {
    revokeUrls();
});

const handleSend = () => {
    emit('send', {
        files: props.files,
        caption: caption.value
    });
};
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
