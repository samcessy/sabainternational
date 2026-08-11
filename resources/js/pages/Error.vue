<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{ status: number }>();

const copy = computed(() => {
    switch (props.status) {
        case 403:
            return {
                title: "You don't have access to this page",
                description:
                    'If you think this is a mistake, please contact us.',
            };
        case 404:
            return {
                title: 'Page not found',
                description:
                    "The page you're looking for doesn't exist or may have moved.",
            };
        case 419:
            return {
                title: 'Your session expired',
                description: 'Please refresh the page and try again.',
            };
        case 429:
            return {
                title: 'Too many requests',
                description: 'Please wait a moment before trying again.',
            };
        default:
            return {
                title: 'Something went wrong',
                description: 'Please try again in a little while.',
            };
    }
});
</script>

<template>
    <Head :title="`${status}`" />

    <section
        class="mx-auto flex min-h-[60vh] max-w-xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6 lg:px-8"
    >
        <p class="text-sm font-semibold text-muted-foreground">
            Error {{ status }}
        </p>
        <h1 class="mt-2 text-3xl font-bold text-foreground">
            {{ copy.title }}
        </h1>
        <p class="mt-4 text-muted-foreground">{{ copy.description }}</p>
        <Button as-child variant="cta" size="lg" class="mt-8">
            <Link href="/">Return Home</Link>
        </Button>
    </section>
</template>
