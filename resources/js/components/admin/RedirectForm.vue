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

type RedirectValues = {
    from_path?: string;
    to_path?: string;
    status_code?: number;
};

const statusCodeOptions = [
    { value: '301', label: '301 — Permanent' },
    { value: '302', label: '302 — Temporary' },
    { value: '307', label: '307 — Temporary (preserve method)' },
    { value: '308', label: '308 — Permanent (preserve method)' },
];

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        redirect?: RedirectValues | null;
        submitLabel: string;
    }>(),
    { redirect: null },
);
</script>

<template>
    <Form
        v-bind="formTarget"
        v-slot="{ errors, processing }"
        class="max-w-2xl space-y-6"
    >
        <div class="space-y-2">
            <Label for="from_path">From path</Label>
            <Input
                id="from_path"
                name="from_path"
                placeholder="/old-url"
                :default-value="redirect?.from_path"
                required
                :aria-invalid="!!errors.from_path"
                :aria-describedby="
                    errors.from_path ? 'from_path-error' : undefined
                "
            />
            <InputError id="from_path-error" :message="errors.from_path" />
        </div>

        <div class="space-y-2">
            <Label for="to_path">To path</Label>
            <Input
                id="to_path"
                name="to_path"
                placeholder="/new-url"
                :default-value="redirect?.to_path"
                required
                :aria-invalid="!!errors.to_path"
                :aria-describedby="errors.to_path ? 'to_path-error' : undefined"
            />
            <InputError id="to_path-error" :message="errors.to_path" />
        </div>

        <div class="space-y-2">
            <Label for="status_code">Status code</Label>
            <Select
                name="status_code"
                :default-value="String(redirect?.status_code ?? 301)"
            >
                <SelectTrigger
                    id="status_code"
                    class="w-full"
                    :aria-invalid="!!errors.status_code"
                    :aria-describedby="
                        errors.status_code ? 'status_code-error' : undefined
                    "
                >
                    <SelectValue placeholder="Choose a status code" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in statusCodeOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError id="status_code-error" :message="errors.status_code" />
        </div>

        <Button type="submit" variant="cta" size="lg" :disabled="processing">
            <Spinner v-if="processing" />
            {{ submitLabel }}
        </Button>
    </Form>
</template>
