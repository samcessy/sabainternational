<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Textarea } from '@/components/ui/textarea';

type Option = { value: string; label: string };

type TeamMemberValues = {
    name?: string;
    role?: string;
    bio?: string | null;
    board_member?: boolean;
    consent_to_publish?: boolean;
    display_order?: number;
    status?: string;
};

withDefaults(
    defineProps<{
        formTarget: { action: string; method: 'get' | 'post' };
        teamMember?: TeamMemberValues | null;
        statusOptions: Option[];
        submitLabel: string;
    }>(),
    { teamMember: null },
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
                :default-value="teamMember?.name"
                required
                :aria-invalid="!!errors.name"
                :aria-describedby="errors.name ? 'name-error' : undefined"
            />
            <InputError id="name-error" :message="errors.name" />
        </div>

        <div class="space-y-2">
            <Label for="role">Role</Label>
            <Input
                id="role"
                name="role"
                :default-value="teamMember?.role"
                required
                :aria-invalid="!!errors.role"
                :aria-describedby="errors.role ? 'role-error' : undefined"
            />
            <InputError id="role-error" :message="errors.role" />
        </div>

        <div class="space-y-2">
            <Label for="bio">Bio (optional)</Label>
            <Textarea
                id="bio"
                name="bio"
                rows="6"
                :default-value="teamMember?.bio ?? undefined"
                :aria-invalid="!!errors.bio"
                :aria-describedby="errors.bio ? 'bio-error' : undefined"
            />
            <InputError id="bio-error" :message="errors.bio" />
            <p class="text-sm text-muted-foreground">
                A team member cannot be published without a bio.
            </p>
        </div>

        <div class="space-y-2">
            <Label for="display_order">Display Order</Label>
            <Input
                id="display_order"
                name="display_order"
                type="number"
                min="0"
                :default-value="teamMember?.display_order ?? 0"
                required
                :aria-invalid="!!errors.display_order"
                :aria-describedby="
                    errors.display_order ? 'display_order-error' : undefined
                "
            />
            <InputError
                id="display_order-error"
                :message="errors.display_order"
            />
        </div>

        <div class="flex items-center gap-2">
            <Checkbox
                id="board_member"
                name="board_member"
                :default-value="teamMember?.board_member ?? false"
            />
            <Label for="board_member" class="font-normal"> Board member </Label>
        </div>

        <div class="flex items-center gap-2">
            <Checkbox
                id="consent_to_publish"
                name="consent_to_publish"
                :default-value="teamMember?.consent_to_publish ?? false"
            />
            <Label for="consent_to_publish" class="font-normal">
                Has consented to having their name, role, and bio published
            </Label>
        </div>

        <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select name="status" :default-value="teamMember?.status">
                <SelectTrigger
                    id="status"
                    class="w-full"
                    :aria-invalid="!!errors.status"
                    :aria-describedby="
                        errors.status ? 'status-error' : undefined
                    "
                >
                    <SelectValue placeholder="Choose a status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError id="status-error" :message="errors.status" />
        </div>

        <Button type="submit" variant="cta" size="lg" :disabled="processing">
            <Spinner v-if="processing" />
            {{ submitLabel }}
        </Button>
    </Form>
</template>
