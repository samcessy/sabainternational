<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';

// A mix of real pages (About, Contact) and anchors into Home's own
// sections — anchors are prefixed with `/` so they resolve correctly from
// any page, not just when already on `/`. Inertia's <Link> handles both
// the same way. Our Work/Stories/Get Involved become dedicated pages as
// they're built; saba.md §4.2 — keep navigation simple, not exhaustive.
const navItems = [
    { title: 'About', href: '/about' },
    { title: 'Our Work', href: '/#our-work' },
    { title: 'Stories', href: '/#stories' },
    { title: 'Get Involved', href: '/#support' },
    { title: 'Contact', href: '/contact' },
];
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
        >
            <Link
                href="/"
                class="flex items-center gap-2 text-lg font-semibold text-foreground"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-md bg-primary text-sm font-bold text-primary-foreground"
                    aria-hidden="true"
                >
                    S
                </span>
                Saba International
            </Link>

            <nav class="hidden items-center gap-8 lg:flex" aria-label="Primary">
                <Link
                    v-for="item in navItems"
                    :key="item.title"
                    :href="item.href"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    {{ item.title }}
                </Link>
            </nav>

            <div class="flex items-center gap-2">
                <Button
                    as-child
                    variant="cta"
                    size="sm"
                    class="hidden sm:inline-flex"
                >
                    <Link href="/give">Make a Difference</Link>
                </Button>

                <Sheet>
                    <SheetTrigger as-child class="lg:hidden">
                        <Button
                            variant="ghost"
                            size="icon"
                            aria-label="Open menu"
                        >
                            <Menu class="size-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-[300px] p-6">
                        <SheetHeader class="p-0">
                            <SheetTitle>Menu</SheetTitle>
                        </SheetHeader>
                        <nav
                            class="mt-6 flex flex-col gap-1"
                            aria-label="Primary"
                        >
                            <Link
                                v-for="item in navItems"
                                :key="item.title"
                                :href="item.href"
                                class="rounded-md px-3 py-2 text-sm font-medium text-foreground hover:bg-accent hover:text-accent-foreground"
                            >
                                {{ item.title }}
                            </Link>
                        </nav>
                        <Button as-child variant="cta" class="mt-6 w-full">
                            <Link href="/give">Make a Difference</Link>
                        </Button>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    </header>
</template>
