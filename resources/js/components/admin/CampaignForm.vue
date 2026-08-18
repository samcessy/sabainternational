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

type CampaignValues = {
    name?: string;
    slug?: string;
    description?: string | null;
    goal_amount?: number | null;
    start_date?: string | null;
    end_date?: string | null;
    featured_image_media_id?: number | null;
    featured_image_thumbnail_url?: string | null;
    impact_statement?: string | null;
    suggested_amounts?: string | null;
    status?: string;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        campaign?: CampaignValues | null;
        statusOptions: Option[];
        submitLabel: string;
    }>(),
    { campaign: null },
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
                    :default-value="campaign?.name"
                    required
                    :aria-invalid="!!errors.name"
                    :aria-describedby="errors.name ? 'name-error' : undefined"
                />
                <InputError id="name-error" :message="errors.name" />
            </div>

            <div class="space-y-2">
                <Label for="slug">Slug</Label>
                <Input
                    id="slug"
                    name="slug"
                    :default-value="campaign?.slug"
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
                :default-value="campaign?.description ?? undefined"
                :aria-invalid="!!errors.description"
                :aria-describedby="
                    errors.description ? 'description-error' : undefined
                "
            />
            <InputError id="description-error" :message="errors.description" />
        </div>

        <MediaPicker
            name="featured_image_media_id"
            label="Featured Image (optional)"
            :initial-media-id="campaign?.featured_image_media_id"
            :initial-preview-url="campaign?.featured_image_thumbnail_url"
        />
        <InputError
            id="featured_image_media_id-error"
            :message="errors.featured_image_media_id"
        />

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="start_date">Start Date (optional)</Label>
                <Input
                    id="start_date"
                    name="start_date"
                    type="date"
                    :default-value="campaign?.start_date ?? undefined"
                    :aria-invalid="!!errors.start_date"
                    :aria-describedby="
                        errors.start_date ? 'start_date-error' : undefined
                    "
                />
                <InputError
                    id="start_date-error"
                    :message="errors.start_date"
                />
            </div>

            <div class="space-y-2">
                <Label for="end_date">End Date (optional)</Label>
                <Input
                    id="end_date"
                    name="end_date"
                    type="date"
                    :default-value="campaign?.end_date ?? undefined"
                    :aria-invalid="!!errors.end_date"
                    :aria-describedby="
                        errors.end_date ? 'end_date-error' : undefined
                    "
                />
                <InputError id="end_date-error" :message="errors.end_date" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="goal_amount">Goal Amount, USD (optional)</Label>
            <Input
                id="goal_amount"
                name="goal_amount"
                type="number"
                min="1"
                step="1"
                :default-value="campaign?.goal_amount ?? undefined"
                :aria-invalid="!!errors.goal_amount"
                :aria-describedby="
                    errors.goal_amount ? 'goal_amount-error' : undefined
                "
            />
            <InputError id="goal_amount-error" :message="errors.goal_amount" />
        </div>

        <div class="space-y-2">
            <Label for="suggested_amounts">
                Suggested Amounts, USD (optional)
            </Label>
            <Input
                id="suggested_amounts"
                name="suggested_amounts"
                placeholder="25, 50, 100, 250, 500"
                :default-value="campaign?.suggested_amounts ?? undefined"
                :aria-invalid="!!errors.suggested_amounts"
                :aria-describedby="
                    errors.suggested_amounts
                        ? 'suggested_amounts-error'
                        : undefined
                "
            />
            <InputError
                id="suggested_amounts-error"
                :message="errors.suggested_amounts"
            />
            <p class="text-sm text-muted-foreground">
                Comma-separated whole dollar amounts.
            </p>
        </div>

        <div class="space-y-2">
            <Label for="impact_statement">Impact Statement (optional)</Label>
            <Textarea
                id="impact_statement"
                name="impact_statement"
                rows="3"
                :default-value="campaign?.impact_statement ?? undefined"
                :aria-invalid="!!errors.impact_statement"
                :aria-describedby="
                    errors.impact_statement
                        ? 'impact_statement-error'
                        : undefined
                "
            />
            <InputError
                id="impact_statement-error"
                :message="errors.impact_statement"
            />
        </div>

        <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select name="status" :default-value="campaign?.status">
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
