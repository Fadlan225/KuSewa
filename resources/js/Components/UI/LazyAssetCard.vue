<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AssetCardSkeleton from './AssetCardSkeleton.vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    asset: { type: Object, required: true },
    categoryName: { type: String, required: true }
});

// thumbnailImages dari backend (max 3), fallback ke images lama atau first_image
const imgList = computed(() => props.asset.thumbnail_images || props.asset.images || []);
const img1 = computed(() => imgList.value[0]?.image_url || props.asset.first_image?.image_url);
const img2 = computed(() => imgList.value[1]?.image_url);
const img3 = computed(() => imgList.value[2]?.image_url);

const imageCount = computed(() => {
    if (img3.value) return 3;
    if (img2.value) return 2;
    if (img1.value) return 1;
    return 0;
});

// ── Intersection Observer (Lazy Load — Mobile Only) ───────────────────
// Di desktop semua kartu langsung visible → skip observer, render langsung.
// Di mobile/tablet pakai observer agar gambar tidak load sekaligus.
const isDesktop = typeof window !== 'undefined' && window.innerWidth >= 1024;
const isIntersecting = ref(isDesktop); // Desktop: langsung true, Mobile: tunggu observer
const imageLoaded = ref(false);
const elRef = ref(null);
let observer = null;

onMounted(() => {
    if (isDesktop) return; // Desktop tidak butuh observer
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            isIntersecting.value = true;
            observer?.disconnect();
            observer = null;
        }
    }, { rootMargin: '300px' }); // Lebih besar agar load lebih awal saat scroll
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

