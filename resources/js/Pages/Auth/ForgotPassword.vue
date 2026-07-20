<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout>
        <Head title="Forgot Password" />

        <div class="mb-6 text-sm text-gray-600">
            Forgot your password? No problem. Enter your email address and we'll
            send you a password reset link.
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label
                    for="email"
                    class="block text-sm font-medium text-gray-700 mb-2"
                >
                    Email
                </label>

                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 outline-none transition focus:border-[#466080] focus:ring-2 focus:ring-[#466080]/20"
                />

                <p
                    v-if="form.errors.email"
                    class="mt-2 text-sm text-red-600"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-[#466080] px-5 py-2.5 text-white font-medium transition hover:bg-[#36506d] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Email Password Reset Link
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
