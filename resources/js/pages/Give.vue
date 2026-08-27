<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { AlertTriangle } from '@lucide/vue';
import { loadStripe } from '@stripe/stripe-js';
import type { Stripe, StripeElements } from '@stripe/stripe-js';
import { computed, nextTick, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import Seo from '@/components/Seo.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import { store as storeDonation } from '@/routes/donations';

type Program = { id: number; name: string };

const props = defineProps<{
    stripeKey: string | null;
    programs: Program[];
}>();

// docs/product-requirements.md §3 — suggested amounts, in whole dollars for
// display; converted to cents before hitting the API, matching
// docs/architecture/payment-architecture.md §8 (integer cents, not decimal).
const suggestedAmounts = [25, 50, 100, 250, 500];

const amount = ref<number | null>(50);
const customAmount = ref<string>('');
const usingCustomAmount = ref(false);
const frequency = ref<'one_time' | 'monthly'>('one_time');
const programId = ref<string>('general');
const name = ref('');
const email = ref('');
const anonymous = ref(false);

const step = ref<'details' | 'payment'>('details');
const submitting = ref(false);
const paying = ref(false);
const errors = ref<Record<string, string>>({});
const generalError = ref<string | null>(null);

const effectiveAmount = computed(() => {
    if (usingCustomAmount.value) {
        const parsed = Number.parseFloat(customAmount.value);

        return Number.isFinite(parsed) ? parsed : null;
    }

    return amount.value;
});

function selectSuggested(value: number) {
    usingCustomAmount.value = false;
    amount.value = value;
    customAmount.value = '';
}

function selectCustom() {
    usingCustomAmount.value = true;
    amount.value = null;
}

let stripe: Stripe | null = null;
let elements: StripeElements | null = null;

async function submitDetails() {
    errors.value = {};
    generalError.value = null;

    if (!effectiveAmount.value || effectiveAmount.value <= 0) {
        errors.value.amount_cents = 'Please choose or enter an amount.';

        return;
    }

    submitting.value = true;

    try {
        const response = await fetch(storeDonation.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': readXsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                amount_cents: Math.round(effectiveAmount.value * 100),
                frequency: frequency.value,
                program_id:
                    programId.value === 'general' ? null : programId.value,
                name: name.value,
                email: email.value,
                anonymous: anonymous.value,
            }),
        });

        if (response.status === 422) {
            const body = await response.json();
            errors.value = Object.fromEntries(
                Object.entries(body.errors as Record<string, string[]>).map(
                    ([field, messages]) => [field, messages[0]],
                ),
            );

            return;
        }

        if (response.status === 429) {
            generalError.value =
                "You've reached the limit of donation attempts for now. Please try again in a little while.";

            return;
        }

        if (!response.ok) {
            generalError.value = 'Something went wrong. Please try again.';

            return;
        }

        const { client_secret: clientSecret } = await response.json();
        await mountPaymentElement(clientSecret);
    } catch {
        generalError.value =
            'Something went wrong. Please check your connection and try again.';
    } finally {
        submitting.value = false;
    }
}

async function mountPaymentElement(clientSecret: string) {
    if (!props.stripeKey) {
        return;
    }

    stripe = await loadStripe(props.stripeKey);

    if (!stripe) {
        generalError.value =
            'Unable to load the payment provider. Please try again.';

        return;
    }

    elements = stripe.elements({ clientSecret });
    const paymentElement = elements.create('payment');

    step.value = 'payment';
    await nextTick();
    paymentElement.mount('#payment-element');
}

async function confirmPayment() {
    if (!stripe || !elements) {
        return;
    }

    paying.value = true;
    generalError.value = null;

    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: `${window.location.origin}/give/thank-you`,
        },
        redirect: 'if_required',
    });

    paying.value = false;

    if (error) {
        generalError.value =
            error.message ?? 'Your payment could not be completed.';

        return;
    }

    router.visit('/give/thank-you');
}

function readXsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

onBeforeUnmount(() => {
    elements = null;
    stripe = null;
});
</script>

