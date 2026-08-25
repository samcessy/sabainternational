<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EventForm from '@/components/admin/EventForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/events';

type Option = { value: string; label: string };

type Event = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    start_at: string;
    end_at: string | null;
    location: string | null;
    featured_image_media_id: number | null;
    featured_image_thumbnail_url: string | null;
    status: string;
};

const props = defineProps<{
    event: Event;
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Events', href: index() },
            { title: 'Edit Event', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${event.title}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ event.title }}
        </h1>

        <EventForm
            class="mt-6"
            :form-target="update.form(props.event.id)"
            :event="event"
            :status-options="statusOptions"
            submit-label="Save Changes"
        />
    </div>
</template>