// ── Navigasi (tanpa <Link> agar tidak konflik di mobile) ──────────────
const navigateToAsset = () => {
    router.visit(route('assets.show', props.asset.id));
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
</script>

<template>
    <!-- Container kartu -->
    <div
        ref="elRef"
        class="flex-none w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px] snap-start flex flex-col bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden"
    >
        <!-- Skeleton sebelum masuk viewport -->
        <AssetCardSkeleton v-if="!isIntersecting" />

        <!-- Konten kartu asli – TANPA <Link> agar tidak konflik di mobile -->
        <div v-else class="w-full h-full flex flex-col group cursor-pointer">

            <!-- ═══ AREA GAMBAR ═══
                 Memisahkan touch (mobile) dan click (desktop) agar double-tap stabil
            -->
            <div
                class="aspect-[3/2] w-full relative bg-gray-100 overflow-hidden"
                @touchstart.passive="onTouchStart"
                @touchend="onTouchEnd"
                @click="onMouseClick"
            >
                <!-- Skeleton gambar download -->
                <div
                    v-if="asset.first_image?.image && !imageLoaded && !asset.imageError"
                    class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 animate-pulse z-10"
                >
                    <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                </div>

                <!-- 0 Image / Error -->
                <div v-if="!img1 || asset.imageError" class="absolute inset-0 w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-300 z-0">
                    <i class="fa-solid fa-image text-3xl mb-1"></i>
                    <span class="text-[10px] font-medium">No Image</span>
                </div>

                <!-- 1 Image Layout -->
                <div v-else-if="imageCount === 1" class="absolute inset-0 w-full h-full z-0">
                    <img :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :class="imageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'" loading="lazy" decoding="async" />
                </div>

                <!-- 2 Image Layout -->
                <div v-else-if="imageCount === 2" class="absolute inset-0 w-full h-full grid grid-cols-2 gap-0.5 z-0 bg-white">
                    <div class="h-full overflow-hidden relative">
                        <img :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :class="imageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'" loading="lazy" />
                    </div>
                    <div class="h-full overflow-hidden relative">
                        <img :src="img2" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                    </div>
                </div>

                <!-- 3 Image Layout -->
                <div v-else-if="imageCount >= 3" class="absolute inset-0 w-full h-full grid grid-cols-3 gap-0.5 z-0 bg-white">
                    <div class="col-span-2 h-full overflow-hidden relative">
                        <img :src="img1" @load="imageLoaded = true" @error="asset.imageError = true" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :class="imageLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'" loading="lazy" />
                    </div>
                    <div class="col-span-1 grid grid-rows-2 gap-0.5 h-full">
                        <div class="h-full overflow-hidden relative">
                            <img :src="img2" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                        </div>
                        <div class="h-full overflow-hidden relative">
                            <img :src="img3" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                        </div>
                    </div>
                </div>

                <!-- Gradients overlay -->
                <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-black/40 to-transparent pointer-events-none z-10"></div>
                <div class="absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-black/30 to-transparent pointer-events-none z-10"></div>

                <!-- Badge Kategori -->
                <div class="absolute top-0 left-0 z-20 bg-[#0A2540] text-white text-[10px] sm:text-[11px] font-bold px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-br-lg uppercase tracking-wider pointer-events-none">
                    {{ categoryName }}
                </div>

                <!-- ❤️ Tombol Favorit
                     Semua event touch di-stop agar TIDAK bubbling ke div gambar di atas
                     (mencegah konflik @touchend="onTouchEnd" dan @click="navigateToAsset")
                -->
                <button
                    class="absolute top-2.5 right-2.5 z-30 text-white drop-shadow-md flex items-center justify-center transition-transform active:scale-125"
                    :class="isPending ? 'opacity-70 pointer-events-none' : 'hover:scale-110'"
                    @touchstart.stop
                    @touchend.stop
                    @pointerdown.stop
                    @click.stop.prevent="toggleFavorite"
                >
                    <i
                        :class="isFavorite ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"
                        class="text-lg sm:text-xl drop-shadow transition-all duration-200"
                        :style="isFavorite ? 'color: #ff4d6d;' : 'color: white;'"
                    ></i>
                </button>

                <!-- Rating -->
                <div
                    v-if="asset.reviews_avg_rating"
                    class="absolute bottom-2 right-2 z-20 bg-[#FFC000] size-7 sm:size-8 rounded-full text-[10px] sm:text-[11px] font-bold text-white flex items-center justify-center shadow-md pointer-events-none"
                >
                    {{ Number(asset.reviews_avg_rating).toFixed(1) }}
                </div>

                <!-- 💖 Floating Hearts (TikTok style, spammable) -->
                <TransitionGroup name="heart-fly" tag="div" class="absolute inset-0 pointer-events-none z-30">
                    <div
                        v-for="heart in hearts"
                        :key="heart.id"
                        class="heart-particle absolute"
                        :style="{
                            left: heart.x + 'px',
                            top: heart.y + 'px',
                            '--drift': heart.drift + 'px',
                            '--angle': heart.angle + 'deg',
                            fontSize: heart.size + 'px',
                        }"
                    ><i class="fa-solid fa-heart text-red-500"></i></div>
                </TransitionGroup>
            </div>

            <!-- ═══ AREA TEKS – klik navigasi ═══ -->
            <div
                class="flex flex-col flex-grow p-3 sm:p-4 gap-1.5 sm:gap-2 bg-white"
                @click="navigateToAsset"
            >
                <h3 class="font-semibold text-sm sm:text-[15px] leading-tight text-[#0A2540] group-hover:text-[#FFC000] transition-colors line-clamp-1">
                    {{ asset.title }}
                </h3>

                <div class="text-[11px] sm:text-xs text-gray-500 font-medium flex items-center gap-1.5 truncate">
                    <i class="fa-solid fa-location-dot text-[12px] sm:text-[13px] text-[#FFC000] flex-shrink-0"></i>
                    <span class="truncate">
                        {{ [asset.city, asset.address].filter(Boolean).join(', ') || 'Lokasi tidak diketahui' }}
                    </span>
                </div>

                <div class="font-bold text-sm sm:text-base text-[#F97316] mt-auto pt-1">
                    <template v-if="asset.default_pricing">
                        {{ formatRupiah(asset.default_pricing.price) }}
                        <span class="text-[10px] font-normal text-gray-400">
                            /sewa
                        </span>
                    </template>
                    <span v-else class="text-[11px] font-medium text-gray-400">Hubungi Pemilik</span>
                </div>
            </div>

        </div>
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
