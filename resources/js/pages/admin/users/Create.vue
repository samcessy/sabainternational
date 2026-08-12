<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import UserForm from '@/components/admin/UserForm.vue';
import { dashboard } from '@/routes';
import { index, store } from '@/routes/admin/users';

type Option = { value: string; label: string };

defineProps<{
    roleOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Users', href: index() },
            { title: 'New User', href: '' },
        ],
    }),
});
</script>

<template>
    <Head title="New User" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">New User</h1>
        <p class="mt-2 max-w-lg text-sm text-muted-foreground">
            They'll be emailed a link to set their own password - no one else
            will ever know it.
        </p>

        <UserForm
            class="mt-6"
            :form-target="store.form()"
            :role-options="roleOptions"
            submit-label="Create User"
        />
    </div>
</template>
