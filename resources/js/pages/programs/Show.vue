<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import Seo from '@/components/Seo.vue';
import { Button } from '@/components/ui/button';

type Program = {
    name: string;
    legal_name: string | null;
    slug: string;
    category: string;
    category_label: string;
    founded_year: number | null;
    location: string | null;
    short_description: string | null;
    overview: string | null;
    what_happens_here: string | null;
    external_url: string | null;
    seo: {
        title: string | null;
        description: string | null;
        og_image: string | null;
    };
};

const props = defineProps<{
    program: Program;
}>();

const page = usePage<{ url: string }>();
const origin = computed(() => new URL(page.props.url).origin);

const breadcrumbItems = computed(() => [
    { title: 'Home', href: '/' },
    { title: 'Our Programs', href: '/programs' },
    { title: props.program.name, href: '' },
]);

const schema = computed(() => ({
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
}));

// Structure follows saba.md §6.1's Problem→Context→Role narrative, using
// only what's actually verified (docs/audit/current-website-audit.md) —
// overview and what_happens_here render only when real content exists for
// them, rather than showing an empty section with a heading and nothing
// under it.
</script>

<template>
    <Seo
        :title="program.seo.title ?? program.name"
        :description="program.seo.description ?? program.short_description"
        :image="program.seo.og_image"
        :schema="schema"
    />

    <div class="mx-auto max-w-3xl px-4 pt-6 sm:px-6 lg:px-8">
        <Breadcrumbs :breadcrumbs="breadcrumbItems" />
    </div>

    <section class="border-b border-border bg-primary text-primary-foreground">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <p
                class="text-sm font-semibold tracking-wide text-primary-foreground/80 uppercase"
            >
                {{ program.category_label }}
                <span v-if="program.founded_year">
                    &middot; Est. {{ program.founded_year }}</span
                >
            </p>
            <h1 class="mt-2 text-4xl font-bold tracking-tight sm:text-5xl">
                {{ program.name }}
            </h1>
            <p v-if="program.location" class="mt-3 text-primary-foreground/90">
                {{ program.location }}
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <p v-if="program.short_description" class="text-lg text-foreground">
            {{ program.short_description }}
        </p>

        <div v-if="program.overview" class="mt-8">
            <h2 class="text-xl font-bold text-foreground">Overview</h2>
            <p class="mt-3 text-muted-foreground">{{ program.overview }}</p>
        </div>

        <div v-if="program.what_happens_here" class="mt-8">
            <h2 class="text-xl font-bold text-foreground">What Happens Here</h2>
            <p class="mt-3 text-muted-foreground">
                {{ program.what_happens_here }}
            </p>
        </div>

        <div class="mt-10 flex flex-wrap gap-3">
            <Button as-child variant="cta" size="lg">
                <Link href="/give">Support This Work</Link>
            </Button>
            <Button
                v-if="program.external_url"
                as-child
                variant="outline"
                size="lg"
            >
                <a
                    :href="program.external_url"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Visit {{ program.name }}'s Site
                </a>
            </Button>
        </div>
    </section>
</template>
