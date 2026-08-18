<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CampaignForm from '@/components/admin/CampaignForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/campaigns';

type Option = { value: string; label: string };

defineProps<{
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Campaigns', href: index() },
            { title: 'New Campaign', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Campaign" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Campaign</h1>

        <CampaignForm
            class="mt-6"
            :form-target="store.form()"
            :status-options="statusOptions"
            submit-label="Create Campaign"
        />
    </div>
</template>
