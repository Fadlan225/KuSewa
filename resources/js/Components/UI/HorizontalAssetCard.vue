<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AssetCardSkeleton from './AssetCardSkeleton.vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    asset: { type: Object, required: true },
    categoryName: { type: String, required: true },
    isOwner: { type: Boolean, default: false }
});

defineEmits(['delete']);

// ── Intersection Observer (Lazy Load — Mobile Only) ───────────────────────
// Desktop (≥1024px): langsung render semua kartu, skip observer (hemat CPU).
// Mobile/tablet: pakai observer agar gambar load bertahap saat scroll.
const isDesktop = typeof window !== 'undefined' && window.innerWidth >= 1024;
const isIntersecting = ref(isDesktop); // Desktop → true (render langsung)
const imageLoaded = ref(false);
const elRef = ref(null);
let observer = null;

// thumbnailImages dari backend (max 3), fallback ke images lama atau first_image
const imgList = computed(() => props.asset.thumbnail_images || props.asset.images || []);
const img1 = computed(() => imgList.value[0]?.image_url || props.asset.first_image?.image_url || props.asset.image || props.asset.thumbnail);
const img2 = computed(() => imgList.value[1]?.image_url);
const img3 = computed(() => imgList.value[2]?.image_url);

const imageCount = computed(() => {
    if (img3.value) return 3;
    if (img2.value) return 2;
    if (img1.value) return 1;
    return 0;
});

