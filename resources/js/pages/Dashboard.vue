<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { AlertTriangle, Clock, HeartHandshake } from '@lucide/vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { Auth } from '@/types';

type PendingApproval = {
    title: string;
    type: string;
    href: string;
    updated_at: string | null;
};

type StaleContent = {
    title: string;
    href: string;
    updated_at: string | null;
};

type RecentDonation = {
    id: number;
    supporter_name: string;
    amount: string;
    currency: string;
    created_at: string | null;
};

defineProps<{
    pendingApprovals?: PendingApproval[];
    staleContent?: StaleContent[];
    recentDonations?: RecentDonation[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    }),
});

const page = usePage<{ auth: Auth }>();
const canViewContent = () =>
    page.props.auth.permissions.includes('content:view');
const canViewFundraising = () =>
    page.props.auth.permissions.includes('fundraising:view');

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString() : '—';
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="grid gap-6 p-4 lg:grid-cols-2">
        <Card v-if="canViewContent()">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <Clock class="size-4" aria-hidden="true" />
                    Pending Approvals
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p
                    v-if="!pendingApprovals || pendingApprovals.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    Nothing is waiting for review.
                </p>
                <ul v-else class="space-y-3">
                    <li
                        v-for="(item, itemIndex) in pendingApprovals"
                        :key="itemIndex"
                        class="flex items-center justify-between gap-4 text-sm"
                    >
                        <Link
                            :href="item.href"
                            class="truncate font-medium text-foreground hover:underline"
                        >
                            {{ item.title }}
                        </Link>
                        <span class="shrink-0 text-muted-foreground">
                            {{ item.type }} · {{ formatDate(item.updated_at) }}
                        </span>
                    </li>
                </ul>
            </CardContent>
        </Card>

        <Card v-if="canViewContent()">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <AlertTriangle class="size-4" aria-hidden="true" />
                    Content Freshness Alerts
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p
                    v-if="!staleContent || staleContent.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No published stories are overdue for review.
                </p>
                <ul v-else class="space-y-3">
                    <li
                        v-for="(item, itemIndex) in staleContent"
                        :key="itemIndex"
                        class="flex items-center justify-between gap-4 text-sm"
                    >
                        <Link
                            :href="item.href"
                            class="truncate font-medium text-foreground hover:underline"
                        >
                            {{ item.title }}
                        </Link>
                        <span class="shrink-0 text-muted-foreground">
                            last updated {{ formatDate(item.updated_at) }}
                        </span>
                    </li>
                </ul>
            </CardContent>
        </Card>

        <Card v-if="canViewFundraising()">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <HeartHandshake class="size-4" aria-hidden="true" />
                    Recent Donations
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p
                    v-if="!recentDonations || recentDonations.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No donations yet.
                </p>
                <ul v-else class="space-y-3">
                    <li
                        v-for="donation in recentDonations"
                        :key="donation.id"
                        class="flex items-center justify-between gap-4 text-sm"
                    >
                        <span class="truncate font-medium text-foreground">
                            {{ donation.supporter_name }}
                        </span>
                        <span class="shrink-0 text-muted-foreground">
                            {{ donation.currency.toUpperCase() }}
                            {{ donation.amount }} ·
                            {{ formatDate(donation.created_at) }}
                        </span>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
