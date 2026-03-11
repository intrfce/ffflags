<script setup>
import { ref } from 'vue'
import AppLayout from '@/components/AppLayout.vue'

const props = defineProps({
    feature: { type: Object, required: true },
    models: { type: Array, default: () => [] },
    modelName: { type: String, default: '' },
    currentRule: { type: Object, default: null },
    conditions: { type: Array, default: () => [] },
    baseUrl: { type: String, default: '/ffflags' },
    csrfToken: { type: String, default: '' },
    success: { type: String, default: '' },
    checkResult: { type: Object, default: null },
})

const condition = ref(props.currentRule?.condition || '')
const selectedValues = ref(props.currentRule?.value || [])

const isMulti = () => condition.value === 'is_one_of' || condition.value === 'is_none_of'

function onConditionChange() {
    if (!isMulti() && selectedValues.value.length > 1) {
        selectedValues.value = [selectedValues.value[0]]
    }
}
</script>

<template>
    <AppLayout :title="`${feature.name} - FFFlags`">
        <a :href="baseUrl" class="text-sm text-primary hover:underline mb-4 inline-block">&larr; Back to dashboard</a>

        <!-- Feature info -->
        <div class="rounded-lg border border-border bg-card p-6 mb-6">
            <h1 class="text-2xl font-bold">{{ feature.name }}</h1>
            <p class="text-sm text-muted-foreground mt-1">
                <code class="bg-muted px-1.5 py-0.5 rounded text-xs font-mono">{{ feature.slug }}</code>
            </p>
            <p v-if="feature.description" class="text-muted-foreground mt-2">{{ feature.description }}</p>
        </div>

        <!-- Success message -->
        <div v-if="success" class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3 mb-6 text-sm">
            {{ success }}
        </div>

        <!-- Rule config (managed features only) -->
        <template v-if="feature.is_managed">
            <div class="rounded-lg border border-border bg-card p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">
                    Model Scope Rule
                    <span class="text-muted-foreground font-normal">({{ modelName }})</span>
                </h2>

                <form method="POST" :action="`${baseUrl}/features/${feature.slug}`">
                    <input type="hidden" name="_token" :value="csrfToken" />

                    <div class="space-y-4">
                        <div>
                            <label for="condition" class="block text-sm font-medium mb-1">Condition</label>
                            <select
                                id="condition"
                                name="condition"
                                v-model="condition"
                                @change="onConditionChange"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <option value="">Select a condition...</option>
                                <option v-for="c in conditions" :key="c.value" :value="c.value">{{ c.label }}</option>
                            </select>
                        </div>

                        <div v-show="condition">
                            <label class="block text-sm font-medium mb-1">
                                {{ isMulti() ? 'Select models' : 'Select a model' }}
                            </label>

                            <!-- Single select -->
                            <select
                                v-if="!isMulti()"
                                name="value[]"
                                v-model="selectedValues[0]"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <option value="">Select...</option>
                                <option v-for="m in models" :key="m.key" :value="m.key">{{ m.label }}</option>
                            </select>

                            <!-- Multi select -->
                            <select
                                v-else
                                name="value[]"
                                v-model="selectedValues"
                                multiple
                                :size="Math.min(models.length, 8)"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <option v-for="m in models" :key="m.key" :value="m.key">{{ m.label }}</option>
                            </select>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90"
                            >
                                Save Rule
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Test rule -->
            <div class="rounded-lg border border-border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Test Rule</h2>

                <form method="POST" :action="`${baseUrl}/features/${feature.slug}/check`" class="flex items-end gap-3">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <div class="flex-1">
                        <label for="scope_id" class="block text-sm font-medium mb-1">{{ modelName }} ID</label>
                        <input
                            type="text"
                            name="scope_id"
                            id="scope_id"
                            placeholder="Enter an ID to test..."
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                        />
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground"
                    >
                        Check
                    </button>
                </form>

                <div
                    v-if="checkResult"
                    class="mt-4 rounded-lg px-4 py-3 text-sm"
                    :class="checkResult.pass ? 'border border-green-200 bg-green-50 text-green-800' : 'border border-red-200 bg-red-50 text-red-800'"
                >
                    <template v-if="checkResult.message">{{ checkResult.message }}</template>
                    <template v-else>
                        {{ modelName }} ID <strong>{{ checkResult.id }}</strong>: {{ checkResult.pass ? 'Passes' : 'Does not pass' }}
                    </template>
                </div>
            </div>
        </template>

        <!-- Code-based feature -->
        <div v-else class="rounded-lg border border-border bg-card p-6">
            <p class="text-muted-foreground">
                This feature uses code-based resolution via the
                <code class="bg-muted px-1.5 py-0.5 rounded text-sm font-mono">resolve()</code> method.
            </p>
        </div>
    </AppLayout>
</template>
