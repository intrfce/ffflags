<script setup lang="ts">
import type { Feature } from '~/types'
import type { TableColumn } from '@nuxt/ui'

const api = useApi()

const { data: features, status } = useAsyncData('features', async () => {
  const res = await api.getFeatures()
  return res.data
})

const columns: TableColumn<Feature>[] = [
  {
    accessorKey: 'name',
    header: 'Name',
  },
  {
    accessorKey: 'slug',
    header: 'Slug',
  },
  {
    accessorKey: 'description',
    header: 'Description',
  },
  {
    id: 'type',
    header: 'Type',
  },
  {
    id: 'actions',
    header: '',
  },
]
</script>

<template>
  <UContainer class="py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">
        FFFlags
      </h1>
    </div>

    <UCard>
      <UTable
        :data="features ?? []"
        :columns="columns"
        :loading="status === 'pending'"
      >
        <template #slug-cell="{ row }">
          <code class="text-sm bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">{{ row.original.slug }}</code>
        </template>

        <template #description-cell="{ row }">
          {{ row.original.description || '—' }}
        </template>

        <template #type-cell="{ row }">
          <UBadge
            v-if="row.original.is_managed"
            color="success"
            variant="subtle"
            label="Managed"
          />
          <UBadge
            v-else
            color="neutral"
            variant="subtle"
            label="Code"
          />
        </template>

        <template #actions-cell="{ row }">
          <UButton
            v-if="row.original.is_managed"
            :to="`/ffflags/admin/features/${row.original.slug}`"
            label="Configure"
            variant="soft"
            size="sm"
          />
          <UBadge
            v-else
            color="neutral"
            variant="outline"
            label="Managed by code"
          />
        </template>

        <template #empty>
          <div class="text-center py-8 text-gray-500">
            <p>No feature flags discovered.</p>
            <p class="text-sm mt-1">
              Run <code>php artisan make:feature</code> to create one.
            </p>
          </div>
        </template>
      </UTable>
    </UCard>
  </UContainer>
</template>
