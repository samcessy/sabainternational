<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';

type Option = { value: string; label: string };

type UserValues = {
    name?: string;
    email?: string;
    admin_role?: string | null;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        user?: UserValues | null;
        roleOptions: Option[];
        submitLabel: string;
    }>(),
    { user: null },
);
</script>

<template>
    <Form
        v-bind="formTarget"
        v-slot="{ errors, processing }"
        class="max-w-lg space-y-6"
    >
        <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input
                id="name"
                name="name"
                :default-value="user?.name"
                required
                :aria-invalid="!!errors.name"
                :aria-describedby="errors.name ? 'name-error' : undefined"
            />
            <InputError id="name-error" :message="errors.name" />
        </div>

        <div class="space-y-2">
            <Label for="email">Email address</Label>
            <Input
                id="email"
                name="email"
                type="email"
                :default-value="user?.email"
                required
                :aria-invalid="!!errors.email"
                :aria-describedby="errors.email ? 'email-error' : undefined"
            />
            <InputError id="email-error" :message="errors.email" />
        </div>

        <div class="space-y-2">
            <Label for="admin_role">Role</Label>
            <Select
                name="admin_role"
                :default-value="user?.admin_role ?? undefined"
            >
                <SelectTrigger
                    id="admin_role"
                    class="w-full"
                    :aria-invalid="!!errors.admin_role"
                    :aria-describedby="
                        errors.admin_role ? 'admin_role-error' : undefined
                    "
                >
                    <SelectValue placeholder="Choose a role" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in roleOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError id="admin_role-error" :message="errors.admin_role" />
        </div>

        <Button type="submit" variant="cta" size="lg" :disabled="processing">
            <Spinner v-if="processing" />
            {{ submitLabel }}
        </Button>
    </Form>
</template>
