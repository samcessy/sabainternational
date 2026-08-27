<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import Seo from '@/components/Seo.vue';
import { Button } from '@/components/ui/button';

type Event = {
    title: string;
    slug: string;
    description: string | null;
    start_at: string;
    end_at: string | null;
    location: string | null;
};

const props = defineProps<{
    event: Event;
}>();

const page = usePage<{ url: string }>();
const origin = computed(() => new URL(page.props.url).origin);

const breadcrumbItems = computed(() => [
    { title: 'Home', href: '/' },
    { title: 'Events', href: '/events' },
    { title: props.event.title, href: '' },
]);

const schema = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'Event',
        name: props.event.title,
        description: props.event.description,
        startDate: props.event.start_at,
        endDate: props.event.end_at,
        location: props.event.location
            ? { '@type': 'Place', name: props.event.location }
            : undefined,
    },
    {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: breadcrumbItems.value.map((item, index) => ({
            '@type': 'ListItem',
            position: index + 1,
            name: item.title,
            item:
                item.href === ''
                    ? page.props.url
                    : new URL(item.href, origin.value).href,
        })),
    },
]);

function formatDate(value: string): string {
    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
</script>

<template>
    <Seo
        :title="event.title"
        :description="event.description"
        type="article"
        :schema="schema"
    />

    <article>
        <div class="mx-auto max-w-3xl px-4 pt-6 sm:px-6 lg:px-8">
            <Breadcrumbs :breadcrumbs="breadcrumbItems" />
        </div>

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
