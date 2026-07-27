<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

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

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    national_id: props.owner_profile?.national_id || '',
    address: props.owner_profile?.address || '',
    place_of_birth: props.owner_profile?.place_of_birth || '',
    date_of_birth: props.owner_profile?.date_of_birth || '',
    bank_name: props.bank_account?.bank_name || '',
    account_number: props.bank_account?.account_number || '',
    account_holder: props.bank_account?.account_holder || '',
});
</script>

<template>
    <section>
        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-8"
        >
            <div class="space-y-6">
                <div>
                    <label for="name" class="block font-medium text-sm text-gray-700 mb-1">Nama</label>
                    <input
                        id="name"
                        type="text"
                        class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <p v-show="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="email" class="block font-medium text-sm text-gray-700 mb-1">Email</label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <p v-show="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="phone" class="block font-medium text-sm text-gray-700 mb-1">Nomor Telepon</label>
                    <input
                        id="phone"
                        type="text"
                        class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                        v-model="form.phone"
                        autocomplete="tel"
                    />
                    <p v-show="form.errors.phone" class="mt-2 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>
            </div>

            <!-- Bagian Owner Profile -->
            <div v-if="isOwner" class="border-t border-gray-200 pt-8 space-y-6">
                <header>
                    <h3 class="text-md font-bold text-gray-900">Data Pemilik Aset (Owner)</h3>
                    <p class="mt-1 text-sm text-gray-600">Perbarui identitas Anda sebagai pemilik aset.</p>
                </header>

                <div>
                    <label for="national_id" class="block font-medium text-sm text-gray-700 mb-1">NIK (KTP)</label>
                    <input
                        id="national_id"
                        type="text"
                        class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                        v-model="form.national_id"
                        maxlength="16"
                    />
                    <p v-show="form.errors.national_id" class="mt-2 text-sm text-red-600">{{ form.errors.national_id }}</p>
                </div>

                <div>
                    <label for="address" class="block font-medium text-sm text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea
                        id="address"
                        class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                        v-model="form.address"
                        rows="3"
                    ></textarea>
                    <p v-show="form.errors.address" class="mt-2 text-sm text-red-600">{{ form.errors.address }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="place_of_birth" class="block font-medium text-sm text-gray-700 mb-1">Tempat Lahir</label>
                        <input
                            id="place_of_birth"
                            type="text"
                            class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                            v-model="form.place_of_birth"
                        />
                        <p v-show="form.errors.place_of_birth" class="mt-2 text-sm text-red-600">{{ form.errors.place_of_birth }}</p>
                    </div>

                    <div>
                        <label for="date_of_birth" class="block font-medium text-sm text-gray-700 mb-1">Tanggal Lahir</label>
                        <input
                            id="date_of_birth"
                            type="date"
                            class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                            v-model="form.date_of_birth"
                        />
                        <p v-show="form.errors.date_of_birth" class="mt-2 text-sm text-red-600">{{ form.errors.date_of_birth }}</p>
                    </div>
                </div>
            </div>

            <!-- Bagian Rekening Bank -->
            <div v-if="isOwner" class="border-t border-gray-200 pt-8 space-y-6">
                <header>
                    <h3 class="text-md font-bold text-gray-900">Data Rekening Bank</h3>
                    <p class="mt-1 text-sm text-gray-600">Rekening ini digunakan untuk menerima pembayaran sewa.</p>
                </header>

                <div>
                    <label for="bank_name" class="block font-medium text-sm text-gray-700 mb-1">Nama Bank</label>
                    <input
                        id="bank_name"
                        type="text"
                        class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                        v-model="form.bank_name"
                        placeholder="Contoh: BCA, BNI, Mandiri"
                    />
                    <p v-show="form.errors.bank_name" class="mt-2 text-sm text-red-600">{{ form.errors.bank_name }}</p>
                </div>

                <div>
                    <label for="account_number" class="block font-medium text-sm text-gray-700 mb-1">Nomor Rekening</label>
                    <input
                        id="account_number"
                        type="text"
                        class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                        v-model="form.account_number"
                    />
                    <p v-show="form.errors.account_number" class="mt-2 text-sm text-red-600">{{ form.errors.account_number }}</p>
                </div>

                <div>
                    <label for="account_holder" class="block font-medium text-sm text-gray-700 mb-1">Atas Nama</label>
                    <input
                        id="account_holder"
                        type="text"
                        class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                        v-model="form.account_holder"
                    />
                    <p v-show="form.errors.account_holder" class="mt-2 text-sm text-red-600">{{ form.errors.account_holder }}</p>
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

            <div class="flex items-center gap-4 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center px-6 py-3 bg-[#0A2540] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#0f3459] active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                >
                    Simpan Perubahan
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-y-2"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm font-bold text-green-600"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
