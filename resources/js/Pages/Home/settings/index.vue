<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    isComponent: { type: Boolean, default: false }
});

// ─── localStorage keys ────────────────────────────────────────────────────────
const APP_NOTIF_KEY = 'app_notifications_enabled';
const APP_LOC_KEY   = 'app_location_enabled';

// ─── State ────────────────────────────────────────────────────────────────────
const notifBrowserStatus  = ref('default'); // 'granted' | 'denied' | 'default' | 'unsupported'
const locBrowserStatus    = ref('prompt');  // 'granted' | 'denied' | 'prompt'  | 'unsupported'

const isNotifActive       = ref(false);
const isLocActive         = ref(false);
const isLocRequesting     = ref(false);

const notifMsg = ref({ text: '', type: '' }); // type: 'success' | 'error' | 'info'
const locMsg   = ref({ text: '', type: '' });

// ─── Init ─────────────────────────────────────────────────────────────────────
onMounted(() => {
    // ── Notifications ──
    if (!('Notification' in window)) {
        notifBrowserStatus.value = 'unsupported';
    } else {
        notifBrowserStatus.value = Notification.permission;
        const savedPref = localStorage.getItem(APP_NOTIF_KEY);
        // Active only if browser granted AND user hasn't explicitly turned it off
        isNotifActive.value = Notification.permission === 'granted' && savedPref !== 'false';
    }

    // ── Geolocation ──
    if (!navigator.geolocation) {
        locBrowserStatus.value = 'unsupported';
    } else if (navigator.permissions) {
        navigator.permissions.query({ name: 'geolocation' }).then((result) => {
            locBrowserStatus.value = result.state;
            const savedPref = localStorage.getItem(APP_LOC_KEY);
            isLocActive.value = result.state === 'granted' && savedPref !== 'false';
            // Auto-sync when user changes via browser settings
            result.onchange = () => {
                locBrowserStatus.value = result.state;
                if (result.state !== 'granted') {
                    isLocActive.value = false;
                    localStorage.setItem(APP_LOC_KEY, 'false');
                }
                locMsg.value = { text: '', type: '' };
            };
        });
    }
});

// ─── Notifications Handler ────────────────────────────────────────────────────
const handleNotifToggle = () => {
    notifMsg.value = { text: '', type: '' };

    if (!('Notification' in window)) {
        isNotifActive.value = false;
        notifMsg.value = { text: 'Browser Anda tidak mendukung notifikasi.', type: 'error' };
        return;
    }

    if (isNotifActive.value) {
        // ── User wants to ENABLE ──
        if (notifBrowserStatus.value === 'denied') {
            // Browser blocked it permanently — can't request again
            isNotifActive.value = false;
            notifMsg.value = {
                text: 'Notifikasi diblokir oleh browser. Klik ikon 🔒 di bilah URL → Izin Situs → Notifikasi → Izinkan, lalu muat ulang halaman.',
                type: 'error'
            };
        } else if (notifBrowserStatus.value === 'granted') {
            // Browser already granted — just flip the app-level flag
            localStorage.setItem(APP_NOTIF_KEY, 'true');
            notifMsg.value = { text: '✓ Notifikasi diaktifkan!', type: 'success' };
        } else {
            // Status = 'default' → trigger browser dialog (like the popup in the screenshot)
            Notification.requestPermission().then((permission) => {
                notifBrowserStatus.value = permission;
                if (permission === 'granted') {
                    isNotifActive.value = true;
                    localStorage.setItem(APP_NOTIF_KEY, 'true');
                    notifMsg.value = { text: '✓ Notifikasi berhasil diaktifkan!', type: 'success' };
                } else {
                    isNotifActive.value = false;
                    localStorage.setItem(APP_NOTIF_KEY, 'false');
                    notifMsg.value = {
                        text: permission === 'denied'
                            ? 'Izin ditolak. Ubah melalui pengaturan browser Anda.'
                            : 'Permintaan dibatalkan.',
                        type: 'error'
                    };
                }
            });
        }
    } else {
        // ── User wants to DISABLE (app-level only — works instantly!) ──
        localStorage.setItem(APP_NOTIF_KEY, 'false');
        notifMsg.value = { text: 'Notifikasi dinonaktifkan untuk aplikasi ini.', type: 'info' };
    }
};

