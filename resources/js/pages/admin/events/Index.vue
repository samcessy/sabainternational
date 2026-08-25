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
import { create, destroy, edit, index } from '@/routes/admin/events';
import type { Auth } from '@/types';

type Event = {
    id: number;
    title: string;
    slug: string;
    start_at: string;
    location: string | null;
    status: string;
    status_label: string;
};

type PaginatedEvents = {
    data: Event[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    events: PaginatedEvents;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Events', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () => page.props.auth.permissions.includes('content:manage');

const pendingDelete = ref<Event | null>(null);

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
    <Head title="Events" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">Events</h1>
            <Button v-if="canManage()" as-child variant="cta">
                <Link :href="create.url()">
                    <Plus class="size-4" aria-hidden="true" />
                    New Event
                </Link>
            </Button>
        </div>

        <div v-if="events.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No events yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Title</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Starts
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Location
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="event in events.data"
                        :key="event.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 font-medium text-foreground">
                            {{ event.title }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ new Date(event.start_at).toLocaleString() }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ event.location ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    event.status === 'published'
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ event.status_label }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="canManage()"
                                class="flex items-center justify-end gap-1"
                            >
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="edit.url(event.id)"
                                        :aria-label="`Edit ${event.title}`"
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
                                    :aria-label="`Delete ${event.title}`"
                                    @click="pendingDelete = event"
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
            v-if="events.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Events pagination"
        >
            <Link
                v-for="(link, linkIndex) in events.links"
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
                <DialogTitle>Delete this event?</DialogTitle>
                <DialogDescription>
                    This will delete "{{ pendingDelete?.title }}". This action
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
