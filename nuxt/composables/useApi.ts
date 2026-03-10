import type { Feature, FeatureDetail, CheckResult } from '~/types'

const baseURL = '/ffflags-api'

let csrfToken: string | null = null

async function getCsrfToken(): Promise<string> {
  if (csrfToken) return csrfToken

  const res = await $fetch<{ token: string }>(`${baseURL}/csrf-token`)
  csrfToken = res.token
  return csrfToken
}

async function post<T>(url: string, body: Record<string, unknown>): Promise<T> {
  const token = await getCsrfToken()
  return $fetch<T>(url, {
    method: 'POST',
    body,
    headers: {
      'X-XSRF-TOKEN': token,
    },
  })
}

export function useApi() {
  return {
    getFeatures: () =>
      $fetch<{ data: Feature[] }>(`${baseURL}/features`),

    getFeature: (slug: string) =>
      $fetch<{ data: FeatureDetail }>(`${baseURL}/features/${slug}`),

    updateRule: (slug: string, body: { condition: string; value: (string | number)[] }) =>
      post<{ success: boolean }>(`${baseURL}/features/${slug}`, body),

    checkRule: (slug: string, scopeId: string | number) =>
      post<CheckResult>(`${baseURL}/features/${slug}/check`, { scope_id: scopeId }),
  }
}
