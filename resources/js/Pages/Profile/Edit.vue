<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthLayout>
        <div class="py-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-[#0A2540]">Profil</h1>
                <Link
                    :href="route('Home')"
                    class="text-sm font-medium text-[#466080] hover:underline"
                >
                    Kembali
                </Link>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 shadow-md rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <div class="bg-white p-6 shadow-md rounded-lg">
                    <UpdatePasswordForm />
                </div>

                <div class="bg-white p-6 shadow-md rounded-lg">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
