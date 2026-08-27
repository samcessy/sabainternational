<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Download } from '@lucide/vue';
import { computed } from 'vue';
import Seo from '@/components/Seo.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type Document = {
    id: number;
    title: string;
    document_type: string;
    document_type_label: string;
    year: number | null;
    summary: string | null;
    file_url: string;
    cover_image_url: string | null;
    published_at: string | null;
};

const props = defineProps<{
    document: Document;
}>();

const page = usePage<{ url: string }>();

const schema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
        {
            '@type': 'ListItem',
            position: 1,
            name: 'Home',
            item: new URL('/', page.props.url).href,
        },
        {
            '@type': 'ListItem',
            position: 2,
            name: 'Transparency Center',
            item: new URL('/documents', page.props.url).href,
        },
        {
            '@type': 'ListItem',
            position: 3,
            name: props.document.title,
            item: page.props.url,
        },
    ],
}));
</script>

<template>
    <Seo
        :title="document.title"
        :description="document.summary"
        :image="document.cover_image_url"
        :schema="schema"
    />

    <article>
        <section
            class="border-b border-border bg-primary text-primary-foreground"
        >
            <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center gap-2">
                    <Badge variant="secondary">
                        {{ document.document_type_label }}
                    </Badge>
                    <span
                        v-if="document.year"
                        class="text-sm text-primary-foreground/90"
                    >
                        {{ document.year }}
                    </span>
                </div>
                <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">
                    {{ document.title }}
                </h1>
            </div>
        </section>

        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <p v-if="document.summary" class="text-lg text-foreground">
                {{ document.summary }}
            </p>

            <div class="mt-10 flex flex-wrap gap-3">
                <Button as-child variant="cta" size="lg">
                    <a :href="document.file_url" target="_blank" rel="noopener">
                        <Download class="size-4" aria-hidden="true" />
                        Download
                    </a>
                </Button>
                <Button as-child variant="outline" size="lg">
                    <Link href="/documents">All Documents</Link>
                </Button>
            </div>
        </div>
    </article>
</template>
