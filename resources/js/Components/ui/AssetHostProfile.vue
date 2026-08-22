<script setup>
import { MessageCircle, MessageSquareMore } from 'lucide-vue-next';
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import UserAvatar from '@/Components/ui/Icons/UserAvatar.vue';

const props = defineProps({
    assetId: {
        type: [Number, String],
        required: true
    },
    ownerProfile: {
        type: Object,
        required: true
    }
});

const page = usePage();
const chatMessage = ref('');

const startChat = () => {
    if (!page.props.auth?.user) {
        window.location.href = '/login';
        return;
    }

    const messageToSend = chatMessage.value.trim() || 'Halo, masih tersedia?';

    router.post(route('chat.start'), {
        asset_id: props.assetId,
        owner_profile_id: props.ownerProfile.id,
        message: messageToSend
    }, {
        onSuccess: () => {
            // Berhasil dialihkan ke halaman chat
        },
        onError: (err) => {
            if(err.error) {
                alert(err.error);
            } else {
                alert('Gagal memulai chat.');
            }
        }
    });
};
</script>

<template>
    <div class="py-10 md:py-12 scroll-mt-32 md:scroll-mt-40">
        <!-- HEADER DESKTOP & MOBILE -->
        <div class="flex items-center gap-4 pb-10 md:pb-12 border-b border-gray-100">
            <div class="w-14 h-14 rounded-full overflow-hidden shrink-0">
                <img
                    v-if="ownerProfile?.user?.avatar"
                    :src="ownerProfile.user.avatar"
                    class="w-full h-full object-cover"
                />
                <div
                    v-else
                    class="w-full h-full flex items-center justify-center bg-[#f8f9fa] overflow-hidden"
                >
                    <UserAvatar :user="ownerProfile?.user" />
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Pemilik Aset</div>
                <h2 class="flex items-center gap-1.5 text-base sm:text-lg font-bold text-[#0A2540]">
                    {{ ownerProfile?.user?.name || 'Anonim' }}
                    <svg v-if="ownerProfile?.status === 'verified'" class="w-[18px] h-[18px] text-green-500" viewBox="0 0 24 24" fill="currentColor" title="Terverifikasi">
                        <path d="M23 11.99l-2.44-2.79.34-3.69-3.61-.82-1.89-3.2-3.4.14-3.4-.14-1.89 3.2-3.61.82.34 3.69L1 11.99l2.44 2.79-.34 3.69 3.61.82 1.89 3.2 3.4-.14 3.4.14 1.89-3.2 3.61-.82-.34-3.69L23 11.99zm-13.06 5.86l-4.59-4.58 1.41-1.41 3.18 3.18 8.18-8.18 1.41 1.41-9.59 9.58z"/>
                    </svg>
                </h2>
                <div class="text-sm text-gray-500 mt-0.5 flex flex-wrap items-center gap-x-1">
                    <span>Informasi Kontak</span>
                    <template v-if="ownerProfile?.user?.phone">
                        <span class="font-bold text-gray-300">·</span>
                        <a :href="'https://wa.me/' + ownerProfile.user.phone" target="_blank" class="hover:underline text-[#0A2540] flex items-center gap-1 font-medium">
                            <MessageCircle class="text-green-500" /> {{ ownerProfile.user.phone }}
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <!-- Hubungi Pemilik        <!-- DESKRIPSI MOBILE -->
        <div v-if="ownerProfile" class="block lg:hidden mt-6 pb-10 md:pb-12 border-b border-gray-100">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-lg font-bold text-[#0A2540] mb-4">Hubungi Pemilik</h3>
                <div class="flex items-center gap-3 border-b-2 border-gray-800 pb-2 focus-within:border-[#FFC000] transition-colors">
                    <MessageSquareMore class="text-xl text-[#FFC000]" />
                    <input v-model="chatMessage" @keyup.enter="startChat" type="text" placeholder="Tanya sesuatu ke pemilik..." class="w-full bg-transparent border-none outline-none text-sm text-gray-700 placeholder-gray-400 focus:ring-0 p-0" />
                    <button @click="startChat" class="text-[#FFC000] font-bold text-sm hover:text-[#e6ad00] transition-colors whitespace-nowrap">
                        kirim
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
