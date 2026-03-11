<script setup>
import { computed } from 'vue'

defineProps({
    title: { type: String, default: 'FFFlags' },
})

const dashboardUrl = computed(() => {
    return document.querySelector('meta[name="ffflags-base-url"]')?.content || '/ffflags'
})
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <header class="border-b border-border bg-card">
            <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
                <a :href="dashboardUrl" class="text-lg font-semibold tracking-tight">FFFlags</a>
            </div>
        </header>
        <main class="max-w-4xl mx-auto px-4 py-8">
            <slot />
        </main>
    </div>
</template>
