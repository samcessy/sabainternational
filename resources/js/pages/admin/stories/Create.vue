<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StoryForm from '@/components/admin/StoryForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/stories';

type Option = { value: string; label: string };
type ProgramOption = { id: number; name: string };
type TagOption = { id: number; name: string };

defineProps<{
    programOptions: ProgramOption[];
    tagOptions: TagOption[];
    storyTypeOptions: Option[];
    consentStatusOptions: Option[];
    imageConsentOptions: Option[];
    sensitiveContentOptions: Option[];
    approvalStageOptions: Option[];
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Stories', href: index() },
            { title: 'New Story', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Story" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Story</h1>

        <StoryForm
            class="mt-6"
            :form-target="store.form()"
            :program-options="programOptions"
            :tag-options="tagOptions"
            :story-type-options="storyTypeOptions"
            :consent-status-options="consentStatusOptions"
            :image-consent-options="imageConsentOptions"
            :sensitive-content-options="sensitiveContentOptions"
            :approval-stage-options="approvalStageOptions"
            :status-options="statusOptions"
            submit-label="Create Story"
        />
    </div>
</template>
