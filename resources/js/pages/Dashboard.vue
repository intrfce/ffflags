<script setup>
import AppLayout from '@/components/AppLayout.vue'

const props = defineProps({
    features: { type: Array, default: () => [] },
    baseUrl: { type: String, default: '/ffflags' },
})
</script>

<template>
    <AppLayout title="FFFlags Dashboard">
        <h1 class="text-2xl font-bold mb-6">Feature Flags</h1>

        <div v-if="features.length === 0" class="rounded-lg border border-border bg-card p-6">
            <p class="text-muted-foreground">
                No feature flags discovered. Create one with
                <code class="bg-muted px-1.5 py-0.5 rounded text-sm font-mono">php artisan make:feature</code>
            </p>
        </div>

        <div v-else class="grid gap-4">
            <a
                v-for="feature in features"
                :key="feature.slug"
                :href="`${baseUrl}/features/${feature.slug}`"
                class="block rounded-lg border border-border bg-card p-5 hover:border-primary/40 transition-colors"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold">{{ feature.name }}</h2>
                        <p v-if="feature.description" class="text-sm text-muted-foreground mt-1">{{ feature.description }}</p>
                    </div>
                    <span
                        v-if="feature.is_managed"
                        class="shrink-0 inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-primary text-primary-foreground"
                    >
                        Managed
                    </span>
                    <span
                        v-else
                        class="shrink-0 inline-flex items-center px-2 py-1 text-xs rounded-md bg-muted text-muted-foreground"
                    >
                        Code-based
                    </span>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    <code class="bg-muted px-1.5 py-0.5 rounded text-xs font-mono text-muted-foreground">{{ feature.slug }}</code>
                    <span v-if="feature.is_managed" class="text-xs text-muted-foreground">
                        Scoped to {{ feature.model_scope_label }}
                    </span>
                </div>
            </a>
        </div>
    </AppLayout>
</template>
