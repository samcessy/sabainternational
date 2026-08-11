<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    Code2,
    GraduationCap,
    Home as HomeIcon,
    MapPin,
    Salad,
    Sparkles,
} from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { subscribe } from '@/routes/newsletter';

// Every fact on this page traces to docs/audit/current-website-audit.md or
// the stakeholder-pending decisions in docs/information-architecture.md —
// nothing here is invented. Sections needing verified data Saba hasn't
// supplied yet (impact numbers, stories, transparency documents) use
// qualitative language or an honest "coming soon" state instead, per
// saba.md §6.3 and §35's "never fabricate" rule.

const pillars = [
    {
        icon: GraduationCap,
        title: 'Education',
        description:
            'Academic support, mentorship, and school access for children who would otherwise go without.',
    },
    {
        icon: Salad,
        title: 'Nutrition',
        description:
            'Daily meals at partner schools so hunger is never the reason a child can’t focus on learning.',
    },
    {
        icon: HomeIcon,
        title: 'Shelter & Family Support',
        description:
            'Safe housing and family reintegration for children in vulnerable circumstances.',
    },
    {
        icon: Code2,
        title: 'Youth Economic Empowerment',
        description:
            'Practical skills training that gives young adults a real path to earning a living.',
    },
];

const programs = [
    {
        name: 'New Dawn',
        slug: 'new-dawn',
        category: 'Education',
        description:
            'An educational center and mentorship program serving vulnerable children from Nairobi slum settlements, offering academics, counseling, spiritual guidance, and meals.',
    },
    {
        name: 'Bethel Kibera School',
        slug: 'bethel-kibera-school',
        category: 'Education',
        description:
            'Began as a small daycare in Kibera and has grown into a full primary school offering education, food assistance, and teen mentorship.',
    },
    {
        name: 'The Nest',
        slug: 'the-nest',
        category: 'Shelter & Family Support',
        description:
            'Provides safe housing for vulnerable children and works toward rehabilitation and family reintegration.',
    },
    {
        name: 'The Hunter Initiative',
        slug: 'the-hunter-initiative',
        category: 'Youth Economic Empowerment',
        description:
            'Delivers software development training to economically disadvantaged youth to strengthen their earning potential.',
    },
];
</script>

