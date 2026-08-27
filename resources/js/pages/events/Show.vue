<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

type Event = {
    title: string;
    slug: string;
    description: string | null;
    start_at: string;
    end_at: string | null;
    location: string | null;
};

defineProps<{
    event: Event;
}>();

function formatDate(value: string): string {
    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
</script>

<template>
    <Head :title="event.title" />

    <article>
        <section
            class="border-b border-border bg-primary text-primary-foreground"
        >
            <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                    {{ event.title }}
                </h1>
                <p class="mt-3 text-primary-foreground/90">
                    {{ formatDate(event.start_at) }}
                    <template v-if="event.end_at">
                        – {{ formatDate(event.end_at) }}
                    </template>
                </p>
                <p
                    v-if="event.location"
                    class="mt-1 text-primary-foreground/90"
                >
                    {{ event.location }}
                </p>
            </div>
        </section>

        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <div
                v-if="event.description"
                class="space-y-4 whitespace-pre-line text-muted-foreground"
            >
                {{ event.description }}
            </div>

            <div class="mt-10 flex flex-wrap gap-3">
                <Button as-child variant="cta" size="lg">
                    <Link href="/give">Support This Work</Link>
                </Button>
                <Button as-child variant="outline" size="lg">
                    <Link href="/events">All Events</Link>
                </Button>
            </div>
        </div>
    </article>
</template>
