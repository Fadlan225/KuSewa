<script setup>
import { FileEdit, Clock, XCircle, CheckCircle, Power, Image, ChevronRight, MoreVertical, Trash2, Info, Edit3, X } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AssetCardSkeleton from '@/Components/ui/AssetCardSkeleton.vue';
import ConfirmModal from '@/Components/ui/ConfirmModal.vue';
import { router } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';

const props = defineProps({
    asset: { type: Object, required: true },
    categoryName: { type: String, required: true },
});

const isInfoModalOpen = ref(false);
const isMenuOpen = ref(false);
const menuRef = ref(null);

onClickOutside(menuRef, () => {
    if (isMenuOpen.value) isMenuOpen.value = false;
    if (isInfoModalOpen.value) isInfoModalOpen.value = false;
});

const showConfirmModal = ref(false);

const openDeleteConfirm = () => {
    showConfirmModal.value = true;
};

const deleteAsset = () => {
    showConfirmModal.value = false;
    router.delete(route('owner.asset.destroy', props.asset.id), {
        preserveScroll: true
    });
};

const rentalUnitLabel = (unit) => {
    const labels = { hour: "jam", day: "hari", night: "malam", month: "bulan" };
    return labels[unit] ?? "sewa";
};

const imgList = computed(() => props.asset.thumbnail_images || props.asset.images || []);
const img1 = computed(() => imgList.value[0]?.image_url || props.asset.first_image?.image_url || props.asset.image || props.asset.thumbnail);

const isDesktop = typeof window !== 'undefined' && window.innerWidth >= 1024;
const isIntersecting = ref(isDesktop);
const imageLoaded = ref(false);
const elRef = ref(null);
let observer = null;

onMounted(() => {
    if (isDesktop) return;
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            isIntersecting.value = true;
            observer?.disconnect();
            observer = null;
        }
    }, { rootMargin: '300px' });
    if (elRef.value) observer.observe(elRef.value);
});

onUnmounted(() => {
    observer?.disconnect();
    observer = null;
});

const navigateToAsset = () => {
    if (props.asset.verification_status === 'draft') {
        router.visit(route('owner.asset.edit-draft', props.asset.id));
    } else {
        router.visit(route('owner.asset.show', props.asset.slug || props.asset.id));
    }
};

