<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Hapus Akun
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.
            </p>
        </header>

        <button
            type="button"
            @click="confirmUserDeletion"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            Hapus Akun
        </button>

        <Teleport to="body" v-if="confirmingUserDeletion">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl transform transition-all duration-300">
                    <h2 class="text-lg font-medium text-gray-900">
                        Apakah Anda yakin ingin menghapus akun?
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun secara permanen.
                    </p>

                    <div class="mt-6">
                        <label for="password" class="sr-only">Kata Sandi</label>

                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-3/4 border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm"
                            placeholder="Kata Sandi"
                            @keyup.enter="deleteUser"
                        />

                        <p v-show="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeModal"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="form.processing"
                            @click="deleteUser"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                        >
                            Hapus Akun
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </section>
</template>
