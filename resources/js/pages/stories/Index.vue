<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen } from '@lucide/vue';
import Seo from '@/components/Seo.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Story = {
    title: string;
    slug: string;
    excerpt: string | null;
    story_type: string;
    featured: boolean;
    program: { name: string; slug: string } | null;
    published_at: string | null;
};

type PaginatedStories = {
    data: Story[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    stories: PaginatedStories;
}>();

// Laravel's paginator labels use HTML entities ("&laquo; Previous") meant
// for v-html — decoded here instead so <Link> (a Vue component, not a raw
// element) never needs v-html, which vue/no-v-text-v-html-on-component
// disallows on components.
function decodeLabel(label: string): string {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
}

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
        title="Stories & News"
        description="Stories of change from the children, families, and communities Saba International supports in East Africa."
    />

    <section class="border-b border-border bg-primary text-primary-foreground">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                Stories & News
            </h1>
            <p class="mt-4 text-primary-foreground/90">
                Verified, consented stories from the young people, families, and
                communities we work alongside.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="sr-only">All Stories</h2>
        <div v-if="stories.data.length === 0" class="text-center">
            <BookOpen
                class="mx-auto size-10 text-muted-foreground"
                aria-hidden="true"
            />
            <p class="mt-4 text-muted-foreground">
                We don't have any published stories yet. Check back soon — every
                story we publish here is verified and shared with consent.
            </p>
        </div>

        <div v-else class="grid gap-6 sm:grid-cols-2">
            <Link
                v-for="story in stories.data"
                :key="story.slug"
                :href="`/stories/${story.slug}`"
                class="block"
            >
                <Card class="h-full transition-shadow hover:shadow-md">
                    <CardHeader>
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle>{{ story.title }}</CardTitle>
                            <Badge variant="secondary">
                                {{
                                    storyTypeLabels[story.story_type] ??
                                    story.story_type
                                }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p
                            v-if="story.excerpt"
                            class="text-sm text-muted-foreground"
                        >
                            {{ story.excerpt }}
                        </p>
                        <p
                            v-if="story.program"
                            class="mt-3 text-xs font-medium text-primary"
                        >
                            {{ story.program.name }}
                        </p>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <nav
            v-if="stories.links.length > 3"
            class="mt-10 flex flex-wrap justify-center gap-2"
            aria-label="Stories pagination"
        >
            <Link
                v-for="(link, index) in stories.links"
                :key="index"
                :href="link.url ?? '#'"
                :class="[
                    'flex min-h-11 min-w-11 items-center justify-center rounded-md px-3 text-sm',
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                    !link.url && 'pointer-events-none opacity-40',
                ]"
                :aria-current="link.active ? 'page' : undefined"
            >
                {{ decodeLabel(link.label) }}
            </Link>
        </nav>
    </section>
</template>
