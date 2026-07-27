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
        <form @submit.prevent="updatePassword" class="space-y-6">
            <div>
                <label for="current_password" class="block font-medium text-sm text-gray-700 mb-1">Kata Sandi Saat Ini</label>

                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                    autocomplete="current-password"
                />

                <p v-show="form.errors.current_password" class="mt-2 text-sm text-red-600">
                    {{ form.errors.current_password }}
                </p>
            </div>

            <div>
                <label for="password" class="block font-medium text-sm text-gray-700 mb-1">Kata Sandi Baru</label>

                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                    autocomplete="new-password"
                />

                <p v-show="form.errors.password" class="mt-2 text-sm text-red-600">
                    {{ form.errors.password }}
                </p>
            </div>

            <div>
                <label for="password_confirmation" class="block font-medium text-sm text-gray-700 mb-1">Konfirmasi Kata Sandi</label>

                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="block w-full border-gray-300 focus:border-[#0A2540] focus:ring-[#0A2540] rounded-xl shadow-sm px-4 py-2"
                    autocomplete="new-password"
                />

                <p v-show="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center px-6 py-3 bg-[#0A2540] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#0f3459] active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#0A2540] focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                >
                    Simpan Kata Sandi
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