// ─── Geolocation Handler ──────────────────────────────────────────────────────
const handleLocToggle = () => {
    locMsg.value = { text: '', type: '' };

    if (!navigator.geolocation) {
        isLocActive.value = false;
        locMsg.value = { text: 'Browser Anda tidak mendukung fitur lokasi.', type: 'error' };
        return;
    }

    if (isLocActive.value) {
        // ── User wants to ENABLE ──
        if (locBrowserStatus.value === 'denied') {
            isLocActive.value = false;
            locMsg.value = {
                text: 'Lokasi diblokir oleh browser. Klik ikon 🔒 di bilah URL → Izin Situs → Lokasi → Izinkan, lalu muat ulang halaman.',
                type: 'error'
            };
        } else if (locBrowserStatus.value === 'granted') {
            // Browser already granted — just flip the app-level flag
            localStorage.setItem(APP_LOC_KEY, 'true');
            localStorage.removeItem('location_denied');
            locMsg.value = { text: '✓ Akses lokasi diaktifkan!', type: 'success' };
        } else {
            // Status = 'prompt' → trigger browser dialog
            isLocRequesting.value = true;
            navigator.geolocation.getCurrentPosition(
                () => {
                    locBrowserStatus.value = 'granted';
                    isLocActive.value = true;
                    isLocRequesting.value = false;
                    localStorage.setItem(APP_LOC_KEY, 'true');
                    localStorage.removeItem('location_denied');
                    locMsg.value = { text: '✓ Akses lokasi berhasil diaktifkan!', type: 'success' };
                },
                (error) => {
                    isLocRequesting.value = false;
                    isLocActive.value = false;
                    if (error.code === error.PERMISSION_DENIED) {
                        locBrowserStatus.value = 'denied';
                        localStorage.setItem(APP_LOC_KEY, 'false');
                        localStorage.setItem('location_denied', 'true');
                        locMsg.value = {
                            text: 'Izin lokasi ditolak. Ubah melalui pengaturan browser Anda.',
                            type: 'error'
                        };
                    } else {
                        // TIMEOUT / UNAVAILABLE — permission might be granted anyway
                        locBrowserStatus.value = 'granted';
                        isLocActive.value = true;
                        localStorage.setItem(APP_LOC_KEY, 'true');
                        locMsg.value = {
                            text: 'Izin diberikan, namun sinyal GPS tidak tersedia saat ini.',
                            type: 'info'
                        };
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    } else {
        // ── User wants to DISABLE (app-level only — works instantly!) ──
        localStorage.setItem(APP_LOC_KEY, 'false');
        localStorage.setItem('location_denied', 'true');
        locMsg.value = { text: 'Akses lokasi dinonaktifkan untuk aplikasi ini.', type: 'info' };
    }
};
</script>

<template>
    <component :is="isComponent ? 'div' : AppLayout" :hideNavbar="!isComponent" class="w-full">
        <Head title="Pengaturan" />

        <div :class="isComponent ? '' : 'bg-[#F8F9FA] min-h-screen pb-24 sm:pb-16'">
            <!-- Custom Top Navbar untuk Mobile -->
            <DetailNavbar 
                v-if="!isComponent"
                title="Pengaturan" 
                backUrl="/profile" 
                :forceBackUrl="true" 
                :showBackButton="true" 
                :showSections="false" 
                :showShare="false" 
                :showFavorite="false" 
                class="md:hidden"
            />

            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 text-[#1D1D1F] min-h-[70vh]">
                <!-- CARD WRAPPER -->
                <div class="bg-white p-6 shadow-md rounded-2xl">

                    <!-- HEADER (desktop panel only) -->
                    <template v-if="isComponent">
                        <div class="flex justify-between items-center mb-2">
                            <h2 class="text-xl font-bold text-[#1D1D1F]">Pengaturan</h2>
                        </div>
                        <div class="border-t border-[#F8F9FA] mb-5"></div>
                    </template>
                    
                    <p class="text-sm text-slate-500 mb-5">
                        Dengan mengaktifkan izin ini, platform dapat memberikan respons yang lebih lancar dan fitur yang maksimal. Data hanya diproses untuk pengalaman Anda di platform KitaSewa.
                    </p>

                    <!-- LIST OF SETTINGS -->
                    <div class="rounded-xl border border-slate-100 flex flex-col divide-y divide-slate-100 overflow-hidden">
                        
                        <!-- ── Notifikasi Item ───────────────────────────── -->
                        <div class="bg-white">
                            <div class="p-5 flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-bold text-[#0A2540] text-[15px]">Notifikasi Aplikasi</h3>
                                        <!-- Status badge -->
                                        <span v-if="isNotifActive"
                                              class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200">
                                            Aktif
                                        </span>
                                        <span v-else-if="notifBrowserStatus === 'denied'"
                                              class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-500 border border-red-200">
                                            Diblokir
                                        </span>
                                        <span v-else-if="notifBrowserStatus === 'unsupported'"
                                              class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                            Tidak Didukung
                                        </span>
                                    </div>
                                    <p class="text-[13px] text-slate-500 leading-relaxed">
                                        Dapatkan pemberitahuan langsung saat ada pesanan baru, pesan, atau pembaruan penting.
                                    </p>
                                    <!-- Inline message -->
                                    <p v-if="notifMsg.text" class="mt-2 text-[12px] leading-relaxed"
                                       :class="{
                                           'text-emerald-600': notifMsg.type === 'success',
                                           'text-red-500': notifMsg.type === 'error',
                                           'text-amber-600': notifMsg.type === 'info',
                                       }">
                                        {{ notifMsg.text }}
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5"
                                       :class="notifBrowserStatus === 'unsupported' ? 'opacity-40 pointer-events-none' : ''">
                                    <input type="checkbox" class="sr-only peer"
                                           v-model="isNotifActive"
                                           @change="handleNotifToggle"
                                           :disabled="notifBrowserStatus === 'unsupported'">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer
                                                peer-checked:bg-[#FFC000]
                                                peer-checked:after:translate-x-full
                                                peer-checked:after:border-white
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                                after:bg-white after:border-gray-300 after:border
                                                after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- ── Lokasi Item ────────────────────────────────── -->
                        <div class="bg-white">
                            <div class="p-5 flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-bold text-[#0A2540] text-[15px]">Izin Akses Lokasi</h3>
                                        <!-- Status badge -->
                                        <span v-if="isLocActive"
                                              class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200">
                                            Aktif
                                        </span>
                                        <span v-else-if="locBrowserStatus === 'denied'"
                                              class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-500 border border-red-200">
                                            Diblokir
                                        </span>
                                        <span v-else-if="locBrowserStatus === 'unsupported'"
                                              class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                            Tidak Didukung
                                        </span>
                                    </div>
                                    <p class="text-[13px] text-slate-500 leading-relaxed">
                                        Izinkan akses lokasi untuk melihat rekomendasi pencarian yang lebih relevan di area sekitar.
                                    </p>
                                    <!-- Inline message -->
                                    <p v-if="locMsg.text" class="mt-2 text-[12px] leading-relaxed"
                                       :class="{
                                           'text-emerald-600': locMsg.type === 'success',
                                           'text-red-500': locMsg.type === 'error',
                                           'text-amber-600': locMsg.type === 'info',
                                       }">
                                        {{ locMsg.text }}
                                    </p>
                                    <!-- Loading indicator -->
                                    <p v-if="isLocRequesting" class="mt-2 text-[12px] text-slate-400 animate-pulse">
                                        Meminta izin lokasi...
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5"
                                       :class="(isLocRequesting || locBrowserStatus === 'unsupported') ? 'opacity-40 pointer-events-none' : ''">
                                    <input type="checkbox" class="sr-only peer"
                                           v-model="isLocActive"
                                           @change="handleLocToggle"
                                           :disabled="isLocRequesting || locBrowserStatus === 'unsupported'">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer
                                                peer-checked:bg-[#FFC000]
                                                peer-checked:after:translate-x-full
                                                peer-checked:after:border-white
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                                after:bg-white after:border-gray-300 after:border
                                                after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
