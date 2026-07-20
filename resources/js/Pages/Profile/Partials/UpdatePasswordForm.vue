<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Perbarui Kata Sandi
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk tetap aman.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <label for="current_password" class="block font-medium text-sm text-gray-700">Kata Sandi Saat Ini</label>

                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full border-gray-300 focus:border-[#466080] focus:ring-[#466080] rounded-md shadow-sm"
                    autocomplete="current-password"
                />

                <p v-show="form.errors.current_password" class="mt-2 text-sm text-red-600">
                    {{ form.errors.current_password }}
                </p>
            </div>

            <div>
                <label for="password" class="block font-medium text-sm text-gray-700">Kata Sandi Baru</label>

                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full border-gray-300 focus:border-[#466080] focus:ring-[#466080] rounded-md shadow-sm"
                    autocomplete="new-password"
                />

                <p v-show="form.errors.password" class="mt-2 text-sm text-red-600">
                    {{ form.errors.password }}
                </p>
            </div>

            <div>
                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Konfirmasi Kata Sandi</label>

                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full border-gray-300 focus:border-[#466080] focus:ring-[#466080] rounded-md shadow-sm"
                    autocomplete="new-password"
                />

                <p v-show="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
                    {{ form.errors.password_confirmation }}
                </p>
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
