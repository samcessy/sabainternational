<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ProgramForm from '@/components/admin/ProgramForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/programs';

type Option = { value: string; label: string };

type Program = {
    id: number;
    name: string;
    legal_name: string | null;
    slug: string;
    category: string;
    relationship_type: string;
    external_url: string | null;
    founded_year: number | null;
    location: string | null;
    short_description: string | null;
    overview: string | null;
    what_happens_here: string | null;
    sensitive_content_classification: string;
    seo_title: string | null;
    seo_description: string | null;
    og_image: string | null;
    status: string;
};

const props = defineProps<{
    program: Program;
    categoryOptions: Option[];
    relationshipTypeOptions: Option[];
    sensitiveContentOptions: Option[];
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Programs', href: index() },
            { title: 'Edit Program', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${program.name}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ program.name }}
        </h1>

        <ProgramForm
            class="mt-6"
            :form-target="update.form(props.program.id)"
            :program="program"
            :category-options="categoryOptions"
            :relationship-type-options="relationshipTypeOptions"
            :sensitive-content-options="sensitiveContentOptions"
            :status-options="statusOptions"
            submit-label="Save Changes"
        />
    </div>
</template>
