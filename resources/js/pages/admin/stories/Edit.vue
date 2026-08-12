<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StoryForm from '@/components/admin/StoryForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/stories';

type Option = { value: string; label: string };
type ProgramOption = { id: number; name: string };

type Story = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    body: string | null;
    program_id: number | null;
    story_type: string;
    location: string | null;
    consent_status: string | null;
    image_consent: string | null;
    guardian_consent: string | null;
    anonymity_requested: boolean;
    sensitive_content_classification: string;
    approval_stage: string;
    attribution: string | null;
    seo_title: string | null;
    seo_description: string | null;
    og_image: string | null;
    status: string;
    featured: boolean;
};

const props = defineProps<{
    story: Story;
    programOptions: ProgramOption[];
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
            { title: 'Edit Story', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${story.title}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ story.title }}
        </h1>

        <StoryForm
            class="mt-6"
            :form-target="update.form(props.story.id)"
            :story="story"
            :program-options="programOptions"
            :story-type-options="storyTypeOptions"
            :consent-status-options="consentStatusOptions"
            :image-consent-options="imageConsentOptions"
            :sensitive-content-options="sensitiveContentOptions"
            :approval-stage-options="approvalStageOptions"
            :status-options="statusOptions"
            submit-label="Save Changes"
        />
    </div>
</template>
