<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import Seo from '@/components/Seo.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { store } from '@/routes/volunteer';
</script>

<template>
    <Seo
        title="Volunteer"
        description="Volunteer with Saba International and help support our education, nutrition, and shelter programs in East Africa."
    />

    <section class="mx-auto max-w-xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-center text-3xl font-bold text-foreground">
            Volunteer With Us
        </h1>
        <p class="mt-3 text-center text-muted-foreground">
            Tell us a bit about yourself and how you'd like to get involved.
            We'll follow up with next steps.
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
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    name="name"
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
                    required
                    :aria-invalid="!!errors.email"
                    :aria-describedby="errors.email ? 'email-error' : undefined"
                />
                <InputError id="email-error" :message="errors.email" />
            </div>

            <div class="space-y-2">
                <Label for="details">
                    What kind of volunteering are you interested in?
                </Label>
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
                    application.
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
                Submit Application
            </Button>

            <p
                v-if="recentlySuccessful"
                class="text-center text-sm text-primary"
            >
                Thanks for applying — we'll be in touch soon.
            </p>
        </Form>
    </section>
</template>
