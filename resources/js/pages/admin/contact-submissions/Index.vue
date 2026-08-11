<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Eye, Trash2 } from '@lucide/vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes';
import { destroy, index, update } from '@/routes/admin/contact-submissions';
import type { Auth } from '@/types';

type Option = { value: string; label: string };

type Submission = {
    id: number;
    name: string;
    email: string;
    country: string | null;
    organization: string | null;
    subject_label: string;
    message: string;
    status: string;
    status_label: string;
    created_at: string | null;
};

type PaginatedSubmissions = {
    data: Submission[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    submissions: PaginatedSubmissions;
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Contact Messages', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () =>
    page.props.auth.permissions.includes('engagement:manage');

const viewing = ref<Submission | null>(null);
const pendingDelete = ref<Submission | null>(null);

function updateStatus(submission: Submission, status: string) {
    router.put(update.url(submission.id), { status }, { preserveScroll: true });
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
    <Head title="Contact Messages" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">Contact Messages</h1>

        <div v-if="submissions.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No messages yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">From</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Subject
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Received
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="submission in submissions.data"
                        :key="submission.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">
                                {{ submission.name }}
                            </div>
                            <div class="text-muted-foreground">
                                {{ submission.email }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ submission.subject_label }}
                        </td>
                        <td class="px-4 py-3">
                            <Select
                                :model-value="submission.status"
                                :disabled="!canManage()"
                                @update:model-value="
                                    (value) =>
                                        updateStatus(submission, String(value))
                                "
                            >
                                <SelectTrigger
                                    :aria-label="`Status for message from ${submission.name}`"
                                    class="h-9 w-40"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                submission.created_at
                                    ? new Date(
                                          submission.created_at,
                                      ).toLocaleDateString()
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`View message from ${submission.name}`"
                                    @click="viewing = submission"
                                >
                                    <Eye class="size-4" aria-hidden="true" />
                                </Button>
                                <Button
                                    v-if="canManage()"
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Delete message from ${submission.name}`"
                                    @click="pendingDelete = submission"
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
            v-if="submissions.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Contact messages pagination"
        >
            <Link
                v-for="(link, linkIndex) in submissions.links"
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
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Message from {{ viewing?.name }}</DialogTitle>
                <DialogDescription>
                    {{ viewing?.email }}
                    <span v-if="viewing?.organization">
                        &middot; {{ viewing.organization }}</span
                    >
                    <span v-if="viewing?.country">
                        &middot; {{ viewing.country }}</span
                    >
                </DialogDescription>
            </DialogHeader>
            <p class="text-sm whitespace-pre-line text-foreground">
                {{ viewing?.message }}
            </p>
        </DialogContent>
    </Dialog>

    <Dialog
        :open="pendingDelete !== null"
        @update:open="(open) => (pendingDelete = open ? pendingDelete : null)"
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete this message?</DialogTitle>
                <DialogDescription>
                    This will delete the message from "{{
                        pendingDelete?.name
                    }}". This action cannot be undone.
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
