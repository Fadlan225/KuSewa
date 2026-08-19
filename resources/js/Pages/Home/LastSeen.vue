<script setup>
import { Check, Clock, MoreVertical, Trash2, ChevronLeft } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import EmptyStateIcon from '@/Components/UI/Icons/EmptyStateIcon.vue';

const props = defineProps({
  initialViews: {
    type: Object,
    required: true
  }
});

const page = usePage();
const views = ref(props.initialViews.data || []);
const pagination = ref({
    current_page: props.initialViews.current_page,
    last_page: props.initialViews.last_page,
    next_page_url: props.initialViews.next_page_url
});

// Category Filter
const activeFilter = ref('Semua');

const filterTabs = computed(() => {
    const cats = new Set();
    views.value.forEach(v => {
        if (v.asset?.type?.category?.name) {
            cats.add(v.asset.type.category.name);
        }
    });

    const tabs = [{ name: 'Semua', count: views.value.length }];
    Array.from(cats).sort().forEach(cat => {
        tabs.push({
            name: cat,
            count: views.value.filter(v => v.asset?.type?.category?.name === cat).length
        });
    });
    return tabs;
});

const filteredViews = computed(() => {
    if (activeFilter.value === 'Semua') return views.value;
    return views.value.filter(v => v.asset?.type?.category?.name === activeFilter.value);
});

const isLoading = ref(false);
const isSelectionMode = ref(false);
const selectedIds = ref([]);

const toggleSelectionMode = () => {
    isSelectionMode.value = !isSelectionMode.value;
    if (!isSelectionMode.value) {
        selectedIds.value = [];
    }
};
const isSelectAll = computed({
    get: () => views.value.length > 0 && selectedIds.value.length === views.value.length,
    set: (value) => {
        if (value) {
            selectedIds.value = views.value.map(v => v.id);
        } else {
            selectedIds.value = [];
        }
    }
});

const activeMenuId = ref(null);
const toggleMenu = (id) => {
    activeMenuId.value = activeMenuId.value === id ? null : id;
};
const closeMenu = () => {
    activeMenuId.value = null;
};

onMounted(() => {
    document.addEventListener('click', closeMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenu);
});

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

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(price);
};

const getImageUrl = (imgObj) => {
    const imgStr = imgObj?.image_url || imgObj?.image;
    if (!imgStr) return 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=300&q=80';
    if (imgStr.startsWith('http')) return imgStr;
    if (imgStr.startsWith('/assets/') || imgStr.startsWith('/storage/')) return imgStr;
    if (imgStr.startsWith('assets/')) return '/' + imgStr;
    if (imgStr.startsWith('/')) return '/storage' + imgStr;
    return '/storage/' + imgStr;
};

// Infinite Scroll
const loadMore = async () => {
    if (isLoading.value || !pagination.value.next_page_url) return;

    isLoading.value = true;
    try {
        const response = await axios.get(pagination.value.next_page_url);
        const data = response.data;
        views.value = [...views.value, ...data.data];
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            next_page_url: data.next_page_url
        };
    } catch (error) {
        console.error("Failed to load more views:", error);
    } finally {
        isLoading.value = false;
    }
};

const handleScroll = () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
        loadMore();
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// Modals
const isDeleteModalOpen = ref(false);
const deleteMode = ref('single'); // single, bulk, all
const singleIdToDelete = ref(null);

const confirmDeleteSingle = (id) => {
    singleIdToDelete.value = id;
    deleteMode.value = 'single';
    isDeleteModalOpen.value = true;
};

const confirmDeleteBulk = () => {
    if (selectedIds.value.length === 0) return;
    deleteMode.value = 'bulk';
    isDeleteModalOpen.value = true;
};

const confirmDeleteAll = () => {
    deleteMode.value = 'all';
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    setTimeout(() => {
        singleIdToDelete.value = null;
        deleteMode.value = 'single';
    }, 300);
};

