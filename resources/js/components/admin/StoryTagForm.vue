<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type StoryTagValues = {
    name?: string;
    slug?: string;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        storyTag?: StoryTagValues | null;
        submitLabel: string;
    }>(),
    { storyTag: null },
);
</script>

<template>
    <Form
        v-bind="formTarget"
        v-slot="{ errors, processing }"
        class="max-w-2xl space-y-6"
    >
        <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input
                id="name"
                name="name"
                :default-value="storyTag?.name"
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
                :default-value="storyTag?.slug"
                required
                :aria-invalid="!!errors.slug"
                :aria-describedby="errors.slug ? 'slug-error' : undefined"
            />
            <InputError id="slug-error" :message="errors.slug" />
        </div>

        <Button type="submit" variant="cta" size="lg" :disabled="processing">
            <Spinner v-if="processing" />
            {{ submitLabel }}
        </Button>
    </Form>
</template>