const formatRupiah = (value) => {
    if (!value) return '';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <div
        ref="elRef"
        :class="['flex-none w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px] snap-start flex flex-col bg-white rounded-md shadow-sm border border-slate-200/60 hover:border-[#FFC000] hover:shadow-md transition-all duration-300 relative', (isMenuOpen || isInfoModalOpen) ? 'z-40 shadow-lg border-[#FFC000]' : 'z-10']"
    >
        <AssetCardSkeleton v-if="!isIntersecting" />

        <div v-else class="w-full h-full flex flex-col group cursor-pointer relative" @click="navigateToAsset">
            <!-- RIBBON BADGE (Placed outside overflow-hidden to allow overhang) -->
            <div class="absolute top-3 -left-1.5 z-20 pointer-events-none flex flex-col gap-1 items-start">
                <!-- Status Verification Badge -->
                <div v-if="asset.verification_status === 'draft'" class="relative bg-slate-200 text-slate-700 text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <FileEdit class="mr-1" />Draft
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-slate-400 border-l-[6px] border-l-transparent"></div>
                </div>
                <div v-else-if="asset.verification_status === 'pending'" class="relative bg-[#FFC000] text-[#0A2540] text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <Clock class="mr-1" />Menunggu
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-[#B38600] border-l-[6px] border-l-transparent"></div>
                </div>
                <div v-else-if="asset.verification_status === 'rejected'" class="relative bg-rose-600 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <XCircle class="mr-1" />Ditolak
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-rose-800 border-l-[6px] border-l-transparent"></div>
                </div>
                <div v-else-if="asset.verification_status === 'approved'" class="relative bg-[#FFC000] text-[#0A2540] text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <CheckCircle class="mr-1" />Terverifikasi
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-[#B38600] border-l-[6px] border-l-transparent"></div>
                </div>
                <div v-else-if="asset.verification_status === 'inactive'" class="relative bg-slate-500 text-white text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-r-md shadow-sm">
                    <Power class="mr-1" />Nonaktif
                    <div class="absolute left-0 -bottom-1.5 w-0 h-0 border-t-[6px] border-t-slate-700 border-l-[6px] border-l-transparent"></div>
                </div>
            </div>

            <!-- KEBAB MENU (3 Dots) -->
            <div class="absolute top-2 right-2 z-30" ref="menuRef" @click.stop>
                <div class="relative inline-block text-left">
                    <button @click="isMenuOpen = !isMenuOpen; isInfoModalOpen = false" class="w-7 h-7 flex items-center justify-center bg-black/30 hover:bg-black/50 text-white rounded-full transition shadow-sm backdrop-blur-sm">
                        <MoreVertical class="w-4 h-4" />
                    </button>
                    <transition
                        enter-active-class="transition duration-100 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-75 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <div v-if="isMenuOpen" class="absolute right-0 mt-2 w-48 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none z-50">
                            <div class="px-1 py-1">
                                <!-- Option for Draft -->
                                <button
                                    v-if="asset.verification_status === 'draft'"
                                    @click="navigateToAsset"
                                    class="text-gray-900 hover:bg-[#FFC000]/20 hover:text-[#997300] group flex w-full items-center rounded-md px-2 py-2 text-xs font-medium transition-colors"
                                >
                                    <Edit3 class="mr-2 h-4 w-4 text-[#FFC000] group-hover:text-[#997300]" aria-hidden="true" />
                                    Lengkapi Data {{ asset.type || categoryName }}
                                </button>
                                <!-- Info Menu -->
                                <button
                                    @click="isInfoModalOpen = true; isMenuOpen = false"
                                    class="text-gray-900 hover:bg-slate-50 hover:text-slate-700 group flex w-full items-center rounded-md px-2 py-2 text-xs font-medium transition-colors"
                                >
                                    <Info class="mr-2 h-4 w-4 text-slate-500" aria-hidden="true" />
                                    Info Aset
                                </button>
                                <!-- Hapus Menu -->
                                <button
                                    v-if="['draft', 'pending', 'rejected'].includes(asset.verification_status)"
                                    @click="openDeleteConfirm(); isMenuOpen = false"
                                    class="text-rose-600 hover:bg-rose-50 hover:text-rose-700 group flex w-full items-center rounded-md px-2 py-2 text-xs font-medium transition-colors"
                                >
                                    <Trash2 class="mr-2 h-4 w-4 text-rose-500" aria-hidden="true" />
                                    Hapus Aset
                                </button>
                            </div>
                        </div>
                    </transition>

                    <!-- INFO POPOVER -->
                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 -translate-x-4"
                        enter-to-class="opacity-100 scale-100 translate-x-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-x-0"
                        leave-to-class="opacity-0 scale-95 -translate-x-4"
                    >
                        <div v-if="isInfoModalOpen" class="absolute right-0 top-full mt-3 sm:right-auto sm:left-full sm:top-0 sm:mt-0 sm:ml-3 w-[280px] sm:w-[320px] origin-top-left bg-white shadow-[0_4px_20px_rgba(0,0,0,0.15)] border border-[#FFC000] z-50 overflow-visible flex flex-col cursor-default rounded-sm">
                            
                            <!-- Arrow UP (Mobile) -->
                            <div class="absolute -top-[7px] right-4 sm:hidden w-3.5 h-3.5 bg-white border-t border-l border-[#FFC000] rotate-45 transform pointer-events-none z-10"></div>
                            
                            <!-- Arrow LEFT (Desktop) -->
                            <div class="hidden sm:block absolute top-3 -left-[7px] w-3.5 h-3.5 bg-white border-b border-l border-[#FFC000] rotate-45 transform pointer-events-none z-10"></div>

                            <div class="relative z-20 bg-transparent rounded-sm">
                                <div class="p-4 flex items-start gap-3">
                                    <div class="shrink-0 mt-0.5">
                                        <div class="w-8 h-8 rounded-sm bg-[#FFC000]/10 border border-[#FFC000]/30 flex items-center justify-center text-[#B38600]">
                                            <Info class="w-4 h-4" />
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-[#0A2540] mb-1 uppercase tracking-tight">
                                            <template v-if="asset.verification_status === 'draft'">Draft Belum Selesai</template>
                                            <template v-else-if="asset.verification_status === 'pending'">Menunggu Verifikasi</template>
                                            <template v-else-if="asset.verification_status === 'rejected'">Aset Ditolak</template>
                                            <template v-else-if="asset.verification_status === 'approved'">Aset Aktif</template>
                                            <template v-else>Informasi Aset</template>
                                        </h3>
                                        <p class="text-xs text-slate-600 leading-relaxed font-medium">
                                            <template v-if="asset.verification_status === 'draft'">
                                                Iklan <span class="font-bold text-[#0A2540]">{{ asset.type || categoryName }}</span> ini masih berupa draft dan belum tampil di halaman pencarian. Silakan lengkapi data untuk mempublikasikan.
                                            </template>
                                            <template v-else-if="asset.verification_status === 'pending'">
                                                Iklan ini sedang ditinjau oleh tim admin kami. Jika disetujui, iklan akan segera tayang.
                                            </template>
                                            <template v-else-if="asset.verification_status === 'rejected'">
                                                Mohon maaf, iklan ini ditolak karena: <span class="font-bold text-rose-600">{{ asset.verification_note || 'Tidak memenuhi kriteria' }}</span>
                                            </template>
                                            <template v-else-if="asset.verification_status === 'approved'">
                                                Iklan Anda sedang tayang dan dapat dilihat oleh para penyewa di halaman pencarian.
                                            </template>
                                            <template v-else>
                                                Iklan ini berstatus {{ asset.verification_status }}.
                                            </template>
                                        </p>
                                    </div>
                                    <button @click="isInfoModalOpen = false" class="absolute top-2 right-2 text-slate-400 hover:text-rose-500 transition">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <div class="bg-slate-50 p-3 border-t border-slate-200 flex items-center justify-end gap-2 rounded-b-sm">
                                    <button @click="deleteAsset(); isInfoModalOpen = false" class="px-3 py-1.5 text-[11px] uppercase tracking-wider font-bold text-rose-600 bg-white border border-rose-200 hover:bg-rose-50 transition rounded-sm">
                                        Hapus
                                    </button>
                                    <button v-if="asset.verification_status === 'draft'" @click="navigateToAsset" class="px-3 py-1.5 text-[11px] uppercase tracking-wider font-bold text-[#0A2540] bg-[#FFC000] hover:bg-[#e6ad00] transition rounded-sm shadow-sm">
                                        Lengkapi Data
                                    </button>
                                    <button v-else @click="isInfoModalOpen = false" class="px-3 py-1.5 text-[11px] uppercase tracking-wider font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition rounded-sm">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- IMAGE SECTION -->
            <div class="aspect-[3/2] w-full relative bg-gray-100 overflow-hidden rounded-t-md">
                <div v-if="asset.first_image?.image && !imageLoaded && !asset.imageError" class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 animate-pulse z-10">
                    <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                </div>

                <div v-if="!img1 || asset.imageError" class="absolute inset-0 w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-300 z-0">
                    <Image class="text-3xl mb-1" />
                    <span class="text-[10px] font-medium">No Image</span>
                </div>
                <div v-else class="absolute inset-0 w-full h-full z-0">
                    <img :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :class="imageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'" loading="lazy" decoding="async" />
                </div>

                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/20 to-transparent pointer-events-none z-10"></div>

                <div v-if="asset.reviews_avg_rating" class="absolute bottom-2 right-2 z-20 bg-[#FFC000] size-7 sm:size-8 rounded-full text-[10px] sm:text-[11px] font-bold text-[#0A2540] flex items-center justify-center shadow-md pointer-events-none">
                    {{ Number(asset.reviews_avg_rating).toFixed(1) }}
                </div>
            </div>

            <!-- CONTENT SECTION -->
            <div class="flex flex-col flex-grow p-2.5 sm:p-3 bg-white rounded-b-md">
                <!-- Location & Type (Top Context) -->
                <div class="text-[9px] sm:text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate mb-0.5">
                    {{ asset.type || categoryName }} &bull; {{ (asset.city?.name || asset.city) || 'Lokasi tidak diketahui' }}
                </div>

                <!-- Title (Primary Anchor) -->
                <h3 class="font-bold text-sm sm:text-[15px] leading-tight text-[#0A2540] line-clamp-1 mb-1">
                    {{ asset.title }}
                </h3>
                
                <!-- Unit Stats (Secondary Context - Subdued) -->
                <div class="flex items-center gap-1.5 text-[9px] sm:text-[10px] text-slate-500 mb-1">
                    <span><span class="font-bold text-slate-700">{{ asset.total_units || 1 }}</span> Unit</span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span><span class="font-bold text-slate-700">{{ asset.occupied_units || 0 }}</span> Terisi</span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span><span class="font-bold text-slate-700">{{ asset.available_units || 0 }}</span> Sisa</span>
                </div>

                <!-- Price (Bottom Anchor) -->
                <div class="flex items-end justify-between mt-auto pt-3 border-t border-slate-100">
                    <div>
                        <span class="block text-[8px] sm:text-[9px] text-slate-400 font-medium mb-0.5">Mulai dari</span>
                        <div class="font-black text-[13px] sm:text-[15px] text-[#0A2540] leading-none">
                            <template v-if="asset.default_pricing || asset.price">
                                {{ formatRupiah(asset.default_pricing?.price || asset.price) }}<span class="text-[10px] sm:text-[11px] font-bold text-slate-500 ml-0.5">/{{ rentalUnitLabel(asset.default_pricing?.rental_unit || asset.type?.rental_unit || asset.rent_period) }}</span>
                            </template>
                            <span v-else class="text-[11px] font-medium text-slate-400">Hubungi</span>
                        </div>
                    </div>
                    
                    <div class="text-slate-300 group-hover:text-[#FFC000] transition-colors flex items-center justify-center pb-0.5 pr-0.5">
                        <ChevronRight class="text-xs" />
                    </div>
                </div>
            </div>
        </div>
        <ConfirmModal
            :show="showConfirmModal"
            title="Hapus Aset"
            message="Apakah Anda yakin ingin menghapus aset ini? Aset yang dihapus tidak akan tampil lagi di daftar."
            @confirm="deleteAsset"
            @cancel="showConfirmModal = false"
        />
    </div>
</template>

<style scoped>
@keyframes shimmer {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(100%);  }
}
.animate-shimmer { animation: shimmer 1.5s infinite; }
</style>
