<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// saba.md §15.1 — the technical SEO fields every indexable page should
// carry. Content-backed pages (Story/Program/Page) pass their own
// seo_title/seo_description/og_image straight through; static marketing
// pages (Home/About/...) pass hand-written copy. Document/Event have no
// dedicated SEO columns, so their callers fall back to title + summary.
//
// `schema` carries saba.md §15.3's page-specific JSON-LD (Article, Event,
// Person, BreadcrumbList, WebSite, DonateAction) — one object or several.
// The sitewide Organization block below is emitted unconditionally since
// every page should identify the publishing org, not just some of them.
const props = withDefaults(
    defineProps<{
        title: string;
        description?: string | null;
        image?: string | null;
        canonical?: string | null;
        noindex?: boolean;
        type?: 'website' | 'article';
        schema?: Record<string, unknown> | Record<string, unknown>[] | null;
    }>(),
    {
        description: null,
        image: null,
        canonical: null,
        noindex: false,
        type: 'website',
        schema: null,
    },
);

const page = usePage<{ url: string; name: string }>();
const canonicalUrl = computed(() => props.canonical ?? page.props.url);

const organizationSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'NGO',
    name: 'Saba International',
    url: new URL('/', page.props.url).href,
    logo: new URL('/apple-touch-icon.png', page.props.url).href,
    description:
        'Saba International supports education, nutrition, and shelter for underprivileged youth and their families in East Africa.',
}));

const schemaBlocks = computed(() => {
    if (!props.schema) {
        return [];
    }

    return Array.isArray(props.schema) ? props.schema : [props.schema];
});

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

// JSON.stringify doesn't escape the angle bracket, so a title or
// description containing a closing script tag as literal text would
// otherwise terminate this element early — browsers scan element content
// for that literal byte sequence, not by parsing it as JS/JSON. Escaping
// every angle bracket to its unicode codepoint (the same mitigation
// Rails' json_escape uses) rules that out while staying valid,
// semantically-identical JSON.
function toJsonLd(value: unknown): string {
    return JSON.stringify(value).replace(/</g, '\\u003c');
}
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

        <script key="schema:organization" type="application/ld+json">
            {{ toJsonLd(organizationSchema) }}
        </script>
        <script
            v-for="(block, index) in schemaBlocks"
            :key="`schema:${index}`"
            type="application/ld+json"
        >
            {{ toJsonLd(block) }}
        </script>
    </Head>
</template>
