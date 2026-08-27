<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { SquarePen } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { edit, index } from '@/routes/admin/supporters';
import type { Auth } from '@/types';

type Supporter = {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
};

type Donation = {
    id: number;
    amount: string;
    currency: string;
    frequency_label: string;
    program: string | null;
    status: string;
    status_label: string;
    created_at: string | null;
};

const props = defineProps<{
    supporter: Supporter;
    donations: Donation[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Supporters', href: index() },
            { title: 'Supporter', href: '' },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canManage = () =>
    page.props.auth.permissions.includes('fundraising:manage');
</script>

<template>
    <Head :title="supporter.name" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">
                    {{ supporter.name }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ supporter.email }}
                </p>
            </div>
            <Button v-if="canManage()" as-child variant="outline">
                <Link :href="edit.url(props.supporter.id)">
                    <SquarePen class="size-4" aria-hidden="true" />
                    Edit
                </Link>
            </Button>
        </div>

        <div class="mt-10 max-w-3xl">
            <h2 class="text-lg font-bold text-foreground">Donation History</h2>

            <div
                v-if="donations.length === 0"
                class="mt-4 text-sm text-muted-foreground"
            >
                No donations recorded yet.
            </div>

            <div
                v-else
                class="mt-4 overflow-x-auto rounded-lg border border-border"
            >
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-muted/50">
                        <tr>
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
                            <th scope="col" class="px-4 py-3 font-medium">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="donation in donations"
                            :key="donation.id"
                            class="border-b border-border last:border-0"
                        >
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ donation.currency.toUpperCase() }}
                                {{ donation.amount }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ donation.frequency_label }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ donation.program ?? '—' }}
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
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
