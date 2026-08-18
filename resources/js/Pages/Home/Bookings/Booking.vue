<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue'
import DetailNavbar from '@/Components/UI/DetailNavbar.vue'
import { computed, watch, ref } from 'vue'
import { usePage, useForm } from '@inertiajs/vue3'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import { Indonesian } from "flatpickr/dist/l10n/id.js"

const props = defineProps({
    asset: Object,
    selectedPricing: Object,
    serviceFee: {
        type: Object,
        default: () => ({ type: 'percentage', value: 5 })
    },
    requestParams: Object,
    bankAccounts: {
        type: Array,
        default: () => []
    },
    bookedDates: {
        type: Array,
        default: () => []
    }
})

const page = usePage()
const user = page.props.auth?.user

// Tentukan mode sewa sebelum form diinisialisasi
const rentalMode = props.requestParams?.rental_mode || props.asset?.type?.rental_unit || 'day';

// Pesan error inline untuk booking conflict / error lainnya
const bookingError = ref('')

const form = useForm({
  asset_id: props.asset?.id || null,
  pricing_id: props.requestParams?.pricing_id || null,
  namaPemesan: user?.name || '',
  booker_name: user?.name || '',
  booker_phone: user?.phone || '',
  booker_email: user?.email || '',
  phone: user?.phone || '',
  email: user?.email || '',
  untukSaya: true,
  namaTamu: user?.name || '',
  guest_name: user?.name || '',
  // Untuk mode JAM: simpan full datetime ("2026-07-30 09:00:00") agar overlap check per-jam berjalan
  // Untuk mode lain: simpan date only ("2026-07-30")
  startDate: rentalMode === 'hour'
    ? (props.requestParams?.date_start || '')
    : (props.requestParams?.date_start?.split(' ')[0] || ''),
  endDate: rentalMode === 'hour'
    ? (props.requestParams?.date_end || '')
    : (props.requestParams?.date_end?.split(' ')[0] || ''),
  duration: props.requestParams?.duration ? Number(props.requestParams.duration) : 1,
  rental_mode: rentalMode,
  payment_method: props.bankAccounts?.length ? props.bankAccounts[0].id : null
})

// Option A: harga adalah harga paket (tidak dikalikan durasi)
const effectivePrice = computed(() => {
    return Number(props.selectedPricing?.price || props.asset?.default_pricing?.price || 0)
})

const subtotalPrice = computed(() => effectivePrice.value)

const calculatedServiceFee = computed(() => {
    if (props.serviceFee?.type === 'fixed') {
        return props.serviceFee.value;
    }
    const val = props.serviceFee?.value ?? 5;
    return subtotalPrice.value * (val / 100);
})

const totalPrice = computed(() => {
    return subtotalPrice.value + calculatedServiceFee.value;
})

const truncatedDescription = computed(() => {
    if (!props.asset?.description) return '';
    const stripped = props.asset.description.replace(/(<([^>]+)>)/gi, "");
    return stripped.length > 80 ? stripped.substring(0, 80) + '...' : stripped;
})

// Label paket pricing yang dipilih
const pricingPackageLabel = computed(() => {
    const p = props.selectedPricing;
    if (!p) return '';
    const units = { hour: 'Jam', day: 'Hari', night: 'Malam', week: 'Minggu', month: 'Bulan' };
    return `${p.duration} ${units[p.rental_unit] || p.rental_unit}`;
})

// Hitung estimasi end date dari start date dan pricing yang dipilih
const estimatedEndDate = computed(() => {
    if (!form.startDate || !props.selectedPricing) return null;
    const start = new Date(form.startDate);
    const { duration, rental_unit } = props.selectedPricing;
    if (rental_unit === 'hour') start.setHours(start.getHours() + duration);
    else if (rental_unit === 'day' || rental_unit === 'night') start.setDate(start.getDate() + duration);
    else if (rental_unit === 'week') start.setDate(start.getDate() + duration * 7);
    else if (rental_unit === 'month') start.setMonth(start.getMonth() + duration);
    return start;
})

const estimatedEndDateLabel = computed(() => {
    if (!estimatedEndDate.value) return '—';
    return estimatedEndDate.value.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
})

const flatpickrConfig = computed(() => {
    return {
        disable: props.bookedDates.map(date => {
            return {
                from: date.from,
                to: date.to
            }
        }),
        minDate: "today",
        locale: Indonesian,
        altInput: true,
        altFormat: "d M Y",
        dateFormat: "Y-m-d",
        disableMobile: "true",
    }
});

