<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StoryTagForm from '@/components/admin/StoryTagForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/story-tags';

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Story Tags', href: index() },
            { title: 'New Tag', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Story Tag" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Story Tag</h1>

        <StoryTagForm
            class="mt-6"
            :form-target="store.form()"
            submit-label="Create Tag"
        />
    </div>
</template>
