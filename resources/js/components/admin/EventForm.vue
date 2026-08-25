<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import MediaPicker from '@/components/admin/MediaPicker.vue';
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

type EventValues = {
    title?: string;
    slug?: string;
    description?: string | null;
    start_at?: string;
    end_at?: string | null;
    location?: string | null;
    featured_image_media_id?: number | null;
    featured_image_thumbnail_url?: string | null;
    status?: string;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        event?: EventValues | null;
        statusOptions: Option[];
        submitLabel: string;
    }>(),
    { event: null },
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
                    :default-value="event?.title"
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
                    :default-value="event?.slug"
                    required
                    :aria-invalid="!!errors.slug"
                    :aria-describedby="errors.slug ? 'slug-error' : undefined"
                />
                <InputError id="slug-error" :message="errors.slug" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="description">Description (optional)</Label>
            <Textarea
                id="description"
                name="description"
                rows="4"
                :default-value="event?.description ?? undefined"
                :aria-invalid="!!errors.description"
                :aria-describedby="
                    errors.description ? 'description-error' : undefined
                "
            />
            <InputError id="description-error" :message="errors.description" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="start_at">Starts</Label>
                <Input
                    id="start_at"
                    name="start_at"
                    type="datetime-local"
                    :default-value="event?.start_at"
                    required
                    :aria-invalid="!!errors.start_at"
                    :aria-describedby="
                        errors.start_at ? 'start_at-error' : undefined
                    "
                />
                <InputError id="start_at-error" :message="errors.start_at" />
            </div>

            <div class="space-y-2">
                <Label for="end_at">Ends (optional)</Label>
                <Input
                    id="end_at"
                    name="end_at"
                    type="datetime-local"
                    :default-value="event?.end_at ?? undefined"
                    :aria-invalid="!!errors.end_at"
                    :aria-describedby="
                        errors.end_at ? 'end_at-error' : undefined
                    "
                />
                <InputError id="end_at-error" :message="errors.end_at" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="location">Location (optional)</Label>
            <Input
                id="location"
                name="location"
                :default-value="event?.location ?? undefined"
                :aria-invalid="!!errors.location"
                :aria-describedby="
                    errors.location ? 'location-error' : undefined
                "
            />
            <InputError id="location-error" :message="errors.location" />
        </div>

        <MediaPicker
            name="featured_image_media_id"
            label="Featured Image (optional)"
            :initial-media-id="event?.featured_image_media_id"
            :initial-preview-url="event?.featured_image_thumbnail_url"
        />
        <InputError
            id="featured_image_media_id-error"
            :message="errors.featured_image_media_id"
        />

        <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select name="status" :default-value="event?.status">
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
