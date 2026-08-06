<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import LocationSelect from '@/Components/UI/LocationSelect.vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    owner_profile: {
        type: Object,
        default: null,
    },
    bank_account: {
        type: Object,
        default: null,
    },
});

const user = usePage().props.auth.user;
const isOwner = usePage().props.user?.is_owner || false;

const dob_day = ref('');
const dob_month = ref('');
const dob_year = ref('');
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 100 }, (_, i) => currentYear - i);

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    gender: user.gender || '',
    date_of_birth: user.date_of_birth || '',
    national_id: props.owner_profile?.national_id || '',
    address: props.owner_profile?.address || '',
    place_of_birth_code: user.place_of_birth_code || '',
    bank_name: props.bank_account?.bank_name || '',
    account_number: props.bank_account?.account_number || '',
    account_holder: props.bank_account?.account_holder || '',
});

onMounted(() => {
    console.log("Auth User:", user);
    console.log("Place of birth code:", user.place_of_birth_code);
    
    if (user.date_of_birth) {
        const parts = user.date_of_birth.split('-');
        if (parts.length === 3) {
            dob_year.value = parts[0];
            dob_month.value = parseInt(parts[1], 10);
            dob_day.value = parseInt(parts[2], 10);
        }
    }
});

watch([dob_year, dob_month, dob_day], ([y, m, d]) => {
    if (y && m && d) {
        form.date_of_birth = `${y}-${m.toString().padStart(2, '0')}-${d.toString().padStart(2, '0')}`;
    } else {
        form.date_of_birth = '';
    }
});

const resetForm = () => {
    form.reset();
    if (user.date_of_birth) {
        const parts = user.date_of_birth.split('-');
        if (parts.length === 3) {
            dob_year.value = parts[0];
            dob_month.value = parseInt(parts[1], 10);
            dob_day.value = parseInt(parts[2], 10);
        }
    } else {
        dob_year.value = '';
        dob_month.value = '';
        dob_day.value = '';
    }
};
</script>

