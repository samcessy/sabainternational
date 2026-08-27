<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Seo from '@/components/Seo.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Program = {
    name: string;
    slug: string;
    category: string;
    short_description: string;
};

defineProps<{
    programs: Program[];
}>();

const categoryLabels: Record<string, string> = {
    education: 'Education',
    nutrition: 'Nutrition',
    shelter_family_support: 'Shelter & Family Support',
    youth_economic_empowerment: 'Youth Economic Empowerment',
};
</script>

<template>
    <Seo
        title="Our Programs"
        description="Four partner programs addressing education, nutrition, shelter, and youth economic empowerment in East Africa."
    />

    <section class="border-b border-border bg-primary text-primary-foreground">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                Our Programs
            </h1>
            <p class="mt-4 text-primary-foreground/90">
                Four partner programs, each addressing a different part of what
                a child and family need to thrive.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="sr-only">All Programs</h2>
        <div class="grid gap-6 sm:grid-cols-2">
            <Link
                v-for="program in programs"
                :key="program.slug"
                :href="`/programs/${program.slug}`"
                class="block"
            >
                <Card class="h-full transition-shadow hover:shadow-md">
                    <CardHeader>
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle>{{ program.name }}</CardTitle>
                            <span
                                class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                            >
                                {{
                                    categoryLabels[program.category] ??
                                    program.category
                                }}
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            {{ program.short_description }}
                        </p>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </section>
</template>
