<script setup>
const settings = useRevenueSettingsStore()

/** @type {import('vue').Ref<'dashboard' | 'filter' | 'create'>} */
const panelMode = ref('dashboard')

const form = reactive({
  property_id: '',
  dynamic_pricing: 0,
  base_rate: '',
  min_rate: '',
})

const filters = reactive({
  property_id: '',
  dynamic_pricing: null,
})

const editingId = ref(null)

const allRows = computed(() => {
  const data = settings.data?.data || settings.data || []

  return Array.isArray(data) ? data : []
})

const rows = computed(() => {
  const propertyQuery = filters.property_id.trim()

  return allRows.value.filter(row => {
    if (propertyQuery && String(row.property_id) !== propertyQuery)
      return false

    if (filters.dynamic_pricing !== null && filters.dynamic_pricing !== undefined && filters.dynamic_pricing !== '') {
      const enabled = Boolean(Number(row.dynamic_pricing))
      if (filters.dynamic_pricing === 'yes' && !enabled)
        return false
      if (filters.dynamic_pricing === 'no' && enabled)
        return false
    }

    return true
  })
})

const dynamicFilterItems = [
  { title: 'All settings', value: null },
  { title: 'Dynamic pricing on', value: 'yes' },
  { title: 'Dynamic pricing off', value: 'no' },
]

const stats = computed(() => {
  const list = allRows.value
  const dynamicOn = list.filter(row => Boolean(Number(row.dynamic_pricing)))
  const withBase = list.filter(row => row.base_rate != null && row.base_rate !== '')
  const withMin = list.filter(row => row.min_rate != null && row.min_rate !== '')
  const avgBase = withBase.length
    ? withBase.reduce((sum, row) => sum + Number(row.base_rate || 0), 0) / withBase.length
    : 0

  return {
    total: list.length,
    dynamicOn: dynamicOn.length,
    dynamicOff: list.length - dynamicOn.length,
    withBase: withBase.length,
    withMin: withMin.length,
    avgBase,
  }
})

const statusChartSeries = computed(() => [stats.value.dynamicOn, stats.value.dynamicOff])

const statusChartOptions = computed(() => ({
  chart: { type: 'donut', parentHeightOffset: 0, toolbar: { show: false } },
  labels: ['Dynamic on', 'Dynamic off'],
  colors: ['#28c76f', '#a8aaae'],
  legend: { position: 'bottom', fontSize: '13px' },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
        labels: {
          show: true,
          name: { show: true, fontSize: '13px' },
          value: { show: true, fontSize: '22px', fontWeight: 600, formatter: value => String(value) },
          total: { show: true, label: 'Total', fontSize: '13px', formatter: () => String(stats.value.total) },
        },
      },
    },
  },
  stroke: { width: 0 },
  tooltip: { y: { formatter: value => `${value} settings` } },
}))

const activityChartSeries = computed(() => ([{
  name: 'Settings',
  data: [stats.value.withBase, stats.value.withMin, Math.round(stats.value.avgBase)],
}]))

