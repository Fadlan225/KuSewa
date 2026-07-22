<template>
  <div class="min-h-screen bg-gray-50 font-sans text-[#0A2540] pb-20 md:pb-10 relative">
    
    <!-- Top Header -->
    <header class="h-14 md:h-16 bg-white shadow-sm border-b px-4 md:px-8 flex items-center sticky top-0 z-20">
      <h1 class="text-lg md:text-xl font-bold">Aktivitas Saya</h1>
    </header>

    <main class="max-w-4xl mx-auto px-4 md:px-0 pt-4 md:pt-6">
      
      <!-- Filter Pills -->
      <section class="mb-4 md:mb-6">
        <div class="flex gap-2 md:gap-3 overflow-x-auto pb-2 scrollbar-hide -mx-4 px-4 md:mx-0 md:px-0">
          <button 
            v-for="tab in filterTabs" 
            :key="tab.name"
            @click="activeFilter = tab.name"
            :class="[
              'flex items-center gap-1.5 px-3.5 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-semibold whitespace-nowrap transition-colors border shadow-sm',
              activeFilter === tab.name 
                ? 'bg-primary text-white border-primary hover:bg-primary/90'     
                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
            ]"
          >
            {{ tab.name }}
            <span 
              v-if="tab.count > 0"
              :class="[
                'flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold',
                activeFilter === tab.name 
                  ? 'bg-white text-[#0A2540]' 
                  : 'bg-gray-200 text-gray-600'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>
      </section>

      <!-- Timeline Cards List -->
      <section class="flex flex-col gap-4 md:gap-5">
        
        <div v-if="filteredActivities.length === 0" class="bg-white rounded-2xl p-8 md:p-10 text-center shadow-sm border border-gray-100 flex flex-col items-center justify-center">
          <img src="empty.svg" alt="No Activity" class="w-24 md:w-32 mb-4 md:mb-5" />
          <h3 class="font-bold text-base md:text-lg mb-1.5 md:mb-2">Belum ada aktivitas</h3>
          <p class="text-gray-500 text-xs md:text-sm mb-5 md:mb-6">Mulai cari aset untuk disewa dan pantau di sini.</p>
          <button class="bg-[#FFC000] text-[#0A2540] font-bold px-6 py-2.5 rounded-xl hover:bg-yellow-500 transition-colors">
            Cari Aset
          </button>
        </div>

        <template v-else>
          <div 
            v-for="item in filteredActivities" 
            :key="item.id"
            class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative flex flex-col md:flex-row gap-4 md:gap-6 overflow-hidden"
          >
            <!-- BAGIAN KIRI: Galeri Mini atau Placeholder FontAwesome jika Gambar Kosong -->
            <div class="flex gap-2 shrink-0">
              
              <!-- Jika Gambar Tersedia -->
              <template v-if="item.images && item.images.length > 0">
                <div class="relative w-28 h-28 md:w-36 md:h-36 shrink-0">
                  <img :src="item.images[0]" class="w-full h-full object-cover rounded-xl shadow-sm" alt="Main Image" />
                  <button v-if="item.images.length > 1" class="absolute left-1 top-1/2 -translate-y-1/2 w-6 h-6 bg-black/50 text-white rounded-full flex items-center justify-center text-xs backdrop-blur-sm">
                    <i class="fas fa-chevron-left"></i>
                  </button>
                </div>
                <div class="hidden sm:flex flex-col gap-2 shrink-0" v-if="item.images.length > 1">
                  <img :src="item.images[1]" class="w-[70px] h-[68px] object-cover rounded-xl shadow-sm" alt="Thumb 1" />
                  <img :src="item.images[2]" class="w-[70px] h-[68px] object-cover rounded-xl shadow-sm" alt="Thumb 2" />
                </div>
              </template>

              <!-- Jika Gambar Kosong (Placeholder FontAwesome) -->
              <template v-else>
                <div class="w-28 h-28 md:w-36 md:h-36 shrink-0 bg-gray-100 border border-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-400">
                  <i class="fas fa-image text-2xl md:text-3xl mb-1"></i>
                  <span class="text-[10px] font-medium">No Image</span>
                </div>
              </template>

            </div>

            <!-- BAGIAN TENGAH: Info & Timeline -->
            <div class="flex-1 min-w-0 flex flex-col py-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="bg-[#FFF4E5] text-[#D97706] font-bold px-2 py-1 rounded-md text-[10px] uppercase tracking-wide">
                  {{ item.type }}
                </span>
                <span class="text-gray-500 text-xs flex items-center gap-1.5">
                  <i :class="item.statusIcon" :style="{ color: item.statusColor }"></i> {{ item.status }}
                </span>
              </div>
              
              <h3 class="font-bold text-lg md:text-xl text-[#E87E04] truncate mb-1">{{ item.name }}</h3>
              
              <p class="text-sm text-gray-500 flex items-center gap-1.5 mb-1">
                <i class="fas fa-map-marker-alt text-[#0A2540]/60"></i> {{ item.location }}
              </p>
              <p class="text-xs font-medium text-gray-700">{{ item.date }}</p>

              <!-- Progress Timeline Horizontal -->
              <div v-if="item.progress" class="mt-4 mb-2 pr-4">
                <div class="flex items-center justify-between relative max-w-sm">
                  <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-gray-100 rounded-full"></div>
                  <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-[#FFC000] transition-all rounded-full" :style="{ width: item.progressPercent + '%' }"></div>
                  
                  <div v-for="(step, index) in item.progressSteps" :key="index" class="flex flex-col items-center z-10 relative">
                    <div 
                      :class="[
                        'w-3 h-3 md:w-4 md:h-4 rounded-full flex items-center justify-center text-[7px] md:text-[8px] font-bold border-2 transition-colors',
                        index <= item.currentStepIndex 
                          ? 'bg-[#FFC000] border-[#FFC000] text-[#0A2540]' 
                          : 'bg-white border-gray-300'
                      ]"
                    >
                      <span v-if="index < item.currentStepIndex"><i class="fas fa-check text-[6px]"></i></span>
                    </div>
                    <span 
                      :class="[
                        'absolute top-4 md:top-5 text-[8px] md:text-[9px] text-center leading-tight w-12 font-medium',
                        index <= item.currentStepIndex ? 'text-gray-800' : 'text-gray-400'
                      ]"
                    >
                      {{ step }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- BAGIAN KANAN: Harga & Aksi -->
            <div class="md:w-[200px] md:border-l border-gray-100 md:pl-5 flex flex-row md:flex-col items-center md:items-start justify-between md:justify-center shrink-0 border-t md:border-t-0 pt-4 md:pt-0 mt-2 md:mt-0">
              
              <div class="mb-0 md:mb-4">
                <p class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Total Biaya</p>
                <p class="font-bold text-lg md:text-2xl text-gray-900 leading-none">{{ item.totalPrice }}</p>
                <p v-if="item.originalPrice" class="text-[10px] text-gray-400 line-through mt-1">{{ item.originalPrice }}</p>
              </div>
              
              <div class="flex flex-col gap-2 w-1/2 md:w-full items-end md:items-start">
                <button 
                  v-for="(action, idx) in item.actions" 
                  :key="idx"
                  @click="handleActionClick(action, item)"
                  :class="[
                    'w-full transition-colors text-center flex justify-center items-center gap-1.5',
                    action.primary 
                      ? 'bg-[#FFC000] text-[#0A2540] hover:bg-yellow-500 font-bold py-2 md:py-2.5 rounded-full text-xs md:text-sm shadow-sm' 
                      : 'bg-transparent text-gray-500 hover:text-gray-700 font-medium py-1 md:py-2 text-[11px] md:text-xs'
                  ]"
                >
                  <i v-if="!action.primary && action.actionId === 'cancel'" class="fas fa-trash-alt text-xs"></i>
                  {{ action.label }}
                </button>
              </div>
            </div>

          </div>
        </template>
      </section>
    </main>

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
              <img :src="selectedAssetToCancel?.images[0]" class="w-14 h-14 rounded-lg object-cover border border-gray-200" alt="Asset" />
            </template>
            <template v-else>
              <div class="w-14 h-14 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                <i class="fas fa-image text-lg"></i>
              </div>
            </template>
            <div class="flex-1 min-w-0">
              <h4 class="font-bold text-[#E87E04] truncate">{{ selectedAssetToCancel?.name }}</h4>
              <p class="text-xs text-gray-500 mt-0.5 truncate">{{ selectedAssetToCancel?.date }}</p>
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
              <select v-model="cancelReason" class="w-full appearance-none bg-white border border-gray-300 text-gray-700 py-3 px-4 pr-8 rounded-xl leading-tight focus:outline-none focus:border-[#FFC000] focus:ring-2 focus:ring-[#FFC000]/20 transition-all">
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
</template>

