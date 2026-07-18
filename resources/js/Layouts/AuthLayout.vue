<script setup>
import { Head } from '@inertiajs/vue3'
</script>

<template>

    <Head title="KuSewa" />

    <div class="min-h-screen bg-background flex items-center justify-center">

        <main class="w-full max-w-md">

            <slot />

        </main>

    </div>

</template>
