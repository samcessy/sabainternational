<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { store } from '@/routes/partnership';
</script>

<template>
    <Head title="Partner With Us" />

    <section class="mx-auto max-w-xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-center text-3xl font-bold text-foreground">
            Partner With Us
        </h1>
        <p class="mt-3 text-center text-muted-foreground">
            Whether you represent a company, foundation, or another
            organization, we'd love to explore how we might work together.
        </p>

        <Form
            v-bind="store.form()"
            :reset-on-success="['details']"
            v-slot="{ errors, processing, recentlySuccessful }"
            class="mt-10 space-y-5"
        >
            <div aria-hidden="true" style="position: absolute; left: -9999px">
                <label for="website">Website</label>
                <input
                    id="website"
                    name="website"
                    type="text"
                    tabindex="-1"
                    autocomplete="off"
                />
            </div>

            <div class="space-y-2">
                <Label for="organization_name">Organization Name</Label>
                <Input
                    id="organization_name"
                    name="organization_name"
                    required
                    :aria-invalid="!!errors.organization_name"
                    :aria-describedby="
                        errors.organization_name
                            ? 'organization_name-error'
                            : undefined
                    "
                />
                <InputError
                    id="organization_name-error"
                    :message="errors.organization_name"
                />
            </div>

            <div class="space-y-2">
                <Label for="contact_name">Your Name</Label>
                <Input
                    id="contact_name"
                    name="contact_name"
                    required
                    :aria-invalid="!!errors.contact_name"
                    :aria-describedby="
                        errors.contact_name ? 'contact_name-error' : undefined
                    "
                />
                <InputError
                    id="contact_name-error"
                    :message="errors.contact_name"
                />
            </div>

            <div class="space-y-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    required
                    :aria-invalid="!!errors.email"
                    :aria-describedby="errors.email ? 'email-error' : undefined"
                />
                <InputError id="email-error" :message="errors.email" />
            </div>

            <div class="space-y-2">
                <Label for="details"
                    >Tell us about your organization and interest in
                    partnering</Label
                >
                <Textarea
                    id="details"
                    name="details"
                    required
                    rows="5"
                    :aria-invalid="!!errors.details"
                    :aria-describedby="
                        errors.details ? 'details-error' : undefined
                    "
                />
                <InputError id="details-error" :message="errors.details" />
            </div>

            <div class="flex items-start gap-3">
                <Checkbox
                    id="consent"
                    name="consent"
                    required
                    class="mt-0.5"
                    :aria-invalid="!!errors.consent"
                    :aria-describedby="
                        errors.consent ? 'consent-error' : undefined
                    "
                />
                <Label
                    for="consent"
                    class="text-sm font-normal text-muted-foreground"
                >
                    I agree to Saba International contacting me about this
                    inquiry.
                </Label>
            </div>
            <InputError id="consent-error" :message="errors.consent" />

            <Button
                type="submit"
                variant="cta"
                size="lg"
                class="w-full"
                :disabled="processing"
            >
                <Spinner v-if="processing" />
                Send Inquiry
            </Button>

            <p
                v-if="recentlySuccessful"
                class="text-center text-sm text-primary"
            >
                Thanks for reaching out — we'll be in touch soon.
            </p>
        </Form>
    </section>
</template>
