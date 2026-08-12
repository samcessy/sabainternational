<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Plus, SquarePen, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { dashboard } from '@/routes';
import { create, destroy, edit, index } from '@/routes/admin/team-members';
import type { Auth } from '@/types';

type TeamMember = {
    id: number;
    name: string;
    role: string;
    board_member: boolean;
    status: string;
    status_label: string;
    display_order: number;
};

type PaginatedTeamMembers = {
    data: TeamMember[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    teamMembers: PaginatedTeamMembers;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Team Members', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () => page.props.auth.permissions.includes('content:manage');

const pendingDelete = ref<TeamMember | null>(null);

function performDelete() {
    if (!pendingDelete.value) {
        return;
    }

    router.delete(destroy.url(pendingDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            pendingDelete.value = null;
        },
    });
}

function decodeLabel(label: string): string {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
}
</script>

<template>
    <Head title="Team Members" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">Team Members</h1>
            <Button v-if="canManage()" as-child variant="cta">
                <Link :href="create.url()">
                    <Plus class="size-4" aria-hidden="true" />
                    New Team Member
                </Link>
            </Button>
        </div>

        <div v-if="teamMembers.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No team members yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Name</th>
                        <th scope="col" class="px-4 py-3 font-medium">Role</th>
                        <th scope="col" class="px-4 py-3 font-medium">Board</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">Order</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="teamMember in teamMembers.data"
                        :key="teamMember.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 font-medium text-foreground">
                            {{ teamMember.name }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ teamMember.role }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ teamMember.board_member ? 'Yes' : 'No' }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    teamMember.status === 'published'
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ teamMember.status_label }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ teamMember.display_order }}
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="canManage()"
                                class="flex items-center justify-end gap-1"
                            >
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="edit.url(teamMember.id)"
                                        :aria-label="`Edit ${teamMember.name}`"
                                    >
                                        <SquarePen
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Delete ${teamMember.name}`"
                                    @click="pendingDelete = teamMember"
                                >
                                    <Trash2 class="size-4" aria-hidden="true" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav
            v-if="teamMembers.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Team members pagination"
        >
            <Link
                v-for="(link, linkIndex) in teamMembers.links"
                :key="linkIndex"
                :href="link.url ?? '#'"
                :class="[
                    'flex min-h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm',
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                    !link.url && 'pointer-events-none opacity-40',
                ]"
                :aria-current="link.active ? 'page' : undefined"
            >
                {{ decodeLabel(link.label) }}
            </Link>
        </nav>
    </div>

    <Dialog
        :open="pendingDelete !== null"
        @update:open="(open) => (pendingDelete = open ? pendingDelete : null)"
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete this team member?</DialogTitle>
                <DialogDescription>
                    This will delete "{{ pendingDelete?.name }}". This action
                    cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="pendingDelete = null">
                    Cancel
                </Button>
                <Button variant="destructive" @click="performDelete">
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
