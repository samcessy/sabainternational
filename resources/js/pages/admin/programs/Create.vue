<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ProgramForm from '@/components/admin/ProgramForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/programs';

type Option = { value: string; label: string };

defineProps<{
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
            { title: 'New Program', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Program" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Program</h1>

        <ProgramForm
            class="mt-6"
            :form-target="store.form()"
            :category-options="categoryOptions"
            :relationship-type-options="relationshipTypeOptions"
            :sensitive-content-options="sensitiveContentOptions"
            :status-options="statusOptions"
            submit-label="Create Program"
        />
    </div>
</template>
