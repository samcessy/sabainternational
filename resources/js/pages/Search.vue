<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Seo from '@/components/Seo.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Result = {
    type: string;
    title: string;
    snippet: string | null;
    href: string;
};

type Pagination = {
    current_page: number;
    last_page: number;
    total: number;
} | null;

const props = defineProps<{
    query: string;
    type: string | null;
    results: Result[];
    pagination: Pagination;
}>();

const typeLabels: Record<string, string> = {
    story: 'Story',
    program: 'Program',
    document: 'Document',
    page: 'Page',
    event: 'Event',
};

const categories = [
    { value: null, label: 'All' },
    { value: 'story', label: 'Stories' },
    { value: 'program', label: 'Programs' },
    { value: 'document', label: 'Documents' },
    { value: 'page', label: 'Pages' },
    { value: 'event', label: 'Events' },
];

function categoryHref(value: string | null): string {
    const params = new URLSearchParams({ q: props.query });

    if (value) {
        params.set('type', value);
    }

    return `/search?${params.toString()}`;
}

function pageHref(page: number): string {
    const params = new URLSearchParams({ q: props.query, page: String(page) });

    if (props.type) {
        params.set('type', props.type);
    }

    return `/search?${params.toString()}`;
}
</script>

<template>
    <Seo title="Search" noindex />

    <section class="border-b border-border bg-primary text-primary-foreground">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                Search
            </h1>
            <form action="/search" method="get" class="mt-6 flex gap-2">
                <Input
                    type="search"
                    name="q"
                    :default-value="query"
                    placeholder="Search stories, programs, documents…"
                    class="bg-primary-foreground text-foreground"
                    aria-label="Search"
                />
                <Button type="submit" variant="cta">Search</Button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <div v-if="query === ''" class="text-center text-muted-foreground">
            Enter a search term to get started.
        </div>

        <template v-else>
            <nav
                class="flex flex-wrap gap-2"
                aria-label="Filter search results by category"
            >
                <Link
                    v-for="category in categories"
                    :key="category.label"
                    :href="categoryHref(category.value)"
                    :class="[
                        'rounded-full px-3 py-1 text-sm font-medium transition-colors',
                        type === category.value
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-secondary text-secondary-foreground hover:bg-secondary/70',
                    ]"
                >
                    {{ category.label }}
                </Link>
            </nav>

            <div
                v-if="results.length === 0"
                class="mt-10 text-center text-muted-foreground"
            >
                No results for "{{ query }}".
            </div>

            <ul v-else class="mt-8 space-y-6">
                <li
                    v-for="(result, resultIndex) in results"
                    :key="`${result.type}-${resultIndex}`"
                    class="border-b border-border pb-6 last:border-0"
                >
                    <Link :href="result.href" class="group block">
                        <div class="flex items-center gap-2">
                            <Badge variant="secondary">
                                {{ typeLabels[result.type] ?? result.type }}
                            </Badge>
                        </div>
                        <h2
                            class="mt-2 text-lg font-semibold text-foreground group-hover:underline"
                        >
                            {{ result.title }}
                        </h2>
                        <p
                            v-if="result.snippet"
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            {{ result.snippet }}
                        </p>
                    </Link>
                </li>
            </ul>

            <nav
                v-if="pagination && pagination.last_page > 1"
                class="mt-10 flex items-center justify-center gap-4"
                aria-label="Search results pagination"
            >
                <Link
                    v-if="pagination.current_page > 1"
                    :href="pageHref(pagination.current_page - 1)"
                    class="text-sm font-medium text-foreground hover:underline"
                >
                    Previous
                </Link>
                <span class="text-sm text-muted-foreground">
                    Page {{ pagination.current_page }} of
                    {{ pagination.last_page }}
                </span>
                <Link
                    v-if="pagination.current_page < pagination.last_page"
                    :href="pageHref(pagination.current_page + 1)"
                    class="text-sm font-medium text-foreground hover:underline"
                >
                    Next
                </Link>
            </nav>
        </template>
    </section>
</template>
