<script setup>
import { ref, computed, onMounted } from 'vue'
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
    logEvaluations: { type: Boolean, default: false },
})

const matchMode = ref(props.currentRule?.match_mode || 'any')
const condition = ref(props.currentRule?.condition || '')
const selectedValues = ref(props.currentRule?.value || [])
const modelSearch = ref('')
const searchFocused = ref(false)

const isMulti = () => condition.value === 'is_one_of' || condition.value === 'is_none_of'

const modelsByKey = computed(() => {
    const map = {}
    for (const m of props.models) {
        map[String(m.key)] = m
    }
    return map
})

const selectedModels = computed(() => {
    return selectedValues.value
        .map(key => modelsByKey.value[String(key)])
        .filter(Boolean)
})

const searchResults = computed(() => {
    if (!modelSearch.value) return []
    const search = modelSearch.value.toLowerCase()
    return props.models.filter(m =>
        (String(m.label).toLowerCase().includes(search) ||
        String(m.key).toLowerCase().includes(search)) &&
        !selectedValues.value.includes(String(m.key))
    ).slice(0, 20)
})

const showDropdown = computed(() => {
    return searchFocused.value && modelSearch.value.length > 0 && searchResults.value.length > 0
})

function onConditionChange() {
    if (!isMulti() && selectedValues.value.length > 1) {
        selectedValues.value = [selectedValues.value[0]]
    }
}

function addModel(key) {
    const strKey = String(key)
    if (!isMulti()) {
        selectedValues.value = [strKey]
    } else if (!selectedValues.value.includes(strKey)) {
        selectedValues.value.push(strKey)
    }
    modelSearch.value = ''
}

function removeModel(key) {
    const strKey = String(key)
    const idx = selectedValues.value.indexOf(strKey)
    if (idx > -1) {
        selectedValues.value.splice(idx, 1)
    }
}

function onSearchBlur() {
    // Small delay so click on dropdown item registers before hiding
    setTimeout(() => { searchFocused.value = false }, 150)
}

function confirmClear(e) {
    if (confirm('Are you sure you want to clear all conditions? This will remove the rule entirely.')) {
        e.target.submit()
    }
}

// Evaluation log
const evaluations = ref([])
const evalPage = ref(1)
const evalLastPage = ref(1)
const evalTotal = ref(0)
const evalLoading = ref(false)
const expandedEval = ref(null)

async function fetchEvaluations(page = 1) {
    evalLoading.value = true
    try {
        const res = await fetch(`${props.baseUrl}/features/${props.feature.slug}/evaluations?page=${page}`)
        const data = await res.json()
        evaluations.value = data.data
        evalPage.value = data.current_page
        evalLastPage.value = data.last_page
        evalTotal.value = data.total
    } finally {
        evalLoading.value = false
    }
}

