<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// saba.md §15.1 — the technical SEO fields every indexable page should
// carry. Content-backed pages (Story/Program/Page) pass their own
// seo_title/seo_description/og_image straight through; static marketing
// pages (Home/About/...) pass hand-written copy. Document/Event have no
// dedicated SEO columns, so their callers fall back to title + summary.
const props = withDefaults(
    defineProps<{
        title: string;
        description?: string | null;
        image?: string | null;
        canonical?: string | null;
        noindex?: boolean;
        type?: 'website' | 'article';
    }>(),
    {
        description: null,
        image: null,
        canonical: null,
        noindex: false,
        type: 'website',
    },
);

const page = usePage<{ url: string }>();
const canonicalUrl = computed(() => props.canonical ?? page.props.url);

// og:image/twitter:image must be absolute for crawlers that don't resolve
// relative URLs against the page they scraped it from.
const absoluteImage = computed(() => {
    if (!props.image) {
        return null;
    }

    return props.image.startsWith('http')
        ? props.image
        : new URL(props.image, page.props.url).href;
});
</script>

<template>
    <Head :title="title">
        <meta
            v-if="description"
            key="description"
            name="description"
            :content="description"
        />
        <link key="canonical" rel="canonical" :href="canonicalUrl" />
        <meta
            key="robots"
            name="robots"
            :content="noindex ? 'noindex, follow' : 'index, follow'"
        />

        <meta key="og:title" property="og:title" :content="title" />
        <meta key="og:type" property="og:type" :content="type" />
        <meta key="og:url" property="og:url" :content="canonicalUrl" />
        <meta
            v-if="description"
            key="og:description"
            property="og:description"
            :content="description"
        />
        <meta
            v-if="absoluteImage"
            key="og:image"
            property="og:image"
            :content="absoluteImage"
        />

        <meta
            key="twitter:card"
            name="twitter:card"
            :content="absoluteImage ? 'summary_large_image' : 'summary'"
        />
        <meta key="twitter:title" name="twitter:title" :content="title" />
        <meta
            v-if="description"
            key="twitter:description"
            name="twitter:description"
            :content="description"
        />
        <meta
            v-if="absoluteImage"
            key="twitter:image"
            name="twitter:image"
            :content="absoluteImage"
        />
    </Head>
</template>
