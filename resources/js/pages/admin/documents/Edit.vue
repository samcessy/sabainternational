<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DocumentForm from '@/components/admin/DocumentForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/documents';

type Option = { value: string; label: string };

type Document = {
    id: number;
    title: string;
    document_type: string;
    year: number | null;
    summary: string | null;
    file_media_id: number | null;
    file_thumbnail_url: string | null;
    file_name: string | null;
    cover_image_media_id: number | null;
    cover_image_thumbnail_url: string | null;
    status: string;
};

const props = defineProps<{
    document: Document;
    documentTypeOptions: Option[];
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Documents', href: index() },
            { title: 'Edit Document', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${document.title}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ document.title }}
        </h1>

        <DocumentForm
            class="mt-6"
            :form-target="update.form(props.document.id)"
            :document="document"
            :document-type-options="documentTypeOptions"
            :status-options="statusOptions"
            submit-label="Save Changes"
        />
    </div>
</template>
