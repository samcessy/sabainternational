<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ImpactMetricForm from '@/components/admin/ImpactMetricForm.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import { dashboard } from '@/routes';
import { index, update } from '@/routes/admin/impact-metrics';
import {
    destroy,
    store as storeValue,
} from '@/routes/admin/impact-metrics/values';

type Option = { value: string; label: string };
type ProgramOption = { id: number; name: string };

type ImpactMetric = {
    id: number;
    name: string;
    unit: string;
    program_id: number | null;
};

type MetricValue = {
    id: number;
    value: string;
    time_period: string;
    data_source: string | null;
    verification_status: string;
    verification_status_label: string;
    last_updated_at: string | null;
};

const props = defineProps<{
    impactMetric: ImpactMetric;
    programOptions: ProgramOption[];
    values: MetricValue[];
    verificationStatusOptions: Option[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Impact Metrics', href: index() },
            { title: 'Edit Metric', href: '' },
        ],
    }),
});

const pendingDelete = ref<MetricValue | null>(null);

function performDelete() {
    if (!pendingDelete.value) {
        return;
    }

    router.delete(
        destroy.url({
            impact_metric: props.impactMetric.id,
            value: pendingDelete.value.id,
        }),
        {
            preserveScroll: true,
            onFinish: () => {
                pendingDelete.value = null;
            },
        },
    );
}
</script>

<template>
    <Head :title="`Edit ${impactMetric.name}`" />

    <div class="p-4">
        <h1 class="text-2xl font-bold text-foreground">
            Edit {{ impactMetric.name }}
        </h1>

        <ImpactMetricForm
            class="mt-6"
            :form-target="update.form(props.impactMetric.id)"
            :impact-metric="impactMetric"
            :program-options="programOptions"
            submit-label="Save Changes"
        />

        <div class="mt-10 max-w-2xl">
            <h2 class="text-lg font-bold text-foreground">Recorded Values</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Only verified values are ever shown as hard numbers on the
                public site (saba.md §6.3) - unverified/estimated values are
                kept for internal tracking.
            </p>

            <div
                v-if="values.length === 0"
                class="mt-4 text-sm text-muted-foreground"
            >
                No values recorded yet.
            </div>

            <div
                v-else
                class="mt-4 overflow-x-auto rounded-lg border border-border"
            >
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-muted/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">
                                Value
                            </th>
                            <th scope="col" class="px-4 py-3 font-medium">
                                Period
                            </th>
                            <th scope="col" class="px-4 py-3 font-medium">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 font-medium">
                                Source
                            </th>
                            <th scope="col" class="px-4 py-3 font-medium">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="value in values"
                            :key="value.id"
                            class="border-b border-border last:border-0"
                        >
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ value.value }} {{ impactMetric.unit }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ value.time_period }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        value.verification_status === 'verified'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{ value.verification_status_label }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ value.data_source ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    aria-label="Delete this value"
                                    @click="pendingDelete = value"
                                >
                                    <Trash2 class="size-4" aria-hidden="true" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 rounded-lg border border-border p-4">
                <h3 class="text-sm font-medium text-foreground">
                    Record a New Value
                </h3>
                <Form
                    v-bind="storeValue.form(impactMetric.id)"
                    :reset-on-success="['value', 'time_period', 'data_source']"
                    v-slot="{ errors, processing }"
                    class="mt-3 grid gap-4 sm:grid-cols-2"
                >
                    <div class="space-y-2">
                        <Label for="value">
                            Value ({{ impactMetric.unit }})
                        </Label>
                        <Input
                            id="value"
                            name="value"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            :aria-invalid="!!errors.value"
                            :aria-describedby="
                                errors.value ? 'value-error' : undefined
                            "
                        />
                        <InputError id="value-error" :message="errors.value" />
                    </div>

                    <div class="space-y-2">
                        <Label for="time_period">Time Period</Label>
                        <Input
                            id="time_period"
                            name="time_period"
                            placeholder="2026 School Year"
                            required
                            :aria-invalid="!!errors.time_period"
                            :aria-describedby="
                                errors.time_period
                                    ? 'time_period-error'
                                    : undefined
                            "
                        />
                        <InputError
                            id="time_period-error"
                            :message="errors.time_period"
                        />
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <Label for="data_source">Data Source (optional)</Label>
                        <Input
                            id="data_source"
                            name="data_source"
                            :aria-invalid="!!errors.data_source"
                            :aria-describedby="
                                errors.data_source
                                    ? 'data_source-error'
                                    : undefined
                            "
                        />
                        <InputError
                            id="data_source-error"
                            :message="errors.data_source"
                        />
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <Label for="verification_status">
                            Verification Status
                        </Label>
                        <Select name="verification_status">
                            <SelectTrigger
                                id="verification_status"
                                class="w-full"
                                :aria-invalid="!!errors.verification_status"
                                :aria-describedby="
                                    errors.verification_status
                                        ? 'verification_status-error'
                                        : undefined
                                "
                            >
                                <SelectValue placeholder="Choose a status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in verificationStatusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError
                            id="verification_status-error"
                            :message="errors.verification_status"
                        />
                    </div>

                    <Button
                        type="submit"
                        variant="outline"
                        class="sm:col-span-2"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" />
                        Add Value
                    </Button>
                </Form>
            </div>
        </div>
    </div>

    <Dialog
        :open="pendingDelete !== null"
        @update:open="(open) => (pendingDelete = open ? pendingDelete : null)"
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete this value?</DialogTitle>
                <DialogDescription>
                    This will delete the {{ pendingDelete?.time_period }} value
                    of {{ pendingDelete?.value }} {{ impactMetric.unit }}. This
                    action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="pendingDelete = null">
                    Cancel
                </Button>
                <Button variant="destructive" @click="performDelete">
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