<template>
    <Head title="Home" />

    <!-- Hero -->
    <section class="border-b border-border bg-primary text-primary-foreground">
        <div
            class="mx-auto max-w-5xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8"
        >
            <p
                class="text-sm font-semibold tracking-wide text-primary-foreground/80 uppercase"
            >
                Saba International &middot; Est. 2009
            </p>
            <h1
                class="mt-4 text-4xl font-bold tracking-tight text-balance sm:text-5xl"
            >
                Supporting education, nutrition and shelter for underprivileged
                youth and their families in East Africa.
            </h1>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <Button as-child variant="cta" size="lg">
                    <Link href="/give">Make a Difference</Link>
                </Button>
                <Button
                    as-child
                    variant="outline"
                    size="lg"
                    class="border-primary-foreground/30 bg-transparent text-primary-foreground hover:bg-primary-foreground/10 hover:text-primary-foreground"
                >
                    <a href="#our-work">Explore Our Work</a>
                </Button>
            </div>
        </div>
    </section>

    <!-- Trust indicators -->
    <section
        class="border-b border-border bg-card"
        aria-label="Organization facts"
    >
        <div
            class="mx-auto grid max-w-5xl grid-cols-2 gap-6 px-4 py-8 text-center sm:px-6 lg:grid-cols-4 lg:px-8"
        >
            <div>
                <p class="text-3xl font-bold text-primary">2009</p>
                <p class="mt-1 text-sm text-muted-foreground">Founded</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-primary">4</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Partner Programs
                </p>
            </div>
            <div>
                <p class="text-3xl font-bold text-primary">Kenya</p>
                <p class="mt-1 text-sm text-muted-foreground">Where We Work</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-primary">4</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Areas of Impact
                </p>
            </div>
        </div>
    </section>

    <!-- Our Mission (pillars) -->
    <section id="mission" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-center text-2xl font-bold text-foreground sm:text-3xl">
            Our Mission
        </h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="pillar in pillars" :key="pillar.title">
                <CardHeader>
                    <component
                        :is="pillar.icon"
                        class="size-8 text-primary"
                        aria-hidden="true"
                    />
                    <CardTitle class="mt-3">{{ pillar.title }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">
                        {{ pillar.description }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </section>

    <!-- Featured Programs -->
    <section id="our-work" class="border-y border-border bg-secondary/40">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <h2
                class="text-center text-2xl font-bold text-foreground sm:text-3xl"
            >
                Our Work
            </h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-muted-foreground">
                Four partner programs, each addressing a different part of what
                a child and family need to thrive.
            </p>
            <div class="mt-10 grid gap-6 sm:grid-cols-2">
                <Link
                    v-for="program in programs"
                    :key="program.name"
                    :href="`/programs/${program.slug}`"
                    class="block"
                >
                    <Card class="h-full transition-shadow hover:shadow-md">
                        <CardHeader>
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <CardTitle>{{ program.name }}</CardTitle>
                                <span
                                    class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                                >
                                    {{ program.category }}
                                </span>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-muted-foreground">
                                {{ program.description }}
                            </p>
                        </CardContent>
                    </Card>
                </Link>
            </div>
            <div class="mt-8 text-center">
                <Button as-child variant="outline">
                    <Link href="/programs">View All Programs</Link>
                </Button>
            </div>
        </div>
    </section>

    <!-- Where We Work -->
    <section
        id="where-we-work"
        class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8"
    >
        <MapPin class="mx-auto size-8 text-primary" aria-hidden="true" />
        <h2 class="mt-3 text-2xl font-bold text-foreground sm:text-3xl">
            Where We Work
        </h2>
        <p class="mx-auto mt-4 max-w-2xl text-muted-foreground">
            All four of our partner programs operate in Kenya, including
            programs serving children and families in and around Nairobi's
            Kibera settlement.
        </p>
    </section>

    <!-- Stories of Change -->
    <section id="stories" class="border-t border-border bg-secondary/40">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <Sparkles class="mx-auto size-8 text-primary" aria-hidden="true" />
            <h2 class="mt-3 text-2xl font-bold text-foreground sm:text-3xl">
                Stories of Change
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-muted-foreground">
                We're building out our library of verified, consented stories
                from the young people and families we work alongside. Check back
                soon, or subscribe below to hear about them first.
            </p>
            <Button as-child variant="outline" class="mt-6">
                <Link href="/stories">Browse Stories</Link>
            </Button>
        </div>
    </section>

    <!-- How Your Support Helps (qualitative, no fabricated numbers) -->
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-center text-2xl font-bold text-foreground sm:text-3xl">
            How Your Support Helps
        </h2>
        <ul class="mx-auto mt-8 max-w-2xl space-y-4 text-muted-foreground">
            <li class="flex gap-3">
                <span
                    class="mt-1 size-1.5 shrink-0 rounded-full bg-primary"
                    aria-hidden="true"
                />
                Students at New Dawn and Bethel Kibera School receive daily
                nutritious meals, enabling them to focus on their studies.
            </li>
            <li class="flex gap-3">
                <span
                    class="mt-1 size-1.5 shrink-0 rounded-full bg-primary"
                    aria-hidden="true"
                />
                Children at The Nest receive safe housing and support toward
                reuniting with their families.
            </li>
            <li class="flex gap-3">
                <span
                    class="mt-1 size-1.5 shrink-0 rounded-full bg-primary"
                    aria-hidden="true"
                />
                Young adults in The Hunter Initiative gain real software
                development skills to support themselves and their communities.
            </li>
        </ul>
        <p
            class="mx-auto mt-6 max-w-2xl text-center text-sm text-muted-foreground italic"
        >
            Verified financial breakdowns and impact metrics will be published
            in our Transparency Center as they're finalized.
        </p>
    </section>

    <!-- Get Involved / Newsletter (the one fully functional form on this page) -->
    <section
        id="support"
        class="border-t border-border bg-primary text-primary-foreground"
    >
        <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold sm:text-3xl">Get Involved</h2>
            <p class="mt-4 text-primary-foreground/90">
                There are a few ways to help — give, volunteer, or partner with
                us. Or subscribe below to stay in the loop.
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <Button as-child variant="cta" size="lg">
                    <Link href="/give">Give Now</Link>
                </Button>
                <Button
                    as-child
                    variant="outline"
                    size="lg"
                    class="border-primary-foreground/30 bg-transparent text-primary-foreground hover:bg-primary-foreground/10 hover:text-primary-foreground"
                >
                    <Link href="/volunteer">Volunteer</Link>
                </Button>
                <Button
                    as-child
                    variant="outline"
                    size="lg"
                    class="border-primary-foreground/30 bg-transparent text-primary-foreground hover:bg-primary-foreground/10 hover:text-primary-foreground"
                >
                    <Link href="/partner">Partner With Us</Link>
                </Button>
            </div>

            <Form
                v-bind="subscribe.form()"
                :reset-on-success="['email']"
                v-slot="{ errors, processing, recentlySuccessful }"
                class="mx-auto mt-8 max-w-sm space-y-4 text-left"
            >
                <div class="space-y-2">
                    <Label for="email" class="text-primary-foreground">
                        Email address
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        placeholder="you@example.com"
                        class="bg-primary-foreground text-foreground"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="flex items-start gap-3">
                    <Checkbox
                        id="consent"
                        name="consent"
                        required
                        class="mt-0.5"
                    />
                    <Label
                        for="consent"
                        class="text-sm font-normal text-primary-foreground/90"
                    >
                        I agree to receive occasional updates from Saba
                        International.
                    </Label>
                </div>
                <InputError :message="errors.consent" />

                <Button
                    type="submit"
                    variant="cta"
                    class="w-full"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Subscribe for Updates
                </Button>

                <p
                    v-if="recentlySuccessful"
                    class="text-center text-sm text-primary-foreground/90"
                >
                    Thanks — check your inbox for confirmation.
                </p>
            </Form>
        </div>
    </section>
</template>
