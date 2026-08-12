<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TeamMemberForm from '@/components/admin/TeamMemberForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/team-members';

type Option = { value: string; label: string };

type TeamMember = {
    id: number;
    name: string;
    role: string;
    bio: string | null;
    board_member: boolean;
    consent_to_publish: boolean;
    display_order: number;
    status: string;
};

const props = defineProps<{
    teamMember: TeamMember;
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Team Members', href: index() },
            { title: 'Edit Team Member', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${teamMember.name}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ teamMember.name }}
        </h1>

        <TeamMemberForm
            class="mt-6"
            :form-target="update.form(props.teamMember.id)"
            :team-member="teamMember"
            :status-options="statusOptions"
            submit-label="Save Changes"
        />
    </div>
</template>
