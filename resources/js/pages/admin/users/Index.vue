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
import { create, destroy, edit, index } from '@/routes/admin/users';
import type { Auth } from '@/types';

type UserRow = {
    id: number;
    name: string;
    email: string;
    admin_role: string | null;
    admin_role_label: string | null;
    two_factor_enabled: boolean;
    created_at: string | null;
};

type PaginatedUsers = {
    data: UserRow[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    users: PaginatedUsers;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Users', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const currentUserId = page.props.auth.user.id;

const pendingDelete = ref<UserRow | null>(null);

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
    <Head title="Users" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">Users</h1>
            <Button as-child variant="cta">
                <Link :href="create.url()">
                    <Plus class="size-4" aria-hidden="true" />
                    New User
                </Link>
            </Button>
        </div>

        <div v-if="users.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No users yet.</p>
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
                        <th scope="col" class="px-4 py-3 font-medium">
                            Two-Factor
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Joined
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">
                                {{ user.name }}
                                <span
                                    v-if="user.id === currentUserId"
                                    class="text-muted-foreground"
                                >
                                    (you)</span
                                >
                            </div>
                            <div class="text-muted-foreground">
                                {{ user.email }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ user.admin_role_label ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    user.two_factor_enabled
                                        ? 'default'
                                        : 'destructive'
                                "
                            >
                                {{
                                    user.two_factor_enabled
                                        ? 'Enabled'
                                        : 'Not set up'
                                }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                user.created_at
                                    ? new Date(
                                          user.created_at,
                                      ).toLocaleDateString()
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="edit.url(user.id)"
                                        :aria-label="`Edit ${user.name}`"
                                    >
                                        <SquarePen
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                    </Link>
                                </Button>
                                <Button
                                    v-if="user.id !== currentUserId"
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Delete ${user.name}`"
                                    @click="pendingDelete = user"
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
            v-if="users.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Users pagination"
        >
            <Link
                v-for="(link, linkIndex) in users.links"
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
                <DialogTitle>Delete this user?</DialogTitle>
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
