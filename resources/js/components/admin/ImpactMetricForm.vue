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

type ProgramOption = { id: number; name: string };

type ImpactMetricValues = {
    name?: string;
    unit?: string;
    program_id?: number | null;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        impactMetric?: ImpactMetricValues | null;
        programOptions: ProgramOption[];
        submitLabel: string;
    }>(),
    { impactMetric: null },
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
                placeholder="Students Enrolled"
                :default-value="impactMetric?.name"
                required
                :aria-invalid="!!errors.name"
                :aria-describedby="errors.name ? 'name-error' : undefined"
            />
            <InputError id="name-error" :message="errors.name" />
        </div>

        <div class="space-y-2">
            <Label for="unit">Unit</Label>
            <Input
                id="unit"
                name="unit"
                placeholder="students"
                :default-value="impactMetric?.unit"
                required
                :aria-invalid="!!errors.unit"
                :aria-describedby="errors.unit ? 'unit-error' : undefined"
            />
            <InputError id="unit-error" :message="errors.unit" />
        </div>

        <div class="space-y-2">
            <Label for="program_id">Program (optional)</Label>
            <Select
                name="program_id"
                :default-value="
                    impactMetric?.program_id
                        ? String(impactMetric.program_id)
                        : undefined
                "
            >
                <SelectTrigger
                    id="program_id"
                    class="w-full"
                    :aria-invalid="!!errors.program_id"
                    :aria-describedby="
                        errors.program_id ? 'program_id-error' : undefined
                    "
                >
                    <SelectValue placeholder="None" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in programOptions"
                        :key="option.id"
                        :value="String(option.id)"
                    >
                        {{ option.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError id="program_id-error" :message="errors.program_id" />
        </div>

        <Button type="submit" variant="cta" size="lg" :disabled="processing">
            <Spinner v-if="processing" />
            {{ submitLabel }}
        </Button>
    </Form>
</template>
