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
    <section>
        <div class="flex items-center justify-between p-4 sm:p-5 border border-gray-200/80 rounded-2xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] bg-white transition-all hover:shadow-[0_4px_12px_-4px_rgba(0,0,0,0.08)]">
            <div class="pr-4">
                <p class="text-[15px] font-bold text-[#1D1D1F]">Hapus Akun</p>
                <p class="text-[13px] text-gray-500 mt-1 leading-relaxed">Setelah akun dihapus, kamu tidak akan bisa mengakses data-datanya untuk selamanya.</p>
            </div>
            <button
                type="button"
                @click="confirmUserDeletion"
                class="px-4 py-2 text-sm font-bold text-[#FFC000] hover:text-[#e6ad00] active:scale-95 transition-all shrink-0"
            >
                Hapus
            </button>
        </div>

        <Teleport to="body" v-if="confirmingUserDeletion">
            <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300">
                    <h2 class="text-lg font-bold text-gray-900">
                        Apakah Anda yakin ingin menghapus akun?
                    </h2>

                    <p class="mt-2 text-sm text-gray-600">
                        Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun secara permanen.
                    </p>

                    <div class="mt-6">
                        <label for="password" class="sr-only">Kata Sandi</label>

                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-xl shadow-sm px-4 py-2"
                            placeholder="Masukkan Kata Sandi"
                            @keyup.enter="deleteUser"
                        />

                        <p v-show="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 flex-col-reverse sm:flex-row">
                        <button
                            type="button"
                            @click="closeModal"
                            class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-xl font-bold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none active:scale-95 transition ease-in-out duration-150"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="form.processing"
                            @click="deleteUser"
                            class="inline-flex items-center justify-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:scale-95 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                        >
                            Hapus Akun
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </section>
</template>
