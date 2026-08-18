<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Plus, SquarePen, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
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
import { create, destroy, edit, index } from '@/routes/admin/impact-metrics';
import type { Auth } from '@/types';

type ImpactMetric = {
    id: number;
    name: string;
    unit: string;
    program: string | null;
    latest_verified_value: string | null;
    value_count: number;
};

type PaginatedImpactMetrics = {
    data: ImpactMetric[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    impactMetrics: PaginatedImpactMetrics;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Impact Metrics', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () => page.props.auth.permissions.includes('impact:manage');

const pendingDelete = ref<ImpactMetric | null>(null);

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
    <Head title="Impact Metrics" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">Impact Metrics</h1>
            <Button v-if="canManage()" as-child variant="cta">
                <Link :href="create.url()">
                    <Plus class="size-4" aria-hidden="true" />
                    New Metric
                </Link>
            </Button>
        </div>

        <div v-if="impactMetrics.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No impact metrics yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Name</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Program
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Latest Verified Value
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Values Recorded
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="metric in impactMetrics.data"
                        :key="metric.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 font-medium text-foreground">
                            {{ metric.name }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ metric.program ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-foreground">
                            {{
                                metric.latest_verified_value
                                    ? `${metric.latest_verified_value} ${metric.unit}`
                                    : 'None verified'
                            }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ metric.value_count }}
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="canManage()"
                                class="flex items-center justify-end gap-1"
                            >
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="edit.url(metric.id)"
                                        :aria-label="`Edit ${metric.name}`"
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
                                    :aria-label="`Delete ${metric.name}`"
                                    @click="pendingDelete = metric"
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
            v-if="impactMetrics.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Impact metrics pagination"
        >
            <Link
                v-for="(link, linkIndex) in impactMetrics.links"
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
                <DialogTitle>Delete this metric?</DialogTitle>
                <DialogDescription>
                    This will delete "{{ pendingDelete?.name }}" and all of its
                    recorded values. This action cannot be undone.
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