const hourDate = ref('');
const hourStartTime = ref('');
const hourEndTime = ref('');

if (rentalMode === 'hour') {
    if (form.startDate) {
        const [d, t] = form.startDate.split(' ');
        hourDate.value = d || '';
        hourStartTime.value = t ? t.substring(0, 5) : '';
    }
    if (form.endDate) {
        const [, t] = form.endDate.split(' ');
        hourEndTime.value = t ? t.substring(0, 5) : '';
    }
}

// Option A: endDate dihitung otomatis di backend dari pricing package
// Tidak perlu watcher durasi/endDate manual

const displayRentalUnit = computed(() => {
    const labels = { hour: 'jam', day: 'hari', night: 'malam', week: 'minggu', month: 'bulan' };
    return labels[form.rental_mode] || 'opsi';
})

const submitBooking = () => {
    // Reset error sebelum submit baru
    bookingError.value = ''

    // Sync snapshot fields before submitting
    form.booker_name = form.namaPemesan
    form.booker_phone = form.phone
    form.booker_email = form.email
    form.guest_name = form.untukSaya ? form.namaPemesan : form.namaTamu

    form.post(route('booking.store'), {
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            bookingError.value = ''
        },
        onError: (errors) => {
            console.error('Booking errors:', errors)
            if (errors.startDate) {
                bookingError.value = errors.startDate
            } else if (errors.endDate) {
                bookingError.value = errors.endDate
            } else {
                bookingError.value = 'Terjadi kesalahan. Silakan periksa kembali data Anda.'
            }
            // Scroll ke atas agar error terlihat
            window.scrollTo({ top: 0, behavior: 'smooth' })
        }
    });
}

// Jika untukSaya dicentang, guest_name selalu = namaPemesan
watch(() => form.untukSaya, (val) => {
    if (val) {
        form.namaTamu = form.namaPemesan
    }
})

// Jika namaPemesan berubah dan untukSaya dicentang, update namaTamu juga
watch(() => form.namaPemesan, (val) => {
    if (form.untukSaya) {
        form.namaTamu = val
    }
})

const showAllBanks = ref(false);
const displayedBanks = computed(() => {
    if (showAllBanks.value) return props.bankAccounts;
    return props.bankAccounts.slice(0, 3);
});

</script>

