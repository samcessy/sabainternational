<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { store } from '@/routes/volunteer';
</script>

<template>
    <Head title="Volunteer" />

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
                <Input id="name" name="name" required />
                <InputError :message="errors.name" />
            </div>

            <div class="space-y-2">
                <Label for="email">Email address</Label>
                <Input id="email" name="email" type="email" required />
                <InputError :message="errors.email" />
            </div>

            <div class="space-y-2">
                <Label for="details">
                    What kind of volunteering are you interested in?
                </Label>
                <Textarea id="details" name="details" required rows="5" />
                <InputError :message="errors.details" />
            </div>

            <div class="flex items-start gap-3">
                <Checkbox id="consent" name="consent" required class="mt-0.5" />
                <Label
                    for="consent"
                    class="text-sm font-normal text-muted-foreground"
                >
                    I agree to Saba International contacting me about this
                    application.
                </Label>
            </div>
            <InputError :message="errors.consent" />

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