function formatDate(iso) {
    const d = new Date(iso)
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
        + ' ' + d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

function shortenPath(path) {
    if (!path) return null
    const parts = path.split('/')
    return parts.length > 3 ? '.../' + parts.slice(-3).join('/') : path
}

function toggleEvalDetail(id) {
    expandedEval.value = expandedEval.value === id ? null : id
}

const conditionLabels = {
    equals: 'Equals',
    does_not_equal: 'Does Not Equal',
    is_one_of: 'Is One Of',
    is_none_of: 'Is None Of',
}

onMounted(() => {
    if (props.logEvaluations) {
        fetchEvaluations()
    }
})
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
                    <input type="hidden" name="match_mode" :value="matchMode" />
                    <input v-if="selectedValues.length === 0" type="hidden" name="value" value="" />
                    <template v-for="v in selectedValues" :key="v">
                        <input type="hidden" name="value[]" :value="v" />
                    </template>

                    <div class="space-y-4">
                        <!-- Match mode toggle -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Match Mode</label>
                            <div class="inline-flex rounded-md border border-input overflow-hidden">
                                <button
                                    type="button"
                                    @click="matchMode = 'any'"
                                    class="px-4 py-2 text-sm font-medium transition-colors"
                                    :class="matchMode === 'any'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-background text-foreground hover:bg-accent'"
                                >
                                    Matches Any
                                </button>
                                <button
                                    type="button"
                                    @click="matchMode = 'all'"
                                    class="px-4 py-2 text-sm font-medium border-l border-input transition-colors"
                                    :class="matchMode === 'all'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-background text-foreground hover:bg-accent'"
                                >
                                    Matches All
                                </button>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">
                                <template v-if="matchMode === 'any'">
                                    The scope passes if <strong>any</strong> of the conditions match (OR).
                                </template>
                                <template v-else>
                                    The scope passes if <strong>all</strong> conditions match (AND).
                                </template>
                            </p>
                        </div>

                        <!-- Condition -->
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

                        <!-- Model picker -->
                        <div v-show="condition">
                            <label class="block text-sm font-medium mb-1">
                                {{ isMulti() ? `Select ${modelName}s` : `Select a ${modelName}` }}
                            </label>

                            <!-- Selected items as tags -->
                            <div v-if="selectedModels.length > 0" class="flex flex-wrap gap-1.5 mb-2">
                                <span
                                    v-for="m in selectedModels"
                                    :key="m.key"
                                    class="inline-flex items-center gap-1 rounded-md bg-primary/10 text-primary px-2 py-1 text-sm"
                                >
                                    {{ m.label }}
                                    <span class="text-xs text-muted-foreground font-mono">#{{ m.key }}</span>
                                    <button
                                        type="button"
                                        @click="removeModel(m.key)"
                                        class="ml-0.5 hover:text-destructive transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 3l6 6M9 3l-6 6" />
                                        </svg>
                                    </button>
                                </span>
                            </div>

                            <!-- Search input with dropdown -->
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="modelSearch"
                                    @focus="searchFocused = true"
                                    @blur="onSearchBlur"
                                    :placeholder="`Search ${modelName}s to add...`"
                                    class="w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                                />
                                <div
                                    v-if="showDropdown"
                                    class="absolute z-10 mt-1 w-full rounded-md border border-input bg-background shadow-lg max-h-48 overflow-y-auto"
                                >
                                    <div
                                        v-for="m in searchResults"
                                        :key="m.key"
                                        @click="addModel(m.key)"
                                        class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-accent transition-colors border-b border-border last:border-b-0"
                                    >
                                        <span class="text-sm">{{ m.label }}</span>
                                        <span class="text-xs text-muted-foreground ml-auto font-mono">{{ m.key }}</span>
                                    </div>
                                </div>
                                <div
                                    v-if="searchFocused && modelSearch.length > 0 && searchResults.length === 0"
                                    class="absolute z-10 mt-1 w-full rounded-md border border-input bg-background shadow-lg"
                                >
                                    <div class="px-3 py-3 text-sm text-muted-foreground text-center">
                                        No results found.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="submit"
                                :disabled="!condition"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Save Rule
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Clear all conditions -->
                <form v-if="currentRule" method="POST" :action="`${baseUrl}/features/${feature.slug}`" class="mt-4 pt-4 border-t border-border" @submit.prevent="confirmClear">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <input type="hidden" name="_method" value="DELETE" />
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition-colors"
                    >
                        Clear All Conditions
                    </button>
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

        <!-- Evaluation log -->
        <div v-if="logEvaluations" class="rounded-lg border border-border bg-card p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">
                    Evaluation Log
                    <span v-if="evalTotal > 0" class="text-muted-foreground font-normal text-sm">({{ evalTotal }})</span>
                </h2>
                <button
                    type="button"
                    @click="fetchEvaluations(evalPage)"
                    :disabled="evalLoading"
                    class="text-xs text-primary hover:underline"
                >
                    Refresh
                </button>
            </div>

            <div v-if="evalLoading && evaluations.length === 0" class="text-sm text-muted-foreground text-center py-6">
                Loading...
            </div>

            <div v-else-if="evaluations.length === 0" class="text-sm text-muted-foreground text-center py-6">
                No evaluations recorded yet.
            </div>

            <template v-else>
                <div class="divide-y divide-border">
                    <div v-for="ev in evaluations" :key="ev.id" class="py-3 first:pt-0 last:pb-0">
                        <div
                            class="flex items-start gap-3"
                            :class="ev.conditions_snapshot ? 'cursor-pointer' : ''"
                            @click="ev.conditions_snapshot && toggleEvalDetail(ev.id)"
                        >
                            <span
                                class="mt-0.5 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium flex-shrink-0"
                                :class="ev.result
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'"
                            >
                                {{ ev.result ? 'Pass' : 'Fail' }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 text-sm">
                                    <span v-if="ev.scope_type" class="text-foreground">
                                        {{ ev.scope_type }}
                                        <span class="font-mono text-muted-foreground">#{{ ev.scope_id }}</span>
                                    </span>
                                    <span v-else class="text-muted-foreground">No scope</span>
                                </div>
                                <div v-if="ev.call_file" class="text-xs text-muted-foreground mt-0.5 font-mono truncate" :title="ev.call_file + ':' + ev.call_line">
                                    {{ shortenPath(ev.call_file) }}<span v-if="ev.call_line">:{{ ev.call_line }}</span>
                                </div>
                            </div>
                            <span class="text-xs text-muted-foreground whitespace-nowrap flex-shrink-0">
                                {{ formatDate(ev.created_at) }}
                            </span>
                            <svg
                                v-if="ev.conditions_snapshot"
                                class="w-4 h-4 text-muted-foreground flex-shrink-0 mt-0.5 transition-transform"
                                :class="expandedEval === ev.id ? 'rotate-180' : ''"
                                viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"
                            >
                                <path d="M4 6l4 4 4-4" />
                            </svg>
                        </div>
                        <!-- Conditions snapshot detail -->
                        <div v-if="expandedEval === ev.id && ev.conditions_snapshot" class="mt-2 ml-8 rounded-md bg-muted/50 border border-border px-3 py-2 text-xs">
                            <div class="text-muted-foreground font-medium mb-1">Conditions at time of evaluation</div>
                            <div class="space-y-1">
                                <div>
                                    <span class="text-muted-foreground">Match mode:</span>
                                    <span class="font-medium ml-1">{{ ev.conditions_snapshot.match_mode === 'all' ? 'All (AND)' : 'Any (OR)' }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Condition:</span>
                                    <span class="font-medium ml-1">{{ conditionLabels[ev.conditions_snapshot.condition] || ev.conditions_snapshot.condition }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Values:</span>
                                    <span v-if="ev.conditions_snapshot.value && ev.conditions_snapshot.value.length > 0" class="font-mono ml-1">{{ ev.conditions_snapshot.value.join(', ') }}</span>
                                    <span v-else class="text-muted-foreground ml-1">(none)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="evalLastPage > 1" class="flex items-center justify-between mt-4 pt-4 border-t border-border">
                    <button
                        type="button"
                        @click="fetchEvaluations(evalPage - 1)"
                        :disabled="evalPage <= 1 || evalLoading"
                        class="inline-flex items-center px-3 py-1.5 text-sm rounded-md border border-input bg-background hover:bg-accent disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <span class="text-sm text-muted-foreground">
                        Page {{ evalPage }} of {{ evalLastPage }}
                    </span>
                    <button
                        type="button"
                        @click="fetchEvaluations(evalPage + 1)"
                        :disabled="evalPage >= evalLastPage || evalLoading"
                        class="inline-flex items-center px-3 py-1.5 text-sm rounded-md border border-input bg-background hover:bg-accent disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
