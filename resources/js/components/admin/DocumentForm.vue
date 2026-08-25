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

type DocumentValues = {
    title?: string;
    document_type?: string;
    year?: number | null;
    summary?: string | null;
    file_media_id?: number | null;
    file_thumbnail_url?: string | null;
    file_name?: string | null;
    cover_image_media_id?: number | null;
    cover_image_thumbnail_url?: string | null;
    status?: string;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        document?: DocumentValues | null;
        documentTypeOptions: Option[];
        statusOptions: Option[];
        submitLabel: string;
    }>(),
    { document: null },
);
</script>

<template>
    <Form
        v-bind="formTarget"
        v-slot="{ errors, processing }"
        class="max-w-2xl space-y-6"
    >
        <div class="space-y-2">
            <Label for="title">Title</Label>
            <Input
                id="title"
                name="title"
                :default-value="document?.title"
                required
                :aria-invalid="!!errors.title"
                :aria-describedby="errors.title ? 'title-error' : undefined"
            />
            <InputError id="title-error" :message="errors.title" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="document_type">Document Type</Label>
                <Select
                    name="document_type"
                    :default-value="document?.document_type"
                >
                    <SelectTrigger
                        id="document_type"
                        class="w-full"
                        :aria-invalid="!!errors.document_type"
                        :aria-describedby="
                            errors.document_type
                                ? 'document_type-error'
                                : undefined
                        "
                    >
                        <SelectValue placeholder="Choose a type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in documentTypeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError
                    id="document_type-error"
                    :message="errors.document_type"
                />
            </div>

            <div class="space-y-2">
                <Label for="year">Year (optional)</Label>
                <Input
                    id="year"
                    name="year"
                    type="number"
                    :default-value="document?.year ?? undefined"
                    :aria-invalid="!!errors.year"
                    :aria-describedby="errors.year ? 'year-error' : undefined"
                />
                <InputError id="year-error" :message="errors.year" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="summary">Summary (optional)</Label>
            <Textarea
                id="summary"
                name="summary"
                rows="4"
                :default-value="document?.summary ?? undefined"
                :aria-invalid="!!errors.summary"
                :aria-describedby="errors.summary ? 'summary-error' : undefined"
            />
            <InputError id="summary-error" :message="errors.summary" />
        </div>

        <MediaPicker
            name="file_media_id"
            label="File"
            choose-label="Choose File"
            :initial-media-id="document?.file_media_id"
            :initial-preview-url="document?.file_thumbnail_url"
            :initial-file-name="document?.file_name"
        />
        <InputError id="file_media_id-error" :message="errors.file_media_id" />

        <MediaPicker
            name="cover_image_media_id"
            label="Cover Image (optional)"
            :initial-media-id="document?.cover_image_media_id"
            :initial-preview-url="document?.cover_image_thumbnail_url"
        />
        <InputError
            id="cover_image_media_id-error"
            :message="errors.cover_image_media_id"
        />

        <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select name="status" :default-value="document?.status">
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
