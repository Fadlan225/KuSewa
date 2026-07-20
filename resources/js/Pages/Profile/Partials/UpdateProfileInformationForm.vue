<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Informasi Profil
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Perbarui informasi profil akun dan alamat email Anda.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nama</label>

                <input
                    id="name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 focus:border-[#466080] focus:ring-[#466080] rounded-md shadow-sm"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <p v-show="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>

                <input
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-gray-300 focus:border-[#466080] focus:ring-[#466080] rounded-md shadow-sm"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <p v-show="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Alamat email Anda belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#466080] focus:ring-offset-2"
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

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center px-4 py-2 bg-[#466080] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#36506d] active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-[#466080] focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                >
                    Simpan
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Disimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
