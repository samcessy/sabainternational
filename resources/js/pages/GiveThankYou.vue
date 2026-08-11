<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { HeartHandshake } from '@lucide/vue';
import { Button } from '@/components/ui/button';

// Deliberately does not assume the donation has actually completed —
// Stripe's webhook (docs/architecture/payment-architecture.md §3) is the
// only source of truth for that. This page confirms the *submission*, and
// a receipt email follows once the webhook actually confirms payment.
</script>

<template>
    <Head title="Thank You" />

    <section class="mx-auto max-w-xl px-4 py-24 text-center sm:px-6 lg:px-8">
        <HeartHandshake
            class="mx-auto size-12 text-primary"
            aria-hidden="true"
        />
        <h1 class="mt-4 text-3xl font-bold text-foreground">Thank You</h1>
        <p class="mt-4 text-muted-foreground">
            We've received your donation. A confirmation and receipt will be
            sent to your email once your payment finishes processing.
        </p>
        <Button as-child variant="cta" size="lg" class="mt-8">
            <Link href="/">Return Home</Link>
        </Button>
    </section>
</template>
