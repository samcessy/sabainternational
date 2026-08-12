<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { KeyRound } from '@lucide/vue';
import UserForm from '@/components/admin/UserForm.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index, sendPasswordReset, update } from '@/routes/admin/users';

type Option = { value: string; label: string };

type User = {
    id: number;
    name: string;
    email: string;
    admin_role: string | null;
};

const props = defineProps<{
    user: User;
    roleOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Users', href: index() },
            { title: 'Edit User', href: '' },
        ],
    }),
});

function sendReset() {
    router.post(
        sendPasswordReset.url(props.user.id),
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <div class="p-4">
        <div class="flex items-start justify-between gap-4">
            <h1 class="text-2xl font-bold text-foreground">
                Edit {{ user.name }}
            </h1>
            <Button variant="outline" @click="sendReset">
                <KeyRound class="size-4" aria-hidden="true" />
                Send Password Reset
            </Button>
        </div>

        <UserForm
            class="mt-6"
            :form-target="update.form(props.user.id)"
            :user="user"
            :role-options="roleOptions"
            submit-label="Save Changes"
        />
    </div>
</template>
