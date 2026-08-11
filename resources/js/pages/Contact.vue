<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
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
import { store } from '@/routes/contact';

// Fields match saba.md §23.1 exactly. The honeypot field ("website") is a
// plain hidden input, not part of the visible form — a real person never
// sees or fills it; a bot's form-filler typically does. See
// App\Http\Controllers\Concerns\DetectsHoneypot.
const subjects = [
    { value: 'general', label: 'General' },
    { value: 'donation', label: 'Donation' },
    { value: 'partnership', label: 'Partnership' },
    { value: 'volunteer', label: 'Volunteer' },
    { value: 'media', label: 'Media' },
];
</script>

<template>
    <Head title="Contact" />

    <section class="mx-auto max-w-xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-center text-3xl font-bold text-foreground">
            Contact Us
        </h1>
        <p class="mt-3 text-center text-muted-foreground">
            Questions about our programs, a potential partnership, or anything
            else — we'd like to hear from you.
        </p>

        <Form
            v-bind="store.form()"
            :reset-on-success="['message']"
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

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="country">Country (optional)</Label>
                    <Input id="country" name="country" />
                    <InputError :message="errors.country" />
                </div>
                <div class="space-y-2">
                    <Label for="organization">Organization (optional)</Label>
                    <Input id="organization" name="organization" />
                    <InputError :message="errors.organization" />
                </div>
            </div>

            <div class="space-y-2">
                <Label for="subject">Subject</Label>
                <Select name="subject">
                    <SelectTrigger id="subject" class="w-full">
                        <SelectValue placeholder="Choose a topic" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="subject in subjects"
                            :key="subject.value"
                            :value="subject.value"
                        >
                            {{ subject.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.subject" />
            </div>

            <div class="space-y-2">
                <Label for="message">Message</Label>
                <Textarea id="message" name="message" required rows="5" />
                <InputError :message="errors.message" />
            </div>

            <div class="flex items-start gap-3">
                <Checkbox id="consent" name="consent" required class="mt-0.5" />
                <Label
                    for="consent"
                    class="text-sm font-normal text-muted-foreground"
                >
                    I agree to Saba International contacting me.
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
                Send Message
            </Button>

            <p
                v-if="recentlySuccessful"
                class="text-center text-sm text-primary"
            >
                Thanks for reaching out — we'll get back to you soon.
            </p>
        </Form>
    </section>
</template>
