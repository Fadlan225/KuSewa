<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  bookings: {
    type: Array,
    default: () => []
  }
});

const page = usePage();
const activeFilter = ref('Semua');
const sort = ref('Terbaru');
const isSortOpenMobile = ref(false);
const isSortOpenDesktop = ref(false);

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) {
        const s = params.get('status');
        if (['Semua', 'Berlangsung', 'Belum Bayar', 'Menunggu', 'Selesai', 'Dibatalkan'].includes(s)) {
            activeFilter.value = s;
        }
    }
});

const sortOptions = [
    { label: 'Terbaru', icon: 'fa-solid fa-arrow-down-short-wide' },
    { label: 'Terlama', icon: 'fa-solid fa-arrow-up-wide-short' }
];

const selectSort = (val) => {
    sort.value = val;
    isSortOpenDesktop.value = false;
    isSortOpenMobile.value = false;
};

// Formatting price
const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(price);
};

// Date formatting for card display
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  }).format(date);
};

// Helper for group headers (Hari Ini, Kemarin, dll)
const getGroupLabel = (dateString) => {
  if (!dateString) return 'Lainnya';
  
  const date = new Date(dateString);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  
  const isSameDay = (d1, d2) => 
    d1.getFullYear() === d2.getFullYear() &&
    d1.getMonth() === d2.getMonth() &&
    d1.getDate() === d2.getDate();
    
  if (isSameDay(date, today)) return 'Hari Ini';
  if (isSameDay(date, yesterday)) return 'Kemarin';
  
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric'
  }).format(date);
};

// Favorite Logic
function getXsrfToken() {
    const match = document.cookie.match(new RegExp('(^|;\\s*)XSRF-TOKEN=([^;]*)'));
    return match ? decodeURIComponent(match[2]) : '';
}

