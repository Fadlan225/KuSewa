<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const faqs = ref([
    {
        question: 'Apakah pendaftaran dikenakan biaya?',
        answer: 'Pendaftaran dan penambahan aset di KitaSewa sepenuhnya gratis. Anda baru akan dikenakan potongan biaya layanan (service fee) kecil hanya ketika ada transaksi penyewaan yang berhasil dibayar melalui platform kami.',
        isOpen: false,
    },
    {
        question: 'Apa saja yang perlu disiapkan?',
        answer: 'Anda perlu menyiapkan KTP untuk verifikasi akun, serta detail informasi aset seperti foto-foto yang jelas, fasilitas, alamat lengkap, dan harga sewa.',
        isOpen: false,
    },
    {
        question: 'Berapa lama proses verifikasi?',
        answer: 'Proses verifikasi data pemilik dan aset biasanya memakan waktu maksimal 1x24 jam pada hari kerja.',
        isOpen: false,
    },
    {
        question: 'Apakah saya bisa mendaftarkan beberapa aset?',
        answer: 'Ya. Anda dapat mendaftarkan dan mengelola berbagai jenis aset (kos, ruko, lahan, dll) secara bersamaan hanya dari satu akun.',
        isOpen: false,
    }
]);

const toggleFaq = (index) => {
    faqs.value[index].isOpen = !faqs.value[index].isOpen;
};

const page = usePage();
const getRegistrationRoute = computed(() => {
    const user = page.props.auth?.user;
    if (!user) return route('owner.register'); // Will redirect to login via auth middleware
    if (user.is_owner || user.role === 'owner') return route('owner.asset.create');
    return route('owner.register');
});
</script>

