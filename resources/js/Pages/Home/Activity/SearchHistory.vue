<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Search, Clock, Trash2, ChevronLeft } from 'lucide-vue-next';
import EmptyStateIcon from '@/Components/UI/Icons/EmptyStateIcon.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    searchLogs: {
        type: Object,
        required: true
    }
});

const isDeleting = ref(false);

const deleteKeyword = (keyword) => {
    if(confirm('Hapus kata kunci ini dari riwayat?')) {
        isDeleting.value = true;
        router.delete(route('search.deleteKeyword'), {
            data: { keyword: keyword },
            preserveScroll: true,
            onFinish: () => isDeleting.value = false
        });
    }
};

const clearAll = () => {
    if(confirm('Anda yakin ingin menghapus SEMUA riwayat pencarian?')) {
        isDeleting.value = true;
        router.delete(route('search.clear'), {
            preserveScroll: true,
            onFinish: () => isDeleting.value = false
        });
    }
};

const searchAgain = (keyword) => {
    router.get(route('assets.search'), { q: keyword });
};

// Calculate time ago
const timeAgo = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));

    if (diffHours < 1) {
        const diffMins = Math.floor(diffMs / (1000 * 60));
        return diffMins <= 0 ? 'Baru saja' : `${diffMins} menit lalu`;
    } else if (diffHours < 24) {
        return `${diffHours} jam lalu`;
    } else {
        const diffDays = Math.floor(diffHours / 24);
        return `${diffDays} hari lalu`;
    }
};
</script>

<template>
    <AppLayout :hideNavbar="true">
        <Head title="Riwayat Pencarian" />

        <div class="bg-[#F8F9FA] min-h-screen pb-24 sm:pb-16">
            <!-- Custom Top Navbar -->
            <div class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">
                <button @click="router.get(route('aktivitas.hub'))" class="p-2 -ml-2 rounded-full hover:bg-slate-50 transition-colors">
                    <ChevronLeft class="w-6 h-6 text-[#1D1D1F]" />
                </button>
                <h1 class="text-base font-bold text-[#1D1D1F]">Riwayat Pencarian</h1>
                <div class="w-10 flex justify-end">
                    <button
                        v-if="searchLogs.data && searchLogs.data.length > 0"
                        @click="clearAll"
                        :disabled="isDeleting"
                        class="text-xs font-semibold text-red-500 disabled:opacity-50 px-2"
                        title="Hapus Semua"
                    >
                        Hapus
                    </button>
                </div>
            </div>

            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 text-[#1D1D1F] min-h-[70vh]">

            <!-- Empty State -->
            <div v-if="!searchLogs.data || searchLogs.data.length === 0" class="bg-white rounded-[1.5rem] border border-slate-200/60 py-16 px-4 text-center shadow-xs flex flex-col items-center justify-center mt-6">
                <EmptyStateIcon class="w-48 h-48 object-contain mb-6 opacity-80" />
                <h2 class="text-xl font-bold text-[#0A2540] mb-2">Riwayat Kosong</h2>
                <p class="text-sm text-[#6C757D] mb-6">Anda belum pernah melakukan pencarian apapun.</p>
                <button @click="router.get(route('assets.search'))" class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors">
                    Mulai Mencari
                </button>
            </div>

            <!-- List -->
            <div v-else class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <ul class="divide-y divide-slate-100">
                    <li
                        v-for="log in searchLogs.data"
                        :key="log.id"
                        class="flex items-center justify-between p-4 sm:p-5 hover:bg-slate-50 transition-colors group cursor-pointer"
                        @click="searchAgain(log.keyword)"
                    >
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-100 text-slate-400 shrink-0">
                                <Search class="w-4 h-4" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm sm:text-base text-[#1D1D1F] truncate group-hover:text-[#FFC000] transition-colors">{{ log.keyword }}</h3>
                                <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                    <Clock class="w-3 h-3" /> {{ timeAgo(log.searched_at) }}
                                </p>
                            </div>
                        </div>

                        <button
                            @click.stop="deleteKeyword(log.keyword)"
                            :disabled="isDeleting"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 disabled:opacity-50"
                            title="Hapus pencarian ini"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Pagination (if more than 1 page) -->
            <div v-if="searchLogs.last_page > 1" class="mt-6 flex justify-center gap-2">
                <button
                    v-for="link in searchLogs.links"
                    :key="link.label"
                    @click="link.url ? router.get(link.url) : null"
                    v-html="link.label"
                    :disabled="!link.url"
                    :class="[
                        'px-3 py-1 text-sm rounded-lg border transition-colors',
                        link.active ? 'bg-[#FFC000] border-[#FFC000] text-[#0A2540] font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50',
                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                ></button>
            </div>

        </div>
        </div>
    </AppLayout>
</template>
