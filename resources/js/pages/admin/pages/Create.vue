<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageForm from '@/components/admin/PageForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/pages';

type Option = { value: string; label: string };

defineProps<{
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pages', href: index() },
            { title: 'New Page', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Page" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Page</h1>

        <PageForm
            class="mt-6"
            :form-target="store.form()"
            :status-options="statusOptions"
            submit-label="Create Page"
        />
    </div>
</template>