<template>
    <section>
        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-5"
        >
            <div class="space-y-5">
                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-sm text-[#333333] mb-1.5">Nama lengkap</label>
                    <input
                        id="name"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <p v-show="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- Nomor Ponsel -->
                <div>
                    <label for="phone" class="block text-sm text-[#333333] mb-1.5">Nomor ponsel</label>
                    <input
                        id="phone"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.phone"
                        autocomplete="tel"
                    />
                    <p class="mt-1.5 text-[13px] text-gray-500">Lengkapi nomor ponsel untuk dapat memulai booking.</p>
                    <p v-show="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm text-[#333333] mb-1.5">Email</label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <p class="mt-2 text-xs text-gray-500 leading-relaxed">Kami akan menghubungimu melalui email untuk masalah terkait akun dan tujuan komunikasi produk.</p>
                    <p v-show="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <!-- Kelamin -->
                <div>
                    <label class="block text-sm text-[#333333] mb-1.5">Jenis kelamin</label>
                    <div class="relative">
                        <select v-model="form.gender" class="block w-full appearance-none bg-white border border-gray-300 rounded-xl py-3 pl-4 pr-10 text-[15px] text-[#1D1D1F] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition-colors shadow-sm cursor-pointer">
                            <option value="" disabled selected>Pilih jenis kelamin</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                            <i class="fa-solid fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="block text-sm text-[#333333] mb-1.5">Tempat Lahir</label>
                    <LocationSelect
                        v-model="form.place_of_birth_code"
                        endpoint="/api/cities"
                        placeholder="Cari Kota/Kabupaten..."
                    />
                    <p v-show="form.errors.place_of_birth_code" class="mt-1 text-sm text-red-600">{{ form.errors.place_of_birth_code }}</p>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-sm text-[#333333] mb-1.5">Tanggal Lahir</label>
                    <div class="flex gap-2 sm:gap-3">
                        <!-- Tanggal -->
                        <div class="relative w-1/3">
                            <select v-model="dob_day" class="block w-full appearance-none bg-white border border-gray-300 rounded-xl py-3 pl-3 pr-8 text-[15px] text-[#1D1D1F] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition-colors shadow-sm cursor-pointer">
                                <option value="" disabled selected>DD</option>
                                <option v-for="d in 31" :key="d" :value="d">{{ String(d).padStart(2, '0') }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>

                        <!-- Bulan -->
                        <div class="relative w-1/3">
                            <select v-model="dob_month" class="block w-full appearance-none bg-white border border-gray-300 rounded-xl py-3 pl-3 pr-8 text-[15px] text-[#1D1D1F] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition-colors shadow-sm cursor-pointer">
                                <option value="" disabled selected>Bulan</option>
                                <option :value="1">Januari</option>
                                <option :value="2">Februari</option>
                                <option :value="3">Maret</option>
                                <option :value="4">April</option>
                                <option :value="5">Mei</option>
                                <option :value="6">Juni</option>
                                <option :value="7">Juli</option>
                                <option :value="8">Agustus</option>
                                <option :value="9">September</option>
                                <option :value="10">Oktober</option>
                                <option :value="11">November</option>
                                <option :value="12">Desember</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>

                        <!-- Tahun -->
                        <div class="relative w-1/3">
                            <select v-model="dob_year" class="block w-full appearance-none bg-white border border-gray-300 rounded-xl py-3 pl-3 pr-8 text-[15px] text-[#1D1D1F] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition-colors shadow-sm cursor-pointer">
                                <option value="" disabled selected>YYYY</option>
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <p v-show="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ form.errors.date_of_birth }}</p>
                </div>
            </div>

            <!-- Bagian Owner Profile -->
            <div v-if="isOwner" class="border-t border-gray-100 pt-6 mt-6 space-y-5">
                <header>
                    <h3 class="text-base font-bold text-[#1D1D1F]">Data Pemilik Aset (Owner)</h3>
                    <p class="text-xs text-gray-500 mt-1">Perbarui identitas Anda sebagai pemilik aset.</p>
                </header>

                <div>
                    <label for="national_id" class="block text-sm text-[#333333] mb-1.5">NIK (KTP)</label>
                    <input
                        id="national_id"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.national_id"
                        maxlength="16"
                    />
                    <p v-show="form.errors.national_id" class="mt-1 text-sm text-red-600">{{ form.errors.national_id }}</p>
                </div>

                <div>
                    <label for="address" class="block text-sm text-[#333333] mb-1.5">Alamat Lengkap</label>
                    <textarea
                        id="address"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.address"
                        rows="3"
                    ></textarea>
                    <p v-show="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                </div>

            </div>

            <!-- Bagian Rekening Bank -->
            <div v-if="isOwner" class="border-t border-gray-100 pt-6 mt-6 space-y-5">
                <header>
                    <h3 class="text-base font-bold text-[#1D1D1F]">Data Rekening Bank</h3>
                    <p class="text-xs text-gray-500 mt-1">Rekening ini digunakan untuk menerima pembayaran sewa.</p>
                </header>

                <div>
                    <label for="bank_name" class="block text-sm text-[#333333] mb-1.5">Nama Bank</label>
                    <input
                        id="bank_name"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.bank_name"
                        placeholder="Contoh: BCA, BNI, Mandiri"
                    />
                    <p v-show="form.errors.bank_name" class="mt-1 text-sm text-red-600">{{ form.errors.bank_name }}</p>
                </div>

                <div>
                    <label for="account_number" class="block text-sm text-[#333333] mb-1.5">Nomor Rekening</label>
                    <input
                        id="account_number"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.account_number"
                    />
                    <p v-show="form.errors.account_number" class="mt-1 text-sm text-red-600">{{ form.errors.account_number }}</p>
                </div>

                <div>
                    <label for="account_holder" class="block text-sm text-[#333333] mb-1.5">Atas Nama</label>
                    <input
                        id="account_holder"
                        type="text"
                        class="block w-full border border-gray-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-xl shadow-sm px-4 py-3 text-[15px] text-[#1D1D1F] transition-colors"
                        v-model="form.account_holder"
                    />
                    <p v-show="form.errors.account_holder" class="mt-1 text-sm text-red-600">{{ form.errors.account_holder }}</p>
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Alamat email Anda belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-[#FFC000] underline hover:text-[#e6ad00] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:ring-offset-2"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    Link verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6">
                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 translate-x-2"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm font-bold text-green-600 mr-2"
                    >
                        Tersimpan.
                    </p>
                </Transition>

                <!-- Tombol Batal/Nanti Saja -->
                <button
                    v-if="form.isDirty"
                    type="button"
                    @click="resetForm"
                    class="inline-flex items-center px-4 py-2 bg-transparent text-gray-500 font-bold rounded-xl text-sm transition-colors hover:text-gray-700"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    :disabled="form.processing || !form.isDirty"
                    class="inline-flex items-center px-6 py-2.5 rounded-xl font-bold text-sm transition-all duration-200"
                    :class="form.isDirty ? 'bg-primary text-white hover:bg-primary/80 active:scale-95 shadow-md cursor-pointer' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                >
                    <i v-if="form.processing" class="fa-solid fa-spinner fa-spin mr-2"></i>
                    Simpan
                </button>
            </div>
        </form>
    </section>
</template>

