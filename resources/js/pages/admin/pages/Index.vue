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
import { create, destroy, edit, index } from '@/routes/admin/pages';
import type { Auth } from '@/types';

type Page = {
    id: number;
    title: string;
    slug: string;
    status: string;
    status_label: string;
    updated_at: string | null;
};

type PaginatedPages = {
    data: Page[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    pages: PaginatedPages;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pages', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () => page.props.auth.permissions.includes('content:manage');

const pendingDelete = ref<Page | null>(null);

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
    <Head title="Pages" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">Pages</h1>
            <Button v-if="canManage()" as-child variant="cta">
                <Link :href="create.url()">
                    <Plus class="size-4" aria-hidden="true" />
                    New Page
                </Link>
            </Button>
        </div>

        <div v-if="pages.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No pages yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Title</th>
                        <th scope="col" class="px-4 py-3 font-medium">Slug</th>
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
                        v-for="pageRow in pages.data"
                        :key="pageRow.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 font-medium text-foreground">
                            {{ pageRow.title }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ pageRow.slug }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    pageRow.status === 'published'
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ pageRow.status_label }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="canManage()"
                                class="flex items-center justify-end gap-1"
                            >
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="edit.url(pageRow.id)"
                                        :aria-label="`Edit ${pageRow.title}`"
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
                                    :aria-label="`Delete ${pageRow.title}`"
                                    @click="pendingDelete = pageRow"
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
            v-if="pages.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Pages pagination"
        >
            <Link
                v-for="(link, linkIndex) in pages.links"
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
                <DialogTitle>Delete this page?</DialogTitle>
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
