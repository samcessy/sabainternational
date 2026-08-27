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

type PageValues = {
    title?: string;
    slug?: string;
    body?: string | null;
    seo_title?: string | null;
    seo_description?: string | null;
    og_image?: string | null;
    status?: string;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        page?: PageValues | null;
        statusOptions: Option[];
        submitLabel: string;
    }>(),
    { page: null },
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
                    :default-value="page?.title"
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
                    :default-value="page?.slug"
                    required
                    :aria-invalid="!!errors.slug"
                    :aria-describedby="errors.slug ? 'slug-error' : undefined"
                />
                <InputError id="slug-error" :message="errors.slug" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="body">Body (optional)</Label>
            <Textarea
                id="body"
                name="body"
                rows="10"
                :default-value="page?.body ?? undefined"
                :aria-invalid="!!errors.body"
                :aria-describedby="errors.body ? 'body-error' : undefined"
            />
            <InputError id="body-error" :message="errors.body" />
        </div>

        <div class="space-y-2">
            <Label for="seo_title">SEO Title (optional)</Label>
            <Input
                id="seo_title"
                name="seo_title"
                :default-value="page?.seo_title ?? undefined"
                :aria-invalid="!!errors.seo_title"
                :aria-describedby="
                    errors.seo_title ? 'seo_title-error' : undefined
                "
            />
            <InputError id="seo_title-error" :message="errors.seo_title" />
        </div>

        <div class="space-y-2">
            <Label for="seo_description">SEO Description (optional)</Label>
            <Textarea
                id="seo_description"
                name="seo_description"
                rows="3"
                :default-value="page?.seo_description ?? undefined"
                :aria-invalid="!!errors.seo_description"
                :aria-describedby="
                    errors.seo_description ? 'seo_description-error' : undefined
                "
            />
            <InputError
                id="seo_description-error"
                :message="errors.seo_description"
            />
        </div>

        <div class="space-y-2">
            <Label for="og_image">OG Image URL (optional)</Label>
            <Input
                id="og_image"
                name="og_image"
                :default-value="page?.og_image ?? undefined"
                :aria-invalid="!!errors.og_image"
                :aria-describedby="
                    errors.og_image ? 'og_image-error' : undefined
                "
            />
            <InputError id="og_image-error" :message="errors.og_image" />
        </div>

        <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select name="status" :default-value="page?.status">
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

        <Button type="submit" variant="cta" size="lg" :disabled="processing">
            <Spinner v-if="processing" />
            {{ submitLabel }}
        </Button>
    </Form>
</template>
