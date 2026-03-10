<script setup lang="ts">
import type { FeatureDetail, CheckResult } from '~/types'

const route = useRoute()
const toast = useToast()
const api = useApi()

const slug = route.params.slug as string

const { data: feature, status, refresh } = useAsyncData(`feature-${slug}`, async () => {
  const res = await api.getFeature(slug)
  return res.data
})

// Rule form state
const selectedCondition = ref('')
const selectedValues = ref<(string | number)[]>([])

// Check form state
const checkScopeId = ref('')
const checkResult = ref<CheckResult | null>(null)
const checkLoading = ref(false)

// Saving state
const saving = ref(false)

// Sync form state when feature loads
watch(feature, (f) => {
  if (f?.current_rule) {
    selectedCondition.value = f.current_rule.condition
    selectedValues.value = [...f.current_rule.value]
  }
}, { immediate: true })

const currentConditionConfig = computed(() => {
  if (!feature.value) return null
  return feature.value.conditions.find(c => c.value === selectedCondition.value)
})

const isMultiSelect = computed(() => currentConditionConfig.value?.is_multi_select ?? false)

// Reset selected values when condition changes between single/multi
watch(isMultiSelect, () => {
  selectedValues.value = []
})

const conditionOptions = computed(() => {
  if (!feature.value) return []
  return feature.value.conditions.map(c => ({
    label: c.label,
    value: c.value,
  }))
})

const modelOptions = computed(() => {
  if (!feature.value) return []
  return feature.value.models.map(m => ({
    label: String(m.label),
    value: String(m.key),
  }))
})

async function saveRule() {
  if (!selectedCondition.value || selectedValues.value.length === 0) return

  saving.value = true
  try {
    await api.updateRule(slug, {
      condition: selectedCondition.value,
      value: selectedValues.value,
    })
    toast.add({ title: 'Rule saved successfully.', color: 'success', icon: 'i-lucide-check' })
    await refresh()
  } catch (e: any) {
    toast.add({ title: 'Failed to save rule.', description: e?.data?.message || e.message, color: 'error', icon: 'i-lucide-x-circle' })
  } finally {
    saving.value = false
  }
}

async function checkRule() {
  if (!checkScopeId.value) return

  checkLoading.value = true
  checkResult.value = null
  try {
    checkResult.value = await api.checkRule(slug, checkScopeId.value)
  } catch (e: any) {
    toast.add({ title: 'Check failed.', description: e?.data?.message || e.message, color: 'error', icon: 'i-lucide-x-circle' })
  } finally {
    checkLoading.value = false
  }
}

// Single select needs a scalar v-model
const singleValue = computed({
  get: () => selectedValues.value[0] != null ? String(selectedValues.value[0]) : '',
  set: (val: string) => { selectedValues.value = val ? [val] : [] },
})
</script>

<template>
  <UContainer class="py-8">
    <div class="mb-6">
      <UButton
        to="/ffflags/admin"
        label="Back to dashboard"
        variant="ghost"
        icon="i-lucide-arrow-left"
        size="sm"
      />
    </div>

    <div v-if="status === 'pending'" class="space-y-4">
      <USkeleton class="h-8 w-64" />
      <USkeleton class="h-48 w-full" />
    </div>

    <template v-else-if="feature">
      <div class="mb-6">
        <h1 class="text-2xl font-bold">
          {{ feature.name }}
        </h1>
        <p v-if="feature.description" class="text-gray-500 mt-1">
          {{ feature.description }}
        </p>
        <div class="flex gap-2 mt-2">
          <UBadge
            v-if="feature.is_managed"
            color="success"
            variant="subtle"
            label="Managed"
          />
          <UBadge v-else color="neutral" variant="subtle" label="Code" />
          <UBadge color="neutral" variant="outline" :label="`Slug: ${feature.slug}`" />
          <UBadge
            v-if="feature.model_scope_label"
            color="info"
            variant="subtle"
            :label="`Scope: ${feature.model_scope_label}`"
          />
        </div>
      </div>

      <!-- Managed feature: rule configuration -->
      <div v-if="feature.is_managed" class="space-y-6">
        <UCard>
          <template #header>
            <h2 class="font-semibold">
              Rule Configuration
            </h2>
          </template>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Condition</label>
              <USelect
                v-model="selectedCondition"
                :items="conditionOptions"
                placeholder="Select condition..."
                value-key="value"
                label-key="label"
              />
            </div>

            <div v-if="selectedCondition">
              <label class="block text-sm font-medium mb-1">
                {{ feature.model_scope_label }} value{{ isMultiSelect ? 's' : '' }}
              </label>

              <USelectMenu
                v-if="isMultiSelect"
                v-model="selectedValues"
                :items="modelOptions"
                placeholder="Select values..."
                multiple
                value-key="value"
                label-key="label"
              />

              <USelect
                v-else
                v-model="singleValue"
                :items="modelOptions"
                placeholder="Select value..."
                value-key="value"
                label-key="label"
              />
            </div>
          </div>

          <template #footer>
            <UButton
              label="Save Rule"
              icon="i-lucide-save"
              :loading="saving"
              :disabled="!selectedCondition || selectedValues.length === 0"
              @click="saveRule"
            />
          </template>
        </UCard>

        <!-- Check rule -->
        <UCard>
          <template #header>
            <h2 class="font-semibold">
              Test Rule
            </h2>
          </template>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">
                {{ feature.model_scope_label }} ID
              </label>
              <UInput
                v-model="checkScopeId"
                placeholder="Enter an ID to test..."
              />
            </div>

            <UButton
              label="Check"
              icon="i-lucide-play"
              variant="soft"
              :loading="checkLoading"
              :disabled="!checkScopeId"
              @click="checkRule"
            />

            <UAlert
              v-if="checkResult"
              :color="checkResult.pass ? 'success' : 'error'"
              :icon="checkResult.pass ? 'i-lucide-check-circle' : 'i-lucide-x-circle'"
              :title="checkResult.pass
                ? `${feature.model_scope_label} #${checkResult.scope_id} passes this rule`
                : checkResult.message || `${feature.model_scope_label} #${checkResult.scope_id} does not pass this rule`"
              variant="subtle"
            />
          </div>
        </UCard>
      </div>

      <!-- Code-managed feature -->
      <UCard v-else>
        <div class="text-center py-4 text-gray-500">
          <p>This feature flag is managed by code.</p>
          <p class="text-sm mt-1">
            Its resolution is determined by the <code>resolve()</code> method in
            <code>{{ feature.class }}</code>.
          </p>
        </div>
      </UCard>
    </template>
  </UContainer>
</template>