<script setup>
import { ref, computed } from 'vue';

const activeFilter = ref('Semua');

const activities = ref([
  {
    id: 1, 
    type: 'Properti',
    name: 'Villa Bukit Indah', 
    location: 'Banjarbaru',
    images: [
      'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=300&q=80',
      'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=150&q=80',
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=150&q=80'
    ], 
    totalPrice: 'Rp 2.500.000',
    originalPrice: 'Rp 3.000.000',
    category: 'Berlangsung', 
    status: 'Sedang Berlangsung', 
    statusIcon: 'fas fa-circle-dot',
    statusColor: '#10B981',
    date: 'Hari ini, 13:00 - 24 Juli 2026', 
    progress: true, 
    progressSteps: ['Booking', 'Dikonfirmasi', 'Bayar', 'Check In', 'Selesai'], 
    currentStepIndex: 3, 
    progressPercent: 75, 
    actions: [
      { label: 'Chat Pemilik', primary: false }, 
      { label: 'Perpanjang', primary: true }
    ]
  },
  {
    id: 3, 
    type: 'Elektronik',
    name: 'Proyektor Epson EB-X51', 
    location: 'Banjarmasin Selatan',
    images: [], // Contoh jika data gambar kosong/tidak ada
    totalPrice: 'Rp 150.000',
    category: 'Menunggu', 
    status: 'Menunggu Pembayaran', 
    statusIcon: 'fas fa-clock',
    statusColor: '#F59E0B',
    date: '25 Juli 2026 (Batas bayar: 23:59)', 
    progress: true, 
    progressSteps: ['Booking', 'Dikonfirmasi', 'Bayar', 'Digunakan', 'Selesai'], 
    currentStepIndex: 1, 
    progressPercent: 25, 
    actions: [
      { label: 'Bayar Sekarang', primary: true, actionId: 'pay' },
      { label: 'Batalkan', primary: false, actionId: 'cancel' }
    ]
  }
]);

const filterTabs = computed(() => {
  return [
    { name: 'Semua', count: activities.value.length },
    { name: 'Berlangsung', count: activities.value.filter(a => a.category === 'Berlangsung').length },
    { name: 'Menunggu', count: activities.value.filter(a => a.category === 'Menunggu').length },
    { name: 'Selesai', count: activities.value.filter(a => a.category === 'Selesai').length },
    { name: 'Dibatalkan', count: activities.value.filter(a => a.category === 'Dibatalkan').length }
  ];
});

const filteredActivities = computed(() => {
  if (activeFilter.value === 'Semua') return activities.value;
  return activities.value.filter(item => item.category === activeFilter.value);
});

const isCancelModalOpen = ref(false);
const selectedAssetToCancel = ref(null);
const cancelReason = ref('');

const handleActionClick = (action, item) => {
  if (action.actionId === 'cancel' || action.label === 'Batalkan') {
    openCancelModal(item);
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
  alert('Penyewaan berhasil dibatalkan');
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

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