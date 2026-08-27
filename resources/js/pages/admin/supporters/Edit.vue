<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/supporters';

type Supporter = {
    id: number;
    name: string;
    email: string;
};

const props = defineProps<{
    supporter: Supporter;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Supporters', href: index() },
            { title: 'Edit Supporter', href: '' },
        ],
    }),
});
</script>

<template>
    <Head :title="`Edit ${supporter.name}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ supporter.name }}
        </h1>

        <Form
            v-bind="update.form(props.supporter.id)"
            v-slot="{ errors, processing }"
            class="mt-6 max-w-2xl space-y-6"
        >
            <div class="space-y-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="supporter.name"
                    required
                    :aria-invalid="!!errors.name"
                    :aria-describedby="errors.name ? 'name-error' : undefined"
                />
                <InputError id="name-error" :message="errors.name" />
            </div>

            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="supporter.email"
                    required
                    :aria-invalid="!!errors.email"
                    :aria-describedby="errors.email ? 'email-error' : undefined"
                />
                <InputError id="email-error" :message="errors.email" />
            </div>

            <Button
                type="submit"
                variant="cta"
                size="lg"
                :disabled="processing"
            >
                <Spinner v-if="processing" />
                Save Changes
            </Button>
        </Form>
    </div>
</template>
