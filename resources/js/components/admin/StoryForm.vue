<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

type Option = { value: string; label: string };
type ProgramOption = { id: number; name: string };

type StoryValues = {
    title?: string;
    slug?: string;
    excerpt?: string | null;
    body?: string | null;
    program_id?: number | null;
    story_type?: string;
    location?: string | null;
    consent_status?: string | null;
    image_consent?: string | null;
    guardian_consent?: string | null;
    anonymity_requested?: boolean;
    sensitive_content_classification?: string;
    approval_stage?: string;
    attribution?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
    og_image?: string | null;
    status?: string;
    featured?: boolean;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        story?: StoryValues | null;
        programOptions: ProgramOption[];
        storyTypeOptions: Option[];
        consentStatusOptions: Option[];
        imageConsentOptions: Option[];
        sensitiveContentOptions: Option[];
        approvalStageOptions: Option[];
        statusOptions: Option[];
        submitLabel: string;
    }>(),
    { story: null },
);
</script>

<template>
    <Form
        v-bind="formTarget"
        v-slot="{ errors, processing }"
        class="max-w-2xl space-y-6"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="title">Title</Label>
                <Input
                    id="title"
                    name="title"
                    :default-value="story?.title"
                    required
                    :aria-invalid="!!errors.title"
                    :aria-describedby="errors.title ? 'title-error' : undefined"
                />
                <InputError id="title-error" :message="errors.title" />
            </div>

            <div class="space-y-2">
                <Label for="slug">Slug</Label>
                <Input
                    id="slug"
                    name="slug"
                    :default-value="story?.slug"
                    required
                    :aria-invalid="!!errors.slug"
                    :aria-describedby="errors.slug ? 'slug-error' : undefined"
                />
                <InputError id="slug-error" :message="errors.slug" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="excerpt">Excerpt (optional)</Label>
            <Textarea
                id="excerpt"
                name="excerpt"
                rows="2"
                :default-value="story?.excerpt ?? undefined"
                :aria-invalid="!!errors.excerpt"
                :aria-describedby="errors.excerpt ? 'excerpt-error' : undefined"
            />
            <InputError id="excerpt-error" :message="errors.excerpt" />
        </div>

        <div class="space-y-2">
            <Label for="body">Body (optional)</Label>
            <Textarea
                id="body"
                name="body"
                rows="8"
                :default-value="story?.body ?? undefined"
                :aria-invalid="!!errors.body"
                :aria-describedby="errors.body ? 'body-error' : undefined"
            />
            <InputError id="body-error" :message="errors.body" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="story_type">Story Type</Label>
                <Select name="story_type" :default-value="story?.story_type">
                    <SelectTrigger
                        id="story_type"
                        class="w-full"
                        :aria-invalid="!!errors.story_type"
                        :aria-describedby="
                            errors.story_type ? 'story_type-error' : undefined
                        "
                    >
                        <SelectValue placeholder="Choose a story type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in storyTypeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError
                    id="story_type-error"
                    :message="errors.story_type"
                />
            </div>

            <div class="space-y-2">
                <Label for="program_id">Program (optional)</Label>
                <Select
                    name="program_id"
                    :default-value="
                        story?.program_id ? String(story.program_id) : undefined
                    "
                >
                    <SelectTrigger
                        id="program_id"
                        class="w-full"
                        :aria-invalid="!!errors.program_id"
                        :aria-describedby="
                            errors.program_id ? 'program_id-error' : undefined
                        "
                    >
                        <SelectValue placeholder="None" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in programOptions"
                            :key="option.id"
                            :value="String(option.id)"
                        >
                            {{ option.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError
                    id="program_id-error"
                    :message="errors.program_id"
                />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="location">Location (optional)</Label>
            <Input
                id="location"
                name="location"
                :default-value="story?.location ?? undefined"
                :aria-invalid="!!errors.location"
                :aria-describedby="
                    errors.location ? 'location-error' : undefined
                "
            />
            <InputError id="location-error" :message="errors.location" />
        </div>

        <fieldset class="space-y-5 rounded-lg border border-border p-4">
            <legend class="px-1 text-sm font-medium text-foreground">
                Consent & Governance
            </legend>
            <p class="text-sm text-muted-foreground">
                A story cannot be published without a recorded consent status -
                saba.md §7.3.
            </p>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="consent_status">Consent Status</Label>
                    <Select
                        name="consent_status"
                        :default-value="story?.consent_status ?? undefined"
                    >
                        <SelectTrigger
                            id="consent_status"
                            class="w-full"
                            :aria-invalid="!!errors.consent_status"
                            :aria-describedby="
                                errors.consent_status
                                    ? 'consent_status-error'
                                    : undefined
                            "
                        >
                            <SelectValue
                                placeholder="Choose a consent status"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in consentStatusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        id="consent_status-error"
                        :message="errors.consent_status"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="image_consent">Image Consent (optional)</Label>
                    <Select
                        name="image_consent"
                        :default-value="story?.image_consent ?? undefined"
                    >
                        <SelectTrigger
                            id="image_consent"
                            class="w-full"
                            :aria-invalid="!!errors.image_consent"
                            :aria-describedby="
                                errors.image_consent
                                    ? 'image_consent-error'
                                    : undefined
                            "
                        >
                            <SelectValue placeholder="Not applicable" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in imageConsentOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        id="image_consent-error"
                        :message="errors.image_consent"
                    />
                </div>
            </div>

            <div class="space-y-2">
                <Label for="guardian_consent">
                    Guardian Consent Details (optional)
                </Label>
                <Input
                    id="guardian_consent"
                    name="guardian_consent"
                    :default-value="story?.guardian_consent ?? undefined"
                    :aria-invalid="!!errors.guardian_consent"
                    :aria-describedby="
                        errors.guardian_consent
                            ? 'guardian_consent-error'
                            : undefined
                    "
                />
                <InputError
                    id="guardian_consent-error"
                    :message="errors.guardian_consent"
                />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox
                    id="anonymity_requested"
                    name="anonymity_requested"
                    :default-value="story?.anonymity_requested ?? false"
                />
                <Label for="anonymity_requested" class="font-normal">
                    Anonymity requested
                </Label>
            </div>

            <div class="space-y-2">
                <Label for="sensitive_content_classification">
                    Sensitive Content
                </Label>
                <Select
                    name="sensitive_content_classification"
                    :default-value="story?.sensitive_content_classification"
                >
                    <SelectTrigger
                        id="sensitive_content_classification"
                        class="w-full"
                        :aria-invalid="
                            !!errors.sensitive_content_classification
                        "
                        :aria-describedby="
                            errors.sensitive_content_classification
                                ? 'sensitive_content_classification-error'
                                : undefined
                        "
                    >
                        <SelectValue placeholder="Choose a classification" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in sensitiveContentOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError
                    id="sensitive_content_classification-error"
                    :message="errors.sensitive_content_classification"
                />
            </div>
        </fieldset>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="approval_stage">Approval Stage</Label>
                <Select
                    name="approval_stage"
                    :default-value="story?.approval_stage"
                >
                    <SelectTrigger
                        id="approval_stage"
                        class="w-full"
                        :aria-invalid="!!errors.approval_stage"
                        :aria-describedby="
                            errors.approval_stage
                                ? 'approval_stage-error'
                                : undefined
                        "
                    >
                        <SelectValue placeholder="Choose a stage" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in approvalStageOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError
                    id="approval_stage-error"
                    :message="errors.approval_stage"
                />
            </div>

            <div class="space-y-2">
                <Label for="status">Status</Label>
                <Select name="status" :default-value="story?.status">
                    <SelectTrigger
                        id="status"
                        class="w-full"
                        :aria-invalid="!!errors.status"
                        :aria-describedby="
                            errors.status ? 'status-error' : undefined
                        "
                    >
                        <SelectValue placeholder="Choose a status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in statusOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError id="status-error" :message="errors.status" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="attribution">Attribution (optional)</Label>
            <Input
                id="attribution"
                name="attribution"
                :default-value="story?.attribution ?? undefined"
                :aria-invalid="!!errors.attribution"
                :aria-describedby="
                    errors.attribution ? 'attribution-error' : undefined
                "
            />
            <InputError id="attribution-error" :message="errors.attribution" />
        </div>

        <div class="flex items-center gap-2">
            <Checkbox
                id="featured"
                name="featured"
                :default-value="story?.featured ?? false"
            />
            <Label for="featured" class="font-normal">
                Feature this story
            </Label>
        </div>

        <fieldset class="space-y-5 rounded-lg border border-border p-4">
            <legend class="px-1 text-sm font-medium text-foreground">
                SEO (optional)
            </legend>

            <div class="space-y-2">
                <Label for="seo_title">SEO Title</Label>
                <Input
                    id="seo_title"
                    name="seo_title"
                    :default-value="story?.seo_title ?? undefined"
                    :aria-invalid="!!errors.seo_title"
                    :aria-describedby="
                        errors.seo_title ? 'seo_title-error' : undefined
                    "
                />
                <InputError id="seo_title-error" :message="errors.seo_title" />
            </div>

            <div class="space-y-2">
                <Label for="seo_description">SEO Description</Label>
                <Textarea
                    id="seo_description"
                    name="seo_description"
                    rows="2"
                    :default-value="story?.seo_description ?? undefined"
                    :aria-invalid="!!errors.seo_description"
                    :aria-describedby="
                        errors.seo_description
                            ? 'seo_description-error'
                            : undefined
                    "
                />
                <InputError
                    id="seo_description-error"
                    :message="errors.seo_description"
                />
            </div>

            <div class="space-y-2">
                <Label for="og_image">Social Share Image URL</Label>
                <Input
                    id="og_image"
                    name="og_image"
                    :default-value="story?.og_image ?? undefined"
                    :aria-invalid="!!errors.og_image"
                    :aria-describedby="
                        errors.og_image ? 'og_image-error' : undefined
                    "
                />
                <InputError id="og_image-error" :message="errors.og_image" />
            </div>
        </fieldset>

        <Button type="submit" variant="cta" size="lg" :disabled="processing">
            <Spinner v-if="processing" />
            {{ submitLabel }}
        </Button>
    </Form>
</template>
