<script setup lang="ts">
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

defineProps<{
    page: Page;
}>();
</script>

<template>
    <Seo
        :title="page.seo.title ?? page.title"
        :description="page.seo.description"
        :image="page.seo.og_image"
    />

    <article class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
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
