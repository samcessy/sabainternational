<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StoryTagForm from '@/components/admin/StoryTagForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/story-tags';

type StoryTag = {
    id: number;
    name: string;
    slug: string;
};

const props = defineProps<{
    storyTag: StoryTag;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Story Tags', href: index() },
            { title: 'Edit Tag', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${storyTag.name}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ storyTag.name }}
        </h1>

        <StoryTagForm
            class="mt-6"
            :form-target="update.form(props.storyTag.id)"
            :story-tag="storyTag"
            submit-label="Save Changes"
        />
    </div>
</template>
