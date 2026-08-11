<script setup lang="ts">
import PublicFooter from '@/components/PublicFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import SkipLink from '@/components/SkipLink.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <SkipLink />
        <PublicHeader />
        <main id="main-content" class="flex-1">
            <slot />
        </main>
        <PublicFooter />
    </div>
</template>
