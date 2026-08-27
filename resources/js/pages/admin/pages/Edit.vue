<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageForm from '@/components/admin/PageForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/pages';

type Option = { value: string; label: string };

type Page = {
    id: number;
    title: string;
    slug: string;
    body: string | null;
    seo_title: string | null;
    seo_description: string | null;
    og_image: string | null;
    status: string;
};

const props = defineProps<{
    page: Page;
    statusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pages', href: index() },
            { title: 'Edit Page', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${page.title}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ page.title }}
        </h1>

        <PageForm
            class="mt-6"
            :form-target="update.form(props.page.id)"
            :page="page"
            :status-options="statusOptions"
            submit-label="Save Changes"
        />
    </div>
</template>
