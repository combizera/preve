<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-[7fr_3fr] lg:px-0"
    >
        <div
            class="relative hidden h-full flex-col bg-cover bg-center text-white lg:flex dark:border-r"
            style="background-image: url('/auth-bg.webp')"
        >
            <Link
                href="/"
                class="absolute top-10 left-10 z-20 flex items-center gap-2 text-xl font-medium"
            >
                <AppLogoIcon class="size-9.5" />
                <span class="mb-0.5">{{ name }}</span>
            </Link>
            <p
                class="absolute bottom-10 left-10 z-20 max-w-xl text-3xl tracking-tight"
            >
                Predictable finances.<br />
                <span class="font-bold">Peaceful days.</span>
            </p>
        </div>
        <div class="lg:p-8">
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-87.5"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
