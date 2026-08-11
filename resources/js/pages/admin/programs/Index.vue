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
import { create, destroy, edit, index } from '@/routes/admin/programs';
import type { Auth } from '@/types';

type Program = {
    id: number;
    name: string;
    slug: string;
    category_label: string;
    status: string;
    status_label: string;
    updated_at: string | null;
};

type PaginatedPrograms = {
    data: Program[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    programs: PaginatedPrograms;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Programs', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () => page.props.auth.permissions.includes('content:manage');

const pendingDelete = ref<Program | null>(null);

function confirmDelete(program: Program) {
    pendingDelete.value = program;
}

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
    <Head title="Programs" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">Programs</h1>
            <Button v-if="canManage()" as-child variant="cta">
                <Link :href="create.url()">
                    <Plus class="size-4" aria-hidden="true" />
                    New Program
                </Link>
            </Button>
        </div>

        <div v-if="programs.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No programs yet.</p>
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
                            Category
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Updated
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="program in programs.data"
                        :key="program.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 font-medium text-foreground">
                            {{ program.name }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ program.category_label }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    program.status === 'published'
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ program.status_label }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                program.updated_at
                                    ? new Date(
                                          program.updated_at,
                                      ).toLocaleDateString()
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="canManage()"
                                class="flex items-center justify-end gap-1"
                            >
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="edit.url(program.id)"
                                        :aria-label="`Edit ${program.name}`"
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
                                    :aria-label="`Delete ${program.name}`"
                                    @click="confirmDelete(program)"
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
            v-if="programs.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Programs pagination"
        >
            <Link
                v-for="(link, linkIndex) in programs.links"
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
                <DialogTitle>Delete this program?</DialogTitle>
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
