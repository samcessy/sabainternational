<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import RedirectForm from '@/components/admin/RedirectForm.vue';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/redirects';

type Redirect = {
    id: number;
    from_path: string;
    to_path: string;
    status_code: number;
};

const props = defineProps<{
    redirect: Redirect;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Redirects', href: index() },
            { title: 'Edit Redirect', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit redirect from ${redirect.from_path}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit redirect from {{ redirect.from_path }}
        </h1>

        <RedirectForm
            class="mt-6"
            :form-target="update.form(props.redirect.id)"
            :redirect="redirect"
            submit-label="Save Changes"
        />
    </div>
</template>
