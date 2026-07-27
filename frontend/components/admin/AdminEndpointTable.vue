<script setup>
const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  endpoint: { type: String, required: true },
  columns: { type: Array, default: () => ['id', 'name', 'created_at'] },
  exportable: Boolean,
})

const entities = useAdminEntitiesStore()
const rows = computed(() => entities.records[props.endpoint] || [])
const label = column => column.replaceAll('_', ' ')

const download = () => {
  const config = useRuntimeConfig()
  window.location.href = `${config.public.apiBase}${props.endpoint}`
}

onMounted(async () => {
  try {
    await entities.list(props.endpoint)
  }
  catch {
    // The store exposes loading errors.
  }
})
</script>

<template>
  <div>
    <PageHeader :title="title" :subtitle="subtitle">
      <template v-if="exportable" #actions>
        <BaseButton label="Download export" @click="download" />
      </template>
    </PageHeader>
    <AppAlert :errors="entities.errors" />
    <DataTableShell
      :title="title"
      :loading="entities.loading"
      :errors="entities.errors"
      :empty="rows.length === 0"
      :empty-colspan="columns.length"
      empty-title="No records found"
    >
      <template #head>
        <thead>
          <tr>
            <th v-for="column in columns" :key="column">{{ label(column) }}</th>
          </tr>
        </thead>
      </template>
      <tr v-for="(row, index) in rows" :key="row.id || index">
        <td v-for="column in columns" :key="column">{{ row[column] ?? '—' }}</td>
      </tr>
    </DataTableShell>
  </div>
</template>
