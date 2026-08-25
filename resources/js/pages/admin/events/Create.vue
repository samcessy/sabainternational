<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EventForm from '@/components/admin/EventForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/events';

type Option = { value: string; label: string };

defineProps<{
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Events', href: index() },
            { title: 'New Event', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Event" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Event</h1>

        <EventForm
            class="mt-6"
            :form-target="store.form()"
            :status-options="statusOptions"
            submit-label="Create Event"
        />
    </div>
</template>
