<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Eye } from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { dashboard } from '@/routes';
import { index } from '@/routes/admin/audit-logs';

type AuditLogEntry = {
    id: number;
    user_name: string | null;
    action: string;
    entity_type: string;
    entity_id: number | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    user_agent: string | null;
    created_at: string | null;
};

type PaginatedAuditLogs = {
    data: AuditLogEntry[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    auditLogs: PaginatedAuditLogs;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Audit Logs', href: index() },
        ],
    }),
});

const viewing = ref<AuditLogEntry | null>(null);

function humanize(value: string): string {
    return value
        .replace(/[-_]/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function actionVariant(
    action: string,
): 'default' | 'secondary' | 'destructive' {
    if (action === 'delete') {
        return 'destructive';
    }

    if (action === 'create') {
        return 'default';
    }

    return 'secondary';
}

function decodeLabel(label: string): string {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
}
</script>

<template>
    <Head title="Audit Logs" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">Audit Logs</h1>
        <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
            Every privileged administrative action, most recent first. This log
            is immutable — entries are written by the system and are never
            edited or deleted through this UI.
        </p>

        <div v-if="auditLogs.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No audit log entries yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">User</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Action
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Entity
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">When</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="entry in auditLogs.data"
                        :key="entry.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 text-foreground">
                            {{ entry.user_name ?? 'System' }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge :variant="actionVariant(entry.action)">
                                {{ humanize(entry.action) }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ humanize(entry.entity_type) }}
                            <span v-if="entry.entity_id"
                                >#{{ entry.entity_id }}</span
                            >
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                entry.created_at
                                    ? new Date(
                                          entry.created_at,
                                      ).toLocaleString()
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <Button
                                variant="ghost"
                                size="icon-sm"
                                aria-label="View entry details"
                                @click="viewing = entry"
                            >
                                <Eye class="size-4" aria-hidden="true" />
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav
            v-if="auditLogs.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Audit logs pagination"
        >
            <Link
                v-for="(link, linkIndex) in auditLogs.links"
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
        :open="viewing !== null"
        @update:open="(open) => (viewing = open ? viewing : null)"
    >
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    {{ viewing ? humanize(viewing.action) : '' }}
                    {{ viewing ? humanize(viewing.entity_type) : '' }}
                    <span v-if="viewing?.entity_id"
                        >#{{ viewing.entity_id }}</span
                    >
                </DialogTitle>
                <DialogDescription>
                    {{ viewing?.user_name ?? 'System' }} &middot;
                    {{
                        viewing?.created_at
                            ? new Date(viewing.created_at).toLocaleString()
                            : ''
                    }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 text-sm">
                <div v-if="viewing?.old_values">
                    <p class="font-medium text-foreground">Before</p>
                    <pre
                        class="mt-1 max-h-40 overflow-auto rounded-md bg-muted p-3 text-xs"
                        >{{ JSON.stringify(viewing.old_values, null, 2) }}</pre>
                </div>
                <div v-if="viewing?.new_values">
                    <p class="font-medium text-foreground">After</p>
                    <pre
                        class="mt-1 max-h-40 overflow-auto rounded-md bg-muted p-3 text-xs"
                        >{{ JSON.stringify(viewing.new_values, null, 2) }}</pre>
                </div>
                <div class="text-muted-foreground">
                    <p v-if="viewing?.ip_address">
                        IP address: {{ viewing.ip_address }}
                    </p>
                    <p v-if="viewing?.user_agent" class="break-all">
                        User agent: {{ viewing.user_agent }}
                    </p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
