<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ImpactMetricForm from '@/components/admin/ImpactMetricForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/impact-metrics';

type ProgramOption = { id: number; name: string };

defineProps<{
    programOptions: ProgramOption[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Impact Metrics', href: index() },
            { title: 'New Metric', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Impact Metric" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Impact Metric</h1>

        <ImpactMetricForm
            class="mt-6"
            :form-target="store.form()"
            :program-options="programOptions"
            submit-label="Create Metric"
        />
    </div>
</template>