const toggleFavorite = async (item) => {
    if (item.isPendingFavorite) return;
    
    const nextState = !item.isFavorite;
    item.isFavorite = nextState;
    item.isPendingFavorite = true;
    
    const user = page.props.auth?.user;
    if (!user) {
        item.isFavorite = !nextState;
        item.isPendingFavorite = false;
        router.visit(route('login'));
        return;
    }

    try {
        if (nextState) {
            const res = await fetch(route('favorites.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': getXsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ asset_id: item.raw.asset_id }),
            });
            if (!res.ok) throw new Error('store failed');
            const data = await res.json();
            item.favoriteId = data.favorite_id;
        } else {
            if (!item.favoriteId) return;
            const res = await fetch(route('favorites.destroy', item.favoriteId), {
                method: 'DELETE',
                headers: {
                    'X-XSRF-TOKEN': getXsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            if (!res.ok && res.status !== 404) throw new Error('destroy failed');
            item.favoriteId = null;
        }
    } catch {
        item.isFavorite = !nextState;
    } finally {
        item.isPendingFavorite = false;
    }
};

const activitiesBase = ref(
  props.bookings.map(b => {
    let category = 'Menunggu';
    let statusText = 'Menunggu Konfirmasi';
    let statusIcon = 'fas fa-clock';
    let statusColor = '#F59E0B';
    let progressSteps = ['Booking', 'Dikonfirmasi', 'Bayar', 'Digunakan', 'Selesai'];
    let currentStepIndex = 0;
    let progressPercent = 10;

    const bs = b.booking_status;
    const ps = b.payment?.payment_status;

    if (bs === 'cancelled') {
      category = 'Dibatalkan';
      statusText = 'Dibatalkan';
      statusIcon = 'fas fa-times-circle';
      statusColor = '#EF4444';
      currentStepIndex = -1;
      progressPercent = 0;
    } else if (ps === 'rejected') {
      category = 'Dibatalkan';
      statusText = 'Pembayaran Ditolak';
      statusIcon = 'fas fa-times-circle';
      statusColor = '#EF4444';
      currentStepIndex = -1;
      progressPercent = 0;
    } else if (ps === 'expired') {
      category = 'Dibatalkan';
      statusText = 'Kadaluarsa';
      statusIcon = 'fas fa-clock';
      statusColor = '#9CA3AF';
      currentStepIndex = -1;
      progressPercent = 0;
    } else if (bs === 'completed') {
      category = 'Selesai';
      statusText = 'Selesai';
      statusIcon = 'fas fa-check-circle';
      statusColor = '#10B981';
      currentStepIndex = 4;
      progressPercent = 100;
    } else if (bs === 'active') {
      category = 'Berlangsung';
      statusText = 'Sedang Digunakan';
      statusIcon = 'fas fa-circle-dot';
      statusColor = '#10B981';
      currentStepIndex = 3;
      progressPercent = 80;
    } else if (bs === 'confirmed' && ps === 'paid') {
      category = 'Berlangsung';
      statusText = 'Dikonfirmasi - Siap Digunakan';
      statusIcon = 'fas fa-circle-dot';
      statusColor = '#10B981';
      currentStepIndex = 2;
      progressPercent = 65;
    } else if (ps === 'verifying') {
      category = 'Menunggu';
      statusText = 'Menunggu Verifikasi Pembayaran';
      statusIcon = 'fas fa-hourglass-half';
      statusColor = '#F59E0B';
      currentStepIndex = 2;
      progressPercent = 50;
    } else if (bs === 'confirmed' && (!ps || ps === 'pending')) {
      category = 'Belum Bayar';
      statusText = 'Menunggu Pembayaran';
      statusIcon = 'fas fa-credit-card';
      statusColor = '#F59E0B';
      currentStepIndex = 1;
      progressPercent = 40;
    } else {
      // bs === 'pending'
      category = 'Menunggu';
      statusText = 'Menunggu Konfirmasi';
      statusIcon = 'fas fa-clock';
      statusColor = '#F59E0B';
      currentStepIndex = 0;
      progressPercent = 15;
    }

    let actions = [];
    if (bs === 'pending') {
      actions.push({ label: 'Batalkan', primary: false, actionId: 'cancel' });
    }
    if (bs === 'confirmed' && (!ps || ps === 'pending')) {
      actions.push({ label: 'Bayar Sekarang', primary: true, actionId: 'pay' });
      actions.push({ label: 'Batalkan', primary: false, actionId: 'cancel' });
    }

    // Default Images
    let images = [];
    const firstImg = b.asset?.first_image || b.asset?.firstImage;
    const imgStr = firstImg?.image_url || firstImg?.image;
    if (imgStr) {
      if (imgStr.startsWith('http')) {
        images.push(imgStr);
      } else if (imgStr.startsWith('/assets/') || imgStr.startsWith('/storage/')) {
        images.push(imgStr);
      } else if (imgStr.startsWith('assets/')) {
        images.push('/' + imgStr);
      } else if (imgStr.startsWith('/')) {
        images.push('/storage' + imgStr);
      } else {
        images.push('/storage/' + imgStr);
      }
    }
    if (images.length === 0) {
      images.push('https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=300&q=80');
      images.push('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=150&q=80');
      images.push('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=150&q=80');
    }
    
    const dateObj = new Date(b.created_at || b.start_date);

    return {
      id: b.id,
      payment_id: b.payment?.id,
      type: b.asset?.type?.category?.name || 'Aset',
      name: b.asset?.title || 'Unknown',
      images: images,
      totalPrice: formatPrice(b.total),
      category: category,
      status: statusText,
      statusIcon: statusIcon,
      statusColor: statusColor,
      dateString: `${formatDate(b.start_date)} - ${formatDate(b.end_date)}`,
      createdAt: dateObj,
      groupLabel: getGroupLabel(b.created_at || b.start_date),
      progress: true,
      progressSteps: progressSteps,
      currentStepIndex: currentStepIndex,
      progressPercent: progressPercent,
      actions: actions,
      raw: b,
      isFavorite: b.asset?.isFavorite ?? false,
      favoriteId: b.asset?.favorite_id ?? null,
      isPendingFavorite: false,
      rating: b.asset?.reviews_avg_rating ? Number(b.asset.reviews_avg_rating).toFixed(1) : null
    };
  })
);

const filterTabs = computed(() => {
  return [
    { name: 'Semua', count: activitiesBase.value.length },
    { name: 'Berlangsung', count: activitiesBase.value.filter(a => a.category === 'Berlangsung').length },
    { name: 'Belum Bayar', count: activitiesBase.value.filter(a => a.category === 'Belum Bayar').length },
    { name: 'Menunggu', count: activitiesBase.value.filter(a => a.category === 'Menunggu').length },
    { name: 'Selesai', count: activitiesBase.value.filter(a => a.category === 'Selesai').length },
    { name: 'Dibatalkan', count: activitiesBase.value.filter(a => a.category === 'Dibatalkan').length }
  ];
});

const groupedActivities = computed(() => {
  // Filter
  let filtered = activitiesBase.value;
  if (activeFilter.value !== 'Semua') {
      filtered = filtered.filter(item => item.category === activeFilter.value);
  }
  
  // Sort
  if (sort.value === 'Terbaru') {
      filtered = [...filtered].sort((a, b) => b.createdAt.getTime() - a.createdAt.getTime());
  } else {
      filtered = [...filtered].sort((a, b) => a.createdAt.getTime() - b.createdAt.getTime());
  }
  
  // Group by day
  const groups = {};
  filtered.forEach(item => {
      const label = item.groupLabel;
      if(!groups[label]) groups[label] = [];
      groups[label].push(item);
  });
  
  // Convert object to array preserving order from the first element of each group
  // Actually, since we sorted the array, Object keys might not preserve order.
  const orderedGroups = [];
  const groupNamesEncountered = new Set();
  filtered.forEach(item => {
      const label = item.groupLabel;
      if (!groupNamesEncountered.has(label)) {
          groupNamesEncountered.add(label);
          orderedGroups.push({
              label: label,
              items: groups[label]
          });
      }
  });
  
  return orderedGroups;
});

const isCancelModalOpen = ref(false);
const selectedAssetToCancel = ref(null);
const cancelReason = ref('');

const handleActionClick = (action, item) => {
  if (action.actionId === 'cancel') {
    openCancelModal(item);
  } else if (action.actionId === 'pay') {
    if(item.payment_id) {
       router.get(`/payment/${item.payment_id}`);
    } else {
       alert("Data pembayaran belum tersedia");
    }
  } else {
    console.log(`Action dipicu: ${action.label} pada ${item.name}`);
  }
};

const openCancelModal = (item) => {
  selectedAssetToCancel.value = item;
  cancelReason.value = '';
  isCancelModalOpen.value = true;
};

const closeCancelModal = () => {
  isCancelModalOpen.value = false;
  setTimeout(() => {
    selectedAssetToCancel.value = null;
  }, 300);
};

const processCancellation = () => {
  if (!cancelReason.value) return;
  console.log(`Membatalkan ${selectedAssetToCancel.value.name} dengan alasan: ${cancelReason.value}`);
  closeCancelModal();
  alert('Penyewaan berhasil dibatalkan (Simulasi)');
};
</script>

<template>
  <AppLayout>
    <Head title="Aktivitas Saya - KuSewa" />
    
    <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-10 pb-24 sm:pb-16 text-[#1D1D1F]">

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
                      <h3 class="font-medium text-xs text-slate-400 uppercase tracking-wider px-1 mb-2">Status Pesanan</h3>
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
                              <i v-if="activeFilter === tab.name" class="fa-solid fa-check text-[10px] text-[#0A2540]"></i>
                          </button>
                      </div>
                  </div>

                  <!-- Dropdown Urutkan -->
                  <div>
                      <h3 class="font-medium text-xs text-slate-400 uppercase tracking-wider px-1 mb-2">Urutkan</h3>
                      <div class="relative">
                          <button
                              @click="isSortOpenDesktop = !isSortOpenDesktop"
                              class="w-full flex items-center justify-between rounded-xl bg-slate-100/80 hover:bg-slate-200/60 border-0 px-3 py-2 text-xs font-medium text-[#1D1D1F] transition-colors"
                          >
                              <div class="flex items-center gap-2">
                                  <i :class="sortOptions.find(o => o.label === sort)?.icon || 'fa-solid fa-clock-rotate-left'" class="text-slate-500 w-3 text-center"></i>
                                  {{ sort }}
                              </div>
                              <i class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform" :class="isSortOpenDesktop ? 'rotate-180' : ''"></i>
                          </button>

                          <!-- Dropdown Menu -->
                          <Transition
                              enter-active-class="transition ease-out duration-100"
                              enter-from-class="transform opacity-0 scale-95"
                              enter-to-class="transform opacity-100 scale-100"
                              leave-active-class="transition ease-in duration-75"
                              leave-from-class="transform opacity-100 scale-100"
                              leave-to-class="transform opacity-0 scale-95"
                          >
                              <div v-if="isSortOpenDesktop" class="absolute z-50 left-0 right-0 mt-2 w-full origin-top-left rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden">
                                  <div class="py-1">
                                      <button
                                          v-for="option in sortOptions"
                                          :key="option.label"
                                          @click="selectSort(option.label)"
                                          class="w-full flex items-center gap-2 px-3 py-2.5 text-xs text-left"
                                          :class="sort === option.label ? 'bg-amber-50 text-[#0A2540] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#1D1D1F]'"
                                      >
                                          <i :class="[option.icon, sort === option.label ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center"></i>
                                          {{ option.label }}
                                      </button>
                                  </div>
                              </div>
                          </Transition>
                      </div>
                  </div>
              </div>
          </aside>

          <!-- CONTENT LIST -->
          <section class="col-span-12 lg:col-span-9">
              <div class="flex justify-between items-center mb-4 px-1">
                  <div>
                      <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1F]">
                          {{ activeFilter === 'Semua' ? 'Semua Pesanan' : 'Pesanan ' + activeFilter }}
                      </h2>
                      <p class="text-slate-400 text-xs mt-0.5">
                          Menampilkan total {{ filterTabs.find(t => t.name === activeFilter)?.count || 0 }} pesanan
                      </p>
                  </div>

                  <!-- Sort Dropdown untuk Mobile -->
                  <div class="block lg:hidden relative">
                      <button
                          @click="isSortOpenMobile = !isSortOpenMobile"
                          class="flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs font-medium text-[#1D1D1F] transition-colors shadow-xs"
                      >
                          <i :class="sortOptions.find(o => o.label === sort)?.icon || 'fa-solid fa-clock-rotate-left'" class="text-slate-500 text-[10px]"></i>
                          {{ sort }}
                          <i class="fa-solid fa-chevron-down text-slate-400 text-[9px] ml-1 transition-transform" :class="isSortOpenMobile ? 'rotate-180' : ''"></i>
                      </button>

                      <Transition
                          enter-active-class="transition ease-out duration-100"
                          enter-from-class="transform opacity-0 scale-95"
                          enter-to-class="transform opacity-100 scale-100"
                          leave-active-class="transition ease-in duration-75"
                          leave-from-class="transform opacity-100 scale-100"
                          leave-to-class="transform opacity-0 scale-95"
                      >
                          <div v-if="isSortOpenMobile" class="absolute z-50 right-0 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden border border-slate-100">
                              <div class="py-1">
                                  <button
                                      v-for="option in sortOptions"
                                      :key="option.label"
                                      @click="selectSort(option.label)"
                                      class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-xs text-left"
                                      :class="sort === option.label ? 'bg-amber-50 text-[#0A2540] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#1D1D1F]'"
                                  >
                                      <i :class="[option.icon, sort === option.label ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center text-[10px]"></i>
                                      {{ option.label }}
                                  </button>
                              </div>
                          </div>
                      </Transition>
                  </div>
              </div>

              <div v-if="groupedActivities.length === 0" class="bg-white rounded-2xl sm:rounded-[1.5rem] border border-slate-200/60 py-12 sm:py-16 px-4 text-center shadow-xs flex flex-col items-center justify-center mt-6">
                <img src="/empty.svg" alt="No Activity" class="w-48 h-48 object-contain mb-6 opacity-80" onerror="this.src='https://placehold.co/400x300?text=No+Data'" />
                <h2 class="text-xl font-bold text-[#0A2540] mb-2">Belum ada aktivitas</h2>
                <p class="text-sm text-[#6C757D] mb-6">Mulai cari aset untuk disewa dan pantau di sini.</p>
                <button @click="router.get('/')" class="px-6 py-2.5 rounded bg-[#FFC000] text-[#0A2540] text-sm font-bold uppercase tracking-wide hover:bg-[#e6ad00] transition-colors">
                  Cari Aset
                </button>
              </div>

              <template v-else>
                <div v-for="group in groupedActivities" :key="group.label" class="mb-8">
                  <!-- Header Group Tanggal -->
                  <div class="flex items-center gap-3 mb-4 px-1">
                      <h3 class="text-sm font-bold text-[#1D1D1F]">{{ group.label }}</h3>
                      <div class="h-px bg-slate-200 flex-1"></div>
                  </div>

                  <div class="flex flex-col gap-4">
                    <div
                      v-for="item in group.items"
                      :key="item.id"
                      class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-row overflow-hidden group p-2.5 md:p-3 items-center gap-3 md:gap-4 select-none [-webkit-touch-callout:none]"
                    >
                      <!-- Gambar -->
                      <div class="w-16 h-16 md:w-20 md:h-20 shrink-0 relative rounded-lg overflow-hidden cursor-pointer bg-slate-100" @click="router.get(`/booking/${item.id}`)">
                        <img :src="item.images[0]" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 pointer-events-none" alt="Main" onerror="this.src='https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=300&q=80'" />
                      </div>

                      <!-- Konten Info & Progress -->
                      <div class="flex-1 min-w-0 flex flex-col justify-center">
                        <h3 class="font-bold text-sm md:text-base text-[#0A2540] truncate cursor-pointer hover:text-[#FFC000]" @click="router.get(`/booking/${item.id}`)">
                            {{ item.name }}
                        </h3>

                        <!-- Progress Bar Minimalis -->
                        <div v-if="item.progress" class="mt-1.5 md:mt-2 w-full max-w-sm">
                          <div class="flex justify-between items-center mb-1">
                             <span class="text-[9px] md:text-[10px] font-bold" :style="{ color: item.statusColor }">{{ item.status }}</span>
                          </div>
                          <div class="w-full h-1.5 md:h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#10B981] transition-all" :style="{ width: item.progressPercent + '%' }"></div>
                          </div>
                        </div>
                      </div>

                      <!-- Harga & Aksi -->
                      <div class="shrink-0 flex flex-col items-end justify-between self-stretch py-0.5">
                        <div class="text-right mb-2 md:mb-0">
                          <p class="font-extrabold text-xs md:text-sm text-[#FFC000] leading-none">{{ item.totalPrice }}</p>
                        </div>

                        <div class="flex gap-1.5 md:gap-2 mt-auto">
                          <button
                            v-for="(action, idx) in item.actions"
                            :key="idx"
                            @click="handleActionClick(action, item)"
                            :class="[
                              'px-2.5 md:px-3 py-1 md:py-1.5 rounded-lg text-[9px] md:text-[10px] font-bold transition-all shadow-sm active:scale-95',
                              action.primary
                                ? 'bg-[#FFC000] text-[#0A2540] hover:bg-[#e6ad00]'
                                : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
                            ]"
                          >
                            {{ action.label }}
                          </button>
                          <button v-if="item.actions.length === 0" @click="router.get(`/booking/${item.id}`)" class="px-2.5 md:px-3 py-1 md:py-1.5 rounded-lg text-[9px] md:text-[10px] font-bold transition-all shadow-sm active:scale-95 bg-white border border-[#0A2540] text-[#0A2540] hover:bg-[#0A2540] hover:text-white">
                            Detail
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

          </section>
      </div>

      <!-- POP-UP PEMBATALAN -->
      <div v-if="isCancelModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity p-0 sm:p-4">
        <div class="bg-white w-full sm:w-[450px] max-h-[90vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl shadow-xl transform transition-transform animate-slide-up sm:animate-fade-in">
          <div class="p-5 md:p-6 border-b border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-500 flex items-center justify-center text-xl shrink-0">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="text-lg md:text-xl font-bold text-gray-900">Batalkan Penyewaan?</h2>
          </div>

          <div class="p-5 md:p-6 text-sm">
            <p class="text-gray-600 mb-4">Anda akan membatalkan penyewaan berikut:</p>

            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 flex items-center gap-3 mb-6">
              <template v-if="selectedAssetToCancel?.images && selectedAssetToCancel?.images.length > 0">
                <img :src="selectedAssetToCancel?.images[0]" class="w-14 h-14 rounded-lg object-cover border border-gray-200" alt="Asset" onerror="this.src='https://placehold.co/100x100?text=No+Image'" />
              </template>
              <template v-else>
                <div class="w-14 h-14 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                  <i class="fas fa-image text-lg"></i>
                </div>
              </template>
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-[#E87E04] truncate">{{ selectedAssetToCancel?.name }}</h4>
                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ selectedAssetToCancel?.dateString }}</p>
              </div>
            </div>

            <div class="mb-6">
              <h4 class="font-bold text-gray-800 mb-2">Konsekuensi Pembatalan:</h4>
              <ul class="space-y-2 text-gray-600 text-sm">
                <li class="flex items-start gap-2"><span class="text-red-500 mt-0.5">•</span> <span>Jadwal penyewaan akan dibatalkan permanen.</span></li>
                <li class="flex items-start gap-2"><span class="text-red-500 mt-0.5">•</span> <span>Refund diproses sesuai dengan kebijakan pemilik/KuSewa.</span></li>
                <li class="flex items-start gap-2"><span class="text-red-500 mt-0.5">•</span> <span>Aset akan kembali tersedia untuk disewa orang lain.</span></li>
              </ul>
            </div>

            <div class="mb-2">
              <label class="block font-bold text-gray-800 mb-2">Alasan Pembatalan <span class="text-red-500">*</span></label>
              <div class="relative">
                <select v-model="cancelReason" class="w-full appearance-none bg-white border border-gray-300 text-gray-700 py-3 px-4 pr-8 rounded-xl leading-tight focus:outline-none focus:border-[#0A2540] focus:ring-2 focus:ring-[#0A2540]/20 transition-all">
                  <option value="" disabled selected>Pilih alasan...</option>
                  <option value="salah_tanggal">Salah pilih tanggal/jadwal</option>
                  <option value="nemu_lain">Menemukan opsi penyewaan lain</option>
                  <option value="berubah_pikiran">Berubah pikiran / Batal butuh</option>
                  <option value="kendala_biaya">Kendala biaya / pembayaran</option>
                  <option value="lainnya">Lainnya</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                  <i class="fas fa-chevron-down text-xs"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="p-5 md:p-6 border-t border-gray-100 flex gap-3 flex-col-reverse sm:flex-row">
            <button @click="closeCancelModal" class="flex-1 py-3 px-4 bg-white border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-center">
              Kembali
            </button>
            <button @click="processCancellation" :disabled="!cancelReason" :class="['flex-1 py-3 px-4 font-bold rounded-xl transition-colors text-center shadow-sm', cancelReason ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-red-200 text-white cursor-not-allowed']">
              Batalkan Penyewaan
            </button>
          </div>
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

@keyframes slide-up {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
@keyframes fade-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
.animate-slide-up {
  animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@media (min-width: 640px) {
  .sm\:animate-fade-in {
    animation: fade-in 0.2s ease-out forwards;
  }
}
</style>
