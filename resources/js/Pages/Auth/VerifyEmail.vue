<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout>
        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600">
            Thanks for signing up! Before getting started, please verify your
            email address by clicking the link we sent to your email. If you
            didn't receive it, we can send another verification link.
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-[#466080] px-5 py-2.5 text-white font-medium transition hover:bg-[#36506d] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Resend Verification Email
                </button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#466080] focus:ring-offset-2"
                >
                    Log Out
                </Link>

            </div>
        </form>
    </AuthLayout>
</template>
