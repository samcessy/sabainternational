<script setup lang="ts">
// saba.md §14.1 — skip-to-content link for keyboard users. Visually
// hidden until focused, matching standard skip-link behavior.
</script>

<template>
    <a
        href="#main-content"
        class="sr-only z-50 rounded-md bg-primary px-4 py-2 text-primary-foreground focus:not-sr-only focus:fixed focus:top-4 focus:left-4"
    >
        Skip to main content
    </a>
</template>