onMounted(() => {
    if (isDesktop) return; // Skip observer di desktop
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

// ── Optimistic UI Favorite ─────────────────────────────────────────────
const isFavorite = ref(props.asset.isFavorite ?? false);
const favoriteId = ref(props.asset.favorite_id ?? null);
const isPending  = ref(false);

const page = usePage();

function getXsrfToken() {
    const match = document.cookie.match(new RegExp('(^|;\\s*)XSRF-TOKEN=([^;]*)'));
    return match ? decodeURIComponent(match[2]) : '';
}

async function syncFavorite(newState) {
    const user = page.props.auth?.user;
    if (!user) {
        isFavorite.value = !newState;
        router.visit(route('login'));
        return;
    }

    try {
        if (newState) {
            // POST – tambah favorit
            const res = await fetch(route('favorites.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': getXsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ asset_id: props.asset.id }),
            });
            if (!res.ok) throw new Error('store failed');
            const data = await res.json();
            favoriteId.value = data.favorite_id;
        } else {
            // DELETE – hapus favorit
            if (!favoriteId.value) return;  // sudah tidak ada, skip
            const res = await fetch(route('favorites.destroy', favoriteId.value), {
                method: 'DELETE',
                headers: {
                    'X-XSRF-TOKEN': getXsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            // 404 = sudah dihapus sebelumnya → anggap sukses, jangan rollback
            if (!res.ok && res.status !== 404) throw new Error('destroy failed');
            favoriteId.value = null;
        }
    } catch {
        // Rollback optimistic update
        isFavorite.value = !newState;
        favoriteId.value = props.asset.favorite_id ?? null;
    } finally {
        isPending.value = false;
    }
}

// Tombol ❤️ – toggle sekali klik/tap
const toggleFavorite = () => {
    if (isPending.value) return;
    const next = !isFavorite.value;
    isFavorite.value = next;    // ← INSTANT (optimistic)
    isPending.value  = true;
    syncFavorite(next);          // ← kirim ke server di background
};

const navigateToAsset = () => {
    if (props.isOwner) {
        router.visit(route('owner.asset.show', props.asset.id));
    } else {
        router.visit(route('assets.show', props.asset.slug));
    }
};

let lastTapTime = 0;
let tapTimeout = null;
let touchStartX = 0;
let touchStartY = 0;
const hearts = ref([]);
let heartIdCounter = 0;

// Mobile: Rekam posisi awal untuk bedakan swipe
const onTouchStart = (e) => {
    touchStartX = e.touches[0].clientX;
    touchStartY = e.touches[0].clientY;
};

// Mobile: Deteksi double tap di touchend
const onTouchEnd = (e) => {
    const t = e.changedTouches[0];
    const dx = Math.abs(t.clientX - touchStartX);
    const dy = Math.abs(t.clientY - touchStartY);

    // Abaikan jika user scroll/swipe
    if (dx > 10 || dy > 10) return;

    // Cegah browser memicu event click bawaan (yang memicu navigasi ganda)
    if (e.cancelable) e.preventDefault();

    const now = Date.now();
    const elapsed = now - lastTapTime;
    const rect = e.currentTarget.getBoundingClientRect();

    if (elapsed > 0 && elapsed < 350) {
        // Tap beruntun (Double, triple, spam)
        clearTimeout(tapTimeout);

        spawnHeart(t.clientX - rect.left, t.clientY - rect.top);

        if (!isFavorite.value && !isPending.value) {
            isFavorite.value = true;
            isPending.value = true;
            syncFavorite(true);
        }

        lastTapTime = now;
    } else {
        // Tap pertama
        lastTapTime = now;
        tapTimeout = setTimeout(() => {
            navigateToAsset();
        }, 300); // 300ms delay sebelum navigasi
    }
};

// Desktop: Mouse click fallback (hanya dieksekusi jika e.preventDefault() di touchend tidak terjadi)
const onMouseClick = (e) => {
    const now = Date.now();
    const elapsed = now - lastTapTime;
    const rect = e.currentTarget.getBoundingClientRect();

    if (elapsed > 0 && elapsed < 350) {
        // Tap beruntun (Double, triple, spam)
        clearTimeout(tapTimeout);

        spawnHeart(e.clientX - rect.left, e.clientY - rect.top);

        if (!isFavorite.value && !isPending.value) {
            isFavorite.value = true;
            isPending.value = true;
            syncFavorite(true);
        }

        lastTapTime = now;
    } else {
        // Tap pertama
        lastTapTime = now;
        tapTimeout = setTimeout(() => {
            navigateToAsset();
        }, 300);
    }
};

// Animasi hati melayang (bisa di-spam)
const spawnHeart = (x, y) => {
    const id    = ++heartIdCounter;
    const size  = 28 + Math.random() * 24;
    const angle = (Math.random() - 0.5) * 40;
    const drift = (Math.random() - 0.5) * 60;
    hearts.value.push({ id, x, y, size, angle, drift });
    setTimeout(() => {
        hearts.value = hearts.value.filter(h => h.id !== id);
    }, 900);
};

// ── Format helpers ─────────────────────────────────────────────────────
const formatRupiah = (value) => {
    if (!value) return '';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(value);
};

const periodLabel = {
    hour: 'jam', day: 'hari',
    week: 'minggu', month: 'bulan', year: 'tahun'
};

const rentalUnitLabel = (unit) => {
    const labels = {
        hour: "jam",
        day: "hari",
        night: "malam",
        month: "bulan",
    };

    return labels[unit] ?? "sewa";
};

const availabilityText = computed(() => {
    // If backend provides specific date/text
    if (props.asset.available_at) {
        return `Tersedia ${props.asset.available_at}`;
    }
    // Default fallback
    return "Tersedia Sekarang";
});
</script>

<template>
    <div
        ref="elRef"
        class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-row overflow-hidden group p-2.5 md:p-3 items-center gap-3 md:gap-4 select-none [-webkit-touch-callout:none] w-full cursor-pointer"
        @click="navigateToAsset"
    >
        <!-- Skeleton sebelum masuk viewport -->
        <div v-if="!isIntersecting" class="flex flex-row w-full animate-pulse items-center gap-3 md:gap-4">
            <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-200 rounded-lg shrink-0"></div>
            <div class="flex-1 space-y-2">
                <div class="w-16 h-3 bg-slate-200 rounded"></div>
                <div class="w-3/4 h-4 bg-slate-200 rounded"></div>
                <div class="w-1/2 h-3 bg-slate-200 rounded"></div>
            </div>
            <div class="shrink-0 flex flex-col items-end gap-2">
                <div class="w-16 h-4 bg-slate-200 rounded"></div>
                <div class="w-6 h-6 bg-slate-200 rounded-full mt-2"></div>
            </div>
        </div>

        <template v-else>
            <!-- ═══ AREA GAMBAR ═══ -->
            <div
                class="w-16 h-16 md:w-20 md:h-20 shrink-0 relative rounded-lg overflow-hidden bg-slate-100"
                @touchstart.passive.stop="onTouchStart"
                @touchend.stop="onTouchEnd"
            >
                <div v-if="!img1 || asset.imageError" class="absolute inset-0 flex items-center justify-center bg-slate-100 text-slate-300">
                    <i class="fa-solid fa-image text-xl"></i>
                </div>
                <img v-else :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 pointer-events-none" loading="lazy" />

                <!-- Animasi Hati TikTok -->
                <TransitionGroup name="heart-fly" tag="div" class="absolute inset-0 pointer-events-none z-30">
                    <div v-for="heart in hearts" :key="heart.id" class="heart-particle absolute" :style="{ left: heart.x + 'px', top: heart.y + 'px', '--drift': heart.drift + 'px', '--angle': heart.angle + 'deg', fontSize: heart.size + 'px' }"><i class="fa-solid fa-heart text-red-500"></i></div>
                </TransitionGroup>
            </div>

            <!-- ═══ AREA TEKS ═══ -->
            <div class="flex-1 min-w-0 flex flex-col justify-center">
                <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                    <span class="px-1.5 py-0.5 bg-[#6C757D]/10 text-[#6C757D] rounded text-[9px] font-bold">{{ categoryName }}</span>
                    
                    <!-- STATUS BADGE (Owner Only) -->
                    <template v-if="isOwner">
                        <span v-if="asset.verification_status === 'pending'" class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[9px] font-bold flex items-center gap-1"><i class="fa-solid fa-clock"></i> Menunggu Verifikasi</span>
                        <span v-else-if="asset.verification_status === 'rejected'" class="px-1.5 py-0.5 bg-rose-100 text-rose-700 rounded text-[9px] font-bold flex items-center gap-1"><i class="fa-solid fa-circle-xmark"></i> Ditolak</span>
                        <span v-else-if="asset.verification_status === 'inactive'" class="px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded text-[9px] font-bold flex items-center gap-1"><i class="fa-solid fa-eye-slash"></i> Nonaktif</span>
                        <span v-else-if="asset.verification_status === 'approved' && asset.status" class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[9px] font-bold flex items-center gap-1"><i class="fa-solid fa-check-circle"></i> {{ asset.status === 'Tersedia' ? 'Tersedia' : 'Tersewa' }}</span>
                    </template>

                    <div v-if="asset.reviews_avg_rating" class="flex items-center gap-1 text-[#FFC000] text-[9px] font-bold ml-auto">
                        <div class="flex items-center gap-0.5">
                            <i class="fa-solid fa-star"></i> {{ Number(asset.reviews_avg_rating).toFixed(1) }}
                        </div>
                        <span v-if="asset.reviews_count" class="text-gray-400 font-medium">({{ asset.reviews_count }})</span>
                    </div>
                </div>

                <h3 class="font-bold text-sm md:text-base text-[#0A2540] truncate group-hover:text-[#FFC000] transition-colors">
                    {{ asset.title }}
                </h3>

                <div class="text-[10px] md:text-xs text-gray-500 font-medium truncate mt-0.5">
                    <i class="fa-solid fa-location-dot text-[#FFC000] mr-0.5"></i>
                    {{ [(asset.district?.name || asset.district), (asset.city?.name || asset.city)].filter(Boolean).join(', ') || 'Lokasi tidak diketahui' }}
                </div>
                
                <div class="text-[10px] md:text-[11px] text-[#10B981] font-bold mt-1.5 flex items-center gap-1">
                    <i class="fa-solid fa-calendar-check"></i>
                    {{ availabilityText }}
                </div>
            </div>

            <!-- ═══ HARGA & AKSI ═══ -->
            <div class="shrink-0 flex flex-col items-end justify-between self-stretch py-0.5">
                <div class="text-right mb-2 md:mb-0">
                    <p class="font-extrabold text-xs md:text-sm text-[#FFC000] leading-none">
                        <template v-if="asset.default_pricing || asset.price">
                            {{ formatRupiah(asset.default_pricing?.price || asset.price) }}
                        </template>
                        <template v-else>
                            Hubungi
                        </template>
                    </p>
                    <p v-if="asset.default_pricing || asset.price" class="text-[9px] md:text-[10px] text-gray-400 mt-1">
                        /{{ rentalUnitLabel(asset.type?.rental_unit || asset.rent_period) }}
                    </p>
                </div>

                <button
                    v-if="!isOwner"
                    class="mt-auto z-30 flex items-center justify-center transition-transform active:scale-125"
                    :class="isPending ? 'opacity-70 pointer-events-none' : 'hover:scale-110'"
                    @click.stop.prevent="toggleFavorite"
                >
                    <i :class="isFavorite ? 'fa-solid fa-heart text-red-500' : 'fa-regular fa-heart text-gray-400'" class="text-lg md:text-xl drop-shadow-sm transition-all duration-200"></i>
                </button>
            </div>
        </template>
    </div>
</template>

<style scoped>
/* Shimmer */
@keyframes shimmer {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(100%);  }
}
.animate-shimmer { animation: shimmer 1.5s infinite; }

/* ── TikTok Floating Heart ── */
.heart-particle {
    display: block;
    transform-origin: center bottom;
    animation: heart-rise 0.85s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    will-change: transform, opacity;
    line-height: 1;
    user-select: none;
    margin-left: -0.5em;
    margin-top: -0.5em;
}

@keyframes heart-rise {
    0% {
        transform: translateX(0) translateY(0) rotate(var(--angle, 0deg)) scale(0);
        opacity: 1;
    }
    15% {
        transform: translateX(calc(var(--drift, 0px) * 0.1)) translateY(-10px) rotate(var(--angle, 0deg)) scale(1.3);
        opacity: 1;
    }
    60% {
        transform: translateX(calc(var(--drift, 0px) * 0.7)) translateY(-70px) rotate(calc(var(--angle, 0deg) * 0.6)) scale(1);
        opacity: 0.9;
    }
    100% {
        transform: translateX(var(--drift, 0px)) translateY(-120px) rotate(0deg) scale(0.6);
        opacity: 0;
    }
}

.heart-fly-enter-active,
.heart-fly-leave-active { transition: none; }
</style>