<template>
    <AppLayout :hideBottombar="true" :hideNavbar="true">
        <DetailNavbar
            :showSections="false"
            :showShare="false"
            :showFavorite="false"
            :backUrl="asset?.slug ? `/assets/${asset.slug}` : '/'"
        />
        <div class="min-h-screen bg-slate-50/60 text-slate-800 font-sans antialiased pb-28 lg:pb-16">
            <main class="max-w-6xl mx-auto px-4 sm:px-6 pt-6">

                <!-- INLINE ERROR BANNER -->
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="bookingError" class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500 shrink-0"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold">Pemesanan Gagal</p>
                            <p class="text-xs mt-0.5 text-red-700">{{ bookingError }}</p>
                        </div>
                        <button @click="bookingError = ''" class="text-red-400 hover:text-red-600 transition-colors ml-2">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </transition>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- MAIN CONTENT (KOLOM KIRI - 7 COLS) -->
                <div class="lg:col-span-7 space-y-6">

                <!-- Selected Asset Info -->
                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-start gap-4">
                    <div class="w-24 h-24 rounded-xl overflow-hidden shrink-0 bg-slate-100">
                        <img v-if="asset?.first_image?.image_url" :src="asset.first_image.image_url" class="w-full h-full object-cover" alt="Asset Image" />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ asset?.title }}</h2>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2" :title="asset?.description">{{ truncatedDescription }}</p>
                        <div class="mt-2 flex items-center gap-2 flex-wrap">
                            <span class="text-[#0A2540] font-black text-sm">Rp {{ Number(effectivePrice).toLocaleString('id-ID') }}</span>
                            <span class="text-xs font-bold bg-[#0A2540]/10 text-[#0A2540] px-2 py-0.5 rounded-full">Paket {{ pricingPackageLabel }}</span>
                        </div>
                    </div>
                </div>

                <!-- Google Auth Info Card -->
                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-xs text-slate-600">
                        {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Terhubung sebagai</p>
                        <p class="text-sm font-semibold text-slate-800">{{ user?.name || 'User' }}</p>
                    </div>
                    </div>
                </div>

                <!-- Section: Data Pemesan -->
                <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        Data Pemesan
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Pastikan data sesuai dengan kartu identitas resmi Anda.</p>
                    </div>

                    <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap*</label>
                        <input type="text" v-model="form.namaPemesan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#ffc000] focus:ring-4 focus:ring-[#ffc000]/20 transition-all text-sm outline-none bg-slate-50/50 focus:bg-white" />
                        <span class="text-[11px] text-slate-400 mt-1 block">Gunakan nama yang tertera di KTP/Paspor.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. Handphone*</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-200 rounded-l-xl text-xs font-semibold text-slate-600">+62</span>
                            <input type="text" v-model="form.phone" class="w-full px-3.5 py-2.5 rounded-r-xl border border-slate-200 focus:border-[#ffc000] focus:ring-4 focus:ring-[#ffc000]/20 transition-all text-sm outline-none bg-slate-50/50 focus:bg-white" />
                        </div>
                        </div>
                        <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email*</label>
                        <input type="email" v-model="form.email" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#ffc000] focus:ring-4 focus:ring-[#ffc000]/20 transition-all text-sm outline-none bg-slate-50/50 focus:bg-white" />
                        </div>
                    </div>

                    <label class="flex items-center gap-2.5 pt-2 cursor-pointer select-none">
                        <input type="checkbox" v-model="form.untukSaya" class="w-4 h-4 rounded text-[#ffc000] focus:ring-[#ffc000] border-slate-300" />
                        <span class="text-xs font-medium text-slate-700">Saya memesan untuk diri sendiri</span>
                    </label>
                    </div>
                </section>

                <!-- Section: Informasi Tamu -->
                <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
                    <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    Nama Tamu Menggunakan
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Orang yang akan menggunakan aset ini.</p>
                    </div>
                    <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap Tamu*</label>
                    <input
                        type="text"
                        v-model="form.namaTamu"
                        :disabled="form.untukSaya"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#ffc000] focus:ring-4 focus:ring-[#ffc000]/20 transition-all text-sm outline-none"
                        :class="form.untukSaya ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-slate-50/50 focus:bg-white'"
                    />
                    <span v-if="form.untukSaya" class="text-[11px] text-slate-400 mt-1 block flex items-center gap-1">
                        <i class="fa-solid fa-lock text-[9px]"></i>
                        Diisi otomatis sesuai data pemesan.
                    </span>
                    </div>
                </section>

                <!-- Section: Metode Pembayaran -->
                <section class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-slate-900">Metode Pembayaran</h2>
                    <div class="bg-white rounded-2xl border border-black/5 shadow-xs overflow-hidden divide-y divide-black/5">
                        <!-- TRANSFER BANK -->
                        <div>
                            <div class="w-full p-4 flex items-center justify-between bg-slate-50/80">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center border-[#ffc000] bg-[#ffc000]">
                                        <svg class="w-3 h-3 text-slate-950 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-900">Transfer Bank</span>
                                </div>
                            </div>

                            <!-- Sub-items Bank -->
                            <div class="px-4 pb-4 pt-1 space-y-2 bg-slate-50/50">
                                <div v-for="bank in displayedBanks" :key="bank.id"
                                     @click="form.payment_method = bank.id"
                                     :class="form.payment_method === bank.id ? 'bg-white ring-2 ring-[#ffc000] shadow-xs' : 'bg-white/60 hover:bg-white border border-black/5'"
                                     class="p-3 rounded-xl flex items-center justify-between cursor-pointer transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                             :class="form.payment_method === bank.id ? 'border-[#ffc000] bg-[#ffc000]' : 'border-slate-300'">
                                            <div v-if="form.payment_method === bank.id" class="w-1.5 h-1.5 rounded-full bg-slate-950"></div>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-semibold text-slate-800">{{ bank.bank_name }}</span>
                                            <span class="text-[10px] text-slate-500">{{ bank.account_holder }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-400 font-mono">{{ bank.account_number }}</span>
                                </div>
                                
                                <!-- Tombol Lihat Lebih Banyak -->
                                <button v-if="bankAccounts.length > 3" @click="showAllBanks = !showAllBanks" type="button" class="w-full mt-2 py-2 text-xs font-semibold text-[#0A2540] hover:bg-[#0A2540]/5 rounded-xl transition-colors border border-transparent hover:border-[#0A2540]/10 flex items-center justify-center gap-1">
                                    {{ showAllBanks ? 'Tampilkan Lebih Sedikit' : 'Lihat ' + (bankAccounts.length - 3) + ' Rekening Lainnya' }}
                                    <i class="fa-solid" :class="showAllBanks ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </button>
                                
                                <!-- Empty State -->
                                <div v-if="bankAccounts.length === 0" class="text-center text-xs text-slate-500 py-4">
                                    Belum ada metode pembayaran yang tersedia.
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                </div>

                <!-- SIDEBAR (KOLOM KANAN - 5 COLS) -->
                <div class="lg:col-span-5 space-y-6 sticky top-24">

                <!-- Order Summary Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xl shadow-slate-200/50 space-y-5">
                    <!-- Paket Pricing yang Dipilih -->
                    <div class="bg-[#0A2540]/5 border border-[#0A2540]/15 rounded-xl p-4 flex items-center justify-between mb-1">
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Paket Sewa Dipilih</p>
                            <p class="text-sm font-black text-[#0A2540]">{{ pricingPackageLabel }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-base font-black text-[#F97316]">Rp {{ Number(effectivePrice).toLocaleString('id-ID') }}</p>
                            <p class="text-[10px] text-slate-400">Harga paket</p>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <!-- Tanggal Mulai -->
                        <div class="p-3.5 cursor-pointer hover:bg-slate-50 transition-colors border-b border-slate-200">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Tanggal Mulai Sewa</label>
                            <flat-pickr v-model="form.startDate" :config="flatpickrConfig" class="w-full text-sm font-bold text-slate-800 bg-transparent border-none p-0 focus:ring-0 cursor-pointer outline-none placeholder:text-slate-400 placeholder:font-medium" placeholder="Pilih Tanggal Mulai"></flat-pickr>
                        </div>
                        <!-- Estimasi End Date (Read-only, auto dari pricing) -->
                        <div class="p-3.5 bg-slate-50/80 border-b border-slate-200">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Estimasi Selesai</label>
                            <div class="text-sm font-bold" :class="estimatedEndDate ? 'text-slate-800' : 'text-slate-400'">{{ estimatedEndDateLabel }}</div>
                            <p class="text-[10px] text-slate-400 mt-0.5">Dihitung otomatis dari paket</p>
                        </div>
                        <!-- Durasi (Read-only, dari pricing) -->
                        <div class="p-3.5 flex justify-between items-center bg-slate-50">
                            <span class="text-xs font-medium text-slate-500">Durasi Sewa</span>
                            <span class="text-sm font-bold text-[#0A2540]">{{ pricingPackageLabel }}</span>
                        </div>
                    </div>

                    <!-- BUG 7 FIX: Tampilkan pesan jika tidak ada pricing -->
                <div v-if="!effectivePrice" class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center text-amber-700 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Harga sewa belum tersedia. Hubungi pemilik.
                </div>

                <button @click="submitBooking" :disabled="form.processing || !form.payment_method || !effectivePrice" class="w-full bg-[#FFC000] hover:bg-[#e6ad00] active:scale-[0.98] text-[#0A2540] font-extrabold py-3.5 rounded-xl transition-all text-sm mt-4 disabled:opacity-50 flex items-center justify-center gap-2">
                    <template v-if="form.processing">
                        <svg class="animate-spin h-4 w-4 text-[#0A2540]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses...
                    </template>
                    <template v-else>Pesan Sekarang</template>
                </button>

                    <!-- BUG 9 FIX: Gunakan effectivePrice (selectedPricing) bukan default_pricing -->
                    <div class="space-y-3 text-sm pt-5 mt-4 border-t border-slate-100">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-bold text-[#0A2540]">Rp {{ subtotalPrice.toLocaleString('id-ID') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Biaya Layanan <template v-if="serviceFee.type === 'percentage'">({{ serviceFee.value }}%)</template></span>
                            <span class="font-bold text-[#0A2540]">Rp {{ calculatedServiceFee.toLocaleString('id-ID') }}</span>
                        </div>

                        <div class="border-t border-slate-100 pt-3 flex justify-between items-baseline mt-3">
                            <span class="font-black text-[#0A2540] text-lg">Total</span>
                            <span class="text-xl font-black text-[#0A2540]">
                                Rp {{ totalPrice.toLocaleString('id-ID') }}
                            </span>
                        </div>
                    </div>
                </div>

                </div>

            </div>
            </main>

            <DetailBottomBar
                :price="totalPrice"
                :durationCount="selectedPricing?.duration || 1"
                :durationLabel="pricingPackageLabel"
                :disabled="form.processing || !form.payment_method || !effectivePrice"
                buttonText="Pesan Sekarang"
                @submit="submitBooking"
            />

        </div>
    </AppLayout>
</template>
