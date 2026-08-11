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
import { destroy, index, update } from '@/routes/admin/partnership-inquiries';
import type { Auth } from '@/types';

type Option = { value: string; label: string };

type Inquiry = {
    id: number;
    organization_name: string;
    contact_name: string;
    email: string;
    details: string | null;
    status: string;
    status_label: string;
    created_at: string | null;
};

type PaginatedInquiries = {
    data: Inquiry[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    inquiries: PaginatedInquiries;
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Partnership Inquiries', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () =>
    page.props.auth.permissions.includes('engagement:manage');

const viewing = ref<Inquiry | null>(null);
const pendingDelete = ref<Inquiry | null>(null);

function updateStatus(inquiry: Inquiry, status: string) {
    router.put(update.url(inquiry.id), { status }, { preserveScroll: true });
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
    <Head title="Partnership Inquiries" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Partnership Inquiries
        </h1>

        <div v-if="inquiries.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No inquiries yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Organization
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Contact
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
                        v-for="inquiry in inquiries.data"
                        :key="inquiry.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 font-medium text-foreground">
                            {{ inquiry.organization_name }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-foreground">
                                {{ inquiry.contact_name }}
                            </div>
                            <div class="text-muted-foreground">
                                {{ inquiry.email }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <Select
                                :model-value="inquiry.status"
                                :disabled="!canManage()"
                                @update:model-value="
                                    (value) =>
                                        updateStatus(inquiry, String(value))
                                "
                            >
                                <SelectTrigger
                                    :aria-label="`Status for ${inquiry.organization_name}'s inquiry`"
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
                                inquiry.created_at
                                    ? new Date(
                                          inquiry.created_at,
                                      ).toLocaleDateString()
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`View ${inquiry.organization_name}'s inquiry`"
                                    @click="viewing = inquiry"
                                >
                                    <Eye class="size-4" aria-hidden="true" />
                                </Button>
                                <Button
                                    v-if="canManage()"
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Delete ${inquiry.organization_name}'s inquiry`"
                                    @click="pendingDelete = inquiry"
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
            v-if="inquiries.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Partnership inquiries pagination"
        >
            <Link
                v-for="(link, linkIndex) in inquiries.links"
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
                <DialogTitle>{{ viewing?.organization_name }}</DialogTitle>
                <DialogDescription>
                    {{ viewing?.contact_name }} &middot;
                    {{ viewing?.email }}
                </DialogDescription>
            </DialogHeader>
            <p class="text-sm whitespace-pre-line text-foreground">
                {{ viewing?.details }}
            </p>
        </DialogContent>
    </Dialog>

    <Dialog
        :open="pendingDelete !== null"
        @update:open="(open) => (pendingDelete = open ? pendingDelete : null)"
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete this inquiry?</DialogTitle>
                <DialogDescription>
                    This will delete the inquiry from "{{
                        pendingDelete?.organization_name
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
