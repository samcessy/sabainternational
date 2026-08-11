<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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
};

defineProps<{
    program: Program;
}>();

// Structure follows saba.md §6.1's Problem→Context→Role narrative, using
// only what's actually verified (docs/audit/current-website-audit.md) —
// overview and what_happens_here render only when real content exists for
// them, rather than showing an empty section with a heading and nothing
// under it.
</script>

<template>
    <Head :title="program.name" />

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
