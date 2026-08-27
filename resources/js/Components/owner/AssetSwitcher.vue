<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { ChevronDown, Map, CheckCircle, Clock, FileEdit, Building } from 'lucide-vue-next';
import NoImageIllustration from '@/Components/ui/Icons/NoImageIllustration.vue';

const props = defineProps({
    currentAssetSlug: {
        type: String,
        default: null
    }
});

const page = usePage();
const ownerAssets = computed(() => page.props.ownerAssets || []);

const isOpen = ref(false);
const dropdownRef = ref(null);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

const currentAsset = computed(() => {
    if (!props.currentAssetSlug) return null;
    return ownerAssets.value.find(a => a.slug === props.currentAssetSlug || a.id == props.currentAssetSlug) || null;
});

const getStatusIcon = (status) => {
    switch(status) {
        case 'approved': return CheckCircle;
        case 'pending': return Clock;
        default: return FileEdit;
    }
};

const getStatusColor = (status) => {
    switch(status) {
        case 'approved': return 'text-emerald-500';
        case 'pending': return 'text-amber-500';
        default: return 'text-slate-400';
    }
};

const switchAsset = (slug) => {
    isOpen.value = false;
    router.post(route('owner.set-active-asset'), { asset_slug: slug }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="relative text-left w-full" ref="dropdownRef">
        <button 
            @click="toggleDropdown" 
            class="flex items-center justify-between w-full gap-2.5 bg-white hover:bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 hover:border-[#FFC000] hover:shadow-sm transition-all focus:outline-none"
        >
            <!-- Mode: Semua Aset -->
            <template v-if="!currentAsset">
                <div class="flex items-center gap-2.5 flex-1 min-w-0">
                    <div class="w-8 h-8 rounded bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                        <Map class="w-4 h-4" />
                    </div>
                    <div class="flex flex-col items-start flex-1 min-w-0">
                        <span class="text-xs text-slate-500 font-semibold leading-tight truncate w-full text-left">Workspace</span>
                        <span class="text-sm font-black text-[#0A2540] leading-tight truncate w-full text-left">Semua Aset</span>
                    </div>
                </div>
            </template>
            <!-- Mode: Single Aset -->
            <template v-else>
                <div class="flex items-center gap-2.5 flex-1 min-w-0">
                    <div class="w-8 h-8 rounded bg-slate-50 shrink-0 overflow-hidden border border-slate-200 flex items-center justify-center relative">
                        <img v-if="currentAsset.thumbnail" :src="currentAsset.thumbnail" class="w-full h-full object-cover" />
                        <div v-else class="absolute inset-0 flex items-center justify-center opacity-50 p-1 bg-slate-50">
                            <NoImageIllustration class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="flex flex-col items-start flex-1 min-w-0 text-left">
                        <span class="text-xs text-slate-500 font-semibold leading-tight truncate w-full text-left">Workspace Aset</span>
                        <span class="text-sm font-black text-[#0A2540] leading-tight truncate w-full text-left">{{ currentAsset.title }}</span>
                    </div>
                </div>
            </template>
            <ChevronDown class="w-4 h-4 text-slate-400 ml-1 transition-transform" :class="isOpen ? 'rotate-180' : ''" />
        </button>

        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div v-if="isOpen" class="absolute left-0 mt-2 w-full bg-white rounded-xl shadow-xl border border-slate-100 z-[100] overflow-hidden">
                <div class="p-2 border-b border-slate-50">
                    <button 
                        @click="switchAsset(null)"
                        class="flex items-center gap-3 w-full p-2 rounded-lg hover:bg-slate-50 transition-colors text-left"
                        :class="!currentAsset ? 'bg-slate-50 border border-slate-100' : ''"
                    >
                        <div class="w-8 h-8 rounded bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <Map class="w-4 h-4" />
                        </div>
                        <div class="flex flex-col items-start flex-1">
                            <span class="text-sm font-bold text-[#0A2540]">Semua Aset</span>
                            <span class="text-[10px] text-slate-400 font-medium">{{ ownerAssets.length }} aset terdaftar</span>
                        </div>
                    </button>
                </div>
                
                <div class="max-h-[300px] overflow-y-auto p-2 space-y-1 custom-scrollbar">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 pt-1 pb-2">Pilih Aset</p>
                    
                    <button
                        v-for="asset in ownerAssets"
                        :key="asset.id"
                        @click="switchAsset(asset.slug || asset.id)"
                        class="flex items-center gap-3 w-full p-2 rounded-lg hover:bg-slate-50 transition-colors group text-left"
                        :class="currentAssetSlug === asset.slug || currentAssetSlug == asset.id ? 'bg-[#FFC000]/10 border border-[#FFC000]/20' : ''"
                    >
                        <div class="w-8 h-8 rounded bg-slate-50 shrink-0 overflow-hidden border border-slate-200 flex items-center justify-center relative">
                            <img v-if="asset.thumbnail" :src="asset.thumbnail" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
                            <div v-else class="absolute inset-0 flex items-center justify-center opacity-50 p-1 bg-slate-50">
                                <NoImageIllustration class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="flex flex-col items-start flex-1 min-w-0">
                            <span class="text-sm font-bold text-[#0A2540] truncate w-full" :title="asset.title">{{ asset.title }}</span>
                            <div class="flex items-center gap-1 mt-0.5">
                                <component :is="getStatusIcon(asset.status)" class="w-3 h-3" :class="getStatusColor(asset.status)" />
                                <span class="text-[10px] capitalize font-medium text-slate-500">{{ asset.status === 'pending' ? 'Menunggu' : asset.status === 'approved' ? 'Terverifikasi' : asset.status }}</span>
                            </div>
                        </div>
                    </button>

                    <div v-if="ownerAssets.length === 0" class="p-4 text-center text-slate-500 text-xs">
                        Belum ada aset terdaftar.
                    </div>
                </div>
                
                <div class="p-2 border-t border-slate-100 bg-slate-50">
                    <Link :href="route('owner.asset.create')" class="flex items-center justify-center gap-2 w-full py-2 text-xs font-bold text-[#0A2540] hover:text-[#d4a000] transition-colors">
                        <Building class="w-3.5 h-3.5" />
                        Daftarkan Aset Baru
                    </Link>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
