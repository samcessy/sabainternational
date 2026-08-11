<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-border bg-secondary/40">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div
                        class="flex items-center gap-2 text-base font-semibold text-foreground"
                    >
                        <span
                            class="flex size-8 items-center justify-center rounded-md bg-primary text-sm font-bold text-primary-foreground"
                            aria-hidden="true"
                        >
                            S
                        </span>
                        Saba International
                    </div>
                    <p class="mt-3 max-w-xs text-sm text-muted-foreground">
                        Supporting education, nutrition and shelter for
                        underprivileged youth and their families in East Africa.
                        Est. 2009.
                    </p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-foreground">
                        Explore
                    </h2>
                    <ul class="mt-3 space-y-2 text-sm text-muted-foreground">
                        <li>
                            <a href="/#our-work" class="hover:text-foreground"
                                >Our Work</a
                            >
                        </li>
                        <li>
                            <a
                                href="/#where-we-work"
                                class="hover:text-foreground"
                                >Where We Work</a
                            >
                        </li>
                        <li>
                            <a href="/#stories" class="hover:text-foreground"
                                >Stories</a
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-foreground">
                        Get Involved
                    </h2>
                    <ul class="mt-3 space-y-2 text-sm text-muted-foreground">
                        <li>
                            <Link href="/give" class="hover:text-foreground"
                                >Give</Link
                            >
                        </li>
                        <li>
                            <a href="/#support" class="hover:text-foreground"
                                >Partner With Us</a
                            >
                        </li>
                        <li>
                            <a href="/#support" class="hover:text-foreground"
                                >Subscribe</a
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-foreground">Legal</h2>
                    <p class="mt-3 text-sm text-muted-foreground">
                        Governance and policy documents (privacy, safeguarding,
                        financial transparency) are in progress and will be
                        published here.
                    </p>
                </div>
            </div>

            <div
                class="mt-10 flex flex-col gap-3 border-t border-border pt-6 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    &copy; {{ year }} Saba International. All rights reserved.
                </p>
                <p class="max-w-xl">
                    This site is being built to meet WCAG 2.2 AA accessibility
                    guidelines. If you encounter a barrier using it, please let
                    us know once our contact channel is live.
                </p>
            </div>
        </div>
    </footer>
</template>
