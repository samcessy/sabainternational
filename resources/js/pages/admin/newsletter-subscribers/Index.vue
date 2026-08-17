<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { MailX, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { dashboard } from '@/routes';
import {
    destroy,
    index,
    unsubscribe,
} from '@/routes/admin/newsletter-subscribers';
import type { Auth } from '@/types';

type Subscriber = {
    id: number;
    email: string;
    status: string;
    status_label: string;
    frequency_preference: string | null;
    consent_timestamp: string | null;
    unsubscribed_at: string | null;
};

type PaginatedSubscribers = {
    data: Subscriber[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    subscribers: PaginatedSubscribers;
    totals: { subscribed_count: number };
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Newsletter Subscribers', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () =>
    page.props.auth.permissions.includes('engagement:manage');

const pendingDelete = ref<Subscriber | null>(null);

function unsubscribeSubscriber(subscriber: Subscriber) {
    router.post(unsubscribe.url(subscriber.id), {}, { preserveScroll: true });
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
    <Head title="Newsletter Subscribers" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Newsletter Subscribers
        </h1>

        <Card class="mt-6 max-w-sm">
            <CardHeader>
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    Active Subscribers
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p class="text-3xl font-bold text-foreground">
                    {{ totals.subscribed_count }}
                </p>
            </CardContent>
        </Card>

        <div v-if="subscribers.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No subscribers yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Email</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Subscribed
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="subscriber in subscribers.data"
                        :key="subscriber.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 text-foreground">
                            {{ subscriber.email }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    subscriber.status === 'subscribed'
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ subscriber.status_label }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                subscriber.consent_timestamp
                                    ? new Date(
                                          subscriber.consent_timestamp,
                                      ).toLocaleDateString()
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="canManage()"
                                class="flex items-center justify-end gap-1"
                            >
                                <Button
                                    v-if="subscriber.status === 'subscribed'"
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Unsubscribe ${subscriber.email}`"
                                    @click="unsubscribeSubscriber(subscriber)"
                                >
                                    <MailX class="size-4" aria-hidden="true" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Delete ${subscriber.email}`"
                                    @click="pendingDelete = subscriber"
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
            v-if="subscribers.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Newsletter subscribers pagination"
        >
            <Link
                v-for="(link, linkIndex) in subscribers.links"
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
                <DialogTitle>Delete this subscriber?</DialogTitle>
                <DialogDescription>
                    This will permanently delete "{{ pendingDelete?.email }}"
                    from the subscriber list. This action cannot be undone.
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
