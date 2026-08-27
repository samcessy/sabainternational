<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Seo from '@/components/Seo.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type Story = {
    title: string;
    slug: string;
    excerpt: string | null;
    body: string;
    story_type: string;
    location: string | null;
    featured: boolean;
    program: { name: string; slug: string } | null;
    seo: {
        title: string | null;
        description: string | null;
        og_image: string | null;
    };
    published_at: string | null;
};

const props = defineProps<{
    story: Story;
}>();

const page = usePage<{ url: string }>();

const schema = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: props.story.title,
        description: props.story.seo.description ?? props.story.excerpt,
        image: props.story.seo.og_image
            ? new URL(props.story.seo.og_image, page.props.url).href
            : undefined,
        datePublished: props.story.published_at,
        publisher: { '@type': 'NGO', name: 'Saba International' },
    },
    {
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
                name: 'Stories',
                item: new URL('/stories', page.props.url).href,
            },
            {
                '@type': 'ListItem',
                position: 3,
                name: props.story.title,
                item: page.props.url,
            },
        ],
    },
]);

const storyTypeLabels: Record<string, string> = {
    story_of_change: 'Story of Change',
    program_update: 'Program Update',
    news: 'News',
    volunteer_story: 'Volunteer Story',
    donor_story: 'Donor Story',
    partner_story: 'Partner Story',
    founder_story: "Founder's Story",
    youth_story: 'Youth Story',
    community_story: 'Community Story',
};
</script>

<template>
    <Seo
        :title="story.seo.title ?? story.title"
        :description="story.seo.description ?? story.excerpt"
        :image="story.seo.og_image"
        type="article"
        :schema="schema"
    />

    <article>
        <section
            class="border-b border-border bg-primary text-primary-foreground"
        >
            <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center gap-2">
                    <Badge variant="secondary">
                        {{
                            storyTypeLabels[story.story_type] ??
                            story.story_type
                        }}
                    </Badge>
                    <Link
                        v-if="story.program"
                        :href="`/programs/${story.program.slug}`"
                        class="text-sm font-medium text-primary-foreground/90 underline-offset-4 hover:underline"
                    >
                        {{ story.program.name }}
                    </Link>
                </div>
                <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">
                    {{ story.title }}
                </h1>
                <p
                    v-if="story.location"
                    class="mt-3 text-primary-foreground/90"
                >
                    {{ story.location }}
                </p>
            </div>
        </section>

        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <p v-if="story.excerpt" class="text-lg text-foreground">
                {{ story.excerpt }}
            </p>

            <div
                class="mt-8 space-y-4 whitespace-pre-line text-muted-foreground"
            >
                {{ story.body }}
            </div>

            <div class="mt-10 flex flex-wrap gap-3">
                <Button as-child variant="cta" size="lg">
                    <Link href="/give">Support This Work</Link>
                </Button>
                <Button as-child variant="outline" size="lg">
                    <Link href="/stories">More Stories</Link>
                </Button>
            </div>
        </div>
    </article>
</template>
