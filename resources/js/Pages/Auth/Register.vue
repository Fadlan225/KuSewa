<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    if (!isPasswordStrong.value) {
        return;
    }

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const showPassword = ref(false);

const passwordScore = computed(() => {
    const password = form.password;

    let score = 0;

    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    return score;
});

const passwordLevel = computed(() => {
    switch (passwordScore.value) {
        case 0:
        case 1:
            return {
                label: 'Sangat Lemah',
                level: 1,
            };

        case 2:
            return {
                label: 'Lemah',
                level: 2,
            };

        case 3:
            return {
                label: 'Kuat',
                level: 3,
            };

        case 4:
            return {
                label: 'Sangat Kuat',
                level: 4,
            };
    }
});

const isPasswordStrong = computed(() => {
    return passwordScore.value === 4 && form.password.length >= 8;
});
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    maxlength="255"
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="phone" value="Phone" />

                <TextInput
                    id="phone"
                    type="tel"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    required
                    maxlength="15"
                    autocomplete="tel"
                    @input="form.phone = form.phone.replace(/[^0-9]/g, '')"
                />

                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <div class="relative">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="mt-1 block w-full pr-10"
                        v-model="form.password"
                        required
                        minlength="8"
                        maxlength="64"
                        autocomplete="new-password"
                    />

                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"
                    >
                        <i
                            :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"
                        ></i>
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />

                <div
                    class="mt-3"
                    v-if="form.password.length > 0"
                >
                    <div class="flex gap-1">
                        <div
                            v-for="index in 4"
                            :key="index"
                            class="h-2 flex-1 rounded-full transition-all duration-300"
                            :class="
                                index <= passwordLevel.level
                                    ? {
                                        1: 'bg-red-500',
                                        2: 'bg-orange-500',
                                        3: 'bg-yellow-500',
                                        4: 'bg-green-500'
                                    }[passwordLevel.level]
                                    : 'bg-gray-200'
                            "
                        ></div>
                    </div>

                    <p class="mt-2 text-sm font-medium">
                        Kekuatan password:
                        {{ passwordLevel.label }}
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    :type="showPassword ? 'text' : 'password'"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing || !isPasswordStrong }"
                    :disabled="form.processing || !isPasswordStrong"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