const activityChartOptions = computed(() => ({
  chart: { type: 'bar', parentHeightOffset: 0, toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } },
  colors: ['#696cff', '#00cfe8', '#ff9f43'],
  dataLabels: { enabled: false },
  legend: { show: false },
  grid: {
    strokeDashArray: 6,
    borderColor: 'rgba(75, 70, 92, 0.12)',
    yaxis: { lines: { show: true } },
    xaxis: { lines: { show: false } },
  },
  xaxis: {
    categories: ['Base rate', 'Min rate', 'Avg base'],
    labels: { style: { colors: '#a5a3ae', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: { style: { colors: '#a5a3ae' }, formatter: value => Math.round(value) },
    min: 0,
    forceNiceScale: true,
  },
  tooltip: { y: { formatter: value => String(value) } },
}))

const panelTitle = computed(() => {
  if (panelMode.value === 'filter')
    return 'Filter revenue settings'
  if (panelMode.value === 'create')
    return editingId.value ? 'Edit revenue setting' : 'Create revenue setting'

  return 'Revenue overview'
})

const openPanel = mode => {
  if (panelMode.value === mode) {
    panelMode.value = 'dashboard'

    return
  }

  if (mode === 'create' && panelMode.value !== 'create')
    resetForm()

  panelMode.value = mode
}

const clearFilters = () => {
  Object.assign(filters, { property_id: '', dynamic_pricing: null })
}

const resetForm = () => {
  editingId.value = null
  Object.assign(form, {
    property_id: '',
    dynamic_pricing: 0,
    base_rate: '',
    min_rate: '',
  })
}

const edit = row => {
  editingId.value = row.id
  Object.assign(form, {
    property_id: row.property_id || '',
    dynamic_pricing: Number(row.dynamic_pricing) || 0,
    base_rate: row.base_rate ?? '',
    min_rate: row.min_rate ?? '',
  })
  panelMode.value = 'create'
}

const submit = async () => {
  try {
    const payload = {
      property_id: Number(form.property_id),
      dynamic_pricing: Number(form.dynamic_pricing) || 0,
      base_rate: form.base_rate === '' ? null : Number(form.base_rate),
      min_rate: form.min_rate === '' ? null : Number(form.min_rate),
    }

    if (editingId.value)
      await settings.update(editingId.value, payload)
    else
      await settings.create(payload)

    resetForm()
    panelMode.value = 'dashboard'
    await settings.load()
  }
  catch {
    // Store exposes validation errors.
  }
}

onMounted(() => settings.load())

definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div>
    <PageHeader
      title="Revenue Settings"
      subtitle="Per-property revenue configuration"
    />

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <DataTableShell
          title="All revenue settings"
          :loading="settings.loading"
          :empty="rows.length === 0"
          :empty-colspan="5"
          empty-title="No revenue settings found"
          :empty-description="allRows.length && rows.length === 0 ? 'No settings match the current filters.' : ''"
        >
          <template #head>
            <thead>
              <tr>
                <th>Property</th>
                <th>Dynamic</th>
                <th>Base rate</th>
                <th>Min rate</th>
                <th>Actions</th>
              </tr>
            </thead>
          </template>

          <tr
            v-for="row in rows"
            :key="row.id"
          >
            <td>{{ row.property?.property_title || row.property_id }}</td>
            <td>{{ Number(row.dynamic_pricing) ? 'On' : 'Off' }}</td>
            <td>{{ row.base_rate ?? '—' }}</td>
            <td>{{ row.min_rate ?? '—' }}</td>
            <td class="text-no-wrap">
              <BaseButton
                size="small"
                variant="tonal"
                label="Edit"
                @click="edit(row)"
              />
            </td>
          </tr>
        </DataTableShell>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardItem>
            <VCardTitle>{{ panelTitle }}</VCardTitle>
            <template #append>
              <div class="d-flex flex-wrap gap-2">
                <BaseButton
                  size="small"
                  :variant="panelMode === 'filter' ? 'flat' : 'tonal'"
                  :color="panelMode === 'filter' ? 'primary' : undefined"
                  label="Filter"
                  prepend-icon="bx-filter-alt"
                  @click="openPanel('filter')"
                />
                <BaseButton
                  size="small"
                  :variant="panelMode === 'create' ? 'flat' : 'tonal'"
                  :color="panelMode === 'create' ? 'primary' : undefined"
                  label="Create"
                  prepend-icon="bx-plus"
                  @click="openPanel('create')"
                />
              </div>
            </template>
          </VCardItem>

          <VCardText>
            <div v-if="panelMode === 'dashboard'">
              <p class="text-body-2 text-medium-emphasis mb-4">
                Snapshot of revenue configuration
              </p>

              <VRow dense class="mb-2">
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--success pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Dynamic on
                    </div>
                    <div class="text-h5">
                      {{ stats.dynamicOn }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--muted pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Dynamic off
                    </div>
                    <div class="text-h5">
                      {{ stats.dynamicOff }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--primary pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      With base rate
                    </div>
                    <div class="text-h5">
                      {{ stats.withBase }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="6">
                  <div class="entity-stat-tile entity-stat-tile--warning pa-3 rounded">
                    <div class="text-caption text-medium-emphasis">
                      Avg base
                    </div>
                    <div class="text-h5">
                      {{ stats.avgBase.toLocaleString(undefined, { maximumFractionDigits: 0 }) }}
                    </div>
                  </div>
                </VCol>
              </VRow>

              <ClientOnly>
                <div class="mt-4">
                  <div class="text-subtitle-2 mb-2">
                    Dynamic pricing
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="donut"
                    height="220"
                    :options="statusChartOptions"
                    :series="statusChartSeries"
                  />
                  <EmptyState
                    v-else-if="!settings.loading"
                    title="No settings yet"
                    description="Counts and charts will appear once settings are loaded."
                  />
                </div>
                <div class="mt-6">
                  <div class="text-subtitle-2 mb-2">
                    Rates
                  </div>
                  <VueApexCharts
                    v-if="stats.total > 0"
                    type="bar"
                    height="200"
                    :options="activityChartOptions"
                    :series="activityChartSeries"
                  />
                </div>
              </ClientOnly>
            </div>

            <div v-else-if="panelMode === 'filter'">
              <VForm @submit.prevent>
                <BaseInput
                  v-model="filters.property_id"
                  label="Property ID"
                  class="mb-2"
                />
                <BaseSelect
                  v-model="filters.dynamic_pricing"
                  label="Dynamic pricing"
                  :items="dynamicFilterItems"
                  clearable
                  class="mb-2"
                />
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <BaseButton
                    type="button"
                    variant="tonal"
                    label="Clear filters"
                    @click="clearFilters"
                  />
                  <BaseButton
                    type="button"
                    variant="text"
                    label="Back to overview"
                    @click="panelMode = 'dashboard'"
                  />
                </div>
              </VForm>
            </div>

            <div v-else>
              <AppAlert :errors="settings.errors" />
              <VForm @submit.prevent="submit">
                <BaseInput
                  v-model="form.property_id"
                  label="Property ID"
                  type="number"
                  :error="settings.errors.property_id"
                />
                <BaseInput
                  v-model="form.dynamic_pricing"
                  label="Dynamic pricing (0/1)"
                  type="number"
                  :error="settings.errors.dynamic_pricing"
                />
                <BaseInput
                  v-model="form.base_rate"
                  label="Base rate"
                  type="number"
                  :error="settings.errors.base_rate"
                />
                <BaseInput
                  v-model="form.min_rate"
                  label="Min rate"
                  type="number"
                  :error="settings.errors.min_rate"
                />
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <BaseButton
                    type="submit"
                    :label="editingId ? 'Update setting' : 'Create setting'"
                    :loading="settings.loading"
                  />
                  <BaseButton
                    type="button"
                    variant="tonal"
                    label="Cancel"
                    @click="panelMode = 'dashboard'"
                  />
                </div>
              </VForm>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.entity-stat-tile {
  border: 1px solid rgba(75, 70, 92, 0.08);
  background: rgba(75, 70, 92, 0.03);
}

.entity-stat-tile--success {
  background: rgba(40, 199, 111, 0.08);
}

.entity-stat-tile--muted {
  background: rgba(168, 170, 174, 0.12);
}

.entity-stat-tile--primary {
  background: rgba(105, 108, 255, 0.1);
}

.entity-stat-tile--warning {
  background: rgba(255, 159, 67, 0.1);
}
</style>
