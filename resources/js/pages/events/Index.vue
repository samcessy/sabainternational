<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Event = {
    title: string;
    slug: string;
    description: string | null;
    start_at: string;
    end_at: string | null;
    location: string | null;
};

defineProps<{
    upcoming: Event[];
    past: Event[];
}>();

function formatDate(value: string): string {
    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
</script>

<template>
    <Head title="Events" />

    <section class="border-b border-border bg-primary text-primary-foreground">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                Events
            </h1>
            <p class="mt-4 text-primary-foreground/90">
                Join us in person or hear about what's coming up.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-xl font-bold text-foreground">Upcoming</h2>
        <div v-if="upcoming.length === 0" class="mt-4 text-muted-foreground">
            No upcoming events are scheduled right now.
        </div>
        <div v-else class="mt-4 grid gap-4 sm:grid-cols-2">
            <Link
                v-for="event in upcoming"
                :key="event.slug"
                :href="`/events/${event.slug}`"
                class="block"
            >
                <Card class="h-full transition-shadow hover:shadow-md">
                    <CardHeader>
                        <CardTitle>{{ event.title }}</CardTitle>
                    </CardHeader>
                    <CardContent
                        class="space-y-1 text-sm text-muted-foreground"
                    >
                        <p>{{ formatDate(event.start_at) }}</p>
                        <p v-if="event.location">{{ event.location }}</p>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <template v-if="past.length > 0">
            <h2 class="mt-12 text-xl font-bold text-foreground">Past Events</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <Link
                    v-for="event in past"
                    :key="event.slug"
                    :href="`/events/${event.slug}`"
                    class="block"
                >
                    <Card class="h-full transition-shadow hover:shadow-md">
                        <CardHeader>
                            <CardTitle>{{ event.title }}</CardTitle>
                        </CardHeader>
                        <CardContent
                            class="space-y-1 text-sm text-muted-foreground"
                        >
                            <p>{{ formatDate(event.start_at) }}</p>
                            <p v-if="event.location">{{ event.location }}</p>
                        </CardContent>
                    </Card>
                </Link>
            </div>
        </template>
    </section>
</template>
