<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TeamMemberForm from '@/components/admin/TeamMemberForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/team-members';

type Option = { value: string; label: string };

defineProps<{
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Team Members', href: index() },
            { title: 'New Team Member', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Team Member" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Team Member</h1>

        <TeamMemberForm
            class="mt-6"
            :form-target="store.form()"
            :status-options="statusOptions"
            submit-label="Create Team Member"
        />
    </div>
</template>