<template>
    <Head title="Sewakan Aset Anda - KitaSewa" />

    <AppLayout :hideNavbar="true">
        <!-- NAVBAR MINIMALIS -->
        <nav class="fixed top-0 inset-x-0 h-[72px] bg-white border-b border-slate-200 z-50 flex items-center">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 w-full flex justify-between items-center">
                <Link :href="route('Home')" class="flex items-center gap-2">
                    <img
                        src="/kitasewa-logo.png"
                        alt="KitaSewa Logo"
                        class="h-8 md:h-9 w-auto object-contain"
                    />
                    <span class="font-bold text-xl text-slate-900 tracking-tight">
                        kitasewa<span class="text-[#FFC000]">.id</span>
                    </span>
                </Link>

                <Link :href="getRegistrationRoute" class="px-5 py-2 text-sm md:text-base font-semibold text-slate-900 hover:text-slate-600 transition-colors">
                    Daftarkan Aset
                </Link>
            </div>
        </nav>

        <main class="flex-grow pt-[72px] bg-white text-slate-900">
            <!-- 1. HERO SECTION -->
            <section class="max-w-7xl mx-auto px-6 lg:px-8 py-24 md:py-32 lg:py-40">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="max-w-xl">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight leading-[1.1] mb-6 text-slate-900">
                            Sewakan aset Anda di KitaSewa.
                        </h1>
                        <p class="text-lg md:text-xl text-slate-600 mb-10 leading-relaxed">
                            Daftarkan aset Anda dan kelola penyewaan dari satu tempat.
                        </p>
                        <Link :href="getRegistrationRoute" class="inline-flex items-center justify-center px-8 py-4 bg-slate-900 text-white font-medium rounded-md hover:bg-slate-800 transition-colors">
                            Daftarkan Aset
                        </Link>
                    </div>
                    <!-- Area untuk visual (Foto nyata/arsitektur/aset asli). Jika kosong, ini akan memberikan whitespace yang bagus. -->
                    <div class="hidden lg:block bg-slate-100 rounded-xl aspect-[4/3] w-full border border-slate-200">
                        <!-- Placeholder foto aset -->
                    </div>
                </div>
            </section>

            <!-- 2. APA YANG ANDA DAPATKAN -->
            <section class="max-w-7xl mx-auto px-6 lg:px-8 py-24 border-t border-slate-200">
                <div class="mb-16 max-w-2xl">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Satu tempat untuk mengelola aset Anda.</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Tampilkan</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Pasang aset Anda agar dapat ditemukan calon penyewa.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Kelola</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Atur informasi, harga, ketersediaan, dan booking.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Terhubung</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Berkomunikasi langsung dengan calon penyewa.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 3. CARA BERGABUNG -->
            <section class="bg-slate-50 border-t border-slate-200 py-24">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="mb-16">
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900">Mulai di KitaSewa</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                        <div class="flex flex-col">
                            <span class="text-sm font-mono text-slate-400 mb-4">01 &mdash; Daftar</span>
                            <p class="text-slate-900 leading-relaxed">
                                Buat akun pemilik dan lengkapi data diri.
                            </p>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-mono text-slate-400 mb-4">02 &mdash; Verifikasi</span>
                            <p class="text-slate-900 leading-relaxed">
                                Kami memeriksa data pemilik sebelum akun digunakan.
                            </p>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-mono text-slate-400 mb-4">03 &mdash; Daftarkan aset</span>
                            <p class="text-slate-900 leading-relaxed">
                                Tambahkan aset, harga, foto, dan informasi lainnya.
                            </p>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-mono text-slate-400 mb-4">04 &mdash; Siap disewakan</span>
                            <p class="text-slate-900 leading-relaxed">
                                Setelah disetujui, aset dapat ditemukan oleh calon penyewa.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4. JENIS ASET -->
            <section class="max-w-7xl mx-auto px-6 lg:px-8 py-24 border-t border-slate-200">
                <div class="mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Apa yang bisa Anda sewakan?</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-12 gap-x-16">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Hunian</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Kos &middot; Villa &middot; Apartemen &middot; Homestay &middot; Guest House &middot; Kontrakan
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Komersial</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Ruko &middot; Kios &middot; Kantor &middot; Gedung
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Lahan & Penyimpanan</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Lahan &middot; Gudang
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Periklanan & Event</h3>
                        <p class="text-slate-600 leading-relaxed">
                            Baliho &middot; Videotron &middot; Aula &middot; Ruang Meeting &middot; Studio
                        </p>
                    </div>
                </div>
            </section>

            <!-- 5. FAQ & CTA -->
            <section class="max-w-7xl mx-auto px-6 lg:px-8 py-24 border-t border-slate-200">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-20">
                    <!-- FAQ -->
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900 mb-10">Pertanyaan sebelum mendaftar</h2>
                        <div class="space-y-0 border-t border-slate-200">
                            <div v-for="(faq, index) in faqs" :key="index" class="border-b border-slate-200">
                                <button
                                    @click="toggleFaq(index)"
                                    class="w-full py-6 text-left flex justify-between items-center focus:outline-none group"
                                >
                                    <span class="font-medium text-slate-900 pr-4 group-hover:text-slate-600 transition-colors">{{ faq.question }}</span>
                                    <ChevronDown
                                        class="w-5 h-5 text-slate-400 transition-transform duration-300 shrink-0"
                                        :class="{ 'rotate-180': faq.isOpen }"
                                    />
                                </button>
                                <div
                                    v-show="faq.isOpen"
                                    class="pb-6 text-slate-600 leading-relaxed pr-8"
                                >
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="flex flex-col justify-center items-start lg:pl-12">
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900 mb-4">Punya aset yang ingin disewakan?</h2>
                        <p class="text-lg text-slate-600 mb-10">
                            Mulai dengan mendaftarkannya di KitaSewa.
                        </p>
                        <Link :href="getRegistrationRoute" class="inline-flex items-center justify-center px-8 py-4 bg-slate-900 text-white font-medium rounded-md hover:bg-slate-800 transition-colors w-full sm:w-auto">
                            Daftarkan Aset
                        </Link>
                    </div>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
