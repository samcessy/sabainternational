<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Download } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import {
    exportMethod as exportSupporters,
    index,
    show,
} from '@/routes/admin/supporters';
import type { Auth } from '@/types';

type Supporter = {
    id: number;
    name: string;
    email: string;
    donations_count: number;
    total_donated: string;
};

type PaginatedSupporters = {
    data: Supporter[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    supporters: PaginatedSupporters;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Supporters', href: index() },
        ],
    }),
});

const page = usePage<{ auth: Auth }>();
const canExport = () =>
    page.props.auth.permissions.includes('fundraising:export-donor-data');

function decodeLabel(label: string): string {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
}
</script>

<template>
    <Head title="Supporters" />

    <div class="p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">Supporters</h1>
            <Button v-if="canExport()" as-child variant="outline">
                <a :href="exportSupporters.url()">
                    <Download class="size-4" aria-hidden="true" />
                    Export CSV
                </a>
            </Button>
        </div>

        <div v-if="supporters.data.length === 0" class="mt-10 text-center">
            <p class="text-muted-foreground">No supporters yet.</p>
        </div>

        <div
            v-else
            class="mt-6 overflow-x-auto rounded-lg border border-border"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Name</th>
                        <th scope="col" class="px-4 py-3 font-medium">Email</th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Donations
                        </th>
                        <th scope="col" class="px-4 py-3 font-medium">
                            Total Given
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="supporter in supporters.data"
                        :key="supporter.id"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-4 py-3 font-medium text-foreground">
                            <Link
                                :href="show.url(supporter.id)"
                                class="hover:underline"
                            >
                                {{ supporter.name }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ supporter.email }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ supporter.donations_count }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            ${{ supporter.total_donated }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav
            v-if="supporters.links.length > 3"
            class="mt-6 flex flex-wrap gap-2"
            aria-label="Supporters pagination"
        >
            <Link
                v-for="(link, linkIndex) in supporters.links"
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
</template>
