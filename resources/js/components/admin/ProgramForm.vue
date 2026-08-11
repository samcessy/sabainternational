<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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

type ProgramValues = {
    name?: string;
    legal_name?: string | null;
    slug?: string;
    category?: string;
    relationship_type?: string;
    external_url?: string | null;
    founded_year?: number | null;
    location?: string | null;
    short_description?: string | null;
    overview?: string | null;
    what_happens_here?: string | null;
    sensitive_content_classification?: string;
    seo_title?: string | null;
    seo_description?: string | null;
    og_image?: string | null;
    status?: string;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        program?: ProgramValues | null;
        categoryOptions: Option[];
        relationshipTypeOptions: Option[];
        sensitiveContentOptions: Option[];
        statusOptions: Option[];
        submitLabel: string;
    }>(),
    { program: null },
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
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="program?.name"
                    required
                    :aria-invalid="!!errors.name"
                    :aria-describedby="errors.name ? 'name-error' : undefined"
                />
                <InputError id="name-error" :message="errors.name" />
            </div>

            <div class="space-y-2">
                <Label for="legal_name">Legal Name (optional)</Label>
                <Input
                    id="legal_name"
                    name="legal_name"
                    :default-value="program?.legal_name ?? undefined"
                    :aria-invalid="!!errors.legal_name"
                    :aria-describedby="
                        errors.legal_name ? 'legal_name-error' : undefined
                    "
                />
                <InputError
                    id="legal_name-error"
                    :message="errors.legal_name"
                />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="slug">Slug</Label>
            <Input
                id="slug"
                name="slug"
                :default-value="program?.slug"
                required
                :aria-invalid="!!errors.slug"
                :aria-describedby="errors.slug ? 'slug-error' : undefined"
            />
            <InputError id="slug-error" :message="errors.slug" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="category">Category</Label>
                <Select name="category" :default-value="program?.category">
                    <SelectTrigger
                        id="category"
                        class="w-full"
                        :aria-invalid="!!errors.category"
                        :aria-describedby="
                            errors.category ? 'category-error' : undefined
                        "
                    >
                        <SelectValue placeholder="Choose a category" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in categoryOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError id="category-error" :message="errors.category" />
            </div>

            <div class="space-y-2">
                <Label for="relationship_type">Relationship</Label>
                <Select
                    name="relationship_type"
                    :default-value="program?.relationship_type"
                >
                    <SelectTrigger
                        id="relationship_type"
                        class="w-full"
                        :aria-invalid="!!errors.relationship_type"
                        :aria-describedby="
                            errors.relationship_type
                                ? 'relationship_type-error'
                                : undefined
                        "
                    >
                        <SelectValue placeholder="Choose a relationship type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in relationshipTypeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError
                    id="relationship_type-error"
                    :message="errors.relationship_type"
                />
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="founded_year">Founded Year (optional)</Label>
                <Input
                    id="founded_year"
                    name="founded_year"
                    type="number"
                    :default-value="program?.founded_year ?? undefined"
                    :aria-invalid="!!errors.founded_year"
                    :aria-describedby="
                        errors.founded_year ? 'founded_year-error' : undefined
                    "
                />
                <InputError
                    id="founded_year-error"
                    :message="errors.founded_year"
                />
            </div>

            <div class="space-y-2">
                <Label for="location">Location (optional)</Label>
                <Input
                    id="location"
                    name="location"
                    :default-value="program?.location ?? undefined"
                    :aria-invalid="!!errors.location"
                    :aria-describedby="
                        errors.location ? 'location-error' : undefined
                    "
                />
                <InputError id="location-error" :message="errors.location" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="external_url">External URL (optional)</Label>
            <Input
                id="external_url"
                name="external_url"
                type="url"
                :default-value="program?.external_url ?? undefined"
                :aria-invalid="!!errors.external_url"
                :aria-describedby="
                    errors.external_url ? 'external_url-error' : undefined
                "
            />
            <InputError
                id="external_url-error"
                :message="errors.external_url"
            />
        </div>

        <div class="space-y-2">
            <Label for="short_description">Short Description (optional)</Label>
            <Textarea
                id="short_description"
                name="short_description"
                rows="3"
                :default-value="program?.short_description ?? undefined"
                :aria-invalid="!!errors.short_description"
                :aria-describedby="
                    errors.short_description
                        ? 'short_description-error'
                        : undefined
                "
            />
            <InputError
                id="short_description-error"
                :message="errors.short_description"
            />
        </div>

        <div class="space-y-2">
            <Label for="overview">Overview (optional)</Label>
            <Textarea
                id="overview"
                name="overview"
                rows="5"
                :default-value="program?.overview ?? undefined"
                :aria-invalid="!!errors.overview"
                :aria-describedby="
                    errors.overview ? 'overview-error' : undefined
                "
            />
            <InputError id="overview-error" :message="errors.overview" />
        </div>

        <div class="space-y-2">
            <Label for="what_happens_here">What Happens Here (optional)</Label>
            <Textarea
                id="what_happens_here"
                name="what_happens_here"
                rows="5"
                :default-value="program?.what_happens_here ?? undefined"
                :aria-invalid="!!errors.what_happens_here"
                :aria-describedby="
                    errors.what_happens_here
                        ? 'what_happens_here-error'
                        : undefined
                "
            />
            <InputError
                id="what_happens_here-error"
                :message="errors.what_happens_here"
            />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="sensitive_content_classification">
                    Sensitive Content
                </Label>
                <Select
                    name="sensitive_content_classification"
                    :default-value="program?.sensitive_content_classification"
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

            <div class="space-y-2">
                <Label for="status">Status</Label>
                <Select name="status" :default-value="program?.status">
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

        <fieldset class="space-y-5 rounded-lg border border-border p-4">
            <legend class="px-1 text-sm font-medium text-foreground">
                SEO (optional)
            </legend>

            <div class="space-y-2">
                <Label for="seo_title">SEO Title</Label>
                <Input
                    id="seo_title"
                    name="seo_title"
                    :default-value="program?.seo_title ?? undefined"
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
                    :default-value="program?.seo_description ?? undefined"
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
                    :default-value="program?.og_image ?? undefined"
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
