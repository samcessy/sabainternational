<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DocumentForm from '@/components/admin/DocumentForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/documents';

type Option = { value: string; label: string };

defineProps<{
    documentTypeOptions: Option[];
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Documents', href: index() },
            { title: 'New Document', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Document" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Document</h1>

        <DocumentForm
            class="mt-6"
            :form-target="store.form()"
            :document-type-options="documentTypeOptions"
            :status-options="statusOptions"
            submit-label="Create Document"
        />
    </div>
</template>
