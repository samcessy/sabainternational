<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AlertTriangle } from '@lucide/vue';
import { computed } from 'vue';
import Seo from '@/components/Seo.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';

type TeamMember = {
    name: string;
    role: string;
    bio: string;
    board_member: boolean;
};

const props = defineProps<{
    teamMembers: TeamMember[];
}>();

const page = usePage<{ url: string }>();

// Every FAQ answer here is a fact already confirmed elsewhere on this site
// (audit-sourced). Questions that would need unverified numbers or
// documents (financial breakdowns, EIN, tax-exempt status) are left out
// entirely rather than answered evasively — saba.md §35's "never
// fabricate" rule applies to omission-as-honesty too, not just to text.
const faqs = [
    {
        question: 'What is Saba International?',
        answer: 'Saba International is a nonprofit, established in 2009, supporting education, nutrition, and shelter for underprivileged youth and their families in East Africa.',
    },
    {
        question: 'Where does Saba International work?',
        answer: "All of Saba's partner programs operate in Kenya, including programs serving children and families in and around Nairobi's Kibera settlement.",
    },
    {
        question: 'What programs does Saba International support?',
        answer: 'Four partner programs: New Dawn, Bethel Kibera School, The Nest, and The Hunter Initiative — spanning education, nutrition, shelter and family support, and youth economic empowerment.',
    },
    {
        question: 'How can I support Saba International?',
        answer: 'You can make a one-time or monthly donation, or subscribe to our newsletter to hear about volunteer and partnership opportunities as they launch.',
    },
];

const schema = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: faqs.map((faq) => ({
            '@type': 'Question',
            name: faq.question,
            acceptedAnswer: {
                '@type': 'Answer',
                text: faq.answer,
            },
        })),
    },
    ...props.teamMembers.map((member) => ({
        '@context': 'https://schema.org',
        '@type': 'Person',
        name: member.name,
        jobTitle: member.role,
        description: member.bio,
        worksFor: { '@type': 'NGO', name: 'Saba International' },
    })),
    {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            {
                '@type': 'ListItem',
                position: 1,
                name: 'Home',
                item: new URL('/', page.props.url).href,
            },
            {
                '@type': 'ListItem',
                position: 2,
                name: 'About',
                item: page.props.url,
            },
        ],
    },
]);

function initials(name: string): string {
    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}
</script>

<template>
    <Seo
        title="About"
        description="Saba International is a nonprofit supporting education, nutrition, and shelter for underprivileged youth and their families in East Africa. Meet our team and learn our story."
        :schema="schema"
    />

    <!-- Hero -->
    <section class="border-b border-border bg-primary text-primary-foreground">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                About Us
            </h1>
            <p class="mt-4 text-primary-foreground/90">
                Saba is a Hebrew word meaning "to fill — up to overflowing."
            </p>
        </div>
    </section>

    <!-- Our Story -->
    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-foreground sm:text-3xl">
            Our Story
        </h2>
        <p class="mt-4 text-muted-foreground">
            Saba International was established in 2009 by Tim and Cathy Woller,
            following Tim's U.S. Air Force assignment that brought the couple to
            Kenya from 2005 to 2008. During that time, they built relationships
            with local organizations serving vulnerable children and families —
            relationships that became the foundation for the partner programs
            Saba supports today.
        </p>
    </section>

    <!-- Our Mission -->
    <section class="border-y border-border bg-secondary/40">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-foreground sm:text-3xl">
                Our Mission
            </h2>
            <p class="mt-4 text-muted-foreground">
                Supporting education, nutrition and shelter for underprivileged
                youth and their families in East Africa.
            </p>
        </div>
    </section>

    <!-- Our Leadership -->
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-foreground sm:text-3xl">
            Our Leadership
        </h2>
        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            <div
                v-for="member in teamMembers"
                :key="member.name"
                class="flex gap-4 rounded-lg border border-border p-4"
            >
                <Avatar class="size-12 shrink-0">
                    <AvatarFallback>{{ initials(member.name) }}</AvatarFallback>
                </Avatar>
                <div>
                    <p class="font-semibold text-foreground">
                        {{ member.name }}
                    </p>
                    <p class="text-sm text-primary">{{ member.role }}</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ member.bio }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Governance -->
    <section id="governance" class="border-t border-border bg-secondary/40">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-foreground sm:text-3xl">
                Governance
            </h2>
            <Alert class="mt-6">
                <AlertTriangle class="size-4" />
                <AlertTitle>Content required</AlertTitle>
                <AlertDescription>
                    Our full board governance structure and key policies
                    (privacy, safeguarding, donation, conflict of interest, code
                    of conduct, child protection) are being finalized for
                    publication here. This is not an oversight — we'd rather
                    show you this note than guess.
                </AlertDescription>
            </Alert>
        </div>
    </section>

    <!-- Financial Transparency -->
    <section
        id="financial-transparency"
        class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8"
    >
        <h2 class="text-2xl font-bold text-foreground sm:text-3xl">
            Financial Transparency
        </h2>
        <Alert class="mt-6">
            <AlertTriangle class="size-4" />
            <AlertTitle>Content required</AlertTitle>
            <AlertDescription>
                Our tax-exempt status documentation, annual reports, and a
                breakdown of how donations are used are being prepared for
                publication here. We believe donor trust starts with
                transparency, which is why we're not filling this section with
                placeholder numbers in the meantime.
            </AlertDescription>
        </Alert>
    </section>

    <!-- FAQ -->
    <section class="border-t border-border bg-secondary/40">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-foreground sm:text-3xl">
                Frequently Asked Questions
            </h2>
            <div class="mt-8 space-y-4">
                <details
                    v-for="faq in faqs"
                    :key="faq.question"
                    class="rounded-lg border border-border bg-card p-4"
                >
                    <summary class="cursor-pointer font-medium text-foreground">
                        {{ faq.question }}
                    </summary>
                    <p class="mt-3 text-sm text-muted-foreground">
                        {{ faq.answer }}
                    </p>
                </details>
            </div>
        </div>
    </section>
</template>
