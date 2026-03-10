export interface Feature {
  class: string
  name: string
  slug: string
  description: string
  bypasses_storage: boolean
  is_managed: boolean
  model_class: string | null
  model_scope_label: string | null
}

export interface ModelOption {
  key: string | number
  label: string
}

export interface ScopeConditionOption {
  value: string
  label: string
  is_multi_select: boolean
}

export interface CurrentRule {
  condition: string
  value: (string | number)[]
}

export interface FeatureDetail extends Feature {
  models: ModelOption[]
  current_rule: CurrentRule | null
  conditions: ScopeConditionOption[]
}

export interface CheckResult {
  scope_id: string | number
  pass: boolean
  message?: string
}
