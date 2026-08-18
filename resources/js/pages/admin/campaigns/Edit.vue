<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CampaignForm from '@/components/admin/CampaignForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/campaigns';

type Option = { value: string; label: string };

type Campaign = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    goal_amount: number | null;
    start_date: string | null;
    end_date: string | null;
    featured_image_media_id: number | null;
    featured_image_thumbnail_url: string | null;
    impact_statement: string | null;
    suggested_amounts: string | null;
    status: string;
};

const props = defineProps<{
    campaign: Campaign;
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Campaigns', href: index() },
            { title: 'Edit Campaign', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${campaign.name}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ campaign.name }}
        </h1>

        <CampaignForm
            class="mt-6"
            :form-target="update.form(props.campaign.id)"
            :campaign="campaign"
            :status-options="statusOptions"
            submit-label="Save Changes"
        />
    </div>
</template>
