<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Confirm Password" />

        <div class="mb-6 text-sm text-gray-600">
            This is a secure area of the application. Please confirm your
            password before continuing.
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label
                    for="password"
                    class="block text-sm font-medium text-gray-700 mb-2"
                >
                    Password
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autofocus
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-[#466080] focus:ring-2 focus:ring-[#466080]/20 outline-none transition"
                />

                <p
                    v-if="form.errors.password"
                    class="mt-2 text-sm text-red-600"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-[#466080] px-5 py-2.5 text-white font-medium transition hover:bg-[#36506d] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Confirm
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