<template>
    <Seo
        title="Give"
        description="Support education, nutrition, and shelter for underprivileged youth and families in East Africa with a one-time or monthly gift."
    />

    <section class="mx-auto max-w-xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-center text-3xl font-bold text-foreground">
            Make a Difference
        </h1>
        <p class="mt-3 text-center text-muted-foreground">
            Your gift directly supports education, nutrition, shelter, and youth
            empowerment programs in Kenya.
        </p>

        <Alert v-if="!stripeKey" variant="destructive" class="mt-8">
            <AlertTriangle class="size-4" />
            <AlertTitle>Payments aren't configured yet</AlertTitle>
            <AlertDescription>
                This donation flow is fully built, but live payment credentials
                haven't been added to this environment yet. Once Stripe keys are
                configured, this page will accept real gifts.
            </AlertDescription>
        </Alert>

        <Alert v-if="generalError" variant="destructive" class="mt-8">
            <AlertTriangle class="size-4" />
            <AlertDescription>{{ generalError }}</AlertDescription>
        </Alert>

        <form
            v-if="step === 'details'"
            class="mt-8 space-y-6"
            @submit.prevent="submitDetails"
        >
            <div>
                <Label>Amount (USD)</Label>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    <Button
                        v-for="value in suggestedAmounts"
                        :key="value"
                        type="button"
                        class="min-h-11"
                        :variant="
                            !usingCustomAmount && amount === value
                                ? 'default'
                                : 'outline'
                        "
                        @click="selectSuggested(value)"
                    >
                        ${{ value }}
                    </Button>
                    <Button
                        type="button"
                        class="min-h-11"
                        :variant="usingCustomAmount ? 'default' : 'outline'"
                        @click="selectCustom"
                    >
                        Custom
                    </Button>
                </div>
                <Input
                    v-if="usingCustomAmount"
                    id="custom-amount"
                    v-model="customAmount"
                    type="number"
                    min="1"
                    step="1"
                    placeholder="Enter amount"
                    class="mt-3"
                    :aria-invalid="!!errors.amount_cents"
                    :aria-describedby="
                        errors.amount_cents ? 'amount_cents-error' : undefined
                    "
                />
                <InputError
                    id="amount_cents-error"
                    :message="errors.amount_cents"
                />
            </div>

            <div>
                <Label id="frequency-label">Frequency</Label>
                <div
                    class="mt-2 grid grid-cols-2 gap-2"
                    role="radiogroup"
                    aria-labelledby="frequency-label"
                    :aria-describedby="
                        errors.frequency ? 'frequency-error' : undefined
                    "
                >
                    <Button
                        type="button"
                        class="min-h-11"
                        :variant="
                            frequency === 'one_time' ? 'default' : 'outline'
                        "
                        role="radio"
                        :aria-checked="frequency === 'one_time'"
                        @click="frequency = 'one_time'"
                    >
                        One-time
                    </Button>
                    <Button
                        type="button"
                        class="min-h-11"
                        :variant="
                            frequency === 'monthly' ? 'default' : 'outline'
                        "
                        role="radio"
                        :aria-checked="frequency === 'monthly'"
                        @click="frequency = 'monthly'"
                    >
                        Monthly
                    </Button>
                </div>
                <InputError id="frequency-error" :message="errors.frequency" />
            </div>

            <div>
                <Label for="designation">Designation</Label>
                <Select v-model="programId">
                    <SelectTrigger
                        id="designation"
                        class="mt-2 w-full"
                        :aria-invalid="!!errors.program_id"
                        :aria-describedby="
                            errors.program_id ? 'program_id-error' : undefined
                        "
                    >
                        <SelectValue placeholder="General Fund" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="general">General Fund</SelectItem>
                        <SelectItem
                            v-for="program in programs"
                            :key="program.id"
                            :value="String(program.id)"
                        >
                            {{ program.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError
                    id="program_id-error"
                    :message="errors.program_id"
                />
            </div>

            <div class="space-y-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    v-model="name"
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
                    v-model="email"
                    type="email"
                    required
                    :aria-invalid="!!errors.email"
                    :aria-describedby="errors.email ? 'email-error' : undefined"
                />
                <InputError id="email-error" :message="errors.email" />
            </div>

            <label
                class="flex items-center gap-2 text-sm text-muted-foreground"
            >
                <input
                    v-model="anonymous"
                    type="checkbox"
                    class="size-4 rounded border-input"
                />
                Give anonymously
            </label>

            <Button
                type="submit"
                variant="cta"
                size="lg"
                class="w-full"
                :disabled="submitting || !stripeKey"
            >
                <Spinner v-if="submitting" />
                Continue to Payment
            </Button>
        </form>

        <div v-else class="mt-8 space-y-6">
            <div id="payment-element" />
            <Button
                variant="cta"
                size="lg"
                class="w-full"
                :disabled="paying"
                @click="confirmPayment"
            >
                <Spinner v-if="paying" />
                Complete Donation
            </Button>
            <Button
                variant="ghost"
                size="lg"
                class="w-full"
                @click="step = 'details'"
            >
                Back
            </Button>
        </div>
    </section>
</template>