const executeDelete = () => {
    isDeleteModalOpen.value = false;

    if (deleteMode.value === 'single' && singleIdToDelete.value) {
        router.delete(route('last-seen.destroy', singleIdToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                views.value = views.value.filter(v => v.id !== singleIdToDelete.value);
                selectedIds.value = selectedIds.value.filter(id => id !== singleIdToDelete.value);
            }
        });
    } else if (deleteMode.value === 'bulk' && selectedIds.value.length > 0) {
        router.delete(route('last-seen.bulkDestroy'), {
            data: { ids: selectedIds.value },
            preserveScroll: true,
            onSuccess: () => {
                views.value = views.value.filter(v => !selectedIds.value.includes(v.id));
                selectedIds.value = [];
                isSelectionMode.value = false;
            }
        });
    } else if (deleteMode.value === 'all') {
        router.delete(route('last-seen.bulkDestroy'), {
            data: { ids: ['all'] },
            preserveScroll: true,
            onSuccess: () => {
                views.value = [];
                selectedIds.value = [];
                isSelectionMode.value = false;
            }
        });
    }
};

</script>

<template>
  <AppLayout :hideNavbar="true">
    <Head title="Terakhir Dilihat" />

    <div class="bg-[#F8F9FA] min-h-screen pb-24 sm:pb-16">
      <!-- Custom Top Navbar -->
      <div class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">
          <button @click="router.get(route('aktivitas.hub'))" class="p-2 -ml-2 rounded-full hover:bg-slate-50 transition-colors">
              <ChevronLeft class="w-6 h-6 text-[#1D1D1F]" />
          </button>
          <h1 class="text-base font-bold text-[#1D1D1F]">Terakhir Dilihat</h1>
          <div class="w-10"></div> <!-- Placeholder -->
      </div>

      <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 text-[#1D1D1F]">

      <!-- Mobile Top: Search or Filter Summary -->
      <div class="flex justify-between items-center mb-5 lg:hidden">
          <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 w-full">
            <button
              v-for="tab in filterTabs"
              :key="tab.name"
              @click="activeFilter = tab.name"
              class="px-3.5 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all duration-200 flex-shrink-0 flex items-center gap-1.5"
              :class="activeFilter === tab.name
                  ? 'bg-[#FFC000] text-[#0A2540] shadow-xs font-semibold'
                  : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
            >
              {{ tab.name }}
              <span
                  class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-bold px-1 transition-colors"
                  :class="activeFilter === tab.name ? 'bg-white text-[#0A2540]' : 'bg-slate-100 text-slate-500'"
              >
                  {{ tab.count }}
              </span>
            </button>
          </div>
      </div>

      <div class="grid grid-cols-12 gap-5 lg:gap-8">
          <!-- SIDEBAR FILTER (Desktop Only) -->
          <aside class="hidden lg:block lg:col-span-3">
              <div class="bg-white backdrop-blur-xl rounded-[1.5rem] border border-slate-100 p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] sticky top-24 space-y-6">
                  <!-- Kategori Filter -->
                  <div>
                      <h3 class="font-medium text-xs text-slate-400 uppercase tracking-wider px-1 mb-2">Kategori Aset</h3>
                      <div class="space-y-1">
                          <button
                              v-for="tab in filterTabs"
                              :key="tab.name"
                              @click="activeFilter = tab.name"
                              class="w-full rounded-xl px-3 py-2 text-left text-xs font-medium transition-all duration-200 flex items-center justify-between group"
                              :class="activeFilter === tab.name
                                  ? 'bg-[#FFC000] text-[#0A2540] shadow-sm'
                                  : 'text-slate-600 hover:bg-slate-100/80'"
                          >
                              <div class="flex items-center gap-2">
                                  <span>{{ tab.name }}</span>
                                  <span
                                      class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-bold px-1 transition-colors"
                                      :class="activeFilter === tab.name ? 'bg-white text-[#0A2540]' : 'bg-slate-200 text-slate-500 group-hover:bg-slate-200/80'"
                                  >
                                      {{ tab.count }}
                                  </span>
                              </div>
                              <Check v-if="activeFilter === tab.name" class="text-[10px] text-[#0A2540]" />
                          </button>
                      </div>
                  </div>
              </div>
          </aside>

          <!-- CONTENT LIST -->
          <section class="col-span-12 lg:col-span-9">

      <!-- Header & Bulk Actions -->
      <div class="flex flex-col sm:flex-row justify-end items-start sm:items-center gap-4 mb-6 px-1">
          <div class="flex items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
              <template v-if="views.length > 0">
                  <button
                      @click="toggleSelectionMode"
                      class="text-xs font-semibold px-4 py-2 rounded-xl transition-colors border w-full sm:w-auto"
                      :class="isSelectionMode ? 'bg-[#FFC000] text-[#0A2540] border-[#FFC000]' : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'"
                  >
                      {{ isSelectionMode ? 'Batal Pilih' : 'Pilih Aset' }}
                  </button>
              </template>
          </div>
      </div>

      <!-- Floating Bulk Action Bar -->
      <transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-4"
      >
          <div v-if="isSelectionMode" class="bg-white rounded-xl shadow-lg border border-slate-200 p-3 sm:px-5 sm:py-3 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 sticky top-24 z-40">
              <div class="flex items-center justify-between sm:justify-start gap-3 w-full sm:w-auto">
                  <label class="flex items-center gap-2 cursor-pointer pl-1">
                      <input type="checkbox" v-model="isSelectAll" class="w-4 h-4 rounded text-[#FFC000] border-slate-300 focus:ring-[#FFC000]">
                      <span class="text-sm font-semibold text-slate-700">Pilih Semua</span>
                  </label>
                  <span class="text-xs text-slate-400 font-medium">({{ selectedIds.length }} dipilih)</span>
              </div>
              <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                  <button
                      v-if="!isSelectAll"
                      @click="confirmDeleteBulk"
                      :disabled="selectedIds.length === 0"
                      class="flex-1 sm:flex-none px-6 py-2 sm:py-1.5 rounded-lg text-xs font-bold transition-all disabled:opacity-50 text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 bg-red-50 sm:bg-transparent text-center"
                  >
                      Hapus Terpilih
                  </button>
                  <button
                      v-else
                      @click="confirmDeleteAll"
                      class="flex-1 sm:flex-none px-6 py-2 sm:py-1.5 rounded-lg text-xs font-bold transition-all text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 bg-red-50 sm:bg-transparent text-center"
                  >
                      Hapus Semua
                  </button>
              </div>
          </div>
      </transition>

      <!-- Empty State -->
      <div v-if="views.length === 0" class="bg-white rounded-[1.5rem] border border-slate-200/60 py-16 px-4 text-center shadow-xs flex flex-col items-center justify-center mt-6">
        <EmptyStateIcon class="w-48 h-48 object-contain mb-6 opacity-80" />
        <h2 class="text-xl font-bold text-[#0A2540] mb-2">Belum ada riwayat</h2>
        <p class="text-sm text-[#6C757D] mb-6">Anda belum melihat aset apapun.</p>
        <button @click="router.get('/')" class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors">
          Jelajahi Aset
        </button>
      </div>

      <!-- Views List -->
      <div v-else class="flex flex-col gap-4">
        <div
          v-for="item in filteredViews"
          :key="item.id"
          class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all flex flex-row group p-3 items-center gap-4 select-none relative"
          :class="{ 'border-[#FFC000] ring-1 ring-[#FFC000]/30 bg-amber-50/10': selectedIds.includes(item.id) }"
        >
          <!-- Selection Checkbox -->
          <div v-if="isSelectionMode" class="pl-2">
              <input type="checkbox" :value="item.id" v-model="selectedIds" class="w-4 h-4 rounded text-[#FFC000] border-slate-300 focus:ring-[#FFC000]">
          </div>

          <!-- Gambar -->
          <div class="w-16 h-16 md:w-20 md:h-20 shrink-0 relative rounded-lg overflow-hidden cursor-pointer bg-slate-100" @click="router.get(`/assets/${item.asset?.slug || item.asset?.id}`)">
            <img :src="getImageUrl(item.asset?.first_image || item.asset?.firstImage)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 pointer-events-none" alt="Asset" onerror="this.src='https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=300&q=80'" />
          </div>

          <!-- Konten Info -->
          <div class="flex-1 min-w-0 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 uppercase tracking-wide">{{ item.asset?.type?.category?.name || 'Aset' }}</span>
                <span class="text-[10px] text-slate-400 flex items-center gap-1"><Clock class="" /> {{ timeAgo(item.last_viewed) }}</span>
            </div>
            <h3 class="font-bold text-sm md:text-base text-[#0A2540] truncate cursor-pointer hover:text-[#FFC000]" @click="router.get(`/assets/${item.asset?.slug || item.asset?.id}`)">
                {{ item.asset?.title }}
            </h3>
            <p class="font-extrabold text-xs md:text-sm text-[#FFC000] leading-none mt-1">
                {{ formatPrice(item.asset?.default_pricing?.price || 0) }}
                <span class="text-[10px] text-slate-400 font-medium">/ {{ item.asset?.type?.rental_unit || 'opsi' }}</span>
            </p>
          </div>

          <!-- Aksi Individu -->
          <div class="shrink-0 relative flex items-center pr-1" v-if="!isSelectionMode">
            <button
                @click.stop.prevent="toggleMenu(item.id)"
                type="button"
                class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-[#0A2540] hover:bg-slate-100 transition-colors cursor-pointer"
                title="Opsi"
            >
                <MoreVertical class="text-sm pointer-events-none" />
            </button>

            <!-- Dropdown Menu -->
            <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div v-if="activeMenuId === item.id" class="absolute right-0 top-10 mt-1 w-32 bg-white rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-slate-100 py-1.5 z-[100]" @click.stop>
                    <button
                        @click="confirmDeleteSingle(item.id); activeMenuId = null"
                        type="button"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition-colors font-semibold"
                    >
                        <Trash2 class="pointer-events-none" />
                        Hapus
                    </button>
                </div>
            </transition>
          </div>
        </div>

        <!-- Loading Indicator -->
        <div v-if="isLoading" class="py-6 flex justify-center items-center">
            <svg class="animate-spin h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>
      </div>

      <!-- POP-UP HAPUS RIWAYAT -->
      <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity p-4">
        <div class="bg-white w-full sm:w-[400px] max-h-[90vh] overflow-y-auto rounded-2xl shadow-xl transform transition-transform animate-fade-in">
          <div class="p-5 md:p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 text-red-500 flex items-center justify-center text-2xl mx-auto mb-4">
              <Trash2 class="" />
            </div>
            <h2 class="text-lg md:text-xl font-bold text-gray-900 mb-2">Hapus Riwayat?</h2>
            <p class="text-sm text-gray-500 mb-6">
                <template v-if="deleteMode === 'single'">Apakah Anda yakin ingin menghapus aset ini dari riwayat terakhir dilihat?</template>
                <template v-else-if="deleteMode === 'bulk'">Apakah Anda yakin ingin menghapus {{ selectedIds.length }} aset terpilih dari riwayat?</template>
                <template v-else>Apakah Anda yakin ingin menghapus SEMUA riwayat terakhir dilihat? Tindakan ini tidak dapat dibatalkan.</template>
            </p>

            <div class="flex gap-3 w-full">
                <button @click="closeDeleteModal" class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors">
                  Batal
                </button>
                <button @click="executeDelete" class="flex-1 py-2.5 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 transition-colors shadow-sm">
                  Ya, Hapus
                </button>
            </div>
          </div>
        </div>
      </div>

          </section>
      </div>

      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

@keyframes fade-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fade-in {
  animation: fade-in 0.2s ease-out forwards;
}
</style>
