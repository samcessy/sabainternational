<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import RedirectForm from '@/components/admin/RedirectForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/redirects';

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Redirects', href: index() },
            { title: 'New Redirect', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New Redirect" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New Redirect</h1>

        <RedirectForm
            class="mt-6"
            :form-target="store.form()"
            submit-label="Create Redirect"
        />
    </div>
</template>
