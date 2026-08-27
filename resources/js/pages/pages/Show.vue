<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import Seo from '@/components/Seo.vue';

type Page = {
    title: string;
    slug: string;
    body: string | null;
    seo: {
        title: string | null;
        description: string | null;
        og_image: string | null;
    };
    published_at: string | null;
};

const props = defineProps<{
    page: Page;
}>();

const inertiaPage = usePage<{ url: string }>();
const origin = computed(() => new URL(inertiaPage.props.url).origin);

const breadcrumbItems = computed(() => [
    { title: 'Home', href: '/' },
    { title: props.page.title, href: '' },
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
                ? inertiaPage.props.url
                : new URL(item.href, origin.value).href,
    })),
}));
</script>

<template>
    <Seo
        :title="page.seo.title ?? page.title"
        :description="page.seo.description"
        :image="page.seo.og_image"
        :schema="schema"
    />

    <article class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <Breadcrumbs :breadcrumbs="breadcrumbItems" class="mb-6" />

        <h1 class="text-4xl font-bold tracking-tight text-foreground">
            {{ page.title }}
        </h1>
        <div
            v-if="page.body"
            class="mt-8 space-y-4 whitespace-pre-line text-muted-foreground"
        >
            {{ page.body }}
        </div>
    </article>
</template>
