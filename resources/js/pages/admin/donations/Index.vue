<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Download, Eye } from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { dashboard } from '@/routes';
import {
    exportMethod as exportDonations,
    index,
} from '@/routes/admin/donations';
import type { Auth } from '@/types';

type Transaction = {
    id: number;
    gateway_reference: string;
    status_label: string;
    receipt_sent_at: string | null;
};

type Donation = {
    id: number;
    supporter_name: string;
    supporter_email: string;
    program: string | null;
    amount: string;
    currency: string;
    frequency_label: string;
    status: string;
    status_label: string;
    anonymous: boolean;
    created_at: string | null;
    transactions: Transaction[];
};

type PaginatedDonations = {
    data: Donation[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    donations: PaginatedDonations;
    totals: { succeeded_count: number; succeeded_amount: string };
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Donations', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canExport = () =>
    page.props.auth.permissions.includes('fundraising:export-donor-data');

const viewing = ref<Donation | null>(null);

function decodeLabel(label: string): string {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
}
</script>

<template>
    <Head title="Donations" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">Donations</h1>
            <Button v-if="canExport()" as-child variant="outline">
                <a :href="exportDonations.url()">
                    <Download class="size-4" aria-hidden="true" />
                    Export CSV
                </a>
            </Button>
        </div>

        <Card class="mt-6 max-w-sm">
            <CardHeader>
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    Succeeded Donations
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p class="text-3xl font-bold text-foreground">
                    ${{ totals.succeeded_amount }}
                </p>
                <p class="text-sm text-muted-foreground">
                    across {{ totals.succeeded_count }} donation{{
                        totals.succeeded_count === 1 ? '' : 's'
                    }}
                </p>
            </CardContent>
        </Card>

        <div v-if="donations.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No donations yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Donor</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Amount
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Frequency
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Program
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">Date</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="donation in donations.data"
                        :key="donation.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">
                                {{ donation.supporter_name }}
                                <Badge
                                    v-if="donation.anonymous"
                                    variant="secondary"
                                    class="ml-1"
                                >
                                    Anonymous
                                </Badge>
                            </div>
                            <div class="text-muted-foreground">
                                {{ donation.supporter_email }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-foreground">
                            ${{ donation.amount }} {{ donation.currency }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ donation.frequency_label }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ donation.program ?? 'General Fund' }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    donation.status === 'succeeded'
                                        ? 'default'
                                        : donation.status === 'failed' ||
                                            donation.status === 'cancelled'
                                          ? 'destructive'
                                          : 'secondary'
                                "
                            >
                                {{ donation.status_label }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                donation.created_at
                                    ? new Date(
                                          donation.created_at,
                                      ).toLocaleDateString()
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <Button
                                variant="ghost"
                                size="icon-sm"
                                :aria-label="`View transactions for ${donation.supporter_name}'s donation`"
                                @click="viewing = donation"
                            >
                                <Eye class="size-4" aria-hidden="true" />
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav
            v-if="donations.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Donations pagination"
        >
            <Link
                v-for="(link, linkIndex) in donations.links"
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
                <DialogTitle>
                    {{ viewing?.supporter_name }}'s Transactions
                </DialogTitle>
                <DialogDescription>
                    ${{ viewing?.amount }} {{ viewing?.currency }} &middot;
                    {{ viewing?.frequency_label }}
                </DialogDescription>
            </DialogHeader>
            <div v-if="viewing && viewing.transactions.length === 0">
                <p class="text-sm text-muted-foreground">
                    No transactions recorded yet.
                </p>
            </div>
            <ul v-else class="space-y-3">
                <li
                    v-for="transaction in viewing?.transactions"
                    :key="transaction.id"
                    class="rounded-md border border-border p-3 text-sm"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs text-muted-foreground">
                            {{ transaction.gateway_reference }}
                        </span>
                        <Badge variant="secondary">
                            {{ transaction.status_label }}
                        </Badge>
                    </div>
                    <p class="mt-1 text-muted-foreground">
                        Receipt:
                        {{
                            transaction.receipt_sent_at
                                ? new Date(
                                      transaction.receipt_sent_at,
                                  ).toLocaleString()
                                : 'Not sent'
                        }}
                    </p>
                </li>
            </ul>
        </DialogContent>
    </Dialog>
</template>
