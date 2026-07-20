<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Reset Password" />

        <form @submit.prevent="submit">

            <!-- Email -->
            <div>
                <label
                    for="email"
                    class="block text-sm font-medium text-gray-700"
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
                    class="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#466080] focus:ring-[#466080]"
                />

                <p
                    v-if="form.errors.email"
                    class="mt-2 text-sm text-red-600"
                >
                    {{ form.errors.email }}
                </p>
            </div>


            <!-- Password -->
            <div class="mt-4">
                <label
                    for="password"
                    class="block text-sm font-medium text-gray-700"
                >
                    Password
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#466080] focus:ring-[#466080]"
                />

                <p
                    v-if="form.errors.password"
                    class="mt-2 text-sm text-red-600"
                >
                    {{ form.errors.password }}
                </p>
            </div>


            <!-- Confirm Password -->
            <div class="mt-4">
                <label
                    for="password_confirmation"
                    class="block text-sm font-medium text-gray-700"
                >
                    Confirm Password
                </label>

                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#466080] focus:ring-[#466080]"
                />

                <p
                    v-if="form.errors.password_confirmation"
                    class="mt-2 text-sm text-red-600"
                >
                    {{ form.errors.password_confirmation }}
                </p>
            </div>


            <!-- Button -->
            <div class="mt-4 flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-[#466080] px-5 py-2.5 text-white font-medium transition hover:bg-[#36506d] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Reset Password
                </button>
            </div>

        </form>
    </AuthLayout>
</template>
