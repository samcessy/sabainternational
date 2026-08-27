<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

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
    documents: Document[];
}>();

const groups = computed(() => {
    const byType = new Map<string, Document[]>();

    for (const document of props.documents) {
        const existing = byType.get(document.document_type_label) ?? [];
        existing.push(document);
        byType.set(document.document_type_label, existing);
    }

    return Array.from(byType.entries()).map(([label, items]) => ({
        label,
        items,
    }));
});
</script>

<template>
    <Head title="Transparency Center" />

    <section class="border-b border-border bg-primary text-primary-foreground">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                Transparency Center
            </h1>
            <p class="mt-4 text-primary-foreground/90">
                Annual reports, financial statements, and governance documents.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <div
            v-if="documents.length === 0"
            class="text-center text-muted-foreground"
        >
            No documents have been published yet.
        </div>

        <div v-else class="space-y-12">
            <div v-for="group in groups" :key="group.label">
                <h2 class="text-xl font-bold text-foreground">
                    {{ group.label }}
                </h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <Link
                        v-for="document in group.items"
                        :key="document.id"
                        :href="`/documents/${document.id}`"
                        class="block"
                    >
                        <Card class="h-full transition-shadow hover:shadow-md">
                            <CardHeader>
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <CardTitle>{{ document.title }}</CardTitle>
                                    <span
                                        v-if="document.year"
                                        class="shrink-0 text-sm text-muted-foreground"
                                    >
                                        {{ document.year }}
                                    </span>
                                </div>
                            </CardHeader>
                            <CardContent v-if="document.summary">
                                <p class="text-sm text-muted-foreground">
                                    {{ document.summary }}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
